<?php
class RCWP_Admin {
	private $logger;
	private $plugin_name;
	private $version;

	public function __construct($logger) {
		$this->logger = $logger;
		$this->plugin_name = 'recruit-connect-wp';
		$this->version = RCWP_VERSION;
	}

	public function add_admin_menu() {
		add_menu_page(
			__('Recruit Connect', 'recruit-connect-wp'),
			__('Recruit Connect', 'recruit-connect-wp'),
			'manage_options',
			'recruit-connect-wp',
			array($this, 'display_plugin_admin_page'),
			'dashicons-businessman',
			30
		);

		// Add submenu pages
		add_submenu_page(
			'recruit-connect-wp',
			__('Dashboard', 'recruit-connect-wp'),
			__('Dashboard', 'recruit-connect-wp'),
			'manage_options',
			'recruit-connect-wp',
			array($this, 'display_plugin_admin_page')
		);

		add_submenu_page(
			'recruit-connect-wp',
			__('Settings', 'recruit-connect-wp'),
			__('Settings', 'recruit-connect-wp'),
			'manage_options',
			'recruit-connect-wp-settings',
			array($this, 'display_plugin_settings_page')
		);
	}

	public function display_plugin_admin_page() {
		// Ensure the file exists before including it
		$template_path = RCWP_PLUGIN_DIR . 'admin/views/dashboard.php';
		if (file_exists($template_path)) {
			include_once $template_path;
		} else {
			$this->logger->error('Admin dashboard template not found: ' . $template_path);
			echo '<div class="wrap"><h1>' . __('Dashboard', 'recruit-connect-wp') . '</h1>';
			echo '<div class="notice notice-error"><p>' . __('Error: Dashboard template not found.', 'recruit-connect-wp') . '</p></div></div>';
		}
	}

	public function display_plugin_settings_page() {
		// Ensure the file exists before including it
		$template_path = RCWP_PLUGIN_DIR . 'admin/views/settings.php';
		if (file_exists($template_path)) {
			include_once $template_path;
		} else {
			$this->logger->error('Admin settings template not found: ' . $template_path);
			echo '<div class="wrap"><h1>' . __('Settings', 'recruit-connect-wp') . '</h1>';
			echo '<div class="notice notice-error"><p>' . __('Error: Settings template not found.', 'recruit-connect-wp') . '</p></div></div>';
		}
	}

	public function enqueue_styles() {
		wp_enqueue_style(
			$this->plugin_name,
			RCWP_PLUGIN_URL . 'admin/css/admin.css',
			array(),
			$this->version,
			'all'
		);
	}

	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name,
			RCWP_PLUGIN_URL . 'admin/js/admin.js',
			array('jquery'),
			$this->version,
			false
		);

		// Add localization for admin scripts
		wp_localize_script($this->plugin_name, 'rcwp_admin', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('rcwp_admin_nonce')
		));
	}
}
