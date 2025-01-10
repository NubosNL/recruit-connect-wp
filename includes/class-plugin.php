<?php
namespace RecruitConnect;

class Plugin {
	protected $loader;
	protected $plugin_name;
	protected $version;

	public function __construct() {
		$this->version = RECRUIT_CONNECT_VERSION;
		$this->plugin_name = 'recruit-connect-wp';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_dependencies() {
		// Core plugin classes
		require_once RECRUIT_CONNECT_PLUGIN_DIR . 'includes/class-loader.php';
		require_once RECRUIT_CONNECT_PLUGIN_DIR . 'includes/class-logger.php';
		require_once RECRUIT_CONNECT_PLUGIN_DIR . 'includes/class-xml-importer.php';
		require_once RECRUIT_CONNECT_PLUGIN_DIR . 'includes/class-post-type.php';
		require_once RECRUIT_CONNECT_PLUGIN_DIR . 'includes/class-ajax-handler.php';
		require_once RECRUIT_CONNECT_PLUGIN_DIR . 'admin/class-settings.php';
		require_once RECRUIT_CONNECT_PLUGIN_DIR . 'admin/class-admin.php';
		require_once RECRUIT_CONNECT_PLUGIN_DIR . 'includes/class-blocks.php';


		// Initialize loader
		$this->loader = new Loader();

		// Initialize core components
		new PostType();
		new AjaxHandler();
		new Blocks();
	}

	private function set_locale() {
		$this->loader->add_action('plugins_loaded', $this, 'load_plugin_textdomain');
	}

	private function define_admin_hooks() {
		require_once RECRUIT_CONNECT_PLUGIN_DIR . 'admin/class-admin.php';
		$plugin_admin = new Admin($this->get_plugin_name(), $this->get_version());
	}

	private function define_public_hooks() {
		// Will add public hooks later
	}

	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'recruit-connect-wp',
			false,
			dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
		);
	}

	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_version() {
		return $this->version;
	}
}
