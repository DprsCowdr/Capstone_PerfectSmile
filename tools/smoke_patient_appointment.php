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

// Initialize session service early to avoid headers/ini_set conflicts when running in CLI with output.
try {
    \Config\Services::session();
} catch (\Throwable $e) {
    // If session initialization fails in this environment, continue without session (smoke tests may still work)
}

$db->transStart();

echo "Smoke test: creating branch, service, users...\n";

$db->table('branches')->insert(['name' => 'Smoke Branch', 'operating_hours' => json_encode(['monday'=>['enabled'=>true,'open'=>'08:00','close'=>'20:00']]), 'created_at' => date('Y-m-d H:i:s')]);
$branchId = $db->insertID();

$db->table('services')->insert(['name' => 'Smoke Service', 'duration_minutes' => 60, 'duration_max_minutes' => 90, 'created_at' => date('Y-m-d H:i:s')]);
$svcId = $db->insertID();

$tbl = $db->table('user');
// Insert two patients and capture their IDs
$tbl->insert(['name' => 'Smoke P1', 'email' => 'smoke1+' . time() . '@test', 'password' => password_hash('x', PASSWORD_DEFAULT), 'user_type' => 'patient', 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')]);
$other = $db->insertID() ?: null;
$tbl->insert(['name' => 'Smoke P2', 'email' => 'smoke2+' . time() . '@test', 'password' => password_hash('x', PASSWORD_DEFAULT), 'user_type' => 'patient', 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')]);
$patient = $db->insertID() ?: $other;

// Write grace file
@file_put_contents(WRITEPATH . 'grace_periods.json', json_encode(['default' => 20]));

// Insert appointment for other user at 10:00 tomorrow and link service
$apptModel = new \App\Models\AppointmentModel();
$date = date('Y-m-d', strtotime('+1 day'));
$ownerUser = $other ?: $patient;
// Insert appointment directly to bypass model validation in smoke tests
$db->table('appointments')->insert(['user_id' => $ownerUser, 'branch_id' => $branchId, 'appointment_datetime' => $date . ' 10:00:00', 'status' => 'confirmed', 'approval_status' => 'approved', 'created_at' => date('Y-m-d H:i:s')]);
$aid = $db->insertID();
$db->table('appointment_service')->insert(['appointment_id' => $aid, 'service_id' => $svcId]);

// Fake session for patient
// Ensure native PHP session is started (CLI safe) and set both native and CI session values
if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}
// Set raw PHP session values as fallback
$_SESSION['isLoggedIn'] = true;
$_SESSION['user_id'] = $patient;
$_SESSION['user_type'] = 'patient';
// Also set CodeIgniter session wrapper if available
try { session()->set('isLoggedIn', true); session()->set('user_id', $patient); session()->set('user_type', 'patient'); } catch (Throwable $e) { /* ignore */ }

// Prepare controller
$controller = new \App\Controllers\Appointments();
$request = \Config\Services::request();
$response = \Config\Services::response();
$logger = \Config\Services::logger();
$controller->initController($request, $response, $logger);

// For admin controller checkConflicts requires auth; instead exercise the patient API checkConflicts endpoint
// Set request context for debug/noauth so availableSlots runs without auth
$_GET['__debug_noauth'] = 1;
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

// (skip invoking API controller directly in smoke script)

// Get available slots for the branch (use DEBUG bypass)
$_POST = ['date' => $date, 'branch_id' => $branchId, 'service_id' => $svcId];
$res2 = $controller->availableSlots();
echo "availableSlots: \n" . (string)$res2->getBody() . "\n\n";

$db->transRollback();

echo "Smoke test completed.\n";
