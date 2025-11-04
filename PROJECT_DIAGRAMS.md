# Student Management System - Project Diagrams

## 1. System Architecture Diagram

```mermaid
graph TB
    subgraph "Client Layer"
        Browser[Web Browser]
    end
    
    subgraph "Frontend Layer"
        React[React Application<br/>Vite + Tailwind CSS]
        Router[React Router]
        AuthContext[Auth Context]
        APIService[API Service<br/>Axios]
    end
    
    subgraph "Backend Layer"
        API[API Router<br/>api/index.php]
        Auth[Auth Middleware<br/>JWT Validation]
        AccessControl[Teacher Access Control<br/>Role-Based Permissions]
        
        subgraph "Controllers"
            AuthCtrl[Auth Controller]
            StudentCtrl[Student Controller]
            GradeCtrl[Grade Controller]
            AssessmentCtrl[Assessment Controller]
            UserCtrl[User Controller]
            SubjectCtrl[Subject Controller]
            TermCtrl[Term Controller]
            ReportCtrl[Report Controller]
            BackupCtrl[Backup Controller]
        end
        
        subgraph "Models"
            UserModel[User Model]
            StudentModel[Student Model]
            GradeModel[Grade Model]
            AssessmentModel[Assessment Model]
        end
        
        subgraph "Services"
            ReportService[Report Service<br/>TCPDF]
            BackupService[Backup Service]
        end
        
        JWT[JWT Utility]
    end
    
    subgraph "Data Layer"
        MySQL[(MySQL Database<br/>student_management)]
    end
    
    Browser --> React
    React --> Router
    React --> AuthContext
    React --> APIService
    
    APIService -->|HTTP/JSON| API
    API --> Auth
    Auth --> AccessControl
    AccessControl --> Controllers
    
    Controllers --> Models
    Controllers --> Services
    Controllers --> JWT
    
    Models --> MySQL
    Services --> MySQL
    
    style Browser fill:#e1f5ff
    style React fill:#61dafb
    style API fill:#777bb4
    style MySQL fill:#00758f
```

## 2. Data Flow Diagram (Level 0 - Context Diagram)

```mermaid
graph LR
    Admin[Admin User]
    Teacher[Teacher User]
    
    System[Student Management<br/>System]
    
    DB[(Database)]
    
    Admin -->|Manage Users| System
    Admin -->|Manage Students| System
    Admin -->|Manage Subjects| System
    Admin -->|Manage Classes| System
    Admin -->|View Reports| System
    Admin -->|Backup/Restore| System
    
    Teacher -->|View Students| System
    Teacher -->|Record Assessments| System
    Teacher -->|View Reports| System
    
    System -->|Store/Retrieve Data| DB
    System -->|Authentication| Admin
    System -->|Authentication| Teacher
    System -->|Reports/Data| Admin
    System -->|Reports/Data| Teacher
    
    style Admin fill:#ff6b6b
    style Teacher fill:#4ecdc4
    style System fill:#95e1d3
    style DB fill:#f38181
```

## 3. Data Flow Diagram (Level 1 - Main Processes)

```mermaid
graph TB
    User[User]
    
    subgraph "Student Management System"
        P1[1.0<br/>Authentication<br/>Process]
        P2[2.0<br/>Student<br/>Management]
        P3[3.0<br/>Assessment<br/>Management]
        P4[4.0<br/>Report<br/>Generation]
        P5[5.0<br/>User<br/>Management]
        P6[6.0<br/>System<br/>Administration]
    end
    
    D1[(Users)]
    D2[(Students)]
    D3[(Assessments)]
    D4[(Terms)]
    D5[(Subjects)]
    D6[(Access Logs)]
    
    User -->|Login Credentials| P1
    P1 -->|JWT Token| User
    P1 -->|Verify| D1
    
    User -->|Student Data| P2
    P2 -->|Student Info| User
    P2 <-->|CRUD| D2
    
    User -->|Assessment Data| P3
    P3 -->|Assessment Results| User
    P3 <-->|CRUD| D3
    P3 -->|Read| D4
    P3 -->|Read| D5
    
    User -->|Report Request| P4
    P4 -->|PDF Report| User
    P4 -->|Read| D2
    P4 -->|Read| D3
    P4 -->|Read| D4
    P4 -->|Read| D5
    
    User -->|User Data| P5
    P5 -->|User Info| User
    P5 <-->|CRUD| D1
    
    User -->|Admin Actions| P6
    P6 -->|System Status| User
    P6 <-->|Manage| D5
    P6 -->|Log| D6
    
    style User fill:#4ecdc4
    style P1 fill:#ffe66d
    style P2 fill:#ffe66d
    style P3 fill:#ffe66d
    style P4 fill:#ffe66d
    style P5 fill:#ffe66d
    style P6 fill:#ffe66d
```

