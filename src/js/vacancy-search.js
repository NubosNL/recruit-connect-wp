/**
 * Vacancy search and filter functionality
 */
(function($) {
    'use strict';

    class VacancySearch {
        constructor() {
            // Elements
            this.searchInput = $('#rcwp-search-input');
            this.categoryFilter = $('#rcwp-category-filter');
            this.educationFilter = $('#rcwp-education-filter');
            this.jobtypeFilter = $('#rcwp-jobtype-filter');
            this.salarySlider = $('#rcwp-salary-slider');
            this.loadMoreBtn = $('#rcwp-load-more');
            this.vacanciesList = $('#rcwp-vacancies-list');

            // State
            this.currentPage = 1;
            this.isLoading = false;
            this.filters = {
                search: '',
                category: '',
                education: '',
                jobtype: '',
                salary_min: '',
                salary_max: ''
            };

            this.initializeComponents();
            this.bindEvents();
        }

        initializeComponents() {
            // Initialize salary range slider if it exists
            if (this.salarySlider.length) {
                const salaryRange = this.getSalaryRange();
                this.salarySlider.slider({
                    range: true,
                    min: salaryRange.min,
                    max: salaryRange.max,
                    values: [salaryRange.min, salaryRange.max],
                    slide: (event, ui) => {
                        $('#salary-min').val(ui.values[0]);
                        $('#salary-max').val(ui.values[1]);
                    },
                    stop: () => this.handleFiltersChange()
                });

                // Set initial values
                $('#salary-min').val(this.salarySlider.slider('values', 0));
                $('#salary-max').val(this.salarySlider.slider('values', 1));
            }
        }

        bindEvents() {
            // Search input with debounce
            let searchTimeout;
            this.searchInput.on('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => this.handleFiltersChange(), 500);
            });

            // Filter changes
            this.categoryFilter.on('change', () => this.handleFiltersChange());
            this.educationFilter.on('change', () => this.handleFiltersChange());
            this.jobtypeFilter.on('change', () => this.handleFiltersChange());

            // Load more
            this.loadMoreBtn.on('click', () => this.loadMore());

            // Browser back/forward buttons
            window.addEventListener('popstate', (e) => {
                if (e.state) {
                    this.filters = e.state.filters;
                    this.currentPage = e.state.page;
                    this.updateFiltersUI();
                    this.fetchVacancies(false);
                }
            });
        }

        handleFiltersChange() {
            this.currentPage = 1;
            this.updateFilters();
            this.fetchVacancies(true);
            this.updateURL();
        }

        updateFilters() {
            this.filters = {
                search: this.searchInput.val(),
                category: this.categoryFilter.val(),
                education: this.educationFilter.val(),
                jobtype: this.jobtypeFilter.val(),
                salary_min: $('#salary-min').val(),
                salary_max: $('#salary-max').val()
            };
        }

        updateURL() {
            const params = new URLSearchParams();
            Object.entries(this.filters).forEach(([key, value]) => {
                if (value) params.set(key, value);
            });

            const newURL = `${window.location.pathname}?${params.toString()}`;
            window.history.pushState(
                { filters: this.filters, page: this.currentPage },
                '',
                newURL
            );
        }

        updateFiltersUI() {
            this.searchInput.val(this.filters.search);
            this.categoryFilter.val(this.filters.category);
            this.educationFilter.val(this.filters.education);
            this.jobtypeFilter.val(this.filters.jobtype);

            if (this.salarySlider.length) {
                this.salarySlider.slider('values', [
                    this.filters.salary_min,
                    this.filters.salary_max
                ]);
                $('#salary-min').val(this.filters.salary_min);
                $('#salary-max').val(this.filters.salary_max);
            }
        }

        async fetchVacancies(replaceContent = true) {
            if (this.isLoading) return;

            this.isLoading = true;
            this.toggleLoading(true);

            try {
                const response = await $.ajax({
                    url: rcwp_vars.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'rcwp_search_vacancies',
                        nonce: rcwp_vars.nonce,
                        page: this.currentPage,
                        ...this.filters
                    }
                });

                if (response.success) {
                    this.updateVacanciesList(response.data, replaceContent);
                    this.updateLoadMoreButton(response.data.max_pages);
                } else {
                    console.error('Error fetching vacancies:', response.data);
                }
            } catch (error) {
                console.error('Ajax error:', error);
            } finally {
                this.isLoading = false;
                this.toggleLoading(false);
            }
        }

        updateVacanciesList(data, replaceContent) {
            const vacanciesHtml = data.html;

            if (replaceContent) {
                this.vacanciesList.html(vacanciesHtml);
            } else {
                this.vacanciesList.find('.rcwp-vacancies-grid').append(vacanciesHtml);
            }

            // Animate new items
            this.vacanciesList.find('.rcwp-vacancy-card').addClass('animated fadeIn');
        }

        updateLoadMoreButton(maxPages) {
            if (this.currentPage >= maxPages) {
                this.loadMoreBtn.hide();
            } else {
                this.loadMoreBtn.show();
            }
        }

        loadMore() {
            this.currentPage++;
            this.fetchVacancies(false);
        }

        toggleLoading(show) {
            if (show) {
                this.vacanciesList.addClass('loading');
                this.loadMoreBtn.prop('disabled', true);
            } else {
                this.vacanciesList.removeClass('loading');
                this.loadMoreBtn.prop('disabled', false);
            }
        }

        getSalaryRange() {
            // This should be populated with actual min/max values from the server
            return {
                min: parseInt(rcwp_vars.salary_range.min) || 0,
                max: parseInt(rcwp_vars.salary_range.max) || 100000
            };
        }
    }

    // Initialize on document ready
    $(document).ready(() => {
        new VacancySearch();
    });

})(jQuery);
