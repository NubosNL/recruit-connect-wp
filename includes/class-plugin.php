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
		$this->set_submenu_position();

		// Register ACF fields using acf/init hook
		add_action('acf/init', array($this, 'register_recruit_connect_acf_fields'));
	}

	private function set_submenu_position() {
		add_filter('custom_menu_order', array($this, 'order_admin_submenu'));
		add_filter('menu_order', array($this, 'order_admin_submenu'));
	}

	public function order_admin_submenu($menu_ord) {
		global $submenu;

		if (isset($submenu['recruit-connect'])) {
			$applications_index = null;

			// Find the index of our applications submenu
			foreach($submenu['recruit-connect'] as $index => $item) {
				if ($item[2] == 'edit.php?post_type=vacancy_application') {
					$applications_index = $index;
					break;
				}
			}

			if($applications_index !== null) {
				// Move the Applications submenu to the end
				$applications = $submenu['recruit-connect'][$applications_index];
				unset($submenu['recruit-connect'][$applications_index]);
				$submenu['recruit-connect'][] = $applications;
			}

		}

		return $menu_ord;
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
		require_once RECRUIT_CONNECT_PLUGIN_DIR . 'includes/class-shortcodes.php';
		require_once RECRUIT_CONNECT_PLUGIN_DIR . 'includes/class-application-form.php';

		// Initialize loader
		$this->loader = new Loader();

		// Initialize core components
		new PostType();
		new AjaxHandler($this);
		new Shortcodes();
		new ApplicationForm();
	}

	public function CreateCustomFields() {
		add_action( 'acf/init', $plugin_admin, 'acf_test_fields_register_field_group' );
	}

	private function set_locale() {
		$this->loader->add_action('plugins_loaded', $this, 'load_plugin_textdomain');
	}

	private function define_admin_hooks() {
		require_once RECRUIT_CONNECT_PLUGIN_DIR . 'admin/class-admin.php';
		$plugin_admin = new Admin($this->get_plugin_name(), $this->get_version());
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
	}

	private function define_public_hooks() {
		// Will add public hooks later
	}

	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'recruit-connect-wp',
			false,
			basename( dirname( __FILE__ ) ) . '/languages/'
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

	public function register_recruit_connect_acf_fields() {
		// Controleer of de ACF-functies beschikbaar zijn (still good to check)
		if (!function_exists('acf_add_local_field_group')) {
			error_log('Recruit Connect WP - register_recruit_connect_acf_fields: ACF not installed, exiting.');
			return false;
		}

		acf_update_setting( 'enable_shortcode', true );

		$field_groups = array(
			// Vacancy Fields Group
			array(
				'key' => 'group_recruit_connect_vacancy',
				'title' => __('Vacancy Fields', 'recruit-connect-wp'),
				'fields' => array(
					array(
						'key' => 'field_recruit_connect_vacancy_id',
						'label' => __('Vacancy ID', 'recruit-connect-wp'),
						'name' => 'vacancy_id',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_company',
						'label' => __('Company', 'recruit-connect-wp'),
						'name' => 'company',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_city',
						'label' => __('City', 'recruit-connect-wp'),
						'name' => 'city',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_category',
						'label' => __('Category', 'recruit-connect-wp'),
						'name' => 'category',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_createdat',
						'label' => __('Created At', 'recruit-connect-wp'),
						'name' => 'createdat',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_streetaddress',
						'label' => __('Street Address', 'recruit-connect-wp'),
						'name' => 'streetaddress',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_postalcode',
						'label' => __('Postal Code', 'recruit-connect-wp'),
						'name' => 'postalcode',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_state',
						'label' => __('State', 'recruit-connect-wp'),
						'name' => 'state',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_country',
						'label' => __('Country', 'recruit-connect-wp'),
						'name' => 'country',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_salary_minimum',
						'label' => __('Salary Minimum', 'recruit-connect-wp'),
						'name' => 'salary_minimum',
						'type' => 'number', // Changed to 'number' type
					),
					array(
						'key' => 'field_recruit_connect_salary_maximum',
						'label' => __('Salary Maximum', 'recruit-connect-wp'),
						'name' => 'salary_maximum',
						'type' => 'number', // Changed to 'number' type
					),
					array(
						'key' => 'field_recruit_connect_education',
						'label' => __('Education', 'recruit-connect-wp'),
						'name' => 'education',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_jobtype',
						'label' => __('Job Type', 'recruit-connect-wp'),
						'name' => 'jobtype',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_experience',
						'label' => __('Experience', 'recruit-connect-wp'),
						'name' => 'experience',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_remotetype',
						'label' => __('Remote Type', 'recruit-connect-wp'),
						'name' => 'remotetype',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_recruitername',
						'label' => __('Recruiter Name', 'recruit-connect-wp'),
						'name' => 'recruitername',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_recruiteremail',
						'label' => __('Recruiter Email', 'recruit-connect-wp'),
						'name' => 'recruiteremail',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_recruiterimage',
						'label' => __('Recruiter Image', 'recruit-connect-wp'),
						'name' => 'recruiterimage',
						'type' => 'image',
					),
				),
				'location' => array(
					array(
						array(
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'vacancy',
						),
					),
				),
				'menu_order' => 0,
				'position' => 'normal',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
				'active' => true,
				'description' => '',
				'show_in_rest' => 0,
			),

			// Application Fields Group
			array(
				'key' => 'group_recruit_connect_application',
				'title' => __('Application Fields', 'recruit-connect-wp'),
				'fields' => array(
					array(
						'key' => 'field_recruit_connect_application_vacancy_id',
						'label' => __('Vacancy ID', 'recruit-connect-wp'),
						'name' => 'application_vacancy_id',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_application_name',
						'label' => __('Applicant Name', 'recruit-connect-wp'),
						'name' => 'application_name',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_application_email',
						'label' => __('Applicant Email', 'recruit-connect-wp'),
						'name' => 'application_email',
						'type' => 'email',
					),
					array(
						'key' => 'field_recruit_connect_application_phone',
						'label' => __('Applicant Phone', 'recruit-connect-wp'),
						'name' => 'application_phone',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_application_motivation',
						'label' => __('Applicant Motivation', 'recruit-connect-wp'),
						'name' => 'application_motivation',
						'type' => 'textarea',
					),
					array(
						'key' => 'field_recruit_connect_application_cv',
						'label' => __('Applicant CV', 'recruit-connect-wp'),
						'name' => 'application_cv',
						'type' => 'file',
					),
					array(
						'key' => 'field_recruit_connect_application_type',
						'label' => __('Application Type', 'recruit-connect-wp'),
						'name' => 'application_type',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_external_submission_status',
						'label' => __('External Submission Status', 'recruit-connect-wp'),
						'name' => 'external_submission_status',
						'type' => 'text',
					),
					array(
						'key' => 'field_recruit_connect_last_error',
						'label' => __('Last Error', 'recruit-connect-wp'),
						'name' => 'last_error',
						'type' => 'text',
					),
				),
				'location' => array(
					array(
						array(
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'vacancy_application',
						),
					),
				),
				'menu_order' => 0,
				'position' => 'normal',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
				'active' => true,
				'description' => '',
				'show_in_rest' => 0,
			)
		);

		foreach ($field_groups as $field_group) {
			error_log('Recruit Connect WP - register_recruit_connect_acf_fields: Registering field group: ' . $field_group['title']);
			$result = acf_add_local_field_group($field_group);
			if ($result === false) {
				error_log('Recruit Connect WP - register_recruit_connect_acf_fields: acf_add_local_field_group returned FALSE for group: ' . $field_group['title']);
			} else {
				error_log('Recruit Connect WP - register_recruit_connect_acf_fields: Successfully registered field group: ' . $field_group['title']);
			}
		}

		return true;
	}
}
