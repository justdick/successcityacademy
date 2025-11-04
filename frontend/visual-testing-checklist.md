# Visual Testing Checklist - School Branding

## Overview
This document provides a comprehensive checklist for manually testing the Success City Academy branding implementation across all components.

## Color Consistency Testing

### Primary Color (Green) Usage
- [ ] Navigation bar background uses `primary-700` (#15803d)
- [ ] Primary buttons use `primary-600` (#16a34a)
- [ ] Primary button hover states use `primary-700` (#15803d)
- [ ] Focus rings on inputs use `primary-500` (#22c55e)
- [ ] Links use `primary-600` (#16a34a)
- [ ] Link hover states use `primary-700` (#15803d)
- [ ] Table headers use `primary-50` (#f0fdf4) background
- [ ] Table header text uses `primary-900` (#14532d)
- [ ] Active sort indicators use `primary-600` (#16a34a)
- [ ] Success badges use `primary-100` (#dcfce7) background with `primary-800` (#166534) text

### Secondary Color (Yellow) Usage
- [ ] Active navigation items use `secondary-400` (#facc15) background
- [ ] Warning badges use `secondary-100` (#fef9c3) background with `secondary-800` (#854d0e) text
- [ ] Login page has yellow accent elements
- [ ] Report cards use secondary colors for accents

### Semantic Colors (Maintained)
- [ ] Error messages use red colors
- [ ] Danger buttons use red colors
- [ ] Info badges use blue colors (for distinction)

## Logo Placement and Sizing

### Navigation Header
- [ ] Logo appears in top-left of navigation bar
- [ ] Logo size is 40px (h-10 w-10)
- [ ] Logo is clearly visible against `primary-700` background
- [ ] Logo is properly aligned with "Success City Academy" text
- [ ] Logo loads correctly (check browser console for errors)

### Login Page
- [ ] Logo appears centered above login form
- [ ] Logo size is 80px (h-20 w-20)
- [ ] Logo is clearly visible against gradient background
- [ ] "Success City Academy" title appears below logo

### PDF Reports
- [ ] Logo appears in PDF header
- [ ] Logo is appropriately sized for print
- [ ] Logo renders correctly in PDF format

## Hover and Active States

### Navigation
- [ ] Navigation items show `primary-600` background on hover
- [ ] Active navigation items show `secondary-400` background
- [ ] Active navigation items show `primary-900` text color
- [ ] Dropdown menus appear on hover
- [ ] Dropdown items show `primary-50` background on hover

### Buttons
- [ ] Primary buttons change to `primary-700` on hover
- [ ] Secondary buttons change to `secondary-600` on hover
- [ ] Outline buttons show `primary-50` background on hover
- [ ] Disabled buttons show gray and no hover effect

### Links
- [ ] Links change to `primary-700` on hover
- [ ] Underline or visual feedback appears on hover

### Tables
- [ ] Table rows show `primary-50` background on hover
- [ ] Sortable column headers show pointer cursor
- [ ] Active sort column shows visual indicator

### Forms
- [ ] Input fields show `primary-500` focus ring
- [ ] Input fields show `primary-500` border on focus
- [ ] Validation states show appropriate colors

## Responsive Behavior

### Desktop (1920x1080)
- [ ] Navigation bar spans full width
- [ ] Logo and text are properly aligned
- [ ] All navigation items are visible
- [ ] Content is centered with appropriate margins
- [ ] Tables display all columns

### Laptop (1366x768)
- [ ] Navigation remains functional
- [ ] Logo is visible
- [ ] Dropdown menus work correctly
- [ ] Content adapts to smaller width

### Tablet (768x1024)
- [ ] Navigation adapts appropriately
- [ ] Logo remains visible
- [ ] Content is readable
- [ ] Tables scroll horizontally if needed
- [ ] Forms remain usable

### Mobile (375x667)
- [ ] Navigation collapses or adapts for mobile
- [ ] Logo is appropriately sized
- [ ] Content stacks vertically
- [ ] Buttons are touch-friendly
- [ ] Forms are usable on small screens

## Component-Specific Testing

### Layout Component
- [ ] Navigation bar uses correct green background
- [ ] Logo displays correctly
- [ ] "Success City Academy" title is visible
- [ ] User welcome message is visible
- [ ] Logout button uses rose color (not branded)
- [ ] Admin badge uses white/20 opacity background

### Login Component
- [ ] Gradient background uses primary and secondary colors
- [ ] Logo is centered and visible
- [ ] "Success City Academy" title is prominent
- [ ] Input focus states use primary colors
- [ ] Sign in button uses primary colors
- [ ] Border uses `secondary-200`

### Student Management
- [ ] Action buttons use primary colors
- [ ] Table styling uses primary colors
- [ ] Status badges use appropriate colors
- [ ] Forms use primary focus states

### Assessment Components
- [ ] Entry forms use primary colors
- [ ] Grid view uses primary colors
- [ ] Summary dashboard uses primary colors
- [ ] Status indicators use appropriate colors

### Report Components
- [ ] Report cards show school branding
- [ ] Headers use primary colors
- [ ] Accents use secondary colors
- [ ] Print preview maintains branding

### Admin Components
- [ ] User management uses primary colors
- [ ] Subject management uses primary colors
- [ ] Class level management uses primary colors
- [ ] Term management uses primary colors
- [ ] Subject weighting uses primary colors

## Cross-Component Consistency

- [ ] All primary buttons use same green shade
- [ ] All secondary buttons use same yellow shade
- [ ] All focus states use same primary-500 ring
- [ ] All hover states are consistent
- [ ] All active states are consistent
- [ ] Font weights are consistent
- [ ] Border radius is consistent
- [ ] Spacing is consistent

## Testing Instructions

1. **Start the development server:**
   ```bash
   cd frontend
   npm run dev
   ```

2. **Test each page systematically:**
   - Login page
   - Student list
   - Student form (add/edit)
   - Assessment entry
   - Assessment grid
   - Assessment summary
   - Student reports
   - Class reports
   - User management (admin)
   - Subject management (admin)
   - Class level management (admin)
   - Term management (admin)
   - Subject weighting (admin)

3. **For each page, verify:**
   - Color consistency
   - Logo placement
   - Hover states
   - Active states
   - Responsive behavior

4. **Use browser DevTools:**
   - Inspect elements to verify CSS classes
   - Check computed styles
   - Test responsive design mode
   - Check console for errors

5. **Document issues:**
   - Take screenshots of any inconsistencies
   - Note specific components with issues
   - Record browser and viewport size
   - Describe expected vs actual behavior

## Sign-off

- [ ] All color consistency checks passed
- [ ] All logo placement checks passed
- [ ] All hover/active state checks passed
- [ ] All responsive behavior checks passed
- [ ] All component-specific checks passed
- [ ] Cross-component consistency verified

**Tested by:** _______________
**Date:** _______________
**Browser(s):** _______________
**Issues found:** _______________
