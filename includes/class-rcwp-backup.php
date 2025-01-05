<?php
class RCWP_Backup {
    private $logger;
    private $backup_dir;
    private $retention_days = 30;
    private $max_backups = 10;

    public function __construct($logger) {
        $this->logger = $logger;
        $this->init_backup_directory();

        add_action('rcwp_scheduled_backup', array($this, 'create_scheduled_backup'));
        add_action('admin_post_rcwp_manual_backup', array($this, 'create_manual_backup'));
        add_action('admin_post_rcwp_restore_backup', array($this, 'restore_backup'));
    }

    /**
     * Initialize backup directory
     */
    private function init_backup_directory() {
        $upload_dir = wp_upload_dir();
        $this->backup_dir = $upload_dir['basedir'] . '/rcwp-backups';

        if (!file_exists($this->backup_dir)) {
            wp_mkdir_p($this->backup_dir);
            file_put_contents($this->backup_dir . '/.htaccess', 'deny from all');
            file_put_contents($this->backup_dir . '/index.php', '<?php // Silence is golden');
        }
    }

    /**
     * Create backup
     */
    public function create_backup($manual = false) {
        try {
            $this->logger->log('Starting backup process...');

            // Create backup filename
            $timestamp = current_time('Y-m-d-His');
            $backup_type = $manual ? 'manual' : 'scheduled';
            $filename = "rcwp-backup-{$backup_type}-{$timestamp}.zip";
            $backup_file = $this->backup_dir . '/' . $filename;

            // Create ZIP archive
            $zip = new ZipArchive();
            if ($zip->open($backup_file, ZipArchive::CREATE) !== true) {
                throw new Exception('Failed to create backup archive');
            }

            // Backup vacancies
            $this->backup_vacancies($zip);

            // Backup applications
            $this->backup_applications($zip);

            // Backup settings
            $this->backup_settings($zip);

            // Backup files
            $this->backup_files($zip);

            $zip->close();

            // Cleanup old backups
            $this->cleanup_old_backups();

            $this->logger->log('Backup completed successfully: ' . $filename);
            return $filename;

        } catch (Exception $e) {
            $this->logger->log('Backup failed: ' . $e->getMessage(), 'error');
            throw $e;
        }
    }

