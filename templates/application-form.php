<?php
// Check if direct access
if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="recruit-connect-application-form" id="applicationForm">
	<?php if ($vacancy_id): ?>
		<div class="vacancy-details">
			<h3><?php esc_html_e('Application for:', 'recruit-connect-wp'); ?></h3>
			<div class="vacancy-summary">
				<h4><?php echo esc_html($vacancy_details['title']); ?></h4>
				<?php if (!empty($vacancy_details['company'])): ?>
					<p class="company"><?php echo esc_html($vacancy_details['company']); ?></p>
				<?php endif; ?>
				<?php if (!empty($vacancy_details['location'])): ?>
					<p class="location"><?php echo esc_html($vacancy_details['location']); ?></p>
				<?php endif; ?>
			</div>
		</div>
	<?php else: ?>
		<div class="open-application-notice">
			<h3><?php esc_html_e('Open Application', 'recruit-connect-wp'); ?></h3>
			<p><?php esc_html_e('Submit your application for future opportunities.', 'recruit-connect-wp'); ?></p>
		</div>
	<?php endif; ?>

	<form method="post" enctype="multipart/form-data">
		<?php wp_nonce_field('recruit_connect_application', 'application_nonce'); ?>
		<input type="hidden" name="vacancy_id" value="<?php echo esc_attr($vacancy_id); ?>">

        <div class="form-row">
            <label for="name"><?php esc_html_e('Full Name', 'recruit-connect-wp'); ?><?php echo in_array('name', $required_fields) ? ' *' : ''; ?></label>
            <input type="text"
                   name="name"
                   id="name"
				<?php echo in_array('name', $required_fields) ? 'required' : ''; ?>>
            <div class="error-message"></div>
        </div>

        <div class="form-row">
            <label for="email"><?php esc_html_e('Email Address', 'recruit-connect-wp'); ?><?php echo in_array('email', $required_fields) ? ' *' : ''; ?></label>
            <input type="email"
                   name="email"
                   id="email"
				<?php echo in_array('email', $required_fields) ? 'required' : ''; ?>>
            <div class="error-message"></div>
        </div>

        <div class="form-row">
            <label for="phone"><?php esc_html_e('Phone Number', 'recruit-connect-wp'); ?><?php echo in_array('phone', $required_fields) ? ' *' : ''; ?></label>
            <input type="tel"
                   name="phone"
                   id="phone"
				<?php echo in_array('phone', $required_fields) ? 'required' : ''; ?>>
            <div class="error-message"></div>
        </div>

        <div class="form-row">
            <label for="cv"><?php esc_html_e('CV Upload', 'recruit-connect-wp'); ?><?php echo in_array('cv', $required_fields) ? ' *' : ''; ?></label>
            <input type="file"
                   name="cv"
                   id="cv"
                   accept=".pdf,.doc,.docx"
				<?php echo in_array('cv', $required_fields) ? 'required' : ''; ?>>
            <div class="file-info"><?php esc_html_e('Accepted formats: PDF, DOC, DOCX (max 10MB)', 'recruit-connect-wp'); ?></div>
            <div class="error-message"></div>
        </div>

        <div class="form-row">
            <label for="motivation"><?php esc_html_e('Motivation Letter', 'recruit-connect-wp'); ?><?php echo in_array('motivation', $required_fields) ? ' *' : ''; ?></label>
            <textarea name="motivation"
                      id="motivation"
                      rows="5"
                      <?php echo in_array('motivation', $required_fields) ? 'required' : ''; ?>></textarea>
            <div class="error-message"></div>
        </div>

        <div class="form-message" style="display: none;"></div>

        <div class="form-submit">
            <button type="submit" class="button button-primary">
				<?php esc_html_e('Submit Application', 'recruit-connect-wp'); ?>
            </button>
            <span class="spinner"></span>
        </div>
	</form>
</div>

<style>
    /* Add styles for vacancy details and open application notice */
    .vacancy-details,
    .open-application-notice {
        margin-bottom: 2em;
        padding: 1.5em;
        background: #f8f9fa;
        border-radius: 4px;
    }

    .vacancy-details h3,
    .open-application-notice h3 {
        margin: 0 0 1em;
        font-size: 1.2em;
        color: #333;
    }

    .vacancy-summary h4 {
        margin: 0 0 0.5em;
        color: #007bff;
    }

    .vacancy-summary p {
        margin: 0.25em 0;
        color: #666;
    }

    .open-application-notice p {
        margin: 0;
        color: #666;
    }

    /* Rest of the styles remain the same */
    /* ... */
</style>
