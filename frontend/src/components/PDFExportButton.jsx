import { useState } from 'react';

/**
 * PDFExportButton Component
 * 
 * Reusable button component for triggering PDF generation with:
 * - Loading state during generation
 * - Automatic download on success
 * - Error handling and display
 * - Support for both single and batch export modes
 * 
 * @param {Object} props - Component props
 * @param {Function} props.onExport - Function to call for PDF export
 * @param {boolean} props.disabled - Whether button is disabled
 * @param {string} props.label - Button label text
 * @param {string} props.loadingLabel - Label text during loading
 * @param {string} props.className - Additional CSS classes
 * @param {string} props.variant - Button variant ('primary' or 'secondary')
 * @param {string} props.size - Button size ('small', 'medium', 'large')
 * @param {boolean} props.fullWidth - Whether button should be full width
 */
const PDFExportButton = ({
  onExport,
  disabled = false,
  label = 'Export to PDF',
  loadingLabel = 'Generating PDF...',
  className = '',
  variant = 'primary', // 'primary' or 'secondary'
  size = 'medium', // 'small', 'medium', 'large'
  fullWidth = false
}) => {
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(false);

  const handleClick = async () => {
    try {
      setIsLoading(true);
      setError('');
      setSuccess(false);

      // Call the export function passed as prop
      await onExport();

      // Show success state briefly
      setSuccess(true);
      setTimeout(() => setSuccess(false), 2000);
    } catch (err) {
      setError(err.message || 'Failed to generate PDF');
      // Clear error after 5 seconds
      setTimeout(() => setError(''), 5000);
    } finally {
      setIsLoading(false);
    }
  };

  // Determine button styles based on variant and size
  const getButtonClasses = () => {
    const baseClasses = 'rounded-lg transition duration-200 font-medium focus:outline-none focus:ring-2 focus:ring-offset-2';
    
    // Variant styles
    const variantClasses = {
      primary: 'bg-green-600 hover:bg-green-700 focus:ring-green-500 text-white',
      secondary: 'bg-secondary-500 hover:bg-secondary-600 focus:ring-secondary-500 text-white'
    };

    // Size styles
    const sizeClasses = {
      small: 'px-3 py-1.5 text-sm',
      medium: 'px-6 py-2 text-base',
      large: 'px-8 py-3 text-lg'
    };

    // Disabled styles
    const disabledClasses = 'disabled:bg-gray-400 disabled:cursor-not-allowed disabled:hover:bg-gray-400';

    // Width classes
    const widthClasses = fullWidth ? 'w-full' : '';

    // Success state
    const successClasses = success ? 'bg-green-800 hover:bg-green-800' : '';

    return `${baseClasses} ${variantClasses[variant]} ${sizeClasses[size]} ${disabledClasses} ${widthClasses} ${successClasses} ${className}`;
  };

  // Determine button content
  const getButtonContent = () => {
    if (isLoading) {
      return (
        <span className="flex items-center justify-center">
          <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {loadingLabel}
        </span>
      );
    }

    if (success) {
      return (
        <span className="flex items-center justify-center">
          <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path>
          </svg>
          Downloaded!
        </span>
      );
    }

    return (
      <span className="flex items-center justify-center">
        <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        {label}
      </span>
    );
  };

  return (
    <div className={fullWidth ? 'w-full' : ''}>
      <button
        onClick={handleClick}
        disabled={disabled || isLoading}
        className={getButtonClasses()}
        type="button"
      >
        {getButtonContent()}
      </button>

      {/* Error Message */}
      {error && (
        <div className="mt-2 text-sm text-red-600 bg-red-50 border border-red-200 rounded px-3 py-2">
          <span className="flex items-center">
            <svg className="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
              <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd"></path>
            </svg>
            {error}
          </span>
        </div>
      )}
    </div>
  );
};

export default PDFExportButton;
