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
 * Create a new database backup (admin only)
 * @returns {Promise<object>} Backup file details {filename, size, sizeFormatted}
 */
export const createBackup = async () => {
  try {
    const response = await apiClient.post('/backups');
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Get list of all backup files (admin only)
 * @returns {Promise<Array>} List of backup files with metadata
 */
export const listBackups = async () => {
  try {
    const response = await apiClient.get('/backups');
    return response.data.data.backups;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Restore database from a backup file (admin only)
 * @param {string} filename - Backup filename to restore from
 * @returns {Promise<object>} Restore operation result
 */
export const restoreBackup = async (filename) => {
  try {
    const response = await apiClient.post('/backups/restore', { filename });
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Download a backup file (admin only)
 * @param {string} filename - Backup filename to download
 * @returns {Promise<void>}
 */
export const downloadBackup = async (filename) => {
  try {
    const token = getToken();
    const response = await axios.get(`${API_URL}/backups/download/${filename}`, {
      headers: {
        Authorization: `Bearer ${token}`
      },
      responseType: 'blob'
    });

    // Create a blob URL and trigger download
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Delete a backup file (admin only)
 * @param {string} filename - Backup filename to delete
 * @returns {Promise<void>}
 */
export const deleteBackup = async (filename) => {
  try {
    await apiClient.delete(`/backups/${filename}`);
  } catch (error) {
    throw new Error(handleError(error));
  }
};

export default {
  createBackup,
  listBackups,
  restoreBackup,
  downloadBackup,
  deleteBackup
};
