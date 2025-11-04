import { useState, useEffect } from 'react';

/**
 * TermForm Component
 * 
 * Form for creating or editing academic terms
 * Validates academic year format (YYYY/YYYY)
 * 
 * @param {object} term - Existing term data for editing (optional)
 * @param {function} onSubmit - Callback function when form is submitted
 * @param {function} onCancel - Callback function when form is cancelled
 */
const TermForm = ({ term = null, onSubmit, onCancel }) => {
  const [formData, setFormData] = useState({
    name: '',
    academic_year: '',
    start_date: '',
    end_date: '',
    is_active: true
  });

  const [errors, setErrors] = useState({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Populate form with existing term data when editing
  useEffect(() => {
    if (term) {
      setFormData({
        name: term.name || '',
        academic_year: term.academic_year || '',
        start_date: term.start_date || '',
        end_date: term.end_date || '',
        is_active: term.is_active !== undefined ? term.is_active : true
      });
    }
  }, [term]);

  /**
   * Validate academic year format (YYYY/YYYY)
   * @param {string} year 
   * @returns {boolean}
   */
  const validateAcademicYear = (year) => {
    const pattern = /^\d{4}\/\d{4}$/;
    if (!pattern.test(year)) {
      return false;
    }
    
    const [startYear, endYear] = year.split('/').map(Number);
    return endYear === startYear + 1;
  };

  /**
   * Validate form data
   * @returns {boolean} True if form is valid
   */
  const validateForm = () => {
    const newErrors = {};

    if (!formData.name.trim()) {
      newErrors.name = 'Term name is required';
    }

    if (!formData.academic_year.trim()) {
      newErrors.academic_year = 'Academic year is required';
    } else if (!validateAcademicYear(formData.academic_year)) {
      newErrors.academic_year = 'Invalid academic year format. Use YYYY/YYYY (e.g., 2024/2025)';
    }

    if (formData.start_date && formData.end_date) {
      if (new Date(formData.start_date) >= new Date(formData.end_date)) {
        newErrors.end_date = 'End date must be after start date';
      }
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  /**
   * Handle input changes
   */
  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value
    }));

    // Clear error for this field when user starts typing
    if (errors[name]) {
      setErrors(prev => ({
        ...prev,
        [name]: ''
      }));
    }
  };

  /**
   * Handle form submission
   */
  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!validateForm()) {
      return;
    }

    setIsSubmitting(true);

    try {
      await onSubmit(formData);
    } catch (error) {
      setErrors({ submit: error.message });
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      {/* Term Name */}
      <div>
        <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-1">
          Term Name <span className="text-red-500">*</span>
        </label>
        <input
          type="text"
          id="name"
          name="name"
          value={formData.name}
          onChange={handleChange}
          placeholder="e.g., Term 1, Term 2, Term 3"
          className={`w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent ${
            errors.name ? 'border-red-500' : 'border-gray-300'
          }`}
        />
        {errors.name && (
          <p className="mt-1 text-sm text-red-600">{errors.name}</p>
        )}
      </div>

      {/* Academic Year */}
      <div>
        <label htmlFor="academic_year" className="block text-sm font-medium text-gray-700 mb-1">
          Academic Year <span className="text-red-500">*</span>
        </label>
        <input
          type="text"
          id="academic_year"
          name="academic_year"
          value={formData.academic_year}
          onChange={handleChange}
          placeholder="e.g., 2024/2025"
          className={`w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent ${
            errors.academic_year ? 'border-red-500' : 'border-gray-300'
          }`}
        />
        {errors.academic_year && (
          <p className="mt-1 text-sm text-red-600">{errors.academic_year}</p>
        )}
        <p className="mt-1 text-xs text-gray-500">Format: YYYY/YYYY (e.g., 2024/2025)</p>
      </div>

      {/* Start Date */}
      <div>
        <label htmlFor="start_date" className="block text-sm font-medium text-gray-700 mb-1">
          Start Date
        </label>
        <input
          type="date"
          id="start_date"
          name="start_date"
          value={formData.start_date}
          onChange={handleChange}
          className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
        />
      </div>

      {/* End Date */}
      <div>
        <label htmlFor="end_date" className="block text-sm font-medium text-gray-700 mb-1">
          End Date
        </label>
        <input
          type="date"
          id="end_date"
          name="end_date"
          value={formData.end_date}
          onChange={handleChange}
          className={`w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent ${
            errors.end_date ? 'border-red-500' : 'border-gray-300'
          }`}
        />
        {errors.end_date && (
          <p className="mt-1 text-sm text-red-600">{errors.end_date}</p>
        )}
      </div>

      {/* Active Status */}
      <div className="flex items-center">
        <input
          type="checkbox"
          id="is_active"
          name="is_active"
          checked={formData.is_active}
          onChange={handleChange}
          className="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
        />
        <label htmlFor="is_active" className="ml-2 block text-sm text-gray-700">
          Active Term
        </label>
      </div>

      {/* Submit Error */}
      {errors.submit && (
        <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
          {errors.submit}
        </div>
      )}

      {/* Form Actions */}
      <div className="flex justify-end space-x-3 pt-4">
        <button
          type="button"
          onClick={onCancel}
          disabled={isSubmitting}
          className="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200 disabled:opacity-50"
        >
          Cancel
        </button>
        <button
          type="submit"
          disabled={isSubmitting}
          className="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {isSubmitting ? 'Saving...' : term ? 'Update Term' : 'Create Term'}
        </button>
      </div>
    </form>
  );
};

export default TermForm;
