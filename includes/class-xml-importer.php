<?php
namespace RecruitConnect;

class XMLImporter {
	private $logger;
	private $processed_ids = array(); // Track processed vacancy IDs

	public function __construct() {
		$this->logger = new Logger();
	}

	public function import() {
		try {
			// Reset processed IDs array
			$this->processed_ids = array();

			// Get XML URL
			$xml_url = get_option('recruit_connect_xml_url');
			if (empty($xml_url)) {
				throw new \Exception('No XML URL configured');
			}

			// Log start
			$this->logger->log('Starting import from: ' . $xml_url);

			// Fetch XML
			$response = wp_remote_get($xml_url, array(
				'timeout' => 30,
				'sslverify' => false
			));

			// Check for wp_remote_get error
			if (is_wp_error($response)) {
				throw new \Exception('Failed to fetch XML: ' . $response->get_error_message());
			}

			// Check response code
			$response_code = wp_remote_retrieve_response_code($response);
			if ($response_code !== 200) {
				throw new \Exception('HTTP Error: ' . $response_code);
			}

			// Get response body
			$xml_string = wp_remote_retrieve_body($response);
			if (empty($xml_string)) {
				throw new \Exception('Empty XML response');
			}

			// Parse XML
			libxml_use_internal_errors(true);
			$xml = simplexml_load_string($xml_string);

			if ($xml === false) {
				$xml_errors = libxml_get_errors();
				libxml_clear_errors();
				throw new \Exception('XML Parse Error: ' . $xml_errors[0]->message);
			}

			// Process vacancies
			$count = 0;
			foreach ($xml->job as $job) {
				$this->process_single_vacancy($job);
				$count++;
			}

			// Clean up old vacancies
			$this->cleanup_old_vacancies();

			// Log success
			$this->logger->log("Successfully processed {$count} vacancies and cleaned up old entries");
			return true;

		} catch (\Exception $e) {
			$this->logger->log('Error: ' . $e->getMessage());
			return false;
		}
	}

	private function process_single_vacancy($job) {
		// Basic validation
		if ( empty( $job->id ) ) {
			throw new \Exception( 'Job missing ID' );
		}

		$vacancy_id = (string) $job->id;

		// Add to processed IDs array
		$this->processed_ids[] = $vacancy_id;

		// Check if vacancy exists based on ACF field 'vacancy_id'
		$existing_posts = get_posts( array(
			'post_type'      => 'vacancy',
			'fields'         => 'ids', // Optimize query to only get post IDs
			'meta_query'     => array(
				array(
					'key'     => 'vacancy_id', // ACF field name - CORRECTED!
					'value'   => $vacancy_id,
					'compare' => '='
				)
			),
			'posts_per_page' => 1,
		) );

		if ( ! empty( $existing_posts ) ) {
			$post_id = $existing_posts[0]; // Get the post ID
			$this->logger->log( "Updating vacancy: {$vacancy_id} (Post ID: {$post_id})" );

			// Update post content using wp_update_post
			wp_update_post(array(
				'ID'           => $post_id,
				'post_title'   => (string) $job->title,
				'post_content' => (string) $job->description,
			));
		} else {
			$post_data = array(
				'post_title'   => (string) $job->title,
				'post_content' => (string) $job->description,
				'post_type'    => 'vacancy',
				'post_status'  => 'publish'
			);
			$post_id = wp_insert_post( $post_data );
			$this->logger->log( "Creating new vacancy: {$vacancy_id} (Post ID: {$post_id})" );
		}

		if ( is_wp_error( $post_id ) ) {
			throw new \Exception( 'Failed to save vacancy: ' . $post_id->get_error_message() );
		}

		// Process and attach recruiter image
		$recruiter_image_url = (string) $job->recruiterimage;
		if ($recruiter_image_url) {
			$this->process_recruiter_image($recruiter_image_url, $post_id);
		}

		// Update meta fields using ACF's update_field()
		update_field('vacancy_id', (string)$job->id, $post_id);
		update_field('company', (string)$job->company, $post_id);
		update_field('city', (string)$job->city, $post_id);
		update_field('category', (string)$job->category, $post_id);
		update_field('createdat', (string)$job->createdate, $post_id);
		update_field('streetaddress', (string)$job->streetaddress, $post_id);
		update_field('postalcode', (string)$job->postalcode, $post_id);
		update_field('state', (string)$job->state, $post_id);
		update_field('country', (string)$job->country, $post_id);
		update_field('education', (string)$job->education, $post_id);
		update_field('jobtype', (string)$job->jobtype, $post_id);
		update_field('experience', (string)$job->experience, $post_id);
		update_field('remotetype', (string)$job->remotetype, $post_id);
		update_field('recruitername', (string)$job->recruitername, $post_id);
		update_field('recruiteremail', (string)$job->recruiteremail, $post_id);
		update_field('custom1', (string)$job->custom1, $post_id);
		update_field('custom2', (string)$job->custom2, $post_id);
		update_field('custom3', (string)$job->custom3, $post_id);
		update_field('custom4', (string)$job->custom4, $post_id);
		update_field('custom5', (string)$job->custom5, $post_id);

		$salary_string = (string)$job->salary;
		$min_salary = '';
		$max_salary = '';

		if (!empty($salary_string)) {
			if (strpos($salary_string, '-') !== false) {
				// Salary range
				list($min_salary, $max_salary) = array_map('trim', explode('-', $salary_string));
				$min_salary = preg_replace('/[^0-9.]/', '', $min_salary); // Sanitize to numbers and dots
				$max_salary = preg_replace('/[^0-9.]/', '', $max_salary); // Sanitize to numbers and dots
			} else {
				// Single salary value
				$min_salary = $max_salary = preg_replace('/[^0-9.]/', '', $salary_string); // Sanitize single value
			}
		}

		update_field('salary_minimum', $min_salary, $post_id);
		update_field('salary_maximum', $max_salary, $post_id);
	}

