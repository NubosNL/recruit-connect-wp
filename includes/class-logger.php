<?php
namespace RecruitConnect;

class Logger {
    private $log_file;

    public function __construct() {
        $upload_dir = wp_upload_dir();
        $this->log_file = $upload_dir['basedir'] . '/recruit-connect-logs.txt';
    }

	public function log($message) {
		$timestamp = current_time('mysql');

		// Get existing logs
		$logs = get_option('recruit_connect_logs', array());

		// Add new log entry
		array_unshift($logs, array(
			'timestamp' => $timestamp,
			'message' => $message
		));

		// Keep only last 100 entries
		$logs = array_slice($logs, 0, 100);

		// Save logs
		update_option('recruit_connect_logs', $logs);
	}
}
