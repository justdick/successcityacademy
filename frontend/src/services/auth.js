import axios from 'axios';

const API_URL = '/api';

/**
 * Login function that calls API and stores token
 * @param {string} username 
 * @param {string} password 
 * @returns {Promise<{token: string, user: object}>}
 */
export const login = async (username, password) => {
  const response = await axios.post(`${API_URL}/auth/login`, {
    username,
    password
  });

  if (response.data.success && response.data.data.token) {
    // Store JWT token in localStorage
    localStorage.setItem('token', response.data.data.token);
    localStorage.setItem('user', JSON.stringify(response.data.data.user));
    return response.data.data;
  }

  throw new Error(response.data.error || 'Login failed');
};

/**
 * Logout function that clears token
 */
export const logout = () => {
  localStorage.removeItem('token');
  localStorage.removeItem('user');
};

/**
 * Get current user from token stored in localStorage
 * @returns {object|null} User object or null if not authenticated
 */
export const getCurrentUser = () => {
  const userStr = localStorage.getItem('user');
  if (userStr) {
    try {
      return JSON.parse(userStr);
    } catch (e) {
      return null;
    }
  }
  return null;
};

/**
 * Get stored JWT token
 * @returns {string|null}
 */
export const getToken = () => {
  return localStorage.getItem('token');
};

/**
 * Check if user is authenticated
 * @returns {boolean}
 */
export const isAuthenticated = () => {
  return !!getToken();
};

/**
 * Check if current user is admin
 * @returns {boolean}
 */
export const isAdmin = () => {
  const user = getCurrentUser();
  return user && user.role === 'admin';
};
