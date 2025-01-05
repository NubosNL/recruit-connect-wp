<?php
class RCWP_REST_API {
    private $namespace = 'recruit-connect/v1';

    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes() {
        // Get vacancies with filters
        register_rest_route($this->namespace, '/vacancies', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_vacancies'),
            'permission_callback' => '__return_true',
            'args' => array(
                'page' => array(
                    'default' => 1,
                    'sanitize_callback' => 'absint'
                ),
                'per_page' => array(
                    'default' => 10,
                    'sanitize_callback' => 'absint'
                ),
                'category' => array(
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'education' => array(
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'jobtype' => array(
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'salary_min' => array(
                    'sanitize_callback' => 'absint'
                ),
                'salary_max' => array(
                    'sanitize_callback' => 'absint'
                ),
                'search' => array(
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ));

        // Get single vacancy
        register_rest_route($this->namespace, '/vacancies/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_vacancy'),
            'permission_callback' => '__return_true'
        ));

        // Submit application
        register_rest_route($this->namespace, '/applications', array(
            'methods' => 'POST',
            'callback' => array($this, 'submit_application'),
            'permission_callback' => '__return_true',
            'args' => array(
                'vacancy_id' => array(
                    'required' => true,
                    'sanitize_callback' => 'absint'
                ),
                'first_name' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'last_name' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'email' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_email'
                )
            )
        ));

        // Get filter options
        register_rest_route($this->namespace, '/filters', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_filters'),
            'permission_callback' => '__return_true'
        ));
    }

    public function get_vacancies($request) {
        $args = array(
            'post_type' => 'vacancy',
            'posts_per_page' => $request['per_page'],
            'paged' => $request['page'],
            'meta_query' => array('relation' => 'AND')
        );

        // Add filters
        if (!empty($request['category'])) {
            $args['meta_query'][] = array(
                'key' => '_vacancy_category',
                'value' => $request['category']
            );
        }

        if (!empty($request['education'])) {
            $args['meta_query'][] = array(
                'key' => '_vacancy_education',
                'value' => $request['education']
            );
        }

        if (!empty($request['jobtype'])) {
            $args['meta_query'][] = array(
                'key' => '_vacancy_jobtype',
                'value' => $request['jobtype']
            );
        }

        if (!empty($request['salary_min']) || !empty($request['salary_max'])) {
            $args['meta_query'][] = array(
                'key' => '_vacancy_salary',
                'value' => array($request['salary_min'], $request['salary_max']),
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN'
            );
        }

        if (!empty($request['search'])) {
            $args['s'] = $request['search'];
        }

        $query = new WP_Query($args);
        $vacancies = array();

        foreach ($query->posts as $post) {
            $vacancies[] = $this->prepare_vacancy_response($post);
        }

        $response = rest_ensure_response($vacancies);
        $response->header('X-WP-Total', $query->found_posts);
        $response->header('X-WP-TotalPages', $query->max_num_pages);

        return $response;
    }

    public function get_vacancy($request) {
        $post = get_post($request['id']);

        if (!$post || $post->post_type !== 'vacancy') {
            return new WP_Error(
                'vacancy_not_found',
                __('Vacancy not found', 'recruit-connect-wp'),
                array('status' => 404)
            );
        }

        return rest_ensure_response($this->prepare_vacancy_response($post));
    }

    public function submit_application($request) {
        $application_handler = new RCWP_Application_Handler();

        try {
            $result = $application_handler->process_application(array(
                'vacancy_id' => $request['vacancy_id'],
                'first_name' => $request['first_name'],
                'last_name' => $request['last_name'],
                'email' => $request['email'],
                'phone' => $request['phone'],
                'motivation' => $request['motivation']
            ));

            return rest_ensure_response(array(
                'success' => true,
                'message' => __('Application submitted successfully', 'recruit-connect-wp')
            ));

        } catch (Exception $e) {
            return new WP_Error(
                'application_error',
                $e->getMessage(),
                array('status' => 400)
            );
        }
    }

    public function get_filters() {
        global $wpdb;

        $filters = array(
            'categories' => $this->get_unique_meta_values('_vacancy_category'),
            'education' => $this->get_unique_meta_values('_vacancy_education'),
            'jobtypes' => $this->get_unique_meta_values('_vacancy_jobtype'),
            'salary_range' => array(
                'min' => $this->get_salary_range('min'),
                'max' => $this->get_salary_range('max')
            )
        );

        return rest_ensure_response($filters);
    }

    private function prepare_vacancy_response($post) {
        $response = array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'content' => $post->post_content,
            'date' => $post->post_date,
            'modified' => $post->post_modified,
            'meta' => array(
                'category' => get_post_meta($post->ID, '_vacancy_category', true),
                'city' => get_post_meta($post->ID, '_vacancy_city', true),
                'company' => get_post_meta($post->ID, '_vacancy_company', true),
                'salary' => get_post_meta($post->ID, '_vacancy_salary', true),
                'education' => get_post_meta($post->ID, '_vacancy_education', true),
                'jobtype' => get_post_meta($post->ID, '_vacancy_jobtype', true),
                'experience' => get_post_meta($post->ID, '_vacancy_experience', true),
                'recruiter' => array(
                    'name' => get_post_meta($post->ID, '_vacancy_recruitername', true),
                    'email' => get_post_meta($post->ID, '_vacancy_recruiteremail', true),
                    'image' => get_post_meta($post->ID, '_vacancy_recruiterimage', true)
                )
            ),
            'link' => get_permalink($post->ID)
        );

        return $response;
    }

    private function get_unique_meta_values($meta_key) {
        global $wpdb;

        return $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT meta_value 
            FROM {$wpdb->postmeta} pm
            JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE pm.meta_key = %s
            AND p.post_type = 'vacancy'
            AND p.post_status = 'publish'
            AND meta_value != ''
            ORDER BY meta_value ASC",
            $meta_key
        ));
    }

    private function get_salary_range($type = 'min') {
        global $wpdb;

        $function = $type === 'min' ? 'MIN' : 'MAX';

        return $wpdb->get_var($wpdb->prepare(
            "SELECT {$function}(CAST(meta_value AS DECIMAL))
            FROM {$wpdb->postmeta}
            WHERE meta_key = %s",
            "_vacancy_salary_{$type}"
        ));
    }
}
