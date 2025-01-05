<?php
class RCWP_Application_Handler {
    private $logger;
    private $max_retries = 5;
    private $retry_delays = [30, 60, 300, 600, 1800]; // Delays in seconds

    public function __construct($logger) {
        $this->logger = $logger;

        add_action('wp_ajax_rcwp_submit_application', array($this, 'handle_submission'));
        add_action('wp_ajax_nopriv_rcwp_submit_application', array($this, 'handle_submission'));
        add_action('rcwp_retry_application_submission', array($this, 'retry_submission'), 10, 2);
    }

    public function handle_submission() {
        check_ajax_referer('rcwp-frontend-nonce', 'nonce');

        try {
            // Validate submission
            $data = $this->validate_submission($_POST, $_FILES);

            // Handle file upload
            if (!empty($_FILES['resume'])) {
                $data['resume_url'] = $this->handle_file_upload($_FILES['resume']);
            }

            // Store application
            $application_id = $this->store_application($data);

            // Send to remote endpoint
            $remote_result = $this->send_to_remote($data);

            if (is_wp_error($remote_result)) {
                // Schedule retry if remote submission fails
                $this->schedule_retry($application_id, $data);
                throw new Exception($remote_result->get_error_message());
            }

            // Update application status
            $this->update_application_status($application_id, 'submitted');

            // Send confirmation email
            $this->send_confirmation_email($data);

            wp_send_json_success(array(
                'message' => get_option('rcwp_thank_you_message',
                           __('Thank you for your application!', 'recruit-connect-wp'))
            ));

        } catch (Exception $e) {
            $this->logger->log('Application submission error: ' . $e->getMessage(), 'error');
            wp_send_json_error($e->getMessage());
        }
    }

    private function validate_submission($post_data, $files) {
        $required_fields = get_option('rcwp_required_fields', array());
        $errors = array();

        // Validate required fields
        foreach ($required_fields as $field) {
            if (empty($post_data[$field])) {
                $errors[] = sprintf(
                    __('Field %s is required', 'recruit-connect-wp'),
                    $this->get_field_label($field)
                );
            }
        }

        // Validate email format
        if (!empty($post_data['email']) && !is_email($post_data['email'])) {
            $errors[] = __('Please enter a valid email address', 'recruit-connect-wp');
        }

        // Validate file upload
        if (in_array('resume', $required_fields) && empty($files['resume'])) {
            $errors[] = __('Resume is required', 'recruit-connect-wp');
        }

        if (!empty($files['resume'])) {
            $this->validate_file($files['resume']);
        }

        if (!empty($errors)) {
            throw new Exception(implode('<br>', $errors));
        }

        return array(
            'vacancy_id' => sanitize_text_field($post_data['vacancy_id']),
            'first_name' => sanitize_text_field($post_data['first_name']),
            'last_name' => sanitize_text_field($post_data['last_name']),
            'email' => sanitize_email($post_data['email']),
            'phone' => sanitize_text_field($post_data['phone']),
            'motivation' => wp_kses_post($post_data['motivation'])
        );
    }

    private function validate_file($file) {
        $allowed_types = array('pdf', 'doc', 'docx');
        $max_size = 5 * 1024 * 1024; // 5MB

        $file_info = pathinfo($file['name']);
        $extension = strtolower($file_info['extension']);

        if (!in_array($extension, $allowed_types)) {
            throw new Exception(__('Invalid file type. Allowed types: PDF, DOC, DOCX', 'recruit-connect-wp'));
        }

        if ($file['size'] > $max_size) {
            throw new Exception(__('File size exceeds maximum limit of 5MB', 'recruit-connect-wp'));
        }
    }

    private function handle_file_upload($file) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $upload = wp_handle_upload($file, array('test_form' => false));

        if (isset($upload['error'])) {
            throw new Exception($upload['error']);
        }

