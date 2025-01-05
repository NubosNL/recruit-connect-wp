<?php
/**
 * Fired during plugin activation
 *
 * @link       https://www.nubos.nl/en
 * @since      1.0.0
 *
 * @package    Recruit_Connect_WP
 * @subpackage Recruit_Connect_WP/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Recruit_Connect_WP
 * @subpackage Recruit_Connect_WP/includes
 * @author     Nubos B.V. <info@nubos.nl>
 */
class RCWP_Activator {

    /**
     * Activate the plugin.
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
        // Create custom database tables for applications
        self::create_applications_table();

        // Create logs table
        self::create_logs_table();

        // Set default options
        self::set_default_options();

        // Schedule the XML import cron job
        if (!wp_next_scheduled('rcwp_xml_import_cron')) {
            wp_schedule_event(time(), 'daily', 'rcwp_xml_import_cron');
        }

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create the applications database table
     */
    private static function create_applications_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'rcwp_applications';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            vacancy_id varchar(255) NOT NULL,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(50) NOT NULL,
            motivation text NOT NULL,
            resume_url varchar(255) NOT NULL,
            status varchar(50) NOT NULL DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create the logs database table
     */
    private static function create_logs_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'rcwp_logs';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            level varchar(20) NOT NULL,
            message text NOT NULL,
            context text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Set default options for the plugin
     */
    private static function set_default_options() {
        // General Settings
        if (!get_option('rcwp_xml_url')) {
            update_option('rcwp_xml_url', '');
        }
        if (!get_option('rcwp_application_url')) {
            update_option('rcwp_application_url', '');
        }
        if (!get_option('rcwp_vacancy_url_parameter')) {
            update_option('rcwp_vacancy_url_parameter', 'vacancy');
        }
        if (!get_option('rcwp_enable_detail_page')) {
            update_option('rcwp_enable_detail_page', '1');
        }

        // Search Components
        if (!get_option('rcwp_search_components')) {
            update_option('rcwp_search_components', array(
                'category' => '1',
                'education' => '1',
                'jobtype' => '1',
                'salary' => '1'
            ));
        }

        // Application Form Settings
        if (!get_option('rcwp_thank_you_message')) {
            update_option('rcwp_thank_you_message', __('Thank you for your application!', 'recruit-connect-wp'));
        }
        if (!get_option('rcwp_required_fields')) {
            update_option('rcwp_required_fields', array(
                'first_name' => '1',
                'last_name' => '1',
                'email' => '1',
                'phone' => '1',
                'motivation' => '1',
                'resume' => '1'
            ));
        }

        // Synchronization Settings
        if (!get_option('rcwp_sync_frequency')) {
            update_option('rcwp_sync_frequency', 'daily');
        }

        // Detail Page Settings
        if (!get_option('rcwp_detail_page_fields')) {
            update_option('rcwp_detail_page_fields', array(
                'title' => '1',
                'description' => '1',
                'company' => '1',
                'location' => '1',
                'salary' => '1',
                'education' => '1',
                'jobtype' => '1',
                'experience' => '1',
                'recruiter' => '1'
            ));
        }
    }
}