## 4. Entity Relationship Diagram (ERD)

```mermaid
erDiagram
    users ||--o{ teacher_class_assignments : "assigned to"
    users ||--o{ teacher_subject_assignments : "assigned to"
    users ||--o{ access_logs : "generates"
    
    class_levels ||--o{ students : "contains"
    class_levels ||--o{ teacher_class_assignments : "assigned"
    
    students ||--o{ term_assessments : "has"
    
    subjects ||--o{ term_assessments : "assessed in"
    subjects ||--|| subject_weightings : "has"
    subjects ||--o{ teacher_subject_assignments : "assigned"
    
    terms ||--o{ term_assessments : "belongs to"
    
    users {
        int id PK
        varchar username UK
        varchar password_hash
        enum role
        timestamp created_at
        timestamp updated_at
    }
    
    students {
        int id PK
        varchar student_id UK
        varchar name
        int class_level_id FK
        timestamp created_at
        timestamp updated_at
    }
    
    class_levels {
        int id PK
        varchar name UK
        timestamp created_at
    }
    
    subjects {
        int id PK
        varchar name UK
        timestamp created_at
    }
    
    terms {
        int id PK
        varchar name
        varchar academic_year
        date start_date
        date end_date
        boolean is_active
        timestamp created_at
    }
    
    term_assessments {
        int id PK
        varchar student_id FK
        int subject_id FK
        int term_id FK
        decimal ca_mark
        decimal exam_mark
        decimal final_mark
        timestamp created_at
        timestamp updated_at
    }
    
    subject_weightings {
        int id PK
        int subject_id FK
        decimal ca_percentage
        decimal exam_percentage
        timestamp created_at
        timestamp updated_at
    }
    
    teacher_class_assignments {
        int id PK
        int user_id FK
        int class_level_id FK
        timestamp created_at
    }
    
    teacher_subject_assignments {
        int id PK
        int user_id FK
        int subject_id FK
        timestamp created_at
    }
    
    access_logs {
        int id PK
        int user_id FK
        varchar resource_type
        varchar resource_id
        tinyint access_denied
        timestamp created_at
    }
```

## 5. Use Case Diagram

```mermaid
graph TB
    Admin[Admin User]
    Teacher[Teacher User]
    
    subgraph "Student Management System"
        UC1[Login/Logout]
        UC2[Manage Students]
        UC3[Manage Users]
        UC4[Manage Subjects]
        UC5[Manage Class Levels]
        UC6[Manage Terms]
        UC7[Record Assessments]
        UC8[View Student Reports]
        UC9[Generate PDF Reports]
        UC10[Configure Subject Weightings]
        UC11[Assign Teachers to Classes]
        UC12[Assign Teachers to Subjects]
        UC13[View Access Logs]
        UC14[Backup Database]
        UC15[Restore Database]
        UC16[View Assessment Summary]
    end
    
    Admin --> UC1
    Teacher --> UC1
    
    Admin --> UC2
    Teacher --> UC2
    
    Admin --> UC3
    Admin --> UC4
    Admin --> UC5
    Admin --> UC6
    
    Admin --> UC7
    Teacher --> UC7
    
    Admin --> UC8
    Teacher --> UC8
    
    Admin --> UC9
    Teacher --> UC9
    
    Admin --> UC10
    
    Admin --> UC11
    Admin --> UC12
    Admin --> UC13
    
    Admin --> UC14
    Admin --> UC15
    
    Admin --> UC16
    Teacher --> UC16
    
    UC7 -.includes.-> UC10
    UC8 -.includes.-> UC7
    UC9 -.includes.-> UC8
    
    style Admin fill:#ff6b6b
    style Teacher fill:#4ecdc4
    style UC1 fill:#a8e6cf
    style UC2 fill:#a8e6cf
    style UC3 fill:#ffd3b6
    style UC4 fill:#ffd3b6
    style UC5 fill:#ffd3b6
    style UC6 fill:#ffd3b6
    style UC7 fill:#ffaaa5
    style UC8 fill:#ffaaa5
    style UC9 fill:#ffaaa5
    style UC10 fill:#ffd3b6
    style UC11 fill:#ffd3b6
    style UC12 fill:#ffd3b6
    style UC13 fill:#ffd3b6
    style UC14 fill:#ff8b94
    style UC15 fill:#ff8b94
    style UC16 fill:#ffaaa5
```

