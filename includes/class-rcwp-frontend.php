<?php
/**
 * Frontend functionality
 *
 * @link       https://www.nubos.nl/en
 * @since      1.0.0
 *
 * @package    Recruit_Connect_WP
 * @subpackage Recruit_Connect_WP/includes
 */

class RCWP_Frontend {
	/**
	 * Initialize the class
	 */
	public function __construct() {
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
		add_shortcode('recruit_connect_vacancies_overview', array($this, 'render_vacancies_overview'));
		add_shortcode('recruit_connect_application_form', array($this, 'render_application_form'));
	}

	/**
	 * Enqueue frontend scripts
	 */
	public function enqueue_scripts() {
		// Vacancy search functionality
		wp_enqueue_script(
			'rcwp-vacancy-search',
			RCWP_PLUGIN_URL . 'dist/js/vacancy-search.min.js',
			array('jquery', 'jquery-ui-slider'),
			RCWP_VERSION,
			true
		);

		// Application form functionality
		wp_enqueue_script(
			'rcwp-application-form',
			RCWP_PLUGIN_URL . 'dist/js/application-form.min.js',
			array('jquery'),
			RCWP_VERSION,
			true
		);

		// Localize scripts
		wp_localize_script('rcwp-vacancy-search', 'rcwp_vars', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('rcwp_search_nonce'),
			'no_results' => __('No vacancies found matching your criteria.', 'recruit-connect-wp')
		));

		wp_localize_script('rcwp-application-form', 'rcwp_vars', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('rcwp_application_nonce'),
			'error_message' => __('An error occurred. Please try again.', 'recruit-connect-wp'),
			'required_field_message' => __('This field is required.', 'recruit-connect-wp'),
			'invalid_email_message' => __('Please enter a valid email address.', 'recruit-connect-wp'),
			'file_too_large_message' => __('File is too large. Maximum size is 5MB.', 'recruit-connect-wp'),
			'invalid_file_type_message' => __('Invalid file type. Please upload PDF or Word document.', 'recruit-connect-wp'),
			'submitting_message' => __('Submitting...', 'recruit-connect-wp'),
			'submit_button_text' => __('Submit Application', 'recruit-connect-wp')
		));
	}

	/**
	 * Enqueue frontend styles
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			'rcwp-public',
			RCWP_PLUGIN_URL . 'dist/css/public.min.css',
			array(),
			RCWP_VERSION
		);

		// Load Font Awesome for icons
		wp_enqueue_style(
			'font-awesome',
			'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
			array(),
			'5.15.4'
		);
	}

	/**
	 * Render vacancies overview shortcode
	 */
	public function render_vacancies_overview($atts) {
		$attributes = shortcode_atts(array(
			'limit' => 10
		), $atts);

		ob_start();
		include RCWP_PLUGIN_DIR . 'templates/blocks/vacancy-overview.php';
		return ob_get_clean();
	}

	/**
	 * Render application form shortcode
	 */
	public function render_application_form($atts) {
		$attributes = shortcode_atts(array(
			'vacancy_id' => get_the_ID()
		), $atts);

		ob_start();
		include RCWP_PLUGIN_DIR . 'templates/blocks/application-form.php';
		return ob_get_clean();
	}
}
