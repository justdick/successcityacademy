# Admin Assignment Management UI Test Checklist

This document provides a comprehensive checklist for manually testing the admin assignment management UI functionality.

## Test Environment Setup

1. **Login Credentials:**
   - Admin: `admin` / `admin123`

2. **Prerequisites:**
   - Database seeded with multiple teachers (users with role='user')
   - Multiple classes and subjects available
   - Some existing assignments for testing

## Test 9.4: Admin Assignment Management UI Tests

### Test 9.4.1: Teacher Selection and Display

**Objective:** Verify that admin can view and select teachers

**Steps:**
1. Login as `admin`
2. Navigate to "Teacher Assignments" page
3. Observe the teacher list/dropdown

**Expected Results:**
- ✓ Page displays "Teacher Assignments" header
- ✓ Teacher selector dropdown is visible
- ✓ All teachers (users with role='user') appear in dropdown
- ✓ Teacher display format shows username or name
- ✓ Current selection is clearly indicated

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.4.2: Display Teacher's Current Assignments

**Objective:** Verify that selecting a teacher displays their current assignments

**Steps:**
1. Login as `admin`
2. Navigate to "Teacher Assignments" page
3. Select a teacher from the dropdown
4. Observe the assignments display

**Expected Results:**
- ✓ "Assigned Classes" section displays
- ✓ Each assigned class shows:
  - Class name
  - Student count (e.g., "25 students")
  - Remove button
- ✓ "Assigned Subjects" section displays
- ✓ Each assigned subject shows:
  - Subject name
  - Remove button
- ✓ If no assignments, appropriate message displays
- ✓ Assignment counts are accurate

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.4.3: Add Single Class Assignment

**Objective:** Verify that admin can assign a teacher to a single class

**Steps:**
1. Login as `admin`
2. Navigate to "Teacher Assignments" page
3. Select a teacher
4. Click "Add Class" or similar button
5. Select a class from dropdown
6. Click "Assign" or "Save"

**Expected Results:**
- ✓ Class assignment form/modal appears
- ✓ Dropdown shows all available classes
- ✓ Already assigned classes are indicated or disabled
- ✓ Success message displays after assignment
- ✓ New class appears in "Assigned Classes" section
- ✓ Student count displays correctly
- ✓ Assignment persists after page refresh

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.4.4: Add Single Subject Assignment

**Objective:** Verify that admin can assign a teacher to a single subject

**Steps:**
1. Login as `admin`
2. Navigate to "Teacher Assignments" page
3. Select a teacher
4. Click "Add Subject" or similar button
5. Select a subject from dropdown
6. Click "Assign" or "Save"

**Expected Results:**
- ✓ Subject assignment form/modal appears
- ✓ Dropdown shows all available subjects
- ✓ Already assigned subjects are indicated or disabled
- ✓ Success message displays after assignment
- ✓ New subject appears in "Assigned Subjects" section
- ✓ Assignment persists after page refresh

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.4.5: Bulk Class Assignment

**Objective:** Verify that admin can assign multiple classes to a teacher at once

**Steps:**
1. Login as `admin`
2. Navigate to "Teacher Assignments" page
3. Select a teacher
4. Click "Bulk Assign" or similar button
5. Select multiple classes (use checkboxes or multi-select)
6. Click "Assign All" or "Save"

**Expected Results:**
- ✓ Bulk assignment form/modal appears
- ✓ Multiple classes can be selected
- ✓ Already assigned classes are indicated
- ✓ Success message shows count: "Assigned to X classes"
- ✓ All selected classes appear in "Assigned Classes" section
- ✓ Student counts display correctly for all
- ✓ Assignments persist after page refresh

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.4.6: Bulk Subject Assignment

**Objective:** Verify that admin can assign multiple subjects to a teacher at once

**Steps:**
1. Login as `admin`
2. Navigate to "Teacher Assignments" page
3. Select a teacher
4. Click "Bulk Assign" or similar button
5. Select multiple subjects (use checkboxes or multi-select)
6. Click "Assign All" or "Save"

