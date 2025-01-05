<?php
class RCWP_Schema {
    public function __construct() {
        add_action('wp_head', array($this, 'output_vacancy_schema'));
    }

    public function output_vacancy_schema() {
        if (!is_singular('vacancy')) {
            return;
        }

        $vacancy_id = get_the_ID();
        $schema = $this->generate_vacancy_schema($vacancy_id);

        printf(
            '<script type="application/ld+json">%s</script>',
            wp_json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    private function generate_vacancy_schema($vacancy_id) {
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'JobPosting',
            'title' => get_the_title($vacancy_id),
            'description' => get_the_content(null, false, $vacancy_id),
            'datePosted' => get_the_date('c', $vacancy_id),
            'validThrough' => $this->get_valid_through($vacancy_id),
            'employmentType' => $this->get_employment_type($vacancy_id),
            'hiringOrganization' => $this->get_organization_schema($vacancy_id),
            'jobLocation' => $this->get_location_schema($vacancy_id),
            'baseSalary' => $this->get_salary_schema($vacancy_id),
            'educationRequirements' => $this->get_education_requirements($vacancy_id),
            'experienceRequirements' => $this->get_experience_requirements($vacancy_id)
        );

        // Add remote work consideration if applicable
        $remote_type = get_post_meta($vacancy_id, '_vacancy_remotetype', true);
        if (!empty($remote_type)) {
            $schema['jobLocationType'] = $this->get_job_location_type($remote_type);
        }

        return $schema;
    }

    private function get_valid_through($vacancy_id) {
        // Default to 30 days from posting if not specified
        $valid_through = get_post_meta($vacancy_id, '_vacancy_valid_through', true);
        if (empty($valid_through)) {
            $post_date = get_the_date('Y-m-d', $vacancy_id);
            return date('c', strtotime($post_date . ' + 30 days'));
        }
        return date('c', strtotime($valid_through));
    }

    private function get_employment_type($vacancy_id) {
        $job_type = get_post_meta($vacancy_id, '_vacancy_jobtype', true);
        $employment_types = array(
            'fulltime' => 'FULL_TIME',
            'parttime' => 'PART_TIME',
            'contract' => 'CONTRACTOR',
            'temporary' => 'TEMPORARY',
            'intern' => 'INTERN',
            'volunteer' => 'VOLUNTEER'
        );

        return isset($employment_types[strtolower($job_type)])
               ? $employment_types[strtolower($job_type)]
               : 'FULL_TIME';
    }

    private function get_organization_schema($vacancy_id) {
        return array(
            '@type' => 'Organization',
            'name' => get_post_meta($vacancy_id, '_vacancy_company', true),
            'sameAs' => get_post_meta($vacancy_id, '_vacancy_company_url', true)
        );
    }

    private function get_location_schema($vacancy_id) {
        return array(
            '@type' => 'Place',
            'address' => array(
                '@type' => 'PostalAddress',
                'streetAddress' => get_post_meta($vacancy_id, '_vacancy_streetaddress', true),
                'addressLocality' => get_post_meta($vacancy_id, '_vacancy_city', true),
                'postalCode' => get_post_meta($vacancy_id, '_vacancy_postalcode', true),
                'addressRegion' => get_post_meta($vacancy_id, '_vacancy_state', true),
                'addressCountry' => get_post_meta($vacancy_id, '_vacancy_country', true)
            )
        );
    }

    private function get_salary_schema($vacancy_id) {
        $salary_low = get_post_meta($vacancy_id, '_vacancy_salary_low', true);
        $salary_high = get_post_meta($vacancy_id, '_vacancy_salary_high', true);

        if (empty($salary_low) && empty($salary_high)) {
            return null;
        }

        return array(
            '@type' => 'MonetaryAmount',
            'currency' => 'EUR',
            'value' => array(
                '@type' => 'QuantitativeValue',
                'minValue' => floatval($salary_low),
                'maxValue' => floatval($salary_high),
                'unitText' => 'YEAR'
            )
        );
    }

    private function get_education_requirements($vacancy_id) {
        $education = get_post_meta($vacancy_id, '_vacancy_education', true);
        return !empty($education) ? $education : null;
    }

    private function get_experience_requirements($vacancy_id) {
        $experience = get_post_meta($vacancy_id, '_vacancy_experience', true);
        return !empty($experience) ? $experience : null;
    }

    private function get_job_location_type($remote_type) {
        $location_types = array(
            'remote' => 'TELECOMMUTE',
            'hybrid' => 'TELECOMMUTE',
            'onsite' => 'ONSITE'
        );

        return isset($location_types[strtolower($remote_type)])
               ? $location_types[strtolower($remote_type)]
               : 'ONSITE';
    }
}
