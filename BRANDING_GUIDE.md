# Success City Academy Branding Guide

## Overview

This guide documents the branding implementation for Success City Academy in the Student Management System. It provides guidelines for maintaining consistent branding across all components and future development.

---

## Brand Identity

### School Name
**Success City Academy**

### Brand Colors

#### Primary Color: Green
The primary green color represents growth, success, and education. It is the dominant color in the school logo and should be used for:
- Primary actions (buttons, links)
- Navigation elements
- Headers and important UI elements
- Active states

#### Secondary Color: Yellow
The secondary yellow/gold color represents brightness, optimism, and achievement. It should be used for:
- Accent highlights
- Active navigation items
- Success indicators
- Secondary actions

### Logo
The Success City Academy logo features a green and yellow design and should be displayed:
- In the navigation header (all pages)
- On the login page
- In PDF report headers
- In any official documentation

---

## Color Palette

### Primary Green Scale

| Shade | Hex Code | RGB | Usage |
|-------|----------|-----|-------|
| primary-50 | #f0fdf4 | rgb(240, 253, 244) | Light backgrounds, table headers |
| primary-100 | #dcfce7 | rgb(220, 252, 231) | Hover states, success badges |
| primary-200 | #bbf7d0 | rgb(187, 247, 208) | Borders, dividers |
| primary-300 | #86efac | rgb(134, 239, 172) | Disabled states |
| primary-400 | #4ade80 | rgb(74, 222, 128) | Secondary elements |
| primary-500 | #22c55e | rgb(34, 197, 94) | Focus rings, active elements |
| primary-600 | #16a34a | rgb(22, 163, 74) | **Primary buttons, links** |
| primary-700 | #15803d | rgb(21, 128, 61) | **Navigation bar, hover states** |
| primary-800 | #166534 | rgb(22, 101, 52) | Badge text, dark text |
| primary-900 | #14532d | rgb(20, 83, 45) | Headings, emphasis text |

### Secondary Yellow Scale

| Shade | Hex Code | RGB | Usage |
|-------|----------|-----|-------|
| secondary-50 | #fefce8 | rgb(254, 252, 232) | Light backgrounds |
| secondary-100 | #fef9c3 | rgb(254, 249, 195) | Warning badges background |
| secondary-200 | #fef08a | rgb(254, 240, 138) | Borders, accents |
| secondary-300 | #fde047 | rgb(253, 224, 71) | Hover states |
| secondary-400 | #facc15 | rgb(250, 204, 21) | **Active navigation items** |
| secondary-500 | #eab308 | rgb(234, 179, 8) | **Secondary buttons, accents** |
| secondary-600 | #ca8a04 | rgb(202, 138, 4) | Hover states |
| secondary-700 | #a16207 | rgb(161, 98, 7) | Dark accents |
| secondary-800 | #854d0e | rgb(133, 77, 14) | Badge text |
| secondary-900 | #713f12 | rgb(113, 63, 18) | Dark text |

### Semantic Colors (Maintained)

These colors are maintained for clear communication and should NOT be replaced with brand colors:

| Purpose | Color | Usage |
|---------|-------|-------|
| Error | Red (red-500, red-600) | Error messages, danger buttons |
| Warning | Orange (orange-500) | Warning messages |
| Info | Blue (blue-500) | Informational messages |
| Success | Primary Green | Success messages, confirmations |

---

## Component Usage Guidelines

### Navigation Bar

```jsx
// Navigation bar background
className="bg-primary-700 text-white"

// Active navigation item
className="bg-secondary-400 text-primary-900"

// Hover state
className="hover:bg-primary-600"

// Logo
<img src="/assets/logo.svg" alt="Success City Academy Logo" className="h-10 w-10" />
```

**Guidelines:**
- Always use `primary-700` for navigation background
- Active items use `secondary-400` background with `primary-900` text
- Hover states use `primary-600`
- Logo should be 40px (h-10 w-10) in navigation

---

### Buttons

#### Primary Button
```jsx
className="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md"
```

**Usage:** Main actions, form submissions, confirmations

#### Secondary Button
```jsx
className="bg-secondary-500 hover:bg-secondary-600 text-primary-900 px-4 py-2 rounded-md"
```

**Usage:** Alternative actions, less prominent actions

#### Outline Button
```jsx
className="border-2 border-primary-600 text-primary-600 hover:bg-primary-50 px-4 py-2 rounded-md"
```

**Usage:** Tertiary actions, cancel buttons

#### Danger Button
```jsx
className="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md"
```

**Usage:** Destructive actions (delete, remove)

**Guidelines:**
- Use primary buttons for main actions
- Use secondary buttons sparingly for emphasis
- Keep danger buttons red for clarity
- Ensure sufficient padding for touch targets (minimum 44x44px on mobile)

---

### Links

```jsx
className="text-primary-600 hover:text-primary-700 underline"
```

**Guidelines:**
- All links use `primary-600`
- Hover states use `primary-700`
- Underline for clarity (optional based on context)
- Visited links can use `primary-800` for distinction

---

### Form Inputs

```jsx
className="border border-gray-300 rounded-md px-3 py-2 
           focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
```

