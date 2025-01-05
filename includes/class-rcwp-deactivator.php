<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://www.nubos.nl/en
 * @since      1.0.0
 *
 * @package    Recruit_Connect_WP
 * @subpackage Recruit_Connect_WP/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Recruit_Connect_WP
 * @subpackage Recruit_Connect_WP/includes
 * @author     Nubos B.V. <info@nubos.nl>
 */
class RCWP_Deactivator {

    /**
     * Deactivate the plugin.
     *
     * Clear scheduled hooks and flush rewrite rules
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Clear scheduled hooks
        wp_clear_scheduled_hook('rcwp_xml_import_cron');

        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
