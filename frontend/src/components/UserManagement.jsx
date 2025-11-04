import { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import { getAllUsers } from '../services/api';
import UserForm from './UserForm';
import DataTable from './DataTable';

const UserManagement = () => {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [showForm, setShowForm] = useState(false);
  const { isAdmin } = useAuth();

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
    loadUsers();
  }, []);

  const loadUsers = async () => {
    try {
      setLoading(true);
      setError('');
      const data = await getAllUsers();
      setUsers(data);
    } catch (err) {
      setError(err.message || 'Failed to load users');
    } finally {
      setLoading(false);
    }
  };

  const handleUserCreated = () => {
    setShowForm(false);
    loadUsers();
  };

  return (
    <div className="min-h-screen bg-gray-100 py-8">
      <div className="max-w-6xl mx-auto px-4">
        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex justify-between items-center mb-6">
            <h1 className="text-2xl font-bold text-gray-800">User Management</h1>
            <button
              onClick={() => setShowForm(!showForm)}
              className="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors"
            >
              {showForm ? 'Cancel' : 'Add New User'}
            </button>
          </div>

          {error && (
            <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
              {error}
            </div>
          )}

          {showForm && (
            <div className="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
              <UserForm onSuccess={handleUserCreated} onCancel={() => setShowForm(false)} />
            </div>
          )}

          {loading ? (
            <div className="text-center py-8">
              <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
              <p className="mt-2 text-gray-600">Loading users...</p>
            </div>
          ) : users.length === 0 ? (
            <div className="text-center py-8 text-gray-500">
              No users found. Click "Add New User" to create one.
            </div>
          ) : (
            <DataTable
              data={users}
              columns={[
                {
                  title: 'ID',
                  data: 'id',
                },
                {
                  title: 'Username',
                  data: 'username',
                  render: (row) => (
                    <span className="font-medium">{row.username}</span>
                  ),
                },
                {
                  title: 'Full Name',
                  data: 'full_name',
                  render: (row) => row.full_name || '-',
                },
                {
                  title: 'Role',
                  data: 'role',
                  render: (row) => (
                    <span
                      className={`px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${
                        row.role === 'admin'
                          ? 'bg-secondary-100 text-secondary-800'
                          : 'bg-primary-100 text-primary-800'
                      }`}
                    >
                      {row.role}
                    </span>
                  ),
                },
                {
                  title: 'Created At',
                  data: 'created_at',
                  render: (row) => new Date(row.created_at).toLocaleString(),
                },
              ]}
              tableId="usersTable"
              options={{
                order: [[0, 'asc']],
                pageLength: 10,
              }}
            />
          )}
        </div>
      </div>
    </div>
  );
};

export default UserManagement;
