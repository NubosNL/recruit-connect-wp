<?php
class RCWP_Test_Case extends WP_UnitTestCase {
    protected $cache;
    protected $logger;
    protected $api;

    public function setUp(): void {
        parent::setUp();

        // Initialize test components
        $this->cache = new RCWP_Cache();
        $this->logger = new RCWP_Logger();
        $this->api = new RCWP_API($this->logger, $this->cache);

        // Clear caches
        $this->cache->flush_all_cache();

        // Reset options
        delete_option('rcwp_api_key');
        delete_option('rcwp_api_base_url');
    }

    public function tearDown(): void {
        parent::tearDown();

        // Clean up test data
        $this->delete_test_vacancies();
        $this->delete_test_applications();
    }

    /**
     * Create test vacancy
     */
    protected function create_test_vacancy($args = array()) {
        $defaults = array(
            'post_type' => 'vacancy',
            'post_title' => 'Test Vacancy',
            'post_content' => 'Test vacancy content',
            'post_status' => 'publish'
        );

        $post_id = wp_insert_post(wp_parse_args($args, $defaults));

        // Add default meta
        update_post_meta($post_id, '_vacancy_id', 'TEST_' . $post_id);
        update_post_meta($post_id, '_vacancy_salary', '50000');
        update_post_meta($post_id, '_vacancy_location', 'Test City');

        return $post_id;
    }

    /**
     * Create test application
     */
    protected function create_test_application($vacancy_id = null) {
        global $wpdb;

        if (!$vacancy_id) {
            $vacancy_id = $this->create_test_vacancy();
        }

        return $wpdb->insert(
            $wpdb->prefix . 'rcwp_applications',
            array(
                'vacancy_id' => $vacancy_id,
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@example.com',
                'status' => 'pending',
                'created_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s')
        );
    }

    /**
     * Delete test vacancies
     */
    protected function delete_test_vacancies() {
        $posts = get_posts(array(
            'post_type' => 'vacancy',
            'posts_per_page' => -1,
            'post_status' => 'any'
        ));

        foreach ($posts as $post) {
            wp_delete_post($post->ID, true);
        }
    }

    /**
     * Delete test applications
     */
    protected function delete_test_applications() {
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}rcwp_applications");
    }

    /**
     * Mock API response
     */
    protected function mock_api_response($response) {
        add_filter('pre_http_request', function() use ($response) {
            return array(
                'body' => json_encode($response),
                'response' => array('code' => 200),
                'headers' => array()
            );
        });
    }
}
