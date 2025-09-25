<?php

// Simple script to check appointment data for debugging
// Run this with: php debug_appointments_today.php

// Simple database connection using PDO
$host = 'localhost';
$dbname = 'perfectsmile_db-v1';
$username = 'root';
$password = '';

$today = '2025-09-25';

echo "=== Debugging Appointments for $today ===\n\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check all appointments for today
    echo "1. All appointments for $today:\n";
    $stmt = $pdo->prepare("SELECT id, appointment_datetime, approval_status, status, branch_id, dentist_id, user_id FROM appointments WHERE DATE(appointment_datetime) = ?");
    $stmt->execute([$today]);
    $allAppts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($allAppts as $apt) {
        echo "   ID: {$apt['id']}, DateTime: {$apt['appointment_datetime']}, Approval: {$apt['approval_status']}, Status: {$apt['status']}, Branch: {$apt['branch_id']}\n";
    }
    
    if (empty($allAppts)) {
        echo "   No appointments found for $today\n";
    }
    
    echo "\n2. Appointments that would be considered 'occupied' (approved/auto_approved, not cancelled/rejected/no_show):\n";
    $stmt2 = $pdo->prepare("SELECT id, appointment_datetime, approval_status, status, branch_id FROM appointments WHERE DATE(appointment_datetime) = ? AND approval_status IN ('approved', 'auto_approved') AND status NOT IN ('cancelled', 'rejected', 'no_show')");
    $stmt2->execute([$today]);
    $occupiedAppts = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($occupiedAppts as $apt) {
        echo "   ID: {$apt['id']}, DateTime: {$apt['appointment_datetime']}, Approval: {$apt['approval_status']}, Status: {$apt['status']}, Branch: {$apt['branch_id']}\n";
    }
    
    if (empty($occupiedAppts)) {
        echo "   No 'occupied' appointments found - this explains why TimeTable shows all slots as available!\n";
        echo "   The appointments exist but don't meet the criteria for being 'occupied'\n";
    }
    
    echo "\n3. Let's see the actual approval_status values:\n";
    $uniqueStatuses = $pdo->query("SELECT DISTINCT approval_status, status FROM appointments WHERE DATE(appointment_datetime) = '$today'")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($uniqueStatuses as $status) {
        echo "   approval_status: '{$status['approval_status']}', status: '{$status['status']}'\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Debug Complete ===\n";