        return $upload['url'];
    }

    private function store_application($data) {
        global $wpdb;

        $result = $wpdb->insert(
            $wpdb->prefix . 'rcwp_applications',
            array(
                'vacancy_id' => $data['vacancy_id'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'motivation' => $data['motivation'],
                'resume_url' => isset($data['resume_url']) ? $data['resume_url'] : '',
                'status' => 'pending',
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        if ($result === false) {
            throw new Exception(__('Failed to store application', 'recruit-connect-wp'));
        }

        return $wpdb->insert_id;
    }

    private function send_to_remote($data) {
        $remote_url = get_option('rcwp_application_url');
        if (empty($remote_url)) {
            return new WP_Error('missing_url', __('Remote URL not configured', 'recruit-connect-wp'));
        }

        $response = wp_remote_post($remote_url, array(
            'body' => json_encode($data),
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-RCWP-Token' => get_option('rcwp_api_token')
            ),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            return new WP_Error(
                'remote_error',
                sprintf(__('Remote server returned error code: %d', 'recruit-connect-wp'),
                        $response_code)
            );
        }

        return true;
    }

    private function schedule_retry($application_id, $data) {
        wp_schedule_single_event(
            time() + $this->retry_delays[0],
            'rcwp_retry_application_submission',
            array($application_id, $data)
        );

        $this->logger->log(
            sprintf('Scheduled retry for application %d', $application_id),
            'info',
            array('retry_count' => 1)
        );
    }

    public function retry_submission($application_id, $data) {
        global $wpdb;

        $retry_count = $wpdb->get_var($wpdb->prepare(
            "SELECT retry_count FROM {$wpdb->prefix}rcwp_applications WHERE id = %d",
            $application_id
        ));

        if ($retry_count >= $this->max_retries) {
            $this->update_application_status($application_id, 'failed');
            $this->logger->log(
                sprintf('Max retries reached for application %d', $application_id),
                'error'
            );
            return;
        }

        $result = $this->send_to_remote($data);

        if (is_wp_error($result)) {
            // Schedule next retry
            $next_retry = $retry_count + 1;
            if ($next_retry < $this->max_retries) {
                wp_schedule_single_event(
                    time() + $this->retry_delays[$next_retry],
                    'rcwp_retry_application_submission',
                    array($application_id, $data)
                );

                $wpdb->update(
                    $wpdb->prefix . 'rcwp_applications',
                    array('retry_count' => $next_retry),
                    array('id' => $application_id)
                );

                $this->logger->log(
                    sprintf('Scheduled retry %d for application %d', $next_retry, $application_id),
                    'info'
                );
            }
        } else {
            $this->update_application_status($application_id, 'submitted');
            $this->logger->log(
                sprintf('Retry successful for application %d', $application_id),
                'info'
            );
        }
    }

    private function update_application_status($application_id, $status) {
        global $wpdb;

        $wpdb->update(
            $wpdb->prefix . 'rcwp_applications',
            array('status' => $status),
            array('id' => $application_id)
        );
    }

    private function send_confirmation_email($data) {
        $to = $data['email'];
        $subject = sprintf(
            __('Application Received - %s', 'recruit-connect-wp'),
            get_the_title($data['vacancy_id'])
        );

        $message = $this->get_email_template($data);

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_option('blogname') . ' <' . get_option('admin_email') . '>'
        );

        wp_mail($to, $subject, $message, $headers);
    }

    private function get_email_template($data) {
        ob_start();
        include RCWP_PLUGIN_DIR . 'templates/email/application-confirmation.php';
        return ob_get_clean();
    }

    private function get_field_label($field) {
        $labels = array(
            'first_name' => __('First Name', 'recruit-connect-wp'),
            'last_name' => __('Last Name', 'recruit-connect-wp'),
            'email' => __('Email', 'recruit-connect-wp'),
            'phone' => __('Phone', 'recruit-connect-wp'),
            'motivation' => __('Motivation', 'recruit-connect-wp'),
            'resume' => __('Resume', 'recruit-connect-wp')
        );

        return isset($labels[$field]) ? $labels[$field] : $field;
    }
}
