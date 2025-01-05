<?php
class RCWP_Frontend {
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('recruit_connect_vacancies_overview', array($this, 'render_vacancies_overview'));
        add_shortcode('recruit_connect_application_form', array($this, 'render_application_form'));
        add_action('wp_ajax_rcwp_load_more_vacancies', array($this, 'ajax_load_more_vacancies'));
        add_action('wp_ajax_nopriv_rcwp_load_more_vacancies', array($this, 'ajax_load_more_vacancies'));
        add_action('wp_ajax_rcwp_submit_application', array($this, 'handle_application_submission'));
        add_action('wp_ajax_nopriv_rcwp_submit_application', array($this, 'handle_application_submission'));
    }

    public function enqueue_scripts() {
        wp_enqueue_style(
            'rcwp-frontend',
            RCWP_PLUGIN_URL . 'public/css/public.css',
            array(),
            RCWP_VERSION
        );

        wp_enqueue_script(
            'rcwp-frontend',
            RCWP_PLUGIN_URL . 'public/js/public.js',
            array('jquery'),
            RCWP_VERSION,
            true
        );

        wp_localize_script('rcwp-frontend', 'rcwpFront', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rcwp-frontend-nonce'),
            'strings' => array(
                'loadMore' => __('Load More', 'recruit-connect-wp'),
                'loading' => __('Loading...', 'recruit-connect-wp'),
                'noMore' => __('No more vacancies', 'recruit-connect-wp'),
                'submitSuccess' => __('Application submitted successfully!', 'recruit-connect-wp'),
                'submitError' => __('Error submitting application. Please try again.', 'recruit-connect-wp')
            )
        ));
    }

    public function render_vacancies_overview($atts) {
        $atts = shortcode_atts(array(
            'limit' => 10
        ), $atts);

        ob_start();
        ?>
        <div class="rcwp-vacancies-wrapper">
            <!-- Search and Filters -->
            <div class="rcwp-search-filters">
                <div class="rcwp-search">
                    <input type="text" id="rcwp-search-input"
                           placeholder="<?php esc_attr_e('Search vacancies...', 'recruit-connect-wp'); ?>">
                </div>

                <div class="rcwp-filters">
                    <?php $this->render_filters(); ?>
                </div>
            </div>

            <!-- Vacancies List -->
            <div class="rcwp-vacancies-list">
                <?php
                $vacancies = $this->get_vacancies($atts['limit']);
                $this->render_vacancies_list($vacancies);
                ?>
            </div>

            <!-- Load More Button -->
            <div class="rcwp-load-more">
                <button class="rcwp-load-more-btn">
                    <?php _e('Load More', 'recruit-connect-wp'); ?>
                </button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function render_filters() {
        // Get all unique values for filters
        $categories = $this->get_unique_meta_values('_vacancy_category');
        $educations = $this->get_unique_meta_values('_vacancy_education');
        $jobtypes = $this->get_unique_meta_values('_vacancy_jobtype');
        $salary_range = $this->get_salary_range();
        ?>
        <div class="rcwp-filter-group">
            <select class="rcwp-filter" data-filter="category">
                <option value=""><?php _e('All Categories', 'recruit-connect-wp'); ?></option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo esc_attr($category); ?>">
                        <?php echo esc_html($category); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select class="rcwp-filter" data-filter="education">
                <option value=""><?php _e('All Education Levels', 'recruit-connect-wp'); ?></option>
                <?php foreach ($educations as $education): ?>
                    <option value="<?php echo esc_attr($education); ?>">
                        <?php echo esc_html($education); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select class="rcwp-filter" data-filter="jobtype">
                <option value=""><?php _e('All Job Types', 'recruit-connect-wp'); ?></option>
                <?php foreach ($jobtypes as $jobtype): ?>
                    <option value="<?php echo esc_attr($jobtype); ?>">
                        <?php echo esc_html($jobtype); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div class="rcwp-salary-range">
                <input type="range"
                       min="<?php echo esc_attr($salary_range['min']); ?>"
                       max="<?php echo esc_attr($salary_range['max']); ?>"
                       value="<?php echo esc_attr($salary_range['min']); ?>"
                       class="rcwp-salary-slider"
                       id="rcwp-salary-min">
                <input type="range"
                       min="<?php echo esc_attr($salary_range['min']); ?>"
                       max="<?php echo esc_attr($salary_range['max']); ?>"
                       value="<?php echo esc_attr($salary_range['max']); ?>"
                       class="rcwp-salary-slider"
                       id="rcwp-salary-max">
                <div class="rcwp-salary-values">
                    <span id="rcwp-salary-min-value"></span> -
                    <span id="rcwp-salary-max-value"></span>
                </div>
            </div>
        </div>
        <?php
    }

    private function get_unique_meta_values($meta_key) {
        global $wpdb;
        $query = $wpdb->prepare(
            "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} pm
            JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE pm.meta_key = %s
            AND p.post_type = 'vacancy'
            AND p.post_status = 'publish'
            AND meta_value != ''",
            $meta_key
        );
        return $wpdb->get_col($query);
    }

    private function get_salary_range() {
        global $wpdb;
        $min = $wpdb->get_var(
            "SELECT MIN(CAST(meta_value AS DECIMAL))
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_vacancy_salary_low'"
        );
        $max = $wpdb->get_var(
            "SELECT MAX(CAST(meta_value AS DECIMAL))
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_vacancy_salary_high'"
        );

        return array(
            'min' => floor($min),
            'max' => ceil($max)
        );
    }

    private function get_vacancies($limit = 10, $offset = 0, $filters = array()) {
        $args = array(
            'post_type' => 'vacancy',
            'posts_per_page' => $limit,
            'offset' => $offset,
            'post_status' => 'publish'
        );

        // Add meta query for filters
        if (!empty($filters)) {
            $args['meta_query'] = array('relation' => 'AND');

            foreach ($filters as $key => $value) {
                if (!empty($value)) {
                    $args['meta_query'][] = array(
                        'key' => '_vacancy_' . $key,
                        'value' => $value
                    );
                }
            }
        }

        return get_posts($args);
    }

    private function render_vacancies_list($vacancies) {
        if (empty($vacancies)) {
            echo '<p class="rcwp-no-vacancies">' .
                 __('No vacancies found.', 'recruit-connect-wp') .
                 '</p>';
            return;
        }

        foreach ($vacancies as $vacancy) {
            $this->render_vacancy_card($vacancy);
        }
    }

    private function render_vacancy_card($vacancy) {
        $meta = $this->get_vacancy_meta($vacancy->ID);
        ?>
        <div class="rcwp-vacancy-card">
            <h3 class="rcwp-vacancy-title">
                <a href="<?php echo get_permalink($vacancy->ID); ?>">
                    <?php echo esc_html($vacancy->post_title); ?>
                </a>
            </h3>

            <div class="rcwp-vacancy-meta">
                <?php if (!empty($meta['company'])): ?>
                    <span class="rcwp-company">
                        <?php echo esc_html($meta['company']); ?>
                    </span>
                <?php endif; ?>

                <?php if (!empty($meta['location'])): ?>
                    <span class="rcwp-location">
                        <?php echo esc_html($meta['location']); ?>
                    </span>
                <?php endif; ?>

                <?php if (!empty($meta['salary'])): ?>
                    <span class="rcwp-salary">
                        <?php echo esc_html($meta['salary']); ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="rcwp-vacancy-excerpt">
                <?php echo wp_trim_words($vacancy->post_content, 20); ?>
            </div>

            <a href="<?php echo get_permalink($vacancy->ID); ?>"
               class="rcwp-vacancy-link">
                <?php _e('View Details', 'recruit-connect-wp'); ?>
            </a>
        </div>
        <?php
    }

    private function get_vacancy_meta($post_id) {
        return array(
            'company' => get_post_meta($post_id, '_vacancy_company', true),
            'location' => get_post_meta($post_id, '_vacancy_city', true),
            'salary' => get_post_meta($post_id, '_vacancy_salary', true),
            // Add other meta fields as needed
        );
    }

    public function ajax_load_more_vacancies() {
        check_ajax_referer('rcwp-frontend-nonce', 'nonce');

        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        $filters = isset($_POST['filters']) ? $_POST['filters'] : array();

        $vacancies = $this->get_vacancies(10, $offset, $filters);
        ob_start();
        $this->render_vacancies_list($vacancies);
        $html = ob_get_clean();

        wp_send_json_success(array(
            'html' => $html,
            'hasMore' => count($vacancies) === 10
        ));
    }
}
