<?php

class Student {
    public $id;
    public $student_id;
    public $name;
    public $class_level_id;
    public $class_level_name;
    public $created_at;
    public $updated_at;

    public function __construct($id = null, $student_id = null, $name = null, $class_level_id = null, $class_level_name = null, $created_at = null, $updated_at = null) {
        $this->id = $id;
        $this->student_id = $student_id;
        $this->name = $name;
        $this->class_level_id = $class_level_id;
        $this->class_level_name = $class_level_name;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    /**
     * Validate student data
     * 
     * @return array Array of validation errors (empty if valid)
     */
    public function validate() {
        $errors = [];

        if (empty($this->student_id)) {
            $errors[] = 'Student ID is required';
        } elseif (strlen($this->student_id) > 50) {
            $errors[] = 'Student ID must not exceed 50 characters';
        }

        if (empty($this->name)) {
            $errors[] = 'Name is required';
        } elseif (strlen($this->name) > 255) {
            $errors[] = 'Name must not exceed 255 characters';
        }

        if (empty($this->class_level_id)) {
            $errors[] = 'Class level is required';
        } elseif (!is_numeric($this->class_level_id) || $this->class_level_id <= 0) {
            $errors[] = 'Class level ID must be a positive number';
        }

        return $errors;
    }

    /**
     * Check if student data is valid
     * 
     * @return bool True if valid, false otherwise
     */
    public function isValid() {
        return empty($this->validate());
    }

    /**
     * Convert student object to array
     * 
     * @return array Student data as associative array
     */
    public function toArray() {
        $data = [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'name' => $this->name,
            'class_level_id' => $this->class_level_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];

        if ($this->class_level_name !== null) {
            $data['class_level_name'] = $this->class_level_name;
        }

        return $data;
    }
}
