<?php
/**
 * Job Type filter component
 */
if (!defined('ABSPATH')) exit;

$job_types = RCWP_Search::get_vacancy_job_types();
?>

<div class="rcwp-filter-group">
    <h4><?php _e('Job Type', 'recruit-connect-wp'); ?></h4>
    <select id="rcwp-jobtype-filter" class="rcwp-filter">
        <option value=""><?php _e('All Job Types', 'recruit-connect-wp'); ?></option>
        <?php foreach ($job_types as $type): ?>
            <option value="<?php echo esc_attr($type); ?>"><?php echo esc_html($type); ?></option>
        <?php endforeach; ?>
    </select>
</div>
