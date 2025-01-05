<?php
if (!defined('ABSPATH')) exit;

$logs = $this->logger->get_recent_logs(50);
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="rcwp-logs-wrapper">
        <div class="rcwp-logs-controls">
            <button type="button" class="button" id="rcwp-clear-logs">
                <?php _e('Clear Logs', 'recruit-connect-wp'); ?>
            </button>
            <button type="button" class="button" id="rcwp-download-logs">
                <?php _e('Download Logs', 'recruit-connect-wp'); ?>
            </button>
        </div>

        <div class="rcwp-logs-table">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Time', 'recruit-connect-wp'); ?></th>
                        <th><?php _e('Level', 'recruit-connect-wp'); ?></th>
                        <th><?php _e('Message', 'recruit-connect-wp'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr class="log-level-<?php echo esc_attr($log->level); ?>">
                            <td><?php echo esc_html($log->created_at); ?></td>
                            <td><?php echo esc_html(ucfirst($log->level)); ?></td>
                            <td><?php echo esc_html($log->message); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
