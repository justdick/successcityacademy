import { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import { useNavigate } from 'react-router-dom';
import {
  createBackup,
  listBackups,
  restoreBackup,
  downloadBackup,
  deleteBackup
} from '../services/backupService';
import DataTable from './DataTable';

const BackupManagement = () => {
  const [backups, setBackups] = useState([]);
  const [loading, setLoading] = useState(true);
  const [operationInProgress, setOperationInProgress] = useState(false);
  const [currentOperation, setCurrentOperation] = useState(null);
  const [error, setError] = useState(null);
  const [successMessage, setSuccessMessage] = useState(null);
  const { isAdmin } = useAuth();
  const navigate = useNavigate();

  // Redirect if not admin
  if (!isAdmin()) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-100">
        <div className="bg-white p-8 rounded-lg shadow-md">
          <h2 className="text-xl font-bold text-red-600 mb-4">Access Denied</h2>
          <p className="text-gray-700">You do not have permission to access this page.</p>
        </div>
      </div>
    );
  }

  useEffect(() => {
    loadBackups();
  }, []);

  const loadBackups = async () => {
    try {
      setLoading(true);
      setError(null);
      const data = await listBackups();
      // Sort backups by creation date (newest first)
      const sortedBackups = data.sort((a, b) => 
        new Date(b.created) - new Date(a.created)
      );
      setBackups(sortedBackups);
    } catch (err) {
      setError(err.message || 'Failed to load backups');
    } finally {
      setLoading(false);
    }
  };

  const formatDateTime = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit'
    });
  };

  const handleCreateBackup = async () => {
    try {
      setOperationInProgress(true);
      setCurrentOperation('creating');
      setError(null);
      setSuccessMessage(null);
      
      await createBackup();
      
      setSuccessMessage('Backup created successfully');
      // Auto-dismiss success message after 5 seconds
      setTimeout(() => setSuccessMessage(null), 5000);
      
      // Refresh backup list
      await loadBackups();
    } catch (err) {
      setError(err.message || 'Failed to create backup');
    } finally {
      setOperationInProgress(false);
      setCurrentOperation(null);
    }
  };

  const handleRestoreBackup = async (filename) => {
    const confirmed = window.confirm(
      `Are you sure you want to restore from "${filename}"?\n\n` +
      'This will replace all current data with the backup data. ' +
      'You will be logged out after the restore completes.'
    );

    if (!confirmed) {
      return;
    }

    try {
      setOperationInProgress(true);
      setCurrentOperation('restoring');
      setError(null);
      setSuccessMessage(null);
      
      await restoreBackup(filename);
      
      setSuccessMessage('Database restored successfully. Redirecting to login...');
      
      // Redirect to login after 2 seconds
      setTimeout(() => {
        navigate('/login');
      }, 2000);
    } catch (err) {
      setError(err.message || 'Failed to restore backup');
      setOperationInProgress(false);
      setCurrentOperation(null);
    }
  };

  const handleDownloadBackup = async (filename) => {
    try {
      setError(null);
      await downloadBackup(filename);
    } catch (err) {
      setError(err.message || 'Failed to download backup');
    }
  };

  const handleDeleteBackup = async (filename, isNewest) => {
    if (isNewest) {
      setError('Cannot delete the most recent backup');
      return;
    }

    const confirmed = window.confirm(
      `Are you sure you want to delete "${filename}"?\n\n` +
      'This action cannot be undone.'
    );

    if (!confirmed) {
      return;
    }

    try {
      setOperationInProgress(true);
      setCurrentOperation('deleting');
      setError(null);
      setSuccessMessage(null);
      
      await deleteBackup(filename);
      
      setSuccessMessage('Backup deleted successfully');
      // Auto-dismiss success message after 5 seconds
      setTimeout(() => setSuccessMessage(null), 5000);
      
      // Refresh backup list
      await loadBackups();
    } catch (err) {
      setError(err.message || 'Failed to delete backup');
    } finally {
      setOperationInProgress(false);
      setCurrentOperation(null);
    }
  };

  return (
    <div className="min-h-screen bg-gray-100 py-8">
      <div className="max-w-6xl mx-auto px-4">
        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex justify-between items-center mb-6">
            <h1 className="text-2xl font-bold text-gray-800">Database Backup & Restore</h1>
            <button
              onClick={handleCreateBackup}
              disabled={operationInProgress}
              className="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center"
            >
              {currentOperation === 'creating' ? (
                <>
                  <div className="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                  Creating...
                </>
              ) : (
                'Create Backup'
              )}
            </button>
          </div>

          {error && (
            <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
              {error}
            </div>
          )}

          {successMessage && (
            <div className="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
              {successMessage}
            </div>
          )}

          {operationInProgress && currentOperation && (
            <div className="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded mb-4 flex items-center">
              <div className="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-blue-700 mr-2"></div>
              <span>
                {currentOperation === 'creating' && 'Creating backup...'}
                {currentOperation === 'restoring' && 'Restoring database...'}
                {currentOperation === 'deleting' && 'Deleting backup...'}
              </span>
            </div>
          )}

          {loading ? (
            <div className="text-center py-8">
              <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
              <p className="mt-2 text-gray-600">Loading backups...</p>
            </div>
          ) : backups.length === 0 ? (
            <div className="text-center py-8 text-gray-500">
              No backups found. Click "Create Backup" to create your first backup.
            </div>
          ) : (
            <DataTable
              data={backups}
              columns={[
                {
                  title: 'Filename',
                  data: 'filename',
                  render: (row) => (
                    <span className="font-medium text-gray-900">{row.filename}</span>
                  )
                },
                {
                  title: 'Date & Time',
                  data: 'created',
                  render: (row) => formatDateTime(row.created)
                },
                {
                  title: 'Size',
                  data: 'sizeFormatted'
                },
                {
                  title: 'Actions',
                  data: null,
                  render: (row, index) => (
                    <div className="flex space-x-2">
                      <button
                        onClick={() => handleRestoreBackup(row.filename)}
                        disabled={operationInProgress}
                        className="text-blue-600 hover:text-blue-800 disabled:text-gray-400 disabled:cursor-not-allowed font-medium"
                      >
                        Restore
                      </button>
                      <button
                        onClick={() => handleDownloadBackup(row.filename)}
                        disabled={operationInProgress}
                        className="text-green-600 hover:text-green-800 disabled:text-gray-400 disabled:cursor-not-allowed font-medium"
                      >
                        Download
                      </button>
                      <button
                        onClick={() => handleDeleteBackup(row.filename, index === 0)}
                        disabled={operationInProgress || index === 0}
                        className="text-red-600 hover:text-red-800 disabled:text-gray-400 disabled:cursor-not-allowed font-medium"
                        title={index === 0 ? 'Cannot delete the most recent backup' : 'Delete backup'}
                      >
                        Delete
                      </button>
                    </div>
                  )
                }
              ]}
              tableId="backupsTable"
              options={{
                order: [[1, 'desc']], // Sort by date descending (newest first)
                pageLength: 5,
                lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'All']]
              }}
            />
          )}
        </div>
      </div>
    </div>
  );
};

export default BackupManagement;
