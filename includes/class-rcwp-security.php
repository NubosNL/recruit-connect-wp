<?php
class RCWP_Security {
    private $logger;
    private $nonce_life = 3600; // 1 hour
    private $allowed_html;
    private $blocked_ips = array();

    public function __construct($logger) {
        $this->logger = $logger;
        $this->init_allowed_html();
        $this->load_blocked_ips();

        // Security hooks
        add_action('init', array($this, 'init_security_measures'));
        add_filter('rcwp_validate_input', array($this, 'sanitize_input'), 10, 2);
        add_action('wp_login_failed', array($this, 'log_failed_login'));
    }

    /**
     * Initialize security measures
     */
    public function init_security_measures() {
        // Set security headers
        add_action('send_headers', array($this, 'set_security_headers'));

        // Rate limiting
        add_action('init', array($this, 'check_rate_limit'));

        // CSRF protection
        if ($this->is_form_submission()) {
            $this->verify_nonce();
        }

        // IP blocking
        $this->check_ip_block();
    }

    /**
     * Set security headers
     */
    public function set_security_headers() {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';");
    }

    /**
     * Initialize allowed HTML tags
     */
    private function init_allowed_html() {
        $this->allowed_html = array(
            'a' => array(
                'href' => array(),
                'title' => array(),
                'target' => array('_blank')
            ),
            'br' => array(),
            'p' => array(),
            'strong' => array(),
            'em' => array(),
            'ul' => array(),
            'li' => array(),
            'span' => array(
                'class' => array()
            )
        );
    }

    /**
     * Sanitize and validate input
     */
    public function sanitize_input($input, $type = 'text') {
        switch ($type) {
            case 'email':
                return $this->sanitize_email($input);

            case 'url':
                return $this->sanitize_url($input);

            case 'html':
                return $this->sanitize_html($input);

            case 'filename':
                return $this->sanitize_filename($input);

            case 'int':
                return $this->sanitize_int($input);

            default:
                return $this->sanitize_text($input);
        }
    }

    /**
     * Sanitize email
     */
    private function sanitize_email($email) {
        $email = sanitize_email($email);
        if (!is_email($email)) {
            throw new Exception(__('Invalid email address', 'recruit-connect-wp'));
        }
        return $email;
    }

    /**
     * Sanitize URL
     */
    private function sanitize_url($url) {
        $url = esc_url_raw($url);
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception(__('Invalid URL', 'recruit-connect-wp'));
        }
        return $url;
    }

    /**
     * Sanitize HTML content
     */
    private function sanitize_html($content) {
        return wp_kses($content, $this->allowed_html);
    }

    /**
     * Sanitize filename
     */
    private function sanitize_filename($filename) {
        return sanitize_file_name($filename);
    }

    /**
     * Sanitize integer
     */
    private function sanitize_int($value) {
        return filter_var($value, FILTER_VALIDATE_INT);
    }

    /**
     * Sanitize text
     */
    private function sanitize_text($text) {
        return sanitize_text_field($text);
    }

    /**
     * Check rate limiting
     */
    public function check_rate_limit() {
        $ip = $this->get_client_ip();
        $key = 'rcwp_rate_limit_' . md5($ip);
        $limit = 100; // requests per hour
        $current = get_transient($key);

        if (false === $current) {
            set_transient($key, 1, HOUR_IN_SECONDS);
        } elseif ($current >= $limit) {
            $this->log_security_event('Rate limit exceeded for IP: ' . $ip);
            wp_die(__('Rate limit exceeded. Please try again later.', 'recruit-connect-wp'));
        } else {
            set_transient($key, $current + 1, HOUR_IN_SECONDS);
        }
    }

    /**
     * Verify nonce
     */
    private function verify_nonce() {
        $nonce = $_REQUEST['_wpnonce'] ?? '';
        if (!wp_verify_nonce($nonce, 'rcwp_form_action')) {
            $this->log_security_event('Invalid nonce detected');
            wp_die(__('Security check failed.', 'recruit-connect-wp'));
        }
    }

    /**
     * Check IP blocking
     */
    private function check_ip_block() {
        $ip = $this->get_client_ip();
        if (in_array($ip, $this->blocked_ips)) {
            $this->log_security_event('Blocked IP attempted access: ' . $ip);
            wp_die(__('Access denied.', 'recruit-connect-wp'));
        }
    }

    /**
     * Log failed login attempts
     */
    public function log_failed_login($username) {
        $ip = $this->get_client_ip();
        $key = 'rcwp_failed_login_' . md5($ip);
        $attempts = get_transient($key) ?: 0;

        if ($attempts >= 5) {
            $this->block_ip($ip);
        } else {
            set_transient($key, $attempts + 1, HOUR_IN_SECONDS);
        }

        $this->log_security_event(sprintf(
            'Failed login attempt for user %s from IP %s',
            $username,
            $ip
        ));
    }

    /**
     * Block IP address
     */
    private function block_ip($ip) {
        if (!in_array($ip, $this->blocked_ips)) {
            $this->blocked_ips[] = $ip;
            update_option('rcwp_blocked_ips', $this->blocked_ips);
            $this->log_security_event('IP blocked: ' . $ip);
        }
    }

    /**
     * Load blocked IPs
     */
    private function load_blocked_ips() {
        $this->blocked_ips = get_option('rcwp_blocked_ips', array());
    }

    /**
     * Get client IP
     */
    private function get_client_ip() {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return filter_var($ip, FILTER_VALIDATE_IP);
    }

    /**
     * Log security event
     */
    private function log_security_event($message) {
        $this->logger->log($message, 'security');
    }

    /**
     * Check if current request is a form submission
     */
    private function is_form_submission() {
        return (
            isset($_POST['action']) &&
            strpos($_POST['action'], 'rcwp_') === 0
        );
    }

    /**
     * Generate nonce field
     */
    public static function nonce_field() {
        wp_nonce_field('rcwp_form_action', '_wpnonce', true, true);
    }

    /**
     * Validate file upload
     */
    public function validate_file_upload($file, $allowed_types = array('pdf', 'doc', 'docx')) {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new Exception(__('Invalid file parameters', 'recruit-connect-wp'));
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new Exception(__('File size exceeds limit', 'recruit-connect-wp'));
            default:
                throw new Exception(__('Unknown file upload error', 'recruit-connect-wp'));
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_types)) {
            throw new Exception(__('File type not allowed', 'recruit-connect-wp'));
        }

        if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
            throw new Exception(__('File size too large', 'recruit-connect-wp'));
        }

        return true;
    }
}
