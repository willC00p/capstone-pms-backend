-- First add the new columns
ALTER TABLE `parking_assignments`
ADD COLUMN `assignee_type` varchar(255) DEFAULT 'guest',
ADD COLUMN `assignment_type` varchar(255) DEFAULT 'assign';

-- Then remove the type column
ALTER TABLE `parking_assignments`
DROP COLUMN `type`;
