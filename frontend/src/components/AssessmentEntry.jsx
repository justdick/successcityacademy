import { useState, useEffect } from 'react';
import { useLocation } from 'react-router-dom';
import { getAllStudents } from '../services/api';
import { getWeightings } from '../services/weightingService';
import { getActiveTerms } from '../services/termService';
import {
  createAssessment,
  getStudentTermAssessments,
  validateCAMark,
  validateExamMark,
  calculateFinalMark,
  isValidNumber
} from '../services/assessmentService';
import { getMyAssignments } from '../services/teacherAssignmentService';
import { isAdmin } from '../services/auth';

const AssessmentEntry = () => {
  const location = useLocation();
  const [students, setStudents] = useState([]);
  const [subjects, setSubjects] = useState([]);
  const [terms, setTerms] = useState([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  // Form state
  const [selectedStudent, setSelectedStudent] = useState('');
  const [selectedSubject, setSelectedSubject] = useState('');
  const [selectedTerm, setSelectedTerm] = useState('');
  const [caMark, setCaMark] = useState('');
  const [examMark, setExamMark] = useState('');

  // Validation state
  const [caError, setCaError] = useState('');
  const [examError, setExamError] = useState('');

  // Current weighting for selected subject
  const [currentWeighting, setCurrentWeighting] = useState(null);

  // Teacher assignments
  const [myAssignments, setMyAssignments] = useState(null);
  const [filteredStudents, setFilteredStudents] = useState([]);
  const [filteredSubjects, setFilteredSubjects] = useState([]);

  // Load initial data
  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      setLoading(true);
      setError('');

      // Load base data
      const [studentsData, weightingsData, termsData] = await Promise.all([
        getAllStudents(),
        getWeightings(),
        getActiveTerms()
      ]);

      setStudents(studentsData);
      setSubjects(weightingsData);
      setTerms(termsData);

      // Load teacher assignments if not admin
      if (!isAdmin()) {
        try {
          const assignments = await getMyAssignments();
          setMyAssignments(assignments);

          // Filter students by assigned classes
          const assignedClassIds = assignments.classes.map(c => c.id);
          const filtered = studentsData.filter(s => 
            assignedClassIds.includes(s.class_level_id)
          );
          setFilteredStudents(filtered);

          // Filter subjects by assigned subjects
          const assignedSubjectIds = assignments.subjects.map(s => s.id);
          const filteredSubs = weightingsData.filter(s => 
            assignedSubjectIds.includes(s.subject_id)
          );
          setFilteredSubjects(filteredSubs);
        } catch (err) {
          // If assignments fail to load, show empty lists
          setMyAssignments({ classes: [], subjects: [] });
          setFilteredStudents([]);
          setFilteredSubjects([]);
        }
      } else {
        // Admin sees all students and subjects
        setFilteredStudents(studentsData);
        setFilteredSubjects(weightingsData);
      }

      // Pre-fill form if navigation state is provided (from AssessmentSummary)
      if (location.state) {
        const { studentId, subjectId, termId } = location.state;
        if (studentId) setSelectedStudent(studentId);
        if (subjectId) setSelectedSubject(subjectId.toString());
        if (termId) setSelectedTerm(termId.toString());
      }
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  // Update current weighting when subject changes
  useEffect(() => {
    if (selectedSubject) {
      const weighting = filteredSubjects.find(s => s.subject_id === parseInt(selectedSubject));
      setCurrentWeighting(weighting);
    } else {
      setCurrentWeighting(null);
    }
  }, [selectedSubject, filteredSubjects]);

  // Load existing assessment when student, subject, and term are selected
  useEffect(() => {
    if (selectedStudent && selectedSubject && selectedTerm) {
      loadExistingAssessment();
    }
  }, [selectedStudent, selectedSubject, selectedTerm]);

  const loadExistingAssessment = async () => {
    try {
      const assessments = await getStudentTermAssessments(selectedStudent, selectedTerm);
      const existing = assessments.find(a => a.subject_id === parseInt(selectedSubject));
      
      if (existing) {
        setCaMark(existing.ca_mark !== null ? existing.ca_mark : '');
        setExamMark(existing.exam_mark !== null ? existing.exam_mark : '');
      } else {
        setCaMark('');
        setExamMark('');
      }
    } catch (err) {
      // No existing assessment, keep fields empty
      setCaMark('');
      setExamMark('');
    }
  };

  // Validate CA mark
  const handleCAMarkChange = (value) => {
    setCaMark(value);
    setCaError('');

    if (value && currentWeighting) {
      if (!isValidNumber(value)) {
        setCaError('Please enter a valid number');
      } else if (!validateCAMark(value, currentWeighting.ca_percentage)) {
        setCaError(`CA mark must be between 0 and ${currentWeighting.ca_percentage}`);
      }
    }
  };

  // Validate exam mark
  const handleExamMarkChange = (value) => {
    setExamMark(value);
    setExamError('');

    if (value && currentWeighting) {
      if (!isValidNumber(value)) {
        setExamError('Please enter a valid number');
      } else if (!validateExamMark(value, currentWeighting.exam_percentage)) {
        setExamError(`Exam mark must be between 0 and ${currentWeighting.exam_percentage}`);
      }
    }
  };

  // Calculate final mark in real-time
  const finalMark = calculateFinalMark(caMark, examMark);

  // Handle form submission
  const handleSubmit = async (e) => {
    e.preventDefault();
    
    // Validate all fields are selected
    if (!selectedStudent || !selectedSubject || !selectedTerm) {
      setError('Please select student, subject, and term');
      return;
    }

    // At least one mark must be provided
    if (!caMark && !examMark) {
      setError('Please enter at least one mark (CA or Exam)');
      return;
    }

    // Check for validation errors
    if (caError || examError) {
      setError('Please fix validation errors before submitting');
      return;
    }

    // Validate teacher assignments (client-side check)
    if (!isAdmin() && myAssignments) {
      // Check if student is in assigned classes
      const student = filteredStudents.find(s => s.student_id === selectedStudent);
      if (!student) {
        setError('You do not have access to this student. Please select a student from your assigned classes.');
        return;
      }

      // Check if subject is in assigned subjects
      const subject = filteredSubjects.find(s => s.subject_id === parseInt(selectedSubject));
      if (!subject) {
        setError('You do not have access to this subject. Please select a subject from your assigned subjects.');
        return;
      }
    }

    try {
      setSaving(true);
      setError('');
      setSuccess('');

      const assessmentData = {
        student_id: selectedStudent,
        subject_id: parseInt(selectedSubject),
        term_id: parseInt(selectedTerm),
        ca_mark: caMark ? parseFloat(caMark) : null,
        exam_mark: examMark ? parseFloat(examMark) : null
      };

      await createAssessment(assessmentData);
      setSuccess('Assessment saved successfully!');
      
      // Clear marks after successful save
      setTimeout(() => {
        setSuccess('');
      }, 3000);
    } catch (err) {
      setError(err.message);
    } finally {
      setSaving(false);
    }
  };

  // Reset form
  const handleReset = () => {
    setSelectedStudent('');
    setSelectedSubject('');
    setSelectedTerm('');
    setCaMark('');
    setExamMark('');
    setCaError('');
    setExamError('');
    setError('');
    setSuccess('');
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="text-lg text-gray-600">Loading...</div>
      </div>
    );
  }

  // Check if teacher has no assignments
  const hasNoAssignments = !isAdmin() && myAssignments && 
    (myAssignments.classes.length === 0 || myAssignments.subjects.length === 0);

  return (
    <div className="max-w-4xl mx-auto">
      <div className="bg-white rounded-lg shadow-lg p-6">
        <h2 className="text-2xl font-bold text-gray-800 mb-6">Assessment Entry</h2>

        {error && (
          <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
            {error}
          </div>
        )}

        {success && (
          <div className="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
            {success}
          </div>
        )}

        {hasNoAssignments && (
          <div className="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded mb-4">
            <p className="font-semibold">No Assignments</p>
            <p className="text-sm mt-1">
              {myAssignments.classes.length === 0 && myAssignments.subjects.length === 0 
                ? 'You have not been assigned to any classes or subjects. Please contact your administrator.'
                : myAssignments.classes.length === 0
                ? 'You have not been assigned to any classes. Please contact your administrator.'
                : 'You have not been assigned to any subjects. Please contact your administrator.'}
            </p>
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Term Selector */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Term <span className="text-red-500">*</span>
            </label>
            <select
              value={selectedTerm}
              onChange={(e) => setSelectedTerm(e.target.value)}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              required
            >
              <option value="">Select Term</option>
              {terms.map((term) => (
                <option key={term.id} value={term.id}>
                  {term.name} - {term.academic_year}
                </option>
              ))}
            </select>
          </div>

          {/* Student Selector */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Student <span className="text-red-500">*</span>
            </label>
            <select
              value={selectedStudent}
              onChange={(e) => setSelectedStudent(e.target.value)}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              required
              disabled={filteredStudents.length === 0}
            >
              <option value="">
                {filteredStudents.length === 0 ? 'No students available' : 'Select Student'}
              </option>
              {filteredStudents.map((student) => (
                <option key={student.student_id} value={student.student_id}>
                  {student.name} ({student.student_id}) - {student.class_level_name}
                </option>
              ))}
            </select>
          </div>

          {/* Subject Selector */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Subject <span className="text-red-500">*</span>
            </label>
            <select
              value={selectedSubject}
              onChange={(e) => setSelectedSubject(e.target.value)}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              required
              disabled={filteredSubjects.length === 0}
            >
              <option value="">
                {filteredSubjects.length === 0 ? 'No subjects available' : 'Select Subject'}
              </option>
              {filteredSubjects.map((subject) => (
                <option key={subject.subject_id} value={subject.subject_id}>
                  {subject.subject_name} (CA: {subject.ca_percentage}%, Exam: {subject.exam_percentage}%)
                </option>
              ))}
            </select>
          </div>

          {/* Display current weighting info */}
          {currentWeighting && (
            <div className="bg-primary-50 border border-primary-200 rounded-lg p-4">
              <h3 className="font-semibold text-primary-900 mb-2">Subject Weighting</h3>
              <div className="grid grid-cols-2 gap-4 text-sm text-primary-800">
                <div>
                  <span className="font-medium">CA:</span> {currentWeighting.ca_percentage}%
                </div>
                <div>
                  <span className="font-medium">Exam:</span> {currentWeighting.exam_percentage}%
                </div>
              </div>
            </div>
          )}

          {/* CA Mark Input */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Continuous Assessment (CA) Mark
              {currentWeighting && (
                <span className="text-gray-500 text-xs ml-2">
                  (Max: {currentWeighting.ca_percentage})
                </span>
              )}
            </label>
            <input
              type="number"
              step="0.01"
              min="0"
              max={currentWeighting?.ca_percentage || 100}
              value={caMark}
              onChange={(e) => handleCAMarkChange(e.target.value)}
              className={`w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent ${
                caError ? 'border-red-500' : 'border-gray-300'
              }`}
              placeholder="Enter CA mark"
              disabled={!currentWeighting}
            />
            {caError && (
              <p className="text-red-500 text-sm mt-1">{caError}</p>
            )}
          </div>

          {/* Exam Mark Input */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Exam Mark
              {currentWeighting && (
                <span className="text-gray-500 text-xs ml-2">
                  (Max: {currentWeighting.exam_percentage})
                </span>
              )}
            </label>
            <input
              type="number"
              step="0.01"
              min="0"
              max={currentWeighting?.exam_percentage || 100}
              value={examMark}
              onChange={(e) => handleExamMarkChange(e.target.value)}
              className={`w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent ${
                examError ? 'border-red-500' : 'border-gray-300'
              }`}
              placeholder="Enter exam mark"
              disabled={!currentWeighting}
            />
            {examError && (
              <p className="text-red-500 text-sm mt-1">{examError}</p>
            )}
          </div>

          {/* Final Mark Display */}
          {(caMark || examMark) && (
            <div className="bg-secondary-50 border border-secondary-200 rounded-lg p-4">
              <h3 className="font-semibold text-secondary-900 mb-2">Calculated Final Mark</h3>
              <div className="text-3xl font-bold text-secondary-700">
                {finalMark.toFixed(2)}
              </div>
              <div className="text-sm text-secondary-600 mt-1">
                {caMark && `CA: ${parseFloat(caMark).toFixed(2)}`}
                {caMark && examMark && ' + '}
                {examMark && `Exam: ${parseFloat(examMark).toFixed(2)}`}
              </div>
            </div>
          )}

          {/* Action Buttons */}
          <div className="flex gap-4">
            <button
              type="submit"
              disabled={saving || !selectedStudent || !selectedSubject || !selectedTerm || (!caMark && !examMark)}
              className="flex-1 bg-primary-600 text-white px-6 py-3 rounded-lg hover:bg-primary-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition duration-200 font-medium"
            >
              {saving ? 'Saving...' : 'Save Assessment'}
            </button>
            <button
              type="button"
              onClick={handleReset}
              className="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200 font-medium"
            >
              Reset
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default AssessmentEntry;