    /**
     * Backup vacancies
     */
    private function backup_vacancies($zip) {
        $vacancies = get_posts(array(
            'post_type' => 'vacancy',
            'posts_per_page' => -1,
            'post_status' => 'any'
        ));

        $vacancy_data = array();
        foreach ($vacancies as $vacancy) {
            $vacancy_data[] = array(
                'post' => $vacancy,
                'meta' => get_post_meta($vacancy->ID)
            );
        }

        $zip->addFromString(
            'vacancies.json',
            json_encode($vacancy_data, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Backup applications
     */
    private function backup_applications($zip) {
        global $wpdb;

        $applications = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}rcwp_applications",
            ARRAY_A
        );

        $zip->addFromString(
            'applications.json',
            json_encode($applications, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Backup settings
     */
    private function backup_settings($zip) {
        $settings = array(
            'general' => get_option('rcwp_general_settings'),
            'application' => get_option('rcwp_application_settings'),
            'sync' => get_option('rcwp_sync_settings'),
            'detail' => get_option('rcwp_detail_settings')
        );

        $zip->addFromString(
            'settings.json',
            json_encode($settings, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Backup uploaded files
     */
    private function backup_files($zip) {
        $upload_dir = wp_upload_dir();
        $files_dir = $upload_dir['basedir'] . '/rcwp-files';

        if (file_exists($files_dir)) {
            $this->add_directory_to_zip($zip, $files_dir, 'files/');
        }
    }

    /**
     * Add directory to ZIP recursively
     */
    private function add_directory_to_zip($zip, $dir, $zip_path) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $file_path = $file->getRealPath();
                $relative_path = substr($file_path, strlen($dir) + 1);
                $zip->addFile($file_path, $zip_path . $relative_path);
            }
        }
    }

    /**
     * Restore from backup
     */
    public function restore_backup($backup_file) {
        try {
            $this->logger->log('Starting restore process...');

            if (!file_exists($backup_file)) {
                throw new Exception('Backup file not found');
            }

            $zip = new ZipArchive();
            if ($zip->open($backup_file) !== true) {
                throw new Exception('Failed to open backup archive');
            }

            // Start transaction
            global $wpdb;
            $wpdb->query('START TRANSACTION');

            try {
                // Restore vacancies
                $this->restore_vacancies($zip);

                // Restore applications
                $this->restore_applications($zip);

                // Restore settings
                $this->restore_settings($zip);

                // Restore files
                $this->restore_files($zip);

                $wpdb->query('COMMIT');
                $this->logger->log('Restore completed successfully');

            } catch (Exception $e) {
                $wpdb->query('ROLLBACK');
                throw $e;
            }

            $zip->close();

        } catch (Exception $e) {
            $this->logger->log('Restore failed: ' . $e->getMessage(), 'error');
            throw $e;
        }
    }

    /**
     * Restore vacancies
     */
    private function restore_vacancies($zip) {
        $vacancies_json = $zip->getFromName('vacancies.json');
        if ($vacancies_json === false) {
            throw new Exception('Vacancies data not found in backup');
        }

        $vacancies = json_decode($vacancies_json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid vacancies data format');
        }

        // Delete existing vacancies
        $existing_vacancies = get_posts(array(
            'post_type' => 'vacancy',
            'posts_per_page' => -1,
            'post_status' => 'any',
            'fields' => 'ids'
        ));

        foreach ($existing_vacancies as $vacancy_id) {
            wp_delete_post($vacancy_id, true);
        }

        // Restore vacancies
        foreach ($vacancies as $vacancy) {
            $post_data = (array) $vacancy['post'];
            unset($post_data['ID']);

            $post_id = wp_insert_post($post_data, true);
            if (is_wp_error($post_id)) {
                throw new Exception('Failed to restore vacancy: ' . $post_id->get_error_message());
            }

            // Restore meta
            foreach ($vacancy['meta'] as $meta_key => $meta_values) {
                foreach ($meta_values as $meta_value) {
                    add_post_meta($post_id, $meta_key, $meta_value);
                }
            }
        }
    }

    /**
     * Restore applications
     */
    private function restore_applications($zip) {
        global $wpdb;

        $applications_json = $zip->getFromName('applications.json');
        if ($applications_json === false) {
            throw new Exception('Applications data not found in backup');
        }

        $applications = json_decode($applications_json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid applications data format');
        }

        // Clear existing applications
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}rcwp_applications");

        // Restore applications
        foreach ($applications as $application) {
            $wpdb->insert(
                $wpdb->prefix . 'rcwp_applications',
                $application,
                array_fill(0, count($application), '%s')
            );
        }
    }

    /**
     * Restore settings
     */
    private function restore_settings($zip) {
        $settings_json = $zip->getFromName('settings.json');
        if ($settings_json === false) {
            throw new Exception('Settings data not found in backup');
        }

        $settings = json_decode($settings_json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid settings data format');
        }

        foreach ($settings as $key => $value) {
            update_option("rcwp_{$key}_settings", $value);
        }
    }

    /**
     * Restore files
     */
    private function restore_files($zip) {
        $upload_dir = wp_upload_dir();
        $files_dir = $upload_dir['basedir'] . '/rcwp-files';

        // Clear existing files
        if (file_exists($files_dir)) {
            $this->remove_directory($files_dir);
        }
        wp_mkdir_p($files_dir);

        // Extract files
        $zip->extractTo($upload_dir['basedir'], 'files/');
    }

    /**
     * Remove directory recursively
     */
    private function remove_directory($dir) {
        if (!file_exists($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->remove_directory($path) : unlink($path);
        }
        return rmdir($dir);
    }

    /**
     * Cleanup old backups
     */
    private function cleanup_old_backups() {
        $backups = glob($this->backup_dir . '/rcwp-backup-*.zip');

        // Sort by modification time
        usort($backups, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        // Remove old backups
        $retention_time = strtotime("-{$this->retention_days} days");

        foreach ($backups as $index => $backup) {
            if ($index >= $this->max_backups || filemtime($backup) < $retention_time) {
                unlink($backup);
                $this->logger->log('Removed old backup: ' . basename($backup));
            }
        }
    }

    /**
     * Get available backups
     */
    public function get_backups() {
        $backups = array();
        $files = glob($this->backup_dir . '/rcwp-backup-*.zip');

        foreach ($files as $file) {
            $backups[] = array(
                'filename' => basename($file),
                'size' => size_format(filesize($file)),
                'date' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), filemtime($file))
            );
        }

        return $backups;
    }
}
