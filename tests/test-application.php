<?php
class RCWP_Application_Test extends RCWP_Test_Case {
    public function test_submit_application() {
        $vacancy_id = $this->create_test_vacancy();

        $application_data = array(
            'vacancy_id' => $vacancy_id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'motivation' => 'Test motivation'
        );

        $handler = new RCWP_Application_Handler($this->logger);
        $result = $handler->process_application($application_data);

        $this->assertTrue($result);

        global $wpdb;
        $application = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}rcwp_applications WHERE email = %s",
                'john@example.com'
            )
        );

        $this->assertNotNull($application);
        $this->assertEquals('pending', $application->status);
    }

    public function test_application_validation() {
        $handler = new RCWP_Application_Handler($this->logger);

        $this->expectException(Exception::class);

        $handler->process_application(array(
            'first_name' => 'John'
            // Missing required fields
        ));
    }

    public function test_application_notification() {
        $vacancy_id = $this->create_test_vacancy();
        $application_id = $this->create_test_application($vacancy_id);

        $notifications = new RCWP_Notifications($this->logger);

        // Mock email sending
        add_filter('wp_mail', function($args) {
            $this->assertEquals('test@example.com', $args['to']);
            return false;
        });

        $notifications->notify_application_status_change($application_id, 'accepted');
    }
}
