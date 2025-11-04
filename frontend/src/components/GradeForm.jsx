import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { addGrade, getAllStudents, getAllSubjects } from '../services/api';

const GradeForm = () => {
  const navigate = useNavigate();

  const [formData, setFormData] = useState({
    student_id: '',
    subject_id: '',
    mark: ''
  });
  const [students, setStudents] = useState([]);
  const [subjects, setSubjects] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [validationErrors, setValidationErrors] = useState({});

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      const [studentsData, subjectsData] = await Promise.all([
        getAllStudents(),
        getAllSubjects()
      ]);
      setStudents(studentsData);
      setSubjects(subjectsData);
    } catch (err) {
      setError('Failed to load data: ' + err.message);
    }
  };

  const validateForm = () => {
    const errors = {};

    if (!formData.student_id) {
      errors.student_id = 'Student is required';
    }

    if (!formData.subject_id) {
      errors.subject_id = 'Subject is required';
    }

    if (!formData.mark) {
      errors.mark = 'Mark is required';
    } else {
      const markValue = parseFloat(formData.mark);
      if (isNaN(markValue)) {
        errors.mark = 'Mark must be a number';
      } else if (markValue < 0 || markValue > 100) {
        errors.mark = 'Mark must be between 0 and 100';
      }
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
        student_id: formData.student_id,
        subject_id: parseInt(formData.subject_id),
        mark: parseFloat(formData.mark)
      };

      await addGrade(submitData);
      
      // Navigate back to grades page
      navigate('/grades');
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const handleCancel = () => {
    navigate('/students');
  };

  return (
    <div className="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
      <h2 className="text-2xl font-bold text-gray-800 mb-6">
        Add Grade
      </h2>

      {error && (
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
          {error}
        </div>
      )}

      <form onSubmit={handleSubmit}>
        {/* Student Selection */}
        <div className="mb-4">
          <label htmlFor="student_id" className="block text-sm font-medium text-gray-700 mb-2">
            Student <span className="text-red-500">*</span>
          </label>
          <select
            id="student_id"
            name="student_id"
            value={formData.student_id}
            onChange={handleChange}
            className={`w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-500 ${
              validationErrors.student_id ? 'border-red-500' : 'border-gray-300'
            }`}
          >
            <option value="">Select a student</option>
            {students.map(student => (
              <option key={student.student_id} value={student.student_id}>
                {student.student_id} - {student.name}
              </option>
            ))}
          </select>
          {validationErrors.student_id && (
            <p className="text-red-500 text-sm mt-1">{validationErrors.student_id}</p>
          )}
        </div>

        {/* Subject Selection */}
        <div className="mb-4">
          <label htmlFor="subject_id" className="block text-sm font-medium text-gray-700 mb-2">
            Subject <span className="text-red-500">*</span>
          </label>
          <select
            id="subject_id"
            name="subject_id"
            value={formData.subject_id}
            onChange={handleChange}
            className={`w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-500 ${
              validationErrors.subject_id ? 'border-red-500' : 'border-gray-300'
            }`}
          >
            <option value="">Select a subject</option>
            {subjects.map(subject => (
              <option key={subject.id} value={subject.id}>
                {subject.name}
              </option>
            ))}
          </select>
          {validationErrors.subject_id && (
            <p className="text-red-500 text-sm mt-1">{validationErrors.subject_id}</p>
          )}
        </div>

        {/* Mark Input */}
        <div className="mb-6">
          <label htmlFor="mark" className="block text-sm font-medium text-gray-700 mb-2">
            Mark (0-100) <span className="text-red-500">*</span>
          </label>
          <input
            type="number"
            id="mark"
            name="mark"
            value={formData.mark}
            onChange={handleChange}
            min="0"
            max="100"
            step="0.01"
            className={`w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-primary-500 ${
              validationErrors.mark ? 'border-red-500' : 'border-gray-300'
            }`}
            placeholder="e.g., 85.5"
          />
          {validationErrors.mark && (
            <p className="text-red-500 text-sm mt-1">{validationErrors.mark}</p>
          )}
        </div>

        {/* Buttons */}
        <div className="flex space-x-4">
          <button
            type="submit"
            disabled={loading}
            className="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded transition disabled:bg-gray-400 disabled:cursor-not-allowed"
          >
            {loading ? 'Adding...' : 'Add Grade'}
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

export default GradeForm;
