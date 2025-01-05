<?php
class RCWP_API_Test extends RCWP_Test_Case {
    public function test_api_authentication() {
        $request = new WP_REST_Request('POST', '/recruit-connect/v1/vacancies/sync');
        $request->add_header('Authorization', 'Bearer invalid_key');

        $response = rest_get_server()->dispatch($request);
        $this->assertEquals(401, $response->get_status());
    }

    public function test_webhook_signature() {
        update_option('rcwp_api_key', 'test_key');

        $payload = json_encode(array('event' => 'test'));
        $signature = hash_hmac('sha256', $payload, 'test_key');

        $request = new WP_REST_Request('POST', '/recruit-connect/v1/webhook');
        $request->add_header('X-Webhook-Signature', $signature);
        $request->set_body($payload);

        $response = rest_get_server()->dispatch($request);
        $this->assertEquals(200, $response->get_status());
    }

    public function test_vacancy_sync() {
        $this->mock_api_response(array(
            array(
                'id' => 'TEST_1',
                'title' => 'API Test Vacancy',
                'description' => 'Test description'
            )
        ));

        $this->api->sync_with_remote();

        $vacancy = get_posts(array(
            'post_type' => 'vacancy',
            'meta_key' => '_vacancy_id',
            'meta_value' => 'TEST_1'
        ));

        $this->assertCount(1, $vacancy);
        $this->assertEquals('API Test Vacancy', $vacancy[0]->post_title);
    }
}
