<?php
require_once 'vendor/autoload.php';

// Bootstrap CI manually
$paths = new Config\Paths();
require $paths->systemDirectory . '/Boot.php';

use CodeIgniter\Boot;
use Config\Database;

// Initialize CI
$app = Boot::createApp();

// Get database connection
$db = Database::connect();

// Check table structure
echo "=== USER TABLE STRUCTURE ===\n";
$result = $db->query('DESCRIBE user');
foreach($result->getResultArray() as $row) {
    if(strpos($row['Field'], 'medical') !== false) {
        echo $row['Field'] . ': ' . $row['Type'] . "\n";
    }
}

// Check for problematic data
echo "\n=== CHECKING MEDICAL_CONDITIONS DATA ===\n";
$result = $db->query('SELECT id, medical_conditions FROM user WHERE medical_conditions IS NOT NULL AND medical_conditions != ""');
foreach($result->getResultArray() as $row) {
    echo "User ID " . $row['id'] . ": " . var_export($row['medical_conditions'], true) . "\n";
}

echo "\n=== CHECKING PATIENT_MEDICAL_HISTORY TABLE ===\n";
try {
    $result = $db->query('SELECT user_id, medical_conditions FROM patient_medical_history LIMIT 5');
    foreach($result->getResultArray() as $row) {
        echo "User ID " . $row['user_id'] . ": " . var_export($row['medical_conditions'], true) . "\n";
    }
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
