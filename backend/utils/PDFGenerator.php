<?php

require_once __DIR__ . '/../vendor/autoload.php';

class PDFGenerator {
    private $pdf;
    
    /**
     * Constructor - Initialize TCPDF with default settings
     */
    public function __construct() {
        // Create new PDF document
        $this->pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Set document information
        $this->pdf->SetCreator('Success City Academy');
        $this->pdf->SetAuthor('Success City Academy');
        $this->pdf->SetTitle('Term Report');
        
        // Remove default header/footer
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
        
        // Set margins
        $this->pdf->SetMargins(15, 15, 15);
        $this->pdf->SetAutoPageBreak(true, 15);
        
        // Set font
        $this->pdf->SetFont('helvetica', '', 10);
    }
    
    /**
     * Generate PDF report for a single student
     * 
     * @param array $student Student information
     * @param array $term Term information
     * @param array $assessments Array of assessment records
     * @param float|null $average Term average
     * @return string PDF content as string
     */
    public function generateStudentReport($student, $term, $assessments, $average) {
        // Add a page
        $this->pdf->AddPage();
        
        // Add header with student info and term details
        $this->addHeader($student, $term);
        
        // Add some spacing
        $this->pdf->Ln(5);
        
        // Add assessment table
        $this->formatReportTable($assessments);
        
        // Add term average with yellow accent
        if ($average !== null) {
            $this->pdf->Ln(5);
            $this->pdf->SetFont('helvetica', 'B', 12);
            $this->pdf->SetTextColor(234, 179, 8); // Yellow #eab308
            $this->pdf->Cell(0, 10, 'Term Average: ' . number_format($average, 2) . '%', 0, 1, 'R');
            $this->pdf->SetTextColor(0, 0, 0); // Reset to black
        }
        
        // Add footer with generation date
        $this->addFooter();
        
        // Return PDF as string
        return $this->pdf->Output('', 'S');
    }
    
    /**
     * Add header with student information and term details
     * 
     * @param array $student Student information
     * @param array $term Term information
     */
    private function addHeader($student, $term) {
        // Try to add school logo (PNG or JPG preferred for PDF compatibility)
        $logoExtensions = ['png', 'jpg', 'jpeg'];
        $logoAdded = false;
        
        foreach ($logoExtensions as $ext) {
            $logoPath = __DIR__ . '/../../frontend/public/assets/logo.' . $ext;
            if (file_exists($logoPath)) {
                try {
                    // Center the logo
                    $this->pdf->Image($logoPath, 85, 15, 40, 0, strtoupper($ext), '', '', true, 150, '', false, false, 0, false, false, false);
                    $this->pdf->Ln(20);
                    $logoAdded = true;
                    break;
                } catch (Exception $e) {
                    // Continue to next format if this one fails
                    continue;
                }
            }
        }
        
        // If no logo was added, add some spacing
        if (!$logoAdded) {
            $this->pdf->Ln(5);
        }
        
        // Institution name with green color
        $this->pdf->SetFont('helvetica', 'B', 18);
        $this->pdf->SetTextColor(22, 163, 74); // Green #16a34a
        $this->pdf->Cell(0, 10, 'Success City Academy', 0, 1, 'C');
        
        // Title with green color
        $this->pdf->SetFont('helvetica', 'B', 14);
        $this->pdf->SetTextColor(22, 163, 74); // Green #16a34a
        $this->pdf->Cell(0, 8, 'STUDENT TERM REPORT', 0, 1, 'C');
        
        // Reset text color to black for content
        $this->pdf->SetTextColor(0, 0, 0);
        
        $this->pdf->Ln(5);
        
        // Student Information
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->Cell(40, 7, 'Student ID:', 0, 0);
        $this->pdf->SetFont('helvetica', '', 11);
        $this->pdf->Cell(60, 7, $student['student_id'], 0, 0);
        
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->Cell(30, 7, 'Class:', 0, 0);
        $this->pdf->SetFont('helvetica', '', 11);
        $this->pdf->Cell(0, 7, $student['class_level_name'], 0, 1);
        
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->Cell(40, 7, 'Student Name:', 0, 0);
        $this->pdf->SetFont('helvetica', '', 11);
        $this->pdf->Cell(0, 7, $student['name'], 0, 1);
        
        $this->pdf->Ln(2);
        
        // Term Information
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->Cell(40, 7, 'Term:', 0, 0);
        $this->pdf->SetFont('helvetica', '', 11);
        $this->pdf->Cell(60, 7, $term['name'], 0, 0);
        
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->Cell(30, 7, 'Academic Year:', 0, 0);
        $this->pdf->SetFont('helvetica', '', 11);
        $this->pdf->Cell(0, 7, $term['academic_year'], 0, 1);
        
        $this->pdf->Ln(3);
        
        // Separator line with green color
        $this->pdf->SetDrawColor(22, 163, 74); // Green #16a34a
        $this->pdf->SetLineWidth(0.5);
        $this->pdf->Line(15, $this->pdf->GetY(), 195, $this->pdf->GetY());
        $this->pdf->SetDrawColor(0, 0, 0); // Reset to black
    }
    
