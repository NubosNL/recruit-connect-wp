<?php
namespace RecruitConnect;

class PostType {
	public function __construct() {
		add_action('init', array($this, 'register_vacancy_post_type'));
		add_action('init', array($this, 'register_vacancy_application_post_type'));
		add_action('admin_init', array($this, 'disable_vacancy_editing'));
		add_filter('post_row_actions', array($this, 'modify_vacancy_actions'), 10, 2);
		add_filter('post_row_actions', array($this, 'modify_vacancy_application_actions'), 10, 2);
		// We are removing the custom metaboxes and custom field logic, because we use ACF

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
	 * Register the vacancy application post type
	 */
	public function register_vacancy_application_post_type() {
		$labels = array(
			'name'               => __('Applications', 'recruit-connect-wp'),
			'singular_name'      => __('Application', 'recruit-connect-wp'),
			'menu_name'          => __('Applications', 'recruit-connect-wp'),
			'add_new'            => __('Add New', 'recruit-connect-wp'),
			'add_new_item'       => __('Add New Application', 'recruit-connect-wp'),
			'edit_item'          => __('Edit Application', 'recruit-connect-wp'),
			'new_item'           => __('New Application', 'recruit-connect-wp'),
			'view_item'          => __('View Application', 'recruit-connect-wp'),
			'search_items'       => __('Search Applications', 'recruit-connect-wp'),
			'not_found'          => __('No applications found', 'recruit-connect-wp'),
			'not_found_in_trash' => __('No applications found in trash', 'recruit-connect-wp')
		);

		$args = array(
			'labels'              => $labels,
			'public'              => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_ui'            => true,
			'show_in_menu'       => 'recruit-connect', // Show under Recruit Connect Menu
			'query_var'          => true,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => array('title'),
			'menu_icon'          => 'dashicons-email',
			// We will use a filter to set the submenu position dynamically, instead of menu_position here
		);

		register_post_type('vacancy_application', $args);
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
	 * Modify the actions available for applications in the list view
	 *
	 * @since    1.0.0
	 * @param    array     $actions    An array of row action links.
	 * @param    WP_Post   $post       The post object.
	 * @return   array                 Modified array of row action links
	 */
	public function modify_vacancy_application_actions($actions, $post) {
		if ($post->post_type === 'vacancy_application') {
			// Remove quick edit action
			unset($actions['inline hide-if-no-js']);
		}
		return $actions;
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
