<?php
namespace RecruitConnect;

class AjaxHandler {
	private $importer;
	private $logger;

	public function __construct() {
		require_once RECRUIT_CONNECT_PLUGIN_DIR . 'includes/class-logger.php';
		require_once RECRUIT_CONNECT_PLUGIN_DIR . 'includes/class-xml-importer.php';

		$this->logger = new Logger();
		$this->importer = new XMLImporter();

		// Add AJAX actions
		add_action('wp_ajax_recruit_connect_sync_now', array($this, 'handle_sync_now'));
		add_action('wp_ajax_recruit_connect_send_support', array($this, 'handle_support_form'));
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
                '_application_vacancy_id' => $vacancy_id,
                '_application_name' => $name,
                '_application_email' => $email,
                '_application_phone' => $phone,
                '_application_message' => $message,
                '_application_cv' => $cv_url
            )
        ));

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

	public function handle_sync_now() {
		try {
			check_ajax_referer('recruit_connect_nonce', 'nonce');

			if (!current_user_can('manage_options')) {
				wp_send_json_error(array('message' => 'Unauthorized access'));
				return;
			}

			$result = $this->importer->import();

			if ($result) {
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
        $vacancy_id = get_post_meta($application_id, '_application_vacancy_id', true);
        $recruiter_email = get_post_meta($vacancy_id, '_vacancy_recruiteremail', true);

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
