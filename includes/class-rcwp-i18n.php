<?php
class RCWP_I18n {
    public function __construct() {
        add_action('init', array($this, 'load_plugin_textdomain'));
        add_filter('load_textdomain_mofile', array($this, 'load_custom_mo_file'), 10, 2);
        add_filter('locale', array($this, 'maybe_switch_locale'));
    }

    /**
     * Load plugin text domain
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'recruit-connect-wp',
            false,
            dirname(plugin_basename(RCWP_PLUGIN_FILE)) . '/languages'
        );
    }

    /**
     * Load custom MO file based on user preferences
     */
    public function load_custom_mo_file($mofile, $domain) {
        if ($domain === 'recruit-connect-wp') {
            $locale = determine_locale();
            $custom_mofile = WP_LANG_DIR . '/plugins/recruit-connect-wp-' . $locale . '.mo';

            if (file_exists($custom_mofile)) {
                return $custom_mofile;
            }
        }
        return $mofile;
    }

    /**
     * Switch locale based on user preference or URL parameter
     */
    public function maybe_switch_locale($locale) {
        if (isset($_GET['lang']) && $this->is_valid_locale($_GET['lang'])) {
            return sanitize_text_field($_GET['lang']);
        }

        return $locale;
    }

    /**
     * Check if locale is valid
     */
    private function is_valid_locale($locale) {
        $valid_locales = array('en_US', 'nl_NL', 'de_DE', 'fr_FR', 'es_ES');
        return in_array($locale, $valid_locales);
    }

    /**
     * Get translated strings
     */
    public static function get_strings() {
        return array(
            'application' => array(
                'success' => __('Application submitted successfully!', 'recruit-connect-wp'),
                'error' => __('Error submitting application.', 'recruit-connect-wp'),
                'required_fields' => __('Please fill in all required fields.', 'recruit-connect-wp')
            ),
            'vacancy' => array(
                'not_found' => __('No vacancies found.', 'recruit-connect-wp'),
                'load_more' => __('Load More', 'recruit-connect-wp'),
                'apply_now' => __('Apply Now', 'recruit-connect-wp')
            ),
            'filters' => array(
                'search' => __('Search vacancies...', 'recruit-connect-wp'),
                'category' => __('Category', 'recruit-connect-wp'),
                'location' => __('Location', 'recruit-connect-wp'),
                'salary' => __('Salary Range', 'recruit-connect-wp')
            )
        );
    }
}
