<?php

require_once FCPATH . '../vendor/autoload.php';

$app = \Config\Services::codeigniter();
$app->initialize();

$db = \Config\Database::connect();

echo "=== Database Migration Verification ===\n";
echo "Patient Check-ins: " . $db->table('patient_checkins')->countAllResults() . "\n";
echo "Treatment Sessions: " . $db->table('treatment_sessions')->countAllResults() . "\n";
echo "Payments: " . $db->table('payments')->countAllResults() . "\n";

// Show some sample data
echo "\n=== Sample Data ===\n";
echo "Recent Check-ins:\n";
$checkins = $db->table('patient_checkins')->orderBy('checked_in_at', 'DESC')->limit(3)->get()->getResultArray();
foreach ($checkins as $checkin) {
    echo "- Appointment {$checkin['appointment_id']} checked in at {$checkin['checked_in_at']}\n";
}

echo "\nPayment Records:\n";
$payments = $db->table('payments')->limit(3)->get()->getResultArray();
foreach ($payments as $payment) {
    echo "- Appointment {$payment['appointment_id']}: {$payment['payment_status']} - \${$payment['total_amount']}\n";
}

echo "\nVerification complete!\n";
