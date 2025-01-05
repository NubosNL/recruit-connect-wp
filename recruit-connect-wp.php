<?php
/**
 * Plugin Name: Recruit Connect WP
 * Description: Imports job vacancies from an XML feed and displays them on the frontend.
 * Version: 1.0
 * Author: Nubos B.V.
 * Author URI: https://www.nubos.nl/en
 * Text Domain: recruit-connect-wp
 * Requires at least: 6.0
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
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
	/**
	 * The single instance of the class.
	 *
	 * @var Recruit_Connect_WP
	 */
	private static $instance = null;

	/**
	 * Core class instances
	 */
	private $loader;
	private $i18n;
	private $logger;
	private $security;
	private $cache;
	private $validator;
	private $post_type;
	private $xml_importer;
	private $application_handler;
	private $mailer;
	private $notifications;
	private $monitor;
	private $blocks;
	private $frontend;
	private $api;
	private $rest_api;
	private $admin;
	private $admin_dashboard;

	/**
	 * Main Recruit_Connect_WP Instance.
	 *
	 * Ensures only one instance of Recruit_Connect_WP is loaded or can be loaded.
	 *
	 * @return Recruit_Connect_WP - Main instance.
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->init_hooks();
	}

	/**
	 * Load the required dependencies.
	 */
	private function load_dependencies() {
		// Load core framework classes first
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-loader.php';
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-i18n.php';
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-logger.php';
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-settings.php';

		// Initialize core framework components
		$this->loader = new RCWP_Loader();
		$this->i18n = new RCWP_i18n();
		$this->logger = new RCWP_Logger();
		$this->settings = new RCWP_Settings();

		// Load all other classes
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-activator.php';
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-deactivator.php';
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-security.php';
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-cache.php';
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-validator.php';
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-post-type.php';
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-xml-importer.php';
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-application.php';
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-application-handler.php';
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-frontend.php';
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-search.php';
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-mailer.php';
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-notifications.php';
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-performance.php';
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-monitor.php';
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-blocks.php';
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-block-category.php';
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-schema.php';
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-api.php';
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-rest-api.php';
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-data-import.php';
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-data-export.php';
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-backup.php';
		require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-updater.php';

		// Initialize components that need logger
		$this->security = new RCWP_Security($this->logger);
		$this->cache = new RCWP_Cache($this->logger);
		$this->validator = new RCWP_Validator($this->logger);
		$this->xml_importer = new RCWP_XML_Importer($this->logger);
		$this->application_handler = new RCWP_Application_Handler($this->logger);
		$this->mailer = new RCWP_Mailer($this->logger);
		$this->monitor = new RCWP_Monitor($this->logger);
		$this->notifications = new RCWP_Notifications($this->logger);
		$this->performance = new RCWP_Performance($this->logger); // Moved here

		// Initialize components that need both logger and settings
		$this->api = new RCWP_API($this->logger, $this->settings);
		$this->rest_api = new RCWP_REST_API($this->logger, $this->settings);

		// Initialize components without logger dependency
		$this->post_type = new RCWP_Post_Type();
		$this->blocks = new RCWP_Blocks();
		$this->frontend = new RCWP_Frontend();

		// Admin classes
		if (is_admin()) {
			require_once RCWP_PLUGIN_DIR . 'includes/class-rcwp-admin.php';

			$this->admin = new RCWP_Admin($this->logger);
		}
	}

	/**
	 * Register all hooks.
	 */
	private function init_hooks() {
		register_activation_hook(__FILE__, array($this, 'activate'));
		register_deactivation_hook(__FILE__, array($this, 'deactivate'));

		add_action('plugins_loaded', array($this, 'init'));
		add_action('init', array($this, 'load_textdomain'));

		// Register hooks with the loader
		$this->loader->add_action('rcwp_xml_import_cron', $this->xml_importer, 'import_vacancies');

		if (is_admin()) {
			$this->loader->add_action('admin_enqueue_scripts', $this->admin, 'enqueue_scripts');
			$this->loader->add_action('admin_enqueue_scripts', $this->admin, 'enqueue_styles');
			$this->loader->add_action('admin_menu', $this->admin, 'add_admin_menu');
			// Remove this line as we'll handle it in the constructor
			// $this->loader->add_action('admin_init', $this->admin, 'register_settings');
		}
	}

	/**
	 * Activation hook callback.
	 */
	public function activate() {
		RCWP_Activator::activate();

		// Create custom tables
		$this->create_tables();

		// Schedule cron job
		if (!wp_next_scheduled('rcwp_xml_import_cron')) {
			wp_schedule_event(time(), 'hourly', 'rcwp_xml_import_cron');
		}

		// Flush rewrite rules
		flush_rewrite_rules();
	}

	/**
	 * Deactivation hook callback.
	 */
	public function deactivate() {
		RCWP_Deactivator::deactivate();
		wp_clear_scheduled_hook('rcwp_xml_import_cron');
		flush_rewrite_rules();
	}

	/**
	 * Create custom database tables.
	 */
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
            PRIMARY KEY (id)
        ) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	/**
	 * Initialize plugin.
	 */
	public function init() {
		// Add custom cron schedules
		add_filter('cron_schedules', array($this, 'add_cron_schedules'));

		// Run the loader
		$this->loader->run();
	}

	/**
	 * Add custom cron schedules.
	 */
	public function add_cron_schedules($schedules) {
		$schedules['quarter_daily'] = array(
			'interval' => 21600, // 6 hours
			'display' => __('Four times daily', 'recruit-connect-wp')
		);
		return $schedules;
	}

	/**
	 * Load plugin text domain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'recruit-connect-wp',
			false,
			dirname(RCWP_PLUGIN_BASENAME) . '/languages'
		);
	}
}

/**
 * Returns the main instance of Recruit_Connect_WP.
 *
 * @return Recruit_Connect_WP
 */
function RCWP() {
	return Recruit_Connect_WP::get_instance();
}

// Initialize the plugin
RCWP();
