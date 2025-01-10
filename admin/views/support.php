<div class="wrap">
    <h1><?php echo esc_html__('Recruit Connect Support', 'recruit-connect-wp'); ?></h1>

    <div class="recruit-connect-support-form">
        <form method="post" id="support-form">
			<?php wp_nonce_field('recruit_connect_support', 'support_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="support_name"><?php echo esc_html__('Your Name', 'recruit-connect-wp'); ?></label>
                    </th>
                    <td>
                        <input type="text"
                               name="support_name"
                               id="support_name"
                               class="regular-text"
                               required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="support_email"><?php echo esc_html__('Your Email', 'recruit-connect-wp'); ?></label>
                    </th>
                    <td>
                        <input type="email"
                               name="support_email"
                               id="support_email"
                               class="regular-text"
                               value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>"
                               required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="support_subject"><?php echo esc_html__('Subject', 'recruit-connect-wp'); ?></label>
                    </th>
                    <td>
                        <input type="text"
                               name="support_subject"
                               id="support_subject"
                               class="regular-text"
                               required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="support_message"><?php echo esc_html__('Message', 'recruit-connect-wp'); ?></label>
                    </th>
                    <td>
                        <textarea name="support_message"
                                  id="support_message"
                                  class="large-text"
                                  rows="10"
                                  required></textarea>
                    </td>
                </tr>
            </table>

            <div id="support-message" class="notice" style="display: none;"></div>

            <p class="submit">
                <button type="submit" class="button button-primary" id="send-support">
					<?php echo esc_html__('Send Support Request', 'recruit-connect-wp'); ?>
                </button>
                <span class="spinner" style="float: none; margin: 0 10px;"></span>
            </p>
        </form>
    </div>

    <script>
        jQuery(document).ready(function($) {
            $('#support-form').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const button = form.find('#send-support');
                const spinner = form.find('.spinner');
                const messageDiv = $('#support-message');

                // Reset message
                messageDiv.removeClass('notice-success notice-error').hide();

                // Disable button and show spinner
                button.prop('disabled', true);
                spinner.addClass('is-active');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'recruit_connect_send_support',
                        nonce: $('#support_nonce').val(),
                        name: $('#support_name').val(),
                        email: $('#support_email').val(),
                        subject: $('#support_subject').val(),
                        message: $('#support_message').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            messageDiv.addClass('notice-success')
                                .html('<p>' + response.data.message + '</p>')
                                .show();
                            form[0].reset();
                        } else {
                            messageDiv.addClass('notice-error')
                                .html('<p>' + response.data.message + '</p>')
                                .show();
                        }
                    },
                    error: function() {
                        messageDiv.addClass('notice-error')
                            .html('<p><?php echo esc_js(__('Connection error occurred. Please try again.', 'recruit-connect-wp')); ?></p>')
                            .show();
                    },
                    complete: function() {
                        button.prop('disabled', false);
                        spinner.removeClass('is-active');
                    }
                });
            });
        });
    </script>
</div>
