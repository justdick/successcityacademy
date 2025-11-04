# Student Management System

A web-based application for managing student information and grades with role-based authentication.

## Features

- User authentication with JWT
- Role-based access control (Admin and User roles)
- Student management (CRUD operations)
- Grade recording and retrieval
- Subject and class level management
- Pre-filled subjects and class levels

## Technology Stack

- **Backend**: PHP with MySQL
- **Frontend**: React with Tailwind CSS
- **Authentication**: JWT (JSON Web Tokens)
- **Database**: MySQL

## Project Structure

```
student-management-system/
├── backend/
│   ├── api/              # API router
│   ├── config/           # Database and configuration files
│   ├── controllers/      # API controllers
│   ├── database/         # SQL schema and seed files
│   ├── middleware/       # Authentication middleware
│   ├── models/           # Data models
│   └── utils/            # Utility functions (JWT)
├── frontend/
│   ├── public/           # Static files
│   └── src/
│       ├── components/   # React components
│       ├── context/      # React context (Auth)
│       └── services/     # API services
└── README.md
```

## Setup Instructions

### Database Setup

1. Create the database and tables:
```bash
mysql -u root -p < backend/database/schema.sql
```

2. Seed the database with initial data:
```bash
mysql -u root -p < backend/database/seed.sql
```

### Default Admin Credentials

- **Username**: admin
- **Password**: admin123

### Backend Configuration

Update the database credentials in `backend/config/database.php` if needed:
- Host: localhost
- Database: student_management
- Username: root
- Password: (empty by default)

Update the JWT secret in `backend/config/config.php` for production use.

### Frontend Setup

Instructions will be added once the React application is initialized.

## Requirements

- PHP 7.4+
- MySQL 5.7+ or MariaDB
- Node.js 16+ (for frontend)
- PDO MySQL extension for PHP

## Security Notes

- Change the JWT secret key in production
- Use HTTPS in production
- Update database credentials for production environment
- The default admin password should be changed after first login
