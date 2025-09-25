<?php
// Test the fixed Patient controller appointment query
require_once 'preload.php';

use App\Models\AppointmentModel;

// Test with a known user_id
$testUserId = 28; // The user_id from your error message
echo "Testing appointment query for user_id: $testUserId\n\n";

// Use the fixed query pattern
$appointmentModel = new AppointmentModel();
$appointments = $appointmentModel->select('appointments.*, user.name as patient_name, branches.name as branch_name, dentists.name as dentist_name')
                                ->join('user', 'user.id = appointments.user_id', 'left')
                                ->join('branches', 'branches.id = appointments.branch_id', 'left') 
                                ->join('user as dentists', 'dentists.id = appointments.dentist_id', 'left')
                                ->where('appointments.user_id', $testUserId)
                                ->orderBy('appointments.appointment_datetime', 'DESC')
                                ->findAll();

echo "Found " . count($appointments) . " appointments for user $testUserId:\n\n";

foreach ($appointments as $apt) {
    echo "ID: " . $apt['id'] . "\n";
    echo "Patient Name: " . ($apt['patient_name'] ?? 'NULL') . "\n";
    echo "User ID: " . ($apt['user_id'] ?? 'NULL') . "\n";
    echo "Branch: " . ($apt['branch_name'] ?? 'NULL') . "\n";
    echo "Dentist: " . ($apt['dentist_name'] ?? 'NULL') . "\n";
    echo "Date/Time: " . ($apt['appointment_datetime'] ?? 'NULL') . "\n";
    echo "---\n";
}

// Test if appointment 295 exists and which user it belongs to
echo "\nChecking appointment ID 295 ownership:\n";
$apt295 = $appointmentModel->select('appointments.*, user.name as patient_name')
                          ->join('user', 'user.id = appointments.user_id', 'left')
                          ->where('appointments.id', 295)
                          ->first();

if ($apt295) {
    echo "Appointment 295 belongs to user_id: " . $apt295['user_id'] . " (name: " . ($apt295['patient_name'] ?? 'NULL') . ")\n";
} else {
    echo "Appointment 295 not found\n";
}
?>