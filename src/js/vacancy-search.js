class VacancySearch {
    constructor() {
        this.form = document.querySelector('.rcwp-search-form');
        this.resultsContainer = document.querySelector('.rcwp-vacancies-list');
        this.loadMoreButton = document.querySelector('.rcwp-load-more-btn');
        this.filterInputs = document.querySelectorAll('.rcwp-filter');
        this.searchInput = document.querySelector('#rcwp-search-input');
        this.salarySlider = document.querySelector('.rcwp-salary-range');

        this.currentPage = 1;
        this.isLoading = false;
        this.hasMore = true;

        this.initializeEvents();
        this.initializeSalarySlider();
        this.restoreFilterState();
    }

    initializeEvents() {
        // Debounced search input handler
        let searchTimeout;
        this.searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.handleSearch();
            }, 500);
        });

        // Filter change handlers
        this.filterInputs.forEach(input => {
            input.addEventListener('change', () => this.handleSearch());
        });

        // Load more button
        if (this.loadMoreButton) {
            this.loadMoreButton.addEventListener('click', () => this.loadMore());
        }

        // Handle browser back/forward
        window.addEventListener('popstate', (e) => {
            if (e.state && e.state.filters) {
                this.setFilters(e.state.filters);
                this.performSearch(false);
            }
        });
    }

    initializeSalarySlider() {
        if (!this.salarySlider) return;

        const minInput = this.salarySlider.querySelector('#salary-min');
        const maxInput = this.salarySlider.querySelector('#salary-max');
        const minValue = this.salarySlider.querySelector('#salary-min-value');
        const maxValue = this.salarySlider.querySelector('#salary-max-value');

        noUiSlider.create(this.salarySlider, {
            start: [parseInt(minInput.value), parseInt(maxInput.value)],
            connect: true,
            range: {
                'min': parseInt(minInput.getAttribute('min')),
                'max': parseInt(maxInput.getAttribute('max'))
            },
            format: {
                to: value => Math.round(value),
                from: value => Math.round(value)
            }
        });

        this.salarySlider.noUiSlider.on('update', (values) => {
            minValue.textContent = this.formatSalary(values[0]);
            maxValue.textContent = this.formatSalary(values[1]);
            minInput.value = values[0];
            maxInput.value = values[1];
        });

        this.salarySlider.noUiSlider.on('change', () => this.handleSearch());
    }

    handleSearch() {
        this.currentPage = 1;
        this.performSearch(true);
    }

    async performSearch(updateUrl = true) {
        if (this.isLoading) return;

        this.isLoading = true;
        this.showLoader();

        const filters = this.getFilters();

        try {
            const response = await this.fetchVacancies(filters);

            if (response.success) {
                this.updateResults(response.data, this.currentPage === 1);
                this.hasMore = response.data.max_pages > this.currentPage;
                this.toggleLoadMoreButton();

                if (updateUrl) {
                    this.updateUrl(filters);
                }
            } else {
                this.showError('Search failed. Please try again.');
            }
        } catch (error) {
            this.showError('An error occurred while searching.');
            console.error('Search error:', error);
        } finally {
            this.isLoading = false;
            this.hideLoader();
        }
    }

    async loadMore() {
        if (this.isLoading || !this.hasMore) return;

        this.currentPage++;
        await this.performSearch(false);
    }

    async fetchVacancies(filters) {
        const formData = new FormData();
        formData.append('action', 'rcwp_search_vacancies');
        formData.append('nonce', rcwpFront.nonce);
        formData.append('page', this.currentPage);

        for (const [key, value] of Object.entries(filters)) {
            formData.append(key, value);
        }

        const response = await fetch(rcwpFront.ajaxurl, {
            method: 'POST',
            body: formData
        });

        return await response.json();
    }

    updateResults(data, replace = true) {
        const html = this.generateVacanciesHtml(data.vacancies);

        if (replace) {
            this.resultsContainer.innerHTML = html;
        } else {
            this.resultsContainer.insertAdjacentHTML('beforeend', html);
        }

        this.updateResultsCount(data.total);
    }

    generateVacanciesHtml(vacancies) {
        return vacancies.map(vacancy => `
            <div class="rcwp-vacancy-card">
                <h3 class="rcwp-vacancy-title">
                    <a href="${vacancy.url}">${vacancy.title}</a>
                </h3>
                <div class="rcwp-vacancy-meta">
                    ${this.generateMetaHtml(vacancy.meta)}
                </div>
                <div class="rcwp-vacancy-excerpt">
                    ${vacancy.excerpt}
                </div>
                <a href="${vacancy.url}" class="rcwp-vacancy-link">
                    ${rcwpFront.strings.viewDetails}
                </a>
            </div>
        `).join('');
    }

    generateMetaHtml(meta) {
        const items = [];

        if (meta.company) {
            items.push(`<span class="rcwp-company">${meta.company}</span>`);
        }
        if (meta.location) {
            items.push(`<span class="rcwp-location">${meta.location}</span>`);
        }
        if (meta.salary) {
            items.push(`<span class="rcwp-salary">${meta.salary}</span>`);
        }
        if (meta.jobtype) {
            items.push(`<span class="rcwp-jobtype">${meta.jobtype}</span>`);
        }

        return items.join(' • ');
    }

    getFilters() {
        const filters = {
            search: this.searchInput.value,
            category: document.querySelector('[data-filter="category"]')?.value,
            education: document.querySelector('[data-filter="education"]')?.value,
            jobtype: document.querySelector('[data-filter="jobtype"]')?.value,
            salary_min: document.querySelector('#salary-min')?.value,
            salary_max: document.querySelector('#salary-max')?.value
        };

        return Object.fromEntries(
            Object.entries(filters).filter(([_, v]) => v != null && v !== '')
        );
    }

    setFilters(filters) {
        if (filters.search) {
            this.searchInput.value = filters.search;
        }

        this.filterInputs.forEach(input => {
            const filterName = input.dataset.filter;
            if (filters[filterName]) {
                input.value = filters[filterName];
            }
        });

        if (this.salarySlider && filters.salary_min && filters.salary_max) {
            this.salarySlider.noUiSlider.set([
                filters.salary_min,
                filters.salary_max
            ]);
        }
    }

    updateUrl(filters) {
        const url = new URL(window.location);

        // Clear existing parameters
        url.searchParams.forEach((_, key) => {
            if (key !== 'post_type') {
                url.searchParams.delete(key);
            }
        });

        // Add new parameters
        Object.entries(filters).forEach(([key, value]) => {
            if (value) {
                url.searchParams.set(key, value);
            }
        });

        window.history.pushState(
            { filters: filters },
            '',
            url.toString()
        );
    }

    restoreFilterState() {
        const params = new URLSearchParams(window.location.search);
        const filters = {};

        params.forEach((value, key) => {
            if (key !== 'post_type') {
                filters[key] = value;
            }
        });

        if (Object.keys(filters).length > 0) {
            this.setFilters(filters);
            this.performSearch(false);
        }
    }

    formatSalary(value) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'EUR'
        }).format(value);
    }

    showLoader() {
        this.resultsContainer.classList.add('loading');
    }

    hideLoader() {
        this.resultsContainer.classList.remove('loading');
    }

    showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'rcwp-error-message';
        errorDiv.textContent = message;

        this.resultsContainer.insertAdjacentElement('beforebegin', errorDiv);

        setTimeout(() => errorDiv.remove(), 5000);
    }

    toggleLoadMoreButton() {
        if (this.loadMoreButton) {
            this.loadMoreButton.style.display = this.hasMore ? 'block' : 'none';
        }
    }

    updateResultsCount(total) {
        const countElement = document.querySelector('.rcwp-results-count');
        if (countElement) {
            countElement.textContent = total === 1
                ? rcwpFront.strings.oneResult
                : rcwpFront.strings.multipleResults.replace('%d', total);
        }
    }
}

// Initialize on document ready
document.addEventListener('DOMContentLoaded', () => {
    new VacancySearch();
});
