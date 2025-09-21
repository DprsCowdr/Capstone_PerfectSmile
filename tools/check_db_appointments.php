<?php
// tools/check_db_appointments.php
// Direct database check for appointment states
// Usage: php tools/check_db_appointments.php

require_once __DIR__ . '/../app/Config/Database.php';

// Try to connect to database using CodeIgniter config
try {
    $config = new \Config\Database();
    $db = \Config\Database::connect();
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "=== Checking appointments for 2025-09-20 ===\n\n";

$query = $db->query("
    SELECT id, appointment_datetime, approval_status, status, dentist_id, branch_id, user_id, procedure_duration, created_at, updated_at
    FROM appointments 
    WHERE DATE(appointment_datetime) = '2025-09-20'
    ORDER BY appointment_datetime
");

$results = $query->getResultArray();

if (empty($results)) {
    echo "No appointments found for 2025-09-20\n";
} else {
    echo "Found " . count($results) . " appointments:\n\n";
    
    foreach ($results as $row) {
        echo "ID: {$row['id']}\n";
        echo "  DateTime: {$row['appointment_datetime']}\n";
        echo "  Approval Status: {$row['approval_status']}\n";
        echo "  Status: {$row['status']}\n";
        echo "  Dentist ID: {$row['dentist_id']}\n";
        echo "  Branch ID: {$row['branch_id']}\n";
        echo "  User ID: {$row['user_id']}\n";
        echo "  Duration: {$row['procedure_duration']}\n";
        echo "  Created: {$row['created_at']}\n";
        echo "  Updated: {$row['updated_at']}\n";
        echo "  ---\n";
    }
}

// Also check specifically for IDs 245, 252, 263
echo "\n=== Checking specific IDs (245, 252, 263) ===\n\n";

$specificQuery = $db->query("
    SELECT id, appointment_datetime, approval_status, status, dentist_id, branch_id, user_id, procedure_duration
    FROM appointments 
    WHERE id IN (245, 252, 263)
    ORDER BY id
");

$specificResults = $specificQuery->getResultArray();

if (empty($specificResults)) {
    echo "None of the specific IDs (245, 252, 263) found\n";
} else {
    foreach ($specificResults as $row) {
        echo "ID {$row['id']}: approval_status='{$row['approval_status']}', status='{$row['status']}', dentist_id={$row['dentist_id']}\n";
    }
}

echo "\nDone.\n";
?>