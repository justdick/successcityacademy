import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { createStudent, updateStudent, getStudent, getAllClassLevels } from '../services/api';
import { getMyAssignments } from '../services/teacherAssignmentService';
import { isAdmin } from '../services/auth';

const StudentForm = () => {
  const { studentId } = useParams();
  const isEditMode = !!studentId;
  const navigate = useNavigate();

  const [formData, setFormData] = useState({
    student_id: '',
    name: '',
    class_level_id: ''
  });
  const [classLevels, setClassLevels] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [validationErrors, setValidationErrors] = useState({});
  const [accessDenied, setAccessDenied] = useState(false);

  useEffect(() => {
    loadClassLevels();
    if (isEditMode) {
      loadStudent();
    }
  }, [studentId]);

  const loadClassLevels = async () => {
    try {
      const data = await getAllClassLevels();
      setClassLevels(data);
    } catch (err) {
      setError('Failed to load class levels: ' + err.message);
    }
  };

  const loadStudent = async () => {
    try {
      setLoading(true);
      const data = await getStudent(studentId);
      
      // Check access for teachers (not admins)
      if (!isAdmin()) {
        try {
          const assignments = await getMyAssignments();
          const accessibleClassIds = assignments.classes.map(c => c.id);
          
          // Check if teacher has access to this student's class
          if (!accessibleClassIds.includes(data.class_level_id)) {
            setAccessDenied(true);
            setError('Access denied. You do not have permission to view this student.');
            setLoading(false);
            return;
          }
        } catch (err) {
          // If no assignments, deny access
          setAccessDenied(true);
          setError('Access denied. You do not have any class assignments.');
          setLoading(false);
          return;
        }
      }
      
      setFormData({
        student_id: data.student_id,
        name: data.name,
        class_level_id: data.class_level_id.toString()
      });
    } catch (err) {
      if (err.message.includes('403') || err.message.includes('Access denied')) {
        setAccessDenied(true);
        setError('Access denied. You do not have permission to view this student.');
      } else {
        setError('Failed to load student: ' + err.message);
      }
    } finally {
      setLoading(false);
    }
  };

  const validateForm = () => {
    const errors = {};

    if (!formData.student_id.trim()) {
      errors.student_id = 'Student ID is required';
    }

    if (!formData.name.trim()) {
      errors.name = 'Name is required';
    }

    if (!formData.class_level_id) {
      errors.class_level_id = 'Class Level is required';
    }

    setValidationErrors(errors);
    return Object.keys(errors).length === 0;
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
    // Clear validation error for this field
    if (validationErrors[name]) {
      setValidationErrors(prev => ({
        ...prev,
        [name]: ''
      }));
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!validateForm()) {
      return;
    }

    try {
      setLoading(true);
      setError('');

      const submitData = {
        ...formData,
        class_level_id: parseInt(formData.class_level_id)
      };

      if (isEditMode) {
        // For update, don't send student_id in the body
        const { student_id, ...updateData } = submitData;
        await updateStudent(studentId, updateData);
      } else {
        await createStudent(submitData);
      }

      navigate('/students');
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const handleCancel = () => {
    navigate('/students');
  };

  if (loading && isEditMode) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="text-gray-600">Loading student...</div>
      </div>
    );
  }

  // Show access denied message
  if (accessDenied) {
    return (
      <div className="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
        <h2 className="text-2xl font-bold text-gray-800 mb-6">Access Denied</h2>
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
          <p className="font-medium">Access Denied</p>
          <p className="text-sm mt-1">{error}</p>
        </div>
        <button
          onClick={() => navigate('/students')}
          className="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded transition"
        >
          Back to Students
        </button>
      </div>
    );
  }

  return (
    <div className="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
      <h2 className="text-2xl font-bold text-gray-800 mb-6">
        {isEditMode ? 'Edit Student' : 'Add New Student'}
      </h2>

      {error && !accessDenied && (
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
          {error}
        </div>
      )}

      <form onSubmit={handleSubmit}>
        {/* Student ID */}
        <div className="mb-4">
          <label htmlFor="student_id" className="block text-sm font-medium text-gray-700 mb-2">
            Student ID <span className="text-red-500">*</span>
          </label>
          <input
            type="text"
            id="student_id"
            name="student_id"
            value={formData.student_id}
            onChange={handleChange}
            disabled={isEditMode}
            className={`w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-500 ${
              validationErrors.student_id ? 'border-red-500' : 'border-gray-300'
            } ${isEditMode ? 'bg-gray-100 cursor-not-allowed' : ''}`}
            placeholder="e.g., S001"
          />
          {validationErrors.student_id && (
            <p className="text-red-500 text-sm mt-1">{validationErrors.student_id}</p>
          )}
          {isEditMode && (
            <p className="text-gray-500 text-sm mt-1">Student ID cannot be changed</p>
          )}
        </div>

        {/* Name */}
        <div className="mb-4">
          <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-2">
            Name <span className="text-red-500">*</span>
          </label>
          <input
            type="text"
            id="name"
            name="name"
            value={formData.name}
            onChange={handleChange}
            className={`w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-500 ${
              validationErrors.name ? 'border-red-500' : 'border-gray-300'
            }`}
            placeholder="e.g., John Doe"
          />
          {validationErrors.name && (
            <p className="text-red-500 text-sm mt-1">{validationErrors.name}</p>
          )}
        </div>

        {/* Class Level */}
        <div className="mb-6">
          <label htmlFor="class_level_id" className="block text-sm font-medium text-gray-700 mb-2">
            Class Level <span className="text-red-500">*</span>
          </label>
          <select
            id="class_level_id"
            name="class_level_id"
            value={formData.class_level_id}
            onChange={handleChange}
            className={`w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-500 ${
              validationErrors.class_level_id ? 'border-red-500' : 'border-gray-300'
            }`}
          >
            <option value="">Select a class level</option>
            {classLevels.map(level => (
              <option key={level.id} value={level.id}>
                {level.name}
              </option>
            ))}
          </select>
          {validationErrors.class_level_id && (
            <p className="text-red-500 text-sm mt-1">{validationErrors.class_level_id}</p>
          )}
        </div>

        {/* Buttons */}
        <div className="flex space-x-4">
          <button
            type="submit"
            disabled={loading}
            className="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded transition disabled:bg-gray-400 disabled:cursor-not-allowed"
          >
            {loading ? 'Saving...' : (isEditMode ? 'Update Student' : 'Add Student')}
          </button>
          <button
            type="button"
            onClick={handleCancel}
            className="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded transition"
          >
            Cancel
          </button>
        </div>
      </form>
    </div>
  );
};

export default StudentForm;
