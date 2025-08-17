<?php

// Quick script to add missing next_appointment_id column
require_once 'vendor/autoload.php';

// Initialize CodeIgniter
$path = realpath(__DIR__) . '/';
require_once $path . 'app/Config/Paths.php';
$paths = new Config\Paths();
require_once $paths->systemDirectory . '/bootstrap.php';

$app = Config\Services::codeigniter();
$app->initialize();

// Get database connection
$db = \Config\Database::connect();

try {
    // Check if column already exists
    $query = $db->query("SHOW COLUMNS FROM dental_record LIKE 'next_appointment_id'");
    if ($query->getNumRows() > 0) {
        echo "Column 'next_appointment_id' already exists in dental_record table.\n";
        exit(0);
    }
    
    // Add the column
    $sql = "ALTER TABLE dental_record ADD COLUMN next_appointment_id INT(11) NULL AFTER next_appointment_date";
    $result = $db->query($sql);
    
    if ($result) {
        echo "Successfully added 'next_appointment_id' column to dental_record table.\n";
        
        // Try to add foreign key constraint
        try {
            $fkSql = "ALTER TABLE dental_record ADD CONSTRAINT fk_dental_record_next_appointment FOREIGN KEY (next_appointment_id) REFERENCES appointments(id) ON DELETE SET NULL ON UPDATE CASCADE";
            $fkResult = $db->query($fkSql);
            if ($fkResult) {
                echo "Successfully added foreign key constraint.\n";
            }
        } catch (Exception $e) {
            echo "Note: Could not add foreign key constraint (this is often okay): " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "Failed to add column. Error: " . $db->error()['message'] . "\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
