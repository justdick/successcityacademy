import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { getAllStudents, deleteStudent, getAllClassLevels } from '../services/api';
import { getMyAssignments } from '../services/teacherAssignmentService';
import { isAdmin } from '../services/auth';
import DataTable from './DataTable';

const StudentList = () => {
  const [students, setStudents] = useState([]);
  const [classLevels, setClassLevels] = useState([]);
  const [filteredStudents, setFilteredStudents] = useState([]);
  const [selectedClassLevel, setSelectedClassLevel] = useState('');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [myAssignments, setMyAssignments] = useState(null);
  const navigate = useNavigate();

  useEffect(() => {
    loadData();
  }, []);

  useEffect(() => {
    filterStudents();
  }, [students, selectedClassLevel]);

  const loadData = async () => {
    try {
      setLoading(true);
      setError('');
      
      // Check if user is admin
      const userIsAdmin = isAdmin();
      
      // Load assignments for teachers (not admins)
      let assignments = null;
      if (!userIsAdmin) {
        try {
          assignments = await getMyAssignments();
          setMyAssignments(assignments);
        } catch (err) {
          // If no assignments, set empty arrays
          setMyAssignments({ classes: [], subjects: [] });
        }
      }
      
      const [studentsData, classLevelsData] = await Promise.all([
        getAllStudents(),
        getAllClassLevels()
      ]);
      
      // Filter students based on teacher's accessible classes
      let filteredData = studentsData;
      if (!userIsAdmin && assignments) {
        const classIds = assignments.classes.map(c => c.id);
        filteredData = studentsData.filter(student => 
          classIds.includes(student.class_level_id)
        );
      }
      
      setStudents(filteredData);
      setClassLevels(classLevelsData);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const filterStudents = () => {
    if (!selectedClassLevel) {
      setFilteredStudents(students);
    } else {
      setFilteredStudents(
        students.filter(student => student.class_level_id === parseInt(selectedClassLevel))
      );
    }
  };

  const handleDelete = async (studentId, studentName) => {
    if (window.confirm(`Are you sure you want to delete student "${studentName}"? This will also delete all their assessments.`)) {
      try {
        await deleteStudent(studentId);
        setStudents(students.filter(s => s.student_id !== studentId));
        setError('');
      } catch (err) {
        setError(err.message);
      }
    }
  };

  const handleEdit = (studentId) => {
    navigate(`/students/edit/${studentId}`);
  };

  const handleAddStudent = () => {
    navigate('/students/add');
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="text-gray-600">Loading students...</div>
      </div>
    );
  }

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-2xl font-bold text-gray-800">Students</h2>
        <button
          onClick={handleAddStudent}
          className="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded transition"
        >
          Add Student
        </button>
      </div>

      {error && (
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
          {error}
        </div>
      )}

      {/* Show teacher's accessible classes indicator */}
      {!isAdmin() && myAssignments && myAssignments.classes.length > 0 && (
        <div className="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded mb-4">
          <p className="font-medium">Viewing students from your assigned classes:</p>
          <p className="text-sm mt-1">
            {myAssignments.classes.map(c => c.name).join(', ')}
          </p>
        </div>
      )}

      {/* Show message if teacher has no class assignments */}
      {!isAdmin() && myAssignments && myAssignments.classes.length === 0 && (
        <div className="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded mb-4">
          <p className="font-medium">No Class Assignments</p>
          <p className="text-sm mt-1">
            You have not been assigned to any classes yet. Please contact your administrator to assign you to classes.
          </p>
        </div>
      )}

      {/* Filter by Class Level */}
      <div className="mb-4">
        <label className="block text-sm font-medium text-gray-700 mb-2">
          Filter by Class Level
        </label>
        <select
          value={selectedClassLevel}
          onChange={(e) => setSelectedClassLevel(e.target.value)}
          className="w-full md:w-64 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary-500"
        >
          <option value="">All Class Levels</option>
          {classLevels.map(level => (
            <option key={level.id} value={level.id}>
              {level.name}
            </option>
          ))}
        </select>
      </div>

      {/* Students Table */}
      {filteredStudents.length === 0 ? (
        <div className="text-center py-8 text-gray-500">
          {!isAdmin() && myAssignments && myAssignments.classes.length === 0 
            ? 'No students to display. You need class assignments to view students.'
            : 'No students found. Click "Add Student" to create one.'}
        </div>
      ) : (
        <DataTable
          key={`students-${selectedClassLevel}-${filteredStudents.length}`}
          data={filteredStudents}
          columns={[
            {
              title: 'Student ID',
              data: 'student_id',
              render: (row) => (
                <span className="font-medium">{row.student_id}</span>
              )
            },
            {
              title: 'Name',
              data: 'name'
            },
            {
              title: 'Class Level',
              data: 'class_level_name'
            },
            {
              title: 'Actions',
              data: null,
              orderable: false,
              render: (row) => (
                <div className="space-x-2">
                  <button
                    onClick={() => handleEdit(row.student_id)}
                    className="text-green-600 hover:text-green-900"
                  >
                    Edit
                  </button>
                  <button
                    onClick={() => handleDelete(row.student_id, row.name)}
                    className="text-red-600 hover:text-red-900"
                  >
                    Delete
                  </button>
                </div>
              )
            }
          ]}
          options={{
            pageLength: 5,
            order: [[1, 'asc']], // Sort by name by default
          }}
          tableId="studentsTable"
        />
      )}
    </div>
  );
};

export default StudentList;
