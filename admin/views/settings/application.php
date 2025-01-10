<?php
if (!defined('ABSPATH')) {
	exit;
}

// Ensure we have an array for required fields
$required_fields = get_option('recruit_connect_required_fields', array());
if (!is_array($required_fields)) {
	$required_fields = array();
}
?>

<table class="form-table" role="presentation">
    <tr>
        <th scope="row">
            <label for="recruit_connect_thank_you_message">
				<?php echo esc_html__('Thank You Message', 'recruit-connect-wp'); ?>
            </label>
        </th>
        <td>
			<?php
			wp_editor(
				get_option('recruit_connect_thank_you_message', ''),
				'recruit_connect_thank_you_message',
				array(
					'textarea_name' => 'recruit_connect_thank_you_message',
					'textarea_rows' => 5,
					'media_buttons' => false,
					'teeny' => true,
					'quicktags' => false
				)
			);
			?>
            <p class="description">
				<?php echo esc_html__('Message shown after successful application submission', 'recruit-connect-wp'); ?>
            </p>
        </td>
    </tr>

    <tr>
        <th scope="row">
			<?php echo esc_html__('Required Fields', 'recruit-connect-wp'); ?>
        </th>
        <td>
            <fieldset>
				<?php
				$fields = array(
					'name' => __('Full Name', 'recruit-connect-wp'),
					'email' => __('Email Address', 'recruit-connect-wp'),
					'phone' => __('Phone Number', 'recruit-connect-wp'),
					'cv' => __('CV Upload', 'recruit-connect-wp'),
					'motivation' => __('Motivation Letter', 'recruit-connect-wp')
				);

				foreach ($fields as $value => $label): ?>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="checkbox"
                               name="recruit_connect_required_fields[]"
                               value="<?php echo esc_attr($value); ?>"
							<?php checked(in_array($value, $required_fields, true)); ?>>
						<?php echo esc_html($label); ?>
                    </label>
				<?php endforeach; ?>
            </fieldset>
            <p class="description">
				<?php echo esc_html__('Select which fields should be required in the application form', 'recruit-connect-wp'); ?>
            </p>
        </td>
    </tr>
</table>
