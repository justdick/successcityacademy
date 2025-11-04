import axios from 'axios';
import { getToken, logout } from './auth';

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
 * Handle 401 responses by logging out user
 */
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      // Token expired or invalid - logout user
      logout();
      window.location.href = '/login';
    }
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

// ============================================
// AUTH ENDPOINTS
// ============================================

/**
 * Login with username and password
 * @param {string} username 
 * @param {string} password 
 * @returns {Promise<{token: string, user: object}>}
 */
export const loginApi = async (username, password) => {
  try {
    const response = await apiClient.post('/auth/login', {
      username,
      password
    });
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Logout
 * @returns {Promise<void>}
 */
export const logoutApi = async () => {
  try {
    await apiClient.post('/auth/logout');
  } catch (error) {
    // Ignore logout errors
    console.error('Logout error:', error);
  }
};

// ============================================
// USER ENDPOINTS (Admin only)
// ============================================

/**
 * Create a new user (admin only)
 * @param {object} userData - {username, password, role}
 * @returns {Promise<object>} Created user
 */
export const createUser = async (userData) => {
  try {
    const response = await apiClient.post('/users', userData);
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Get all users (admin only)
 * @returns {Promise<Array>} List of users
 */
export const getAllUsers = async () => {
  try {
    const response = await apiClient.get('/users');
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

// ============================================
// SUBJECT ENDPOINTS
// ============================================

/**
 * Get all subjects
 * @returns {Promise<Array>} List of subjects
 */
export const getAllSubjects = async () => {
  try {
    const response = await apiClient.get('/subjects');
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Create a new subject (admin only)
 * @param {object} subjectData - {name}
 * @returns {Promise<object>} Created subject
 */
export const createSubject = async (subjectData) => {
  try {
    const response = await apiClient.post('/subjects', subjectData);
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Delete a subject (admin only)
 * @param {number} id - Subject ID
 * @returns {Promise<void>}
 */
export const deleteSubject = async (id) => {
  try {
    await apiClient.delete(`/subjects/${id}`);
  } catch (error) {
    throw new Error(handleError(error));
  }
};

// ============================================
// CLASS LEVEL ENDPOINTS
// ============================================

/**
 * Get all class levels
 * @returns {Promise<Array>} List of class levels
 */
export const getAllClassLevels = async () => {
  try {
    const response = await apiClient.get('/class-levels');
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Create a new class level (admin only)
 * @param {object} classLevelData - {name}
 * @returns {Promise<object>} Created class level
 */
export const createClassLevel = async (classLevelData) => {
  try {
    const response = await apiClient.post('/class-levels', classLevelData);
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Delete a class level (admin only)
 * @param {number} id - Class level ID
 * @returns {Promise<void>}
 */
export const deleteClassLevel = async (id) => {
  try {
    await apiClient.delete(`/class-levels/${id}`);
  } catch (error) {
    throw new Error(handleError(error));
  }
};

// ============================================
// STUDENT ENDPOINTS
// ============================================

/**
 * Create a new student
 * @param {object} studentData - {student_id, name, class_level_id}
 * @returns {Promise<object>} Created student
 */
export const createStudent = async (studentData) => {
  try {
    const response = await apiClient.post('/students', studentData);
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Get all students
 * @returns {Promise<Array>} List of students
 */
export const getAllStudents = async () => {
  try {
    const response = await apiClient.get('/students');
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Get a specific student by student_id
 * @param {string} studentId - Student ID
 * @returns {Promise<object>} Student data
 */
export const getStudent = async (studentId) => {
  try {
    const response = await apiClient.get(`/students/${studentId}`);
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Update student information
 * @param {string} studentId - Student ID
 * @param {object} studentData - {name, class_level_id}
 * @returns {Promise<object>} Updated student
 */
export const updateStudent = async (studentId, studentData) => {
  try {
    const response = await apiClient.put(`/students/${studentId}`, studentData);
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Delete a student
 * @param {string} studentId - Student ID
 * @returns {Promise<void>}
 */
export const deleteStudent = async (studentId) => {
  try {
    await apiClient.delete(`/students/${studentId}`);
  } catch (error) {
    throw new Error(handleError(error));
  }
};

// ============================================
// GRADE ENDPOINTS
// ============================================

/**
 * Add a grade for a student
 * @param {object} gradeData - {student_id, subject_id, mark}
 * @returns {Promise<object>} Created grade
 */
export const addGrade = async (gradeData) => {
  try {
    const response = await apiClient.post('/grades', gradeData);
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

/**
 * Get all grades for a specific student
 * @param {string} studentId - Student ID
 * @returns {Promise<object>} Student with grades {student, grades}
 */
export const getStudentGrades = async (studentId) => {
  try {
    const response = await apiClient.get(`/students/${studentId}/grades`);
    return response.data.data;
  } catch (error) {
    throw new Error(handleError(error));
  }
};

export default {
  // Auth
  loginApi,
  logoutApi,
  // Users
  createUser,
  getAllUsers,
  // Subjects
  getAllSubjects,
  createSubject,
  deleteSubject,
  // Class Levels
  getAllClassLevels,
  createClassLevel,
  deleteClassLevel,
  // Students
  createStudent,
  getAllStudents,
  getStudent,
  updateStudent,
  deleteStudent,
  // Grades
  addGrade,
  getStudentGrades
};
