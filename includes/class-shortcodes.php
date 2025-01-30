<?php
namespace RecruitConnect;

class Shortcodes {
	public function __construct() {
		add_shortcode('recruit_connect_vacancies', array($this, 'render_vacancies_list'));
		add_shortcode('recruit_connect_search', array($this, 'render_search_form'));
		add_shortcode('recruit_connect_vacancy', array($this, 'render_single_vacancy'));
		add_shortcode('recruit_connect_vacancy_overview', array($this, 'render_vacancy_overview'));
	}

	public function render_vacancy_overview($atts) {
		wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
		wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), null, true);
		wp_enqueue_style('nouislider', 'https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.css');
		wp_enqueue_script('nouislider', 'https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.js', array(), null, true);

		wp_enqueue_style('recruit-connect-overview', RECRUIT_CONNECT_PLUGIN_URL . 'public/css/vacancy-overview.css');
		wp_enqueue_script('recruit-connect-overview', RECRUIT_CONNECT_PLUGIN_URL . 'public/js/vacancy-overview.js', array('jquery', 'bootstrap', 'nouislider'), null, true);

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
			)
		));

		$education_options = $this->get_unique_meta_values('education');
		$categories = $this->get_unique_meta_values('category');
		$salary_range = $this->get_salary_range();
		$jobtype_options = $this->get_unique_meta_values('jobtype');

		// Ensure filtering works for multiple job types
		add_filter('posts_where', function ($where, $query) {
			global $wpdb;
			if (!is_admin() && $query->get('post_type') === 'vacancy' && isset($_GET['jobtype'])) {
				$jobtype = sanitize_text_field($_GET['jobtype']);
				$where .= $wpdb->prepare(" AND EXISTS (SELECT 1 FROM {$wpdb->postmeta} pm WHERE pm.post_id = {$wpdb->posts}.ID AND pm.meta_key = 'jobtype' AND pm.meta_value LIKE %s)", '%' . $wpdb->esc_like($jobtype) . '%');
			}
			return $where;
		}, 10, 2);

		$search_components = get_option('recruit_connect_search_components', array());

		ob_start();
		include RECRUIT_CONNECT_PLUGIN_DIR . 'templates/vacancy-overview.php';
		return ob_get_clean();
	}


	private function get_unique_meta_values($meta_key) {
		global $wpdb;
		error_log('Recruit Connect WP - get_unique_meta_values for meta_key: ' . $meta_key);

		$query = $wpdb->prepare(
			"SELECT DISTINCT meta_value FROM {$wpdb->postmeta} pm
        JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = %s
        AND p.post_type = 'vacancy'
        AND p.post_status = 'publish'
        AND pm.meta_value != ''",
			$meta_key
		);

		$results = $wpdb->get_col($query);

		// Splits values by comma and remove duplicates
		$unique_values = array();
		foreach ($results as $result) {
			$values = array_map('trim', explode(',', $result));
			foreach ($values as $value) {
				if (!in_array($value, $unique_values)) {
					$unique_values[] = $value;
				}
			}
		}

		sort($unique_values); // Sort values alphabetically
		error_log('Recruit Connect WP - get_unique_meta_values result: ' . print_r($unique_values, true));

		return $unique_values;
	}

	private function get_salary_range() {
		global $wpdb;
		$query = "SELECT
			MIN(CAST(pm_min.meta_value AS UNSIGNED)) as min_salary,
			MAX(CAST(pm_max.meta_value AS UNSIGNED)) as max_salary
		FROM {$wpdb->posts} p
		LEFT JOIN {$wpdb->postmeta} pm_min ON p.ID = pm_min.post_id AND pm_min.meta_key = 'salary_minimum'
		LEFT JOIN {$wpdb->postmeta} pm_max ON p.ID = pm_max.post_id AND pm_max.meta_key = 'salary_maximum'
		WHERE p.post_type = 'vacancy'
		  AND p.post_status = 'publish'
		  AND (pm_min.meta_value IS NOT NULL AND pm_min.meta_value != '')
		  AND (pm_max.meta_value IS NOT NULL AND pm_max.meta_value != '')
		  AND pm_min.meta_value REGEXP '^[0-9]+$'
		  AND pm_max.meta_value REGEXP '^[0-9]+$'";


		$result = $wpdb->get_row($query);

		return array(
			'min' => (int)($result->min_salary ?? 0),
			'max' => (int)($result->max_salary ?? 100000)
		);
	}

	public function render_vacancies_list($atts) {
		$atts = shortcode_atts(array(
			'limit' => 10,
			'category' => '',
			'education' => '',
			'jobtype' => '',
			'layout' => 'grid' // grid or list
		), $atts);

		// Query vacancies
		$args = array(
			'post_type' => 'vacancy',
			'posts_per_page' => $atts['limit'],
			'meta_query' => array()
		);

		if (!empty($atts['category'])) {
			$args['meta_query'][] = array(
				'key' => 'category', // Correct ACF field name
				'value' => $atts['category']
			);
		}

		if (!empty($atts['education'])) {
			$args['meta_query'][] = array(
				'key' => 'education', // Correct ACF field name
				'value' => $atts['education']
			);
		}

		if (!empty($atts['jobtype'])) {
			$args['meta_query'][] = array(
				'key' => 'jobtype', // Correct ACF field name
				'value' => $atts['jobtype']
			);
		}

		$query = new \WP_Query($args);

		ob_start();
		include RECRUIT_CONNECT_PLUGIN_DIR . 'templates/vacancies-' . $atts['layout'] . '.php';
		return ob_get_clean();
	}

	public function render_search_form($atts) {
		$atts = shortcode_atts(array(
			'show_category' => true,
			'show_education' => true,
			'show_jobtype' => true,
			'show_salary' => true
		), $atts);

		ob_start();
		include RECRUIT_CONNECT_PLUGIN_DIR . 'templates/search-form.php';
		return ob_get_clean();
	}

	public function render_single_vacancy($atts) {
		$atts = shortcode_atts(array(
			'id' => get_query_var('vacancy_id', 0)
		), $atts);

		if (empty($atts['id'])) {
			return '';
		}

		$vacancy = get_post($atts['id']);
		if (!$vacancy || $vacancy->post_type !== 'vacancy') {
			return '';
		}

		ob_start();
		include RECRUIT_CONNECT_PLUGIN_DIR . 'templates/single-vacancy.php';
		return ob_get_clean();
	}
}
