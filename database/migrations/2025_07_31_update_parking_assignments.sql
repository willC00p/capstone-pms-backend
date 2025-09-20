-- Add new columns
ALTER TABLE `parking_assignments`
ADD COLUMN `assignee_type` varchar(255) DEFAULT 'guest' AFTER `type`,
ADD COLUMN `assignment_type` varchar(255) DEFAULT 'assign' AFTER `assignee_type`;

-- Update existing type column default
ALTER TABLE `parking_assignments` 
MODIFY COLUMN `type` varchar(255) DEFAULT 'guest';
