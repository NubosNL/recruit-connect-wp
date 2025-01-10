<?php
namespace RecruitConnect;

/**
 * The admin-specific functionality of the plugin.
 */
class Admin {
	private $plugin_name;
	private $version;

	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;

		// Add menu items
		add_action('admin_menu', array($this, 'add_plugin_admin_menu'));

		// Add settings link to plugins page
		add_filter('plugin_action_links_' . plugin_basename(RECRUIT_CONNECT_PLUGIN_DIR . 'recruit-connect-wp.php'),
			array($this, 'add_action_links')
		);
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
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
	 * Add settings action link to the plugins page.
	 */
	public function add_action_links($links) {
		$settings_link = array(
			'<a href="' . admin_url('admin.php?page=recruit-connect-settings') . '">' . __('Settings', 'recruit-connect-wp') . '</a>',
		);
		return array_merge($settings_link, $links);
	}

	/**
	 * Render the dashboard page
	 */
	public function display_plugin_dashboard_page() {
		include_once RECRUIT_CONNECT_PLUGIN_DIR . 'admin/views/dashboard.php';
	}

	/**
	 * Render the settings page
	 */
	public function display_plugin_settings_page() {
		include_once RECRUIT_CONNECT_PLUGIN_DIR . 'admin/views/settings.php';
	}

	/**
	 * Render the support page
	 */
	public function display_plugin_support_page() {
		include_once RECRUIT_CONNECT_PLUGIN_DIR . 'admin/views/support.php';
	}

	/**
	 * Render the about page
	 */
	public function display_plugin_about_page() {
		include_once RECRUIT_CONNECT_PLUGIN_DIR . 'admin/views/about.php';
	}

	/**
	 * Register the stylesheets for the admin area.
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
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name,
			RECRUIT_CONNECT_PLUGIN_URL . 'admin/js/admin.js',
			array('jquery'),
			$this->version,
			false
		);
	}
}
