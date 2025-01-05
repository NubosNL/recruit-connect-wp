<?php
if (!defined('ABSPATH')) exit;
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="rcwp-api-wrapper">
        <!-- API Settings -->
        <div class="rcwp-settings-section">
            <h2><?php _e('API Configuration', 'recruit-connect-wp'); ?></h2>
            <form method="post" action="options.php">
                <?php
                settings_fields('rcwp_api_settings');
                do_settings_sections('rcwp_api_settings');
                submit_button();
                ?>
            </form>
        </div>

        <!-- API Documentation -->
        <div class="rcwp-api-docs">
            <h2><?php _e('API Documentation', 'recruit-connect-wp'); ?></h2>
            <!-- Add API documentation content -->
        </div>
    </div>
</div>
