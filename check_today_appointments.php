<?php
require 'preload.php';

$db = \Config\Database::connect();
$query = $db->query('SELECT id, appointment_date, appointment_time, approval_status, status, user_id FROM appointments WHERE appointment_date = "2025-09-20" ORDER BY appointment_time');
$apts = $query->getResultArray();

echo "Appointments for 2025-09-20:\n";
foreach($apts as $apt) {
    echo "ID: {$apt['id']}, Time: {$apt['appointment_time']}, Status: {$apt['status']}, Approval: {$apt['approval_status']}, User: {$apt['user_id']}\n";
}