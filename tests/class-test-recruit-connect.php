<?php
namespace RecruitConnect\Tests;

class TestRecruitConnect extends \WP_UnitTestCase {
    private $plugin;
    private $importer;

    public function setUp(): void {
        parent::setUp();
        $this->plugin = new \RecruitConnect\Plugin();
        $this->importer = new \RecruitConnect\XMLImporter();
    }

    public function test_plugin_initialization() {
        $this->assertNotNull($this->plugin);
        $this->assertTrue(post_type_exists('vacancy'));
    }

    public function test_xml_import() {
        // Sample XML data
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<source>
    <job>
        <id><![CDATA[test123]]></id>
        <title><![CDATA[Test Vacancy]]></title>
        <description><![CDATA[Test Description]]></description>
    </job>
</source>
XML;

        // Mock the wp_remote_get response
        add_filter('pre_http_request', function() use ($xml) {
            return array(
                'body' => $xml,
                'response' => array('code' => 200)
            );
        });

        $result = $this->importer->import();
        $this->assertTrue($result);

        // Check if vacancy was created
        $vacancy = get_posts(array(
            'post_type' => 'vacancy',
            'meta_key' => '_vacancy_id',
            'meta_value' => 'test123'
        ));

        $this->assertCount(1, $vacancy);
    }

    public function test_shortcode_output() {
        $shortcodes = new \RecruitConnect\Shortcodes();

        // Test vacancies list shortcode
        $output = $shortcodes->render_vacancies_list(array());
        $this->assertIsString($output);

        // Test search form shortcode
        $output = $shortcodes->render_search_form(array());
        $this->assertIsString($output);
    }
}
