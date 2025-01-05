<?php
class RCWP_Logger {
    private $log_table;
    private $max_logs = 1000; // Maximum number of log entries to keep

    public function __construct() {
        global $wpdb;
        $this->log_table = $wpdb->prefix . 'rcwp_logs';
        $this->maybe_create_log_table();
    }

    private function maybe_create_log_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->log_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            message text NOT NULL,
            type varchar(50) NOT NULL,
            context text NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY type (type),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function log($message, $type = 'info', $context = array()) {
        global $wpdb;

        // Serialize context if it's not empty
        $context_data = !empty($context) ? serialize($context) : null;

        $wpdb->insert(
            $this->log_table,
            array(
                'message' => $message,
                'type' => $type,
                'context' => $context_data
            ),
            array('%s', '%s', '%s')
        );

        // Cleanup old logs
        $this->cleanup_old_logs();
    }

    public function get_logs($args = array()) {
        global $wpdb;

        $defaults = array(
            'limit' => 100,
            'offset' => 0,
            'type' => null,
            'date_from' => null,
            'date_to' => null,
            'search' => null
        );

        $args = wp_parse_args($args, $defaults);
        $where = array('1=1');
        $prepare = array();

        // Filter by type
        if (!empty($args['type'])) {
            $where[] = 'type = %s';
            $prepare[] = $args['type'];
        }

        // Filter by date range
        if (!empty($args['date_from'])) {
            $where[] = 'created_at >= %s';
            $prepare[] = $args['date_from'];
        }
        if (!empty($args['date_to'])) {
            $where[] = 'created_at <= %s';
            $prepare[] = $args['date_to'];
        }

        // Search in message
        if (!empty($args['search'])) {
            $where[] = 'message LIKE %s';
            $prepare[] = '%' . $wpdb->esc_like($args['search']) . '%';
        }

        // Build query
        $query = "SELECT * FROM {$this->log_table} WHERE " .
                 implode(' AND ', $where) .
                 " ORDER BY created_at DESC LIMIT %d OFFSET %d";

        $prepare[] = $args['limit'];
        $prepare[] = $args['offset'];

        return $wpdb->get_results($wpdb->prepare($query, $prepare));
    }

    public function get_log_count($args = array()) {
        global $wpdb;

        $where = array('1=1');
        $prepare = array();

        if (!empty($args['type'])) {
            $where[] = 'type = %s';
            $prepare[] = $args['type'];
        }

        if (!empty($args['date_from'])) {
            $where[] = 'created_at >= %s';
            $prepare[] = $args['date_from'];
        }

        if (!empty($args['date_to'])) {
            $where[] = 'created_at <= %s';
            $prepare[] = $args['date_to'];
        }

        $query = "SELECT COUNT(*) FROM {$this->log_table} WHERE " .
                 implode(' AND ', $where);

        return $wpdb->get_var($wpdb->prepare($query, $prepare));
    }

    public function clear_logs() {
        global $wpdb;
        return $wpdb->query("TRUNCATE TABLE {$this->log_table}");
    }

    private function cleanup_old_logs() {
        global $wpdb;

        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$this->log_table}");

        if ($count > $this->max_logs) {
            $to_delete = $count - $this->max_logs;

            $wpdb->query($wpdb->prepare(
                "DELETE FROM {$this->log_table} 
                ORDER BY created_at ASC 
                LIMIT %d",
                $to_delete
            ));
        }
    }

    public function export_logs($args = array()) {
        $logs = $this->get_logs(array_merge(
            $args,
            array('limit' => 5000) // Limit for export
        ));

        $filename = 'rcwp-logs-' . date('Y-m-d') . '.csv';
        $headers = array(
            'ID',
            'Message',
            'Type',
            'Context',
            'Created At'
        );

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);

        foreach ($logs as $log) {
            $context = !empty($log->context) ? unserialize($log->context) : '';
            if (is_array($context)) {
                $context = json_encode($context);
            }

            fputcsv($output, array(
                $log->id,
                $log->message,
                $log->type,
                $context,
                $log->created_at
            ));
        }

        fclose($output);
        exit;
    }
}
