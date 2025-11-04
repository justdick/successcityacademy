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
 * Get term report for a specific student
 * @param {string} studentId - Student ID
 * @param {number} termId - Term ID
 * @returns {Promise<object>} Student term report with assessments and average
 */
export const getStudentReport = async (studentId, termId) => {
  try {
    const response = await apiClient.get(`/reports/student/${studentId}/term/${termId}`);
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Get term reports for all students in a class
 * @param {number} classLevelId - Class level ID
 * @param {number} termId - Term ID
 * @returns {Promise<object>} Class term reports with all student reports
 */
export const getClassReports = async (classLevelId, termId) => {
  try {
    const response = await apiClient.get(`/reports/class/${classLevelId}/term/${termId}`);
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Download PDF report for a single student
 * @param {string} studentId - Student ID
 * @param {number} termId - Term ID
 * @returns {Promise<Blob>} PDF file as blob
 */
export const downloadStudentPDF = async (studentId, termId) => {
  try {
    const token = getToken();
    const response = await axios.get(
      `${API_URL}/reports/pdf/student/${studentId}/term/${termId}`,
      {
        headers: {
          Authorization: `Bearer ${token}`
        },
        responseType: 'blob'
      }
    );
    return response.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Download batch PDF reports for a class
 * @param {number} classLevelId - Class level ID
 * @param {number} termId - Term ID
 * @returns {Promise<Blob>} PDF file(s) as blob
 */
export const downloadClassPDFs = async (classLevelId, termId) => {
  try {
    const token = getToken();
    const response = await axios.post(
      `${API_URL}/reports/pdf/class/${classLevelId}/term/${termId}`,
      {},
      {
        headers: {
          Authorization: `Bearer ${token}`
        },
        responseType: 'blob'
      }
    );
    return response.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Trigger download of a blob as a file
 * @param {Blob} blob - File blob
 * @param {string} filename - Filename for download
 */
export const triggerDownload = (blob, filename) => {
  const url = window.URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = filename;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  window.URL.revokeObjectURL(url);
};

/**
 * Calculate term average from assessments
 * @param {Array} assessments - Array of assessment objects with final_mark
 * @returns {number} Average mark rounded to 2 decimal places
 */
export const calculateTermAverage = (assessments) => {
  if (!assessments || assessments.length === 0) {
    return 0;
  }
  
  const total = assessments.reduce((sum, assessment) => {
    return sum + (parseFloat(assessment.final_mark) || 0);
  }, 0);
  
  return parseFloat((total / assessments.length).toFixed(2));
};

export default {
  getStudentReport,
  getClassReports,
  downloadStudentPDF,
  downloadClassPDFs,
  triggerDownload,
  calculateTermAverage
};