**Expected Results:**
- ✓ Bulk assignment form/modal appears
- ✓ Multiple subjects can be selected
- ✓ Already assigned subjects are indicated
- ✓ Success message shows count: "Assigned to X subjects"
- ✓ All selected subjects appear in "Assigned Subjects" section
- ✓ Assignments persist after page refresh

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.4.7: Remove Class Assignment

**Objective:** Verify that admin can remove a class assignment from a teacher

**Steps:**
1. Login as `admin`
2. Navigate to "Teacher Assignments" page
3. Select a teacher with existing class assignments
4. Click "Remove" button next to a class
5. Confirm removal if prompted

**Expected Results:**
- ✓ Confirmation dialog appears (optional but recommended)
- ✓ Success message displays after removal
- ✓ Class disappears from "Assigned Classes" section
- ✓ Removal persists after page refresh
- ✓ Teacher can no longer access students from that class

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.4.8: Remove Subject Assignment

**Objective:** Verify that admin can remove a subject assignment from a teacher

**Steps:**
1. Login as `admin`
2. Navigate to "Teacher Assignments" page
3. Select a teacher with existing subject assignments
4. Click "Remove" button next to a subject
5. Confirm removal if prompted

**Expected Results:**
- ✓ Confirmation dialog appears (optional but recommended)
- ✓ Success message displays after removal
- ✓ Subject disappears from "Assigned Subjects" section
- ✓ Removal persists after page refresh
- ✓ Teacher can no longer enter assessments for that subject

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.4.9: Filter by Teacher

**Objective:** Verify that admin can filter assignments by teacher

**Steps:**
1. Login as `admin`
2. Navigate to "Teacher Assignments" page
3. Use teacher filter/search
4. Observe filtered results

**Expected Results:**
- ✓ Filter/search field is visible
- ✓ Typing filters teacher list in real-time
- ✓ Only matching teachers display
- ✓ Selecting filtered teacher shows their assignments
- ✓ Clear filter option available

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.4.10: Filter by Class

**Objective:** Verify that admin can filter to see which teachers are assigned to a specific class

**Steps:**
1. Login as `admin`
2. Navigate to "Teacher Assignments" page
3. Use class filter dropdown
4. Select a specific class
5. Observe filtered results

**Expected Results:**
- ✓ Class filter dropdown is visible
- ✓ All classes appear in dropdown
- ✓ Selecting a class filters the view
- ✓ Only teachers assigned to that class display
- ✓ Assignment details show for filtered teachers
- ✓ Clear filter option available

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.4.11: Filter by Subject

**Objective:** Verify that admin can filter to see which teachers are assigned to a specific subject

**Steps:**
1. Login as `admin`
2. Navigate to "Teacher Assignments" page
3. Use subject filter dropdown
4. Select a specific subject
5. Observe filtered results

**Expected Results:**
- ✓ Subject filter dropdown is visible
- ✓ All subjects appear in dropdown
- ✓ Selecting a subject filters the view
- ✓ Only teachers assigned to that subject display
- ✓ Assignment details show for filtered teachers
- ✓ Clear filter option available

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.4.12: View All Teachers Summary

**Objective:** Verify that admin can view a summary of all teachers and their assignment counts

**Steps:**
1. Login as `admin`
2. Navigate to "Teacher Assignments" page
3. View the main list/table (before selecting a specific teacher)

**Expected Results:**
- ✓ List/table shows all teachers
- ✓ Each teacher row shows:
  - Teacher name/username
  - Number of assigned classes
  - Number of assigned subjects
- ✓ Clicking a teacher shows detailed assignments
- ✓ Teachers with no assignments are clearly indicated
- ✓ Summary is sortable (optional)

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.4.13: Duplicate Assignment Prevention

**Objective:** Verify that system prevents duplicate assignments

**Steps:**
1. Login as `admin`
2. Navigate to "Teacher Assignments" page
3. Select a teacher
4. Try to assign a class/subject that is already assigned

**Expected Results:**
- ✓ Already assigned classes are disabled or hidden in dropdown
- ✓ If duplicate attempted, error message displays
- ✓ Error message: "Teacher is already assigned to this class/subject"
- ✓ No duplicate assignment is created
- ✓ UI prevents duplicate selection

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.4.14: Error Handling - Invalid Teacher

