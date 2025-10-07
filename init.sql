-- Create database (optional)
-- CREATE DATABASE grading_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE grading_portal;

DROP TABLE IF EXISTS grades;
DROP TABLE IF EXISTS parents;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS subjects;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('teacher','student','parent') NOT NULL
);

CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_no VARCHAR(30) NOT NULL,
  name VARCHAR(120) NOT NULL,
  class VARCHAR(50) NOT NULL,
  user_id INT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE parents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  child_student_id INT NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (child_student_id) REFERENCES students(id) ON DELETE CASCADE
);

CREATE TABLE subjects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL
);

CREATE TABLE grades (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  subject_id INT NOT NULL,
  period VARCHAR(10) NOT NULL,
  grade DECIMAL(5,2) NOT NULL,
  teacher_id INT NOT NULL,
  created_at DATETIME NOT NULL,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subjects(id),
  FOREIGN KEY (teacher_id) REFERENCES users(id)
);

-- Demo data
INSERT INTO users (name, email, password_hash, role) VALUES
('Teacher Tess', 'teacher@example.com', '$2y$10$2J4Jq0i6x8Cw1Z1g3Yk0yO1oYwFjC7pO8d3mF9Z7r0m3n1q5xRkzK', 'teacher'), -- password: password123
('Student Sam', 'student@example.com', '$2y$10$2J4Jq0i6x8Cw1Z1g3Yk0yO1oYwFjC7pO8d3mF9Z7r0m3n1q5xRkzK', 'student'), -- password: password123
('Parent Pat', 'parent@example.com', '$2y$10$2J4Jq0i6x8Cw1Z1g3Yk0yO1oYwFjC7pO8d3mF9Z7r0m3n1q5xRkzK', 'parent'); -- password: password123

INSERT INTO students (student_no, name, class, user_id) VALUES
('S-0001', 'Student Sam', 'Grade 10 - A', 2);

INSERT INTO parents (user_id, child_student_id) VALUES
(3, 1);

INSERT INTO subjects (name) VALUES
('Mathematics'), ('Science'), ('English'), ('Filipino'), ('AP'), ('MAPEH');

INSERT INTO grades (student_id, subject_id, period, grade, teacher_id, created_at) VALUES
(1, 1, 'Q1', 88.50, 1, NOW()),
(1, 2, 'Q1', 92.00, 1, NOW()),
(1, 3, 'Q1', 85.25, 1, NOW());
