<?php
/**
 * Admin settings view
 *
 * @link       https://www.nubos.nl/en
 * @since      1.0.0
 *
 * @package    Recruit_Connect_WP
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <h2 class="nav-tab-wrapper">
        <a href="#general" class="nav-tab nav-tab-active" data-tab="general"><?php _e('General Settings', 'recruit-connect-wp'); ?></a>
        <a href="#application" class="nav-tab" data-tab="application"><?php _e('Application Form', 'recruit-connect-wp'); ?></a>
        <a href="#sync" class="nav-tab" data-tab="sync"><?php _e('Synchronization', 'recruit-connect-wp'); ?></a>
        <a href="#display" class="nav-tab" data-tab="display"><?php _e('Display Settings', 'recruit-connect-wp'); ?></a>
        <a href="#logs" class="nav-tab" data-tab="logs"><?php _e('Logs', 'recruit-connect-wp'); ?></a>
    </h2>

    <form method="post" action="options.php">
        <?php settings_fields('rcwp_settings'); ?>

        <div class="tab-content" id="general">
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('XML Feed URL', 'recruit-connect-wp'); ?></th>
                    <td>
                        <input type="url" name="rcwp_xml_url" value="<?php echo esc_attr(get_option('rcwp_xml_url')); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Application Destination URL', 'recruit-connect-wp'); ?></th>
                    <td>
                        <input type="url" name="rcwp_application_url" value="<?php echo esc_attr(get_option('rcwp_application_url')); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Vacancy URL Parameter', 'recruit-connect-wp'); ?></th>
                    <td>
                        <input type="text" name="rcwp_vacancy_url_parameter" value="<?php echo esc_attr(get_option('rcwp_vacancy_url_parameter', 'vacancy')); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Enable Detail Page', 'recruit-connect-wp'); ?></th>
                    <td>
                        <input type="checkbox" name="rcwp_enable_detail_page" value="1" <?php checked(get_option('rcwp_enable_detail_page', '1')); ?>>
                    </td>
                </tr>
            </table>
        </div>

        <div class="tab-content" id="application" style="display: none;">
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Thank You Message', 'recruit-connect-wp'); ?></th>
                    <td>
                        <textarea name="rcwp_thank_you_message" rows="5" cols="50"><?php echo esc_textarea(get_option('rcwp_thank_you_message')); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Required Fields', 'recruit-connect-wp'); ?></th>
                    <td>
                        <?php
                        $required_fields = get_option('rcwp_required_fields', array());
                        $fields = array(
                            'first_name' => __('First Name', 'recruit-connect-wp'),
                            'last_name'  => __('Last Name', 'recruit-connect-wp'),
                            'email'      => __('Email', 'recruit-connect-wp'),
                            'phone'      => __('Phone', 'recruit-connect-wp'),
                            'motivation' => __('Motivation', 'recruit-connect-wp'),
                            'resume'     => __('Resume', 'recruit-connect-wp')
                        );
                        foreach ($fields as $field_key => $field_label) :
                        ?>
                            <label>
                                <input type="checkbox" name="rcwp_required_fields[]" value="<?php echo esc_attr($field_key); ?>"
                                    <?php checked(in_array($field_key, $required_fields)); ?>>
                                <?php echo esc_html($field_label); ?>
                            </label><br>
                        <?php endforeach; ?>
                    </td>
                </tr>
            </table>
        </div>

        <div class="tab-content" id="sync" style="display: none;">
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Sync Frequency', 'recruit-connect-wp'); ?></th>
                    <td>
                        <select name="rcwp_sync_frequency">
                            <option value="hourly" <?php selected(get_option('rcwp_sync_frequency'), 'hourly'); ?>><?php _e('Hourly', 'recruit-connect-wp'); ?></option>
                            <option value="daily" <?php selected(get_option('rcwp_sync_frequency'), 'daily'); ?>><?php _e('Daily', 'recruit-connect-wp'); ?></option>
                            <option value="twicedaily" <?php selected(get_option('rcwp_sync_frequency'), 'twicedaily'); ?>><?php _e('Twice Daily', 'recruit-connect-wp'); ?></option>
                            <option value="fourhourly" <?php selected(get_option('rcwp_sync_frequency'), 'fourhourly'); ?>><?php _e('Every 4 Hours', 'recruit-connect-wp'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Manual Sync', 'recruit-connect-wp'); ?></th>
                    <td>
                        <button type="button" class="button" id="rcwp-sync-now"><?php _e('Sync Now', 'recruit-connect-wp'); ?></button>
                        <span class="spinner"></span>
                        <span class="sync-status"></span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="tab-content" id="display" style="display: none;">
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Search Components', 'recruit-connect-wp'); ?></th>
                    <td>
                        <?php
                        $search_components = get_option('rcwp_search_components', array());
                        $components = array(
                            'category' => __('Category', 'recruit-connect-wp'),
                            'education' => __('Education', 'recruit-connect-wp'),
                            'jobtype' => __('Job Type', 'recruit-connect-wp'),
                            'salary' => __('Salary Range', 'recruit-connect-wp')
                        );
                        foreach ($components as $component_key => $component_label) :
                        ?>
                            <label>
                                <input type="checkbox" name="rcwp_search_components[]" value="<?php echo esc_attr($component_key); ?>"
                                    <?php checked(in_array($component_key, $search_components)); ?>>
                                <?php echo esc_html($component_label); ?>
                            </label><br>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Detail Page Fields', 'recruit-connect-wp'); ?></th>
                    <td>
                        <div id="rcwp-detail-fields-sortable">
                            <?php
                            $detail_fields = get_option('rcwp_detail_page_fields', array());
                            $fields = array(
                                'title' => __('Title', 'recruit-connect-wp'),
                                'description' => __('Description', 'recruit-connect-wp'),
                                'company' => __('Company', 'recruit-connect-wp'),
                                'location' => __('Location', 'recruit-connect-wp'),
                                'salary' => __('Salary', 'recruit-connect-wp'),
                                'education' => __('Education', 'recruit-connect-wp'),
                                'jobtype' => __('Job Type', 'recruit-connect-wp'),
                                'experience' => __('Experience', 'recruit-connect-wp'),
                                'recruiter' => __('Recruiter', 'recruit-connect-wp')
                            );
                            foreach ($fields as $field_key => $field_label) :
                            ?>
                                <div class="field-item" data-field="<?php echo esc_attr($field_key); ?>">
                                    <span class="dashicons dashicons-menu"></span>
                                    <label>
                                        <input type="checkbox" name="rcwp_detail_page_fields[<?php echo esc_attr($field_key); ?>]" value="1"
                                            <?php checked(isset($detail_fields[$field_key])); ?>>
                                        <?php echo esc_html($field_label); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="tab-content" id="logs" style="display: none;">
            <div id="rcwp-logs-wrapper">
                <div class="log-controls">
                    <button type="button" class="button" id="rcwp-refresh-logs"><?php _e('Refresh Logs', 'recruit-connect-wp'); ?></button>
                    <button type="button" class="button" id="rcwp-clear-logs"><?php _e('Clear Logs', 'recruit-connect-wp'); ?></button>
                </div>
                <div class="log-entries">
                    <!-- Log entries will be loaded here via AJAX -->
                </div>
            </div>
        </div>

        <?php submit_button(); ?>
    </form>
</div>
