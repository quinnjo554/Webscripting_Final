-- Create the database
CREATE DATABASE IF NOT EXISTS scheduling_system;
USE scheduling_system;

-- Users table (instructors and students)
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Will store hashed passwords
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('student', 'instructor') NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Instructor's available time slots
CREATE TABLE IF NOT EXISTS available_slots (
    slot_id INT AUTO_INCREMENT PRIMARY KEY,
    instructor_id INT NOT NULL,
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_booked BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (instructor_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_slot (instructor_id, date, start_time)
);

-- Student appointments
CREATE TABLE IF NOT EXISTS appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    slot_id INT NOT NULL,
    student_id INT NOT NULL,
    project_name VARCHAR(255) NOT NULL,
    group_members TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (slot_id) REFERENCES available_slots(slot_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Insert demo users
INSERT INTO users (username, password, email, role, full_name) VALUES
('instructor1', '$2y$10$j8DjCBrSIAQMYUqG.6Rh/.Mvr3g.QMODQm9LprJ7spebQUUwkPPKe', 'instructor@example.com', 'instructor', 'Dr. Demo'),
('student1', '$2y$10$j8DjCBrSIAQMYUqG.6Rh/.Mvr3g.QMODQm9LprJ7spebQUUwkPPKe', 'student1@example.com', 'student', 'Demo Student 1'),
('student2', '$2y$10$j8DjCBrSIAQMYUqG.6Rh/.Mvr3g.QMODQm9LprJ7spebQUUwkPPKe', 'student2@example.com', 'student', 'Demo Student 2');
-- Note: All passwords are 'password123' (hashed with bcrypt)

-- Insert sample available slots (for testing)
INSERT INTO available_slots (instructor_id, date, start_time, end_time) VALUES
(1, CURDATE(), '09:00:00', '09:20:00'),
(1, CURDATE(), '09:20:00', '09:40:00'),
(1, CURDATE(), '09:40:00', '10:00:00'),
(1, CURDATE() + INTERVAL 1 DAY, '14:00:00', '14:20:00'),
(1, CURDATE() + INTERVAL 1 DAY, '14:20:00', '14:40:00'),
(1, CURDATE() + INTERVAL 1 DAY, '14:40:00', '15:00:00'),
(1, CURDATE() + INTERVAL 2 DAY, '10:00:00', '10:20:00'),
(1, CURDATE() + INTERVAL 2 DAY, '10:20:00', '10:40:00'),
(1, CURDATE() + INTERVAL 2 DAY, '10:40:00', '11:00:00'),
(1, CURDATE() + INTERVAL 3 DAY, '13:00:00', '13:20:00');

-- Book some sample appointments
UPDATE available_slots SET is_booked = TRUE WHERE slot_id IN (1, 4);

INSERT INTO appointments (slot_id, student_id, project_name, group_members) VALUES
(1, 2, 'Web Development Project', 'Demo Student 1, Alice Smith'),
(4, 3, 'Database Design Project', 'Demo Student 2, Bob Johnson');
