<?php
if (!defined('ABSPATH')) {
	exit;
}

$logs = get_option('recruit_connect_logs', array());
?>

<table class="form-table" role="presentation">
    <tr>
        <th scope="row">
            <label for="recruit_connect_sync_frequency">
				<?php echo esc_html__('XML Check Frequency', 'recruit-connect-wp'); ?>
            </label>
        </th>
        <td>
            <select name="recruit_connect_sync_frequency" id="recruit_connect_sync_frequency">
				<?php
				$current_frequency = get_option('recruit_connect_sync_frequency', 'daily');
				$frequencies = array(
					'hourly' => __('Hourly', 'recruit-connect-wp'),
					'twicedaily' => __('Twice Daily', 'recruit-connect-wp'),
					'daily' => __('Daily', 'recruit-connect-wp'),
					'fourhourly' => __('Every 4 Hours', 'recruit-connect-wp')
				);
				foreach ($frequencies as $value => $label) {
					printf(
						'<option value="%s" %s>%s</option>',
						esc_attr($value),
						selected($current_frequency, $value, false),
						esc_html($label)
					);
				}
				?>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php echo esc_html__('Manual Sync', 'recruit-connect-wp'); ?></th>
        <td>
            <button type="button" class="button button-primary" id="recruit-connect-sync-now">
				<?php echo esc_html__('Sync Now', 'recruit-connect-wp'); ?>
            </button>
            <span class="spinner" style="float: none; margin-left: 4px;"></span>
            <p class="sync-message"></p>
        </td>
    </tr>
</table>

<div class="recruit-connect-logs">
    <h3><?php echo esc_html__('Recent Logs', 'recruit-connect-wp'); ?></h3>
	<?php if (!empty($logs)): ?>
        <div class="log-entries" style="max-height: 300px; overflow-y: auto; margin-top: 10px;">
			<?php foreach ($logs as $log): ?>
                <div class="log-entry" style="margin-bottom: 5px;">
                    <span class="log-time"><?php echo esc_html($log['timestamp']); ?></span>:
                    <span class="log-message"><?php echo esc_html($log['message']); ?></span>
                </div>
			<?php endforeach; ?>
        </div>
	<?php else: ?>
        <p><?php echo esc_html__('No logs available.', 'recruit-connect-wp'); ?></p>
	<?php endif; ?>
</div>

<script>
    jQuery(document).ready(function($) {
        $('#recruit-connect-sync-now').on('click', function() {
            const button = $(this);
            const spinner = button.next('.spinner');
            const message = $('.sync-message');

            button.prop('disabled', true);
            spinner.addClass('is-active');
            message.html('');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'recruit_connect_sync_now',
                    nonce: '<?php echo wp_create_nonce('recruit_connect_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        message.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                        // Reload page after 2 seconds to show updated logs
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        message.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                    }
                },
                error: function() {
                    message.html('<div class="notice notice-error"><p><?php echo esc_js(__('Connection error occurred', 'recruit-connect-wp')); ?></p></div>');
                },
                complete: function() {
                    button.prop('disabled', false);
                    spinner.removeClass('is-active');
                }
            });
        });
    });
</script>
