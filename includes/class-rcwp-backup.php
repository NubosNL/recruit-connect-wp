<?php
/**
 * Handle backup functionality
 *
 * @link       https://www.nubos.nl/en
 * @since      1.0.0
 *
 * @package    Recruit_Connect_WP
 * @subpackage Recruit_Connect_WP/includes
 */

class RCWP_Backup {
	/**
	 * The logger instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      RCWP_Logger    $logger    The logger instance.
	 */
	private $logger;

	/**
	 * Initialize the class.
	 *
	 * @since    1.0.0
	 * @param    RCWP_Logger    $logger    The logger instance.
	 */
	public function __construct($logger) {
		$this->logger = $logger;
	}

	/**
	 * Get backup status
	 *
	 * @since    1.0.0
	 * @return   array    The backup status
	 */
	public function get_status() {
		return array(
			'enabled' => get_option('rcwp_backup_enabled', false),
			'last_backup' => get_option('rcwp_last_backup', ''),
			'next_backup' => $this->get_next_backup_date(),
			'backup_location' => get_option('rcwp_backup_location', ''),
			'total_backups' => $this->count_backups()
		);
	}

	/**
	 * Get backup history
	 *
	 * @since    1.0.0
	 * @return   array    Array of backup history items
	 */
	public function get_history() {
		$history = get_option('rcwp_backup_history', array());

		if (empty($history)) {
			return array();
		}

		// Sort by date descending
		usort($history, function($a, $b) {
			return strtotime($b['date']) - strtotime($a['date']);
		});

		return $history;
	}

	/**
	 * Create a new backup
	 *
	 * @since    1.0.0
	 * @return   bool|WP_Error    True on success, WP_Error on failure
	 */
	public function create_backup() {
		try {
			$this->logger->info('Starting backup process');

			// Create backup directory if it doesn't exist
			$backup_dir = $this->get_backup_directory();
			if (!file_exists($backup_dir)) {
				wp_mkdir_p($backup_dir);
			}

			// Generate backup filename
			$filename = 'backup-' . date('Y-m-d-H-i-s') . '.zip';
			$backup_file = $backup_dir . '/' . $filename;

			// Get data to backup
			$data = $this->get_backup_data();

			// Create ZIP file
			$result = $this->create_zip_backup($backup_file, $data);
			if (is_wp_error($result)) {
				throw new Exception($result->get_error_message());
			}

			// Update backup history
			$this->update_backup_history(array(
				'date' => current_time('mysql'),
				'type' => 'manual',
				'status' => 'success',
				'filename' => $filename,
				'size' => filesize($backup_file),
				'download_url' => $this->get_backup_download_url($filename)
			));

			// Update last backup time
			update_option('rcwp_last_backup', current_time('mysql'));

			$this->logger->info('Backup completed successfully');
			return true;

		} catch (Exception $e) {
			$this->logger->error('Backup failed: ' . $e->getMessage());
			return new WP_Error('backup_failed', $e->getMessage());
		}
	}

	/**
	 * Get the backup directory path
	 *
	 * @since    1.0.0
	 * @return   string    The backup directory path
	 */
	private function get_backup_directory() {
		$upload_dir = wp_upload_dir();
		return $upload_dir['basedir'] . '/rcwp-backups';
	}

	/**
	 * Get the next scheduled backup date
	 *
	 * @since    1.0.0
	 * @return   string    The next backup date or empty string if not scheduled
	 */
	private function get_next_backup_date() {
		$timestamp = wp_next_scheduled('rcwp_scheduled_backup');
		return $timestamp ? date('Y-m-d H:i:s', $timestamp) : '';
	}

	/**
	 * Count total number of backups
	 *
	 * @since    1.0.0
	 * @return   int    The total number of backups
	 */
	private function count_backups() {
		$history = get_option('rcwp_backup_history', array());
		return count($history);
	}

	/**
	 * Update backup history
	 *
	 * @since    1.0.0
	 * @param    array    $backup    The backup details
	 */
	private function update_backup_history($backup) {
		$history = get_option('rcwp_backup_history', array());
		array_unshift($history, $backup);

		// Keep only last 10 entries
		$history = array_slice($history, 0, 10);

		update_option('rcwp_backup_history', $history);
	}

	/**
	 * Get backup download URL
	 *
	 * @since    1.0.0
	 * @param    string    $filename    The backup filename
	 * @return   string    The download URL
	 */
	private function get_backup_download_url($filename) {
		$upload_dir = wp_upload_dir();
		return $upload_dir['baseurl'] . '/rcwp-backups/' . $filename;
	}

	/**
	 * Get data to backup
	 *
	 * @since    1.0.0
	 * @return   array    Array of data to backup
	 */
	private function get_backup_data() {
		return array(
			'vacancies' => $this->get_vacancies_data(),
			'applications' => $this->get_applications_data(),
			'settings' => $this->get_settings_data()
		);
	}

	/**
	 * Get vacancies data
	 *
	 * @since    1.0.0
	 * @return   array    Array of vacancies data
	 */
	private function get_vacancies_data() {
		$args = array(
			'post_type' => 'vacancy',
			'posts_per_page' => -1,
			'post_status' => 'any'
		);

		$vacancies = get_posts($args);
		$data = array();

		foreach ($vacancies as $vacancy) {
			$data[] = array(
				'id' => $vacancy->ID,
				'title' => $vacancy->post_title,
				'content' => $vacancy->post_content,
				'status' => $vacancy->post_status,
				'meta' => get_post_meta($vacancy->ID)
			);
		}

		return $data;
	}

	/**
	 * Get applications data
	 *
	 * @since    1.0.0
	 * @return   array    Array of applications data
	 */
	private function get_applications_data() {
		global $wpdb;
		return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}rcwp_applications");
	}

	/**
	 * Get settings data
	 *
	 * @since    1.0.0
	 * @return   array    Array of settings data
	 */
	private function get_settings_data() {
		$settings = array();
		$option_names = array(
			'rcwp_xml_url',
			'rcwp_application_url',
			'rcwp_vacancy_url_parameter',
			'rcwp_enable_detail_page',
			'rcwp_search_components',
			'rcwp_thank_you_message',
			'rcwp_required_fields',
			'rcwp_sync_frequency'
		);

		foreach ($option_names as $option) {
			$settings[$option] = get_option($option);
		}

		return $settings;
	}

	/**
	 * Create ZIP backup
	 *
	 * @since    1.0.0
	 * @param    string    $file    The backup file path
	 * @param    array     $data    The data to backup
	 * @return   bool|WP_Error    True on success, WP_Error on failure
	 */
	private function create_zip_backup($file, $data) {
		if (!class_exists('ZipArchive')) {
			return new WP_Error('zip_missing', 'ZipArchive class is not available');
		}

		try {
			$zip = new ZipArchive();
			if ($zip->open($file, ZipArchive::CREATE) !== true) {
				throw new Exception('Unable to create ZIP file');
			}

			// Add data files
			$zip->addFromString('vacancies.json', json_encode($data['vacancies']));
			$zip->addFromString('applications.json', json_encode($data['applications']));
			$zip->addFromString('settings.json', json_encode($data['settings']));

			$zip->close();
			return true;

		} catch (Exception $e) {
			return new WP_Error('zip_failed', $e->getMessage());
		}
	}
}
