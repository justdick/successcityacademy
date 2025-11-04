<?php

class Grade {
    public $id;
    public $student_id;
    public $subject_id;
    public $subject_name;
    public $mark;
    public $created_at;

    public function __construct($id = null, $student_id = null, $subject_id = null, $subject_name = null, $mark = null, $created_at = null) {
        $this->id = $id;
        $this->student_id = $student_id;
        $this->subject_id = $subject_id;
        $this->subject_name = $subject_name;
        $this->mark = $mark;
        $this->created_at = $created_at;
    }

    /**
     * Validate grade data
     * 
     * @return array Array of validation errors (empty if valid)
     */
    public function validate() {
        $errors = [];

        if (empty($this->student_id)) {
            $errors[] = 'Student ID is required';
        }

        if (empty($this->subject_id)) {
            $errors[] = 'Subject ID is required';
        } elseif (!is_numeric($this->subject_id) || $this->subject_id <= 0) {
            $errors[] = 'Subject ID must be a positive number';
        }

        if ($this->mark === null || $this->mark === '') {
            $errors[] = 'Mark is required';
        } elseif (!is_numeric($this->mark)) {
            $errors[] = 'Mark must be a number';
        } elseif (!$this->isValidMark()) {
            $errors[] = 'Mark must be between 0 and 100';
        }

        return $errors;
    }

    /**
     * Check if mark is within valid range (0-100)
     * 
     * @return bool True if mark is valid, false otherwise
     */
    public function isValidMark() {
        if ($this->mark === null || !is_numeric($this->mark)) {
            return false;
        }
        
        $mark = floatval($this->mark);
        return $mark >= 0 && $mark <= 100;
    }

    /**
     * Check if grade data is valid
     * 
     * @return bool True if valid, false otherwise
     */
    public function isValid() {
        return empty($this->validate());
    }

    /**
     * Convert grade object to array
     * 
     * @return array Grade data as associative array
     */
    public function toArray() {
        $data = [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'subject_id' => $this->subject_id,
            'mark' => $this->mark,
            'created_at' => $this->created_at
        ];

        if ($this->subject_name !== null) {
            $data['subject_name'] = $this->subject_name;
        }

        return $data;
    }
}
