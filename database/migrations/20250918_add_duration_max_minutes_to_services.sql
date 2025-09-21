-- Migration: Add duration_max_minutes column to services table
-- Date: 2025-09-18
-- Description: Add duration_max_minutes field to store maximum service duration in minutes

ALTER TABLE `services` ADD COLUMN `duration_max_minutes` INT(11) DEFAULT NULL COMMENT 'Maximum duration of service in minutes';

-- You can run this SQL against your database or use your migration runner.