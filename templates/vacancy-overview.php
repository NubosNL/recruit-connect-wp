<div class="recruit-connect-vacancy-overview container-fluid">
    <!-- Mobile Filter Button -->
    <div class="container">
        <button class="btn btn-primary d-lg-none w-100 mb-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas">
            <i class="bi bi-funnel"></i> <?php esc_html_e('Show Filters', 'recruit-connect-wp'); ?>
        </button>

        <div class="row">
            <!-- Filters Sidebar -->
            <div class="col-lg-3">
                <div class="filters-wrapper d-none d-lg-block">
		            <?php
		            $salary_range = $this->get_salary_range(); // Make sure $salary_range is available here if needed in filters
		            include RECRUIT_CONNECT_PLUGIN_DIR . 'templates/partials/vacancy-filters.php';
		            ?>
                </div>

                <!-- Mobile Offcanvas -->
                <div class="offcanvas offcanvas-start" tabindex="-1" id="filterOffcanvas">
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title"><?php esc_html_e('Filters', 'recruit-connect-wp'); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                    </div>
                    <div class="offcanvas-body">
			            <?php
			            $salary_range = $this->get_salary_range(); // Make sure $salary_range is available here too if needed
			            include RECRUIT_CONNECT_PLUGIN_DIR . 'templates/partials/vacancy-filters.php';
			            ?>
                    </div>
                </div>
            </div>

            <!-- Vacancies List -->
            <div class="col-lg-9">
                <!-- Search Bar -->
                <div class="search-bar mb-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" id="vacancySearch"
                               placeholder="<?php esc_attr_e('Search vacancies...', 'recruit-connect-wp'); ?>">
                    </div>
                </div>

                <!-- Results Count -->
                <div class="results-count mb-3">
                    <span class="total-count"></span>
                </div>

                <!-- Vacancies Grid -->
                <div class="vacancies-grid" id="vacanciesGrid">
                    <div class="loading-spinner text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden"><?php esc_html_e('Loading...', 'recruit-connect-wp'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="pagination-wrapper text-center mt-4"></div>
            </div>
        </div>
    </div>
	<?php
	$salary_range = $this->get_salary_range();
	wp_localize_script('recruit-connect-overview', 'recruitConnect', array(
		'ajaxurl' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('recruit_connect_overview'),
		'currency' => '€',
		'strings' => array(
			'noResults' => __('No vacancies found', 'recruit-connect-wp'),
			'loading' => __('Loading...', 'recruit-connect-wp'),
			'filters' => __('Filters', 'recruit-connect-wp'),
			'close' => __('Close', 'recruit-connect-wp'),
			'viewDetails' => __('View Details', 'recruit-connect-wp'),
			'vacancy' => __('Vacancy', 'recruit-connect-wp'),
			'vacancies' => __('Vacancies', 'recruit-connect-wp'),
			'salaryRange' => __('Salary Range', 'recruit-connect-wp'),
			'jobType' => __('Job Type', 'recruit-connect-wp'),
			'educationLevel' => __('Education Level', 'recruit-connect-wp'),
			'category' => __('Category', 'recruit-connect-wp'),
		),
		'searchComponents' => $search_components // Localize search components for JS
	));
	?>
</div>
