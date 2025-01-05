<?php
/**
 * Plugin Name: Recruit Connect WP
 * Description: Imports job vacancies from an XML feed and displays them on the frontend.
 * Version: 1.0
 * Author: Nubos B.V.
 * Author URI: https://www.nubos.nl/en
 * Text Domain: recruit-connect-wp
 * Requires at least: 6.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('RCWP_VERSION', '1.0.0');
define('RCWP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RCWP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RCWP_PLUGIN_BASENAME', plugin_basename(__FILE__));

class Recruit_Connect_WP {
    private static $instance = null;
    private $xml_importer;
    private $post_type;
    private $logger;
    private $frontend;
    private $admin;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    private function load_dependencies() {
        // Core classes
        require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-post-type.php';
        require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-xml-importer.php';
        require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-logger.php';
        require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-application.php';
        require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-frontend.php';

        // Initialize core components
        $this->logger = new RCWP_Logger();
        $this->post_type = new RCWP_Post_Type();
        $this->xml_importer = new RCWP_XML_Importer($this->logger);
        $this->frontend = new RCWP_Frontend();

        // Admin classes
        if (is_admin()) {
            require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-admin.php';
            require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-settings.php';
            $this->admin = new RCWP_Admin($this->logger);
        }
    }

    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        add_action('plugins_loaded', array($this, 'init'));
        add_action('init', array($this, 'load_textdomain'));
    }

    public function activate() {
        // Create custom tables
        $this->create_tables();

        // Schedule cron job
        if (!wp_next_scheduled('rcwp_xml_import_cron')) {
            wp_schedule_event(time(), 'hourly', 'rcwp_xml_import_cron');
        }

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    public function deactivate() {
        wp_clear_scheduled_hook('rcwp_xml_import_cron');
        flush_rewrite_rules();
    }

    private function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rcwp_applications (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            vacancy_id varchar(255) NOT NULL,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(50),
            motivation text,
            resume_url varchar(255),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            status varchar(50) DEFAULT 'pending',
            retry_count int DEFAULT 0,
            last_retry datetime,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function init() {
        // Add custom cron schedules
        add_filter('cron_schedules', array($this, 'add_cron_schedules'));
    }

    public function add_cron_schedules($schedules) {
        $schedules['quarter_daily'] = array(
            'interval' => 21600, // 6 hours
            'display' => __('Four times daily', 'recruit-connect-wp')
        );
        return $schedules;
    }

    public function load_textdomain() {
        load_plugin_textdomain(
            'recruit-connect-wp',
            false,
            dirname(RCWP_PLUGIN_BASENAME) . '/languages'
        );
    }
}

function RCWP() {
    return Recruit_Connect_WP::get_instance();
}

// Initialize the plugin
RCWP();
