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
		// Register the main settings
		register_setting('rcwp_settings', 'rcwp_settings');

		// General Settings Section
		add_settings_section(
			'rcwp_general_section',
			__('General Settings', 'recruit-connect-wp'),
			array($this, 'render_general_section'),
			'rcwp-settings-general'
		);

		// Application Settings Section
		add_settings_section(
			'rcwp_application_section',
			__('Application Form Settings', 'recruit-connect-wp'),
			array($this, 'render_application_section'),
			'rcwp-settings-application'
		);

		// Sync Settings Section
		add_settings_section(
			'rcwp_sync_section',
			__('Synchronization Settings', 'recruit-connect-wp'),
			array($this, 'render_sync_section'),
			'rcwp-settings-sync'
		);

		// General Settings Fields
		add_settings_field(
			'rcwp_xml_url',
			__('XML Feed URL', 'recruit-connect-wp'),
			array($this, 'render_text_field'),
			'rcwp-settings-general',
			'rcwp_general_section',
			array('name' => 'rcwp_settings[xml_url]')
		);

		add_settings_field(
			'rcwp_application_url',
			__('Application Handler URL', 'recruit-connect-wp'),
			array($this, 'render_text_field'),
			'rcwp-settings-general',
			'rcwp_general_section',
			array('name' => 'rcwp_settings[application_url]')
		);

		// Application Settings Fields
		add_settings_field(
			'rcwp_thank_you_message',
			__('Thank You Message', 'recruit-connect-wp'),
			array($this, 'render_textarea_field'),
			'rcwp-settings-application',
			'rcwp_application_section',
			array('name' => 'rcwp_settings[thank_you_message]')
		);

		add_settings_field(
			'rcwp_required_fields',
			__('Required Fields', 'recruit-connect-wp'),
			array($this, 'render_checkboxes_field'),
			'rcwp-settings-application',
			'rcwp_application_section',
			array(
				'name' => 'rcwp_settings[required_fields]',
				'options' => array(
					'first_name' => __('First Name', 'recruit-connect-wp'),
					'last_name' => __('Last Name', 'recruit-connect-wp'),
					'email' => __('Email', 'recruit-connect-wp'),
					'phone' => __('Phone', 'recruit-connect-wp'),
					'motivation' => __('Motivation', 'recruit-connect-wp'),
					'resume' => __('Resume', 'recruit-connect-wp')
				)
			)
		);

		// Sync Settings Fields
		add_settings_field(
			'rcwp_sync_frequency',
			__('Sync Frequency', 'recruit-connect-wp'),
			array($this, 'render_select_field'),
			'rcwp-settings-sync',
			'rcwp_sync_section',
			array(
				'name' => 'rcwp_settings[sync_frequency]',
				'options' => array(
					'hourly' => __('Hourly', 'recruit-connect-wp'),
					'twicedaily' => __('Twice Daily', 'recruit-connect-wp'),
					'daily' => __('Daily', 'recruit-connect-wp')
				)
			)
		);
	}

	public function render_text_field($args) {
		$options = get_option('rcwp_settings');
		$name = $args['name'];
		$field_key = str_replace('rcwp_settings[', '', str_replace(']', '', $name));
		$value = isset($options[$field_key]) ? $options[$field_key] : '';
		echo '<input type="text" class="regular-text" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '">';
	}

	public function render_checkbox_field($args) {
		$options = get_option('rcwp_settings');
		$name = $args['name'];
		$field_key = str_replace('rcwp_settings[', '', str_replace(']', '', $name));
		$value = isset($options[$field_key]) ? $options[$field_key] : '';
		echo '<input type="checkbox" name="' . esc_attr($name) . '" value="1" ' . checked(1, $value, false) . '>';
	}

	public function render_checkboxes_field($args) {
		$options = get_option('rcwp_settings');
		$name = $args['name'];
		$field_key = str_replace('rcwp_settings[', '', str_replace(']', '', $name));
		$values = isset($options[$field_key]) ? $options[$field_key] : array();

		foreach ($args['options'] as $key => $label) {
			$checked = isset($values[$key]) ? checked(1, $values[$key], false) : '';
			echo '<label style="display:block;margin-bottom:5px;">';
			echo '<input type="checkbox" name="' . esc_attr($name) . '[' . esc_attr($key) . ']" value="1" ' . $checked . '> ';
			echo esc_html($label);
			echo '</label>';
		}
	}

	public function render_select_field($args) {
		$options = get_option('rcwp_settings');
		$name = $args['name'];
		$field_key = str_replace('rcwp_settings[', '', str_replace(']', '', $name));
		$value = isset($options[$field_key]) ? $options[$field_key] : '';

		echo '<select name="' . esc_attr($name) . '">';
		foreach ($args['options'] as $key => $label) {
			echo '<option value="' . esc_attr($key) . '" ' . selected($key, $value, false) . '>';
			echo esc_html($label);
			echo '</option>';
		}
		echo '</select>';
	}

	public function render_textarea_field($args) {
		$options = get_option('rcwp_settings');
		$name = $args['name'];
		$field_key = str_replace('rcwp_settings[', '', str_replace(']', '', $name));
		$value = isset($options[$field_key]) ? $options[$field_key] : '';
		echo '<textarea class="large-text" rows="5" name="' . esc_attr($name) . '">' . esc_textarea($value) . '</textarea>';
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
