<div class="wrap">
    <h1><?php _e('Backup and Restore', 'recruit-connect-wp'); ?></h1>

    <?php if (isset($_GET['message'])): ?>
        <div class="notice notice-success">
            <p><?php
                switch ($_GET['message']) {
                    case 'backup_created':
                        _e('Backup created successfully.', 'recruit-connect-wp');
                        break;
                    case 'backup_restored':
                        _e('Backup restored successfully.', 'recruit-connect-wp');
                        break;
                }
            ?></p>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2><?php _e('Create Backup', 'recruit-connect-wp'); ?></h2>
        <p><?php _e('Create a backup of all vacancies, applications, settings, and uploaded files.', 'recruit-connect-wp'); ?></p>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <?php wp_nonce_field('rcwp_manual_backup'); ?>
            <input type="hidden" name="action" value="rcwp_manual_backup">
            <button type="submit" class="button button-primary">
                <?php _e('Create Backup', 'recruit-connect-wp'); ?>
            </button>
        </form>
    </div>

    <div class="card">
        <h2><?php _e('Available Backups', 'recruit-connect-wp'); ?></h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Filename', 'recruit-connect-wp'); ?></th>
                    <th><?php _e('Size', 'recruit-connect-wp'); ?></th>
                    <th><?php _e('Date', 'recruit-connect-wp'); ?></th>
                    <th><?php _e('Actions', 'recruit-connect-wp'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->backup->get_backups() as $backup): ?>
                    <tr>
                        <td><?php echo esc_html($backup['filename']); ?></td>
                        <td><?php echo esc_html($backup['size']); ?></td>
                        <td><?php echo esc_html($backup['date']); ?></td>
                        <td>
                            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline;">
                                <?php wp_nonce_field('rcwp_restore_backup'); ?>
                                <input type="hidden" name="action" value="rcwp_restore_backup">
                                <input type="hidden" name="backup" value="<?php echo esc_attr($backup['filename']); ?>">
                                <button type="submit" class="button" onclick="return confirm('<?php esc_attr_e('Are you sure you want to restore this backup? This will overwrite all current data.', 'recruit-connect-wp'); ?>')">
                                    <?php _e('Restore', 'recruit-connect-wp'); ?>
                                </button>
                            </form>
                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=rcwp_download_backup&backup=' . urlencode($backup['filename'])), 'rcwp_download_backup')); ?>" class="button">
                                <?php _e('Download', 'recruit-connect-wp'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
