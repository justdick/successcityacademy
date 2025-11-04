import { useEffect, useRef } from 'react';

/**
 * Reusable DataTable Component
 * Wraps jQuery DataTables with React
 * 
 * @param {Object} props
 * @param {Array} props.data - Array of data objects
 * @param {Array} props.columns - Array of column definitions
 * @param {Object} props.options - Additional DataTables options
 * @param {string} props.tableId - Unique ID for the table
 */
const DataTable = ({ data, columns, options = {}, tableId = 'dataTable' }) => {
  const tableRef = useRef(null);
  const dataTableRef = useRef(null);
  const isInitialized = useRef(false);

  useEffect(() => {
    // Wait for jQuery and data
    if (!tableRef.current || !window.$ || data.length === 0) {
      return;
    }

    // Destroy existing DataTable if it exists
    if (dataTableRef.current) {
      try {
        dataTableRef.current.destroy(true); // true = remove from DOM completely
        dataTableRef.current = null;
        isInitialized.current = false;
      } catch (error) {
        console.warn('Error destroying DataTable:', error);
      }
    }

    // Small delay to ensure DOM is ready
    const timer = setTimeout(() => {
      if (!tableRef.current || !window.$) return;

      try {
        // Default options
        const defaultOptions = {
          responsive: true,
          pageLength: 5, // Changed to 5 as requested
          lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'All']],
          order: [[0, 'asc']],
          language: {
            search: 'Search:',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            infoEmpty: 'Showing 0 to 0 of 0 entries',
            infoFiltered: '(filtered from _MAX_ total entries)',
            zeroRecords: 'No matching records found',
            emptyTable: 'No data available in table',
            paginate: {
              first: 'First',
              last: 'Last',
              next: 'Next',
              previous: 'Previous'
            }
          },
          dom: '<"flex flex-col md:flex-row justify-between items-center mb-4"lf>rtip',
          destroy: true, // Allow reinitialization
          ...options
        };

        // Initialize DataTable
        dataTableRef.current = window.$(tableRef.current).DataTable(defaultOptions);
        isInitialized.current = true;
      } catch (error) {
        console.error('Error initializing DataTable:', error);
      }
    }, 50);

    // Cleanup
    return () => {
      clearTimeout(timer);
      if (dataTableRef.current && isInitialized.current) {
        try {
          dataTableRef.current.destroy(true);
          dataTableRef.current = null;
          isInitialized.current = false;
        } catch (error) {
          console.warn('Error in cleanup:', error);
        }
      }
    };
  }, [data, columns, options]);

  return (
    <div className="overflow-x-auto">
      <table
        ref={tableRef}
        id={tableId}
        className="min-w-full divide-y divide-gray-200 display responsive nowrap"
        style={{ width: '100%' }}
      >
        <thead className="bg-primary-50">
          <tr>
            {columns.map((column, index) => (
              <th
                key={index}
                className="px-6 py-3 text-left text-xs font-medium text-primary-900 uppercase tracking-wider"
              >
                {column.title}
              </th>
            ))}
          </tr>
        </thead>
        <tbody className="bg-white divide-y divide-gray-200">
          {data.map((row, rowIndex) => (
            <tr key={rowIndex} className="hover:bg-primary-50">
              {columns.map((column, colIndex) => (
                <td
                  key={colIndex}
                  className="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                >
                  {column.render ? column.render(row, rowIndex) : row[column.data]}
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};

export default DataTable;
