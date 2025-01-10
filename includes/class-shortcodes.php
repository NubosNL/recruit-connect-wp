<?php
namespace RecruitConnect;

class Shortcodes {
    public function __construct() {
        add_shortcode('recruit_connect_vacancies', array($this, 'render_vacancies_list'));
        add_shortcode('recruit_connect_search', array($this, 'render_search_form'));
        add_shortcode('recruit_connect_vacancy', array($this, 'render_single_vacancy'));
    }

    public function render_vacancies_list($atts) {
        $atts = shortcode_atts(array(
            'limit' => 10,
            'category' => '',
            'education' => '',
            'jobtype' => '',
            'layout' => 'grid' // grid or list
        ), $atts);

        // Query vacancies
        $args = array(
            'post_type' => 'vacancy',
            'posts_per_page' => $atts['limit'],
            'meta_query' => array()
        );

        if (!empty($atts['category'])) {
            $args['meta_query'][] = array(
                'key' => '_vacancy_category',
                'value' => $atts['category']
            );
        }

        if (!empty($atts['education'])) {
            $args['meta_query'][] = array(
                'key' => '_vacancy_education',
                'value' => $atts['education']
            );
        }

        if (!empty($atts['jobtype'])) {
            $args['meta_query'][] = array(
                'key' => '_vacancy_jobtype',
                'value' => $atts['jobtype']
            );
        }

        $query = new \WP_Query($args);

        ob_start();
        include RECRUIT_CONNECT_PLUGIN_DIR . 'templates/vacancies-' . $atts['layout'] . '.php';
        return ob_get_clean();
    }

    public function render_search_form($atts) {
        $atts = shortcode_atts(array(
            'show_category' => true,
            'show_education' => true,
            'show_jobtype' => true,
            'show_salary' => true
        ), $atts);

        ob_start();
        include RECRUIT_CONNECT_PLUGIN_DIR . 'templates/search-form.php';
        return ob_get_clean();
    }

    public function render_single_vacancy($atts) {
        $atts = shortcode_atts(array(
            'id' => get_query_var('vacancy_id', 0)
        ), $atts);

        if (empty($atts['id'])) {
            return '';
        }

        $vacancy = get_post($atts['id']);
        if (!$vacancy || $vacancy->post_type !== 'vacancy') {
            return '';
        }

        ob_start();
        include RECRUIT_CONNECT_PLUGIN_DIR . 'templates/single-vacancy.php';
        return ob_get_clean();
    }
}
