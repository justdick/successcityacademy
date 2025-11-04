import { useState, useEffect } from 'react';
import { validatePercentageSum, validatePercentage } from '../services/weightingService';

/**
 * WeightingForm Component
 * 
 * Form for configuring CA and exam percentages for a subject
 * Features:
 * - Real-time validation for sum = 100%
 * - Display error messages for invalid percentages
 * - Show calculated sum as user types
 * - Visual feedback for valid/invalid state
 */
const WeightingForm = ({ weighting, onSubmit, onCancel }) => {
  const [formData, setFormData] = useState({
    ca_percentage: '',
    exam_percentage: ''
  });
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  const [sum, setSum] = useState(0);

  useEffect(() => {
    if (weighting) {
      setFormData({
        ca_percentage: weighting.ca_percentage || '',
        exam_percentage: weighting.exam_percentage || ''
      });
    }
  }, [weighting]);

  useEffect(() => {
    // Calculate sum whenever percentages change
    const ca = parseFloat(formData.ca_percentage) || 0;
    const exam = parseFloat(formData.exam_percentage) || 0;
    setSum(ca + exam);
  }, [formData.ca_percentage, formData.exam_percentage]);

  /**
   * Validate form data
   * @returns {boolean} True if valid
   */
  const validate = () => {
    const newErrors = {};

    // Validate CA percentage
    if (!formData.ca_percentage && formData.ca_percentage !== 0) {
      newErrors.ca_percentage = 'CA percentage is required';
    } else if (!validatePercentage(formData.ca_percentage)) {
      newErrors.ca_percentage = 'CA percentage must be between 0 and 100';
    }

    // Validate exam percentage
    if (!formData.exam_percentage && formData.exam_percentage !== 0) {
      newErrors.exam_percentage = 'Exam percentage is required';
    } else if (!validatePercentage(formData.exam_percentage)) {
      newErrors.exam_percentage = 'Exam percentage must be between 0 and 100';
    }

    // Validate sum
    if (formData.ca_percentage && formData.exam_percentage) {
      if (!validatePercentageSum(formData.ca_percentage, formData.exam_percentage)) {
        newErrors.sum = 'CA and exam percentages must sum to exactly 100%';
      }
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  /**
   * Handle input change
   */
  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
    
    // Clear error for this field
    if (errors[name]) {
      setErrors(prev => ({
        ...prev,
        [name]: ''
      }));
    }
    
    // Clear sum error when user is typing
    if (errors.sum) {
      setErrors(prev => ({
        ...prev,
        sum: ''
      }));
    }
  };

  /**
   * Handle form submission
   */
  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!validate()) {
      return;
    }

    try {
      setLoading(true);
      setErrors({});

      // Convert to numbers
      const data = {
        ca_percentage: parseFloat(formData.ca_percentage),
        exam_percentage: parseFloat(formData.exam_percentage)
      };

      await onSubmit(data);
    } catch (err) {
      setErrors({ submit: err.message });
    } finally {
      setLoading(false);
    }
  };

  /**
   * Determine if sum is valid
   */
  const isSumValid = () => {
    if (!formData.ca_percentage || !formData.exam_percentage) {
      return null; // Not yet determined
    }
    return validatePercentageSum(formData.ca_percentage, formData.exam_percentage);
  };

  const sumValid = isSumValid();

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      {/* CA Percentage */}
      <div>
        <label htmlFor="ca_percentage" className="block text-sm font-medium text-gray-700 mb-1">
          Continuous Assessment (CA) Percentage
        </label>
        <input
          type="number"
          id="ca_percentage"
          name="ca_percentage"
          value={formData.ca_percentage}
          onChange={handleChange}
          min="0"
          max="100"
          step="0.01"
          className={`w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 ${
            errors.ca_percentage
              ? 'border-red-300 focus:ring-red-500'
              : 'border-gray-300 focus:ring-primary-500'
          }`}
          placeholder="e.g., 40"
          disabled={loading}
        />
        {errors.ca_percentage && (
          <p className="mt-1 text-sm text-red-600">{errors.ca_percentage}</p>
        )}
      </div>

      {/* Exam Percentage */}
      <div>
        <label htmlFor="exam_percentage" className="block text-sm font-medium text-gray-700 mb-1">
          Exam Percentage
        </label>
        <input
          type="number"
          id="exam_percentage"
          name="exam_percentage"
          value={formData.exam_percentage}
          onChange={handleChange}
          min="0"
          max="100"
          step="0.01"
          className={`w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 ${
            errors.exam_percentage
              ? 'border-red-300 focus:ring-red-500'
              : 'border-gray-300 focus:ring-primary-500'
          }`}
          placeholder="e.g., 60"
          disabled={loading}
        />
        {errors.exam_percentage && (
          <p className="mt-1 text-sm text-red-600">{errors.exam_percentage}</p>
        )}
      </div>

      {/* Sum Display */}
      <div className={`p-3 rounded-lg border ${
        sumValid === null
          ? 'bg-gray-50 border-gray-200'
          : sumValid
          ? 'bg-green-50 border-green-200'
          : 'bg-red-50 border-red-200'
      }`}>
        <div className="flex justify-between items-center">
          <span className="text-sm font-medium text-gray-700">Total:</span>
          <span className={`text-lg font-bold ${
            sumValid === null
              ? 'text-gray-700'
              : sumValid
              ? 'text-green-700'
              : 'text-red-700'
          }`}>
            {sum.toFixed(2)}%
          </span>
        </div>
        {sumValid === false && (
          <p className="mt-1 text-sm text-red-600">Must equal 100%</p>
        )}
        {sumValid === true && (
          <p className="mt-1 text-sm text-green-600">✓ Valid</p>
        )}
      </div>

      {/* Sum Error */}
      {errors.sum && (
        <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
          {errors.sum}
        </div>
      )}

      {/* Submit Error */}
      {errors.submit && (
        <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
          {errors.submit}
        </div>
      )}

      {/* Buttons */}
      <div className="flex justify-end space-x-3 pt-2">
        <button
          type="button"
          onClick={onCancel}
          className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200"
          disabled={loading}
        >
          Cancel
        </button>
        <button
          type="submit"
          className="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
          disabled={loading}
        >
          {loading ? 'Saving...' : 'Save Weighting'}
        </button>
      </div>
    </form>
  );
};

export default WeightingForm;
