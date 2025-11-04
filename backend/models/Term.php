<?php

class Term {
    public $id;
    public $name;
    public $academic_year;
    public $start_date;
    public $end_date;
    public $is_active;
    public $created_at;

    public function __construct($id = null, $name = null, $academic_year = null, $start_date = null, $end_date = null, $is_active = true, $created_at = null) {
        $this->id = $id;
        $this->name = $name;
        $this->academic_year = $academic_year;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->is_active = $is_active;
        $this->created_at = $created_at;
    }

    /**
     * Validate term data
     * 
     * @return array Array of validation errors (empty if valid)
     */
    public function validate() {
        $errors = [];

        if (empty($this->name)) {
            $errors[] = 'Term name is required';
        } elseif (strlen($this->name) > 100) {
            $errors[] = 'Term name must not exceed 100 characters';
        }

        if (empty($this->academic_year)) {
            $errors[] = 'Academic year is required';
        } elseif (!$this->isValidAcademicYear()) {
            $errors[] = 'Invalid academic year format. Use YYYY/YYYY (e.g., 2024/2025)';
        }

        return $errors;
    }

    /**
     * Check if academic year format is valid (YYYY/YYYY)
     * 
     * @return bool True if valid, false otherwise
     */
    public function isValidAcademicYear() {
        if (empty($this->academic_year)) {
            return false;
        }

        // Check format YYYY/YYYY
        if (!preg_match('/^\d{4}\/\d{4}$/', $this->academic_year)) {
            return false;
        }

        // Extract years
        $parts = explode('/', $this->academic_year);
        $year1 = intval($parts[0]);
        $year2 = intval($parts[1]);

        // Validate that second year is exactly one year after first
        if ($year2 !== $year1 + 1) {
            return false;
        }

        return true;
    }

    /**
     * Check if term data is valid
     * 
     * @return bool True if valid, false otherwise
     */
    public function isValid() {
        return empty($this->validate());
    }

    /**
     * Convert term object to array
     * 
     * @return array Term data as associative array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'academic_year' => $this->academic_year,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at
        ];
    }
}
