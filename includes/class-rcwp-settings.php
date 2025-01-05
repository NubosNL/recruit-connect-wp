<?php
class RCWP_Settings {
    private $active_tab;

    public function __construct() {
        $this->active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <h2 class="nav-tab-wrapper">
                <a href="?page=recruit-connect-wp&tab=general"
                   class="nav-tab <?php echo $this->active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('General Settings', 'recruit-connect-wp'); ?>
                </a>
                <a href="?page=recruit-connect-wp&tab=application"
                   class="nav-tab <?php echo $this->active_tab === 'application' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Application Form', 'recruit-connect-wp'); ?>
                </a>
                <a href="?page=recruit-connect-wp&tab=sync"
                   class="nav-tab <?php echo $this->active_tab === 'sync' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Synchronization', 'recruit-connect-wp'); ?>
                </a>
                <a href="?page=recruit-connect-wp&tab=detail"
                   class="nav-tab <?php echo $this->active_tab === 'detail' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Detail Page', 'recruit-connect-wp'); ?>
                </a>
            </h2>

            <form method="post" action="options.php">
                <?php
                switch ($this->active_tab) {
                    case 'application':
                        settings_fields('rcwp_application_settings');
                        $this->render_application_settings();
                        break;
                    case 'sync':
                        settings_fields('rcwp_sync_settings');
                        $this->render_sync_settings();
                        break;
                    case 'detail':
                        settings_fields('rcwp_detail_settings');
                        $this->render_detail_settings();
                        break;
                    default:
                        settings_fields('rcwp_general_settings');
                        $this->render_general_settings();
                        break;
                }
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    private function render_general_settings() {
        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('XML Feed URL', 'recruit-connect-wp'); ?></th>
                <td>
                    <input type="url" name="rcwp_xml_url"
                           value="<?php echo esc_attr(get_option('rcwp_xml_url')); ?>"
                           class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Application Destination URL', 'recruit-connect-wp'); ?></th>
                <td>
                    <input type="url" name="rcwp_application_url"
                           value="<?php echo esc_attr(get_option('rcwp_application_url')); ?>"
                           class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Vacancy Detail URL Parameter', 'recruit-connect-wp'); ?></th>
                <td>
                    <input type="text" name="rcwp_detail_url_param"
                           value="<?php echo esc_attr(get_option('rcwp_detail_url_param', 'vacancy')); ?>"
                           class="regular-text">
                </td>
            </tr>
            <!-- Add more general settings fields -->
        </table>
        <?php
    }

    private function render_application_settings() {
        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Thank You Message', 'recruit-connect-wp'); ?></th>
                <td>
                    <?php
                    wp_editor(
                        get_option('rcwp_thank_you_message'),
                        'rcwp_thank_you_message',
                        array('textarea_rows' => 5)
                    );
                    ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Required Fields', 'recruit-connect-wp'); ?></th>
                <td>
                    <?php
                    $required_fields = get_option('rcwp_required_fields', array());
                    $fields = array(
                        'first_name' => __('First Name', 'recruit-connect-wp'),
                        'last_name' => __('Last Name', 'recruit-connect-wp'),
                        'email' => __('Email', 'recruit-connect-wp'),
                        'phone' => __('Phone', 'recruit-connect-wp'),
                        'motivation' => __('Motivation', 'recruit-connect-wp'),
                        'resume' => __('Resume', 'recruit-connect-wp')
                    );

                    foreach ($fields as $field_key => $field_label) {
                        ?>
                        <label>
                            <input type="checkbox"
                                   name="rcwp_required_fields[]"
                                   value="<?php echo esc_attr($field_key); ?>"
                                   <?php checked(in_array($field_key, $required_fields)); ?>>
                            <?php echo esc_html($field_label); ?>
                        </label><br>
                        <?php
                    }
                    ?>
                </td>
            </tr>
        </table>
        <?php
    }

    private function render_sync_settings() {
        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Sync Frequency', 'recruit-connect-wp'); ?></th>
                <td>
                    <select name="rcwp_sync_frequency">
                        <?php
                        $current_frequency = get_option('rcwp_sync_frequency', 'daily');
                        $frequencies = array(
                            'quarter_daily' => __('4 times daily', 'recruit-connect-wp'),
                            'twicedaily' => __('Twice daily', 'recruit-connect-wp'),
                            'daily' => __('Daily', 'recruit-connect-wp'),
                            'hourly' => __('Hourly', 'recruit-connect-wp')
                        );

                        foreach ($frequencies as $value => $label) {
                            printf(
                                '<option value="%s" %s>%s</option>',
                                esc_attr($value),
                                selected($current_frequency, $value, false),
                                esc_html($label)
                            );
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Manual Sync', 'recruit-connect-wp'); ?></th>
                <td>
                    <button type="button" class="button" id="rcwp-sync-now">
                        <?php _e('Sync Now', 'recruit-connect-wp'); ?>
                    </button>
                    <span class="spinner"></span>
                    <span class="sync-status"></span>
                </td>
            </tr>
        </table>
        <?php
    }

    private function render_detail_settings() {
        $enabled_fields = get_option('rcwp_detail_fields', array());
        $field_order = get_option('rcwp_field_order', array());

        ?>
        <div class="rcwp-detail-fields-wrapper">
            <h3><?php _e('Enable/Disable Fields', 'recruit-connect-wp'); ?></h3>
            <div class="rcwp-sortable-fields">
                <?php
                $available_fields = array(
                    'category' => __('Category', 'recruit-connect-wp'),
                    'city' => __('City', 'recruit-connect-wp'),
                    'company' => __('Company', 'recruit-connect-wp'),
                    'salary' => __('Salary', 'recruit-connect-wp'),
                    'education' => __('Education', 'recruit-connect-wp'),
                    'jobtype' => __('Job Type', 'recruit-connect-wp'),
                    'experience' => __('Experience', 'recruit-connect-wp'),
                    'recruiter' => __('Recruiter Info', 'recruit-connect-wp'),
                    'remotetype' => __('Remote Type', 'recruit-connect-wp')
                );

                foreach ($available_fields as $field_key => $field_label) {
                    ?>
                    <div class="rcwp-field-item" data-field="<?php echo esc_attr($field_key); ?>">
                        <label>
                            <input type="checkbox"
                                   name="rcwp_detail_fields[]"
                                   value="<?php echo esc_attr($field_key); ?>"
                                   <?php checked(in_array($field_key, $enabled_fields)); ?>>
                            <?php echo esc_html($field_label); ?>
                        </label>
                        <span class="dashicons dashicons-menu"></span>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
        <?php
    }
}
