<?php
class RCWP_Mailer {
    private $from_email;
    private $from_name;
    private $template_path;

    public function __construct() {
        $this->from_email = get_option('rcwp_from_email', get_option('admin_email'));
        $this->from_name = get_option('rcwp_from_name', get_option('blogname'));
        $this->template_path = RCWP_PLUGIN_DIR . 'templates/emails/';

        add_filter('wp_mail_content_type', array($this, 'set_html_content_type'));
    }

    /**
     * Send email using template
     */
    public function send_template($to, $template, $data) {
        $subject = $this->get_template_subject($template);
        $message = $this->get_template_content($template, $data);

        $headers = array(
            'From: ' . $this->from_name . ' <' . $this->from_email . '>',
            'Content-Type: text/html; charset=UTF-8'
        );

        $result = wp_mail($to, $subject, $message, $headers);

        if (!$result) {
            throw new Exception('Failed to send email');
        }

        return true;
    }

    /**
     * Get template subject
     */
    private function get_template_subject($template) {
        $subjects = array(
            'application_status_change' => __('Your application status has been updated', 'recruit-connect-wp'),
            'new_application_admin' => __('New job application received', 'recruit-connect-wp'),
            'sync_completion' => __('Vacancy sync completed', 'recruit-connect-wp')
        );

        return $subjects[$template] ?? __('Notification from', 'recruit-connect-wp') . ' ' . get_bloginfo('name');
    }

    /**
     * Get template content
     */
    private function get_template_content($template, $data) {
        $template_file = $this->template_path . $template . '.php';

        if (!file_exists($template_file)) {
            throw new Exception('Email template not found');
        }

        ob_start();
        include $template_file;
        $content = ob_get_clean();

        // Replace variables in template
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }

        return $this->wrap_content($content);
    }

    /**
     * Wrap email content in layout
     */
    private function wrap_content($content) {
        ob_start();
        include $this->template_path . 'layout.php';
        return ob_get_clean();
    }

    /**
     * Set HTML content type
     */
    public function set_html_content_type() {
        return 'text/html';
    }
}
