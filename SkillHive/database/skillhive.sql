-- =========================================================
-- SkillHive Database Schema
-- Import this file in phpMyAdmin (XAMPP) to set everything up
-- =========================================================

CREATE DATABASE IF NOT EXISTS skillhive;
USE skillhive;

-- ---------------------------------------------------------
-- Users
-- ---------------------------------------------------------
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  department VARCHAR(100) DEFAULT '',
  year VARCHAR(20) DEFAULT '',
  bio TEXT,
  mode_preference ENUM('online','in-person','both') DEFAULT 'online',
  onboarding_complete TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------
-- Skills (shared list, students can add new ones)
-- ---------------------------------------------------------
CREATE TABLE skills (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  created_by INT,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ---------------------------------------------------------
-- Skills a user can teach
-- ---------------------------------------------------------
CREATE TABLE user_skills (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  skill_id INT NOT NULL,
  level ENUM('Beginner','Intermediate','Advanced') NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE,
  UNIQUE KEY unique_user_skill (user_id, skill_id)
);

-- ---------------------------------------------------------
-- Skills a user wants to learn
-- ---------------------------------------------------------
CREATE TABLE learning_goals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  skill_id INT NOT NULL,
  target_level ENUM('Beginner','Intermediate','Advanced') NOT NULL,
  priority ENUM('Low','Medium','High') DEFAULT 'Medium',
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE,
  UNIQUE KEY unique_user_goal (user_id, skill_id)
);

