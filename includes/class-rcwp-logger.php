<?php
/**
 * Handles logging functionality
 *
 * @link       https://www.nubos.nl/en
 * @since      1.0.0
 *
 * @package    Recruit_Connect_WP
 * @subpackage Recruit_Connect_WP/includes
 */

class RCWP_Logger {
	/**
	 * Log levels
	 */
	const ERROR = 'error';
	const WARNING = 'warning';
	const INFO = 'info';
	const DEBUG = 'debug';

	/**
	 * Log a message
	 *
	 * @param string $message The message to log
	 * @param string $level The log level (error, warning, info, debug)
	 */
	public function log($message, $level = self::INFO) {
		$this->write_log($message, $level);
	}

	/**
	 * Log an info message
	 *
	 * @param string $message The message to log
	 */
	public function info($message) {
		$this->log($message, self::INFO);
	}

	/**
	 * Log an error message
	 *
	 * @param string $message The message to log
	 */
	public function error($message) {
		$this->log($message, self::ERROR);
	}

	/**
	 * Log a warning message
	 *
	 * @param string $message The message to log
	 */
	public function warning($message) {
		$this->log($message, self::WARNING);
	}

	/**
	 * Log a debug message
	 *
	 * @param string $message The message to log
	 */
	public function debug($message) {
		$this->log($message, self::DEBUG);
	}

	/**
	 * Write log entry to database
	 *
	 * @param string $message The message to log
	 * @param string $level The log level
	 */
	private function write_log($message, $level) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'rcwp_logs';

		$wpdb->insert(
			$table_name,
			array(
				'message' => $message,
				'level' => $level,
				'created_at' => current_time('mysql')
			),
			array(
				'%s',
				'%s',
				'%s'
			)
		);
	}

	/**
	 * Get recent logs
	 *
	 * @param int $limit Number of logs to retrieve
	 * @return array Array of log entries
	 */
	public function get_recent_logs($limit = 50) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'rcwp_logs';

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT %d",
				$limit
			)
		);
	}

	/**
	 * Clear all logs
	 */
	public function clear_logs() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'rcwp_logs';

		$wpdb->query("TRUNCATE TABLE {$table_name}");
	}
}
