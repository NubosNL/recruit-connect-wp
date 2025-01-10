<div class="recruit-connect-logs">
    <?php
    $logs = get_option('recruit_connect_logs', array());
    if (!empty($logs)): ?>
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php echo esc_html__('Time', 'recruit-connect-wp'); ?></th>
                    <th><?php echo esc_html__('Message', 'recruit-connect-wp'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo esc_html($log['timestamp']); ?></td>
                        <td><?php echo esc_html($log['message']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p>
            <button type="button"
                    class="button"
                    id="clear-logs">
                <?php echo esc_html__('Clear Logs', 'recruit-connect-wp'); ?>
            </button>
        </p>
    <?php else: ?>
        <p><?php echo esc_html__('No logs available.', 'recruit-connect-wp'); ?></p>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    $('#clear-logs').on('click', function() {
        if (confirm('<?php echo esc_js(__('Are you sure you want to clear all logs?', 'recruit-connect-wp')); ?>')) {
            $.post(ajaxurl, {
                action: 'recruit_connect_clear_logs',
                nonce: recruitConnect.nonce
            }, function(response) {
                if (response.success) {
                    location.reload();
                }
            });
        }
    });
});
</script>
