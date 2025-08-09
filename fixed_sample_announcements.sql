-- Fix the sample announcements data with proper date format
DELETE FROM announcements;

INSERT INTO announcements (title, content, date_posted, display_date, category, priority, author, is_active) VALUES
('Welcome to School Year 2024-2025', 'We warmly welcome all students, faculty, and staff to the new academic year 2024-2025. Let us work together to achieve excellence in education and create meaningful learning experiences for everyone.', '2024-08-15', 'August 2024', 'general', 'high', 'Administrator', 1),

('Class Schedule and Room Assignments', 'All students are advised to check their class schedules and room assignments posted on the school bulletin board. Classes will begin on August 15, 2024. Please report to your assigned classrooms on time.', '2024-08-10', 'August 2024', 'academic', 'high', 'Administrator', 1),

('Parent-Teacher Conference', 'We cordially invite all parents and guardians to attend the Parent-Teacher Conference scheduled for September 2024. This is an excellent opportunity to discuss your child\'s academic progress and development.', '2024-09-01', 'September 2024', 'event', 'normal', 'Administrator', 1),

('Sports Week 2024', 'Join us for our annual Sports Week featuring various athletic competitions including basketball, volleyball, track and field, and more. Registration is now open for all interested students.', '2024-10-01', 'October 2024', 'sports', 'normal', 'Administrator', 1),

('School Library Hours Extended', 'Good news! Our school library will now be open from 7:00 AM to 6:00 PM, Monday through Friday. We encourage all students to take advantage of these extended hours for study and research.', '2024-08-20', 'August 2024', 'facility', 'low', 'Administrator', 1),

('Science Fair Registration Open', 'The annual Science Fair is approaching! All students interested in participating should register with their respective science teachers. Deadline for registration is November 15, 2024.', '2024-11-01', 'November 2024', 'academic', 'normal', 'Administrator', 1),

('Uniform Policy Reminder', 'Please ensure that all students are following the proper school uniform policy. Proper grooming and complete uniform are required at all times during school hours.', '2024-08-05', 'August 2024', 'general', 'normal', 'Administrator', 1);
