import { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import { getAllUsers } from '../services/api';
import { getAllSubjects, getAllClassLevels } from '../services/api';
import {
  getAllTeacherAssignments,
  assignTeacherToClass,
  assignTeacherToSubject,
  bulkAssignTeacher,
  removeClassAssignment,
  removeSubjectAssignment
} from '../services/teacherAssignmentService';

const TeacherAssignments = () => {
  const { isAdmin } = useAuth();
  
  // State for data
  const [teachers, setTeachers] = useState([]);
  const [assignments, setAssignments] = useState([]);
  const [subjects, setSubjects] = useState([]);
  const [classLevels, setClassLevels] = useState([]);
  
  // State for UI
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  
  // State for filters
  const [selectedTeacher, setSelectedTeacher] = useState('');
  const [filterClass, setFilterClass] = useState('');
  const [filterSubject, setFilterSubject] = useState('');
  
  // State for forms
  const [showClassForm, setShowClassForm] = useState(false);
  const [showSubjectForm, setShowSubjectForm] = useState(false);
  const [showBulkForm, setShowBulkForm] = useState(false);
  
  // State for form data
  const [formTeacherId, setFormTeacherId] = useState('');
  const [formClassId, setFormClassId] = useState('');
  const [formSubjectId, setFormSubjectId] = useState('');
  const [bulkClassIds, setBulkClassIds] = useState([]);
  const [bulkSubjectIds, setBulkSubjectIds] = useState([]);
  
  // State for confirmation dialog
  const [confirmDialog, setConfirmDialog] = useState({ show: false, type: '', id: null, name: '' });

  // Redirect if not admin
  if (!isAdmin()) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-100">
        <div className="bg-white p-8 rounded-lg shadow-md">
          <h2 className="text-xl font-bold text-red-600 mb-4">Access Denied</h2>
          <p className="text-gray-700">You do not have permission to access this page.</p>
        </div>
      </div>
    );
  }

  useEffect(() => {
    loadData();
  }, []);

  useEffect(() => {
    loadAssignments();
  }, [selectedTeacher, filterClass, filterSubject]);

  const loadData = async () => {
    try {
      setLoading(true);
      setError('');
      
      const [usersData, subjectsData, classLevelsData] = await Promise.all([
        getAllUsers(),
        getAllSubjects(),
        getAllClassLevels()
      ]);
      
      // Filter only teachers (role = 'user')
      const teachersOnly = usersData.filter(user => user.role === 'user');
      setTeachers(teachersOnly);
      setSubjects(subjectsData);
      setClassLevels(classLevelsData);
      
      await loadAssignments();
    } catch (err) {
      setError(err.message || 'Failed to load data');
    } finally {
      setLoading(false);
    }
  };

  const loadAssignments = async () => {
    try {
      const filters = {};
      if (selectedTeacher) filters.user_id = selectedTeacher;
      if (filterClass) filters.class_level_id = filterClass;
      if (filterSubject) filters.subject_id = filterSubject;
      
      const data = await getAllTeacherAssignments(filters);
      setAssignments(data);
    } catch (err) {
      setError(err.message || 'Failed to load assignments');
    }
  };

  const handleAssignClass = async (e) => {
    e.preventDefault();
    try {
      setError('');
      setSuccess('');
      
      await assignTeacherToClass({
        user_id: parseInt(formTeacherId),
        class_level_id: parseInt(formClassId)
      });
      
      setSuccess('Teacher assigned to class successfully');
      setShowClassForm(false);
      setFormTeacherId('');
      setFormClassId('');
      await loadAssignments();
    } catch (err) {
      setError(err.message || 'Failed to assign teacher to class');
    }
  };

  const handleAssignSubject = async (e) => {
    e.preventDefault();
    try {
      setError('');
      setSuccess('');
      
      await assignTeacherToSubject({
        user_id: parseInt(formTeacherId),
        subject_id: parseInt(formSubjectId)
      });
      
      setSuccess('Teacher assigned to subject successfully');
      setShowSubjectForm(false);
      setFormTeacherId('');
      setFormSubjectId('');
      await loadAssignments();
    } catch (err) {
      setError(err.message || 'Failed to assign teacher to subject');
    }
  };

  const handleBulkAssign = async (e) => {
    e.preventDefault();
    try {
      setError('');
      setSuccess('');
      
      const result = await bulkAssignTeacher({
        user_id: parseInt(formTeacherId),
        class_level_ids: bulkClassIds.map(id => parseInt(id)),
        subject_ids: bulkSubjectIds.map(id => parseInt(id))
      });
      
      setSuccess(`Bulk assignment successful: ${result.classes_assigned} classes and ${result.subjects_assigned} subjects assigned`);
      setShowBulkForm(false);
      setFormTeacherId('');
      setBulkClassIds([]);
      setBulkSubjectIds([]);
      await loadAssignments();
    } catch (err) {
      setError(err.message || 'Failed to bulk assign teacher');
    }
  };

  const handleRemoveAssignment = async () => {
    try {
      setError('');
      setSuccess('');
      
      if (confirmDialog.type === 'class') {
        await removeClassAssignment(confirmDialog.id);
        setSuccess('Class assignment removed successfully');
      } else if (confirmDialog.type === 'subject') {
        await removeSubjectAssignment(confirmDialog.id);
        setSuccess('Subject assignment removed successfully');
      }
      
      setConfirmDialog({ show: false, type: '', id: null, name: '' });
      await loadAssignments();
    } catch (err) {
      setError(err.message || 'Failed to remove assignment');
      setConfirmDialog({ show: false, type: '', id: null, name: '' });
    }
  };

  const toggleBulkClass = (classId) => {
    setBulkClassIds(prev => 
      prev.includes(classId) 
        ? prev.filter(id => id !== classId)
        : [...prev, classId]
    );
  };

  const toggleBulkSubject = (subjectId) => {
    setBulkSubjectIds(prev => 
      prev.includes(subjectId)
        ? prev.filter(id => id !== subjectId)
        : [...prev, subjectId]
    );
  };

  return (
    <div className="min-h-screen bg-gray-100 py-8">
      <div className="max-w-7xl mx-auto px-4">
        <div className="bg-white rounded-lg shadow-md p-6">
          {/* Header */}
          <div className="flex justify-between items-center mb-6">
            <h1 className="text-2xl font-bold text-gray-800">Teacher Assignments</h1>
            <div className="flex space-x-2">
              <button
                onClick={() => {
                  setShowClassForm(!showClassForm);
                  setShowSubjectForm(false);
                  setShowBulkForm(false);
                }}
                className="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 transition-colors"
              >
                Assign Class
              </button>
              <button
                onClick={() => {
                  setShowSubjectForm(!showSubjectForm);
                  setShowClassForm(false);
                  setShowBulkForm(false);
                }}
                className="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 transition-colors"
              >
                Assign Subject
              </button>
              <button
                onClick={() => {
                  setShowBulkForm(!showBulkForm);
                  setShowClassForm(false);
                  setShowSubjectForm(false);
                }}
                className="bg-secondary-600 text-white px-4 py-2 rounded-md hover:bg-secondary-700 transition-colors"
              >
                Bulk Assign
              </button>
            </div>
          </div>

          {/* Messages */}
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

          {/* Class Assignment Form */}
          {showClassForm && (
            <div className="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
              <h3 className="text-lg font-semibold mb-4">Assign Teacher to Class</h3>
              <form onSubmit={handleAssignClass} className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Teacher
                  </label>
                  <select
                    value={formTeacherId}
                    onChange={(e) => setFormTeacherId(e.target.value)}
                    required
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                    <option value="">Select Teacher</option>
                    {teachers.map(teacher => (
                      <option key={teacher.id} value={teacher.id}>
                        {teacher.username}
                      </option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Class Level
                  </label>
                  <select
                    value={formClassId}
                    onChange={(e) => setFormClassId(e.target.value)}
                    required
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                    <option value="">Select Class</option>
                    {classLevels.map(classLevel => (
                      <option key={classLevel.id} value={classLevel.id}>
                        {classLevel.name}
                      </option>
                    ))}
                  </select>
                </div>
                <div className="flex space-x-2">
                  <button
                    type="submit"
                    className="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 transition-colors"
                  >
                    Assign
                  </button>
                  <button
                    type="button"
                    onClick={() => setShowClassForm(false)}
                    className="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors"
                  >
                    Cancel
                  </button>
                </div>
              </form>
            </div>
          )}

          {/* Subject Assignment Form */}
          {showSubjectForm && (
            <div className="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
              <h3 className="text-lg font-semibold mb-4">Assign Teacher to Subject</h3>
              <form onSubmit={handleAssignSubject} className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Teacher
                  </label>
                  <select
                    value={formTeacherId}
                    onChange={(e) => setFormTeacherId(e.target.value)}
                    required
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                    <option value="">Select Teacher</option>
                    {teachers.map(teacher => (
                      <option key={teacher.id} value={teacher.id}>
                        {teacher.username}
                      </option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Subject
                  </label>
                  <select
                    value={formSubjectId}
                    onChange={(e) => setFormSubjectId(e.target.value)}
                    required
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                    <option value="">Select Subject</option>
                    {subjects.map(subject => (
                      <option key={subject.id} value={subject.id}>
                        {subject.name}
                      </option>
                    ))}
                  </select>
                </div>
                <div className="flex space-x-2">
                  <button
                    type="submit"
                    className="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 transition-colors"
                  >
                    Assign
                  </button>
                  <button
                    type="button"
                    onClick={() => setShowSubjectForm(false)}
                    className="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors"
                  >
                    Cancel
                  </button>
                </div>
              </form>
            </div>
          )}

          {/* Bulk Assignment Form */}
          {showBulkForm && (
            <div className="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
              <h3 className="text-lg font-semibold mb-4">Bulk Assign Teacher</h3>
              <form onSubmit={handleBulkAssign} className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Teacher
                  </label>
                  <select
                    value={formTeacherId}
                    onChange={(e) => setFormTeacherId(e.target.value)}
                    required
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                    <option value="">Select Teacher</option>
                    {teachers.map(teacher => (
                      <option key={teacher.id} value={teacher.id}>
                        {teacher.username}
                      </option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Classes (select multiple)
                  </label>
                  <div className="border border-gray-300 rounded-md p-3 max-h-40 overflow-y-auto">
                    {classLevels.map(classLevel => (
                      <label key={classLevel.id} className="flex items-center space-x-2 mb-2">
                        <input
                          type="checkbox"
                          checked={bulkClassIds.includes(classLevel.id)}
                          onChange={() => toggleBulkClass(classLevel.id)}
                          className="rounded text-primary-600 focus:ring-primary-500"
                        />
                        <span>{classLevel.name}</span>
                      </label>
                    ))}
                  </div>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Subjects (select multiple)
                  </label>
                  <div className="border border-gray-300 rounded-md p-3 max-h-40 overflow-y-auto">
                    {subjects.map(subject => (
                      <label key={subject.id} className="flex items-center space-x-2 mb-2">
                        <input
                          type="checkbox"
                          checked={bulkSubjectIds.includes(subject.id)}
                          onChange={() => toggleBulkSubject(subject.id)}
                          className="rounded text-primary-600 focus:ring-primary-500"
                        />
                        <span>{subject.name}</span>
                      </label>
                    ))}
                  </div>
                </div>
                <div className="flex space-x-2">
                  <button
                    type="submit"
                    className="bg-secondary-600 text-white px-4 py-2 rounded-md hover:bg-secondary-700 transition-colors"
                  >
                    Bulk Assign
                  </button>
                  <button
                    type="button"
                    onClick={() => setShowBulkForm(false)}
                    className="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors"
                  >
                    Cancel
                  </button>
                </div>
              </form>
            </div>
          )}

          {/* Filters */}
          <div className="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <h3 className="text-lg font-semibold mb-4">Filters</h3>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Filter by Teacher
                </label>
                <select
                  value={selectedTeacher}
                  onChange={(e) => setSelectedTeacher(e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                  <option value="">All Teachers</option>
                  {teachers.map(teacher => (
                    <option key={teacher.id} value={teacher.id}>
                      {teacher.username}
                    </option>
                  ))}
                </select>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Filter by Class
                </label>
                <select
                  value={filterClass}
                  onChange={(e) => setFilterClass(e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                  <option value="">All Classes</option>
                  {classLevels.map(classLevel => (
                    <option key={classLevel.id} value={classLevel.id}>
                      {classLevel.name}
                    </option>
                  ))}
                </select>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Filter by Subject
                </label>
                <select
                  value={filterSubject}
                  onChange={(e) => setFilterSubject(e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                  <option value="">All Subjects</option>
                  {subjects.map(subject => (
                    <option key={subject.id} value={subject.id}>
                      {subject.name}
                    </option>
                  ))}
                </select>
              </div>
            </div>
          </div>

          {/* Assignments List */}
          {loading ? (
            <div className="text-center py-8">
              <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
              <p className="mt-2 text-gray-600">Loading assignments...</p>
            </div>
          ) : assignments.length === 0 ? (
            <div className="text-center py-8 text-gray-500">
              No teacher assignments found
            </div>
          ) : (
            <div className="space-y-6">
              {assignments.map((assignment) => (
                <div key={assignment.user_id} className="border border-gray-200 rounded-lg p-4">
                  <div className="flex justify-between items-start mb-4">
                    <div>
                      <h3 className="text-lg font-semibold text-gray-800">
                        {assignment.username}
                      </h3>
                      <p className="text-sm text-gray-600">
                        {assignment.classes?.length || 0} classes, {assignment.subjects?.length || 0} subjects
                      </p>
                    </div>
                  </div>

                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {/* Assigned Classes */}
                    <div>
                      <h4 className="font-medium text-gray-700 mb-2">Assigned Classes</h4>
                      {assignment.classes && assignment.classes.length > 0 ? (
                        <div className="space-y-2">
                          {assignment.classes.map((cls) => (
                            <div
                              key={cls.id}
                              className="flex justify-between items-center bg-primary-50 px-3 py-2 rounded"
                            >
                              <span className="text-sm">{cls.name}</span>
                              <button
                                onClick={() => setConfirmDialog({
                                  show: true,
                                  type: 'class',
                                  id: cls.assignment_id,
                                  name: cls.name
                                })}
                                className="text-red-600 hover:text-red-800 text-sm font-medium"
                              >
                                Remove
                              </button>
                            </div>
                          ))}
                        </div>
                      ) : (
                        <p className="text-sm text-gray-500">No classes assigned</p>
                      )}
                    </div>

                    {/* Assigned Subjects */}
                    <div>
                      <h4 className="font-medium text-gray-700 mb-2">Assigned Subjects</h4>
                      {assignment.subjects && assignment.subjects.length > 0 ? (
                        <div className="space-y-2">
                          {assignment.subjects.map((subject) => (
                            <div
                              key={subject.id}
                              className="flex justify-between items-center bg-secondary-50 px-3 py-2 rounded"
                            >
                              <span className="text-sm">{subject.name}</span>
                              <button
                                onClick={() => setConfirmDialog({
                                  show: true,
                                  type: 'subject',
                                  id: subject.assignment_id,
                                  name: subject.name
                                })}
                                className="text-red-600 hover:text-red-800 text-sm font-medium"
                              >
                                Remove
                              </button>
                            </div>
                          ))}
                        </div>
                      ) : (
                        <p className="text-sm text-gray-500">No subjects assigned</p>
                      )}
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>

      {/* Confirmation Dialog */}
      {confirmDialog.show && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 className="text-lg font-semibold mb-4">Confirm Removal</h3>
            <p className="text-gray-700 mb-6">
              Are you sure you want to remove the {confirmDialog.type} assignment "{confirmDialog.name}"?
            </p>
            <div className="flex justify-end space-x-2">
              <button
                onClick={() => setConfirmDialog({ show: false, type: '', id: null, name: '' })}
                className="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors"
              >
                Cancel
              </button>
              <button
                onClick={handleRemoveAssignment}
                className="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors"
              >
                Remove
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default TeacherAssignments;
