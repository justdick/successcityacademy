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
 * Get all terms
 * @returns {Promise<Array>} List of terms
 */
export const getAllTerms = async () => {
  try {
    const response = await apiClient.get('/terms');
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Get active terms only
 * @returns {Promise<Array>} List of active terms
 */
export const getActiveTerms = async () => {
  try {
    const response = await apiClient.get('/terms/active');
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Get a specific term by ID
 * @param {number} id - Term ID
 * @returns {Promise<object>} Term data
 */
export const getTerm = async (id) => {
  try {
    const response = await apiClient.get(`/terms/${id}`);
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Create a new term (admin only)
 * @param {object} termData - {name, academic_year, start_date, end_date, is_active}
 * @returns {Promise<object>} Created term
 */
export const createTerm = async (termData) => {
  try {
    const response = await apiClient.post('/terms', termData);
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Update a term (admin only)
 * @param {number} id - Term ID
 * @param {object} termData - {name, academic_year, start_date, end_date, is_active}
 * @returns {Promise<object>} Updated term
 */
export const updateTerm = async (id, termData) => {
  try {
    const response = await apiClient.put(`/terms/${id}`, termData);
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Delete a term (admin only)
 * @param {number} id - Term ID
 * @returns {Promise<void>}
 */
export const deleteTerm = async (id) => {
  try {
    await apiClient.delete(`/terms/${id}`);
  } catch (error) {
    throw new Error(handleError(error));
  }
};

export default {
  getAllTerms,
  getActiveTerms,
  getTerm,
  createTerm,
  updateTerm,
  deleteTerm
};
