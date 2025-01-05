<?php
/**
 * Register all actions and filters for the plugin
 *
 * @link       https://www.nubos.nl/en
 * @since      1.0.0
 *
 * @package    Recruit_Connect_WP
 * @subpackage Recruit_Connect_WP/includes
 */

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress plugin API.
 *
 * @package    Recruit_Connect_WP
 * @subpackage Recruit_Connect_WP/includes
 * @author     Nubos B.V. <info@nubos.nl>
 */
class RCWP_Loader {

	/**
	 * The array of actions registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
	 */
	protected $actions;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
	 */
	protected $filters;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->actions = array();
		$this->filters = array();

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		// Include the license class
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-rcwp-license.php';

		// Include the i18n class
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-rcwp-i18n.php';

		// Include the admin class
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-rcwp-admin.php';

		// Include the public class
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-rcwp-public.php';

		// Include the XML importer class
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-rcwp-xml-importer.php';

		// Include the post type class
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-rcwp-post-type.php';

		// Include the application handler class
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-rcwp-application.php';

		// Include the logger class
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-rcwp-logger.php';
	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		foreach ($this->filters as $hook) {
			add_filter($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
		}

		foreach ($this->actions as $hook) {
			add_action($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
		}
	}

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string    $hook             The name of the WordPress action that is being registered.
	 * @param    object    $component        A reference to the instance of the object on which the action is defined.
	 * @param    string    $callback         The name of the function definition on the $component.
	 * @param    int       $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int       $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 */
	public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
		$this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string    $hook             The name of the WordPress filter that is being registered.
	 * @param    object    $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string    $callback         The name of the function definition on the $component.
	 * @param    int       $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int       $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1
	 */
	public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
		$this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
	}

	/**
	 * A utility function that is used to register the actions and hooks into a single
	 * collection.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array     $hooks            The collection of hooks that is being registered (that is, actions or filters).
	 * @param    string    $hook             The name of the WordPress filter that is being registered.
	 * @param    object    $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string    $callback         The name of the function definition on the $component.
	 * @param    int       $priority         The priority at which the function should be fired.
	 * @param    int       $accepted_args    The number of arguments that should be passed to the $callback.
	 * @return   array                       The collection of actions and filters registered with WordPress.
	 */
	private function add($hooks, $hook, $component, $callback, $priority, $accepted_args) {
		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		);

		return $hooks;
	}

	/**
	 * Set the locale for this plugin for internationalization.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new RCWP_i18n();
		$this->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new RCWP_Admin();

		// Add admin menu
		$this->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');

		// Add settings link to plugins page
		$this->add_filter('plugin_action_links_' . RCWP_PLUGIN_BASENAME, $plugin_admin, 'add_action_links');

		// Register settings
		$this->add_action('admin_init', $plugin_admin, 'register_settings');

		// Enqueue admin scripts and styles
		$this->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new RCWP_Public();

		// Enqueue public scripts and styles
		$this->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

		// Register shortcodes
		$this->add_action('init', $plugin_public, 'register_shortcodes');
	}
}
