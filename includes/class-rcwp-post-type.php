<?php
class RCWP_Post_Type {
	public function register_vacancy_post_type() {  // This is likely the actual method name
		$labels = array(
			'name'               => _x('Vacancies', 'Post type general name', 'recruit-connect-wp'),
			'singular_name'      => _x('Vacancy', 'Post type singular name', 'recruit-connect-wp'),
			'menu_name'         => _x('Vacancies', 'Admin Menu text', 'recruit-connect-wp'),
			'name_admin_bar'     => _x('Vacancy', 'Add New on Toolbar', 'recruit-connect-wp'),
			'add_new'           => _x('Add New', 'vacancy', 'recruit-connect-wp'),
			'add_new_item'      => __('Add New Vacancy', 'recruit-connect-wp'),
			'new_item'          => __('New Vacancy', 'recruit-connect-wp'),
			'edit_item'         => __('Edit Vacancy', 'recruit-connect-wp'),
			'view_item'         => __('View Vacancy', 'recruit-connect-wp'),
			'all_items'         => __('All Vacancies', 'recruit-connect-wp'),
			'search_items'      => __('Search Vacancies', 'recruit-connect-wp'),
			'not_found'         => __('No vacancies found.', 'recruit-connect-wp'),
			'not_found_in_trash'=> __('No vacancies found in Trash.', 'recruit-connect-wp'),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'           => true,
			'show_in_menu'      => true,
			'query_var'         => true,
			'rewrite'           => array('slug' => get_option('rcwp_vacancy_url_parameter', 'vacancy')),
			'capability_type'    => 'post',
			'has_archive'       => true,
			'hierarchical'      => false,
			'menu_position'     => null,
			'supports'          => array('title', 'editor', 'author', 'thumbnail'),
			'show_in_rest'      => true,
		);

		register_post_type('vacancy', $args);
	}

	public function __construct() {
		add_action('init', array($this, 'register_vacancy_post_type'));
	}
}
