<?php
namespace RecruitConnect;

class ApplicationForm {
	private $logger;
	private $retry_intervals = [5, 15, 30, 60, 120]; // Minutes between retries

	public function __construct() {
		$this->logger = new Logger();

		// Register shortcode
		add_shortcode('recruit_connect_application_form', array($this, 'render_form'));

		// AJAX handlers
		add_action('wp_ajax_recruit_connect_submit_application', array($this, 'handle_submission'));
		add_action('wp_ajax_nopriv_recruit_connect_submit_application', array($this, 'handle_submission'));

		// Schedule retry attempts
		add_action('recruit_connect_retry_external_submission', array($this, 'process_external_submission'), 10, 2);
	}

	public function render_form($atts = array()) {
		global $post;

		// Check if we're on a vacancy post type and get its ID
		$vacancy_id = 0;
		$is_open_application = true;

		if (is_singular('vacancy')) {
			$vacancy_id = $post->ID;
			$is_open_application = false;
		}

		// Enqueue scripts and styles (same as before)
		wp_enqueue_script(
			'recruit-connect-application',
			RECRUIT_CONNECT_PLUGIN_URL . 'public/js/application-form.js',
			array('jquery'),
			RECRUIT_CONNECT_VERSION,
			true
		);

		wp_localize_script('recruit-connect-application', 'recruitConnectApp', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('recruit_connect_application'),
			'strings' => array(
				'required' => __('This field is required', 'recruit-connect-wp'),
				'invalid_email' => __('Please enter a valid email address', 'recruit-connect-wp'),
				'invalid_phone' => __('Please enter a valid phone number', 'recruit-connect-wp'),
				'file_size' => __('File size must be less than 10MB', 'recruit-connect-wp'),
				'file_type' => __('Only PDF, DOC, and DOCX files are allowed', 'recruit-connect-wp')
			)
		));

		// Get required fields from settings
		$required_fields = get_option('recruit_connect_required_fields', array('name', 'email'));

		// Get vacancy details if this is a specific vacancy application
		$vacancy_details = array();
		if ($vacancy_id) {
			$vacancy_details = array(
				'title' => get_the_title($vacancy_id),
				'company' => get_field('company', $vacancy_id), // Use ACF get_field
				'location' => get_field('city', $vacancy_id), // Use ACF get_field
			);
		}

