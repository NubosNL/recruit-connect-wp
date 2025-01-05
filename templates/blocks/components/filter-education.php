<?php
/**
 * Education filter component
 */
if (!defined('ABSPATH')) exit;

$education_levels = RCWP_Search::get_vacancy_education_levels();
?>

<div class="rcwp-filter-group">
    <h4><?php _e('Education', 'recruit-connect-wp'); ?></h4>
    <select id="rcwp-education-filter" class="rcwp-filter">
        <option value=""><?php _e('All Education Levels', 'recruit-connect-wp'); ?></option>
        <?php foreach ($education_levels as $level): ?>
            <option value="<?php echo esc_attr($level); ?>"><?php echo esc_html($level); ?></option>
        <?php endforeach; ?>
    </select>
</div>
