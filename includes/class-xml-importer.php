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
		if (empty($job->id)) {
			throw new \Exception('Job missing ID');
		}

		$vacancy_id = (string)$job->id;

		// Add to processed IDs array
		$this->processed_ids[] = $vacancy_id;

		// Prepare post data
		$post_data = array(
			'post_title'    => (string)$job->title,
			'post_content'  => (string)$job->description,
			'post_type'     => 'vacancy',
			'post_status'   => 'publish'
		);

		// Check if vacancy exists
		$existing_posts = get_posts(array(
			'post_type' => 'vacancy',
			'meta_key' => '_vacancy_id',
			'meta_value' => $vacancy_id,
			'posts_per_page' => 1
		));

		if (!empty($existing_posts)) {
			$post_data['ID'] = $existing_posts[0]->ID;
			$this->logger->log("Updating vacancy: {$vacancy_id}");
		} else {
			$this->logger->log("Creating new vacancy: {$vacancy_id}");
		}

		// Insert or update post
		$post_id = wp_insert_post($post_data);

		if (is_wp_error($post_id)) {
			throw new \Exception('Failed to save vacancy: ' . $post_id->get_error_message());
		}

		// Update meta fields
		update_post_meta($post_id, '_vacancy_id', $vacancy_id);
		update_post_meta($post_id, '_vacancy_company', (string)$job->company);
		update_post_meta($post_id, '_vacancy_city', (string)$job->city);
		update_post_meta($post_id, '_vacancy_createdat', (string)$job->createdate);
		update_post_meta($post_id, '_vacancy_streetaddress', (string)$job->streetaddress);
		update_post_meta($post_id, '_vacancy_postalcode', (string)$job->postalcode);
		update_post_meta($post_id, '_vacancy_state', (string)$job->state);
		update_post_meta($post_id, '_vacancy_country', (string)$job->country);
		update_post_meta($post_id, '_vacancy_salary', (string)$job->salary);
		update_post_meta($post_id, '_vacancy_education', (string)$job->education);
		update_post_meta($post_id, '_vacancy_jobtype', (string)$job->jobtype);
		update_post_meta($post_id, '_vacancy_experience', (string)$job->experience);
		update_post_meta($post_id, '_vacancy_recruitername', (string)$job->recruitername);
		update_post_meta($post_id, '_vacancy_recruiteremail', (string)$job->recruiteremail);
		update_post_meta($post_id, '_vacancy_recruiterimage', (string)$job->recruiterimage);
		update_post_meta($post_id, '_vacancy_remotetype', (string)$job->remotetype);
		update_post_meta($post_id, '_vacancy_custom1', (string)$job->custom1);
		update_post_meta($post_id, '_vacancy_custom2', (string)$job->custom2);
		update_post_meta($post_id, '_vacancy_custom3', (string)$job->custom3);
		update_post_meta($post_id, '_vacancy_custom4', (string)$job->custom4);
		update_post_meta($post_id, '_vacancy_custom5', (string)$job->custom5);
	}

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

		foreach ($existing_vacancies as $vacancy) {
			$vacancy_id = get_post_meta($vacancy->ID, '_vacancy_id', true);

			// If this vacancy's ID is not in our processed IDs, delete it
			if (!in_array($vacancy_id, $this->processed_ids)) {
				wp_delete_post($vacancy->ID, true); // true = force delete, skip trash
				$this->logger->log("Removed vacancy no longer in feed: {$vacancy_id}");
				$removed_count++;
			}
		}

		if ($removed_count > 0) {
			$this->logger->log("Cleaned up {$removed_count} old vacancies");
		}
	}
}
