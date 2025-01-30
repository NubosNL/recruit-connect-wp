console.log('vacancy-overview.js file is loaded and executing'); // ADD THIS LINE
(function($) {
    'use strict';

    const VacancyOverview = {
        init: function() {
            this.filters = this.loadFiltersFromStorage() || {};
            this.currentPage = 1;
            this.bindEvents();
            this.initializeFromUrl();
            this.loadVacancies();
        },

        bindEvents: function() {
            const self = this;

            // Search input with debounce (Keep this)
            let searchTimeout;
            $('#vacancySearch').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    self.filters.search = $(this).val();
                    self.currentPage = 1;
                    self.updateFilters();
                }, 500);
            });

            // Select filters (Keep this - and ensure jobtypeFilter is included if you added it)
            $('#educationFilter, #categoryFilter, #jobtypeFilter').on('change', function() {
                self.filters[$(this).attr('id')] = $(this).val();
                self.currentPage = 1;
                self.updateFilters();
            });

            // Pagination (Keep this)
            $(document).on('click', '.pagination .page-link', function(e) {
                e.preventDefault();
                self.currentPage = $(this).data('page');
                self.loadVacancies();
                $('html, body').animate({
                    scrollTop: $('.recruit-connect-vacancy-overview').offset().top - 50
                }, 500);
            });
        },

        updateFilters: function() {
            this.saveFiltersToStorage();
            this.updateUrl();
            this.loadVacancies();
        },

        loadVacancies: function() {
            const self = this;
            const $grid = $('#vacanciesGrid');

            $grid.addClass('loading');

            $.ajax({
                url: recruitConnect.ajaxurl,
                type: 'POST',
                data: {
                    action: 'recruit_connect_load_vacancies',
                    nonce: recruitConnect.nonce,
                    filters: this.filters,
                    page: this.currentPage
                },
                success: function(response) {
                    if (response.success) {
                        self.renderVacancies(response.data);
                    }
                },
                complete: function() {
                    $grid.removeClass('loading');
                }
            });
        },

        renderVacancies: function(data) {
            const $grid = $('#vacanciesGrid');
            const $count = $('.results-count');

            // Update total count
            $count.html(`${data.total} ${data.total === 1 ?
                recruitConnect.strings.vacancy :
                recruitConnect.strings.vacancies}`);

            // Render vacancies
            if (data.vacancies.length === 0) {
                $grid.html(`<div class="no-results">${recruitConnect.strings.noResults}</div>`);
                return;
            }

            let html = '';
            data.vacancies.forEach(vacancy => {
                html += this.renderVacancyCard(vacancy);
            });

            $grid.html(html);
            this.renderPagination(data.total_pages);
            this.highlightSearchTerms();
        },

        renderVacancyCard: function(vacancy) {
            return `
                <div class="vacancy-card">
                    <h3 class="vacancy-title">
                        <a href="${vacancy.link}">${vacancy.title}</a>
                    </h3>
                    <div class="vacancy-meta">
                        ${vacancy.location ? `
                            <span class="location">
                                <i class="bi bi-geo-alt"></i> ${vacancy.location}
                            </span>
                        ` : ''}
                        ${vacancy.job_type ? `
                            <span class="job-type">
                                <i class="bi bi-briefcase"></i> ${vacancy.job_type}
                            </span>
                        ` : ''}
                        ${vacancy.salary ? `
                            <span class="salary">
                                <i class="bi bi-currency-euro"></i>
                                ${vacancy.salary} ${recruitConnect.currency}
                            </span>
                        ` : ''}
                        <span class="date">
                            <i class="bi bi-calendar"></i> ${vacancy.date}
                        </span>
                    </div>
                    <div class="vacancy-excerpt">${vacancy.excerpt}</div>
                    <a href="${vacancy.link}" class="btn btn-primary btn-sm">
                        ${recruitConnect.strings.viewDetails}
                    </a>
                </div>
            `;
        },

        renderPagination: function(totalPages) {
            if (totalPages <= 1) return;

            let html = '<ul class="pagination justify-content-center">';

            // Previous button
            if (this.currentPage > 1) {
                html += `
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="${this.currentPage - 1}">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                `;
            }

            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                if (
                    i === 1 ||
                    i === totalPages ||
                    (i >= this.currentPage - 2 && i <= this.currentPage + 2)
                ) {
                    html += `
                        <li class="page-item ${i === this.currentPage ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>
                    `;
                } else if (
                    i === this.currentPage - 3 ||
                    i === this.currentPage + 3
                ) {
                    html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }

            // Next button
            if (this.currentPage < totalPages) {
                html += `
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="${this.currentPage + 1}">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                `;
            }

            html += '</ul>';
            $('.pagination-wrapper').html(html);
        },

        highlightSearchTerms: function() {
            if (!this.filters.search) return;

            const terms = this.filters.search.split(' ').filter(term => term.length > 2);
            if (terms.length === 0) return;

            $('.vacancy-title a, .vacancy-excerpt').each(function() {
                let text = $(this).text();
                terms.forEach(term => {
                    const regex = new RegExp(`(${term})`, 'gi');
                    text = text.replace(regex, '<mark>$1</mark>');
                });
                $(this).html(text);
            });
        },

        saveFiltersToStorage: function() {
            localStorage.setItem('recruitConnectFilters', JSON.stringify(this.filters));
        },

        loadFiltersFromStorage: function() {
            const stored = localStorage.getItem('recruitConnectFilters');
            return stored ? JSON.parse(stored) : null;
        },

        updateUrl: function() {
            const params = new URLSearchParams();

            Object.entries(this.filters).forEach(([key, value]) => {
                if (value) params.set(key, value);
            });

            if (this.currentPage > 1) {
                params.set('page', this.currentPage);
            }

            const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
            window.history.pushState({}, '', newUrl);
        },

        initializeFromUrl: function() {
            const params = new URLSearchParams(window.location.search);

            params.forEach((value, key) => {
                this.filters[key] = value;

                if (key === 'page') {
                    this.currentPage = parseInt(value);
                } else {
                    const $element = $(`#${key}`);
                    if ($element.length) {
                        $element.val(value);
                    }
                }
            });
        }
    };

    $(document).ready(function() {
        VacancyOverview.init();
    });

})(jQuery);