## 6. System Flow Chart (User Authentication Flow)

```mermaid
flowchart TD
    Start([User Accesses System])
    CheckToken{Token<br/>Exists?}
    ValidateToken{Token<br/>Valid?}
    ShowLogin[Display Login Page]
    EnterCreds[User Enters Credentials]
    ValidateCreds{Credentials<br/>Valid?}
    GenerateToken[Generate JWT Token]
    StoreToken[Store Token in LocalStorage]
    ShowDashboard[Display Dashboard]
    ShowError[Show Error Message]
    CheckRole{User<br/>Role?}
    AdminDash[Show Admin Dashboard]
    TeacherDash[Show Teacher Dashboard]
    End([End])
    
    Start --> CheckToken
    CheckToken -->|No| ShowLogin
    CheckToken -->|Yes| ValidateToken
    ValidateToken -->|Invalid| ShowLogin
    ValidateToken -->|Valid| CheckRole
    
    ShowLogin --> EnterCreds
    EnterCreds --> ValidateCreds
    ValidateCreds -->|Invalid| ShowError
    ShowError --> ShowLogin
    ValidateCreds -->|Valid| GenerateToken
    GenerateToken --> StoreToken
    StoreToken --> CheckRole
    
    CheckRole -->|Admin| AdminDash
    CheckRole -->|Teacher| TeacherDash
    AdminDash --> End
    TeacherDash --> End
    
    style Start fill:#a8e6cf
    style End fill:#ff8b94
    style ShowLogin fill:#ffd3b6
    style ShowDashboard fill:#dcedc1
    style ShowError fill:#ffaaa5
```

## 7. Assessment Recording Flow Chart

```mermaid
flowchart TD
    Start([Start Assessment Recording])
    SelectTerm[Select Term]
    SelectStudent[Select Student]
    CheckAccess{Has Access<br/>to Student?}
    SelectSubject[Select Subject]
    CheckSubjectAccess{Has Access<br/>to Subject?}
    GetWeighting[Retrieve Subject Weighting]
    EnterCA[Enter CA Mark]
    ValidateCA{CA Mark<br/>Valid?}
    EnterExam[Enter Exam Mark]
    ValidateExam{Exam Mark<br/>Valid?}
    CalcFinal[Calculate Final Mark]
    CheckExists{Assessment<br/>Exists?}
    UpdateDB[Update Assessment]
    InsertDB[Insert Assessment]
    ShowSuccess[Show Success Message]
    ShowError[Show Error Message]
    LogAccess[Log Unauthorized Access]
    End([End])
    
    Start --> SelectTerm
    SelectTerm --> SelectStudent
    SelectStudent --> CheckAccess
    CheckAccess -->|No| LogAccess
    LogAccess --> ShowError
    CheckAccess -->|Yes| SelectSubject
    SelectSubject --> CheckSubjectAccess
    CheckSubjectAccess -->|No| LogAccess
    CheckSubjectAccess -->|Yes| GetWeighting
    GetWeighting --> EnterCA
    EnterCA --> ValidateCA
    ValidateCA -->|Invalid| ShowError
    ValidateCA -->|Valid| EnterExam
    EnterExam --> ValidateExam
    ValidateExam -->|Invalid| ShowError
    ValidateExam -->|Valid| CalcFinal
    CalcFinal --> CheckExists
    CheckExists -->|Yes| UpdateDB
    CheckExists -->|No| InsertDB
    UpdateDB --> ShowSuccess
    InsertDB --> ShowSuccess
    ShowSuccess --> End
    ShowError --> End
    
    style Start fill:#a8e6cf
    style End fill:#ff8b94
    style ShowSuccess fill:#dcedc1
    style ShowError fill:#ffaaa5
    style LogAccess fill:#ff8b94
```