		ob_start();
		include RECRUIT_CONNECT_PLUGIN_DIR . 'templates/application-form.php';
		return ob_get_clean();
	}

	public function handle_submission() {
		check_ajax_referer('recruit_connect_application', 'nonce');

		$required_fields = get_option('recruit_connect_required_fields', array('name', 'email'));
		$errors = array();

		// Validate required fields
		foreach ($required_fields as $field) {
			if (empty($_POST[$field])) {
				$errors[$field] = __('This field is required', 'recruit-connect-wp');
			}
		}

		// Validate email
		if (!empty($_POST['email']) && !is_email($_POST['email'])) {
			$errors['email'] = __('Please enter a valid email address', 'recruit-connect-wp');
		}

		// Handle file upload
		$cv_url = '';
		if (!empty($_FILES['cv'])) {
			$upload = $this->handle_file_upload($_FILES['cv']);
			if (is_wp_error($upload)) {
				$errors['cv'] = $upload->get_error_message();
			} else {
				$cv_url = $upload['url'];
			}
		} elseif (in_array('cv', $required_fields)) {
			$errors['cv'] = __('CV is required', 'recruit-connect-wp');
		}

		if (!empty($errors)) {
			wp_send_json_error(array('errors' => $errors));
			return;
		}

		// Determine if this is an open application
		$vacancy_id = !empty($_POST['vacancy_id']) ? intval($_POST['vacancy_id']) : 0;
		$is_open_application = $vacancy_id === 0;

		// Create application title
		$application_title = $is_open_application
			? sprintf(__('Open Application from %s', 'recruit-connect-wp'), sanitize_text_field($_POST['name']))
			: sprintf(__('Application for %s from %s', 'recruit-connect-wp'),
				get_the_title($vacancy_id),
				sanitize_text_field($_POST['name'])
			);

		// Store application locally
		$application_id = wp_insert_post(array(
			'post_type' => 'vacancy_application',
			'post_status' => 'publish',
			'post_title' => $application_title,
			'meta_input' => array(
				'_application_name' => sanitize_text_field($_POST['name']),
				'_application_email' => sanitize_email($_POST['email']),
				'_application_phone' => sanitize_text_field($_POST['phone']),
				'_application_motivation' => sanitize_textarea_field($_POST['motivation']),
				'_application_cv' => $cv_url,
				'_application_vacancy_id' => $vacancy_id,
				'_application_type' => $is_open_application ? 'open' : 'vacancy',
				'_external_submission_status' => 'pending'

			)
		));

		//Store as ACF data
		if ( !is_wp_error($application_id) ){
			update_field('application_name', sanitize_text_field($_POST['name']), $application_id);
			update_field('application_email', sanitize_email($_POST['email']), $application_id);
			update_field('application_phone', sanitize_text_field($_POST['phone']), $application_id);
			update_field('application_motivation', sanitize_textarea_field($_POST['motivation']), $application_id);
			update_field('application_cv', $cv_url, $application_id);
			update_field('application_vacancy_id', $vacancy_id, $application_id);
			update_field('application_type', $is_open_application ? 'open' : 'vacancy', $application_id);
			update_field('external_submission_status', 'pending', $application_id);
		}


		if (is_wp_error($application_id)) {
			wp_send_json_error(array(
				'message' => __('Failed to store application', 'recruit-connect-wp')
			));
			return;
		}

		// Schedule immediate external submission
		wp_schedule_single_event(time(), 'recruit_connect_retry_external_submission', array(
			$application_id,
			0 // Attempt number
		));

		// Send success response with thank you message
		wp_send_json_success(array(
			'message' => get_option(
				'recruit_connect_thank_you_message',
				__('Thank you for your application!', 'recruit-connect-wp')
			)
		));
	}

	private function handle_file_upload($file) {
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');

		// Check file size (10MB max)
		if ($file['size'] > 10 * 1024 * 1024) {
			return new \WP_Error('size_error', __('File size must be less than 10MB', 'recruit-connect-wp'));
		}

		// Check file type
		$allowed_types = array('pdf', 'doc', 'docx');
		$file_type = wp_check_filetype($file['name']);
		if (!in_array(strtolower($file_type['ext']), $allowed_types)) {
			return new \WP_Error('type_error', __('Only PDF, DOC, and DOCX files are allowed', 'recruit-connect-wp'));
		}

		return wp_handle_upload($file, array('test_form' => false));
	}

	public function process_external_submission($application_id, $attempt) {
		$application = get_post($application_id);
		if (!$application || $application->post_type !== 'vacancy_application') {
			return;
		}

		$destination_url = get_option('recruit_connect_application_url');
		if (empty($destination_url)) {
			$this->log_error($application_id, 'No destination URL configured');
			return;
		}

		// Prepare application data
		$data = array(
			'name' => get_field('application_name', $application_id), // Use ACF get_field
			'email' => get_field('application_email', $application_id),  // Use ACF get_field
			'phone' => get_field('application_phone', $application_id),  // Use ACF get_field
			'motivation' => get_field('application_motivation', $application_id),  // Use ACF get_field
			'cv_url' => get_field('application_cv', $application_id),  // Use ACF get_field
			'vacancy_id' => get_field('application_vacancy_id', $application_id) // Use ACF get_field
		);

		// Send to external endpoint
		$response = wp_remote_post($destination_url, array(
			'body' => $data,
			'timeout' => 30
		));

		if (is_wp_error($response)) {
			$this->handle_submission_error($application_id, $attempt, $response->get_error_message());
			return;
		}

		$response_code = wp_remote_retrieve_response_code($response);
		if ($response_code !== 200) {
			$this->handle_submission_error(
				$application_id,
				$attempt,
				"HTTP Error: {$response_code}"
			);
			return;
		}

		// Success
		update_field('external_submission_status', 'completed', $application_id); // Use ACF update_field
		$this->logger->log("Application {$application_id} successfully submitted to external endpoint");
	}

	private function handle_submission_error($application_id, $attempt, $error_message) {
		$this->logger->log("Application {$application_id} submission failed: {$error_message}");
		update_field('external_submission_status', 'failed', $application_id); // Use ACF update_field
		update_field('last_error', $error_message, $application_id);  // Use ACF update_field

		// Schedule retry if attempts remain
		if ($attempt < count($this->retry_intervals)) {
			wp_schedule_single_event(
				time() + ($this->retry_intervals[$attempt] * MINUTE_IN_SECONDS),
				'recruit_connect_retry_external_submission',
				array($application_id, $attempt + 1)
			);
		} else {
			// Send alert email to admin
			$this->send_alert_email($application_id, $error_message);
		}
	}

	private function send_alert_email($application_id, $error_message) {
		$admin_email = get_option('admin_email');
		$subject = sprintf(
			__('[Alert] Failed to submit application #%d to external endpoint', 'recruit-connect-wp'),
			$application_id
		);

		$message = sprintf(
			__("Application #%d failed to submit to the external endpoint after multiple attempts.\n\nLast error: %s", 'recruit-connect-wp'),
			$application_id,
			$error_message
		);

		wp_mail($admin_email, $subject, $message);
	}
}
