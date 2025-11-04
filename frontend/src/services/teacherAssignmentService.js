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
 */
const handleError = (error) => {
  if (error.response && error.response.data && error.response.data.error) {
    return error.response.data.error;
  }
  return error.message || 'An error occurred';
};

// ============================================
// TEACHER ASSIGNMENT ENDPOINTS
// ============================================

/**
 * Get all teacher assignments
 * @param {object} filters - Optional filters {user_id, class_level_id, subject_id}
 * @returns {Promise<Array>} List of teacher assignments
 */
export const getAllTeacherAssignments = async (filters = {}) => {
  try {
    const params = new URLSearchParams();
    if (filters.user_id) params.append('user_id', filters.user_id);
    if (filters.class_level_id) params.append('class_level_id', filters.class_level_id);
    if (filters.subject_id) params.append('subject_id', filters.subject_id);
    
    const response = await apiClient.get(`/teacher-assignments?${params.toString()}`);
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Get current teacher's assignments
 * @returns {Promise<object>} Teacher's assignments {classes, subjects}
 */
export const getMyAssignments = async () => {
  try {
    const response = await apiClient.get('/teacher-assignments/my-assignments');
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Assign a teacher to a class
 * @param {object} assignmentData - {user_id, class_level_id}
 * @returns {Promise<object>} Created assignment
 */
export const assignTeacherToClass = async (assignmentData) => {
  try {
    const response = await apiClient.post('/teacher-assignments/class', assignmentData);
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Assign a teacher to a subject
 * @param {object} assignmentData - {user_id, subject_id}
 * @returns {Promise<object>} Created assignment
 */
export const assignTeacherToSubject = async (assignmentData) => {
  try {
    const response = await apiClient.post('/teacher-assignments/subject', assignmentData);
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Bulk assign classes and subjects to a teacher
 * @param {object} assignmentData - {user_id, class_level_ids, subject_ids}
 * @returns {Promise<object>} Assignment counts {classes_assigned, subjects_assigned}
 */
export const bulkAssignTeacher = async (assignmentData) => {
  try {
    const response = await apiClient.post('/teacher-assignments/bulk', assignmentData);
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Remove a class assignment
 * @param {number} assignmentId - Assignment ID
 * @returns {Promise<void>}
 */
export const removeClassAssignment = async (assignmentId) => {
  try {
    await apiClient.delete(`/teacher-assignments/class/${assignmentId}`);
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Remove a subject assignment
 * @param {number} assignmentId - Assignment ID
 * @returns {Promise<void>}
 */
export const removeSubjectAssignment = async (assignmentId) => {
  try {
    await apiClient.delete(`/teacher-assignments/subject/${assignmentId}`);
  } catch (error) {
    throw new Error(handleError(error));
  }
};

export default {
  getAllTeacherAssignments,
  getMyAssignments,
  assignTeacherToClass,
  assignTeacherToSubject,
  bulkAssignTeacher,
  removeClassAssignment,
  removeSubjectAssignment
};
