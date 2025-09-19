-- Migration: Add duration_minutes column to services table
-- Date: 2025-09-18
-- Description: Add duration_minutes field to store service duration in minutes

-- Add duration_minutes column to services table
ALTER TABLE `services` ADD COLUMN `duration_minutes` INT(11) DEFAULT 30 COMMENT 'Duration of service in minutes';

-- Add index for better performance if needed
-- CREATE INDEX idx_services_duration ON services(duration_minutes);