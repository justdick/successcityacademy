import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useState } from 'react';

const Layout = ({ children }) => {
  const { user, logout, isAdmin } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const [showAssessmentMenu, setShowAssessmentMenu] = useState(false);
  const [showReportsMenu, setShowReportsMenu] = useState(false);
  const [showAdminMenu, setShowAdminMenu] = useState(false);

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  const isActive = (path) => {
    return location.pathname === path || location.pathname.startsWith(path + '/');
  };

  const getLinkClass = (path) => {
    const baseClass = "px-4 py-2 rounded-lg transition duration-200 font-medium";
    return isActive(path) 
      ? `${baseClass} bg-secondary-400 text-primary-900` 
      : `${baseClass} hover:bg-primary-600`;
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
      {/* Navigation Bar */}
      <nav className="bg-primary-700 text-white shadow-xl">
        <div className="container mx-auto px-4">
          <div className="flex justify-between items-center py-4">
            <div className="flex items-center space-x-6">
              <div className="flex items-center space-x-3">
                <img 
                  src="/assets/sca.jpg" 
                  alt="Success City Academy Logo" 
                  className="h-10 w-10 object-contain"
                />
                <h1 className="text-xl font-bold tracking-wide">Success City Academy</h1>
              </div>
              
              {/* Main Navigation Links */}
              <div className="flex space-x-2">
                {!isAdmin() && (
                  <Link 
                    to="/my-assignments" 
                    className={getLinkClass('/my-assignments')}
                  >
                    My Assignments
                  </Link>
                )}
                <Link 
                  to="/students" 
                  className={getLinkClass('/students')}
                >
                  Students
                </Link>
                
                {/* Assessment Dropdown Menu */}
                <div 
                  className="relative"
                  onMouseEnter={() => setShowAssessmentMenu(true)}
                  onMouseLeave={() => setShowAssessmentMenu(false)}
                >
                  <button 
                    className={`${getLinkClass('/assessments')} flex items-center space-x-1`}
                  >
                    <span>Assessments</span>
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>
                  {showAssessmentMenu && (
                    <div className="absolute top-full left-0 mt-1 bg-white text-gray-800 rounded-lg shadow-xl py-2 min-w-[200px] z-50">
                      <Link 
                        to="/assessments/entry" 
                        className="block px-4 py-2 hover:bg-primary-50 transition duration-200"
                      >
                        Entry Form
                      </Link>
                      <Link 
                        to="/assessments/grid" 
                        className="block px-4 py-2 hover:bg-primary-50 transition duration-200"
                      >
                        Grid Entry
                      </Link>
                      <Link 
                        to="/assessments/summary" 
                        className="block px-4 py-2 hover:bg-primary-50 transition duration-200"
                      >
                        Summary Dashboard
                      </Link>
                    </div>
                  )}
                </div>
                
                {/* Reports Dropdown Menu */}
                <div 
                  className="relative"
                  onMouseEnter={() => setShowReportsMenu(true)}
                  onMouseLeave={() => setShowReportsMenu(false)}
                >
                  <button 
                    className={`${getLinkClass('/reports')} flex items-center space-x-1`}
                  >
                    <span>Reports</span>
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>
                  {showReportsMenu && (
                    <div className="absolute top-full left-0 mt-1 bg-white text-gray-800 rounded-lg shadow-xl py-2 min-w-[200px] z-50">
                      <Link 
                        to="/reports/student" 
                        className="block px-4 py-2 hover:bg-primary-50 transition duration-200"
                      >
                        Student Report
                      </Link>
                      <Link 
                        to="/reports/class" 
                        className="block px-4 py-2 hover:bg-primary-50 transition duration-200"
                      >
                        Class Reports
                      </Link>
                    </div>
                  )}
                </div>
                
                {/* Admin Dropdown Menu */}
                {isAdmin() && (
                  <div 
                    className="relative"
                    onMouseEnter={() => setShowAdminMenu(true)}
                    onMouseLeave={() => setShowAdminMenu(false)}
                  >
                    <button 
                      className={`${getLinkClass('/admin')} flex items-center space-x-1`}
                    >
                      <span>Admin</span>
                      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                      </svg>
                    </button>
                    {showAdminMenu && (
                      <div className="absolute top-full left-0 mt-1 bg-white text-gray-800 rounded-lg shadow-xl py-2 min-w-[200px] z-50">
                        <div className="px-4 py-2 text-xs font-semibold text-gray-500 uppercase">System</div>
                        <Link 
                          to="/users" 
                          className="block px-4 py-2 hover:bg-primary-50 transition duration-200"
                        >
                          Users
                        </Link>
                        <Link 
                          to="/subjects" 
                          className="block px-4 py-2 hover:bg-primary-50 transition duration-200"
                        >
                          Subjects
                        </Link>
                        <Link 
                          to="/class-levels" 
                          className="block px-4 py-2 hover:bg-primary-50 transition duration-200"
                        >
                          Class Levels
                        </Link>
                        <div className="border-t border-gray-200 my-2"></div>
                        <div className="px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Term Config</div>
                        <Link 
                          to="/terms" 
                          className="block px-4 py-2 hover:bg-primary-50 transition duration-200"
                        >
                          Terms
                        </Link>
                        <Link 
                          to="/subject-weightings" 
                          className="block px-4 py-2 hover:bg-primary-50 transition duration-200"
                        >
                          Subject Weightings
                        </Link>
                        <div className="border-t border-gray-200 my-2"></div>
                        <div className="px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Access Control</div>
                        <Link 
                          to="/teacher-assignments" 
                          className="block px-4 py-2 hover:bg-primary-50 transition duration-200"
                        >
                          Teacher Assignments
                        </Link>
                        <div className="border-t border-gray-200 my-2"></div>
                        <div className="px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Database</div>
                        <Link 
                          to="/backup-management" 
                          className="block px-4 py-2 hover:bg-primary-50 transition duration-200"
                        >
                          Backup & Restore
                        </Link>
                      </div>
                    )}
                  </div>
                )}
              </div>
            </div>

            {/* User Info and Logout */}
            <div className="flex items-center space-x-4">
              <span className="text-sm">
                Welcome, <span className="font-semibold">{user?.username}</span>
                {isAdmin() && <span className="ml-2 text-xs bg-white/20 px-3 py-1 rounded-full font-medium">Admin</span>}
              </span>
              <button
                onClick={handleLogout}
                className="bg-rose-500 hover:bg-rose-600 px-5 py-2 rounded-lg transition duration-200 font-medium shadow-lg hover:shadow-xl"
              >
                Logout
              </button>
            </div>
          </div>
        </div>
      </nav>

      {/* Main Content */}
      <main className="container mx-auto px-4 py-8">
        {children}
      </main>
    </div>
  );
};

export default Layout;
