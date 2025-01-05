<?php
class RCWP_API {
    private $api_base_url;
    private $api_key;
    private $logger;
    private $cache;

    public function __construct($logger, $cache) {
        $this->logger = $logger;
        $this->cache = $cache;
        $this->api_base_url = get_option('rcwp_api_base_url');
        $this->api_key = get_option('rcwp_api_key');

        add_action('rest_api_init', array($this, 'register_api_routes'));
        add_action('rcwp_sync_with_remote', array($this, 'sync_with_remote'));
    }

    /**
     * Register custom API routes
     */
    public function register_api_routes() {
        register_rest_route('recruit-connect/v1', '/webhook', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_webhook'),
            'permission_callback' => array($this, 'verify_webhook')
        ));

        register_rest_route('recruit-connect/v1', '/vacancies/sync', array(
            'methods' => 'POST',
            'callback' => array($this, 'trigger_sync'),
            'permission_callback' => array($this, 'verify_api_key')
        ));
    }

    /**
     * Send application to remote API
     */
    public function send_application($application_data) {
        $response = wp_remote_post($this->api_base_url . '/applications', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($application_data),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            $this->logger->log('API Error: ' . $response->get_error_message(), 'error');
            throw new Exception($response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code !== 200) {
            $this->logger->log('API Error: ' . ($body['message'] ?? 'Unknown error'), 'error');
            throw new Exception('API Error: ' . ($body['message'] ?? 'Unknown error'));
        }

        return $body;
    }

    /**
     * Verify webhook signature
     */
    public function verify_webhook($request) {
        $signature = $request->get_header('X-Webhook-Signature');
        if (empty($signature)) {
            return false;
        }

        $payload = $request->get_body();
        $expected_signature = hash_hmac('sha256', $payload, $this->api_key);

        return hash_equals($expected_signature, $signature);
    }

    /**
     * Handle incoming webhook
     */
    public function handle_webhook($request) {
        $payload = $request->get_json_params();
        $event_type = $payload['event'] ?? '';

        switch ($event_type) {
            case 'vacancy.updated':
                $this->handle_vacancy_update($payload['data']);
                break;
            case 'application.status_changed':
                $this->handle_application_status_change($payload['data']);
                break;
            default:
                return new WP_Error('invalid_event', 'Invalid event type', array('status' => 400));
        }

        return new WP_REST_Response(array('status' => 'success'), 200);
    }

    /**
     * Handle vacancy update from webhook
     */
    private function handle_vacancy_update($data) {
        $vacancy_id = $data['external_id'] ?? null;
        if (!$vacancy_id) {
            throw new Exception('Missing vacancy ID');
        }

        // Update vacancy
        $vacancy_updater = new RCWP_Vacancy_Updater();
        $vacancy_updater->update_from_api_data($data);

        // Clear cache
        $this->cache->delete('vacancy_' . $vacancy_id);
        $this->cache->delete('vacancies_list');
    }

    /**
     * Handle application status change
     */
    private function handle_application_status_change($data) {
        global $wpdb;

        $application_id = $data['application_id'] ?? null;
        $new_status = $data['status'] ?? null;

        if (!$application_id || !$new_status) {
            throw new Exception('Missing required data');
        }

        // Update application status
        $wpdb->update(
            $wpdb->prefix . 'rcwp_applications',
            array('status' => $new_status),
            array('id' => $application_id),
            array('%s'),
            array('%d')
        );

        // Trigger notification
        do_action('rcwp_application_status_changed', $application_id, $new_status);
    }

    /**
     * Verify API key for protected endpoints
     */
    public function verify_api_key($request) {
        $auth_header = $request->get_header('Authorization');
        if (empty($auth_header)) {
            return false;
        }

        $api_key = str_replace('Bearer ', '', $auth_header);
        return $api_key === $this->api_key;
    }

    /**
     * Trigger manual sync
     */
    public function trigger_sync($request) {
        try {
            do_action('rcwp_sync_with_remote');
            return new WP_REST_Response(array('status' => 'success'), 200);
        } catch (Exception $e) {
            return new WP_Error('sync_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * Sync with remote API
     */
    public function sync_with_remote() {
        try {
            $response = wp_remote_get($this->api_base_url . '/vacancies', array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api_key
                ),
                'timeout' => 60
            ));

            if (is_wp_error($response)) {
                throw new Exception($response->get_error_message());
            }

            $vacancies = json_decode(wp_remote_retrieve_body($response), true);

            foreach ($vacancies as $vacancy) {
                $this->process_remote_vacancy($vacancy);
            }

            $this->logger->log('Remote sync completed successfully');
        } catch (Exception $e) {
            $this->logger->log('Remote sync failed: ' . $e->getMessage(), 'error');
            throw $e;
        }
    }

    /**
     * Process remote vacancy data
     */
    private function process_remote_vacancy($vacancy) {
        $vacancy_updater = new RCWP_Vacancy_Updater();
        $vacancy_updater->update_from_api_data($vacancy);
    }
}
