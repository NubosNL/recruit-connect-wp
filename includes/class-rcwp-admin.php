<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.nubos.nl/en
 * @since      1.0.0
 *
 * @package    Recruit_Connect_WP
 * @subpackage Recruit_Connect_WP/admin
 */

class RCWP_Admin {
	/**
	 * The logger instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      RCWP_Logger    $logger    The logger instance.
	 */
	private $logger;

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
	 * @param    RCWP_Logger    $logger       The logger instance.
	 */
	public function __construct($logger) {
		$this->logger = $logger;
		$this->plugin_name = 'recruit-connect-wp';
		$this->version = RCWP_VERSION;

		add_action('admin_menu', array($this, 'add_admin_menu'));
		add_action('admin_init', array($this, 'register_plugin_settings'));
	}

	/**
	 * Register the admin menu items.
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu() {
		// Main menu
		add_menu_page(
			__('Recruit Connect', 'recruit-connect-wp'),
			__('Recruit Connect', 'recruit-connect-wp'),
			'manage_options',
			'recruit-connect-wp',
			array($this, 'display_plugin_dashboard'),
			'dashicons-businessman',
			30
		);

		// Submenus
		add_submenu_page(
			'recruit-connect-wp',
			__('Dashboard', 'recruit-connect-wp'),
			__('Dashboard', 'recruit-connect-wp'),
			'manage_options',
			'recruit-connect-wp',
			array($this, 'display_plugin_dashboard')
		);

		add_submenu_page(
			'recruit-connect-wp',
			__('Settings', 'recruit-connect-wp'),
			__('Settings', 'recruit-connect-wp'),
			'manage_options',
			'recruit-connect-wp-settings',
			array($this, 'display_plugin_settings')
		);

		add_submenu_page(
			'recruit-connect-wp',
			__('Logs', 'recruit-connect-wp'),
			__('Logs', 'recruit-connect-wp'),
			'manage_options',
			'recruit-connect-wp-logs',
			array($this, 'display_plugin_logs')
		);
	}

	/**
	 * Register all plugin settings
	 *
	 * @since    1.0.0
	 */
	public function register_plugin_settings() {
		// General Settings
		register_setting(
			'rcwp_general_settings', // Option group
			'rcwp_xml_url'          // Option name
		);
		register_setting(
			'rcwp_general_settings',
			'rcwp_application_url'
		);
		register_setting(
			'rcwp_general_settings',
			'rcwp_vacancy_url_parameter'
		);
		register_setting(
			'rcwp_general_settings',
			'rcwp_enable_detail_page'
		);
		register_setting(
			'rcwp_general_settings',
			'rcwp_search_components'
		);

		// Application Form Settings
		register_setting(
			'rcwp_application_settings',
			'rcwp_thank_you_message'
		);
		register_setting(
			'rcwp_application_settings',
			'rcwp_required_fields'
		);

		// Sync Settings
		register_setting(
			'rcwp_sync_settings',
			'rcwp_sync_frequency'
		);

		// Add settings sections
		add_settings_section(
			'rcwp_general_section',
			__('General Settings', 'recruit-connect-wp'),
			array($this, 'render_general_section'),
			'rcwp_general_settings'
		);

		add_settings_section(
			'rcwp_application_section',
			__('Application Form Settings', 'recruit-connect-wp'),
			array($this, 'render_application_section'),
			'rcwp_application_settings'
		);

		add_settings_section(
			'rcwp_sync_section',
			__('Synchronization Settings', 'recruit-connect-wp'),
			array($this, 'render_sync_section'),
			'rcwp_sync_settings'
		);

		// Add settings fields
		$this->add_settings_fields();
	}

	/**
	 * Add settings fields
	 *
	 * @since    1.0.0
	 */
	private function add_settings_fields() {
		// General Settings Fields
		add_settings_field(
			'rcwp_xml_url',
			__('XML Feed URL', 'recruit-connect-wp'),
			array($this, 'render_text_field'),
			'rcwp_general_settings',
			'rcwp_general_section',
			array('name' => 'rcwp_xml_url')
		);

		add_settings_field(
			'rcwp_application_url',
			__('Application Handler URL', 'recruit-connect-wp'),
			array($this, 'render_text_field'),
			'rcwp_general_settings',
			'rcwp_general_section',
			array('name' => 'rcwp_application_url')
		);

		// Add more fields as needed
	}

	/**
	 * Display admin pages
	 */
	public function display_plugin_dashboard() {
		if (!current_user_can('manage_options')) {
			return;
		}
		require_once RCWP_PLUGIN_DIR . 'admin/views/dashboard.php';
	}

	public function display_plugin_settings() {
		if (!current_user_can('manage_options')) {
			return;
		}
		require_once RCWP_PLUGIN_DIR . 'admin/views/settings.php';
	}

	public function display_plugin_logs() {
		if (!current_user_can('manage_options')) {
			return;
		}
		require_once RCWP_PLUGIN_DIR . 'admin/views/logs.php';
	}

	/**
	 * Section renderers
	 */
	public function render_general_section() {
		echo '<p>' . esc_html__('Configure the general settings for the plugin.', 'recruit-connect-wp') . '</p>';
	}

	public function render_application_section() {
		echo '<p>' . esc_html__('Configure the application form settings.', 'recruit-connect-wp') . '</p>';
	}

	public function render_sync_section() {
		echo '<p>' . esc_html__('Configure synchronization settings for vacancy imports.', 'recruit-connect-wp') . '</p>';
	}

	/**
	 * Field renderers
	 */
	public function render_text_field($args) {
		$name = $args['name'];
		$value = get_option($name);
		echo '<input type="text" class="regular-text" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '">';
	}

	public function render_checkbox_field($args) {
		$name = $args['name'];
		$value = get_option($name);
		echo '<input type="checkbox" name="' . esc_attr($name) . '" value="1" ' . checked(1, $value, false) . '>';
	}

	public function render_select_field($args) {
		$name = $args['name'];
		$options = $args['options'];
		$value = get_option($name);

		echo '<select name="' . esc_attr($name) . '">';
		foreach ($options as $key => $label) {
			echo '<option value="' . esc_attr($key) . '" ' . selected($key, $value, false) . '>';
			echo esc_html($label);
			echo '</option>';
		}
		echo '</select>';
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles($hook) {
		if (strpos($hook, 'recruit-connect-wp') === false) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_name,
			RCWP_PLUGIN_URL . 'admin/css/admin.css',
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
	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts($hook) {
		if (strpos($hook, 'recruit-connect-wp') === false) {
			return;
		}

		// Enqueue jQuery UI and its dependencies
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-sortable');

		wp_enqueue_script(
			$this->plugin_name,
			RCWP_PLUGIN_URL . 'admin/js/admin.js',
			array('jquery', 'jquery-ui-sortable'),  // Add sortable as dependency
			$this->version,
			true
		);

		wp_localize_script($this->plugin_name, 'rcwp_admin', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('rcwp_admin_nonce'),
			'strings' => array(
				'sync_success' => __('Synchronization completed successfully.', 'recruit-connect-wp'),
				'sync_error' => __('Error during synchronization.', 'recruit-connect-wp')
			)
		));
	}
}
