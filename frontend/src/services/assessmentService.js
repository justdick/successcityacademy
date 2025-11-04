import axios from 'axios';
import { getToken } from './auth';

const API_URL = '/api';

/**
 * Create axios instance with default config
 */
const apiClient = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json'
  }
});

/**
 * Add JWT token to all authenticated requests
 */
apiClient.interceptors.request.use(
  (config) => {
    const token = getToken();
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

/**
 * Handle API errors and extract error message
 * @param {Error} error 
 * @returns {string} Error message
 */
const handleError = (error) => {
  if (error.response && error.response.data && error.response.data.error) {
    return error.response.data.error;
  }
  return error.message || 'An error occurred';
};

/**
 * Create or update an assessment
 * @param {object} assessmentData - {student_id, subject_id, term_id, ca_mark, exam_mark}
 * @returns {Promise<object>} Created/updated assessment
 */
export const createAssessment = async (assessmentData) => {
  try {
    const response = await apiClient.post('/assessments', assessmentData);
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Update an existing assessment
 * @param {number} id - Assessment ID
 * @param {object} assessmentData - {ca_mark, exam_mark}
 * @returns {Promise<object>} Updated assessment
 */
export const updateAssessment = async (id, assessmentData) => {
  try {
    const response = await apiClient.put(`/assessments/${id}`, assessmentData);
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Get all assessments for a student in a specific term
 * @param {string} studentId - Student ID
 * @param {number} termId - Term ID
 * @returns {Promise<Array>} List of assessments
 */
export const getStudentTermAssessments = async (studentId, termId) => {
  try {
    const response = await apiClient.get(`/assessments/student/${studentId}/term/${termId}`);
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Get all assessments for a class in a specific term
 * @param {number} termId - Term ID
 * @param {number} classLevelId - Class level ID
 * @returns {Promise<Array>} List of assessments
 */
export const getClassTermAssessments = async (termId, classLevelId) => {
  try {
    const response = await apiClient.get(`/assessments/term/${termId}/class/${classLevelId}`);
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Delete an assessment
 * @param {number} id - Assessment ID
 * @returns {Promise<void>}
 */
export const deleteAssessment = async (id) => {
  try {
    await apiClient.delete(`/assessments/${id}`);
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Validate CA mark against subject weighting
 * @param {number} mark - CA mark value
 * @param {number} maxMark - Maximum allowed CA mark (from weighting)
 * @returns {boolean} True if valid
 */
export const validateCAMark = (mark, maxMark) => {
  const value = parseFloat(mark);
  if (isNaN(value)) return false;
  return value >= 0 && value <= maxMark;
};

/**
 * Validate exam mark against subject weighting
 * @param {number} mark - Exam mark value
 * @param {number} maxMark - Maximum allowed exam mark (from weighting)
 * @returns {boolean} True if valid
 */
export const validateExamMark = (mark, maxMark) => {
  const value = parseFloat(mark);
  if (isNaN(value)) return false;
  return value >= 0 && value <= maxMark;
};

/**
 * Calculate final mark from CA and exam marks
 * @param {number} caMark - CA mark (can be null)
 * @param {number} examMark - Exam mark (can be null)
 * @returns {number} Final mark (sum of CA and exam)
 */
export const calculateFinalMark = (caMark, examMark) => {
  const ca = parseFloat(caMark) || 0;
  const exam = parseFloat(examMark) || 0;
  return ca + exam;
};

/**
 * Validate mark value is a valid number
 * @param {any} value - Value to validate
 * @returns {boolean} True if valid number
 */
export const isValidNumber = (value) => {
  if (value === null || value === undefined || value === '') return true; // Allow empty
  const num = parseFloat(value);
  return !isNaN(num) && num >= 0;
};

export default {
  createAssessment,
  updateAssessment,
  getStudentTermAssessments,
  getClassTermAssessments,
  deleteAssessment,
  validateCAMark,
  validateExamMark,
  calculateFinalMark,
  isValidNumber
};

/**
 * Get assessment summary for a term and class
 * Shows completion status for all students and subjects
 * @param {number} termId - Term ID
 * @param {number} classLevelId - Class level ID
 * @returns {Promise<object>} Assessment summary with grid data
 */
export const getAssessmentSummary = async (termId, classLevelId) => {
  try {
    const response = await apiClient.get(`/reports/summary/term/${termId}/class/${classLevelId}`);
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Calculate status for a subject assessment
 * @param {boolean} hasCa - Has CA mark
 * @param {boolean} hasExam - Has exam mark
 * @returns {string} Status: 'complete', 'partial', or 'missing'
 */
export const calculateAssessmentStatus = (hasCa, hasExam) => {
  if (hasCa && hasExam) {
    return 'complete';
  } else if (hasCa || hasExam) {
    return 'partial';
  }
  return 'missing';
};

/**
 * Get status color class for Tailwind CSS
 * @param {string} status - Status: 'complete', 'partial', or 'missing'
 * @returns {string} Tailwind CSS color classes
 */
export const getStatusColorClass = (status) => {
  switch (status) {
    case 'complete':
      return 'bg-primary-100 border-primary-300 text-primary-800';
    case 'partial':
      return 'bg-secondary-100 border-secondary-300 text-secondary-800';
    case 'missing':
      return 'bg-red-100 border-red-300 text-red-800';
    default:
      return 'bg-gray-100 border-gray-300 text-gray-800';
  }
};

/**
 * Get status icon for display
 * @param {string} status - Status: 'complete', 'partial', or 'missing'
 * @returns {string} Status icon/symbol
 */
export const getStatusIcon = (status) => {
  switch (status) {
    case 'complete':
      return '✓';
    case 'partial':
      return '◐';
    case 'missing':
      return '✗';
    default:
      return '?';
  }
};
