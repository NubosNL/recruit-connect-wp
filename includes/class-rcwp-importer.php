<?php
class RCWP_XML_Importer {
    private $logger;

    public function __construct() {
        $this->logger = new RCWP_Logger();
        add_action('rcwp_xml_import_cron', array($this, 'import_vacancies'));
        add_action('admin_post_rcwp_sync_now', array($this, 'handle_manual_sync'));
    }

    public function handle_manual_sync() {
        check_admin_referer('rcwp_sync_now');
        $this->import_vacancies();
        wp_redirect(admin_url('admin.php?page=recruit-connect-wp&sync=complete'));
        exit;
    }

    public function import_vacancies() {
        $xml_url = get_option('rcwp_xml_url');
        if (empty($xml_url)) {
            $this->logger->log('Error: XML URL not configured');
            return;
        }

        $response = wp_remote_get($xml_url);
        if (is_wp_error($response)) {
            $this->logger->log('Error fetching XML: ' . $response->get_error_message());
            return;
        }

        $xml_string = wp_remote_retrieve_body($response);

        try {
            $xml = new SimpleXMLElement($xml_string);
        } catch (Exception $e) {
            $this->logger->log('Error parsing XML: ' . $e->getMessage());
            return;
        }

        $this->logger->log('Total jobs found: ' . count($xml->job));

        foreach ($xml->job as $job) {
            $this->process_job($job);
        }
    }

    private function process_job($job) {
        $job_id = (string) $job->id;
        $this->logger->log('Processing job: ' . $job_id);

        // Extract job data
        $job_data = array(
            'title' => (string) $job->title,
            'description' => (string) $job->description,
            'category' => (string) $job->category,
            'city' => (string) $job->city,
            // ... extract other fields
        );

        // Update or create post
        $existing_post_id = $this->get_existing_vacancy($job_id);

        if ($existing_post_id) {
            $this->update_vacancy($existing_post_id, $job_data);
        } else {
            $this->create_vacancy($job_id, $job_data);
        }
    }

    private function get_existing_vacancy($job_id) {
        $args = array(
            'post_type' => 'vacancy',
            'meta_key' => '_vacancy_id',
            'meta_value' => $job_id,
            'posts_per_page' => 1,
            'fields' => 'ids'
        );

        $posts = get_posts($args);
        return !empty($posts) ? $posts[0] : false;
    }

    private function create_vacancy($job_id, $job_data) {
        $post_data = array(
            'post_title' => $job_data['title'],
            'post_content' => $job_data['description'],
            'post_type' => 'vacancy',
            'post_status' => 'publish'
        );

        $post_id = wp_insert_post($post_data);

        if (!is_wp_error($post_id)) {
            $this->update_vacancy_meta($post_id, $job_id, $job_data);
            $this->logger->log('Created vacancy: ' . $job_id);
        }
    }

    private function update_vacancy($post_id, $job_data) {
        $post_data = array(
            'ID' => $post_id,
            'post_title' => $job_data['title'],
            'post_content' => $job_data['description']
        );

        wp_update_post($post_data);
        $this->update_vacancy_meta($post_id, $job_data['id'], $job_data);
        $this->logger->log('Updated vacancy: ' . $job_data['id']);
    }

    private function update_vacancy_meta($post_id, $job_id, $job_data) {
        update_post_meta($post_id, '_vacancy_id', $job_id);
        update_post_meta($post_id, '_vacancy_category', $job_data['category']);
        update_post_meta($post_id, '_vacancy_city', $job_data['city']);
        // ... update other meta fields
    }
}
