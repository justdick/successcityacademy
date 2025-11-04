-- Student Management System Seed Data
-- This file populates the database with initial data

USE student_management;

-- Insert default admin user
-- Username: admin
-- Password: admin123
-- Note: The password hash is generated using PHP password_hash() with PASSWORD_DEFAULT (bcrypt)
INSERT INTO users (username, password_hash, role) VALUES
('admin', '$2y$10$VDsBmiNDMtb.WOzdagwb0OM.3Wr6gchzMShHm7syR164vXxwMPuia', 'admin');

-- Insert pre-filled subjects
INSERT INTO subjects (name) VALUES
('Mathematics'),
('English'),
('Science'),
('History'),
('Geography'),
('Physics'),
('Chemistry'),
('Biology'),
('Computer Science'),
('Physical Education'),
('Art'),
('Music');

-- Insert pre-filled class levels
INSERT INTO class_levels (name) VALUES
('Grade 9'),
('Grade 10'),
('Grade 11'),
('Grade 12');
