<?php
// Safe migration runner: adds only missing columns to `availability` table
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db   = 'perfectsmile_db-v1';
$port = 3306;

$mysqli = new mysqli($host, $user, $pass, $db, $port);
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "\n";
    exit(4);
}

// Columns we'd like to ensure exist
$cols = [
    'type' => "ALTER TABLE `availability` ADD COLUMN `type` VARCHAR(64) DEFAULT NULL",
    'start_datetime' => "ALTER TABLE `availability` ADD COLUMN `start_datetime` DATETIME DEFAULT NULL",
    'end_datetime' => "ALTER TABLE `availability` ADD COLUMN `end_datetime` DATETIME DEFAULT NULL",
    'is_recurring' => "ALTER TABLE `availability` ADD COLUMN `is_recurring` TINYINT(1) DEFAULT 0",
    'notes' => "ALTER TABLE `availability` ADD COLUMN `notes` TEXT DEFAULT NULL",
    'created_by' => "ALTER TABLE `availability` ADD COLUMN `created_by` INT DEFAULT NULL",
    'created_at' => "ALTER TABLE `availability` ADD COLUMN `created_at` TIMESTAMP NULL DEFAULT NULL",
    'updated_at' => "ALTER TABLE `availability` ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT NULL",
];

foreach ($cols as $col => $sql) {
    $res = $mysqli->query("SHOW COLUMNS FROM `availability` LIKE '{$col}'");
    if ($res && $res->num_rows > 0) {
        echo "Column {$col} already exists, skipping.\n";
        continue;
    }
    echo "Adding column {$col}...\n";
    if ($mysqli->query($sql) === TRUE) {
        echo "Added {$col}.\n";
    } else {
        echo "Failed to add {$col}: (" . $mysqli->errno . ") " . $mysqli->error . "\n";
    }
}

// Add indexes if missing
// Add indexes if missing (check information_schema)
$schema = $mysqli->real_escape_string($db);
$indexChecks = [
    'idx_availability_user' => "ALTER TABLE `availability` ADD INDEX `idx_availability_user` (`user_id`)",
    'idx_availability_start_end' => "ALTER TABLE `availability` ADD INDEX `idx_availability_start_end` (`start_datetime`,`end_datetime`)"
];
foreach ($indexChecks as $idxName => $sql) {
    $q = "SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = '{$schema}' AND TABLE_NAME = 'availability' AND INDEX_NAME = '{$idxName}' LIMIT 1";
    $res = $mysqli->query($q);
    if ($res && $res->num_rows > 0) {
        echo "Index {$idxName} already exists, skipping.\n";
        continue;
    }
    if ($mysqli->query($sql) === TRUE) {
        echo "Added index {$idxName}.\n";
    } else {
        echo "Failed to add index {$idxName}: (" . $mysqli->errno . ") " . $mysqli->error . "\n";
    }
}

echo "Safe migration completed.\n";
exit(0);
