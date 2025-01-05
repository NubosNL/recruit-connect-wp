<?php
/**
 * Salary filter component
 */
if (!defined('ABSPATH')) exit;

$salary_range = RCWP_Search::get_vacancy_salary_range();
?>

<div class="rcwp-filter-group">
    <h4><?php _e('Salary Range', 'recruit-connect-wp'); ?></h4>
    <div id="rcwp-salary-slider"
         data-min="<?php echo esc_attr($salary_range['min']); ?>"
         data-max="<?php echo esc_attr($salary_range['max']); ?>">
    </div>
    <div class="salary-inputs">
        <input type="number" id="salary-min" readonly>
        <input type="number" id="salary-max" readonly>
    </div>
</div>
