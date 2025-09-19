<?php
// Simple smoke test for appointment computation and available slots
require_once __DIR__ . '/../vendor/autoload.php';
// Try to load test bootstrap if available to initialize CI environment
if (is_file(__DIR__ . '/../tests/_bootstrap.php')) require_once __DIR__ . '/../tests/_bootstrap.php';

// Utility: safe db connect
try {
    $db = \Config\Database::connect();
} catch (Exception $e) {
    echo "DB connect failed: " . $e->getMessage() . "\n";
    exit(1);
}

$db->transStart();

echo "Smoke test: creating branch, service, users...\n";

$branchModel = new \App\Models\BranchModel();
$branchId = $branchModel->insert(['name' => 'Smoke Branch', 'operating_hours' => json_encode(['monday'=>['enabled'=>true,'open'=>'08:00','close'=>'20:00']])]);

$svcModel = new \App\Models\ServiceModel();
$svcId = $svcModel->insert(['name' => 'Smoke Service', 'duration_minutes' => 60, 'duration_max_minutes' => 90]);

$tbl = $db->table('user');
$tbl->insert(['name' => 'Smoke P1', 'email' => 'smoke1+' . time() . '@test', 'password' => password_hash('x', PASSWORD_DEFAULT), 'user_type' => 'patient', 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')]);
$other = $db->insertID();
$tbl->insert(['name' => 'Smoke P2', 'email' => 'smoke2+' . time() . '@test', 'password' => password_hash('x', PASSWORD_DEFAULT), 'user_type' => 'patient', 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')]);
$patient = $db->insertID();

// Write grace file
@file_put_contents(WRITEPATH . 'grace_periods.json', json_encode(['default' => 20]));

// Insert appointment for other user at 10:00 tomorrow and link service
$apptModel = new \App\Models\AppointmentModel();
$date = date('Y-m-d', strtotime('+1 day'));
$aid = $apptModel->insert(['user_id' => $other, 'branch_id' => $branchId, 'appointment_datetime' => $date . ' 10:00:00', 'status' => 'confirmed', 'approval_status' => 'approved']);
$db->table('appointment_service')->insert(['appointment_id' => $aid, 'service_id' => $svcId]);

// Fake session for patient
session()->set('isLoggedIn', true);
session()->set('user_id', $patient);
session()->set('user_type', 'patient');

// Prepare controller
$controller = new \App\Controllers\Appointments();
$request = \Config\Services::request();
$response = \Config\Services::response();
$logger = \Config\Services::logger();
$controller->initController($request, $response, $logger);

$# Check conflicts for 10:30
$_POST = ['date' => $date, 'time' => '10:30', 'service_id' => $svcId];
$res = $controller->checkConflicts();
echo "checkConflicts: \n" . (string)$res->getBody() . "\n\n";

// Get available slots for the branch
$
// Get available slots for the branch
$_POST = ['date' => $date, 'branch_id' => $branchId, 'service_id' => $svcId];
$res2 = $controller->availableSlots();
echo "availableSlots: \n" . (string)$res2->getBody() . "\n\n";

$db->transRollback();

echo "Smoke test completed.\n";
