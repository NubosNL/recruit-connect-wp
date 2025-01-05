<?php
class RCWP_Data_Export {
    private $logger;
    private $formats = ['csv', 'json', 'xml'];
    private $batch_size = 500;

    public function __construct($logger) {
        $this->logger = $logger;

        add_action('admin_post_rcwp_export_data', array($this, 'handle_export'));
        add_filter('rcwp_export_formats', array($this, 'register_formats'));
    }

    /**
     * Handle export request
     */
    public function handle_export() {
        try {
            check_admin_referer('rcwp_export_data', 'nonce');

            $format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : 'csv';
            $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'all';
            $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
            $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';

            if (!in_array($format, $this->formats)) {
                throw new Exception(__('Invalid export format', 'recruit-connect-wp'));
            }

            $data = $this->get_export_data($type, $date_from, $date_to);
            $this->output_data($data, $format, $type);

        } catch (Exception $e) {
            $this->logger->log('Export error: ' . $e->getMessage(), 'error');
            wp_die($e->getMessage());
        }
    }

    /**
     * Get data for export
     */
    private function get_export_data($type, $date_from, $date_to) {
        $data = array();

        switch ($type) {
            case 'vacancies':
                $data = $this->get_vacancies_data($date_from, $date_to);
                break;

            case 'applications':
                $data = $this->get_applications_data($date_from, $date_to);
                break;

            case 'statistics':
                $data = $this->get_statistics_data($date_from, $date_to);
                break;

            case 'all':
                $data = array(
                    'vacancies' => $this->get_vacancies_data($date_from, $date_to),
                    'applications' => $this->get_applications_data($date_from, $date_to),
                    'statistics' => $this->get_statistics_data($date_from, $date_to)
                );
                break;

            default:
                throw new Exception(__('Invalid export type', 'recruit-connect-wp'));
        }

        return $data;
    }

    /**
     * Get vacancies data
     */
    private function get_vacancies_data($date_from, $date_to) {
        $args = array(
            'post_type' => 'vacancy',
            'posts_per_page' => -1,
            'post_status' => 'any'
        );

        if ($date_from) {
            $args['date_query'][]['after'] = $date_from;
        }
        if ($date_to) {
            $args['date_query'][]['before'] = $date_to;
        }

        $vacancies = get_posts($args);
        $data = array();

        foreach ($vacancies as $vacancy) {
            $data[] = array(
                'id' => $vacancy->ID,
                'title' => $vacancy->post_title,
                'content' => $vacancy->post_content,
                'status' => $vacancy->post_status,
                'date' => $vacancy->post_date,
                'modified' => $vacancy->post_modified,
                'meta' => get_post_meta($vacancy->ID)
            );
        }

        return $data;
    }

    /**
     * Get applications data
     */
    private function get_applications_data($date_from, $date_to) {
        global $wpdb;

        $query = "SELECT * FROM {$wpdb->prefix}rcwp_applications WHERE 1=1";
        $params = array();

        if ($date_from) {
            $query .= " AND created_at >= %s";
            $params[] = $date_from;
        }
        if ($date_to) {
            $query .= " AND created_at <= %s";
            $params[] = $date_to;
        }

        if (!empty($params)) {
            $query = $wpdb->prepare($query, $params);
        }

        return $wpdb->get_results($query, ARRAY_A);
    }

    /**
     * Get statistics data
     */
    private function get_statistics_data($date_from, $date_to) {
        global $wpdb;

        $stats = array(
            'total_vacancies' => wp_count_posts('vacancy')->publish,
            'total_applications' => $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->prefix}rcwp_applications"
            ),
            'applications_by_status' => $this->get_applications_by_status(),
            'top_vacancies' => $this->get_top_vacancies(),
            'monthly_stats' => $this->get_monthly_stats($date_from, $date_to)
        );

        return $stats;
    }

    /**
     * Output data in specified format
     */
    private function output_data($data, $format, $type) {
        $filename = 'rcwp-export-' . $type . '-' . date('Y-m-d') . '.' . $format;

        header('Content-Type: ' . $this->get_content_type($format));
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        switch ($format) {
            case 'csv':
                $this->output_csv($data);
                break;

            case 'json':
                $this->output_json($data);
                break;

            case 'xml':
                $this->output_xml($data);
                break;
        }

        exit;
    }

    /**
     * Output CSV format
     */
    private function output_csv($data) {
        $output = fopen('php://output', 'w');

        // Use first row as headers
        if (!empty($data)) {
            if (isset($data[0]) && is_array($data[0])) {
                fputcsv($output, array_keys($data[0]));
            }

            // Output data rows
            foreach ($data as $row) {
                if (is_array($row)) {
                    // Handle nested arrays/objects
                    array_walk_recursive($row, function(&$item) {
                        if (is_array($item) || is_object($item)) {
                            $item = json_encode($item);
                        }
                    });
                    fputcsv($output, $row);
                }
            }
        }

        fclose($output);
    }

    /**
     * Output JSON format
     */
    private function output_json($data) {
        echo json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Output XML format
     */
    private function output_xml($data) {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><data></data>');
        $this->array_to_xml($data, $xml);
        echo $xml->asXML();
    }

    /**
     * Convert array to XML
     */
    private function array_to_xml($data, &$xml) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item' . $key;
                }
                $subnode = $xml->addChild($key);
                $this->array_to_xml($value, $subnode);
            } else {
                $xml->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }

    /**
     * Get content type for format
     */
    private function get_content_type($format) {
        $types = array(
            'csv' => 'text/csv',
            'json' => 'application/json',
            'xml' => 'application/xml'
        );

        return $types[$format] ?? 'text/plain';
    }

    /**
     * Get applications by status
     */
    private function get_applications_by_status() {
        global $wpdb;

        return $wpdb->get_results(
            "SELECT status, COUNT(*) as count 
            FROM {$wpdb->prefix}rcwp_applications 
            GROUP BY status",
            ARRAY_A
        );
    }

    /**
     * Get top vacancies
     */
    private function get_top_vacancies() {
        global $wpdb;

        return $wpdb->get_results(
            "SELECT vacancy_id, COUNT(*) as applications 
            FROM {$wpdb->prefix}rcwp_applications 
            GROUP BY vacancy_id 
            ORDER BY applications DESC 
            LIMIT 10",
            ARRAY_A
        );
    }

    /**
     * Get monthly statistics
     */
    private function get_monthly_stats($date_from, $date_to) {
        global $wpdb;

        $query = "SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as total_applications
            FROM {$wpdb->prefix}rcwp_applications
            WHERE 1=1";

        $params = array();

        if ($date_from) {
            $query .= " AND created_at >= %s";
            $params[] = $date_from;
        }
        if ($date_to) {
            $query .= " AND created_at <= %s";
            $params[] = $date_to;
        }

        $query .= " GROUP BY month ORDER BY month";

        if (!empty($params)) {
            $query = $wpdb->prepare($query, $params);
        }

        return $wpdb->get_results($query, ARRAY_A);
    }
}
