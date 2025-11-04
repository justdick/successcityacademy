# Teacher Assignments User Guide

## Table of Contents

1. [Introduction](#introduction)
2. [For Administrators](#for-administrators)
3. [For Teachers](#for-teachers)
4. [Access Restrictions](#access-restrictions)
5. [Troubleshooting](#troubleshooting)
6. [Frequently Asked Questions](#frequently-asked-questions)

---

## Introduction

The Teacher Assignments feature allows administrators to control which students and subjects teachers can access in the Student Management System. This ensures that:

- Teachers only see students in their assigned classes
- Teachers can only enter grades for subjects they teach
- Data security is maintained through role-based access control
- The system reflects real-world teaching assignments

### Key Concepts

**Class Assignment:** Links a teacher to a specific grade level (e.g., Grade 9, Grade 10). Teachers can only view and manage students in their assigned classes.

**Subject Assignment:** Links a teacher to a specific subject (e.g., Mathematics, Science). Teachers can only enter grades for their assigned subjects.

**Combined Access:** Teachers need both class and subject assignments to enter grades. For example, a teacher assigned to Grade 9 and Mathematics can enter math grades for Grade 9 students only.

---

## For Administrators

Administrators have full access to all system features and are responsible for managing teacher assignments.

### Accessing Teacher Assignments

1. Log in with your admin account
2. Click **"Teacher Assignments"** in the navigation menu
3. The Teacher Assignments page will open

### Assigning a Teacher to Classes

#### Method 1: Individual Assignment

1. On the Teacher Assignments page, select a teacher from the dropdown
2. Click the **"+ Add Class"** button in the "Assigned Classes" section
3. Select the class level from the dropdown
4. Click **"Assign"**
5. The class will appear in the teacher's assigned classes list

#### Method 2: Bulk Assignment

1. On the Teacher Assignments page, select a teacher from the dropdown
2. Click the **"Bulk Assign"** button
3. In the bulk assignment form:
   - Check the boxes next to all classes you want to assign
   - Leave subjects unchecked if you only want to assign classes
4. Click **"Assign Selected"**
5. A success message will show how many assignments were created

**Example:** Assigning a teacher to Grade 9, Grade 10, and Grade 11 in one operation.

### Assigning a Teacher to Subjects

#### Method 1: Individual Assignment

1. On the Teacher Assignments page, select a teacher from the dropdown
2. Click the **"+ Add Subject"** button in the "Assigned Subjects" section
3. Select the subject from the dropdown
4. Click **"Assign"**
5. The subject will appear in the teacher's assigned subjects list

#### Method 2: Bulk Assignment

1. On the Teacher Assignments page, select a teacher from the dropdown
2. Click the **"Bulk Assign"** button
3. In the bulk assignment form:
   - Check the boxes next to all subjects you want to assign
   - Leave classes unchecked if you only want to assign subjects
4. Click **"Assign Selected"**
5. A success message will show how many assignments were created

**Example:** Assigning a teacher to Mathematics, Science, and English in one operation.

### Complete Teacher Setup Example

**Scenario:** Setting up a new Mathematics teacher who teaches Grade 9 and Grade 10.

1. Select the teacher from the dropdown
2. Click **"Bulk Assign"**
3. Check the boxes for:
   - Grade 9 (class)
   - Grade 10 (class)
   - Mathematics (subject)
4. Click **"Assign Selected"**
5. Verify the assignments appear in the teacher's profile

The teacher can now:
- View students in Grade 9 and Grade 10
- Enter mathematics grades for those students
- Generate reports for those students

### Removing Assignments

#### Remove a Class Assignment

1. Find the teacher in the Teacher Assignments page
2. Locate the class in the "Assigned Classes" section
3. Click the **"Remove"** button next to the class
4. Confirm the removal in the dialog
5. The class will be removed from the teacher's assignments

#### Remove a Subject Assignment

1. Find the teacher in the Teacher Assignments page
2. Locate the subject in the "Assigned Subjects" section
3. Click the **"Remove"** button next to the subject
4. Confirm the removal in the dialog
5. The subject will be removed from the teacher's assignments

**Warning:** Removing assignments immediately restricts the teacher's access. They will no longer be able to view students in removed classes or enter grades for removed subjects.

### Viewing All Assignments

#### View by Teacher

1. On the Teacher Assignments page, select a teacher from the dropdown
2. All their class and subject assignments are displayed
3. Student counts are shown for each assigned class

#### Filter Assignments

Use the filter options to find specific assignments:

- **By Teacher:** Select a teacher to see only their assignments
- **By Class:** Filter to see which teachers are assigned to a specific class
- **By Subject:** Filter to see which teachers are assigned to a specific subject

### Best Practices for Administrators

1. **Set up assignments at the start of each term**
   - Use bulk assignment to save time
   - Verify all teachers have appropriate assignments

2. **Review assignments regularly**
   - Check that teachers have access to the classes they teach
   - Remove outdated assignments when teachers change schedules

3. **Communicate with teachers**
   - Inform teachers when their assignments change
   - Provide guidance on viewing their assignments

4. **Use consistent naming**
   - Ensure class and subject names are clear and consistent
   - This helps teachers understand their assignments

5. **Test access after assignment**
   - Log in as the teacher (or ask them to verify)
   - Confirm they can see the expected students and subjects

### Common Admin Tasks

#### Task: Assign a new teacher

1. Ensure the teacher account exists (role: "user")
2. Go to Teacher Assignments
3. Select the teacher
4. Use bulk assign to add all their classes and subjects
5. Verify the assignments

#### Task: Update assignments for a new term

1. Review current assignments
2. Remove assignments for classes/subjects no longer taught
3. Add new assignments for the upcoming term
4. Verify with teachers that they see correct data

#### Task: Handle a teacher covering for another

1. Add temporary assignments to the covering teacher
2. The covering teacher will immediately gain access
3. Remove the assignments when the regular teacher returns

---

## For Teachers

Teachers have restricted access based on their class and subject assignments.

### Viewing Your Assignments

1. Log in with your teacher account
2. Click **"My Assignments"** in the navigation menu
3. You'll see:
   - **My Classes:** All classes you're assigned to teach, with student counts
   - **My Subjects:** All subjects you're assigned to teach

**Example Display:**
```
My Classes:
- Grade 9 (25 students)
- Grade 10 (30 students)

My Subjects:
- Mathematics
- Science
```

### Viewing Your Students

1. Click **"Students"** in the navigation menu
2. You'll see only students from your assigned classes
3. The page shows which classes you're viewing

**If you have no class assignments:**
- The student list will be empty
- A message will explain that you have no class assignments
- Contact your administrator to request assignments

### Entering Grades

#### Using Assessment Entry Form

1. Click **"Assessment Entry"** in the navigation menu
2. Select a term from the dropdown
3. The subject dropdown shows only your assigned subjects
4. The student dropdown shows only students from your assigned classes
5. Enter the assessment type and score
6. Click **"Submit"**

**If you have no assignments:**
- The subject dropdown will be empty
- A message will explain that you need subject assignments
- Contact your administrator

#### Using Assessment Grid

1. Click **"Assessment Grid"** in the navigation menu
2. Select a term from the dropdown
3. The subject dropdown shows only your assigned subjects
4. The class dropdown shows only your assigned classes
5. Enter grades in the grid
6. Click **"Save All"**

**Access Control:**
- You can only enter grades for subjects you're assigned to teach
- You can only enter grades for students in your assigned classes
- Attempting to enter grades outside your assignments will show an error

### Generating Reports

#### Student Reports

1. Click **"Student Report"** in the navigation menu
2. The student dropdown shows only students from your assigned classes
3. Select a student and term
4. Click **"Generate Report"**

#### Class Reports

1. Click **"Class Report"** in the navigation menu
2. The class dropdown shows only your assigned classes
3. Select a class and term
4. Click **"Generate Report"**

**If you have no class assignments:**
- The dropdowns will be empty
- A message will explain that you need class assignments
- Contact your administrator

### Understanding Access Restrictions

As a teacher, you can only access:

✅ **Students in your assigned classes**
- View student list
- View student details
- Generate student reports

✅ **Grades for your assigned subjects**
- Enter assessments
- View assessment history
- Edit your own grade entries

✅ **Reports for your assigned classes**
- Student reports
- Class reports
- Term reports

❌ **You cannot access:**
- Students in classes you're not assigned to
- Grades for subjects you're not assigned to teach
- Reports for classes you're not assigned to
- Teacher assignment management (admin only)

### What to Do If You Can't Access Something

**Problem:** "I can't see any students"
- **Cause:** You have no class assignments
- **Solution:** Contact your administrator to request class assignments

**Problem:** "I can't enter grades for a subject"
- **Cause:** You're not assigned to teach that subject
- **Solution:** Contact your administrator to request subject assignment

**Problem:** "I get an 'Access Denied' error"
- **Cause:** You're trying to access data outside your assignments
- **Solution:** Verify your assignments in "My Assignments" and contact your administrator if incorrect

**Problem:** "I should have access but don't"
- **Cause:** Assignments may not have been set up yet
- **Solution:** Contact your administrator to verify your assignments

### Tips for Teachers

1. **Check your assignments regularly**
   - Visit "My Assignments" to see your current classes and subjects
   - Verify the student counts match your expectations

2. **Report discrepancies immediately**
   - If you're missing access to a class or subject, contact your administrator
   - If you have access you shouldn't have, report it

3. **Use the filtered views**
   - The system automatically filters data to your assignments
   - This helps you focus on your students without distraction

4. **Plan ahead**
   - Check your assignments before entering grades
   - Ensure you have both class and subject assignments for your teaching schedule

---

## Access Restrictions

### How Access Control Works

The system enforces access control at multiple levels:

1. **UI Level:** Dropdowns and lists show only data you can access
2. **API Level:** Server validates all requests against your assignments
3. **Database Level:** Queries filter data based on your assignments

This multi-layered approach ensures security even if someone tries to bypass the UI.

### Admin vs. Teacher Access

| Feature | Admin Access | Teacher Access |
|---------|--------------|----------------|
| View all students | ✅ Yes | ❌ No - Only assigned classes |
| Enter grades for any subject | ✅ Yes | ❌ No - Only assigned subjects |
| Generate any report | ✅ Yes | ❌ No - Only assigned classes |
| Manage teacher assignments | ✅ Yes | ❌ No |
| View all teachers | ✅ Yes | ❌ No |
| Manage users | ✅ Yes | ❌ No |

### Assignment Requirements

To fully function as a teacher, you need:

**Minimum Requirements:**
- At least one class assignment (to see students)
- At least one subject assignment (to enter grades)

**Recommended Setup:**
- All classes you teach
- All subjects you teach
- Assignments should match your actual teaching schedule

**Example Scenarios:**

**Scenario 1: Teacher with only class assignments**
- Can view students in assigned classes
- Cannot enter any grades (no subject assignments)
- Can generate reports for assigned classes

**Scenario 2: Teacher with only subject assignments**
- Cannot view any students (no class assignments)
- Cannot enter grades (needs students to grade)
- Cannot generate reports

**Scenario 3: Teacher with both assignments**
- Can view students in assigned classes
- Can enter grades for assigned subjects
- Can generate reports for assigned classes
- ✅ Fully functional

### Security Features

1. **Automatic Filtering**
   - All data is automatically filtered to your assignments
   - You never see data you shouldn't access

2. **Access Logging**
   - Unauthorized access attempts are logged
   - Administrators can review access logs for security auditing

3. **Real-time Validation**
   - Every action is validated against your current assignments
   - Assignments changes take effect immediately

4. **Error Messages**
   - Clear error messages explain why access was denied
   - Helps you understand what assignments you need

---

## Troubleshooting

### For Administrators

#### Problem: Teacher reports they can't see students

**Diagnosis:**
1. Go to Teacher Assignments
2. Select the teacher
3. Check if they have class assignments

**Solution:**
- If no class assignments: Assign them to the appropriate classes
- If assignments exist: Verify the class IDs are correct
- Ask teacher to log out and log back in

#### Problem: Teacher can't enter grades

**Diagnosis:**
1. Check if teacher has subject assignments
2. Check if teacher has class assignments (needs both)

**Solution:**
- Assign missing classes or subjects
- Verify assignments match their teaching schedule

#### Problem: Duplicate assignment error

**Diagnosis:**
- The teacher is already assigned to that class or subject

**Solution:**
- Check existing assignments before creating new ones
- Use the filter to see current assignments
- Remove old assignment if you need to recreate it

#### Problem: Teacher has too much access

**Diagnosis:**
- Teacher may have incorrect assignments
- Teacher may have admin role

**Solution:**
- Review and remove incorrect assignments
- Verify user role is "user" not "admin"

### For Teachers

#### Problem: Empty student list

**Diagnosis:**
- You have no class assignments

**Solution:**
- Contact your administrator
- Provide your teaching schedule
- Wait for assignments to be created

#### Problem: Cannot enter grades

**Diagnosis:**
- Missing subject assignments
- Missing class assignments
- Missing both

**Solution:**
- Check "My Assignments" to see what you have
- Contact administrator with specific subjects/classes you need

#### Problem: "Access Denied" error

**Diagnosis:**
- Attempting to access data outside your assignments

**Solution:**
- Verify the student/class/subject is in your assignments
- If it should be, contact administrator
- If it shouldn't be, you don't have access to that data

#### Problem: Assignments don't match schedule

**Diagnosis:**
- Assignments may be outdated or incorrect

**Solution:**
- Contact administrator with your current teaching schedule
- Request updates to your assignments

### Common Error Messages

| Error Message | Meaning | Solution |
|---------------|---------|----------|
| "You have no class assignments" | No classes assigned | Contact admin for class assignments |
| "You have no subject assignments" | No subjects assigned | Contact admin for subject assignments |
| "Access denied. You do not have permission to access this student." | Student not in your assigned classes | Verify assignments or contact admin |
| "Access denied. You are not assigned to teach this subject." | Subject not in your assignments | Verify assignments or contact admin |
| "Admin access required" | Trying to access admin-only feature | This feature is for administrators only |

---

## Frequently Asked Questions

### For Administrators

**Q: Can a teacher be assigned to multiple classes?**
A: Yes, teachers can be assigned to as many classes as needed.

**Q: Can multiple teachers be assigned to the same class?**
A: Yes, multiple teachers can share access to the same class.

**Q: What happens when I remove an assignment?**
A: The teacher immediately loses access to that class or subject. They won't be able to view students or enter grades for removed assignments.

**Q: Can I assign teachers for specific terms?**
A: Currently, assignments are not term-specific. You'll need to manually update assignments each term.

**Q: Do admins need assignments?**
A: No, admins have unrestricted access to all data regardless of assignments.

**Q: How do I set up a substitute teacher?**
A: Assign the substitute teacher to the same classes and subjects as the regular teacher. Remove the assignments when the regular teacher returns.

**Q: Can I bulk remove assignments?**
A: Currently, assignments must be removed individually. Use the Remove button next to each assignment.

**Q: What if I accidentally remove an assignment?**
A: Simply create the assignment again. There's no permanent deletion or history loss.

### For Teachers

**Q: Why can't I see all students?**
A: You can only see students in your assigned classes. This is by design to help you focus on your students.

**Q: Can I request assignments myself?**
A: No, only administrators can create assignments. Contact your administrator with your teaching schedule.

**Q: What if my schedule changes mid-term?**
A: Contact your administrator to update your assignments. Changes take effect immediately.

**Q: Can I see what other teachers are assigned to?**
A: No, only administrators can view all teacher assignments.

**Q: Why do I need both class and subject assignments?**
A: Class assignments give you access to students. Subject assignments give you permission to enter grades. You need both to fully function.

**Q: Can I enter grades for students not in my classes?**
A: No, you can only enter grades for students in your assigned classes and for your assigned subjects.

**Q: What if I'm teaching a new subject this term?**
A: Contact your administrator to add the subject to your assignments.

**Q: Do my assignments carry over to the next term?**
A: Yes, assignments persist until an administrator removes them. You may need to request updates for new terms.

### General Questions

**Q: Is this feature required?**
A: Yes, for schools with multiple teachers. It ensures data security and proper access control.

**Q: Can assignments be imported from a file?**
A: Not currently. Assignments must be created through the UI or API.

**Q: Are assignments logged?**
A: Yes, assignment creation and deletion are logged. Unauthorized access attempts are also logged.

**Q: Can I export assignment data?**
A: Not directly through the UI. Administrators can query the database or use the API.

**Q: What happens to grades if a teacher's assignment is removed?**
A: Existing grades remain in the system. The teacher just can't access or modify them anymore.

---

## Getting Help

### For Administrators

If you need help with teacher assignments:

1. Review this user guide
2. Check the API documentation (`backend/api/TEACHER_ASSIGNMENTS_API.md`)
3. Review the database schema documentation
4. Check the test files for examples
5. Contact system support

### For Teachers

If you need help:

1. Review this user guide
2. Check "My Assignments" to verify your current assignments
3. Contact your school administrator
4. Provide specific details about what you're trying to access

### Support Resources

- **API Documentation:** `backend/api/TEACHER_ASSIGNMENTS_API.md`
- **Database Schema:** See Database Schema Documentation
- **Test Files:** `backend/test_teacher_assignments.php`, `backend/test_access_control.php`
- **Implementation Details:** `.kiro/specs/teacher-assignments/design.md`

---

## Appendix: Quick Reference

### Admin Quick Actions

| Task | Steps |
|------|-------|
| Assign teacher to class | Select teacher → + Add Class → Select class → Assign |
| Assign teacher to subject | Select teacher → + Add Subject → Select subject → Assign |
| Bulk assign | Select teacher → Bulk Assign → Check boxes → Assign Selected |
| Remove assignment | Find assignment → Click Remove → Confirm |
| View all assignments | Go to Teacher Assignments page |

### Teacher Quick Actions

| Task | Steps |
|------|-------|
| View assignments | Click "My Assignments" |
| View students | Click "Students" (filtered to your classes) |
| Enter grades | Click "Assessment Entry" or "Assessment Grid" |
| Generate report | Click "Student Report" or "Class Report" |

### Access Control Summary

| User Role | Student Access | Grade Entry | Report Generation | Assignment Management |
|-----------|----------------|-------------|-------------------|----------------------|
| Admin | All students | All subjects | All reports | Yes |
| Teacher | Assigned classes only | Assigned subjects only | Assigned classes only | No |

---

**Document Version:** 1.0  
**Last Updated:** November 3, 2024  
**Feature Version:** Teacher Assignments v1.0
