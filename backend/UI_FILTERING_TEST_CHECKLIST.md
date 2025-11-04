# UI Filtering Test Checklist

This document provides a comprehensive checklist for manually testing UI filtering functionality for the Teacher Assignments feature.

## Test Environment Setup

1. **Login Credentials:**
   - Admin: `admin` / `admin123`
   - Teacher: `teacher1` / `teacher123`

2. **Prerequisites:**
   - Database seeded with students, classes, subjects, and terms
   - Teacher has some (but not all) class and subject assignments
   - Some students exist in classes not assigned to the teacher

## Test 9.3: UI Filtering Tests

### Test 9.3.1: Student List Filtering for Teachers

**Objective:** Verify that teachers only see students from their assigned classes

**Steps:**
1. Login as `teacher1`
2. Navigate to "Students" page
3. Observe the student list

**Expected Results:**
- ✓ Blue info banner displays: "Viewing students from your assigned classes: [Class Names]"
- ✓ Only students from assigned classes are visible in the table
- ✓ Student count matches the number of students in assigned classes
- ✓ Class filter dropdown only shows assigned classes
- ✓ No students from non-assigned classes appear in the list

**Test Data Verification:**
- Teacher's assigned classes: [Record from "My Assignments" page]
- Students displayed: [Count and verify class_level_id matches assigned classes]

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.3.2: Student List - No Assignments Message

**Objective:** Verify appropriate message displays when teacher has no class assignments

**Steps:**
1. Login as a teacher with NO class assignments (or temporarily remove all class assignments)
2. Navigate to "Students" page

**Expected Results:**
- ✓ Yellow warning banner displays: "No Class Assignments"
- ✓ Message states: "You have not been assigned to any classes yet. Please contact your administrator to assign you to classes."
- ✓ Student table shows: "No students to display. You need class assignments to view students."
- ✓ "Add Student" button is still visible (for admins only)

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.3.3: Assessment Entry - Subject Filtering

**Objective:** Verify that teachers only see their assigned subjects in assessment forms

**Steps:**
1. Login as `teacher1`
2. Navigate to "Assessment Entry" page
3. Select a term
4. Observe the subject dropdown

**Expected Results:**
- ✓ Subject dropdown only contains assigned subjects
- ✓ Subject count matches teacher's assigned subjects
- ✓ Non-assigned subjects do not appear in dropdown
- ✓ Weighting information displays correctly for selected subject

**Test Data Verification:**
- Teacher's assigned subjects: [Record from "My Assignments" page]
- Subjects in dropdown: [Verify matches assigned subjects]

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.3.4: Assessment Entry - Student Filtering

**Objective:** Verify that teachers only see students from assigned classes in assessment forms

**Steps:**
1. Login as `teacher1`
2. Navigate to "Assessment Entry" page
3. Select a term
4. Observe the student dropdown

**Expected Results:**
- ✓ Student dropdown only contains students from assigned classes
- ✓ Student count matches students in assigned classes
- ✓ Students from non-assigned classes do not appear
- ✓ Student display format: "Name (ID) - Class"

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.3.5: Assessment Entry - No Assignments Message

**Objective:** Verify appropriate messages display when teacher has no assignments

**Steps:**
1. Login as a teacher with NO assignments
2. Navigate to "Assessment Entry" page

**Expected Results:**
- ✓ Yellow warning banner displays: "No Assignments"
- ✓ Appropriate message based on missing assignments:
  - No classes AND no subjects: "You have not been assigned to any classes or subjects. Please contact your administrator."
  - No classes only: "You have not been assigned to any classes. Please contact your administrator."
  - No subjects only: "You have not been assigned to any subjects. Please contact your administrator."
- ✓ Student dropdown shows: "No students available"
- ✓ Subject dropdown shows: "No subjects available"
- ✓ Form fields are disabled appropriately

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.3.6: Assessment Grid - Subject Filtering

**Objective:** Verify that teachers only see their assigned subjects in grid view

**Steps:**
1. Login as `teacher1`
2. Navigate to "Assessment Grid" page
3. Select a term and class
4. Observe the subject dropdown

**Expected Results:**
- ✓ Subject dropdown only contains assigned subjects
- ✓ Grid only displays columns for assigned subjects
- ✓ Non-assigned subjects do not appear

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.3.7: Assessment Grid - Student Filtering

**Objective:** Verify that teachers only see students from assigned classes in grid view

**Steps:**
1. Login as `teacher1`
2. Navigate to "Assessment Grid" page
3. Select a term
4. Observe the class dropdown and student list

**Expected Results:**
- ✓ Class dropdown only contains assigned classes
- ✓ Student rows only show students from selected assigned class
- ✓ Students from non-assigned classes do not appear

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.3.8: Student Report - Class Filtering

**Objective:** Verify that teachers can only generate reports for students in assigned classes

**Steps:**
1. Login as `teacher1`
2. Navigate to "Student Report" page
3. Observe the student dropdown

**Expected Results:**
- ✓ Student dropdown only contains students from assigned classes
- ✓ Students from non-assigned classes do not appear
- ✓ Report generation works for assigned students

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.3.9: Class Report - Class Filtering

**Objective:** Verify that teachers can only generate reports for assigned classes

**Steps:**
1. Login as `teacher1`
2. Navigate to "Class Report" page
3. Observe the class dropdown

**Expected Results:**
- ✓ Class dropdown only contains assigned classes
- ✓ Non-assigned classes do not appear
- ✓ Report generation works for assigned classes

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.3.10: My Assignments Dashboard

**Objective:** Verify that "My Assignments" page displays correctly

**Steps:**
1. Login as `teacher1`
2. Navigate to "My Assignments" page

**Expected Results:**
- ✓ Page displays "My Teaching Assignments" header
- ✓ "My Classes" section shows all assigned classes
- ✓ Each class card displays:
  - Class name
  - Student count
  - "View Students" button
- ✓ "My Subjects" section shows all assigned subjects
- ✓ Subject cards display subject names
- ✓ "Quick Actions" section displays with links to:
  - View My Students
  - Enter Assessments
  - Generate Reports

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.3.11: My Assignments - No Assignments Message

**Objective:** Verify appropriate message when teacher has no assignments

**Steps:**
1. Login as a teacher with NO assignments
2. Navigate to "My Assignments" page

**Expected Results:**
- ✓ Icon displays (document/clipboard icon)
- ✓ Header: "No Assignments Yet"
- ✓ Message: "You have not been assigned to any classes or subjects yet."
- ✓ Instruction: "Please contact your administrator to get assigned to classes and subjects."
- ✓ No class or subject cards display
- ✓ No "Quick Actions" section displays

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

### Test 9.3.12: Admin View - No Filtering

**Objective:** Verify that admins see all data without filtering

**Steps:**
1. Login as `admin`
2. Navigate to each page:
   - Students
   - Assessment Entry
   - Assessment Grid
   - Student Report
   - Class Report

**Expected Results:**
- ✓ Students page: All students from all classes visible
- ✓ Assessment Entry: All subjects and all students available
- ✓ Assessment Grid: All classes and all subjects available
- ✓ Student Report: All students available
- ✓ Class Report: All classes available
- ✓ No "viewing assigned classes" messages display
- ✓ No filtering restrictions apply

**Status:** [ ] Pass [ ] Fail

**Notes:**
_______________________________________________________________________

---

## Test Summary

**Total Tests:** 12
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
