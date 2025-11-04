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
 * Get all subject weightings
 * @returns {Promise<Array>} List of subject weightings
 */
export const getWeightings = async () => {
  try {
    const response = await apiClient.get('/subject-weightings');
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Get weighting for a specific subject
 * @param {number} subjectId - Subject ID
 * @returns {Promise<object>} Subject weighting data
 */
export const getWeighting = async (subjectId) => {
  try {
    const response = await apiClient.get(`/subject-weightings/${subjectId}`);
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Update subject weighting (admin only)
 * @param {number} subjectId - Subject ID
 * @param {object} weightingData - {ca_percentage, exam_percentage}
 * @returns {Promise<object>} Updated weighting
 */
export const updateWeighting = async (subjectId, weightingData) => {
  try {
    const response = await apiClient.put(`/subject-weightings/${subjectId}`, weightingData);
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Create or update subject weighting (admin only)
 * @param {object} weightingData - {subject_id, ca_percentage, exam_percentage}
 * @returns {Promise<object>} Created/updated weighting
 */
export const createOrUpdateWeighting = async (weightingData) => {
  try {
    const response = await apiClient.post('/subject-weightings', weightingData);
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Validate that CA and exam percentages sum to 100
 * @param {number} caPercentage - CA percentage
 * @param {number} examPercentage - Exam percentage
 * @returns {boolean} True if valid
 */
export const validatePercentageSum = (caPercentage, examPercentage) => {
  const ca = parseFloat(caPercentage) || 0;
  const exam = parseFloat(examPercentage) || 0;
  return Math.abs(ca + exam - 100) < 0.01; // Allow for floating point precision
};

/**
 * Validate individual percentage value
 * @param {number} percentage - Percentage value
 * @returns {boolean} True if valid (0-100)
 */
export const validatePercentage = (percentage) => {
  const value = parseFloat(percentage);
  return !isNaN(value) && value >= 0 && value <= 100;
};

export default {
  getWeightings,
  getWeighting,
  updateWeighting,
  createOrUpdateWeighting,
  validatePercentageSum,
  validatePercentage
};
