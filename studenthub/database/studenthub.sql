-- StudentHub database (Practical 8). Import in phpMyAdmin.
CREATE DATABASE IF NOT EXISTS studenthub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE studenthub;
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS audit_logs, remember_tokens, registrations, events, students, users;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','student') NOT NULL DEFAULT 'student',
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  last_login DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE students (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  name VARCHAR(80) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  mobile CHAR(10) NOT NULL,
  course VARCHAR(40) NOT NULL,
  year TINYINT NOT NULL,
  gender ENUM('Male','Female','Other') NOT NULL,
  status ENUM('active','deleted') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE events (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(120) NOT NULL,
  description TEXT,
  category VARCHAR(40) NOT NULL DEFAULT 'General',
  event_date DATE NOT NULL,
  venue VARCHAR(120) NOT NULL,
  status ENUM('open','closed') NOT NULL DEFAULT 'open',
  poster VARCHAR(255) NULL,
  created_by INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE registrations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  event_id INT UNSIGNED NOT NULL,
  registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_reg (student_id, event_id),
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE remember_tokens (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  selector CHAR(24) NOT NULL UNIQUE,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  action VARCHAR(60) NOT NULL,
  details VARCHAR(255) NULL,
  ip_address VARCHAR(45) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Seed data. Admin: admin@studenthub.local / Admin@123 ; Student: student@studenthub.local / Student@123
INSERT INTO users (name,email,password_hash,role) VALUES
('Administrator','admin@studenthub.local','$2y$10$Wga5Nt.eWulaUF8gbRLGEuRlLj8oYXYXebHZBx4MGaNTejir6OvBC','admin'),
('Demo Student','student@studenthub.local','$2y$10$jNi/BCdPkPkN.yLdTXevHO7V/M3V6vqlyw/uh4ITvEcRUAo3TDpzi','student');

INSERT INTO students (user_id,name,email,mobile,course,year,gender) VALUES
(2,'Demo Student','student@studenthub.local','9876543210','B.Tech CE',3,'Female'),
(NULL,'Aarav Patel','aarav.patel0@student.local','8260181590','B.Tech CE',1,'Male'),
(NULL,'Vihaan Shah','vihaan.shah1@student.local','7016613186','B.Tech IT',2,'Male'),
(NULL,'Diya Mehta','diya.mehta2@student.local','6913909960','B.Tech CSE',3,'Female'),
(NULL,'Anaya Desai','anaya.desai3@student.local','7082462819','B.Tech AIML',4,'Female'),
(NULL,'Riya Joshi','riya.joshi4@student.local','8821993518','MCA',1,'Female'),
(NULL,'Kabir Parmar','kabir.parmar5@student.local','6909378657','BCA',2,'Male'),
(NULL,'Ishaan Trivedi','ishaan.trivedi6@student.local','9543231948','B.Tech CE',3,'Male'),
(NULL,'Meera Modi','meera.modi7@student.local','9574911862','B.Tech IT',4,'Female'),
(NULL,'Krish Vyas','krish.vyas8@student.local','8276018955','B.Tech CSE',1,'Male'),
(NULL,'Saanvi Bhatt','saanvi.bhatt9@student.local','8979711471','B.Tech AIML',2,'Female'),
(NULL,'Tara Patel','tara.patel10@student.local','6497465075','MCA',3,'Female'),
(NULL,'Dev Shah','dev.shah11@student.local','7917034236','BCA',4,'Male'),
(NULL,'Nisha Mehta','nisha.mehta12@student.local','9712768426','B.Tech CE',1,'Female'),
(NULL,'Rohan Desai','rohan.desai13@student.local','8656321223','B.Tech IT',2,'Male'),
(NULL,'Pooja Joshi','pooja.joshi14@student.local','7079244026','B.Tech CSE',3,'Female'),
(NULL,'Yash Parmar','yash.parmar15@student.local','8995289078','B.Tech AIML',4,'Male'),
(NULL,'Kavya Trivedi','kavya.trivedi16@student.local','9666176031','MCA',1,'Female'),
(NULL,'Harsh Modi','harsh.modi17@student.local','7721590109','BCA',2,'Male'),
(NULL,'Isha Vyas','isha.vyas18@student.local','7815901396','B.Tech CE',3,'Female'),
(NULL,'Jay Bhatt','jay.bhatt19@student.local','7459571177','B.Tech IT',4,'Male'),
(NULL,'Mansi Patel','mansi.patel20@student.local','9741215472','B.Tech CSE',1,'Female'),
(NULL,'Parth Shah','parth.shah21@student.local','6385280841','B.Tech AIML',2,'Male'),
(NULL,'Sneha Mehta','sneha.mehta22@student.local','8852538885','MCA',3,'Female'),
(NULL,'Tejas Desai','tejas.desai23@student.local','7933633875','BCA',4,'Male');

INSERT INTO events (title,description,category,event_date,venue,status,created_by) VALUES
('HackCharusat','HackCharusat organised for StudentHub members.','Cultural','2026-11-03','Seminar Hall A','open',1),
('Web Dev Bootcamp','Web Dev Bootcamp organised for StudentHub members.','Sports','2026-11-05','Computer Lab 3','open',1),
('Annual Cultural Fest','Annual Cultural Fest organised for StudentHub members.','Workshop','2026-11-07','Sports Ground','open',1),
('Inter-College Cricket','Inter-College Cricket organised for StudentHub members.','Seminar','2026-11-09','Library Hall','closed',1),
('AI & ML Seminar','AI & ML Seminar organised for StudentHub members.','Technical','2026-11-11','Main Auditorium','open',1),
('Resume Building Workshop','Resume Building Workshop organised for StudentHub members.','Cultural','2026-11-13','Seminar Hall A','open',1),
('Open Source Day','Open Source Day organised for StudentHub members.','Sports','2026-11-15','Computer Lab 3','open',1),
('Robotics Expo','Robotics Expo organised for StudentHub members.','Workshop','2026-11-17','Sports Ground','closed',1),
('Photography Walk','Photography Walk organised for StudentHub members.','Seminar','2026-11-19','Library Hall','open',1),
('Alumni Meet','Alumni Meet organised for StudentHub members.','Technical','2026-12-21','Main Auditorium','open',1),
('Cyber Security Talk','Cyber Security Talk organised for StudentHub members.','Cultural','2026-12-23','Seminar Hall A','open',1),
('Startup Pitch Night','Startup Pitch Night organised for StudentHub members.','Sports','2026-12-25','Computer Lab 3','closed',1),
('Python Marathon','Python Marathon organised for StudentHub members.','Workshop','2026-12-27','Sports Ground','open',1),
('Debate Championship','Debate Championship organised for StudentHub members.','Seminar','2026-12-02','Library Hall','open',1),
('Placement Orientation','Placement Orientation organised for StudentHub members.','Technical','2026-12-04','Main Auditorium','open',1),
('Git & GitHub Workshop','Git & GitHub Workshop organised for StudentHub members.','Cultural','2026-12-06','Seminar Hall A','closed',1),
('Yoga Morning','Yoga Morning organised for StudentHub members.','Sports','2026-12-08','Computer Lab 3','open',1),
('Data Science Seminar','Data Science Seminar organised for StudentHub members.','Workshop','2026-12-10','Sports Ground','open',1);

INSERT INTO registrations (student_id,event_id) VALUES (1,1),(1,5),(1,9),(2,12),(3,13),(5,18),(6,5),(7,1),(7,11),(7,12),(7,16),(8,4),(8,11),(8,16),(9,7),(9,18),(10,17),(12,3),(13,7),(14,5),(14,7),(14,11),(15,12),(15,13),(16,6),(16,9),(16,12),(17,5),(18,5),(19,15),(20,1),(20,16),(21,3),(21,5),(22,4),(22,12),(24,1),(24,3),(24,4),(24,6);

INSERT INTO audit_logs (user_id,action,details,ip_address) VALUES (1,'seed','Database seeded','127.0.0.1');

-- Spread registration dates over the last ~5 months (for the monthly analytics chart)
UPDATE registrations SET registered_at = DATE_SUB(NOW(), INTERVAL ((id * 11) % 150) DAY);

-- Practical 8 (advanced): stored procedures
DROP PROCEDURE IF EXISTS sp_registrations_by_month;
DROP PROCEDURE IF EXISTS sp_event_registration_count;
DELIMITER $$
CREATE PROCEDURE sp_registrations_by_month()
BEGIN
  SELECT DATE_FORMAT(registered_at, '%Y-%m') AS month, COUNT(*) AS total
  FROM registrations
  WHERE registered_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
  GROUP BY month ORDER BY month;
END$$
CREATE PROCEDURE sp_event_registration_count(IN p_event_id INT)
BEGIN
  SELECT e.title, COUNT(r.id) AS total
  FROM events e LEFT JOIN registrations r ON r.event_id = e.id
  WHERE e.id = p_event_id GROUP BY e.id;
END$$
DELIMITER ;
