-- Migration: extend existing `availability` table to support ad-hoc blocks (day-off, emergency)
-- Run this against your database (adjust prefix/schema as needed)

ALTER TABLE `availability`
  ADD COLUMN `type` varchar(50) DEFAULT 'recurring' AFTER `user_id`,
  ADD COLUMN `start_datetime` datetime DEFAULT NULL AFTER `end_time`,
  ADD COLUMN `end_datetime` datetime DEFAULT NULL AFTER `start_datetime`,
  ADD COLUMN `is_recurring` tinyint(1) NOT NULL DEFAULT 1 AFTER `end_datetime`,
  ADD COLUMN `notes` text DEFAULT NULL AFTER `is_recurring`,
  ADD COLUMN `created_by` int(11) DEFAULT NULL AFTER `notes`,
  ADD COLUMN `created_at` datetime DEFAULT NULL,
  ADD COLUMN `updated_at` datetime DEFAULT NULL;

-- Optional: add index to speed up queries by date range and user
CREATE INDEX IF NOT EXISTS idx_availability_user ON `availability` (`user_id`);
CREATE INDEX IF NOT EXISTS idx_availability_start_end ON `availability` (`start_datetime`,`end_datetime`);

-- Usage notes:
-- - Existing rows (weekly recurring) keep their `day_of_week`, `start_time`, `end_time` and will have is_recurring=1 (default).
-- - For ad-hoc blocks (day off, emergency), insert rows with is_recurring=0 and set start_datetime/end_datetime and type (e.g. 'day_off','emergency').
