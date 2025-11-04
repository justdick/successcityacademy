# Student Management System - Quick Start Guide

## 🚀 Getting Started in 5 Minutes

### Step 0: Database Setup (First Time Only)

**Fresh Installation:**
```bash
php backend/migrate_fresh_seed.php
```

This will:
- Create the database with all tables
- Seed sample data (30 students, 10 subjects, 6 terms, etc.)
- Create default admin and teacher accounts

**Already have data? Just reseed:**
```bash
php backend/seed.php
```

See [DATABASE_SEEDING.md](backend/DATABASE_SEEDING.md) for detailed seeding documentation.

### Step 1: Access the System

1. Make sure WAMP is running (green icon in system tray)
2. Open your browser and go to: `http://localhost/studentmgt/frontend/`
3. You should see the login page

### Step 2: Login

Use the default admin credentials (created by seeder):
- **Username:** `admin`
- **Password:** `admin123`

Or use a teacher account:
- **Username:** `teacher1`
- **Password:** `teacher123`

Click **"Login"**

### Step 3: Your First Student

1. Click **"Students"** in the top navigation
2. Click the **"Add New Student"** button
3. Fill in the form:
   ```
   Student ID: S001
   Name: John Doe
   Class Level: Grade 10 (select from dropdown)
   ```
4. Click **"Add Student"**
5. You should see "Student added successfully!" message

### Step 4: Record a Grade

1. Click **"Grades"** in the navigation
2. Click **"Add Grade"** button
3. Fill in the form:
   ```
   Student: John Doe (S001)
   Subject: Mathematics
   Mark: 85
   ```
4. Click **"Add Grade"**
5. You should see "Grade added successfully!" message

### Step 5: View Grades

1. Stay on the **"Grades"** page
2. Use the **"Filter by Student"** dropdown
3. Select **"John Doe (S001)"**
4. You'll see all grades for that student

## 🎯 New Feature: Termly Exam Reports

The system now includes comprehensive termly exam reports with CA and exam marks!

### Quick Tour of Termly Reports

1. **View Terms:** Navigate to **Terms** to see academic terms (Term 1, 2, 3)
2. **Configure Weightings:** Go to **Subject Weightings** to set CA/Exam percentages
3. **Enter Assessments:** Use **Assessments** to record CA and exam marks
4. **View Reports:** 
   - **Student Reports:** Individual student term reports with averages
   - **Class Reports:** View all students in a class
   - **Assessment Summary:** Dashboard showing completion status
5. **Export PDFs:** Generate printable report cards

### Sample Workflow

1. Navigate to **Assessments**
2. Select a term (e.g., "Term 1 2024/2025")
3. Select a student (e.g., "John Smith STU2024001")
4. Select a subject (e.g., "Mathematics")
5. Enter marks:
   - CA Mark: 28 (out of 30%)
   - Exam Mark: 65 (out of 70%)
6. System automatically calculates final mark: 93%
7. View the student's report to see all subjects and term average

See [TERMLY_REPORTS_TESTING.md](backend/TERMLY_REPORTS_TESTING.md) for comprehensive testing documentation.

## 🎯 Common Tasks

### Add More Students

Navigate to **Students** → **Add New Student**

Example students to add:
```
Student ID: S002, Name: Jane Smith, Class: Grade 10
Student ID: S003, Name: Bob Johnson, Class: Grade 11
Student ID: S004, Name: Alice Williams, Class: Grade 12
```

### Add More Grades

Navigate to **Grades** → **Add Grade**

Example grades to add:
```
Student: John Doe, Subject: English, Mark: 78
Student: John Doe, Subject: Science, Mark: 92
Student: Jane Smith, Subject: Mathematics, Mark: 88
```

### Create a New User Account

1. Navigate to **Users** (admin only)
2. Click **"Add User"**
3. Create a teacher account:
   ```
   Username: teacher1
   Password: teacher123
   Role: user
   ```
4. Click **"Create User"**
5. Logout and try logging in with the new account

### Add Custom Subjects

1. Navigate to **Subjects** (admin only)
2. Click **"Add Subject"**
3. Add subjects like:
   - Computer Science
   - History
   - Geography
   - Art
4. These will now appear in the grade recording dropdown

### Add Custom Class Levels

1. Navigate to **Class Levels** (admin only)
2. Click **"Add Class Level"**
3. Add levels like:
   - Grade 13
   - Pre-K
   - Kindergarten

## 📊 Sample Data Workflow

Here's a complete example to populate your system:

### 1. Add Subjects (Admin)
- Computer Science
- Physical Education
- Art

### 2. Add Class Levels (Admin)
- Grade 9
- Grade 13

### 3. Add Students
```
S005 | Michael Brown | Grade 9
S006 | Sarah Davis | Grade 11
S007 | David Wilson | Grade 12
```

### 4. Record Grades
```
Michael Brown | Mathematics | 75
Michael Brown | English | 82
Michael Brown | Science | 88
Sarah Davis | Mathematics | 91
Sarah Davis | Computer Science | 95
David Wilson | English | 79
David Wilson | Science | 86
```

### 5. View Reports
- Go to **Grades** page
- Filter by each student to see their complete grade report

## 🔐 Security Features

- **Authentication Required:** All pages require login
- **Role-Based Access:** Regular users cannot access admin features
- **Session Management:** Automatic logout on browser close
- **Password Hashing:** All passwords are securely hashed

## 🛠️ Troubleshooting

### Can't Login?
- Check WAMP is running (green icon)
- Verify database is running in phpMyAdmin
- Try default credentials: admin/admin123

### Can't Add Student?
- Make sure Student ID is unique
- All fields must be filled
- Select a valid class level

### Can't Add Grade?
- Mark must be between 0 and 100
- Student must exist
- Subject must exist

### Can't Delete Subject/Class Level?
- Cannot delete if in use
- Remove all grades for that subject first
- Remove all students from that class level first

## 📱 Browser Compatibility

Works best with:
- Chrome (recommended)
- Firefox
- Edge
- Safari

## 🎓 Tips for Best Use

1. **Start with Setup:** Add all subjects and class levels before adding students
2. **Unique IDs:** Use a consistent format for student IDs (e.g., S001, S002)
3. **Regular Backups:** Export your database regularly from phpMyAdmin
4. **User Accounts:** Create separate accounts for different teachers
5. **Grade Entry:** Enter grades regularly to keep records up to date

## 📞 Need Help?

- Check the **TESTING.md** file for technical details
- Review the **requirements.md** and **design.md** in `.kiro/specs/student-management-system/`
- Run integration tests: `php backend/integration_tests.php`

## 🎉 You're Ready!

You now know how to:
- ✅ Login to the system
- ✅ Add students
- ✅ Record grades
- ✅ View student records
- ✅ Manage subjects and class levels
- ✅ Create user accounts

Start using the system and explore all the features!
