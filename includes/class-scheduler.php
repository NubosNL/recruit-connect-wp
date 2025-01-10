<?php
namespace RecruitConnect;

class Scheduler {
    private $importer;
    private $hook_name = 'recruit_connect_xml_import';

    public function __construct() {
        $this->importer = new XMLImporter();

        add_action($this->hook_name, array($this->importer, 'import'));
        add_action('admin_init', array($this, 'setup_schedule'));
        add_action('recruit_connect_settings_updated', array($this, 'update_schedule'));
    }

    public function setup_schedule() {
        if (!wp_next_scheduled($this->hook_name)) {
            $this->schedule_import();
        }
    }

    public function update_schedule() {
        $this->clear_schedule();
        $this->schedule_import();
    }

    public function clear_schedule() {
        wp_clear_scheduled_hook($this->hook_name);
    }

    private function schedule_import() {
        $frequency = get_option('recruit_connect_sync_frequency', 'daily');

        // Add custom frequency if needed
        if ($frequency === 'fourhourly') {
            add_filter('cron_schedules', function($schedules) {
                $schedules['fourhourly'] = array(
                    'interval' => 4 * HOUR_IN_SECONDS,
                    'display' => __('Every 4 hours', 'recruit-connect-wp')
                );
                return $schedules;
            });
        }

        wp_schedule_event(time(), $frequency, $this->hook_name);
    }
}
