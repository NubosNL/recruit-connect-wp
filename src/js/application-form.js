/**
 * Application form handling
 */
(function($) {
    'use strict';

    class ApplicationForm {
        constructor() {
            this.form = $('#rcwp-application-form');
            this.submitBtn = this.form.find('button[type="submit"]');
            this.fileInput = this.form.find('input[type="file"]');
            this.maxFileSize = 5 * 1024 * 1024; // 5MB
            this.allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

            this.bindEvents();
        }

        bindEvents() {
            this.form.on('submit', (e) => this.handleSubmit(e));
            this.fileInput.on('change', (e) => this.handleFileSelect(e));
        }

        async handleSubmit(e) {
            e.preventDefault();

            if (!this.validateForm()) {
                return;
            }

            this.toggleLoading(true);

            try {
                const formData = new FormData(this.form[0]);
                formData.append('action', 'rcwp_submit_application');
                formData.append('nonce', rcwp_vars.nonce);

                const response = await $.ajax({
                    url: rcwp_vars.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false
                });

                if (response.success) {
                    this.showSuccess(response.data.message);
                    this.resetForm();
                } else {
                    this.showError(response.data.message);
                }
            } catch (error) {
                console.error('Application submission error:', error);
                this.showError(rcwp_vars.error_message);
            } finally {
                this.toggleLoading(false);
            }
        }

        validateForm() {
            let isValid = true;
            const requiredFields = this.form.find('[required]');

            // Reset previous errors
            this.removeErrors();

            // Check required fields
            requiredFields.each((_, field) => {
                if (!field.value.trim()) {
                    this.addError(field, rcwp_vars.required_field_message);
                    isValid = false;
                }
            });

            // Validate email
            const emailField = this.form.find('input[type="email"]');
            if (emailField.length && !this.isValidEmail(emailField.val())) {
                this.addError(emailField[0], rcwp_vars.invalid_email_message);
                isValid = false;
            }

            // Validate file
            if (this.fileInput.length && this.fileInput[0].files.length) {
                const file = this.fileInput[0].files[0];

                if (!this.isValidFileType(file)) {
                    this.addError(this.fileInput[0], rcwp_vars.invalid_file_type_message);
                    isValid = false;
                }

                if (!this.isValidFileSize(file)) {
                    this.addError(this.fileInput[0], rcwp_vars.file_too_large_message);
                    isValid = false;
                }
            }

            return isValid;
        }

        handleFileSelect(e) {
            const file = e.target.files[0];
            if (file) {
                if (!this.isValidFileType(file)) {
                    this.addError(e.target, rcwp_vars.invalid_file_type_message);
                    e.target.value = '';
                    return;
                }

                if (!this.isValidFileSize(file)) {
                    this.addError(e.target, rcwp_vars.file_too_large_message);
                    e.target.value = '';
                    return;
                }

                this.removeError(e.target);
            }
        }

        isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        isValidFileType(file) {
            return this.allowedTypes.includes(file.type);
        }

        isValidFileSize(file) {
            return file.size <= this.maxFileSize;
        }

        addError(field, message) {
            $(field).addClass('error');
            $(`<div class="error-message">${message}</div>`)
                .insertAfter(field);
        }

        removeError(field) {
            $(field).removeClass('error')
                .next('.error-message')
                .remove();
        }

        removeErrors() {
            this.form.find('.error').removeClass('error');
            this.form.find('.error-message').remove();
        }

        showSuccess(message) {
            const alert = $(`<div class="rcwp-alert success">${message}</div>`);
            this.form.prepend(alert);
            $('html, body').animate({
                scrollTop: this.form.offset().top - 100
            }, 500);
        }

        showError(message) {
            const alert = $(`<div class="rcwp-alert error">${message}</div>`);
            this.form.prepend(alert);
            $('html, body').animate({
                scrollTop: this.form.offset().top - 100
            }, 500);
        }

        toggleLoading(show) {
            if (show) {
                this.submitBtn.prop('disabled', true)
                    .html('<span class="spinner"></span> ' + rcwp_vars.submitting_message);
            } else {
                this.submitBtn.prop('disabled', false)
                    .html(rcwp_vars.submit_button_text);
            }
        }

        resetForm() {
            this.form[0].reset();
            this.removeErrors();
        }
    }

    // Initialize on document ready
    $(document).ready(() => {
        new ApplicationForm();
    });

})(jQuery);
