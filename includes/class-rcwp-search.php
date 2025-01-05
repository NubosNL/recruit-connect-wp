<?php
class RCWP_Search {
    private $cache_group = 'rcwp_search';
    private $cache_time = 3600; // 1 hour

    public function __construct() {
        add_action('wp_ajax_rcwp_search_vacancies', array($this, 'handle_search'));
        add_action('wp_ajax_nopriv_rcwp_search_vacancies', array($this, 'handle_search'));
        add_action('save_post_vacancy', array($this, 'clear_cache'));
    }

    public function handle_search() {
        check_ajax_referer('rcwp-frontend-nonce', 'nonce');

        $filters = array(
            'search' => sanitize_text_field($_POST['search'] ?? ''),
            'category' => sanitize_text_field($_POST['category'] ?? ''),
            'education' => sanitize_text_field($_POST['education'] ?? ''),
            'jobtype' => sanitize_text_field($_POST['jobtype'] ?? ''),
            'salary_min' => intval($_POST['salary_min'] ?? 0),
            'salary_max' => intval($_POST['salary_max'] ?? 0),
            'page' => intval($_POST['page'] ?? 1),
            'per_page' => intval($_POST['per_page'] ?? 10)
        );

        $cache_key = 'search_' . md5(serialize($filters));
        $results = wp_cache_get($cache_key, $this->cache_group);

        if (false === $results) {
            $results = $this->search_vacancies($filters);
            wp_cache_set($cache_key, $results, $this->cache_group, $this->cache_time);
        }

        wp_send_json_success($results);
    }

    private function search_vacancies($filters) {
        $args = array(
            'post_type' => 'vacancy',
            'posts_per_page' => $filters['per_page'],
            'paged' => $filters['page'],
            'post_status' => 'publish',
            'meta_query' => array('relation' => 'AND'),
            'tax_query' => array('relation' => 'AND')
        );

        // Search in title and content
        if (!empty($filters['search'])) {
            $args['s'] = $filters['search'];
        }

        // Category filter
        if (!empty($filters['category'])) {
            $args['meta_query'][] = array(
                'key' => '_vacancy_category',
                'value' => $filters['category']
            );
        }

        // Education filter
        if (!empty($filters['education'])) {
            $args['meta_query'][] = array(
                'key' => '_vacancy_education',
                'value' => $filters['education']
            );
        }

        // Job type filter
        if (!empty($filters['jobtype'])) {
            $args['meta_query'][] = array(
                'key' => '_vacancy_jobtype',
                'value' => $filters['jobtype']
            );
        }

        // Salary range filter
        if (!empty($filters['salary_min']) || !empty($filters['salary_max'])) {
            $args['meta_query'][] = array(
                'relation' => 'AND',
                array(
                    'key' => '_vacancy_salary_low',
                    'value' => $filters['salary_min'],
                    'type' => 'NUMERIC',
                    'compare' => '>='
                ),
                array(
                    'key' => '_vacancy_salary_high',
                    'value' => $filters['salary_max'],
                    'type' => 'NUMERIC',
                    'compare' => '<='
                )
            );
        }

        $query = new WP_Query($args);

        return array(
            'vacancies' => $this->prepare_vacancies($query->posts),
            'total' => $query->found_posts,
            'max_pages' => $query->max_num_pages
        );
    }

    private function prepare_vacancies($posts) {
        $vacancies = array();

        foreach ($posts as $post) {
            $vacancies[] = array(
                'id' => $post->ID,
                'title' => $post->post_title,
                'excerpt' => $this->get_custom_excerpt($post),
                'url' => get_permalink($post->ID),
                'meta' => $this->get_vacancy_meta($post->ID)
            );
        }

        return $vacancies;
    }

    private function get_custom_excerpt($post) {
        $excerpt = has_excerpt($post) ?
                  get_the_excerpt($post) :
                  wp_trim_words($post->post_content, 20);

        return apply_filters('rcwp_vacancy_excerpt', $excerpt, $post);
    }

    private function get_vacancy_meta($post_id) {
        return array(
            'company' => get_post_meta($post_id, '_vacancy_company', true),
            'location' => get_post_meta($post_id, '_vacancy_city', true),
            'salary' => get_post_meta($post_id, '_vacancy_salary', true),
            'education' => get_post_meta($post_id, '_vacancy_education', true),
            'jobtype' => get_post_meta($post_id, '_vacancy_jobtype', true),
            'posted_date' => get_the_date('Y-m-d', $post_id)
        );
    }

    public function clear_cache() {
        wp_cache_delete_group($this->cache_group);
    }
}
