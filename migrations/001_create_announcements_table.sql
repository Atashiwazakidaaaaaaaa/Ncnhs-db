-- Migration to create announcements table
-- Run this SQL script in your MySQL database

CREATE TABLE `announcements` (
  `id` SERIAL PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `date_posted` DATE NOT NULL,
  `display_date` VARCHAR(100) NOT NULL COMMENT 'Human-readable date like "August 5th, 2025"',
  `category` VARCHAR(50) NOT NULL DEFAULT 'general' COMMENT 'academic, event, maintenance, general',
  `priority` VARCHAR(20) NOT NULL DEFAULT 'medium' COMMENT 'high, medium, low',
  `attachment` VARCHAR(500) NULL COMMENT 'URL to uploaded file',
  `author` VARCHAR(255) NOT NULL DEFAULT 'Administrator',
  `is_active` INT NOT NULL DEFAULT 1 COMMENT '1 for active, 0 for archived',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert some sample announcements
INSERT INTO `announcements` (`title`, `content`, `date_posted`, `display_date`, `category`, `priority`, `author`) VALUES
('Parent-Teacher Conference Schedule', 
 '<p>Dear Parents and Guardians,</p><p>Our annual Parent-Teacher Conference is scheduled for <strong>August 5th, 2025</strong>, from 8:00 AM to 4:00 PM. This is a crucial opportunity to discuss your child''s academic progress and overall well-being.</p><p>Please ensure you have booked your slots with your child''s respective teachers through our online booking system.</p>', 
 '2025-07-28', 
 'August 5th 2025', 
 'academic', 
 'high', 
 'Principal Office'),

('Welcome Back to School!', 
 '<p>Welcome back, students and faculty, to another exciting academic year at New Cabalan National High School! We are thrilled to have you all back on campus.</p><p>Please take note of the important dates and events for the first quarter, including orientation sessions and club registration deadlines.</p>', 
 '2025-07-01', 
 'July 1, 2025', 
 'academic', 
 'medium', 
 'Administrator'),

('School Maintenance Notice', 
 '<p>The school will undergo maintenance work on the electrical systems during the weekend. Classes will not be affected.</p><p>All activities scheduled for Saturday and Sunday will proceed as normal.</p>', 
 '2025-07-15', 
 'July 15, 2025', 
 'maintenance', 
 'low', 
 'Maintenance Department');
