// js/application-form.js
jQuery(document).ready(function($) {
    $('#application-form').on('submit', function(e) {
        e.preventDefault();

        if (!this.checkValidity()) {
            e.stopPropagation();
            $(this).addClass('was-validated');
            return;
        }

        var formData = new FormData(this);
        formData.append('action', 'submit_application');
        formData.append('nonce', applicationAjax.nonce);

        $.ajax({
            url: applicationAjax.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#application-form').hide();
                    $('#thank-you-message').show();
                }
            },
            error: function() {
                alert('Er is een fout opgetreden. Probeer het later opnieuw.');
            }
        });
    });
});
