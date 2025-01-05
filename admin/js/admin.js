jQuery(document).ready(function($) {
    // Make fields sortable
    $('.rcwp-sortable-fields').sortable({
        handle: '.dashicons-menu',
        update: function(event, ui) {
            var order = $(this).sortable('toArray', { attribute: 'data-field' });
            $.ajax({
                url: rcwpAdmin.ajaxurl,
                method: 'POST',
                data: {
                    action: 'rcwp_update_field_order',
                    order: order,
                    nonce: rcwpAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Optional: Show success message
                    }
                }
            });
        }
    });

    // Handle manual sync
    $('#rcwp-sync-now').on('click', function() {
        var $button = $(this);
        var $spinner = $button.next('.spinner');
        var $status = $('.sync-status');

        $button.prop('disabled', true);
        $spinner.addClass('is-active');
        $status.html('');

        $.ajax({
            url: rcwpAdmin.ajaxurl,
            method: 'POST',
            data: {
                action: 'rcwp_manual_sync',
                nonce: rcwpAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $status.html(rcwpAdmin.strings.syncSuccess).css('color', 'green');
                } else {
                    $status.html(rcwpAdmin.strings.syncError).css('color', 'red');
                }
            },
            error: function() {
                $status.html(rcwpAdmin.strings.syncError).css('color', 'red');
            },
            complete: function() {
                $button.prop('disabled', false);
                $spinner.removeClass('is-active');
            }
        });
    });
});
