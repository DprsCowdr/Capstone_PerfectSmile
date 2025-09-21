<?php
/**
 * Guest Booking Migration Runner
 * Adds guest booking columns to appointments table
 */

// Use the same connection pattern as other tools
$mysqli = new mysqli('127.0.0.1', 'root', '', 'perfectsmile_db-v1', 3306);
if ($mysqli->connect_errno) {
    die("DB connect failed: " . $mysqli->connect_error . "\n");
}

echo "=== Guest Booking Migration ===\n\n";

// Check if columns already exist
$checkQuery = $mysqli->query("SHOW COLUMNS FROM appointments LIKE 'patient_%'");
$existingColumns = [];
while ($row = $checkQuery->fetch_assoc()) {
    $existingColumns[] = $row['Field'];
}

if (!empty($existingColumns)) {
    echo "⚠️  Guest booking columns already exist: " . implode(', ', $existingColumns) . "\n";
    echo "Skipping migration.\n";
    exit(0);
}

echo "🔄 Adding guest booking columns to appointments table...\n";

try {
    // Add guest booking columns
    $sql1 = "ALTER TABLE `appointments` 
             ADD COLUMN `patient_email` VARCHAR(255) NULL COMMENT 'Email for guest bookings' AFTER `user_id`,
             ADD COLUMN `patient_phone` VARCHAR(20) NULL COMMENT 'Phone for guest bookings' AFTER `patient_email`,
             ADD COLUMN `patient_name` VARCHAR(255) NULL COMMENT 'Name for guest bookings' AFTER `patient_phone`";
    
    if (!$mysqli->query($sql1)) {
        throw new Exception("Failed to add columns: " . $mysqli->error);
    }
    echo "✅ Added guest booking columns\n";
    
    // Add indexes
    $sql2 = "CREATE INDEX `idx_appointments_guest_email` ON `appointments` (`patient_email`)";
    if (!$mysqli->query($sql2)) {
        throw new Exception("Failed to create email index: " . $mysqli->error);
    }
    echo "✅ Created email index\n";
    
    $sql3 = "CREATE INDEX `idx_appointments_guest_phone` ON `appointments` (`patient_phone`)";
    if (!$mysqli->query($sql3)) {
        throw new Exception("Failed to create phone index: " . $mysqli->error);
    }
    echo "✅ Created phone index\n";
    
    // Update user_id comment
    $sql4 = "ALTER TABLE `appointments` 
             MODIFY COLUMN `user_id` INT(11) NULL COMMENT 'User ID (NULL for guest bookings)'";
    if (!$mysqli->query($sql4)) {
        throw new Exception("Failed to update user_id comment: " . $mysqli->error);
    }
    echo "✅ Updated user_id column comment\n";
    
    echo "\n🎉 Migration completed successfully!\n";
    
    // Verify the changes
    echo "\n📊 Verification:\n";
    $verifyQuery = $mysqli->query("SHOW COLUMNS FROM appointments LIKE 'patient_%'");
    while ($row = $verifyQuery->fetch_assoc()) {
        echo "   - {$row['Field']}: {$row['Type']} ({$row['Null']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

$mysqli->close();