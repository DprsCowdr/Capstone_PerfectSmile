<?php
// Simple script to check appointments for today
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=perfectsmile_db-v1', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare('SELECT id, appointment_datetime, approval_status, status, user_id FROM appointments WHERE DATE(appointment_datetime) = ? ORDER BY appointment_datetime');
    $stmt->execute(['2025-09-20']);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Appointments for 2025-09-20:\n";
    echo "Total count: " . count($appointments) . "\n\n";
    
    foreach($appointments as $apt) {
        $shouldBlock = ($apt['approval_status'] === 'approved' && !in_array($apt['status'], ['cancelled', 'rejected', 'no_show'])) ? 'YES' : 'NO';
        echo "ID: {$apt['id']}, DateTime: {$apt['appointment_datetime']}, Status: {$apt['status']}, Approval: {$apt['approval_status']}, User: {$apt['user_id']}, Should Block: $shouldBlock\n";
    }
    
    // Check what appointment ID 1 looks like now
    $stmt = $pdo->prepare('SELECT * FROM appointments WHERE id = 1');
    $stmt->execute();
    $apt1 = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($apt1) {
        echo "\n--- Appointment ID 1 (after force-approve) ---\n";
        echo "Date/Time: {$apt1['appointment_datetime']}\n";
        echo "Status: {$apt1['status']}\n";
        echo "Approval Status: {$apt1['approval_status']}\n";
        echo "Updated At: {$apt1['updated_at']}\n";
        echo "User ID: {$apt1['user_id']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}