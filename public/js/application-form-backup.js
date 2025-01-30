(function($) {
    'use strict';

    class ApplicationForm {
        constructor(form) {
            this.form = form;
            this.spinner = form.find('.spinner');
            this.submitButton = form.find('button[type="submit"]');
            this.messageContainer = form.find('.form-message');
            this.bindEvents();
        }

        bindEvents() {
            this.form.on('submit', (e) => this.handleSubmit(e));

            // File input validation
            this.form.find('input[type="file"]').on('change', (e) => this.validateFile(e));

            // Clear errors on input
            this.form.find('input, textarea').on('input', (e) => {
                $(e.target).closest('.form-row')
                    .find('.error-message')
                    .removeClass('show')
                    .text('');
            });
        }

        validateFile(e) {
            const file = e.target.files[0];
            const errorContainer = $(e.target).closest('.form-row').find('.error-message');

            if (!file) return;

            // Check file size (10MB)
            if (file.size > 10 * 1024 * 1024) {
                e.target.value = '';
                errorContainer.text(recruitConnectApp.strings.file_size).addClass('show');
                return false;
            }

            // Check file type
            const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            if (!allowedTypes.includes(file.type)) {
                e.target.value = '';
                errorContainer.text(recruitConnectApp.strings.file_type).addClass('show');
                return false;
            }

            errorContainer.removeClass('show').text('');
            return true;
        }

        handleSubmit(e) {
            e.preventDefault();

            // Clear previous messages
            this.clearMessages();

            // Show loading state
            this.setLoading(true);

            // Create FormData object
            const formData = new FormData(this.form[0]);
            formData.append('action', 'recruit_connect_submit_application');
            formData.append('nonce', recruitConnectApp.nonce);

            // Submit form
            $.ajax({
                url: recruitConnectApp.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => this.handleResponse(response),
                error: () => this.handleError(),
                complete: () => this.setLoading(false)
            });
        }

        handleResponse(response) {
            if (response.success) {
                this.showMessage(response.data.message, 'success');
                this.form[0].reset();
            } else {
                if (response.data.errors) {
                    this.showFieldErrors(response.data.errors);
                } else {
                    this.showMessage(response.data.message || 'Submission failed', 'error');
                }
            }
        }

        handleError() {
            this.showMessage('Connection error occurred. Please try again.', 'error');
        }

        showFieldErrors(errors) {
            Object.entries(errors).forEach(([field, message]) => {
                this.form.find(`[name="${field}"]`)
                    .closest('.form-row')
                    .find('.error-message')
                    .text(message)
                    .addClass('show');
            });
        }

        showMessage(message, type) {
            this.messageContainer
                .removeClass('success error')
                .addClass(type)
                .html(message)
                .show();

            if (type === 'success') {
                $('html, body').animate({
                    scrollTop: this.messageContainer.offset().top - 100
                }, 500);
            }
        }

        clearMessages() {
            this.messageContainer.removeClass('success error').hide();
            this.form.find('.error-message').removeClass('show').text('');
        }

        setLoading(isLoading) {
            this.submitButton.prop('disabled', isLoading);
            this.spinner.toggleClass('show', isLoading);
        }
    }

    // Initialize forms
    $(document).ready(function() {
        $('.recruit-connect-application-form form').each(function() {
            new ApplicationForm($(this));
        });
    });

})(jQuery);
