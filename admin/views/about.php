<div class="wrap">
    <h1><?php echo esc_html__('About Recruit Connect', 'recruit-connect-wp'); ?></h1>

    <div class="recruit-connect-about">
        <div class="about-header">
            <img src="<?php echo RECRUIT_CONNECT_PLUGIN_URL; ?>admin/images/logo.png"
                 alt="Recruit Connect Logo"
                 class="about-logo">
            <h2><?php echo esc_html__('Welcome to Recruit Connect', 'recruit-connect-wp'); ?></h2>
        </div>

        <div class="about-section">
            <h3><?php echo esc_html__('About Nubos B.V.', 'recruit-connect-wp'); ?></h3>
            <p><?php echo esc_html__('Nubos B.V. is a leading provider of recruitment software solutions, helping businesses streamline their recruitment processes and improve hiring efficiency.', 'recruit-connect-wp'); ?></p>
        </div>

        <div class="about-section">
            <h3><?php echo esc_html__('Recruit Connect Features', 'recruit-connect-wp'); ?></h3>
            <ul>
                <li><?php echo esc_html__('Automated XML vacancy import', 'recruit-connect-wp'); ?></li>
                <li><?php echo esc_html__('Customizable application forms', 'recruit-connect-wp'); ?></li>
                <li><?php echo esc_html__('Advanced search functionality', 'recruit-connect-wp'); ?></li>
                <li><?php echo esc_html__('Detailed vacancy pages', 'recruit-connect-wp'); ?></li>
                <li><?php echo esc_html__('Integration with your existing website', 'recruit-connect-wp'); ?></li>
            </ul>
        </div>

        <div class="about-section">
            <h3><?php echo esc_html__('Useful Links', 'recruit-connect-wp'); ?></h3>
            <p>
                <a href="https://www.nubos.nl/en/recruit-connect"
                   target="_blank"
                   class="button button-primary">
                    <?php echo esc_html__('Visit Product Website', 'recruit-connect-wp'); ?>
                </a>
                <a href="https://www.nubos.nl/en/contact"
                   target="_blank"
                   class="button">
                    <?php echo esc_html__('Contact Us', 'recruit-connect-wp'); ?>
                </a>
            </p>
        </div>

        <div class="about-section">
            <h3><?php echo esc_html__('Plugin Information', 'recruit-connect-wp'); ?></h3>
            <table class="widefat">
                <tr>
                    <th><?php echo esc_html__('Version', 'recruit-connect-wp'); ?></th>
                    <td><?php echo RECRUIT_CONNECT_VERSION; ?></td>
                </tr>
                <tr>
                    <th><?php echo esc_html__('WordPress Version', 'recruit-connect-wp'); ?></th>
                    <td><?php echo get_bloginfo('version'); ?></td>
                </tr>
                <tr>
                    <th><?php echo esc_html__('PHP Version', 'recruit-connect-wp'); ?></th>
                    <td><?php echo phpversion(); ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<style>
.recruit-connect-about {
    max-width: 800px;
    margin: 20px 0;
}

.about-header {
    text-align: center;
    margin-bottom: 40px;
}

.about-logo {
    max-width: 200px;
    height: auto;
    margin-bottom: 20px;
}

.about-section {
    margin-bottom: 40px;
}

.about-section h3 {
    border-bottom: 1px solid #ccc;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.about-section ul {
    list-style: disc;
    margin-left: 20px;
}
</style>
