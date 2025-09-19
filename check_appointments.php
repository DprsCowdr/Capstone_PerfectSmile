<?php
// Direct database connection using the config values
$db = new PDO('mysql:host=127.0.0.1;dbname=perfectsmile_db-v1', 'root', '');

$stmt = $db->prepare('SELECT id, appointment_date, appointment_time, approval_status, status, user_id FROM appointments WHERE appointment_date = ? ORDER BY appointment_time');
$stmt->execute(['2025-09-20']);
$apts = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Appointments for 2025-09-20:\n";
foreach($apts as $apt) {
    echo "ID: {$apt['id']}, Time: {$apt['appointment_time']}, Status: {$apt['status']}, Approval: {$apt['approval_status']}, User: {$apt['user_id']}\n";
}

// Also check what was the latest change to appointment ID 1
$stmt = $db->prepare('SELECT * FROM appointments WHERE id = 1');
$stmt->execute();
$apt1 = $stmt->fetch(PDO::FETCH_ASSOC);
if ($apt1) {
    echo "\nAppointment ID 1 details:\n";
    echo "Date/Time: {$apt1['appointment_date']} {$apt1['appointment_time']}\n";
    echo "Status: {$apt1['status']}\n"; 
    echo "Approval Status: {$apt1['approval_status']}\n";
    echo "Updated At: {$apt1['updated_at']}\n";
    echo "User ID: {$apt1['user_id']}\n";
}