-- Migration: Add image_url column to faculty table
-- Date: 2025-08-08
-- Description: Add profile image support for faculty members

ALTER TABLE faculty 
ADD COLUMN image_url VARCHAR(500) DEFAULT NULL;

-- Update existing records to have NULL image_url (which will show initials)
UPDATE faculty SET image_url = NULL WHERE image_url IS NULL;
