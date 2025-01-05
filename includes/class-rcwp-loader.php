<?php
// File: includes/class-rcwp-loader.php

if (!defined('ABSPATH')) {
	exit; // Prevent direct access
}

class RCWP_Loader {

	public static function init() {
		// Register custom post type for vacancies
		RCWP_Vacancy::register_post_type();

		// Initialize admin settings
		RCWP_Admin::init();

		// Initialize frontend components
		RCWP_Frontend::init();

		// Schedule XML sync if not already scheduled
		if (!wp_next_scheduled('rcwp_xml_sync_event')) {
			wp_schedule_event(time(), 'hourly', 'rcwp_xml_sync_event');
		}
	}

	public static function deactivate() {
		// Clear scheduled event on deactivation
		wp_clear_scheduled_hook('rcwp_xml_sync_event');
	}
}

// Hook for deactivation
register_deactivation_hook(__FILE__, ['RCWP_Loader', 'deactivate']);
