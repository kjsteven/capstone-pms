-- Update existing enum values for status column in tenants table
ALTER TABLE tenants 
MODIFY COLUMN status ENUM('active', 'inactive', 'archived', 'turnover') NOT NULL DEFAULT 'active';

-- Add comment to explain status values
ALTER TABLE tenants 
MODIFY COLUMN status ENUM('active', 'inactive', 'archived', 'turnover') 
COMMENT 'active: currently renting, inactive: no longer renting, archived: historical record, turnover: unit turned over';
