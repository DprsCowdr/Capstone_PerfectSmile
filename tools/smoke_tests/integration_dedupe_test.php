<?php

// Integration test: ensure server-side dedupe prevents duplicate appointments for same user/time
require __DIR__ . '/../../vendor/autoload.php';

use CodeIgniter\Boot;

// Bootstrapping
$paths = new \Config\Paths();
require $paths->systemDirectory . '/Boot.php';
if (! defined('APPPATH')) define('APPPATH', realpath(__DIR__ . '/../../app') . DIRECTORY_SEPARATOR);
if (! defined('FCPATH')) define('FCPATH', realpath(__DIR__ . '/../../public') . DIRECTORY_SEPARATOR);
if (! defined('ROOTPATH')) define('ROOTPATH', realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR);
if (! defined('SYSTEMPATH')) define('SYSTEMPATH', realpath($paths->systemDirectory) . DIRECTORY_SEPARATOR);
if (! defined('WRITEPATH')) define('WRITEPATH', realpath($paths->writableDirectory) . DIRECTORY_SEPARATOR);
if (! defined('TESTPATH')) define('TESTPATH', realpath($paths->testsDirectory) . DIRECTORY_SEPARATOR);
if (! defined('ENVIRONMENT')) define('ENVIRONMENT', 'development');

Boot::bootTest($paths);

use App\Services\AppointmentService;

$service = new AppointmentService();

echo "Starting dedupe integration test...\n";

// Create a temporary patient user
$userModel = new \App\Models\UserModel();
$email = "dedupe.test+" . time() . "@example.com";
$userId = $userModel->insert([
    'user_type' => 'patient',
    'name' => 'Dedupe Test User',
    'email' => $email,
    'password' => 'testpass',
    'phone' => '0000000000',
    'status' => 'active',
]);
if (! $userId) {
    echo "Failed to create test user.\n";
    exit(1);
}

$date = date('Y-m-d');
$time = '14:30';

$payload = [
    'user_id' => $userId,
    'appointment_date' => $date,
    'appointment_time' => $time,
    'appointment_type' => 'scheduled',
    'dentist_id' => null,
    'branch_id' => 1,
    'origin' => 'patient',
];

echo "Creating first appointment...\n";
$r1 = $service->createAppointment($payload);
print_r($r1);

echo "Creating second (duplicate) appointment for same user/time...\n";
$r2 = $service->createAppointment($payload);
print_r($r2);

$passed = false;
if (!empty($r1['record']['id']) && !empty($r2['record']['id'])) {
    if ($r1['record']['id'] == $r2['record']['id']) {
        // Service returned same record id -> dedupe
        if (!empty($r2['duplicate']) || (!empty($r2['success']) && empty($r2['created']))) {
            $passed = true;
        }
    }
}

if ($passed) {
    echo "DEDUPE TEST PASSED: duplicate appointment returned existing record.\n";
    $exit = 0;
} else {
    echo "DEDUPE TEST FAILED: second creation returned a new record or did not signal duplicate.\n";
    $exit = 2;
}

// Cleanup: remove created appointment(s) and user
try {
    if (!empty($r1['record']['id'])) {
        $aps = new \App\Models\AppointmentModel();
        $aps->delete($r1['record']['id']);
    }
} catch (Exception $e) {}
try { $userModel->delete($userId); } catch (Exception $e) {}

exit($exit);

?>
