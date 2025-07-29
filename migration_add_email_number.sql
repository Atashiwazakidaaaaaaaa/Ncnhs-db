-- Migration to add email and number fields to faculty table
ALTER TABLE faculty 
ADD COLUMN email VARCHAR(255),
ADD COLUMN number VARCHAR(20);