## 8. Database Schema Diagram (Detailed)

```mermaid
graph TB
    subgraph "Core Tables"
        Users[users<br/>---<br/>PK: id<br/>UK: username<br/>password_hash<br/>role: admin/user<br/>timestamps]
        
        Students[students<br/>---<br/>PK: id<br/>UK: student_id<br/>name<br/>FK: class_level_id<br/>timestamps]
        
        ClassLevels[class_levels<br/>---<br/>PK: id<br/>UK: name<br/>created_at]
        
        Subjects[subjects<br/>---<br/>PK: id<br/>UK: name<br/>created_at]
    end
    
    subgraph "Assessment Tables"
        Terms[terms<br/>---<br/>PK: id<br/>name<br/>academic_year<br/>start_date<br/>end_date<br/>is_active<br/>created_at]
        
        Assessments[term_assessments<br/>---<br/>PK: id<br/>FK: student_id<br/>FK: subject_id<br/>FK: term_id<br/>ca_mark<br/>exam_mark<br/>final_mark: computed<br/>timestamps]
        
        Weightings[subject_weightings<br/>---<br/>PK: id<br/>FK: subject_id<br/>ca_percentage: 40%<br/>exam_percentage: 60%<br/>timestamps]
    end
    
    subgraph "Access Control Tables"
        TeacherClass[teacher_class_assignments<br/>---<br/>PK: id<br/>FK: user_id<br/>FK: class_level_id<br/>created_at]
        
        TeacherSubject[teacher_subject_assignments<br/>---<br/>PK: id<br/>FK: user_id<br/>FK: subject_id<br/>created_at]
        
        AccessLogs[access_logs<br/>---<br/>PK: id<br/>FK: user_id<br/>resource_type<br/>resource_id<br/>access_denied<br/>created_at]
    end
    
    ClassLevels -->|1:N| Students
    Students -->|1:N| Assessments
    Subjects -->|1:N| Assessments
    Subjects -->|1:1| Weightings
    Terms -->|1:N| Assessments
    
    Users -->|1:N| TeacherClass
    ClassLevels -->|1:N| TeacherClass
    
    Users -->|1:N| TeacherSubject
    Subjects -->|1:N| TeacherSubject
    
    Users -->|1:N| AccessLogs
    
    style Users fill:#ff6b6b
    style Students fill:#4ecdc4
    style ClassLevels fill:#95e1d3
    style Subjects fill:#95e1d3
    style Terms fill:#ffe66d
    style Assessments fill:#ffaaa5
    style Weightings fill:#ffd3b6
    style TeacherClass fill:#a8e6cf
    style TeacherSubject fill:#a8e6cf
    style AccessLogs fill:#dcedc1
```

## 9. Report Generation Flow Chart

```mermaid
flowchart TD
    Start([Start Report Generation])
    SelectType{Report<br/>Type?}
    SelectStudent[Select Student]
    SelectClass[Select Class]
    SelectTerm[Select Term]
    CheckStudentAccess{Has Student<br/>Access?}
    CheckClassAccess{Has Class<br/>Access?}
    FetchAssessments[Fetch Term Assessments]
    FetchWeightings[Fetch Subject Weightings]
    CalcAverages[Calculate Term Averages]
    CalcGrades[Calculate Letter Grades]
    FormatData[Format Report Data]
    GeneratePDF[Generate PDF using TCPDF]
    DownloadPDF[Download PDF]
    ShowError[Show Error Message]
    LogAccess[Log Unauthorized Access]
    End([End])
    
    Start --> SelectType
    SelectType -->|Student Report| SelectStudent
    SelectType -->|Class Report| SelectClass
    
    SelectStudent --> SelectTerm
    SelectClass --> SelectTerm
    
    SelectTerm --> CheckStudentAccess
    SelectTerm --> CheckClassAccess
    
    CheckStudentAccess -->|No| LogAccess
    CheckClassAccess -->|No| LogAccess
    LogAccess --> ShowError
    
    CheckStudentAccess -->|Yes| FetchAssessments
    CheckClassAccess -->|Yes| FetchAssessments
    
    FetchAssessments --> FetchWeightings
    FetchWeightings --> CalcAverages
    CalcAverages --> CalcGrades
    CalcGrades --> FormatData
    FormatData --> GeneratePDF
    GeneratePDF --> DownloadPDF
    DownloadPDF --> End
    ShowError --> End
    
    style Start fill:#a8e6cf
    style End fill:#ff8b94
    style GeneratePDF fill:#ffe66d
    style DownloadPDF fill:#dcedc1
    style ShowError fill:#ffaaa5
```

