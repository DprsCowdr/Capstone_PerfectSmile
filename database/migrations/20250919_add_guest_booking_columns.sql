-- Migration: Add guest booking columns to appointments table
-- Date: 2025-09-19
-- Purpose: Support guest bookings without requiring user registration

-- Add guest booking columns to appointments table
ALTER TABLE `appointments` 
ADD COLUMN `patient_email` VARCHAR(255) NULL COMMENT 'Email for guest bookings' AFTER `user_id`,
ADD COLUMN `patient_phone` VARCHAR(20) NULL COMMENT 'Phone for guest bookings' AFTER `patient_email`,
ADD COLUMN `patient_name` VARCHAR(255) NULL COMMENT 'Name for guest bookings' AFTER `patient_phone`;

-- Add index for guest booking searches
CREATE INDEX `idx_appointments_guest_email` ON `appointments` (`patient_email`);
CREATE INDEX `idx_appointments_guest_phone` ON `appointments` (`patient_phone`);

-- Update comments for user_id to clarify it's optional for guest bookings
ALTER TABLE `appointments` 
MODIFY COLUMN `user_id` INT(11) NULL COMMENT 'User ID (NULL for guest bookings)';