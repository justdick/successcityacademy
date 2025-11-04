# Branding Quick Reference

Quick reference for Success City Academy branding implementation.

## Colors

### Primary (Green)
- **Buttons:** `bg-primary-600 hover:bg-primary-700`
- **Links:** `text-primary-600 hover:text-primary-700`
- **Navigation:** `bg-primary-700`
- **Focus:** `focus:ring-primary-500`

### Secondary (Yellow)
- **Active Nav:** `bg-secondary-400`
- **Accents:** `text-secondary-500`
- **Warnings:** `bg-secondary-100 text-secondary-800`

## Logo

```jsx
// Navigation (40px)
<img src="/assets/logo.svg" alt="Success City Academy Logo" className="h-10 w-10" />

// Login (80px)
<img src="/assets/logo.svg" alt="Success City Academy Logo" className="h-20 w-20" />
```

## Common Components

### Button
```jsx
<button className="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md">
  Click Me
</button>
```

### Input
```jsx
<input 
  className="border border-gray-300 rounded-md px-3 py-2 
             focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
/>
```

### Link
```jsx
<a className="text-primary-600 hover:text-primary-700 underline">
  Link Text
</a>
```

### Badge
```jsx
<span className="bg-primary-100 text-primary-800 px-3 py-1 rounded-full text-sm">
  Success
</span>
```

## Accessibility

- **Contrast:** All combinations meet WCAG AA (4.5:1)
- **Focus:** Always include `focus:ring-2 focus:ring-primary-500`
- **Touch:** Minimum 44x44px on mobile
- **Alt Text:** Always include for logo

## Don'ts

❌ Don't use hardcoded hex values
❌ Don't modify the logo
❌ Don't use green/yellow for errors
❌ Don't skip accessibility testing

## Full Documentation

See `BRANDING_GUIDE.md` for complete guidelines.
