<?php
class RCWP_Application {
    private $logger;
    private $retry_count = 5;
    private $retry_delays = array(30, 60, 300, 600, 1800); // Delays in seconds

    public function __construct($logger) {
        $this->logger = $logger;
        add_action('wp_ajax_rcwp_submit_application', array($this, 'handle_submission'));
        add_action('wp_ajax_nopriv_rcwp_submit_application', array($this, 'handle_submission'));
    }

    public function render_form($vacancy_id) {
        $required_fields = get_option('rcwp_required_fields', array());
        ob_start();
        ?>
        <form class="rcwp-application-form" id="rcwp-application-form">
            <input type="hidden" name="vacancy_id" value="<?php echo esc_attr($vacancy_id); ?>">

            <div class="rcwp-form-row">
                <label for="first_name">
                    <?php _e('First Name', 'recruit-connect-wp'); ?>
                    <?php if (in_array('first_name', $required_fields)) echo '*'; ?>
                </label>
                <input type="text" name="first_name" id="first_name"
                       <?php if (in_array('first_name', $required_fields)) echo 'required'; ?>>
            </div>

            <div class="rcwp-form-row">
                <label for="last_name">
                    <?php _e('Last Name', 'recruit-connect-wp'); ?>
                    <?php if (in_array('last_name', $required_fields)) echo '*'; ?>
                </label>
                <input type="text" name="last_name" id="last_name"
                       <?php if (in_array('last_name', $required_fields)) echo 'required'; ?>>
            </div>

            <div class="rcwp-form-row">
                <label for="email">
                    <?php _e('Email', 'recruit-connect-wp'); ?>
                    <?php if (in_array('email', $required_fields)) echo '*'; ?>
                </label>
                <input type="email" name="email" id="email"
                       <?php if (in_array('email', $required_fields)) echo 'required'; ?>>
            </div>

            <div class="rcwp-form-row">
                <label for="phone">
                    <?php _e('Phone', 'recruit-connect-wp'); ?>
                    <?php if (in_array('phone', $required_fields)) echo '*'; ?>
                </label>
                <input type="tel" name="phone" id="phone"
                       <?php if (in_array('phone', $required_fields)) echo 'required'; ?>>
            </div>

            <div class="rcwp-form-row">
                <label for="motivation">
                    <?php _e('Motivation', 'recruit-connect-wp'); ?>
                    <?php if (in_array('motivation', $required_fields)) echo '*'; ?>
                </label>
                <textarea name="motivation" id="motivation"
                          <?php if (in_array('motivation', $required_fields)) echo 'required'; ?>></textarea>
            </div>

            <div class="rcwp-form-row">
                <label for="resume">
                    <?php _e('Resume', 'recruit-connect-wp'); ?>
                    <?php if (in_array('resume', $required_fields)) echo '*'; ?>
                </label>
                <input type="file" name="resume" id="resume" accept=".pdf,.doc,.docx"
                       <?php if (in_array('resume', $required_fields)) echo 'required'; ?>>
            </div>

            <div class="rcwp-form-submit">
                <button type="submit" class="rcwp-submit-btn">
                    <?php _e('Submit Application', 'recruit-connect-wp'); ?>
                </button>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }

    public function handle_submission() {
        check_ajax_referer('rcwp-frontend-nonce', 'nonce');

        $application_data = $this->validate_and_sanitize_data($_POST);
        if (is_wp_error($application_data)) {
            wp_send_json_error($application_data->get_error_message());
        }

        // Handle file upload
        if (!empty($_FILES['resume'])) {
            $upload_result = $this->handle_file_upload($_FILES['resume']);
            if (is_wp_error($upload_result)) {
                wp_send_json_error($upload_result->get_error_message());
            }
            $application_data['resume_url'] = $upload_result;
        }

        // Store in database
        $stored = $this->store_application($application_data);
        if (is_wp_error($stored)) {
            wp_send_json_error($stored->get_error_message());
        }

        // Send to remote URL
        $remote_result = $this->send_to_remote($application_data);
        if (is_wp_error($remote_result)) {
            // Schedule retry
            $this->schedule_retry($stored, $application_data);
        }

        wp_send_json_success(array(
            'message' => get_option('rcwp_thank_you_message',
                       __('Thank you for your application!', 'recruit-connect-wp'))
        ));
    }

    private function validate_and_sanitize_data($data) {
        $required_fields = get_option('rcwp_required_fields', array());
        $sanitized = array();

        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return new WP_Error(
                    'missing_required_field',
                    sprintf(__('Field %s is required', 'recruit-connect-wp'), $field)
                );
            }
        }

        $sanitized['vacancy_id'] = sanitize_text_field($data['vacancy_id']);
        $sanitized['first_name'] = sanitize_text_field($data['first_name']);
        $sanitized['last_name'] = sanitize_text_field($data['last_name']);
        $sanitized['email'] = sanitize_email($data['email']);
        $sanitized['phone'] = sanitize_text_field($data['phone']);
        $sanitized['motivation'] = wp_kses_post($data['motivation']);

        return $sanitized;
    }

    private function handle_file_upload($file) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $upload = wp_handle_upload($file, array('test_form' => false));

        if (isset($upload['error'])) {
            return new WP_Error('upload_error', $upload['error']);
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
                'resume_url' => $data['resume_url'],
                'status' => 'pending'
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        if ($result === false) {
            return new WP_Error(
                'db_error',
                __('Could not store application', 'recruit-connect-wp')
            );
        }

        return $wpdb->insert_id;
    }

    private function send_to_remote($data) {
        $remote_url = get_option('rcwp_application_url');
        if (empty($remote_url)) {
            return new WP_Error(
                'missing_url',
                __('Remote URL not configured', 'recruit-connect-wp')
            );
        }

        $response = wp_remote_post($remote_url, array(
            'body' => json_encode($data),
            'headers' => array('Content-Type' => 'application/json'),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            $this->logger->log('Remote submission failed: ' . $response->get_error_message());
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
            array($application_id, $data, 1)
        );
    }

    public function handle_retry($application_id, $data, $attempt) {
        $result = $this->send_to_remote($data);

        if (is_wp_error($result) && $attempt < $this->retry_count) {
            wp_schedule_single_event(
                time() + $this->retry_delays[$attempt],
                'rcwp_retry_application_submission',
                array($application_id, $data, $attempt + 1)
            );
        } else {
            $this->update_application_status(
                $application_id,
                is_wp_error($result) ? 'failed' : 'sent'
            );
        }
    }

    private function update_application_status($application_id, $status) {
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'rcwp_applications',
            array('status' => $status),
            array('id' => $application_id),
            array('%s'),
            array('%d')
        );
    }
}