**Objective:** Verify appropriate error handling for invalid teacher selection

**Steps:**
1. Login as `admin`
2. Navigate to "Teacher Assignments" page
3. Attempt to create assignment with invalid teacher ID (via API or URL manipulation)

**Expected Results:**
- ✓ Error message displays
- ✓ Error message is user-friendly
- ✓ No assignment is created
- ✓ UI remains stable

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.4.15: Error Handling - Invalid Class/Subject

**Objective:** Verify appropriate error handling for invalid class/subject selection

**Steps:**
1. Login as `admin`
2. Navigate to "Teacher Assignments" page
3. Attempt to create assignment with invalid class/subject ID

**Expected Results:**
- ✓ Error message displays
- ✓ Error message is user-friendly
- ✓ No assignment is created
- ✓ UI remains stable

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.4.16: Navigation and Layout

**Objective:** Verify that the Teacher Assignments page is properly integrated into the navigation

**Steps:**
1. Login as `admin`
2. Check the navigation menu

**Expected Results:**
- ✓ "Teacher Assignments" link appears in admin navigation menu
- ✓ Link is in appropriate section (e.g., "Administration" or "Management")
- ✓ Clicking link navigates to Teacher Assignments page
- ✓ Current page is highlighted in navigation
- ✓ Page layout is consistent with other admin pages

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.4.17: Responsive Design

**Objective:** Verify that the UI works on different screen sizes

**Steps:**
1. Login as `admin`
2. Navigate to "Teacher Assignments" page
3. Resize browser window or test on different devices

**Expected Results:**
- ✓ Layout adapts to mobile screens
- ✓ All functionality accessible on mobile
- ✓ Dropdowns and buttons are touch-friendly
- ✓ Tables/lists are scrollable or responsive
- ✓ No horizontal scrolling required
- ✓ Text is readable at all sizes

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.4.18: Loading States

**Objective:** Verify appropriate loading indicators during data operations

**Steps:**
1. Login as `admin`
2. Navigate to "Teacher Assignments" page
3. Observe loading states during:
   - Initial page load
   - Teacher selection
   - Assignment creation
   - Assignment removal

**Expected Results:**
- ✓ Loading spinner/indicator displays during operations
- ✓ Buttons are disabled during save operations
- ✓ Loading text is clear (e.g., "Loading...", "Saving...")
- ✓ UI doesn't allow duplicate submissions
- ✓ Loading states clear after operation completes

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.4.19: Success Messages

**Objective:** Verify that success messages display appropriately

**Steps:**
1. Login as `admin`
2. Navigate to "Teacher Assignments" page
3. Perform various operations and observe success messages

**Expected Results:**
- ✓ Success message displays after class assignment
- ✓ Success message displays after subject assignment
- ✓ Success message displays after bulk assignment
- ✓ Success message displays after removal
- ✓ Messages are clear and specific
- ✓ Messages auto-dismiss after a few seconds (optional)
- ✓ Messages are visually distinct (green background, checkmark icon)

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.4.20: Accessibility

**Objective:** Verify that the UI is accessible

**Steps:**
1. Login as `admin`
2. Navigate to "Teacher Assignments" page
3. Test accessibility features

**Expected Results:**
- ✓ All interactive elements are keyboard accessible
- ✓ Tab order is logical
- ✓ Focus indicators are visible
- ✓ Form labels are properly associated
- ✓ Error messages are announced to screen readers
- ✓ Color contrast meets WCAG standards
- ✓ ARIA labels are present where needed

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

## Test Summary

**Total Tests:** 20
**Passed:** _____
**Failed:** _____
**Completion Date:** _____
**Tester:** _____

## Issues Found

| Test # | Issue Description | Severity | Status |
|--------|------------------|----------|--------|
|        |                  |          |        |
|        |                  |          |        |
|        |                  |          |        |

## Additional Notes

_______________________________________________________________________
_______________________________________________________________________
_______________________________________________________________________

## Sign-off

**Tested By:** _____________________ **Date:** _____________________

**Reviewed By:** _____________________ **Date:** _____________________
