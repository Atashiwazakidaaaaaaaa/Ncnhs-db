-- Add event integration fields to announcements table
ALTER TABLE announcements 
ADD COLUMN event_date DATE NULL COMMENT 'Date when the announced event will occur',
ADD COLUMN event_time_start TIME NULL COMMENT 'Start time of the announced event',
ADD COLUMN event_time_end TIME NULL COMMENT 'End time of the announced event',
ADD COLUMN event_location VARCHAR(255) NULL COMMENT 'Location of the announced event',
ADD COLUMN create_calendar_event TINYINT(1) DEFAULT 0 COMMENT 'Whether to create a corresponding calendar event',
ADD COLUMN calendar_event_id INT NULL COMMENT 'Reference to the created calendar event',
ADD INDEX idx_event_date (event_date),
ADD INDEX idx_calendar_event_id (calendar_event_id);

-- Add foreign key constraint to link announcements to events
ALTER TABLE announcements 
ADD CONSTRAINT fk_announcement_event 
FOREIGN KEY (calendar_event_id) REFERENCES events(id) 
ON DELETE SET NULL 
ON UPDATE CASCADE;

-- Add announcement_id field to events table to track which announcements created which events
ALTER TABLE events 
ADD COLUMN announcement_id INT NULL COMMENT 'Reference to the announcement that created this event',
ADD INDEX idx_announcement_id (announcement_id);

-- Add foreign key constraint to link events back to announcements
ALTER TABLE events 
ADD CONSTRAINT fk_event_announcement 
FOREIGN KEY (announcement_id) REFERENCES announcements(id) 
ON DELETE SET NULL 
ON UPDATE CASCADE;
