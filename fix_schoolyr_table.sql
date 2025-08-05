-- Create or update the schoolyr table to match the schema
CREATE TABLE IF NOT EXISTS schoolyr (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schoolyr VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- If the table already exists but is missing columns, add them
-- Note: This might fail if columns already exist, which is expected
ALTER TABLE schoolyr ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE schoolyr ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE schoolyr ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Insert a default school year if the table is empty
INSERT INTO schoolyr (schoolyr) 
SELECT 'School Year 2024-2025' 
WHERE NOT EXISTS (SELECT 1 FROM schoolyr LIMIT 1);
