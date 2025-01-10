<?php
namespace RecruitConnect;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    RecruitConnect
 * @subpackage RecruitConnect/includes
 */
class Activator {

	/**
	 * Activate the plugin.
	 *
	 * Set up necessary database tables, options, and scheduled tasks.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		// Check WordPress version
		if (version_compare(get_bloginfo('version'), '6.0', '<')) {
			wp_die(
				__('This plugin requires WordPress version 6.0 or higher.', 'recruit-connect-wp'),
				__('Plugin Activation Error', 'recruit-connect-wp'),
				array('back_link' => true)
			);
		}

		// Set default options
		self::set_default_options();

		// Register custom post type to flush rewrite rules
		self::register_vacancy_post_type();

		// Schedule the import task (default: daily)
		if (!wp_next_scheduled('recruit_connect_xml_import')) {
			wp_schedule_event(time(), 'daily', 'recruit_connect_xml_import');
		}

		// Flush rewrite rules
		flush_rewrite_rules();

		// Log activation
		if (class_exists('RecruitConnect\Logger')) {
			$logger = new Logger();
			$logger->log('Plugin activated');
		}

		// Ensure required fields option starts as an array
		if (false === get_option('recruit_connect_required_fields')) {
			add_option('recruit_connect_required_fields', array('name', 'email', 'cv'));
		}

		// Set default detail page fields if not set
		if (false === get_option('recruit_connect_detail_fields')) {
			add_option('recruit_connect_detail_fields', array(
				'description',
				'company',
				'location',
				'salary',
				'education',
				'experience',
				'jobtype',
				'recruiter'
			));
		}

		if (false === get_option('recruit_connect_fields_order')) {
			add_option('recruit_connect_fields_order', array());
		}
	}

	/**
	 * Set default options for the plugin
	 */
	private static function set_default_options() {
		$default_options = array(
			'recruit_connect_xml_url' => '',
			'recruit_connect_application_url' => '',
			'recruit_connect_detail_param' => 'vacancy_id',
			'recruit_connect_enable_detail' => '1',
			'recruit_connect_search_components' => array('category', 'education', 'jobtype', 'salary'),
			'recruit_connect_thank_you_message' => __('Thank you for your application!', 'recruit-connect-wp'),
			'recruit_connect_required_fields' => array('name', 'email', 'cv'),
			'recruit_connect_sync_frequency' => 'daily',
			'recruit_connect_detail_fields' => array(
				'description',
				'company',
				'location',
				'salary',
				'education',
				'experience',
				'jobtype',
				'recruiter'
			),
			'recruit_connect_fields_order' => array(
				'description',
				'company',
				'location',
				'salary',
				'education',
				'experience',
				'jobtype',
				'recruiter'
			)
		);

		foreach ($default_options as $option_name => $option_value) {
			if (get_option($option_name) === false) {
				add_option($option_name, $option_value);
			}
		}
	}

	/**
	 * Register the vacancy post type
	 */
	private static function register_vacancy_post_type() {
		register_post_type('vacancy', array(
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => true,
			'rewrite' => array('slug' => 'vacancies'),
			'capability_type' => 'post',
			'has_archive' => true,
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array('title', 'editor'),
			'menu_icon' => 'dashicons-businessman'
		));
	}
}