-- ---------------------------------------------------------
-- Connection requests between students
-- ---------------------------------------------------------
CREATE TABLE connections (
  id INT AUTO_INCREMENT PRIMARY KEY,
  requester_id INT NOT NULL,
  receiver_id INT NOT NULL,
  status ENUM('pending','accepted','rejected') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (requester_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_connection (requester_id, receiver_id)
);

-- ---------------------------------------------------------
-- Learning sessions
-- ---------------------------------------------------------
CREATE TABLE sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  connection_id INT NOT NULL,
  teacher_id INT NOT NULL,
  learner_id INT NOT NULL,
  skill_id INT NOT NULL,
  session_date DATE NOT NULL,
  session_time TIME NOT NULL,
  mode ENUM('online','in-person') NOT NULL,
  meeting_link VARCHAR(255),
  status ENUM('scheduled','completed','cancelled') DEFAULT 'scheduled',
  teacher_confirmed TINYINT(1) DEFAULT 0,
  learner_confirmed TINYINT(1) DEFAULT 0,
  FOREIGN KEY (connection_id) REFERENCES connections(id) ON DELETE CASCADE,
  FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (learner_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
);

-- ---------------------------------------------------------
-- Reviews after completed sessions
-- ---------------------------------------------------------
CREATE TABLE reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id INT NOT NULL,
  reviewer_id INT NOT NULL,
  reviewee_id INT NOT NULL,
  rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  feedback TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
  FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (reviewee_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_review (session_id, reviewer_id)
);

-- =========================================================
-- SEED DATA — so the app looks alive immediately
-- =========================================================

-- Common skills
INSERT INTO skills (name) VALUES
('Java'),('Python'),('React'),('Node.js'),('C++'),('Figma'),
('Public Speaking'),('Excel'),('Photography'),('Video Editing'),
('UI/UX Design'),('Machine Learning'),('DSA'),('Spring Boot'),
('Digital Marketing'),('Content Writing'),('Guitar'),('Photoshop'),
('SQL'),('AWS'),('Git/GitHub'),('Data Analysis'),('Flutter'),
('Communication Skills'),('Resume Building');

-- Demo users
-- All passwords are: password123
-- (hash below is for 'password123' using PHP password_hash with default algo)
INSERT INTO users (name, email, password_hash, department, year, bio, mode_preference, onboarding_complete) VALUES
('Priya Sharma', 'priya@college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.YO0M9OTpGl.4b/Kd/6ie1o0k5xL5bqXlq', 'CSE', '2nd Year', 'Love coding and teaching juniors.', 'online', 1),
('Arjun Mehta', 'arjun@college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.YO0M9OTpGl.4b/Kd/6ie1o0k5xL5bqXlq', 'IT', '3rd Year', 'Frontend enthusiast, React lover.', 'online', 1),
('Meera Iyer', 'meera@college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.YO0M9OTpGl.4b/Kd/6ie1o0k5xL5bqXlq', 'ECE', '2nd Year', 'Designer exploring ML.', 'both', 1),
('Rahul Verma', 'rahul@college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.YO0M9OTpGl.4b/Kd/6ie1o0k5xL5bqXlq', 'CSE', '4th Year', 'DSA and competitive programming.', 'online', 1),
('Sneha Reddy', 'sneha@college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.YO0M9OTpGl.4b/Kd/6ie1o0k5xL5bqXlq', 'MECH', '3rd Year', 'Photography and content creation.', 'in-person', 1),
('Karthik Raja', 'karthik@college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.YO0M9OTpGl.4b/Kd/6ie1o0k5xL5bqXlq', 'IT', '2nd Year', 'Backend developer, Java and Spring Boot.', 'online', 1),
('Divya Nair', 'divya@college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.YO0M9OTpGl.4b/Kd/6ie1o0k5xL5bqXlq', 'CSE', '1st Year', 'Just starting out, eager to learn.', 'online', 1),
('Vikram Singh', 'vikram@college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.YO0M9OTpGl.4b/Kd/6ie1o0k5xL5bqXlq', 'ECE', '3rd Year', 'AWS and cloud enthusiast.', 'both', 1);

-- Teaching skills (user_skills)
INSERT INTO user_skills (user_id, skill_id, level) VALUES
(1, 1, 'Advanced'),      -- Priya teaches Java
(1, 2, 'Intermediate'),  -- Priya teaches Python
(2, 3, 'Advanced'),      -- Arjun teaches React
(2, 4, 'Intermediate'),  -- Arjun teaches Node.js
(3, 3, 'Intermediate'),  -- Meera teaches React
(3, 6, 'Advanced'),      -- Meera teaches Figma
(4, 13, 'Advanced'),     -- Rahul teaches DSA
(4, 1, 'Intermediate'),  -- Rahul teaches Java
(5, 9, 'Advanced'),      -- Sneha teaches Photography
(5, 10, 'Intermediate'), -- Sneha teaches Video Editing
(6, 1, 'Advanced'),      -- Karthik teaches Java
(6, 14, 'Advanced'),     -- Karthik teaches Spring Boot
(7, 19, 'Beginner'),     -- Divya teaches SQL (learning to teach)
(8, 20, 'Advanced'),     -- Vikram teaches AWS
(8, 21, 'Intermediate'); -- Vikram teaches Git/GitHub

-- Learning goals
INSERT INTO learning_goals (user_id, skill_id, target_level, priority) VALUES
(1, 3, 'Beginner', 'High'),   -- Priya wants React
(1, 4, 'Beginner', 'Medium'), -- Priya wants Node.js
(2, 1, 'Beginner', 'High'),   -- Arjun wants Java
(2, 2, 'Beginner', 'Medium'), -- Arjun wants Python
(3, 12, 'Beginner', 'High'),  -- Meera wants Machine Learning
(4, 3, 'Beginner', 'Low'),    -- Rahul wants React
(5, 15, 'Beginner', 'Medium'),-- Sneha wants Digital Marketing
(6, 3, 'Beginner', 'High'),   -- Karthik wants React
(7, 1, 'Beginner', 'High'),   -- Divya wants Java
(7, 13, 'Beginner', 'High'),  -- Divya wants DSA
(8, 22, 'Intermediate', 'Medium'); -- Vikram wants Data Analysis
