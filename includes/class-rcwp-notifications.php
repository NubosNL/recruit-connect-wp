<?php
class RCWP_Notifications {
    private $mailer;
    private $logger;

    public function __construct($logger) {
        $this->logger = $logger;
        $this->mailer = new RCWP_Mailer();

        add_action('rcwp_application_status_changed', array($this, 'notify_application_status_change'), 10, 2);
        add_action('rcwp_application_submitted', array($this, 'notify_new_application'), 10, 2);
        add_action('rcwp_sync_completed', array($this, 'notify_sync_completion'));
    }

    /**
     * Notify applicant of status change
     */
    public function notify_application_status_change($application_id, $new_status) {
        global $wpdb;

        $application = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}rcwp_applications WHERE id = %d",
            $application_id
        ));

        if (!$application) {
            return;
        }

        $vacancy = get_post($application->vacancy_id);
        if (!$vacancy) {
            return;
        }

        $template_data = array(
            'applicant_name' => $application->first_name . ' ' . $application->last_name,
            'vacancy_title' => $vacancy->post_title,
            'status' => $new_status,
            'application_date' => $application->created_at
        );

        try {
            $this->mailer->send_template(
                $application->email,
                'application_status_change',
                $template_data
            );

            $this->logger->log(
                sprintf('Status change notification sent to %s', $application->email)
            );
        } catch (Exception $e) {
            $this->logger->log(
                'Failed to send status change notification: ' . $e->getMessage(),
                'error'
            );
        }
    }

    /**
     * Notify admin of new application
     */
    public function notify_new_application($application_id, $vacancy_id) {
        $admin_email = get_option('rcwp_admin_email');
        if (!$admin_email) {
            return;
        }

        $vacancy = get_post($vacancy_id);
        if (!$vacancy) {
            return;
        }

        $template_data = array(
            'vacancy_title' => $vacancy->post_title,
            'admin_url' => admin_url('admin.php?page=rcwp-applications&application=' . $application_id)
        );

        try {
            $this->mailer->send_template(
                $admin_email,
                'new_application_admin',
                $template_data
            );
        } catch (Exception $e) {
            $this->logger->log(
                'Failed to send admin notification: ' . $e->getMessage(),
                'error'
            );
        }
    }

    /**
     * Notify admin of sync completion
     */
    public function notify_sync_completion() {
        $admin_email = get_option('rcwp_admin_email');
        if (!$admin_email) {
            return;
        }

        $stats = $this->get_sync_stats();

        try {
            $this->mailer->send_template(
                $admin_email,
                'sync_completion',
                $stats
            );
        } catch (Exception $e) {
            $this->logger->log(
                'Failed to send sync completion notification: ' . $e->getMessage(),
                'error'
            );
        }
    }

    /**
     * Get sync statistics
     */
    private function get_sync_stats() {
        return array(
            'total_vacancies' => wp_count_posts('vacancy')->publish,
            'updated_count' => get_option('rcwp_last_sync_updated', 0),
            'created_count' => get_option('rcwp_last_sync_created', 0),
            'sync_time' => get_option('rcwp_last_sync_time')
        );
    }
}
