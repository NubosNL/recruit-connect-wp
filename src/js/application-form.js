class ApplicationForm {
    constructor(formElement) {
        this.form = formElement;
        this.fileInput = this.form.querySelector('input[type="file"]');
        this.submitButton = this.form.querySelector('button[type="submit"]');
        this.uploadArea = this.form.querySelector('.upload-area');
        this.filePreview = this.form.querySelector('.file-preview');
        this.successMessage = this.form.querySelector('.rcwp-success-message');

        this.maxFileSize = 5 * 1024 * 1024; // 5MB
        this.allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

        this.initializeEvents();
    }

    initializeEvents() {
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        this.fileInput.addEventListener('change', (e) => this.handleFileSelect(e));

        // Drag and drop handling
        this.uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            this.uploadArea.classList.add('dragover');
        });

        this.uploadArea.addEventListener('dragleave', () => {
            this.uploadArea.classList.remove('dragover');
        });

        this.uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            this.uploadArea.classList.remove('dragover');
            this.handleFileDrop(e);
        });

        // Form validation
        this.form.querySelectorAll('input, textarea').forEach(input => {
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('input', () => this.validateField(input));
        });
    }

    async handleSubmit(e) {
        e.preventDefault();

        if (!this.validateForm()) {
            return;
        }

        this.setLoading(true);

        try {
            const formData = new FormData(this.form);
            const response = await this.submitApplication(formData);

            if (response.success) {
                this.showSuccess(response.message);
            } else {
                this.showError(response.message);
            }
        } catch (error) {
            this.showError('An error occurred while submitting your application.');
            console.error('Application submission error:', error);
        } finally {
            this.setLoading(false);
        }
    }

    async submitApplication(formData) {
        const response = await fetch(rcwpFront.ajaxurl, {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    }

    handleFileSelect(e) {
        const file = e.target.files[0];
        if (file) {
            this.validateAndPreviewFile(file);
        }
    }

    handleFileDrop(e) {
        const file = e.dataTransfer.files[0];
        if (file) {
            this.fileInput.files = e.dataTransfer.files;
            this.validateAndPreviewFile(file);
        }
    }

    validateAndPreviewFile(file) {
        if (!this.isValidFile(file)) {
            return;
        }

        this.showFilePreview(file);
    }

    isValidFile(file) {
        if (!this.allowedTypes.includes(file.type)) {
            this.showError('Please upload a PDF or Word document.');
            return false;
        }

        if (file.size > this.maxFileSize) {
            this.showError('File size should not exceed 5MB.');
            return false;
        }

        return true;
    }

    showFilePreview(file) {
        const fileSize = this.formatFileSize(file.size);
        const fileIcon = this.getFileIcon(file.type);

        this.filePreview.innerHTML = `
            <div class="file-info">
                <span class="file-icon">${fileIcon}</span>
                <span class="file-name">${file.name}</span>
                <span class="file-size">${fileSize}</span>
            </div>
            <span class="remove-file">×</span>
        `;

        this.filePreview.classList.add('visible');
        this.filePreview.querySelector('.remove-file').addEventListener('click', () => {
            this.removeFile();
        });
    }

    removeFile() {
        this.fileInput.value = '';
        this.filePreview.classList.remove('visible');
        setTimeout(() => {
            this.filePreview.innerHTML = '';
        }, 300);
    }

    validateField(input) {
        const field = input.closest('.rcwp-form-field');
        const errorElement = field.querySelector('.field-error');
        let isValid = true;
        let errorMessage = '';

        if (input.required && !input.value) {
            isValid = false;
            errorMessage = 'This field is required';
        } else if (input.type === 'email' && input.value && !this.isValidEmail(input.value)) {
            isValid = false;
            errorMessage = 'Please enter a valid email address';
        }

        input.classList.toggle('error', !isValid);
        if (errorElement) {
            errorElement.textContent = errorMessage;
            errorElement.classList.toggle('visible', !isValid);
        }

        return isValid;
    }

    validateForm() {
        let isValid = true;
        this.form.querySelectorAll('input, textarea').forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });
        return isValid;
    }

    setLoading(loading) {
        this.submitButton.disabled = loading;
        this.submitButton.querySelector('.spinner').classList.toggle('visible', loading);
    }

    showSuccess(message) {
        this.form.querySelector('.rcwp-form-fields').style.display = 'none';
        this.successMessage.innerHTML = `
            <div class="success-icon">✓</div>
            <h4>Application Submitted Successfully!</h4>
            <p>${message}</p>
        `;
        this.successMessage.classList.add('visible');
    }

    showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'rcwp-form-error';
        errorDiv.textContent = message;

        this.form.insertBefore(errorDiv, this.form.firstChild);

        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    getFileIcon(fileType) {
        const icons = {
            'application/pdf': '📄',
            'application/msword': '📝',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document': '📝'
        };
        return icons[fileType] || '📎';
    }

    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
}

// Initialize all application forms on the page
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.rcwp-application-form').forEach(form => {
        new ApplicationForm(form);
    });
});
