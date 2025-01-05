<?php
if (!defined('ABSPATH')) exit;

$backup_enabled = get_option('rcwp_backup_enabled', false);
$last_backup = get_option('rcwp_last_backup', '');
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="rcwp-backup-wrapper">
        <!-- Backup Status -->
        <div class="rcwp-backup-status">
            <h2><?php _e('Backup Status', 'recruit-connect-wp'); ?></h2>
            <table class="widefat">
                <tr>
                    <th><?php _e('Automatic Backup', 'recruit-connect-wp'); ?></th>
                    <td>
						<?php if ($backup_enabled): ?>
                            <span class="status-enabled"><?php _e('Enabled', 'recruit-connect-wp'); ?></span>
						<?php else: ?>
                            <span class="status-disabled"><?php _e('Disabled', 'recruit-connect-wp'); ?></span>
						<?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Last Backup', 'recruit-connect-wp'); ?></th>
                    <td>
						<?php echo $last_backup ? esc_html($last_backup) : __('Never', 'recruit-connect-wp'); ?>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Backup Actions -->
        <div class="rcwp-backup-actions">
            <h2><?php _e('Backup Actions', 'recruit-connect-wp'); ?></h2>
            <p>
                <button type="button" class="button button-primary" id="rcwp-backup-now">
					<?php _e('Backup Now', 'recruit-connect-wp'); ?>
                </button>
                <button type="button" class="button" id="rcwp-export-data">
					<?php _e('Export Data', 'recruit-connect-wp'); ?>
                </button>
            </p>
        </div>

        <!-- Backup History -->
        <div class="rcwp-backup-history">
            <h2><?php _e('Backup History', 'recruit-connect-wp'); ?></h2>
			<?php if (!empty($backup_history)): ?>
                <table class="widefat">
                    <thead>
                    <tr>
                        <th><?php _e('Date', 'recruit-connect-wp'); ?></th>
                        <th><?php _e('Type', 'recruit-connect-wp'); ?></th>
                        <th><?php _e('Status', 'recruit-connect-wp'); ?></th>
                        <th><?php _e('Actions', 'recruit-connect-wp'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
					<?php foreach ($backup_history as $backup): ?>
                        <tr>
                            <td><?php echo esc_html($backup['date']); ?></td>
                            <td><?php echo esc_html($backup['type']); ?></td>
                            <td><?php echo esc_html($backup['status']); ?></td>
                            <td>
                                <a href="<?php echo esc_url($backup['download_url']); ?>" class="button button-small">
									<?php _e('Download', 'recruit-connect-wp'); ?>
                                </a>
                            </td>
                        </tr>
					<?php endforeach; ?>
                    </tbody>
                </table>
			<?php else: ?>
                <p><?php _e('No backup history available.', 'recruit-connect-wp'); ?></p>
			<?php endif; ?>
        </div>

        <!-- Backup Settings -->
        <div class="rcwp-backup-settings">
            <h2><?php _e('Backup Settings', 'recruit-connect-wp'); ?></h2>
            <form method="post" action="options.php">
				<?php
				settings_fields('rcwp_backup_settings');
				do_settings_sections('rcwp_backup_settings');
				submit_button();
				?>
            </form>
        </div>
    </div>
</div>
