<?php
class RCWP_XML_Importer {
    private $logger;
    private $batch_size = 50; // Process vacancies in batches
    private $processed_ids = array();

    public function __construct($logger) {
        $this->logger = $logger;
        add_action('rcwp_xml_import_cron', array($this, 'import_vacancies'));
        add_action('admin_post_rcwp_sync_now', array($this, 'handle_manual_sync'));
        add_action('rcwp_after_import', array($this, 'cleanup_old_vacancies'));
    }

    public function handle_manual_sync() {
        check_admin_referer('rcwp_sync_now');

        $this->logger->log('Manual sync initiated');
        $result = $this->import_vacancies();

        wp_redirect(add_query_arg(
            array(
                'page' => 'recruit-connect-wp',
                'sync' => $result ? 'success' : 'error'
            ),
            admin_url('admin.php')
        ));
        exit;
    }

    public function import_vacancies() {
        $this->logger->log('Starting vacancy import');

        $xml_url = get_option('rcwp_xml_url');
        if (empty($xml_url)) {
            $this->logger->log('Error: XML URL not configured', 'error');
            return false;
        }

        try {
            $xml_data = $this->fetch_xml($xml_url);
            $jobs = $this->parse_xml($xml_data);

            if (empty($jobs)) {
                $this->logger->log('No jobs found in XML feed', 'warning');
                return false;
            }

            $this->logger->log(sprintf('Found %d jobs in XML feed', count($jobs)));

            // Process jobs in batches
            $batches = array_chunk($jobs, $this->batch_size);
            foreach ($batches as $batch) {
                $this->process_job_batch($batch);
            }

            // Cleanup old vacancies
            do_action('rcwp_after_import');

            $this->logger->log('Import completed successfully');
            return true;

        } catch (Exception $e) {
            $this->logger->log('Import failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    private function fetch_xml($url) {
        $response = wp_remote_get($url, array(
            'timeout' => 60,
            'sslverify' => false
        ));

        if (is_wp_error($response)) {
            throw new Exception('Failed to fetch XML: ' . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        if (empty($body)) {
            throw new Exception('Empty response from XML feed');
        }

        return $body;
    }

    private function parse_xml($xml_string) {
        libxml_use_internal_errors(true);

        try {
            $xml = new SimpleXMLElement($xml_string);
            $jobs = array();

            foreach ($xml->job as $job) {
                $jobs[] = $this->normalize_job_data($job);
            }

            return $jobs;

        } catch (Exception $e) {
            $errors = libxml_get_errors();
            libxml_clear_errors();

            $error_messages = array();
            foreach ($errors as $error) {
                $error_messages[] = sprintf(
                    'Line %d: %s',
                    $error->line,
                    $error->message
                );
            }

            throw new Exception('XML parsing failed: ' . implode('; ', $error_messages));
        }
    }

    private function normalize_job_data($job) {
        return array(
            'id' => (string) $job->id,
            'title' => (string) $job->title,
            'description' => (string) $job->description,
            'meta' => array(
                '_vacancy_category' => (string) $job->category,
                '_vacancy_city' => (string) $job->city,
                '_vacancy_createdat' => (string) $job->createdate,
                '_vacancy_company' => (string) $job->company,
                '_vacancy_streetaddress' => (string) $job->streetaddress,
                '_vacancy_postalcode' => (string) $job->postalcode,
                '_vacancy_state' => (string) $job->state,
                '_vacancy_country' => (string) $job->country,
                '_vacancy_salary' => (string) $job->salary,
                '_vacancy_education' => (string) $job->education,
                '_vacancy_jobtype' => (string) $job->jobtype,
                '_vacancy_experience' => (string) $job->experience,
                '_vacancy_recruitername' => (string) $job->recruitername,
                '_vacancy_recruiteremail' => (string) $job->recruiteremail,
                '_vacancy_recruiterimage' => (string) $job->recruiterimage,
                '_vacancy_remotetype' => (string) $job->remotetype,
                '_vacancy_custom1' => (string) $job->custom1,
                '_vacancy_custom2' => (string) $job->custom2,
                '_vacancy_custom3' => (string) $job->custom3,
                '_vacancy_custom4' => (string) $job->custom4,
                '_vacancy_custom5' => (string) $job->custom5,
                '_vacancy_id' => (string) $job->id
            )
        );
    }

    private function process_job_batch($jobs) {
        foreach ($jobs as $job) {
            try {
                $this->process_single_job($job);
                $this->processed_ids[] = $job['id'];
            } catch (Exception $e) {
                $this->logger->log(sprintf(
                    'Error processing job %s: %s',
                    $job['id'],
                    $e->getMessage()
                ), 'error');
            }
        }
    }

    private function process_single_job($job) {
        $existing_post = $this->get_existing_vacancy($job['id']);

        if ($existing_post) {
            $this->update_vacancy($existing_post, $job);
            $this->logger->log(sprintf('Updated vacancy: %s', $job['id']));
        } else {
            $this->create_vacancy($job);
            $this->logger->log(sprintf('Created vacancy: %s', $job['id']));
        }
    }

    private function get_existing_vacancy($job_id) {
        $posts = get_posts(array(
            'post_type' => 'vacancy',
            'meta_key' => '_vacancy_id',
            'meta_value' => $job_id,
            'posts_per_page' => 1,
            'post_status' => 'any'
        ));

        return !empty($posts) ? $posts[0] : null;
    }

    private function create_vacancy($job) {
        $post_data = array(
            'post_title' => wp_strip_all_tags($job['title']),
            'post_content' => $job['description'],
            'post_type' => 'vacancy',
            'post_status' => 'publish'
        );

        $post_id = wp_insert_post($post_data, true);
        if (is_wp_error($post_id)) {
            throw new Exception($post_id->get_error_message());
        }

        $this->update_vacancy_meta($post_id, $job['meta']);
    }

    private function update_vacancy($post, $job) {
        $post_data = array(
            'ID' => $post->ID,
            'post_title' => wp_strip_all_tags($job['title']),
            'post_content' => $job['description'],
            'post_status' => 'publish'
        );

        $updated = wp_update_post($post_data, true);
        if (is_wp_error($updated)) {
            throw new Exception($updated->get_error_message());
        }

        $this->update_vacancy_meta($post->ID, $job['meta']);
    }

    private function update_vacancy_meta($post_id, $meta_data) {
        foreach ($meta_data as $key => $value) {
            update_post_meta($post_id, $key, $value);
        }
    }

    public function cleanup_old_vacancies() {
        if (empty($this->processed_ids)) {
            return;
        }

        $args = array(
            'post_type' => 'vacancy',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_vacancy_id',
                    'value' => $this->processed_ids,
                    'compare' => 'NOT IN'
                )
            )
        );

        $old_posts = get_posts($args);

        foreach ($old_posts as $post_id) {
            wp_update_post(array(
                'ID' => $post_id,
                'post_status' => 'draft'
            ));
            $this->logger->log(sprintf('Archived vacancy: %d', $post_id));
        }
    }
}
