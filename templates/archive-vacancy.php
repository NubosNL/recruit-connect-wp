<?php
/**
 * Template for displaying vacancy archives
 */

get_header();
?>

<div class="rcwp-archive-wrapper">
    <header class="rcwp-archive-header">
        <h1><?php _e('Vacancies', 'recruit-connect-wp'); ?></h1>
    </header>

    <?php echo do_shortcode('[recruit_connect_vacancies_overview]'); ?>
</div>

<?php
get_footer();
?>
