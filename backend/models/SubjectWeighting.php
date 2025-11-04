<?php

class SubjectWeighting {
    public $id;
    public $subject_id;
    public $subject_name;
    public $ca_percentage;
    public $exam_percentage;
    public $created_at;
    public $updated_at;

    public function __construct($id = null, $subject_id = null, $ca_percentage = 40.00, $exam_percentage = 60.00, $subject_name = null, $created_at = null, $updated_at = null) {
        $this->id = $id;
        $this->subject_id = $subject_id;
        $this->ca_percentage = $ca_percentage;
        $this->exam_percentage = $exam_percentage;
        $this->subject_name = $subject_name;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    /**
     * Validate subject weighting data
     * 
     * @return array Array of validation errors (empty if valid)
     */
    public function validate() {
        $errors = [];

        if (empty($this->subject_id)) {
            $errors[] = 'Subject ID is required';
        } elseif (!is_numeric($this->subject_id)) {
            $errors[] = 'Subject ID must be numeric';
        }

        if (!is_numeric($this->ca_percentage)) {
            $errors[] = 'CA percentage must be numeric';
        } elseif ($this->ca_percentage < 0 || $this->ca_percentage > 100) {
            $errors[] = 'CA percentage must be between 0 and 100';
        }

        if (!is_numeric($this->exam_percentage)) {
            $errors[] = 'Exam percentage must be numeric';
        } elseif ($this->exam_percentage < 0 || $this->exam_percentage > 100) {
            $errors[] = 'Exam percentage must be between 0 and 100';
        }

        // Check if percentages sum to 100
        if (is_numeric($this->ca_percentage) && is_numeric($this->exam_percentage)) {
            $sum = floatval($this->ca_percentage) + floatval($this->exam_percentage);
            if (abs($sum - 100.00) > 0.01) { // Allow small floating point differences
                $errors[] = 'CA and exam percentages must sum to exactly 100';
            }
        }

        return $errors;
    }

    /**
     * Check if subject weighting data is valid
     * 
     * @return bool True if valid, false otherwise
     */
    public function isValid() {
        return empty($this->validate());
    }

    /**
     * Convert subject weighting object to array
     * 
     * @return array Subject weighting data as associative array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'subject_id' => $this->subject_id,
            'subject_name' => $this->subject_name,
            'ca_percentage' => $this->ca_percentage,
            'exam_percentage' => $this->exam_percentage,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
