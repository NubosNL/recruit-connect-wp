<?php
/**
 * Salary meta component
 */
if (!defined('ABSPATH')) exit;

$salary = get_post_meta($args['vacancy_id'], '_vacancy_salary', true);
if ($salary): ?>
    <div class="meta-item salary">
        <i class="fas fa-euro-sign"></i>
        <span><?php echo esc_html($salary); ?></span>
    </div>
<?php endif; ?>
