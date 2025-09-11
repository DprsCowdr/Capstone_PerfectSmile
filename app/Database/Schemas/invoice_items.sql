-- Schema for invoice_items table
-- Two variants are provided: the first uses a foreign key constraint (if your `invoices` table exists),
-- the second is a safe version without foreign key constraints.

-- Variant A: with foreign key (recommended if `invoices(id)` exists)
CREATE TABLE IF NOT EXISTS `invoice_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id` INT UNSIGNED NOT NULL,
  `procedure_id` INT UNSIGNED NULL,
  `description` VARCHAR(255) NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `total` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_invoice_items_invoice_id` (`invoice_id`),
  CONSTRAINT `fk_invoice_items_invoice`
    FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Variant B: without foreign key (use if `invoices` table is not present or you prefer no FK)
-- Uncomment and run this instead if you cannot add the FK:
-- CREATE TABLE IF NOT EXISTS `invoice_items` (
--   `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
--   `invoice_id` INT UNSIGNED NOT NULL,
--   `procedure_id` INT UNSIGNED NULL,
--   `description` VARCHAR(255) NULL,
--   `quantity` INT NOT NULL DEFAULT 1,
--   `unit_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
--   `total` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
--   `created_at` DATETIME NULL,
--   `updated_at` DATETIME NULL,
--   PRIMARY KEY (`id`),
--   KEY `idx_invoice_items_invoice_id` (`invoice_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quick manual import examples (PowerShell):
-- mysql -u <user> -p <database> -e "SOURCE C:\\path\\to\\invoice_items.sql;"
-- or
-- mysql -u <user> -p <database> < "C:\\path\\to\\invoice_items.sql"

-- After creating the table, re-run your seeder if needed:
-- php spark db:seed InvoiceItemsSeeder
