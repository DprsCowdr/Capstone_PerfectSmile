<?php

// Simple smoke test script for AppointmentService::createAppointment
require __DIR__ . '/../../vendor/autoload.php';

// Bootstrap CodeIgniter similar to public/index.php so services/models have DB access
use CodeIgniter\Boot;
use Config\Paths;


// Use the app's Config\Paths so directory constants are correct
$paths = new \Config\Paths();
require $paths->systemDirectory . '/Boot.php';

// Define APPPATH and FCPATH constants expected by Boot::loadConstants
if (! defined('APPPATH')) {
    define('APPPATH', realpath(__DIR__ . '/../../app') . DIRECTORY_SEPARATOR);
}
if (! defined('FCPATH')) {
    define('FCPATH', realpath(__DIR__ . '/../../public') . DIRECTORY_SEPARATOR);
}

// Define ROOTPATH so app/Config/Constants.php can compute COMPOSER_PATH
if (! defined('ROOTPATH')) {
    define('ROOTPATH', realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR);
}

// Define SYSTEMPATH, WRITEPATH, TESTPATH from the Config\Paths so Boot can load system files
if (! defined('SYSTEMPATH')) {
    define('SYSTEMPATH', realpath($paths->systemDirectory) . DIRECTORY_SEPARATOR);
}
if (! defined('WRITEPATH')) {
    define('WRITEPATH', realpath($paths->writableDirectory) . DIRECTORY_SEPARATOR);
}
if (! defined('TESTPATH')) {
    define('TESTPATH', realpath($paths->testsDirectory) . DIRECTORY_SEPARATOR);
}

// Initialize framework for CLI testing (does not run the web request)
// Ensure environment constant is set for Boot::loadEnvironmentBootstrap
if (! defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development');
}

Boot::bootTest($paths);

use App\Services\AppointmentService;

$service = new AppointmentService();

// Test data: make a scheduled appointment for today at 09:00
$date = date('Y-m-d');
$time = '09:00';

// Create temporary patient users to satisfy FK constraints for smoke tests
$userModel = new \App\Models\UserModel();
$tempUsers = [];
for ($i = 1; $i <= 2; $i++) {
    $email = "smoke.user{$i}+" . time() . "@example.com";
    $insertId = $userModel->insert([
        'user_type' => 'patient',
        'name' => "Smoke User {$i}",
        'email' => $email,
        'password' => 'smokepass',
        'phone' => '0000000000',
        'status' => 'active',
    ]);
    if ($insertId) $tempUsers[] = $insertId;
}

$user1 = $tempUsers[0] ?? 1;
$user2 = $tempUsers[1] ?? 2;

$data = [
    'user_id' => $user1,
    'appointment_date' => $date,
    'appointment_time' => $time,
    'appointment_type' => 'scheduled',
    'dentist_id' => null,
    'branch_id' => 1,
    // Simulate guest/patient origin so service builds patient-facing messages
    'origin' => 'guest'
];

$result = $service->createAppointment($data);

echo "Result:\n";
print_r($result);

// Try to create a conflicting appointment at same time to test FCFS
$data2 = $data;
$data2['user_id'] = $user2;
// Also simulate an explicit patient-created appointment for the second run
$data2['origin'] = 'patient';

$result2 = $service->createAppointment($data2);

echo "Result 2 (conflict test):\n";
print_r($result2);
// Cleanup temporary users
if (!empty($tempUsers)) {
    foreach ($tempUsers as $uid) {
        try {
            $userModel->delete($uid);
        } catch (\Exception $e) {
            // ignore cleanup errors
        }
    }
}

?>