## 10. Access Control Flow Chart

```mermaid
flowchart TD
    Start([API Request Received])
    ValidateToken{JWT Token<br/>Valid?}
    ExtractUser[Extract User from Token]
    CheckRole{User<br/>Role?}
    AdminAccess[Grant Full Access]
    CheckResource{Resource<br/>Type?}
    
    CheckStudent[Check Student Access]
    CheckClass[Check Class Access]
    CheckSubject[Check Subject Access]
    
    GetAssignments[Get Teacher Assignments]
    ValidateAccess{Access<br/>Allowed?}
    
    GrantAccess[Process Request]
    DenyAccess[Return 403 Forbidden]
    LogDenial[Log Access Denial]
    Return401[Return 401 Unauthorized]
    End([End])
    
    Start --> ValidateToken
    ValidateToken -->|Invalid| Return401
    ValidateToken -->|Valid| ExtractUser
    ExtractUser --> CheckRole
    
    CheckRole -->|Admin| AdminAccess
    CheckRole -->|Teacher| CheckResource
    
    AdminAccess --> GrantAccess
    
    CheckResource -->|Student| CheckStudent
    CheckResource -->|Class| CheckClass
    CheckResource -->|Subject| CheckSubject
    
    CheckStudent --> GetAssignments
    CheckClass --> GetAssignments
    CheckSubject --> GetAssignments
    
    GetAssignments --> ValidateAccess
    ValidateAccess -->|Yes| GrantAccess
    ValidateAccess -->|No| LogDenial
    
    LogDenial --> DenyAccess
    
    GrantAccess --> End
    DenyAccess --> End
    Return401 --> End
    
    style Start fill:#a8e6cf
    style End fill:#ff8b94
    style GrantAccess fill:#dcedc1
    style DenyAccess fill:#ffaaa5
    style Return401 fill:#ffaaa5
    style AdminAccess fill:#ffe66d
```

---

## Diagram Descriptions

### 1. System Architecture Diagram
Shows the three-tier architecture: Client (Browser), Frontend (React), Backend (PHP), and Data (MySQL) layers with all major components and their relationships.

### 2. Data Flow Diagram (Level 0)
Context diagram showing external entities (Admin, Teacher) and their interactions with the system and database.

### 3. Data Flow Diagram (Level 1)
Detailed process breakdown showing six main processes: Authentication, Student Management, Assessment Management, Report Generation, User Management, and System Administration.

### 4. Entity Relationship Diagram
Complete database schema showing all tables, their attributes, primary keys (PK), foreign keys (FK), unique keys (UK), and relationships with cardinality.

### 5. Use Case Diagram
Shows all system use cases organized by user role (Admin vs Teacher) with include relationships between related use cases.

### 6. System Flow Chart (Authentication)
Detailed flow of user authentication process including token validation, credential verification, and role-based dashboard routing.

### 7. Assessment Recording Flow Chart
Step-by-step process for recording student assessments including access control checks, validation, and database operations.

### 8. Database Schema Diagram
Visual representation of database tables grouped by functionality (Core, Assessment, Access Control) with detailed field listings.

### 9. Report Generation Flow Chart
Process flow for generating PDF reports including data fetching, calculations, formatting, and PDF generation using TCPDF.

### 10. Access Control Flow Chart
Detailed flow of the role-based access control system showing how teacher permissions are validated for different resource types.

---

## Technology Stack Summary

**Frontend:**
- React 18.2
- React Router 6.20
- Axios for API calls
- Tailwind CSS 4.1 for styling
- Vite 5.0 as build tool

**Backend:**
- PHP 7.4+
- Custom MVC architecture
- JWT for authentication
- TCPDF for PDF generation
- PDO for database access

**Database:**
- MySQL 5.7+ / MariaDB
- InnoDB engine
- UTF-8 character set

**Security:**
- JWT token-based authentication
- Role-based access control (RBAC)
- Teacher-level resource permissions
- Password hashing with PHP password_hash()
- SQL injection prevention with prepared statements
- Access logging for audit trails
