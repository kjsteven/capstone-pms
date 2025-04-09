-- Create the tenant_turnovers table to track the turnover process
CREATE TABLE IF NOT EXISTS tenant_turnovers (
    turnover_id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    status ENUM('notified', 'scheduled', 'inspected', 'completed') NOT NULL DEFAULT 'notified',
    
    -- Notification details
    notification_date DATETIME NULL,
    notification_message TEXT NULL,
    
    -- Inspection schedule details
    inspection_date DATETIME NULL,
    staff_assigned INT NULL,
    inspection_notes TEXT NULL,
    
    -- Inspection results
    cleanliness_rating ENUM('excellent', 'good', 'fair', 'poor') NULL,
    damage_rating ENUM('none', 'minor', 'moderate', 'major') NULL,
    equipment_rating ENUM('excellent', 'good', 'fair', 'poor') NULL,
    inspection_report TEXT NULL,
    inspection_photos JSON NULL,
    inspection_completed_date DATETIME NULL,
    
    -- Completion details
    completion_notes TEXT NULL,
    completion_date DATETIME NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key constraint
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    FOREIGN KEY (staff_assigned) REFERENCES staff(staff_id)
);

-- Update tenant status enum to include turnover status
ALTER TABLE tenants 
MODIFY COLUMN status ENUM('active', 'inactive', 'archived', 'turnover') 
COMMENT 'active: currently renting, inactive: no longer renting, archived: historical record, turnover: unit turned over';
