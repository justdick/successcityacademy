<?php

class TermAssessment {
    public $id;
    public $student_id;
    public $student_name;
    public $subject_id;
    public $subject_name;
    public $term_id;
    public $term_name;
    public $ca_mark;
    public $exam_mark;
    public $final_mark;
    public $created_at;
    public $updated_at;

    public function __construct(
        $id = null, 
        $student_id = null, 
        $subject_id = null, 
        $term_id = null, 
        $ca_mark = null, 
        $exam_mark = null,
        $student_name = null,
        $subject_name = null,
        $term_name = null,
        $final_mark = null,
        $created_at = null, 
        $updated_at = null
    ) {
        $this->id = $id;
        $this->student_id = $student_id;
        $this->subject_id = $subject_id;
        $this->term_id = $term_id;
        $this->ca_mark = $ca_mark;
        $this->exam_mark = $exam_mark;
        $this->student_name = $student_name;
        $this->subject_name = $subject_name;
        $this->term_name = $term_name;
        $this->final_mark = $final_mark;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    /**
     * Validate term assessment data
     * 
     * @param float $ca_percentage Maximum CA percentage for the subject
     * @param float $exam_percentage Maximum exam percentage for the subject
     * @return array Array of validation errors (empty if valid)
     */
    public function validate($ca_percentage = null, $exam_percentage = null) {
        $errors = [];

        if (empty($this->student_id)) {
            $errors[] = 'Student ID is required';
        }

        if (empty($this->subject_id)) {
            $errors[] = 'Subject ID is required';
        } elseif (!is_numeric($this->subject_id)) {
            $errors[] = 'Subject ID must be numeric';
        }

        if (empty($this->term_id)) {
            $errors[] = 'Term ID is required';
        } elseif (!is_numeric($this->term_id)) {
            $errors[] = 'Term ID must be numeric';
        }

        // At least one mark must be provided
        if ($this->ca_mark === null && $this->exam_mark === null) {
            $errors[] = 'At least one mark (CA or Exam) must be provided';
        }

        // Validate CA mark if provided
        if ($this->ca_mark !== null) {
            if (!is_numeric($this->ca_mark)) {
                $errors[] = 'CA mark must be numeric';
            } elseif ($this->ca_mark < 0) {
                $errors[] = 'CA mark cannot be negative';
            } elseif ($ca_percentage !== null && $this->ca_mark > $ca_percentage) {
                $errors[] = "CA mark cannot exceed {$ca_percentage}% for this subject";
            }
        }

        // Validate exam mark if provided
        if ($this->exam_mark !== null) {
            if (!is_numeric($this->exam_mark)) {
                $errors[] = 'Exam mark must be numeric';
            } elseif ($this->exam_mark < 0) {
                $errors[] = 'Exam mark cannot be negative';
            } elseif ($exam_percentage !== null && $this->exam_mark > $exam_percentage) {
                $errors[] = "Exam mark cannot exceed {$exam_percentage}% for this subject";
            }
        }

        return $errors;
    }

    /**
     * Check if term assessment data is valid
     * 
     * @param float $ca_percentage Maximum CA percentage for the subject
     * @param float $exam_percentage Maximum exam percentage for the subject
     * @return bool True if valid, false otherwise
     */
    public function isValid($ca_percentage = null, $exam_percentage = null) {
        return empty($this->validate($ca_percentage, $exam_percentage));
    }

    /**
     * Calculate final mark from CA and exam marks
     * 
     * @return float Final mark (sum of CA and exam marks)
     */
    public function calculateFinalMark() {
        $ca = $this->ca_mark !== null ? floatval($this->ca_mark) : 0;
        $exam = $this->exam_mark !== null ? floatval($this->exam_mark) : 0;
        return $ca + $exam;
    }

    /**
     * Convert term assessment object to array
     * 
     * @return array Term assessment data as associative array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'student_name' => $this->student_name,
            'subject_id' => $this->subject_id,
            'subject_name' => $this->subject_name,
            'term_id' => $this->term_id,
            'term_name' => $this->term_name,
            'ca_mark' => $this->ca_mark,
            'exam_mark' => $this->exam_mark,
            'final_mark' => $this->final_mark,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
