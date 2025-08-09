<?php
// Simple test script to debug dentist routes
require_once 'vendor/autoload.php';

// Test database connection
try {
    $db = \Config\Database::connect();
    echo "✅ Database connection successful\n";
    
    // Test user table
    $users = $db->table('user')->where('user_type', 'doctor')->get()->getResultArray();
    echo "✅ Found " . count($users) . " dentist users\n";
    
    // Test appointments table
    $appointments = $db->table('appointments')->get(5)->getResultArray();
    echo "✅ Found " . count($appointments) . " appointments in database\n";
    
    // Test branches table
    $branches = $db->table('branches')->get()->getResultArray();
    echo "✅ Found " . count($branches) . " branches in database\n";
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
?>
