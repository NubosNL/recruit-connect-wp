<?php
if (!defined('ABSPATH')) {
	exit;
}

// Ensure $active_tab is set
if (!isset($active_tab)) {
	$active_tab = 'general';
}
?>
<div class="wrap">
    <h1><?php echo esc_html__('Recruit Connect Settings', 'recruit-connect-wp'); ?></h1>

    <h2 class="nav-tab-wrapper">
        <a href="?page=recruit-connect-settings&tab=general"
           class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">
			<?php echo esc_html__('General', 'recruit-connect-wp'); ?>
        </a>
        <a href="?page=recruit-connect-settings&tab=application"
           class="nav-tab <?php echo $active_tab == 'application' ? 'nav-tab-active' : ''; ?>">
			<?php echo esc_html__('Application Form', 'recruit-connect-wp'); ?>
        </a>
        <a href="?page=recruit-connect-settings&tab=sync"
           class="nav-tab <?php echo $active_tab == 'sync' ? 'nav-tab-active' : ''; ?>">
			<?php echo esc_html__('Synchronization', 'recruit-connect-wp'); ?>
        </a>
        <a href="?page=recruit-connect-settings&tab=logs"
           class="nav-tab <?php echo $active_tab == 'logs' ? 'nav-tab-active' : ''; ?>">
			<?php echo esc_html__('Logs', 'recruit-connect-wp'); ?>
        </a>
    </h2>

    <form method="post" action="options.php">
		<?php
		// Output all settings fields that need to be preserved
		$all_settings = array(
			'recruit_connect_xml_url',
			'recruit_connect_application_url',
			'recruit_connect_detail_param',
			'recruit_connect_search_components',
			'recruit_connect_thank_you_message',
			'recruit_connect_required_fields',
			'recruit_connect_sync_frequency',
		);

		// Add hidden fields for all settings to preserve their values
		foreach ($all_settings as $setting) {
			$value = get_option($setting);
			if (is_array($value)) {
				foreach ($value as $val) {
					echo '<input type="hidden" name="' . esc_attr($setting) . '[]" value="' . esc_attr($val) . '">';
				}
			} else {
				echo '<input type="hidden" name="' . esc_attr($setting) . '" value="' . esc_attr($value) . '">';
			}
		}

		settings_fields('recruit_connect_settings');

		switch($active_tab) {
			case 'general':
				require_once RECRUIT_CONNECT_PLUGIN_DIR . 'admin/views/settings/general.php';
				break;
			case 'application':
				require_once RECRUIT_CONNECT_PLUGIN_DIR . 'admin/views/settings/application.php';
				break;
			case 'sync':
				require_once RECRUIT_CONNECT_PLUGIN_DIR . 'admin/views/settings/sync.php';
				break;
			case 'logs':
				require_once RECRUIT_CONNECT_PLUGIN_DIR . 'admin/views/settings/logs.php';
				break;
		}

		if ($active_tab !== 'logs') {
			submit_button();
		}
		?>
    </form>
</div>
