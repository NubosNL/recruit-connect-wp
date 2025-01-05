<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f5f5f5;
            padding: 20px;
            text-align: center;
            margin-bottom: 30px;
        }
        .content {
            padding: 20px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php _e('Application Received', 'recruit-connect-wp'); ?></h1>
        </div>

        <div class="content">
            <p><?php printf(
                __('Dear %s %s,', 'recruit-connect-wp'),
                esc_html($data['first_name']),
                esc_html($data['last_name'])
            ); ?></p>

            <p><?php _e('Thank you for submitting your application for the position of:', 'recruit-connect-wp'); ?><br>
            <strong><?php echo get_the_title($data['vacancy_id']); ?></strong></p>

            <p><?php _e('We have received your application and will review it shortly. Here\'s a summary of your submission:', 'recruit-connect-wp'); ?></p>

            <ul>
                <li><strong><?php _e('Position:', 'recruit-connect-wp'); ?></strong>
                    <?php echo get_the_title($data['vacancy_id']); ?></li>
                <li><strong><?php _e('Name:', 'recruit-connect-wp'); ?></strong>
                    <?php echo esc_html($data['first_name'] . ' ' . $data['last_name']); ?></li>
                <li><strong><?php _e('Email:', 'recruit-connect-wp'); ?></strong>
                    <?php echo esc_html($data['email']); ?></li>
                <?php if (!empty($data['phone'])): ?>
                <li><strong><?php _e('Phone:', 'recruit-connect-wp'); ?></strong>
                    <?php echo esc_html($data['phone']); ?></li>
                <?php endif; ?>
            </ul>

            <p><?php _e('What happens next?', 'recruit-connect-wp'); ?></p>
            <ol>
                <li><?php _e('Our team will review your application', 'recruit-connect-wp'); ?></li>
                <li><?php _e('We will contact you if your profile matches our requirements', 'recruit-connect-wp'); ?></li>
                <li><?php _e('The review process typically takes 5-7 business days', 'recruit-connect-wp'); ?></li>
            </ol>

            <p><?php _e('If you have any questions, please don\'t hesitate to contact us.', 'recruit-connect-wp'); ?></p>
        </div>

        <div class="footer">
            <p><?php echo esc_html(get_option('blogname')); ?><br>
            <?php echo esc_html(get_option('rcwp_company_address', '')); ?></p>
        </div>
    </div>
</body>
</html>