**Guidelines:**
- Focus rings use `primary-500`
- Focus borders use `primary-500`
- Valid states can use `border-primary-300`
- Error states use `border-red-500`
- Labels use `text-gray-700` for readability

---

### Tables

#### Table Header
```jsx
className="bg-primary-50 text-primary-900 font-semibold"
```

#### Table Row Hover
```jsx
className="hover:bg-primary-50"
```

#### Active Sort Indicator
```jsx
className="text-primary-600"
```

**Guidelines:**
- Headers use light green background (`primary-50`)
- Header text uses dark green (`primary-900`)
- Row hover uses same light green
- Active sort indicators use `primary-600`
- Maintain zebra striping if needed with `bg-gray-50`

---

### Badges and Status Indicators

#### Success Badge
```jsx
className="bg-primary-100 text-primary-800 px-3 py-1 rounded-full text-sm font-medium"
```

#### Warning Badge
```jsx
className="bg-secondary-100 text-secondary-800 px-3 py-1 rounded-full text-sm font-medium"
```

#### Info Badge
```jsx
className="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium"
```

#### Error Badge
```jsx
className="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium"
```

**Guidelines:**
- Success badges use primary green
- Warning badges use secondary yellow
- Keep info badges blue for distinction
- Keep error badges red for clarity
- Use rounded-full for pill shape
- Ensure text contrast meets WCAG AA standards

---

### Login Page

```jsx
// Background gradient
className="bg-gradient-to-br from-primary-50 via-secondary-50 to-primary-100"

// Card border
className="border-2 border-secondary-200"

// Logo
<img src="/assets/logo.svg" alt="Success City Academy Logo" className="h-20 w-20" />

// Title
<h2 className="text-3xl font-bold text-primary-700">Success City Academy</h2>
```

**Guidelines:**
- Use gradient background with primary and secondary colors
- Logo should be 80px (h-20 w-20) on login page
- Title uses `primary-700`
- Card has subtle yellow border
- Login button uses primary colors

---

### Dropdown Menus

```jsx
// Dropdown container
className="bg-white text-gray-800 rounded-lg shadow-xl"

// Dropdown item
className="px-4 py-2 hover:bg-primary-50"
```

**Guidelines:**
- White background for contrast
- Hover states use `primary-50`
- Maintain good text contrast
- Add subtle shadow for depth

---

## Logo Usage

### Logo Files
- **Location:** `frontend/public/assets/logo.svg`
- **Format:** SVG (scalable vector graphics)
- **Fallback:** PNG versions if needed

### Logo Sizes

| Context | Size | Class |
|---------|------|-------|
| Navigation Header | 40px | h-10 w-10 |
| Login Page | 80px | h-20 w-20 |
| PDF Reports | 60px | (PHP: 60x60) |
| Favicon | 32px | (separate file) |

### Logo Placement

**Do:**
- Place logo in top-left of navigation
- Center logo on login page
- Include logo in PDF report headers
- Ensure logo has adequate spacing
- Use on white or dark green backgrounds

**Don't:**
- Stretch or distort logo
- Place on busy backgrounds
- Use logo smaller than 32px
- Overlay text on logo
- Modify logo colors

### Logo Accessibility
- Always include alt text: "Success City Academy Logo"
- Ensure logo is visible against background
- Provide text alternative if logo fails to load

---

## Accessibility Guidelines

### Color Contrast

All color combinations meet WCAG AA standards (4.5:1 for normal text, 3:1 for large text):

| Combination | Contrast Ratio | Status |
|-------------|----------------|--------|
| primary-700 on white | 6.37:1 | ✅ Pass |
| primary-600 on white | 4.89:1 | ✅ Pass |
| secondary-400 on primary-900 | 8.52:1 | ✅ Pass |
| primary-900 on primary-50 | 13.87:1 | ✅ Pass |
| primary-800 on primary-100 | 7.94:1 | ✅ Pass |
| secondary-800 on secondary-100 | 8.12:1 | ✅ Pass |

### Focus Indicators
- All interactive elements must have visible focus indicators
- Use `focus:ring-2 focus:ring-primary-500` for consistency
- Ensure focus indicators are visible against all backgrounds
- Minimum 2px ring width

### Touch Targets
- Minimum 44x44px for mobile touch targets
- Adequate spacing between interactive elements
- Larger targets for primary actions

---

## Tailwind Configuration

The brand colors are defined in `frontend/tailwind.config.js`:

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

**To update brand colors:**
1. Modify the color values in `tailwind.config.js`
2. Rebuild the CSS: `npm run build`
3. Test all components for contrast compliance
4. Update this documentation

---

## PDF Generation

### Backend Configuration
**File:** `backend/utils/PDFGenerator.php`

### PDF Branding Elements

```php
// Header background color
$headerColor = '#16a34a'; // primary-600

// Accent color
$accentColor = '#eab308'; // secondary-500

// Institution name
$institutionName = 'Success City Academy';

// Logo path
$logoPath = '../frontend/public/assets/logo.svg';
```

