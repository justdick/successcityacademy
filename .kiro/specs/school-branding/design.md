# Design Document: School Branding

## Overview

This design implements Success City Academy's branding throughout the student management system by replacing the current indigo/blue color scheme with the school's official green and yellow colors, and integrating the school logo across all interfaces.

The implementation focuses on:
- Centralized color configuration using Tailwind CSS
- Logo integration in navigation and reports
- Consistent application of brand colors across all components
- Maintaining accessibility and readability standards

## Architecture

### Color System

Based on the Success City Academy logo, we'll implement the following color palette:

**Primary (Green):**
- `primary-50`: #f0fdf4 (very light green - backgrounds)
- `primary-100`: #dcfce7 (light green - hover states)
- `primary-200`: #bbf7d0 (lighter green - borders)
- `primary-300`: #86efac (medium light)
- `primary-400`: #4ade80 (medium)
- `primary-500`: #22c55e (main green - primary actions)
- `primary-600`: #16a34a (darker green - hover on primary)
- `primary-700`: #15803d (dark green - active states)
- `primary-800`: #166534 (very dark)
- `primary-900`: #14532d (darkest)

**Secondary (Yellow/Gold):**
- `secondary-50`: #fefce8 (very light yellow)
- `secondary-100`: #fef9c3 (light yellow)
- `secondary-200`: #fef08a (lighter yellow)
- `secondary-300`: #fde047 (medium light)
- `secondary-400`: #facc15 (medium)
- `secondary-500`: #eab308 (main yellow - accents)
- `secondary-600`: #ca8a04 (darker yellow)
- `secondary-700`: #a16207 (dark yellow)
- `secondary-800`: #854d0e (very dark)
- `secondary-900`: #713f12 (darkest)

### Component Mapping

Current indigo/blue colors will be replaced as follows:
- `indigo-600` → `primary-600` (buttons, links)
- `indigo-700` → `primary-700` (hover states)
- `indigo-500` → `primary-500` (focus rings)
- `blue-*` → `primary-*` (informational elements)

Yellow will be used for:
- Active navigation items
- Success states and badges
- Accent highlights
- Secondary buttons

## Components and Interfaces

### 1. Tailwind Configuration

**File:** `frontend/tailwind.config.js`

Extend the theme with custom colors:

```javascript
theme: {
  extend: {
    colors: {
      primary: {
        50: '#f0fdf4',
        100: '#dcfce7',
        200: '#bbf7d0',
        300: '#86efac',
        400: '#4ade80',
        500: '#22c55e',
        600: '#16a34a',
        700: '#15803d',
        800: '#166534',
        900: '#14532d',
      },
      secondary: {
        50: '#fefce8',
        100: '#fef9c3',
        200: '#fef08a',
        300: '#fde047',
        400: '#facc15',
        500: '#eab308',
        600: '#ca8a04',
        700: '#a16207',
        800: '#854d0e',
        900: '#713f12',
      }
    }
  }
}
```

### 2. Logo Asset Management

**Directory:** `frontend/public/assets/`

Store the school logo:
- `logo.png` - Full color logo for light backgrounds
- `logo-white.png` - White version for dark backgrounds (if needed)

### 3. Layout Component Updates

**File:** `frontend/src/components/Layout.jsx`

Updates needed:
- Replace sidebar background from gray to `primary-700`
- Add school logo to header
- Update navigation item colors to use primary/secondary
- Active items use `secondary-400` background
- Hover states use `primary-600`

### 4. Login Page Updates

**File:** `frontend/src/components/Login.jsx`

Updates needed:
- Add school logo above login form
- Replace "Student Management System" with "Success City Academy"
- Update button colors from indigo to primary
- Update focus states to use primary colors
- Add subtle yellow accent border or shadow

### 5. Button Components

All buttons across the system:
- Primary buttons: `bg-primary-600 hover:bg-primary-700`
- Secondary buttons: `bg-secondary-500 hover:bg-secondary-600`
- Outline buttons: `border-primary-600 text-primary-600 hover:bg-primary-50`
- Danger buttons: Keep red for destructive actions

### 6. Form Components

All form inputs:
- Focus ring: `focus:ring-primary-500`
- Focus border: `focus:border-primary-500`
- Valid state: `border-primary-300`
- Labels: `text-gray-700` (maintain readability)

