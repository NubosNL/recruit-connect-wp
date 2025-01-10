<?php
/**
 * Plugin Name: Recruit Connect WP
 * Plugin URI: https://www.nubos.nl/en/recruit-connect
 * Description: Import and display job vacancies from Recruit Connect XML feed
 * Version: 1.0.0
 * Author: Nubos B.V.
 * Author URI: https://www.nubos.nl/en
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: recruit-connect-wp
 * Domain Path: /languages
 * Requires at least: 6.0
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

// Plugin version
define('RECRUIT_CONNECT_VERSION', '1.0.0');
define('RECRUIT_CONNECT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RECRUIT_CONNECT_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load required files directly
require_once RECRUIT_CONNECT_PLUGIN_DIR . 'includes/class-loader.php';
require_once RECRUIT_CONNECT_PLUGIN_DIR . 'includes/class-plugin.php';

// Activation
register_activation_hook(__FILE__, function() {
	require_once RECRUIT_CONNECT_PLUGIN_DIR . 'includes/class-activator.php';
	RecruitConnect\Activator::activate();
});

// Deactivation
register_deactivation_hook(__FILE__, function() {
	require_once RECRUIT_CONNECT_PLUGIN_DIR . 'includes/class-deactivator.php';
	RecruitConnect\Deactivator::deactivate();
});

/**
 * Begins execution of the plugin.
 */
function run_recruit_connect() {
	$plugin = new RecruitConnect\Plugin();
	$plugin->run();
}

// Start the plugin
run_recruit_connect();
