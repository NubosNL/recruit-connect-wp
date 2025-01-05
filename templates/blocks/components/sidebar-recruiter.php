<?php
/**
 * Recruiter sidebar component
 */
if (!defined('ABSPATH')) exit;

$recruiter_name = get_post_meta($args['vacancy_id'], '_vacancy_recruitername', true);
$recruiter_email = get_post_meta($args['vacancy_id'], '_vacancy_recruiteremail', true);
$recruiter_image = get_post_meta($args['vacancy_id'], '_vacancy_recruiterimage', true);

if ($recruiter_name || $recruiter_email): ?>
    <div class="sidebar-section recruiter-info">
        <h3><?php _e('Recruiter', 'recruit-connect-wp'); ?></h3>
        <?php if ($recruiter_image): ?>
            <img src="<?php echo esc_url($recruiter_image); ?>"
                 alt="<?php echo esc_attr($recruiter_name); ?>"
                 class="recruiter-image">
        <?php endif; ?>
        <?php if ($recruiter_name): ?>
            <p class="recruiter-name"><?php echo esc_html($recruiter_name); ?></p>
        <?php endif; ?>
        <?php if ($recruiter_email): ?>
            <p class="recruiter-email">
                <a href="mailto:<?php echo esc_attr($recruiter_email); ?>">
                    <?php echo esc_html($recruiter_email); ?>
                </a>
            </p>
        <?php endif; ?>
    </div>
<?php endif; ?>
