<?php
if (!defined('ABSPATH')) exit;
?>
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <form method="post" action="options.php">
		<?php settings_fields('rcwp_settings'); ?>

        <h2 class="nav-tab-wrapper">
            <a href="#general" class="nav-tab nav-tab-active"><?php _e('General', 'recruit-connect-wp'); ?></a>
            <a href="#application" class="nav-tab"><?php _e('Application Form', 'recruit-connect-wp'); ?></a>
            <a href="#sync" class="nav-tab"><?php _e('Synchronization', 'recruit-connect-wp'); ?></a>
        </h2>

        <!-- General Settings -->
        <div id="general" class="tab-content">
			<?php do_settings_sections('rcwp-settings-general'); ?>
        </div>

        <!-- Application Form Settings -->
        <div id="application" class="tab-content" style="display: none;">
			<?php do_settings_sections('rcwp-settings-application'); ?>
        </div>

        <!-- Sync Settings -->
        <div id="sync" class="tab-content" style="display: none;">
			<?php do_settings_sections('rcwp-settings-sync'); ?>
        </div>

		<?php submit_button(); ?>
    </form>
</div>
