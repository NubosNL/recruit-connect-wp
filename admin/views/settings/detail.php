<?php
if (!defined('ABSPATH')) {
	exit;
}

// Ensure we have arrays for our options
$enabled_fields = get_option('recruit_connect_detail_fields', array());
if (!is_array($enabled_fields)) {
	$enabled_fields = array();
}

$fields_order = get_option('recruit_connect_fields_order', array());
if (!is_array($fields_order)) {
	$fields_order = array();
}

// Define available fields
$available_fields = array(
	'description' => __('Description', 'recruit-connect-wp'),
	'company' => __('Company', 'recruit-connect-wp'),
	'location' => __('Location', 'recruit-connect-wp'),
	'salary' => __('Salary', 'recruit-connect-wp'),
	'education' => __('Education', 'recruit-connect-wp'),
	'experience' => __('Experience', 'recruit-connect-wp'),
	'jobtype' => __('Job Type', 'recruit-connect-wp'),
	'recruiter' => __('Recruiter Information', 'recruit-connect-wp')
);
?>

<table class="form-table" role="presentation">
    <tr>
        <th scope="row">
			<?php echo esc_html__('Enabled Fields', 'recruit-connect-wp'); ?>
        </th>
        <td>
            <fieldset>
				<?php foreach ($available_fields as $value => $label): ?>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="checkbox"
                               name="recruit_connect_detail_fields[]"
                               value="<?php echo esc_attr($value); ?>"
							<?php checked(in_array($value, $enabled_fields, true)); ?>>
						<?php echo esc_html($label); ?>
                    </label>
				<?php endforeach; ?>
            </fieldset>
            <p class="description">
				<?php echo esc_html__('Select which fields should be displayed on the vacancy detail page', 'recruit-connect-wp'); ?>
            </p>
        </td>
    </tr>

    <tr>
        <th scope="row">
			<?php echo esc_html__('Field Order', 'recruit-connect-wp'); ?>
        </th>
        <td>
            <div id="sortable-fields" class="sortable-list">
				<?php
				// If fields_order is empty, use enabled_fields or all available fields
				$ordered_fields = !empty($fields_order) ? $fields_order : array_keys($available_fields);
				foreach ($ordered_fields as $field) {
					if (isset($available_fields[$field])) {
						echo sprintf(
							'<div class="sortable-item" data-field="%s">
                                <span class="dashicons dashicons-menu"></span>
                                <span class="field-label">%s</span>
                                <input type="hidden" name="recruit_connect_fields_order[]" value="%s">
                            </div>',
							esc_attr($field),
							esc_html($available_fields[$field]),
							esc_attr($field)
						);
					}
				}
				?>
            </div>
            <p class="description">
				<?php echo esc_html__('Drag and drop to reorder fields', 'recruit-connect-wp'); ?>
            </p>
        </td>
    </tr>
</table>

<style>
    .sortable-list {
        max-width: 500px;
        border: 1px solid #ccd0d4;
        background: #fff;
    }

    .sortable-item {
        padding: 10px;
        background: #f8f9fa;
        border-bottom: 1px solid #ccd0d4;
        cursor: move;
        display: flex;
        align-items: center;
    }

    .sortable-item:last-child {
        border-bottom: none;
    }

    .sortable-item .dashicons {
        margin-right: 10px;
        color: #666;
    }

    .sortable-item.ui-sortable-helper {
        background: #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.15);
    }
</style>

<script>
    jQuery(document).ready(function($) {
        $('#sortable-fields').sortable({
            handle: '.dashicons-menu',
            axis: 'y',
            update: function(event, ui) {
                // Optional: Add animation when updating order
                ui.item.addClass('updated').delay(300).queue(function() {
                    $(this).removeClass('updated').dequeue();
                });
            }
        });
    });
</script>
