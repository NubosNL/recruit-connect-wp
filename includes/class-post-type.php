<?php
namespace RecruitConnect;

class PostType {
	public function __construct() {
		add_action('init', array($this, 'register_vacancy_post_type'));
		add_action('admin_init', array($this, 'disable_vacancy_editing'));
		add_filter('post_row_actions', array($this, 'modify_vacancy_actions'), 10, 2);
		add_filter('single_template', array($this, 'load_vacancy_template'));

		// Add meta boxes
		add_action('add_meta_boxes', array($this, 'add_vacancy_meta_boxes'));
	}

	/**
	 * Add meta boxes for vacancy details
	 */
	public function add_vacancy_meta_boxes() {
		add_meta_box(
			'vacancy_details',
			__('Vacancy Details', 'recruit-connect-wp'),
			array($this, 'render_vacancy_details_meta_box'),
			'vacancy',
			'normal',
			'high'
		);

		add_meta_box(
			'recruiter_details',
			__('Recruiter Details', 'recruit-connect-wp'),
			array($this, 'render_recruiter_details_meta_box'),
			'vacancy',
			'normal',
			'high'
		);
	}

	/**
	 * Render vacancy details meta box
	 */
	public function render_vacancy_details_meta_box($post) {
		$meta_fields = array(
			'_vacancy_id' => __('Vacancy ID', 'recruit-connect-wp'),
			'_vacancy_company' => __('Company', 'recruit-connect-wp'),
			'_vacancy_city' => __('City', 'recruit-connect-wp'),
			'_vacancy_createdat' => __('Created At', 'recruit-connect-wp'),
			'_vacancy_streetaddress' => __('Street Address', 'recruit-connect-wp'),
			'_vacancy_postalcode' => __('Postal Code', 'recruit-connect-wp'),
			'_vacancy_state' => __('State', 'recruit-connect-wp'),
			'_vacancy_country' => __('Country', 'recruit-connect-wp'),
			'_vacancy_salary' => __('Salary', 'recruit-connect-wp'),
			'_vacancy_education' => __('Education', 'recruit-connect-wp'),
			'_vacancy_jobtype' => __('Job Type', 'recruit-connect-wp'),
			'_vacancy_experience' => __('Experience', 'recruit-connect-wp'),
			'_vacancy_remotetype' => __('Remote Type', 'recruit-connect-wp')
		);

		echo '<div class="vacancy-meta-fields">';
		echo '<style>
            .vacancy-meta-fields table { width: 100%; border-collapse: collapse; }
            .vacancy-meta-fields th { text-align: left; width: 200px; padding: 8px; }
            .vacancy-meta-fields td { padding: 8px; }
            .vacancy-meta-fields tr:nth-child(even) { background: #f9f9f9; }
        </style>';

		echo '<table>';
		foreach ($meta_fields as $key => $label) {
			$value = get_post_meta($post->ID, $key, true);
			echo '<tr>';
			echo '<th>' . esc_html($label) . ':</th>';
			echo '<td>' . esc_html($value) . '</td>';
			echo '</tr>';
		}
		echo '</table>';
		echo '</div>';
	}

	/**
	 * Render recruiter details meta box
	 */
	public function render_recruiter_details_meta_box($post) {
		$meta_fields = array(
			'_vacancy_recruitername' => __('Recruiter Name', 'recruit-connect-wp'),
			'_vacancy_recruiteremail' => __('Recruiter Email', 'recruit-connect-wp'),
			'_vacancy_recruiterimage' => __('Recruiter Image', 'recruit-connect-wp')
		);

		echo '<div class="recruiter-meta-fields">';
		echo '<style>
            .recruiter-meta-fields table { width: 100%; border-collapse: collapse; }
            .recruiter-meta-fields th { text-align: left; width: 200px; padding: 8px; }
            .recruiter-meta-fields td { padding: 8px; }
            .recruiter-meta-fields tr:nth-child(even) { background: #f9f9f9; }
            .recruiter-meta-fields img { max-width: 100px; height: auto; border-radius: 50%; }
        </style>';

		echo '<table>';
		foreach ($meta_fields as $key => $label) {
			$value = get_post_meta($post->ID, $key, true);
			echo '<tr>';
			echo '<th>' . esc_html($label) . ':</th>';
			echo '<td>';
			if ($key === '_vacancy_recruiterimage' && !empty($value)) {
				echo '<img src="' . esc_url($value) . '" alt="Recruiter">';
			} else {
				echo esc_html($value);
			}
			echo '</td>';
			echo '</tr>';
		}
		echo '</table>';
		echo '</div>';
	}

	/**
	 * Register the vacancy post type
	 */
	public function register_vacancy_post_type() {
		$labels = array(
			'name'               => __('Vacancies', 'recruit-connect-wp'),
			'singular_name'      => __('Vacancy', 'recruit-connect-wp'),
			'menu_name'          => __('Vacancies', 'recruit-connect-wp'),
			'add_new'            => __('Add New', 'recruit-connect-wp'),
			'add_new_item'       => __('Add New Vacancy', 'recruit-connect-wp'),
			'edit_item'          => __('Edit Vacancy', 'recruit-connect-wp'),
			'new_item'           => __('New Vacancy', 'recruit-connect-wp'),
			'view_item'          => __('View Vacancy', 'recruit-connect-wp'),
			'search_items'       => __('Search Vacancies', 'recruit-connect-wp'),
			'not_found'          => __('No vacancies found', 'recruit-connect-wp'),
			'not_found_in_trash' => __('No vacancies found in trash', 'recruit-connect-wp')
		);

		$args = array(
			'labels'              => $labels,
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array('slug' => 'vacancies'),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 20,
			'supports'           => array('title', 'editor'),
			'menu_icon'          => 'dashicons-businessman'
		);

		register_post_type('vacancy', $args);
	}

	/**
	 * Disable editing capabilities for vacancies
	 *
	 * @since    1.0.0
	 */
	public function disable_vacancy_editing() {
		global $pagenow;

		// Only run on the edit.php page for vacancies
		if ($pagenow === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'vacancy') {
			// Remove "Add New" button
			add_action('admin_head', function() {
				echo '<style type="text/css">
                    .page-title-action { display: none; }
                    .inline-edit-row { display: none !important; }
                </style>';
			});
		}
	}

	/**
	 * Modify the actions available for vacancies in the list view
	 *
	 * @since    1.0.0
	 * @param    array     $actions    An array of row action links.
	 * @param    WP_Post   $post       The post object.
	 * @return   array                 Modified array of row action links
	 */
	public function modify_vacancy_actions($actions, $post) {
		if ($post->post_type === 'vacancy') {
			// Remove edit and quick edit actions
			unset($actions['edit']);
			unset($actions['inline hide-if-no-js']);

			// Optionally remove other actions
			// unset($actions['trash']);
		}
		return $actions;
	}

	public function load_vacancy_template($template) {
		if (is_singular('vacancy')) {
			$custom_template = RECRUIT_CONNECT_PLUGIN_DIR . 'templates/single-vacancy.php';
			if (file_exists($custom_template)) {
				return $custom_template;
			}
		}
		return $template;
	}
}
