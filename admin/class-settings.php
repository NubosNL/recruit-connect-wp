<?php
namespace RecruitConnect;

class Settings {
	private $option_group = 'recruit_connect_settings';
	private $page = 'recruit-connect-settings';

	public function __construct() {
		add_action('admin_init', array($this, 'register_settings'));
	}

	public function register_settings() {
		// Register all settings at once
		$settings = array(
			'recruit_connect_xml_url' => array(
				'type' => 'string',
				'sanitize_callback' => 'esc_url_raw'
			),
			'recruit_connect_application_url' => array(
				'type' => 'string',
				'sanitize_callback' => 'esc_url_raw'
			),
			'recruit_connect_detail_param' => array(
				'type' => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default' => 'vacancy_id'
			),
			'recruit_connect_enable_detail' => array(
				'type' => 'boolean',
				'sanitize_callback' => array($this, 'sanitize_checkbox'),
				'default' => true
			),
			'recruit_connect_search_components' => array(
				'type' => 'array',
				'sanitize_callback' => array($this, 'sanitize_array'),
				'default' => array()
			),
			'recruit_connect_thank_you_message' => array(
				'type' => 'string',
				'sanitize_callback' => 'wp_kses_post',
				'default' => __('Thank you for your application!', 'recruit-connect-wp')
			),
			'recruit_connect_required_fields' => array(
				'type' => 'array',
				'sanitize_callback' => array($this, 'sanitize_array'),
				'default' => array('name', 'email', 'cv')
			),
			'recruit_connect_sync_frequency' => array(
				'type' => 'string',
				'sanitize_callback' => array($this, 'sanitize_frequency'),
				'default' => 'daily'
			),
			'recruit_connect_detail_fields' => array(
				'type' => 'array',
				'sanitize_callback' => array($this, 'sanitize_array'),
				'default' => array()
			),
			'recruit_connect_fields_order' => array(
				'type' => 'array',
				'sanitize_callback' => array($this, 'sanitize_array'),
				'default' => array()
			)
		);

		foreach ($settings as $option_name => $args) {
			register_setting($this->option_group, $option_name, $args);
		}
	}

	public function sanitize_checkbox($value) {
		return (isset($value) && $value == 1) ? 1 : 0;
	}

	public function sanitize_array($value) {
		if (!is_array($value)) {
			return array();
		}
		return array_map('sanitize_text_field', $value);
	}

	public function sanitize_frequency($value) {
		$allowed = array('hourly', 'twicedaily', 'daily', 'fourhourly');
		return in_array($value, $allowed) ? $value : 'daily';
	}
}
