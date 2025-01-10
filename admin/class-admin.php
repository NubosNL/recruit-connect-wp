<?php
namespace RecruitConnect;

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    RecruitConnect
 * @subpackage RecruitConnect/admin
 */
class Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $plugin_name    The name of this plugin.
	 * @param    string    $version        The version of this plugin.
	 */
	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;

		// Initialize settings
		$this->settings = new Settings();

		// Add menu items
		add_action('admin_menu', array($this, 'add_plugin_admin_menu'));

		// Add settings link to plugins page
		add_filter('plugin_action_links_' . plugin_basename(RECRUIT_CONNECT_PLUGIN_DIR . 'recruit-connect-wp.php'),
			array($this, 'add_action_links')
		);
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			$this->plugin_name,
			RECRUIT_CONNECT_PLUGIN_URL . 'admin/css/admin.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name,
			RECRUIT_CONNECT_PLUGIN_URL . 'admin/js/admin.js',
			array('jquery'),
			$this->version,
			false
		);

		wp_localize_script($this->plugin_name, 'recruitConnect', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('recruit_connect_nonce')
		));
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links($links) {
		$settings_link = array(
			'<a href="' . admin_url('admin.php?page=recruit-connect-settings') . '">' .
			__('Settings', 'recruit-connect-wp') . '</a>'
		);
		return array_merge($settings_link, $links);
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		// Main menu item
		add_menu_page(
			__('Recruit Connect', 'recruit-connect-wp'),
			__('Recruit Connect', 'recruit-connect-wp'),
			'manage_options',
			'recruit-connect',
			array($this, 'display_plugin_dashboard_page'),
			'dashicons-businessman',
			30
		);

		// Submenus
		add_submenu_page(
			'recruit-connect',
			__('Dashboard', 'recruit-connect-wp'),
			__('Dashboard', 'recruit-connect-wp'),
			'manage_options',
			'recruit-connect',
			array($this, 'display_plugin_dashboard_page')
		);

		add_submenu_page(
			'recruit-connect',
			__('Settings', 'recruit-connect-wp'),
			__('Settings', 'recruit-connect-wp'),
			'manage_options',
			'recruit-connect-settings',
			array($this, 'display_plugin_settings_page')
		);

		add_submenu_page(
			'recruit-connect',
			__('Support', 'recruit-connect-wp'),
			__('Support', 'recruit-connect-wp'),
			'manage_options',
			'recruit-connect-support',
			array($this, 'display_plugin_support_page')
		);

		add_submenu_page(
			'recruit-connect',
			__('About', 'recruit-connect-wp'),
			__('About', 'recruit-connect-wp'),
			'manage_options',
			'recruit-connect-about',
			array($this, 'display_plugin_about_page')
		);
	}

	/**
	 * Get statistics for the dashboard
	 *
	 * @since    1.0.0
	 * @return   array    Array containing dashboard statistics
	 */
	private function get_dashboard_stats() {
		// Initialize default values
		$stats = array(
			'total_vacancies' => 0,
			'total_applications' => 0,
			'last_import' => ''
		);

		// Get vacancy counts safely
		$vacancy_counts = wp_count_posts('vacancy');
		if ($vacancy_counts && isset($vacancy_counts->publish)) {
			$stats['total_vacancies'] = (int)$vacancy_counts->publish;
		}

		// Get application counts safely
		$application_counts = wp_count_posts('vacancy_application');
		if ($application_counts && isset($application_counts->publish)) {
			$stats['total_applications'] = (int)$application_counts->publish;
		}

		// Get last import time
		$last_import = get_option('recruit_connect_last_import');
		if ($last_import) {
			$stats['last_import'] = $last_import;
		}

		return $stats;
	}

	/**
	 * Render the dashboard page
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_dashboard_page() {
		$stats = $this->get_dashboard_stats();
		require_once RECRUIT_CONNECT_PLUGIN_DIR . 'admin/views/dashboard.php';
	}

	/**
	 * Render the settings page
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_settings_page() {
		$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';

		require_once RECRUIT_CONNECT_PLUGIN_DIR . 'admin/views/settings.php';
	}

	/**
	 * Render the support page
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_support_page() {
		require_once RECRUIT_CONNECT_PLUGIN_DIR . 'admin/views/support.php';
	}

	/**
	 * Render the about page
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_about_page() {
		require_once RECRUIT_CONNECT_PLUGIN_DIR . 'admin/views/about.php';
	}
}
