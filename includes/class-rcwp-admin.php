<?php
class RCWP_Admin {
    private $logger;
    private $settings;

    public function __construct($logger) {
        $this->logger = $logger;
        $this->settings = new RCWP_Settings();

        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('admin_init', array($this, 'init_admin'));
    }

    public function add_admin_menu() {
        add_menu_page(
            __('Recruit Connect', 'recruit-connect-wp'),
            __('Recruit Connect', 'recruit-connect-wp'),
            'manage_options',
            'recruit-connect-wp',
            array($this->settings, 'render_settings_page'),
            'dashicons-businessman',
            30
        );

        add_submenu_page(
            'recruit-connect-wp',
            __('Settings', 'recruit-connect-wp'),
            __('Settings', 'recruit-connect-wp'),
            'manage_options',
            'recruit-connect-wp'
        );

        add_submenu_page(
            'recruit-connect-wp',
            __('Logs', 'recruit-connect-wp'),
            __('Logs', 'recruit-connect-wp'),
            'manage_options',
            'recruit-connect-wp-logs',
            array($this, 'render_logs_page')
        );
    }

    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'recruit-connect-wp') === false) {
            return;
        }

        wp_enqueue_style(
            'rcwp-admin-css',
            RCWP_PLUGIN_URL . 'admin/css/admin.css',
            array(),
            RCWP_VERSION
        );

        wp_enqueue_script(
            'rcwp-admin-js',
            RCWP_PLUGIN_URL . 'admin/js/admin.js',
            array('jquery', 'jquery-ui-sortable'),
            RCWP_VERSION,
            true
        );

        wp_localize_script('rcwp-admin-js', 'rcwpAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rcwp-admin-nonce'),
            'strings' => array(
                'syncSuccess' => __('Sync completed successfully!', 'recruit-connect-wp'),
                'syncError' => __('Error during sync. Please check logs.', 'recruit-connect-wp')
            )
        ));
    }

    public function init_admin() {
        // Register settings
        register_setting('rcwp_general_settings', 'rcwp_xml_url');
        register_setting('rcwp_general_settings', 'rcwp_application_url');
        register_setting('rcwp_general_settings', 'rcwp_detail_url_param');
        register_setting('rcwp_general_settings', 'rcwp_enable_detail_page');
        register_setting('rcwp_general_settings', 'rcwp_search_components');

        // Application form settings
        register_setting('rcwp_application_settings', 'rcwp_thank_you_message');
        register_setting('rcwp_application_settings', 'rcwp_required_fields');

        // Sync settings
        register_setting('rcwp_sync_settings', 'rcwp_sync_frequency');
    }

    public function render_logs_page() {
        $logs = $this->logger->get_logs();
        include RCWP_PLUGIN_DIR . 'admin/views/logs-page.php';
    }
}
