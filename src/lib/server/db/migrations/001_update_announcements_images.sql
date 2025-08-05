-- Migration to update announcements table for storing images in database
-- Run this SQL script on your database to update the schema

-- Modify the attachment column to store base64 image data instead of file URLs
ALTER TABLE announcements 
MODIFY COLUMN attachment TEXT;

-- Add new column for storing original filename
ALTER TABLE announcements   
ADD COLUMN image_filename VARCHAR(255);

-- Note: Existing file-based attachments will need to be manually converted
-- This migration script only updates the schema structure