### 7. Table Components

**File:** `frontend/src/components/DataTable.jsx` and others

Updates needed:
- Header background: `bg-primary-50`
- Header text: `text-primary-900`
- Hover rows: `hover:bg-primary-50`
- Active sort: `text-primary-600`

### 8. Badge and Status Components

Status badges:
- Success: `bg-primary-100 text-primary-800`
- Warning: `bg-secondary-100 text-secondary-800`
- Info: `bg-blue-100 text-blue-800` (keep for distinction)
- Error: `bg-red-100 text-red-800` (keep for distinction)

### 9. Navigation Components

**Files:** All navigation-related components

Updates needed:
- Sidebar: `bg-primary-700`
- Navigation text: `text-white`
- Active item: `bg-secondary-400 text-primary-900`
- Hover item: `bg-primary-600`
- Icons: `text-white` with `text-secondary-400` for active

### 10. PDF Report Updates

**File:** `backend/utils/PDFGenerator.php`

Updates needed:
- Add logo to report header
- Update header colors to use green (#16a34a)
- Update accent colors to use yellow (#eab308)
- Add "Success City Academy" as institution name
- Ensure colors work well in print

### 11. Report Card Component

**File:** `frontend/src/components/ReportCard.jsx`

Updates needed:
- Header uses primary colors
- School name displays "Success City Academy"
- Logo appears in header
- Accent elements use secondary colors

## Data Models

No database changes required. This is purely a frontend styling update with some backend PDF generation changes.

## Error Handling

### Color Contrast Issues

If any color combinations fail WCAG AA accessibility standards:
- Adjust shade darkness/lightness
- Add text shadows or borders for readability
- Use alternative color combinations

### Logo Loading Failures

If logo fails to load:
- Display school name as text fallback
- Log error for debugging
- Ensure graceful degradation

### Browser Compatibility

Test color rendering across:
- Chrome/Edge
- Firefox
- Safari
- Mobile browsers

## Testing Strategy

### Visual Testing

1. **Component Review:**
   - Manually review each page/component
   - Verify color consistency
   - Check logo placement and sizing
   - Validate hover and active states

2. **Accessibility Testing:**
   - Run color contrast checker on all text/background combinations
   - Ensure minimum 4.5:1 ratio for normal text
   - Ensure minimum 3:1 ratio for large text and UI components

3. **Cross-browser Testing:**
   - Test on Chrome, Firefox, Safari
   - Test on mobile devices
   - Verify PDF generation with new colors

### Functional Testing

1. **Navigation:**
   - Verify all navigation links work
   - Check active state highlighting
   - Test hover effects

2. **Forms:**
   - Test focus states on all inputs
   - Verify validation styling
   - Check button interactions

3. **Reports:**
   - Generate PDF reports
   - Verify logo appears correctly
   - Check color rendering in PDF

### Regression Testing

1. Ensure no existing functionality is broken
2. Verify all components still render correctly
3. Test responsive behavior on different screen sizes

## Implementation Notes

### Phase 1: Configuration
- Update Tailwind config with custom colors
- Add logo assets to public directory

### Phase 2: Core Components
- Update Layout and Navigation
- Update Login page
- Update common button styles

### Phase 3: Feature Components
- Update all form components
- Update table components
- Update badge and status indicators

### Phase 4: Reports
- Update PDF generator
- Update report card component
- Test print styling

### Phase 5: Testing & Refinement
- Visual review of all pages
- Accessibility audit
- Cross-browser testing
- Final adjustments

## Design Decisions

### Why Green as Primary?

Green is the dominant color in the Success City Academy logo and represents growth, success, and education. It will be used for all primary actions and navigation.

### Why Yellow as Secondary?

Yellow/gold from the logo's sun element represents brightness, optimism, and achievement. It's perfect for highlights, active states, and success indicators.

### Maintaining Semantic Colors

Red for errors and warnings will be maintained for clear communication of issues, as these are universal UI conventions that shouldn't be overridden by branding.

### Logo Placement

The logo will appear in:
- Top left of navigation header (all pages)
- Center of login page
- Header of PDF reports

This ensures consistent brand presence without overwhelming the interface.
