<?php
// Ensure $stats is available
if (!isset($stats)) {
	$stats = array(
		'total_vacancies' => 0,
		'total_applications' => 0,
		'last_import' => ''
	);
}
?>
<div class="wrap">
    <h1><?php echo esc_html__('Recruit Connect Dashboard', 'recruit-connect-wp'); ?></h1>

    <div class="recruit-connect-dashboard">
        <div class="dashboard-widget">
            <h2><?php echo esc_html__('Statistics', 'recruit-connect-wp'); ?></h2>
            <div class="stats-grid">
                <div class="stat-box">
                    <span class="stat-label"><?php echo esc_html__('Total Vacancies', 'recruit-connect-wp'); ?></span>
                    <span class="stat-value"><?php echo esc_html($stats['total_vacancies']); ?></span>
                </div>

                <div class="stat-box">
                    <span class="stat-label"><?php echo esc_html__('Total Applications', 'recruit-connect-wp'); ?></span>
                    <span class="stat-value"><?php echo esc_html($stats['total_applications']); ?></span>
                </div>

                <div class="stat-box">
                    <span class="stat-label"><?php echo esc_html__('Last Import', 'recruit-connect-wp'); ?></span>
                    <span class="stat-value">
                        <?php
                        if (!empty($stats['last_import'])) {
	                        echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($stats['last_import'])));
                        } else {
	                        echo esc_html__('Never', 'recruit-connect-wp');
                        }
                        ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .stat-box {
        background: #fff;
        padding: 20px;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        text-align: center;
    }

    .stat-label {
        display: block;
        color: #646970;
        margin-bottom: 10px;
    }

    .stat-value {
        display: block;
        font-size: 24px;
        font-weight: bold;
        color: #1d2327;
    }
</style>
