<?php
/**
 * Block template for displaying application form
 *
 * @package    Recruit_Connect_WP
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

// Get block attributes
$className = isset($attributes['className']) ? $attributes['className'] : '';
$vacancy_id = isset($attributes['vacancyId']) ? $attributes['vacancyId'] : get_the_ID();

// Get required fields from settings
$required_fields = get_option('rcwp_required_fields', array(
	'first_name' => true,
	'last_name'  => true,
	'email'      => true,
	'phone'      => true,
	'motivation' => true,
	'resume'     => true
));
?>

<div class="wp-block-rcwp-application-form <?php echo esc_attr($className); ?>">
	<div id="application-form" class="rcwp-application-form">
		<h2><?php _e('Apply for this position', 'recruit-connect-wp'); ?></h2>

		<form id="rcwp-application-form" method="post" enctype="multipart/form-data">
			<input type="hidden" name="vacancy_id" value="<?php echo esc_attr($vacancy_id); ?>">
			<?php wp_nonce_field('rcwp_submit_application', 'rcwp_nonce'); ?>

			<div class="form-row">
				<label for="first_name" <?php echo !empty($required_fields['first_name']) ? 'class="required"' : ''; ?>>
					<?php _e('First Name', 'recruit-connect-wp'); ?>
				</label>
				<input type="text"
				       id="first_name"
				       name="first_name"
					<?php echo !empty($required_fields['first_name']) ? 'required' : ''; ?>>
			</div>

			<div class="form-row">
				<label for="last_name" <?php echo !empty($required_fields['last_name']) ? 'class="required"' : ''; ?>>
					<?php _e('Last Name', 'recruit-connect-wp'); ?>
				</label>
				<input type="text"
				       id="last_name"
				       name="last_name"
					<?php echo !empty($required_fields['last_name']) ? 'required' : ''; ?>>
			</div>

			<div class="form-row">
				<label for="email" <?php echo !empty($required_fields['email']) ? 'class="required"' : ''; ?>>
					<?php _e('Email', 'recruit-connect-wp'); ?>
				</label>
				<input type="email"
				       id="email"
				       name="email"
					<?php echo !empty($required_fields['email']) ? 'required' : ''; ?>>
			</div>

			<div class="form-row">
				<label for="phone" <?php echo !empty($required_fields['phone']) ? 'class="required"' : ''; ?>>
					<?php _e('Phone', 'recruit-connect-wp'); ?>
				</label>
				<input type="tel"
				       id="phone"
				       name="phone"
					<?php echo !empty($required_fields['phone']) ? 'required' : ''; ?>>
			</div>

			<div class="form-row">
				<label for="motivation" <?php echo !empty($required_fields['motivation']) ? 'class="required"' : ''; ?>>
					<?php _e('Motivation', 'recruit-connect-wp'); ?>
				</label>
				<textarea id="motivation"
				          name="motivation"
				          rows="5"
                          <?php echo !empty($required_fields['motivation']) ? 'required' : ''; ?>></textarea>
			</div>

			<div class="form-row">
				<label for="resume" <?php echo !empty($required_fields['resume']) ? 'class="required"' : ''; ?>>
					<?php _e('Resume', 'recruit-connect-wp'); ?>
				</label>
				<div class="file-upload">
					<input type="file"
					       id="resume"
					       name="resume"
					       accept=".pdf,.doc,.docx"
						<?php echo !empty($required_fields['resume']) ? 'required' : ''; ?>>
					<label for="resume" class="file-upload-button">
						<?php _e('Choose File', 'recruit-connect-wp'); ?>
					</label>
					<span class="file-name"></span>
				</div>
			</div>

			<div class="form-row">
				<button type="submit" class="rcwp-button rcwp-button-primary">
					<?php _e('Submit Application', 'recruit-connect-wp'); ?>
				</button>
			</div>
		</form>
	</div>
</div>
