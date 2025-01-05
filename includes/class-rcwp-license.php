<?php
class RCWP_License {
    private $api_url = 'https://www.nubos.nl/api/v1/licenses';
    private $product_id = 'recruit-connect-wp';
    private $logger;
    private $cache;

    public function __construct($logger, $cache) {
        $this->logger = $logger;
        $this->cache = $cache;

        add_action('admin_init', array($this, 'check_license'));
        add_action('admin_notices', array($this, 'admin_notices'));
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_updates'));
    }

    /**
     * Activate license
     */
    public function activate_license($license_key) {
        try {
            $response = wp_remote_post($this->api_url . '/activate', array(
                'body' => array(
                    'license_key' => $license_key,
                    'product_id' => $this->product_id,
                    'site_url' => home_url(),
                    'php_version' => PHP_VERSION,
                    'wp_version' => get_bloginfo('version')
                )
            ));

            if (is_wp_error($response)) {
                throw new Exception($response->get_error_message());
            }

            $result = json_decode(wp_remote_retrieve_body($response), true);

            if (!$result['success']) {
                throw new Exception($result['message']);
            }

            update_option('rcwp_license_key', $license_key);
            update_option('rcwp_license_status', 'valid');
            update_option('rcwp_license_expiry', $result['expiry']);

            $this->logger->log('License activated successfully');
            return true;

        } catch (Exception $e) {
            $this->logger->log('License activation failed: ' . $e->getMessage(), 'error');
            throw $e;
        }
    }

    /**
     * Deactivate license
     */
    public function deactivate_license() {
        $license_key = get_option('rcwp_license_key');

        if (!$license_key) {
            return false;
        }

        try {
            $response = wp_remote_post($this->api_url . '/deactivate', array(
                'body' => array(
                    'license_key' => $license_key,
                    'product_id' => $this->product_id,
                    'site_url' => home_url()
                )
            ));

            if (is_wp_error($response)) {
                throw new Exception($response->get_error_message());
            }

            delete_option('rcwp_license_key');
            delete_option('rcwp_license_status');
            delete_option('rcwp_license_expiry');

            $this->logger->log('License deactivated successfully');
            return true;

        } catch (Exception $e) {
            $this->logger->log('License deactivation failed: ' . $e->getMessage(), 'error');
            throw $e;
        }
    }

    /**
     * Check license status
     */
    public function check_license() {
        if (!$this->should_check_license()) {
            return;
        }

        $license_key = get_option('rcwp_license_key');
        if (!$license_key) {
            return;
        }

        try {
            $response = wp_remote_get($this->api_url . '/verify', array(
                'body' => array(
                    'license_key' => $license_key,
                    'product_id' => $this->product_id,
                    'site_url' => home_url()
                )
            ));

            if (is_wp_error($response)) {
                throw new Exception($response->get_error_message());
            }

            $result = json_decode(wp_remote_retrieve_body($response), true);

            if (!$result['success']) {
                update_option('rcwp_license_status', 'invalid');
                throw new Exception($result['message']);
            }

            update_option('rcwp_license_status', 'valid');
            update_option('rcwp_license_expiry', $result['expiry']);
            update_option('rcwp_last_license_check', time());

        } catch (Exception $e) {
            $this->logger->log('License check failed: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Check for plugin updates
     */
    public function check_for_updates($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $license_key = get_option('rcwp_license_key');
        if (!$license_key || get_option('rcwp_license_status') !== 'valid') {
            return $transient;
        }

        try {
            $response = wp_remote_get($this->api_url . '/updates', array(
                'body' => array(
                    'license_key' => $license_key,
                    'product_id' => $this->product_id,
                    'version' => RCWP_VERSION
                )
            ));

            if (is_wp_error($response)) {
                throw new Exception($response->get_error_message());
            }

            $result = json_decode(wp_remote_retrieve_body($response), true);

            if ($result['has_update']) {
                $transient->response[RCWP_PLUGIN_BASENAME] = (object) array(
                    'slug' => 'recruit-connect-wp',
                    'new_version' => $result['version'],
                    'package' => $result['download_url'],
                    'tested' => $result['tested_wp_version']
                );
            }

        } catch (Exception $e) {
            $this->logger->log('Update check failed: ' . $e->getMessage(), 'error');
        }

        return $transient;
    }

    /**
     * Display admin notices
     */
    public function admin_notices() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $license_status = get_option('rcwp_license_status');
        $license_expiry = get_option('rcwp_license_expiry');

        if (!$license_status || $license_status === 'invalid') {
            echo '<div class="notice notice-error">';
            echo '<p>' . __('Your Recruit Connect WP license is invalid or has expired. Please enter a valid license key to continue receiving updates and support.', 'recruit-connect-wp') . '</p>';
            echo '<p><a href="' . admin_url('admin.php?page=rcwp-license') . '" class="button button-primary">' . __('Enter License Key', 'recruit-connect-wp') . '</a></p>';
            echo '</div>';
        } elseif ($license_expiry && strtotime($license_expiry) < strtotime('+30 days')) {
            echo '<div class="notice notice-warning">';
            echo '<p>' . sprintf(
                __('Your Recruit Connect WP license will expire on %s. Please renew your license to continue receiving updates and support.', 'recruit-connect-wp'),
                date_i18n(get_option('date_format'), strtotime($license_expiry))
            ) . '</p>';
            echo '<p><a href="https://www.nubos.nl/en/recruit-connect/renew" class="button button-primary" target="_blank">' . __('Renew License', 'recruit-connect-wp') . '</a></p>';
            echo '</div>';
        }
    }

    /**
     * Check if license should be verified
     */
    private function should_check_license() {
        $last_check = get_option('rcwp_last_license_check');
        return !$last_check || (time() - $last_check) > DAY_IN_SECONDS;
    }

    /**
     * Get license information
     */
    public function get_license_info() {
        return array(
            'key' => get_option('rcwp_license_key'),
            'status' => get_option('rcwp_license_status'),
            'expiry' => get_option('rcwp_license_expiry'),
            'last_check' => get_option('rcwp_last_license_check')
        );
    }
}