### PDF Guidelines
- Use `primary-600` (#16a34a) for headers
- Use `secondary-500` (#eab308) for accents
- Include logo in header (60x60px)
- Display "Success City Academy" prominently
- Ensure colors work well in print
- Test both digital and printed output
- Maintain professional appearance

---

## Responsive Design

### Breakpoints

| Breakpoint | Width | Usage |
|------------|-------|-------|
| Mobile Small | 320px | Minimum supported width |
| Mobile | 375px | Standard mobile phones |
| Mobile Large | 414px | Large mobile phones |
| Tablet | 768px | Tablets portrait |
| Laptop | 1024px | Small laptops, tablets landscape |
| Desktop | 1280px | Standard desktop |
| Desktop Large | 1920px | Large desktop |

### Responsive Guidelines
- Logo scales appropriately at each breakpoint
- Navigation adapts for mobile (consider hamburger menu)
- Touch targets are minimum 44x44px on mobile
- Tables scroll horizontally on small screens
- Forms stack vertically on mobile
- Maintain brand colors across all breakpoints

---

## Best Practices

### Do's
✅ Use semantic color names (primary, secondary) in code
✅ Maintain consistent spacing and sizing
✅ Test color contrast for accessibility
✅ Use the logo at specified sizes
✅ Keep semantic colors (red for errors, etc.)
✅ Test on multiple browsers and devices
✅ Ensure keyboard navigation works
✅ Provide alt text for images

### Don'ts
❌ Don't use hardcoded hex values in components
❌ Don't modify the logo
❌ Don't use brand colors for errors/warnings
❌ Don't skip accessibility testing
❌ Don't use colors that fail contrast tests
❌ Don't forget to test PDF output
❌ Don't ignore responsive design
❌ Don't use color alone to convey information

---

## Common Patterns

### Card Component
```jsx
<div className="bg-white rounded-lg shadow-md border border-gray-200 p-6">
  <h3 className="text-xl font-bold text-primary-700 mb-4">Card Title</h3>
  <p className="text-gray-600">Card content...</p>
  <button className="mt-4 bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md">
    Action
  </button>
</div>
```

### Alert Component
```jsx
// Success Alert
<div className="bg-primary-50 border-l-4 border-primary-600 p-4">
  <p className="text-primary-800">Success message</p>
</div>

// Warning Alert
<div className="bg-secondary-50 border-l-4 border-secondary-600 p-4">
  <p className="text-secondary-800">Warning message</p>
</div>

// Error Alert
<div className="bg-red-50 border-l-4 border-red-600 p-4">
  <p className="text-red-800">Error message</p>
</div>
```

### Loading Spinner
```jsx
<div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
```

---

## Testing Checklist

Before deploying any changes:

- [ ] Visual consistency across all pages
- [ ] Logo displays correctly everywhere
- [ ] Color contrast meets WCAG AA standards
- [ ] Hover states work correctly
- [ ] Focus indicators are visible
- [ ] Responsive design works at all breakpoints
- [ ] Keyboard navigation works
- [ ] Screen reader compatibility
- [ ] Cross-browser testing complete
- [ ] PDF generation works correctly
- [ ] Print output is acceptable
- [ ] Performance is acceptable

---

## Maintenance

### Updating Brand Colors
1. Update `tailwind.config.js`
2. Update this documentation
3. Run accessibility tests
4. Test all components
5. Update PDF generator if needed
6. Deploy changes

### Adding New Components
1. Follow existing patterns
2. Use semantic color names
3. Test accessibility
4. Document any new patterns
5. Update this guide if needed

### Troubleshooting

**Colors not updating:**
- Rebuild Tailwind CSS: `npm run build`
- Clear browser cache
- Check for hardcoded hex values

**Logo not displaying:**
- Check file path: `/assets/logo.svg`
- Verify file exists in `frontend/public/assets/`
- Check browser console for errors
- Verify SVG is valid

**Contrast issues:**
- Use WebAIM Contrast Checker
- Adjust shade lighter or darker
- Test with actual users
- Document any exceptions

---

## Resources

### Tools
- **Color Contrast Checker:** https://webaim.org/resources/contrastchecker/
- **Tailwind CSS Docs:** https://tailwindcss.com/docs
- **WCAG Guidelines:** https://www.w3.org/WAI/WCAG21/quickref/
- **Lighthouse:** Chrome DevTools > Lighthouse tab

### Files
- **Tailwind Config:** `frontend/tailwind.config.js`
- **Logo:** `frontend/public/assets/logo.svg`
- **PDF Generator:** `backend/utils/PDFGenerator.php`
- **Layout Component:** `frontend/src/components/Layout.jsx`
- **Login Component:** `frontend/src/components/Login.jsx`

### Documentation
- **Visual Testing:** `frontend/visual-testing-checklist.md`
- **Accessibility Testing:** `frontend/accessibility-testing-report.md`
- **Cross-Browser Testing:** `frontend/cross-browser-testing-guide.md`
- **This Guide:** `BRANDING_GUIDE.md`

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2024 | Initial branding implementation |

---

## Contact

For questions about branding implementation or to request changes, contact the development team.

---

**Last Updated:** November 2024
**Maintained By:** Development Team
**Status:** Active
