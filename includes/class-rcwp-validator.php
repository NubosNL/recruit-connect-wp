<?php
class RCWP_Validator {
    private $errors = array();
    private $data = array();
    private $rules = array();

    /**
     * Set validation rules
     */
    public function set_rules($rules) {
        $this->rules = $rules;
        return $this;
    }

    /**
     * Set data to validate
     */
    public function set_data($data) {
        $this->data = $data;
        return $this;
    }

    /**
     * Run validation
     */
    public function validate() {
        $this->errors = array();

        foreach ($this->rules as $field => $rules) {
            $value = isset($this->data[$field]) ? $this->data[$field] : null;

            foreach ($rules as $rule => $param) {
                $method = 'validate_' . $rule;
                if (method_exists($this, $method)) {
                    try {
                        $this->$method($field, $value, $param);
                    } catch (Exception $e) {
                        $this->errors[$field][] = $e->getMessage();
                    }
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Get validation errors
     */
    public function get_errors() {
        return $this->errors;
    }

    /**
     * Validate required field
     */
    private function validate_required($field, $value, $param) {
        if ($param && (is_null($value) || trim($value) === '')) {
            throw new Exception(sprintf(
                __('The %s field is required', 'recruit-connect-wp'),
                $field
            ));
        }
    }

    /**
     * Validate email
     */
    private function validate_email($field, $value, $param) {
        if ($param && !empty($value) && !is_email($value)) {
            throw new Exception(sprintf(
                __('The %s field must be a valid email address', 'recruit-connect-wp'),
                $field
            ));
        }
    }

    /**
     * Validate minimum length
     */
    private function validate_min_length($field, $value, $param) {
        if (!empty($value) && strlen($value) < $param) {
            throw new Exception(sprintf(
                __('The %s field must be at least %d characters', 'recruit-connect-wp'),
                $field,
                $param
            ));
        }
    }

    /**
     * Validate maximum length
     */
    private function validate_max_length($field, $value, $param) {
        if (!empty($value) && strlen($value) > $param) {
            throw new Exception(sprintf(
                __('The %s field cannot exceed %d characters', 'recruit-connect-wp'),
                $field,
                $param
            ));
        }
    }

    /**
     * Validate numeric value
     */
    private function validate_numeric($field, $value, $param) {
        if ($param && !empty($value) && !is_numeric($value)) {
            throw new Exception(sprintf(
                __('The %s field must be numeric', 'recruit-connect-wp'),
                $field
            ));
        }
    }

    /**
     * Validate URL
     */
    private function validate_url($field, $value, $param) {
        if ($param && !empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
            throw new Exception(sprintf(
                __('The %s field must be a valid URL', 'recruit-connect-wp'),
                $field
            ));
        }
    }

    /**
     * Validate date
     */
    private function validate_date($field, $value, $param) {
        if ($param && !empty($value)) {
            $d = DateTime::createFromFormat($param, $value);
            if (!$d || $d->format($param) !== $value) {
                throw new Exception(sprintf(
                    __('The %s field must be a valid date in the format %s', 'recruit-connect-wp'),
                    $field,
                    $param
                ));
            }
        }
    }

    /**
     * Validate regex pattern
     */
    private function validate_pattern($field, $value, $param) {
        if (!empty($value) && !preg_match($param, $value)) {
            throw new Exception(sprintf(
                __('The %s field format is invalid', 'recruit-connect-wp'),
                $field
            ));
        }
    }

    /**
     * Validate value in list
     */
    private function validate_in($field, $value, $param) {
        if (!empty($value) && !in_array($value, $param)) {
            throw new Exception(sprintf(
                __('The selected %s is invalid', 'recruit-connect-wp'),
                $field
            ));
        }
    }
}
