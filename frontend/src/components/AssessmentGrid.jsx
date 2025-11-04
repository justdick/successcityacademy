import React, { useState, useEffect } from 'react';
import { getAllStudents, getAllClassLevels } from '../services/api';
import { getWeightings } from '../services/weightingService';
import { getActiveTerms } from '../services/termService';
import {
  createAssessment,
  getClassTermAssessments,
  validateCAMark,
  validateExamMark
} from '../services/assessmentService';
import { getMyAssignments } from '../services/teacherAssignmentService';
import { isAdmin } from '../services/auth';

const AssessmentGrid = () => {
  const [students, setStudents] = useState([]);
  const [subjects, setSubjects] = useState([]);
  const [terms, setTerms] = useState([]);
  const [classLevels, setClassLevels] = useState([]);
  const [assessments, setAssessments] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  // Filters
  const [selectedTerm, setSelectedTerm] = useState('');
  const [selectedClassLevel, setSelectedClassLevel] = useState('');

  // Editing state
  const [editingCell, setEditingCell] = useState(null); // {studentId, subjectId, type: 'ca' | 'exam'}
  const [editValue, setEditValue] = useState('');
  const [saving, setSaving] = useState(false);

  // Teacher assignments
  const [myAssignments, setMyAssignments] = useState(null);
  const [filteredClassLevels, setFilteredClassLevels] = useState([]);
  const [filteredSubjects, setFilteredSubjects] = useState([]);

  // Load initial data
  useEffect(() => {
    loadInitialData();
  }, []);

  const loadInitialData = async () => {
    try {
      setLoading(true);
      setError('');

      const [weightingsData, termsData, classLevelsData] = await Promise.all([
        getWeightings(),
        getActiveTerms(),
        getAllClassLevels()
      ]);

      setSubjects(weightingsData);
      setTerms(termsData);
      setClassLevels(classLevelsData);

      // Load teacher assignments if not admin
      if (!isAdmin()) {
        try {
          const assignments = await getMyAssignments();
          setMyAssignments(assignments);

          // Filter class levels by assigned classes
          const assignedClassIds = assignments.classes.map(c => c.id);
          const filteredClasses = classLevelsData.filter(c => 
            assignedClassIds.includes(c.id)
          );
          setFilteredClassLevels(filteredClasses);

          // Filter subjects by assigned subjects
          const assignedSubjectIds = assignments.subjects.map(s => s.id);
          const filteredSubs = weightingsData.filter(s => 
            assignedSubjectIds.includes(s.subject_id)
          );
          setFilteredSubjects(filteredSubs);
        } catch (err) {
          // If assignments fail to load, show empty lists
          setMyAssignments({ classes: [], subjects: [] });
          setFilteredClassLevels([]);
          setFilteredSubjects([]);
        }
      } else {
        // Admin sees all class levels and subjects
        setFilteredClassLevels(classLevelsData);
        setFilteredSubjects(weightingsData);
      }
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  // Load students and assessments when filters change
  useEffect(() => {
    if (selectedTerm && selectedClassLevel) {
      loadGridData();
    }
  }, [selectedTerm, selectedClassLevel]);

  const loadGridData = async () => {
    try {
      setLoading(true);
      setError('');

      // Get all students
      const allStudents = await getAllStudents();
      
      // Filter students by selected class level
      const filteredStudents = allStudents.filter(
        s => s.class_level_id === parseInt(selectedClassLevel)
      );
      setStudents(filteredStudents);

      // Get assessments for the class and term
      const assessmentsData = await getClassTermAssessments(
        selectedTerm,
        selectedClassLevel
      );
      setAssessments(assessmentsData);
    } catch (err) {
      setError(err.message);
      setStudents([]);
      setAssessments([]);
    } finally {
      setLoading(false);
    }
  };

  // Get assessment for a specific student and subject
  const getAssessment = (studentId, subjectId) => {
    return assessments.find(
      a => a.student_id === studentId && a.subject_id === subjectId
    );
  };

  // Check if assessment is complete (has both CA and exam)
  const isComplete = (studentId, subjectId) => {
    const assessment = getAssessment(studentId, subjectId);
    return assessment && assessment.ca_mark !== null && assessment.exam_mark !== null;
  };

  // Check if assessment is partial (has only CA or only exam)
  const isPartial = (studentId, subjectId) => {
    const assessment = getAssessment(studentId, subjectId);
    return assessment && (
      (assessment.ca_mark !== null && assessment.exam_mark === null) ||
      (assessment.ca_mark === null && assessment.exam_mark !== null)
    );
  };

  // Get cell background color based on completion status
  const getCellColor = (studentId, subjectId) => {
    if (isComplete(studentId, subjectId)) {
      return 'bg-primary-50';
    } else if (isPartial(studentId, subjectId)) {
      return 'bg-secondary-50';
    }
    return 'bg-white';
  };

  // Start editing a cell
  const startEditing = (studentId, subjectId, type) => {
    const assessment = getAssessment(studentId, subjectId);
    const currentValue = assessment 
      ? (type === 'ca' ? assessment.ca_mark : assessment.exam_mark)
      : null;
    
    setEditingCell({ studentId, subjectId, type });
    setEditValue(currentValue !== null ? currentValue : '');
  };

  // Cancel editing
  const cancelEditing = () => {
    setEditingCell(null);
    setEditValue('');
  };

  // Save the edited value
  const saveCell = async () => {
    if (!editingCell) return;

    const { studentId, subjectId, type } = editingCell;
    
    // Validate teacher assignments (client-side check)
    if (!isAdmin() && myAssignments) {
      // Check if student is in current class (already filtered, but double-check)
      const student = students.find(s => s.student_id === studentId);
      if (!student) {
        setError('You do not have access to this student.');
        cancelEditing();
        return;
      }

      // Check if subject is in assigned subjects
      const assignedSubjectIds = myAssignments.subjects.map(s => s.id);
      if (!assignedSubjectIds.includes(subjectId)) {
        setError('You do not have access to this subject.');
        cancelEditing();
        return;
      }
    }

    const subject = filteredSubjects.find(s => s.subject_id === subjectId);
    
    if (!subject) {
      setError('Subject not found');
      return;
    }

    // Validate the mark
    const maxMark = type === 'ca' ? subject.ca_percentage : subject.exam_percentage;
    const isValid = type === 'ca' 
      ? validateCAMark(editValue, maxMark)
      : validateExamMark(editValue, maxMark);

    if (editValue && !isValid) {
      setError(`${type === 'ca' ? 'CA' : 'Exam'} mark must be between 0 and ${maxMark}`);
      return;
    }

    try {
      setSaving(true);
      setError('');

      const existingAssessment = getAssessment(studentId, subjectId);
      
      const assessmentData = {
        student_id: studentId,
        subject_id: subjectId,
        term_id: parseInt(selectedTerm),
        ca_mark: type === 'ca' 
          ? (editValue ? parseFloat(editValue) : null)
          : (existingAssessment?.ca_mark || null),
        exam_mark: type === 'exam'
          ? (editValue ? parseFloat(editValue) : null)
          : (existingAssessment?.exam_mark || null)
      };

      await createAssessment(assessmentData);
      
      // Reload grid data
      await loadGridData();
      
      setSuccess('Assessment saved');
      setTimeout(() => setSuccess(''), 2000);
      
      cancelEditing();
    } catch (err) {
      setError(err.message);
    } finally {
      setSaving(false);
    }
  };

  // Handle key press in edit mode
  const handleKeyPress = (e) => {
    if (e.key === 'Enter') {
      saveCell();
    } else if (e.key === 'Escape') {
      cancelEditing();
    }
  };

  if (loading && !selectedTerm) {
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
    <div className="max-w-full mx-auto">
      <div className="bg-white rounded-lg shadow-lg p-6">
        <h2 className="text-2xl font-bold text-gray-800 mb-6">Assessment Grid - Bulk Entry</h2>

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

        {/* Filters */}
        <div className="grid grid-cols-2 gap-4 mb-6">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Term <span className="text-red-500">*</span>
            </label>
            <select
              value={selectedTerm}
              onChange={(e) => setSelectedTerm(e.target.value)}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            >
              <option value="">Select Term</option>
              {terms.map((term) => (
                <option key={term.id} value={term.id}>
                  {term.name} - {term.academic_year}
                </option>
              ))}
            </select>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Class Level <span className="text-red-500">*</span>
            </label>
            <select
              value={selectedClassLevel}
              onChange={(e) => setSelectedClassLevel(e.target.value)}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              disabled={filteredClassLevels.length === 0}
            >
              <option value="">
                {filteredClassLevels.length === 0 ? 'No classes available' : 'Select Class Level'}
              </option>
              {filteredClassLevels.map((classLevel) => (
                <option key={classLevel.id} value={classLevel.id}>
                  {classLevel.name}
                </option>
              ))}
            </select>
          </div>
        </div>

        {/* Legend */}
        {selectedTerm && selectedClassLevel && (
          <div className="flex gap-4 mb-4 text-sm">
            <div className="flex items-center gap-2">
              <div className="w-4 h-4 bg-primary-50 border border-primary-200"></div>
              <span>Complete (CA + Exam)</span>
            </div>
            <div className="flex items-center gap-2">
              <div className="w-4 h-4 bg-secondary-50 border border-secondary-200"></div>
              <span>Partial (CA or Exam only)</span>
            </div>
            <div className="flex items-center gap-2">
              <div className="w-4 h-4 bg-white border border-gray-200"></div>
              <span>Missing</span>
            </div>
          </div>
        )}

        {/* Grid */}
        {selectedTerm && selectedClassLevel && (
          <div className="overflow-x-auto">
            {loading ? (
              <div className="text-center py-8 text-gray-600">Loading grid data...</div>
            ) : students.length === 0 ? (
              <div className="text-center py-8 text-gray-600">
                No students found in this class level
              </div>
            ) : (
              <table className="min-w-full border-collapse border border-gray-300">
                <thead>
                  <tr className="bg-gray-100">
                    <th className="border border-gray-300 px-4 py-2 text-left font-semibold sticky left-0 bg-gray-100 z-10">
                      Student
                    </th>
                    {filteredSubjects.map((subject) => (
                      <th
                        key={subject.subject_id}
                        colSpan={2}
                        className="border border-gray-300 px-4 py-2 text-center font-semibold"
                      >
                        {subject.subject_name}
                        <div className="text-xs font-normal text-gray-600">
                          CA: {subject.ca_percentage}% | Exam: {subject.exam_percentage}%
                        </div>
                      </th>
                    ))}
                  </tr>
                  <tr className="bg-gray-50">
                    <th className="border border-gray-300 px-4 py-2 sticky left-0 bg-gray-50 z-10"></th>
                    {filteredSubjects.map((subject) => (
                      <React.Fragment key={`${subject.subject_id}-headers`}>
                        <th
                          className="border border-gray-300 px-2 py-1 text-xs text-center"
                        >
                          CA
                        </th>
                        <th
                          className="border border-gray-300 px-2 py-1 text-xs text-center"
                        >
                          Exam
                        </th>
                      </React.Fragment>
                    ))}
                  </tr>
                </thead>
                <tbody>
                  {students.map((student) => (
                    <tr key={student.student_id} className="hover:bg-gray-50">
                      <td className="border border-gray-300 px-4 py-2 font-medium sticky left-0 bg-white z-10">
                        {student.name}
                        <div className="text-xs text-gray-500">{student.student_id}</div>
                      </td>
                      {filteredSubjects.map((subject) => {
                        const assessment = getAssessment(student.student_id, subject.subject_id);
                        const cellColor = getCellColor(student.student_id, subject.subject_id);
                        
                        return (
                          <React.Fragment key={`${student.student_id}-${subject.subject_id}`}>
                            {/* CA Cell */}
                            <td
                              className={`border border-gray-300 px-2 py-2 text-center ${cellColor}`}
                              onClick={() => !editingCell && startEditing(student.student_id, subject.subject_id, 'ca')}
                            >
                              {editingCell?.studentId === student.student_id &&
                               editingCell?.subjectId === subject.subject_id &&
                               editingCell?.type === 'ca' ? (
                                <input
                                  type="number"
                                  step="0.01"
                                  value={editValue}
                                  onChange={(e) => setEditValue(e.target.value)}
                                  onBlur={saveCell}
                                  onKeyDown={handleKeyPress}
                                  className="w-full px-1 py-1 border border-primary-500 rounded text-center focus:outline-none"
                                  autoFocus
                                  disabled={saving}
                                />
                              ) : (
                                <span className="cursor-pointer hover:bg-primary-50 px-2 py-1 rounded">
                                  {assessment?.ca_mark !== null && assessment?.ca_mark !== undefined ? assessment.ca_mark : '-'}
                                </span>
                              )}
                            </td>

                            {/* Exam Cell */}
                            <td
                              className={`border border-gray-300 px-2 py-2 text-center ${cellColor}`}
                              onClick={() => !editingCell && startEditing(student.student_id, subject.subject_id, 'exam')}
                            >
                              {editingCell?.studentId === student.student_id &&
                               editingCell?.subjectId === subject.subject_id &&
                               editingCell?.type === 'exam' ? (
                                <input
                                  type="number"
                                  step="0.01"
                                  value={editValue}
                                  onChange={(e) => setEditValue(e.target.value)}
                                  onBlur={saveCell}
                                  onKeyDown={handleKeyPress}
                                  className="w-full px-1 py-1 border border-primary-500 rounded text-center focus:outline-none"
                                  autoFocus
                                  disabled={saving}
                                />
                              ) : (
                                <span className="cursor-pointer hover:bg-primary-50 px-2 py-1 rounded">
                                  {assessment?.exam_mark !== null && assessment?.exam_mark !== undefined ? assessment.exam_mark : '-'}
                                </span>
                              )}
                            </td>
                          </React.Fragment>
                        );
                      })}
                    </tr>
                  ))}
                </tbody>
              </table>
            )}
          </div>
        )}

        {!selectedTerm || !selectedClassLevel ? (
          <div className="text-center py-12 text-gray-500">
            Please select both term and class level to view the assessment grid
          </div>
        ) : null}

        {/* Instructions */}
        {selectedTerm && selectedClassLevel && students.length > 0 && (
          <div className="mt-4 text-sm text-gray-600 bg-primary-50 border border-primary-200 rounded p-4">
            <p className="font-semibold mb-2">Instructions:</p>
            <ul className="list-disc list-inside space-y-1">
              <li>Click on any cell to edit the mark</li>
              <li>Press Enter to save or Escape to cancel</li>
              <li>Marks are auto-saved when you click outside the cell</li>
              <li>Green cells indicate complete assessments (both CA and Exam entered)</li>
              <li>Yellow cells indicate partial assessments (only CA or Exam entered)</li>
            </ul>
          </div>
        )}
      </div>
    </div>
  );
};

export default AssessmentGrid;
