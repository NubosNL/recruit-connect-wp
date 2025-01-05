(function($) {
    'use strict';

    // Store filter values
    let filterValues = {
        search: '',
        category: '',
        education: '',
        jobtype: '',
        salaryMin: null,
        salaryMax: null
    };

    // Initialize components
    function init() {
        initFilters();
        initLoadMore();
        initApplicationForm();
        initSalarySlider();
    }

    // Initialize filters
    function initFilters() {
        const $searchInput = $('#rcwp-search-input');
        const $filters = $('.rcwp-filter');

        // Restore filter values from URL params
        restoreFilterValues();

        // Handle search input with debounce
        let searchTimeout;
        $searchInput.on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterValues.search = $(this).val();
                updateVacancies(true);
            }, 500);
        });

        // Handle filter changes
        $filters.on('change', function() {
            const filterType = $(this).data('filter');
            filterValues[filterType] = $(this).val();
            updateVacancies(true);
        });
    }

    // Initialize load more functionality
    function initLoadMore() {
        $('.rcwp-load-more-btn').on('click', function() {
            const $button = $(this);
            const offset = $('.rcwp-vacancy-card').length;

            $button.prop('disabled', true).text(rcwpFront.strings.loading);

            $.ajax({
                url: rcwpFront.ajaxurl,
                method: 'POST',
                data: {
                    action: 'rcwp_load_more_vacancies',
                    nonce: rcwpFront.nonce,
                    offset: offset,
                    filters: filterValues
                },
                success: function(response) {
                    if (response.success) {
                        $('.rcwp-vacancies-list').append(response.data.html);

                        if (!response.data.hasMore) {
                            $button.remove();
                        } else {
                            $button.prop('disabled', false)
                                  .text(rcwpFront.strings.loadMore);
                        }
                    }
                },
                error: function() {
                    $button.prop('disabled', false)
                           .text(rcwpFront.strings.loadMore);
                }
            });
        });
    }

    // Initialize application form
    function initApplicationForm() {
        $('.rcwp-application-form').on('submit', function(e) {
            e.preventDefault();

            const $form = $(this);
            const $submit = $form.find('.rcwp-submit-btn');
            const formData = new FormData(this);

            $submit.prop('disabled', true);

            $.ajax({
                url: rcwpFront.ajaxurl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $form.html('<div class="rcwp-success-message">' +
                                 response.data.message + '</div>');
                    } else {
                        showFormError($form, response.data);
                    }
                },
                error: function() {
                    showFormError($form, rcwpFront.strings.submitError);
                },
                complete: function() {
                    $submit.prop('disabled', false);
                }
            });
        });
    }

    // Initialize salary range slider
    function initSalarySlider() {
        const $minSlider = $('#rcwp-salary-min');
        const $maxSlider = $('#rcwp-salary-max');
        const $minValue = $('#rcwp-salary-min-value');
        const $maxValue = $('#rcwp-salary-max-value');

        function updateSliderValues() {
            filterValues.salaryMin = parseInt($minSlider.val());
            filterValues.salaryMax = parseInt($maxSlider.val());

            $minValue.text(formatSalary(filterValues.salaryMin));
            $maxValue.text(formatSalary(filterValues.salaryMax));
        }

        $minSlider.on('input', function() {
            const minVal = parseInt($(this).val());
            const maxVal = parseInt($maxSlider.val());

            if (minVal > maxVal) {
                $maxSlider.val(minVal);
            }

            updateSliderValues();
        });

        $maxSlider.on('input', function() {
            const maxVal = parseInt($(this).val());
            const minVal = parseInt($minSlider.val());

            if (maxVal < minVal) {
                $minSlider.val(maxVal);
            }

            updateSliderValues();
        });

        // Trigger change event after slider movement ends
        $minSlider.add($maxSlider).on('change', function() {
            updateVacancies(true);
        });

        // Initialize values
        updateSliderValues();
    }

    // Helper function to update vacancies list
    function updateVacancies(resetOffset = false) {
        const $list = $('.rcwp-vacancies-list');
        const $loadMore = $('.rcwp-load-more-btn');

        // Update URL parameters
        updateUrlParams();

        $.ajax({
            url: rcwpFront.ajaxurl,
            method: 'POST',
            data: {
                action: 'rcwp_load_more_vacancies',
                nonce: rcwpFront.nonce,
                offset: resetOffset ? 0 : $('.rcwp-vacancy-card').length,
                filters: filterValues
            },
            beforeSend: function() {
                if (resetOffset) {
                    $list.html('<div class="rcwp-loading">Loading...</div>');
                }
            },
            success: function(response) {
                if (response.success) {
                    if (resetOffset) {
                        $list.html(response.data.html);
                    } else {
                        $list.append(response.data.html);
                    }

                    $loadMore.toggle(response.data.hasMore);
                }
            }
        });
    }

    // Helper function to update URL parameters
    function updateUrlParams() {
        const params = new URLSearchParams(window.location.search);

        Object.entries(filterValues).forEach(([key, value]) => {
            if (value) {
                params.set(key, value);
            } else {
                params.delete(key);
            }
        });

        const newUrl = window.location.pathname +
                      (params.toString() ? '?' + params.toString() : '');
        window.history.pushState({}, '', newUrl);
    }

    // Helper function to restore filter values from URL
    function restoreFilterValues() {
        const params = new URLSearchParams(window.location.search);

        params.forEach((value, key) => {
            if (filterValues.hasOwnProperty(key)) {
                filterValues[key] = value;

                // Update UI elements
                if (key === 'search') {
                    $('#rcwp-search-input').val(value);
                } else if (['category', 'education', 'jobtype'].includes(key)) {
                    $(`.rcwp-filter[data-filter="${key}"]`).val(value);
                } else if (key === 'salaryMin') {
                    $('#rcwp-salary-min').val(value);
                } else if (key === 'salaryMax') {
                    $('#rcwp-salary-max').val(value);
                }
            }
        });
    }

    // Helper function to format salary
    function formatSalary(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'EUR'
        }).format(amount);
    }

    // Helper function to show form errors
    function showFormError($form, message) {
        const $error = $form.find('.rcwp-form-error');

        if ($error.length) {
            $error.html(message);
        } else {
            $form.prepend(`<div class="rcwp-form-error">${message}</div>`);
        }
    }

    // Initialize when document is ready
    $(document).ready(init);

})(jQuery);
