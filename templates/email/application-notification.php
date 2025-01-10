<?php
$application_id = isset($application_id) ? $application_id : 0;
$vacancy_id = get_post_meta($application_id, '_application_vacancy_id', true);
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f8f9fa; padding: 20px; margin-bottom: 20px; }
        .content { padding: 20px; }
        .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><?php echo esc_html__('New Job Application', 'recruit-connect-wp'); ?></h2>
        </div>

        <div class="content">
            <p><strong><?php echo esc_html__('Vacancy:', 'recruit-connect-wp'); ?></strong>
                <?php echo esc_html(get_the_title($vacancy_id)); ?>
            </p>

            <p><strong><?php echo esc_html__('Applicant Details:', 'recruit-connect-wp'); ?></strong></p>
            <ul>
                <li><strong><?php echo esc_html__('Name:', 'recruit-connect-wp'); ?></strong>
                    <?php echo esc_html(get_post_meta($application_id, '_application_name', true)); ?>
                </li>
                <li><strong><?php echo esc_html__('Email:', 'recruit-connect-wp'); ?></strong>
                    <?php echo esc_html(get_post_meta($application_id, '_application_email', true)); ?>
                </li>
                <li><strong><?php echo esc_html__('Phone:', 'recruit-connect-wp'); ?></strong>
                    <?php echo esc_html(get_post_meta($application_id, '_application_phone', true)); ?>
                </li>
            </ul>

            <p><strong><?php echo esc_html__('Message:', 'recruit-connect-wp'); ?></strong></p>
            <p><?php echo nl2br(esc_html(get_post_meta($application_id, '_application_message', true))); ?></p>

            <?php
            $cv_url = get_post_meta($application_id, '_application_cv', true);
            if (!empty($cv_url)):
            ?>
                <p><strong><?php echo esc_html__('CV:', 'recruit-connect-wp'); ?></strong>
                    <a href="<?php echo esc_url($cv_url); ?>"><?php echo esc_html__('Download CV', 'recruit-connect-wp'); ?></a>
                </p>
            <?php endif; ?>
        </div>

        <div class="footer">
            <p><em><?php echo esc_html__('This email was sent from your Recruit Connect WP Plugin', 'recruit-connect-wp'); ?></em></p>
        </div>
    </div>
</body>
</html>
