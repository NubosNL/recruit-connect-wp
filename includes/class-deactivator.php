<?php
namespace RecruitConnect;

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    RecruitConnect
 * @subpackage RecruitConnect/includes
 */
class Deactivator {

	/**
	 * Deactivate the plugin.
	 *
	 * Clear scheduled hooks and clean up plugin data if necessary.
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {
		// Clear scheduled import hook
		wp_clear_scheduled_hook('recruit_connect_xml_import');

		// Remove any transients we've created
		delete_transient('recruit_connect_last_import');

		// Log deactivation
		if (class_exists('RecruitConnect\Logger')) {
			$logger = new Logger();
			$logger->log('Plugin deactivated');
		}

		// Optionally, you might want to keep this commented out until you're sure
		// you want to remove all data upon deactivation
		/*
		// Remove plugin options
		$options = [
			'recruit_connect_xml_url',
			'recruit_connect_application_url',
			'recruit_connect_detail_param',
			'recruit_connect_enable_detail',
			'recruit_connect_search_components',
			'recruit_connect_thank_you_message',
			'recruit_connect_required_fields',
			'recruit_connect_sync_frequency',
			'recruit_connect_detail_fields',
			'recruit_connect_fields_order',
			'recruit_connect_logs'
		];

		foreach ($options as $option) {
			delete_option($option);
		}

		// Remove custom post type posts
		$posts = get_posts([
			'post_type' => 'vacancy',
			'numberposts' => -1,
			'post_status' => 'any'
		]);

		foreach ($posts as $post) {
			wp_delete_post($post->ID, true);
		}
		*/

		// Flush rewrite rules
		flush_rewrite_rules();
	}
}
