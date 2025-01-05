<?php
class RCWP_Data_Import {
    private $logger;
    private $allowed_types = ['csv', 'json', 'xml'];
    private $batch_size = 100;

    public function __construct($logger) {
        $this->logger = $logger;

        add_action('admin_post_rcwp_import_data', array($this, 'handle_import'));
        add_filter('upload_mimes', array($this, 'allow_import_types'));
    }

    /**
     * Handle import request
     */
    public function handle_import() {
        try {
            check_admin_referer('rcwp_import_data', 'nonce');

            if (!isset($_FILES['import_file'])) {
                throw new Exception(__('No file uploaded', 'recruit-connect-wp'));
            }

            $file = $_FILES['import_file'];
            $type = $this->get_file_type($file['name']);

            if (!in_array($type, $this->allowed_types)) {
                throw new Exception(__('Invalid file type', 'recruit-connect-wp'));
            }

            $data = $this->parse_import_file($file, $type);
            $this->process_import_data($data);

            wp_redirect(add_query_arg(
                array(
                    'page' => 'rcwp-tools',
                    'message' => 'import_success'
                ),
                admin_url('admin.php')
            ));
            exit;

        } catch (Exception $e) {
            $this->logger->log('Import error: ' . $e->getMessage(), 'error');
            wp_die($e->getMessage());
        }
    }

    /**
     * Parse import file
     */
    private function parse_import_file($file, $type) {
        $content = file_get_contents($file['tmp_name']);

        switch ($type) {
            case 'csv':
                return $this->parse_csv($content);

            case 'json':
                return $this->parse_json($content);

            case 'xml':
                return $this->parse_xml($content);

            default:
                throw new Exception(__('Unsupported file type', 'recruit-connect-wp'));
        }
    }

    /**
     * Parse CSV content
     */
    private function parse_csv($content) {
        $data = array();
        $rows = str_getcsv($content, "\n");

        if (empty($rows)) {
            return $data;
        }

        $headers = str_getcsv(array_shift($rows));

        foreach ($rows as $row) {
            $values = str_getcsv($row);
            if (count($headers) === count($values)) {
                $data[] = array_combine($headers, $values);
            }
        }

        return $data;
    }

    /**
     * Parse JSON content
     */
    private function parse_json($content) {
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON format: ' . json_last_error_msg());
        }

        return $data;
    }

    /**
     * Parse XML content
     */
    private function parse_xml($content) {
        libxml_use_internal_errors(true);

        $xml = simplexml_load_string($content);

        if ($xml === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            throw new Exception('Invalid XML format: ' . $errors[0]->message);
        }

        return json_decode(json_encode($xml), true);
    }

    /**
     * Process import data
     */
    private function process_import_data($data) {
        global $wpdb;

        $wpdb->query('START TRANSACTION');

        try {
            $processed = 0;
            $total = count($data);

            foreach (array_chunk($data, $this->batch_size) as $batch) {
                foreach ($batch as $item) {
                    $this->import_item($item);
                    $processed++;
                }

                // Log progress
                $this->logger->log(sprintf(
                    'Processed %d of %d items',
                    $processed,
                    $total
                ));
            }

            $wpdb->query('COMMIT');

            $this->logger->log('Import completed successfully');

        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            throw $e;
        }
    }

    /**
     * Import single item
     */
    private function import_item($item) {
        if (empty($item['type'])) {
            throw new Exception(__('Missing item type', 'recruit-connect-wp'));
        }

        switch ($item['type']) {
            case 'vacancy':
                $this->import_vacancy($item);
                break;

            case 'application':
                $this->import_application($item);
                break;

            default:
                throw new Exception(sprintf(
                    __('Unknown item type: %s', 'recruit-connect-wp'),
                    $item['type']
                ));
        }
    }

    /**
     * Import vacancy
     */
    private function import_vacancy($data) {
        $post_data = array(
            'post_type' => 'vacancy',
            'post_title' => $data['title'],
            'post_content' => $data['content'] ?? '',
            'post_status' => $data['status'] ?? 'publish'
        );

        $post_id = wp_insert_post($post_data, true);

        if (is_wp_error($post_id)) {
            throw new Exception($post_id->get_error_message());
        }

        // Import meta data
        if (!empty($data['meta']) && is_array($data['meta'])) {
            foreach ($data['meta'] as $key => $value) {
                update_post_meta($post_id, $key, $value);
            }
        }

        return $post_id;
    }

    /**
     * Import application
     */
    private function import_application($data) {
        global $wpdb;

        $required_fields = array('vacancy_id', 'first_name', 'last_name', 'email');

        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                throw new Exception(sprintf(
                    __('Missing required field: %s', 'recruit-connect-wp'),
                    $field
                ));
            }
        }

        $result = $wpdb->insert(
            $wpdb->prefix . 'rcwp_applications',
            array(
                'vacancy_id' => $data['vacancy_id'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? '',
                'motivation' => $data['motivation'] ?? '',
                'status' => $data['status'] ?? 'pending',
                'created_at' => $data['created_at'] ?? current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        if ($result === false) {
            throw new Exception($wpdb->last_error);
        }

        return $wpdb->insert_id;
    }

    /**
     * Get file type from filename
     */
    private function get_file_type($filename) {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Allow import file types
     */
    public function allow_import_types($mimes) {
        $mimes['csv'] = 'text/csv';
        $mimes['json'] = 'application/json';
        $mimes['xml'] = 'application/xml';
        return $mimes;
    }
}