    /**
     * Format and add assessment table to PDF
     * 
     * @param array $assessments Array of assessment records
     */
    private function formatReportTable($assessments) {
        $this->pdf->Ln(5);
        
        // Table header with green background
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->SetFillColor(240, 253, 244); // Light green #f0fdf4
        $this->pdf->SetTextColor(20, 83, 45); // Dark green #14532d
        $this->pdf->SetDrawColor(22, 163, 74); // Green border #16a34a
        
        $this->pdf->Cell(60, 8, 'Subject', 1, 0, 'L', true);
        $this->pdf->Cell(30, 8, 'CA Mark', 1, 0, 'C', true);
        $this->pdf->Cell(30, 8, 'Exam Mark', 1, 0, 'C', true);
        $this->pdf->Cell(30, 8, 'Final Mark', 1, 0, 'C', true);
        $this->pdf->Cell(30, 8, 'Weighting', 1, 1, 'C', true);
        
        // Table body
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->SetFillColor(255, 255, 255);
        $this->pdf->SetTextColor(0, 0, 0); // Reset to black
        $this->pdf->SetDrawColor(200, 200, 200); // Light gray borders for rows
        
        if (empty($assessments)) {
            $this->pdf->Cell(180, 8, 'No assessments recorded for this term', 1, 1, 'C');
        } else {
            foreach ($assessments as $assessment) {
                $this->pdf->Cell(60, 8, $assessment['subject_name'], 1, 0, 'L');
                
                // CA Mark
                $caDisplay = $assessment['ca_mark'] !== null 
                    ? number_format($assessment['ca_mark'], 2) 
                    : '-';
                $this->pdf->Cell(30, 8, $caDisplay, 1, 0, 'C');
                
                // Exam Mark
                $examDisplay = $assessment['exam_mark'] !== null 
                    ? number_format($assessment['exam_mark'], 2) 
                    : '-';
                $this->pdf->Cell(30, 8, $examDisplay, 1, 0, 'C');
                
                // Final Mark
                $finalDisplay = $assessment['final_mark'] !== null 
                    ? number_format($assessment['final_mark'], 2) 
                    : '-';
                $this->pdf->Cell(30, 8, $finalDisplay, 1, 0, 'C');
                
                // Weighting
                $weightingDisplay = number_format($assessment['ca_percentage'], 0) . '/' . 
                                   number_format($assessment['exam_percentage'], 0);
                $this->pdf->Cell(30, 8, $weightingDisplay, 1, 1, 'C');
            }
        }
        
        // Reset draw color
        $this->pdf->SetDrawColor(0, 0, 0);
    }
    
    /**
     * Add footer with generation date
     */
    private function addFooter() {
        // Position at bottom of page
        $this->pdf->SetY(-25);
        
        // Separator line with green color
        $this->pdf->SetDrawColor(22, 163, 74); // Green #16a34a
        $this->pdf->SetLineWidth(0.3);
        $this->pdf->Line(15, $this->pdf->GetY(), 195, $this->pdf->GetY());
        $this->pdf->SetDrawColor(0, 0, 0); // Reset to black
        
        $this->pdf->Ln(2);
        
        // Generation date
        $this->pdf->SetFont('helvetica', 'I', 8);
        $this->pdf->SetTextColor(100, 100, 100); // Gray for footer text
        $this->pdf->Cell(0, 5, 'Generated on: ' . date('F d, Y h:i A'), 0, 1, 'C');
        
        // Page number
        $this->pdf->Cell(0, 5, 'Page ' . $this->pdf->getAliasNumPage() . ' of ' . $this->pdf->getAliasNbPages(), 0, 1, 'C');
        $this->pdf->SetTextColor(0, 0, 0); // Reset to black
    }
    
    /**
     * Get the PDF object for advanced operations
     * 
     * @return TCPDF
     */
    public function getPDF() {
        return $this->pdf;
    }
}