	private function process_recruiter_image($image_url, $post_id) {
		// Check if the URL is empty
		if (empty($image_url)) {
			return; // Skip if no URL provided
		}

		// Check if image already exists
		$existing_image_id = $this->get_existing_image_by_url($image_url);

		if ($existing_image_id) {
			// Image exists, just update the field
			update_field('recruiterimage', $existing_image_id, $post_id);
			$this->logger->log("Recruiter image found, using existing ID {$existing_image_id} for post {$post_id}");
			return;
		}

		// Download the image
		$image_data = wp_remote_get($image_url, array('timeout' => 30));

		if (is_wp_error($image_data)) {
			$this->logger->log("Error downloading image from {$image_url}: " . $image_data->get_error_message());
			return;
		}

		$image_body = wp_remote_retrieve_body($image_data);
		if (empty($image_body)) {
			$this->logger->log("Error downloading image from {$image_url}: Empty image body");
			return;
		}

		// Get the image file type
		$file_type = wp_remote_retrieve_header($image_data, 'content-type');
		$file_name = basename($image_url);

		if (!$file_type || !$file_name) {
			$this->logger->log("Error getting the filename and or filetype for  {$image_url}");
			return;
		}

		// Create a temporary file
		$tmp_file = wp_tempnam( $file_name );

		// Put the file data into the temp file
		file_put_contents( $tmp_file, $image_body );

		// Prepare the file array for upload
		$file_array = array(
			'name' => $file_name,
			'tmp_name' => $tmp_file,
			'type' => $file_type,
			'size' => strlen($image_body)
		);

		// Upload the image
		$upload_overrides = array( 'test_form' => false ); // Skip form test
		$upload = wp_handle_sideload( $file_array, $upload_overrides);

		// Delete temp file
		@unlink($tmp_file);

		if (is_wp_error($upload)) {
			$this->logger->log("Error uploading image from {$image_url}: " . $upload->get_error_message());
			return;
		}

		$attachment_id = $upload['id'];

		if ($attachment_id) {
			update_field('recruiterimage', $attachment_id, $post_id);
			$this->logger->log("Successfully uploaded recruiter image {$image_url} with attachment ID {$attachment_id} for post {$post_id}");
		}
	}

	private function get_existing_image_by_url($url) {
		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE guid = %s AND post_type = 'attachment'",
			$url
		);

		$attachment_id = $wpdb->get_var($query);

		if ($attachment_id){
			return (int) $attachment_id;
		}
		return false;
	}


	/**
	 * Remove vacancies that are no longer in the XML feed
	 */
	/**
	 * Remove vacancies that are no longer in the XML feed
	 */
	/**
	 * Remove vacancies that are no longer in the XML feed
	 */
	private function cleanup_old_vacancies() {
		// Get all current vacancy posts
		$existing_vacancies = get_posts(array(
			'post_type' => 'vacancy',
			'posts_per_page' => -1,
			'post_status' => 'any'
		));

		$removed_count = 0;

		// Debugging log: Output the processed IDs before cleanup starts
		$this->logger->log("Processed vacancy IDs during import: " . implode(", ", $this->processed_ids));

		foreach ($existing_vacancies as $vacancy) {
			// Use get_field with the ACF field name 'vacancy_id'
			$vacancy_id = get_field('vacancy_id', $vacancy->ID);

			// Debugging log: Check the vacancy ID retrieved from ACF
			$this->logger->log("Checking vacancy ID for cleanup: " . $vacancy_id . " (Post ID: " . $vacancy->ID . ")");

			// If this vacancy's ID is not in our processed IDs, delete it
			if (!in_array($vacancy_id, $this->processed_ids)) {
				wp_delete_post($vacancy->ID, true); // true = force delete, skip trash
				$this->logger->log("Removed vacancy no longer in feed: {$vacancy_id} (Post ID: " . $vacancy->ID . ")");
				$removed_count++;
			}
		}

		if ($removed_count > 0) {
			$this->logger->log("Cleaned up {$removed_count} old vacancies");
		}
	}
}
