<?php
class RCWP_Vacancy_Test extends RCWP_Test_Case {
    public function test_create_vacancy() {
        $vacancy_id = $this->create_test_vacancy();

        $this->assertNotEmpty($vacancy_id);
        $this->assertEquals('vacancy', get_post_type($vacancy_id));
    }

    public function test_vacancy_meta() {
        $vacancy_id = $this->create_test_vacancy();

        $this->assertEquals('50000', get_post_meta($vacancy_id, '_vacancy_salary', true));
        $this->assertEquals('Test City', get_post_meta($vacancy_id, '_vacancy_location', true));
    }

    public function test_vacancy_search() {
        $vacancy_id = $this->create_test_vacancy(array(
            'post_title' => 'Unique Test Title'
        ));

        $query = new WP_Query(array(
            'post_type' => 'vacancy',
            's' => 'Unique Test'
        ));

        $this->assertEquals(1, $query->found_posts);
        $this->assertEquals($vacancy_id, $query->posts[0]->ID);
    }

    public function test_vacancy_filters() {
        $vacancy_id = $this->create_test_vacancy();
        update_post_meta($vacancy_id, '_vacancy_category', 'IT');

        $query = new WP_Query(array(
            'post_type' => 'vacancy',
            'meta_key' => '_vacancy_category',
            'meta_value' => 'IT'
        ));

        $this->assertEquals(1, $query->found_posts);
    }
}
