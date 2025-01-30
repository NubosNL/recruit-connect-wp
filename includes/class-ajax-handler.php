<?php
namespace RecruitConnect;

use Exception;

class AjaxHandler {
	private $importer;
	private $logger;
	private $plugin_instance;

	public function __construct(Plugin $plugin_instance) {
		require_once RECRUIT_CONNECT_PLUGIN_DIR . 'includes/class-logger.php';
		require_once RECRUIT_CONNECT_PLUGIN_DIR . 'includes/class-xml-importer.php';

		$this->logger = new Logger();
		$this->importer = new XMLImporter();
		$this->plugin_instance = $plugin_instance;

		// Add AJAX actions
		add_action('wp_ajax_recruit_connect_sync_now', array($this, 'handle_sync_now'));
		add_action('wp_ajax_recruit_connect_send_support', array($this, 'handle_support_form'));
		add_action('wp_ajax_recruit_connect_acf_setup', array($this, 'handle_acf_setup'));
		add_action('wp_ajax_recruit_connect_load_vacancies', array($this, 'handle_load_vacancies'));
		add_action('wp_ajax_nopriv_recruit_connect_load_vacancies', array($this, 'handle_load_vacancies'));
	}

	public function handle_load_vacancies() {
		check_ajax_referer('recruit_connect_overview', 'nonce');

		$filters = isset($_POST['filters']) ? $_POST['filters'] : array();
		$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
		$posts_per_page = 10;

		$args = array(
			'post_type' => 'vacancy',
			'posts_per_page' => $posts_per_page,
			'paged' => $page,
			'post_status' => 'publish',
			'meta_query' => array(),
			'orderby' => 'date',
			'order' => 'DESC'
		);

		// Apply filters
		if (!empty($filters['search'])) {
			$args['s'] = sanitize_text_field($filters['search']);
		}

		if (!empty($filters['educationFilter'])) {
			$args['meta_query'][] = array(
				'key' => 'education',
				'value' => sanitize_text_field($filters['educationFilter'])
			);
		}

		if (!empty($filters['categoryFilter'])) {
			$args['meta_query'][] = array(
				'key' => 'category',
				'value' => sanitize_text_field($filters['categoryFilter'])
			);
		}

		if (!empty($filters['jobtypeFilter'])) {
			$jobtypes = array_map('trim', explode(',', sanitize_text_field($filters['jobtypeFilter'])));
			$meta_query_jobtype = array('relation' => 'OR');

			foreach ($jobtypes as $jobtype) {
				$meta_query_jobtype[] = array(
					'key' => 'jobtype',
					'value' => $jobtype,
					'compare' => 'LIKE'
				);
			}

			$args['meta_query'][] = $meta_query_jobtype;
		}

		$query = new \WP_Query($args); // Add backslash here
		$vacancies = array();

		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
				$vacancies[] = array(
					'title' => get_the_title(),
					'link' => get_permalink(),
					'excerpt' => wp_trim_words(get_the_content(), 20),
					'date' => get_the_date(),
					'location' => get_field('city', get_the_ID()), // ACF field
					'job_type' => get_field('jobtype', get_the_ID()), // ACF field
					'salary' => get_field('salary_minimum', get_the_ID()) // ACF field - using min salary for display
				);
			}
		}

		wp_reset_postdata();

		wp_send_json_success(array(
			'vacancies' => $vacancies,
			'total' => $query->found_posts,
			'total_pages' => $query->max_num_pages
		));
	}

	/**
	 * Handle support form submission
	 */
	public function handle_support_form() {
		// Verify nonce
		check_ajax_referer('recruit_connect_support', 'nonce');

		// Check permissions
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => __('Unauthorized access', 'recruit-connect-wp')));
		}

		// Get and sanitize form data
		$name = sanitize_text_field($_POST['name']);
		$email = sanitize_email($_POST['email']);
		$subject = sanitize_text_field($_POST['subject']);
		$message = sanitize_textarea_field($_POST['message']);

		// Validate required fields
		if (empty($name) || empty($email) || empty($subject) || empty($message)) {
			wp_send_json_error(array('message' => __('All fields are required', 'recruit-connect-wp')));
		}

		// Prepare email content
		$to = 'support@nubos.nl';
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . $name . ' <' . $email . '>',
			'Reply-To: ' . $email
		);

		$email_content = sprintf(
			'<p><strong>%s</strong></p>
            <p><strong>%s:</strong> %s</p>
            <p><strong>%s:</strong> %s</p>
            <p><strong>%s:</strong> %s</p>
            <p><strong>%s:</strong></p>
            <p>%s</p>
            <hr>
            <p><em>%s</em></p>
            <p><em>%s: %s</em></p>
            <p><em>%s: %s</em></p>',
			__('New Support Request from Recruit Connect WP Plugin', 'recruit-connect-wp'),
			__('Name', 'recruit-connect-wp'),
			esc_html($name),
			__('Email', 'recruit-connect-wp'),
			esc_html($email),
			__('Subject', 'recruit-connect-wp'),
			esc_html($subject),
			__('Message', 'recruit-connect-wp'),
			nl2br(esc_html($message)),
			__('Site Information', 'recruit-connect-wp'),
			__('Site URL', 'recruit-connect-wp'),
			get_site_url(),
			__('WordPress Version', 'recruit-connect-wp'),
			get_bloginfo('version')
		);

		// Send email using wp_mail()
		$mail_sent = wp_mail(
			$to,
			'Support Request: ' . $subject,
			$email_content,
			$headers
		);

		if ($mail_sent) {
			wp_send_json_success(array(
				'message' => __('Support request sent successfully', 'recruit-connect-wp')
			));
		} else {
			// Log the error if available
			$wp_error = error_get_last();
			error_log('Recruit Connect WP - Support Email Error: ' . print_r($wp_error, true));

			wp_send_json_error(array(
				'message' => __('Failed to send support request. Please try again or contact us directly.', 'recruit-connect-wp')
			));
		}
	}

	public function handle_application() {
		check_ajax_referer('recruit_connect_application', 'nonce');

		$vacancy_id = sanitize_text_field($_POST['vacancy_id']);
		$name = sanitize_text_field($_POST['name']);
		$email = sanitize_email($_POST['email']);
		$phone = sanitize_text_field($_POST['phone']);
		$message = sanitize_textarea_field($_POST['message']);

		// Validate required fields
		$required_fields = get_option('recruit_connect_required_fields', array());
		foreach ($required_fields as $field) {
			if (empty($_POST[$field])) {
				wp_send_json_error(array(
					'message' => sprintf(
						__('Field %s is required', 'recruit-connect-wp'),
						$field
					)
				));
			}
		}

		// Handle file upload
		$cv_url = '';
		if (!empty($_FILES['cv'])) {
			$cv_url = $this->handle_file_upload($_FILES['cv']);
			if (is_wp_error($cv_url)) {
				wp_send_json_error(array(
					'message' => $cv_url->get_error_message()
				));
			}
		}

		// Store application
		$application_id = wp_insert_post(array(
			'post_type' => 'vacancy_application',
			'post_status' => 'publish',
			'post_title' => sprintf(
				__('Application from %s for %s', 'recruit-connect-wp'),
				$name,
				get_the_title($vacancy_id)
			),
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
		}

		// Send notification email
		$this->send_application_notification($application_id);

		wp_send_json_success(array(
			'message' => get_option(
				'recruit_connect_thank_you_message',
				__('Thank you for your application!', 'recruit-connect-wp')
			)
		));
	}

	public function handle_acf_setup() {
		error_log('Recruit Connect WP - handle_acf_setup AJAX call started');

		// Verify nonce
		check_ajax_referer('recruit_connect_nonce', 'nonce');

		// Check for permissions
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => 'Unauthorized access'));
			error_log('Recruit Connect WP - handle_acf_setup: Unauthorized access');
			return;
		}

		// Ensure ACF is active (still check)
		if (!function_exists('acf_add_local_field_group')) {
			wp_send_json_error(array('message' => 'ACF plugin function acf_add_local_field_group is not available. Is ACF active and correctly loaded?'));
			error_log('Recruit Connect WP - handle_acf_setup: ACF not available');
			return;
		}

		error_log('Recruit Connect WP - handle_acf_setup: ACF checks passed, triggering field registration');

		try {
			// Call field registration function - it will now register on admin_init
			$this->plugin_instance->register_acf_fields_on_admin_init(); // Call the new function
			wp_send_json_success(array('message' => 'ACF fields registration triggered. Please reload the page and check Custom Fields.')); // Updated message
			error_log('Recruit Connect WP - handle_acf_setup: Registration triggered, success response sent.');

		} catch (Exception $e) {
			error_log('Recruit Connect WP - handle_acf_setup: Exception during ACF setup: ' . $e->getMessage());
			wp_send_json_error(array('message' => 'Error: ' . $e->getMessage()));
		}

		return true;
	}


	public function handle_sync_now() {
		try {
			check_ajax_referer('recruit_connect_nonce', 'nonce');

			if (!current_user_can('manage_options')) {
				wp_send_json_error(array('message' => 'Unauthorized access'));
				return;
			}

			$result = $this->importer->import();

			// Update the 'recruit_connect_last_import' option
			if ($result) {
				update_option('recruit_connect_last_import', current_time('mysql'));
				wp_send_json_success(array('message' => 'Sync completed successfully'));
			} else {
				wp_send_json_error(array('message' => 'Sync failed. Check logs for details.'));
			}

		} catch (\Exception $e) {
			$this->logger->log('Error during sync: ' . $e->getMessage());
			wp_send_json_error(array(
				'message' => 'Error: ' . $e->getMessage()
			));
		}
	}

	private function create_acf_fields() {
		$field_groups = array(
			array(
				'key' => 'group_recruit_connect_vacancy',
				'title' => __('Vacancy Fields', 'recruit-connect-wp'),
				'fields' => array(
					array(
						'key' => 'field_recruit_connect_vacancy_id',
						'label' => __('Vacancy ID', 'recruit-connect-wp'),
						'name' => 'vacancy_id',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_company',
						'label' => __('Company', 'recruit-connect-wp'),
						'name' => 'company',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_city',
						'label' => __('City', 'recruit-connect-wp'),
						'name' => 'city',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_createdat',
						'label' => __('Created At', 'recruit-connect-wp'),
						'name' => 'createdat',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_streetaddress',
						'label' => __('Street Address', 'recruit-connect-wp'),
						'name' => 'streetaddress',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_postalcode',
						'label' => __('Postal Code', 'recruit-connect-wp'),
						'name' => 'postalcode',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_state',
						'label' => __('State', 'recruit-connect-wp'),
						'name' => 'state',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_country',
						'label' => __('Country', 'recruit-connect-wp'),
						'name' => 'country',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_salary',
						'label' => __('Salary', 'recruit-connect-wp'),
						'name' => 'salary',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_education',
						'label' => __('Education', 'recruit-connect-wp'),
						'name' => 'education',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_jobtype',
						'label' => __('Job Type', 'recruit-connect-wp'),
						'name' => 'jobtype',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_experience',
						'label' => __('Experience', 'recruit-connect-wp'),
						'name' => 'experience',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_remotetype',
						'label' => __('Remote Type', 'recruit-connect-wp'),
						'name' => 'remotetype',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_recruitername',
						'label' => __('Recruiter Name', 'recruit-connect-wp'),
						'name' => 'recruitername',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_recruiteremail',
						'label' => __('Recruiter Email', 'recruit-connect-wp'),
						'name' => 'recruiteremail',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_recruiterimage',
						'label' => __('Recruiter Image', 'recruit-connect-wp'),
						'name' => 'recruiterimage',
						'type' => 'image',
					),
				),
				'location' => array(
					array(
						array(
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'vacancy',
						),
					),
				),
				'menu_order' => 0,
				'position' => 'normal',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
				'active' => true,
				'show_in_rest' => 0
			),
			array(
				'key' => 'group_recruit_connect_application',
				'title' => __('Application Fields', 'recruit-connect-wp'),
				'fields' => array(
					array(
						'key' => 'field_recruit_connect_application_vacancy_id',
						'label' => __('Vacancy ID', 'recruit-connect-wp'),
						'name' => 'application_vacancy_id',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_application_name',
						'label' => __('Applicant Name', 'recruit-connect-wp'),
						'name' => 'application_name',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_application_email',
						'label' => __('Applicant Email', 'recruit-connect-wp'),
						'name' => 'application_email',
						'type' => 'email',
					),
					array(
						'key' => 'field_recruit_connect_application_phone',
						'label' => __('Applicant Phone', 'recruit-connect-wp'),
						'name' => 'application_phone',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_application_motivation',
						'label' => __('Applicant Motivation', 'recruit-connect-wp'),
						'name' => 'application_motivation',
						'type' => 'textarea',
					),
					array(
						'key' => 'field_recruit_connect_application_cv',
						'label' => __('Applicant CV', 'recruit-connect-wp'),
						'name' => 'application_cv',
						'type' => 'file',
					),
					array(
						'key' => 'field_recruit_connect_application_type',
						'label' => __('Application Type', 'recruit-connect-wp'),
						'name' => 'application_type',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_external_submission_status',
						'label' => __('External Submission Status', 'recruit-connect-wp'),
						'name' => 'external_submission_status',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_last_error',
						'label' => __('Last Error', 'recruit-connect-wp'),
						'name' => 'last_error',
						'type' => 'text',
					),
				),
				'location' => array(
					array(
						array(
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'vacancy_application',
						),
					),
				),
				'menu_order' => 0,
				'position' => 'normal',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
				'active' => true,
				'show_in_rest' => 0
			),
		);
		foreach ($field_groups as $field_group) {
			acf_add_local_field_group($field_group);
		}
	}
	private function handle_file_upload($file) {
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');

		$upload = wp_handle_upload($file, array('test_form' => false));

		if (isset($upload['error'])) {
			return new \WP_Error('upload_error', $upload['error']);
		}

		return $upload['url'];
	}

	private function send_application_notification($application_id) {
		$application = get_post($application_id);
		$vacancy_id = get_field('application_vacancy_id', $application_id);
		$recruiter_email = get_field('recruiteremail', $vacancy_id);

		if (empty($recruiter_email)) {
			$recruiter_email = get_option('admin_email');
		}

		$subject = sprintf(
			__('New Application: %s', 'recruit-connect-wp'),
			get_the_title($vacancy_id)
		);

		$message = $this->get_application_email_template($application_id);

		wp_mail($recruiter_email, $subject, $message, array('Content-Type: text/html; charset=UTF-8'));
	}

	private function get_application_email_template($application_id) {
		ob_start();
		include RECRUIT_CONNECT_PLUGIN_DIR . 'templates/email/application-notification.php';
		return ob_get_clean();
	}
}
