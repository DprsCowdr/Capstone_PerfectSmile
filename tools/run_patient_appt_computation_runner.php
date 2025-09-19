<?php
// Lightweight runner to validate appointment computation logic
// Use the test bootstrap to properly initialize CodeIgniter environment
require_once __DIR__ . '/../tests/_bootstrap.php';

$db = \Config\Database::connect();
$db->transStart();

// Create branch
$branchModel = new \App\Models\BranchModel();
$oh = [
    'monday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00'],
    'tuesday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00'],
    'wednesday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00'],
    'thursday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00'],
    'friday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00'],
    'saturday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00'],

// Also set native PHP session values and ensure session is started so CI session can read them in CLI
if (php_sapi_name() === 'cli') {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    $_SESSION['isLoggedIn'] = true;
    $_SESSION['user_id'] = $patientId;
    $_SESSION['user_type'] = 'patient';
}
    'sunday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00'],
<?php
// Lightweight runner to validate appointment computation logic
// Use the test bootstrap to properly initialize CodeIgniter environment
require_once __DIR__ . '/../tests/_bootstrap.php';

$db = \Config\Database::connect();
$db->transStart();

// Create branch
$branchModel = new \App\Models\BranchModel();
$oh = [
    'monday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00'],
    'tuesday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00'],
    'wednesday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00'],
    'thursday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00'],
    'friday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00'],
    'saturday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00'],
    'sunday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00'],
];
$branchId = $branchModel->insert(['name' => 'Runner Branch', 'operating_hours' => json_encode($oh)]);

// Create service
$svcModel = new \App\Models\ServiceModel();
$svcId = $svcModel->insert(['name' => 'Runner Service', 'duration_minutes' => 60, 'duration_max_minutes' => 120]);

// Create users
$db->table('user')->insert(['name' => 'Runner P1', 'email' => 'r1+' . time() . '@test', 'password' => password_hash('x', PASSWORD_DEFAULT), 'user_type' => 'patient', 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')]);
$otherId = $db->insertID();
$db->table('user')->insert(['name' => 'Runner P2', 'email' => 'r2+' . time() . '@test', 'password' => password_hash('x', PASSWORD_DEFAULT), 'user_type' => 'patient', 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')]);
$patientId = $db->insertID();

// Write grace file
@file_put_contents(WRITEPATH . 'grace_periods.json', json_encode(['default' => 20]));

// Insert existing appointment for other user at 10:00 tomorrow and link service
$appointmentModel = new \App\Models\AppointmentModel();
$date = date('Y-m-d', strtotime('+1 day'));
$aid = $appointmentModel->insert(['user_id' => $otherId, 'branch_id' => $branchId, 'appointment_datetime' => $date . ' 10:00:00', 'status' => 'confirmed', 'approval_status' => 'approved']);
$db->table('appointment_service')->insert(['appointment_id' => $aid, 'service_id' => $svcId]);

// Simulate patient checking conflicts at 10:30
$_POST = ['date' => $date, 'time' => '10:30', 'service_id' => $svcId];

// Ensure PHP session is available and mark authenticated user for CLI runs
if (php_sapi_name() === 'cli') {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    $_SESSION['isLoggedIn'] = true;
    $_SESSION['user_id'] = $patientId;
    $_SESSION['user_type'] = 'patient';
    // Also set CI session if available
    try { if (function_exists('session')) { session()->set('isLoggedIn', true); session()->set('user_id', $patientId); session()->set('user_type', 'patient'); } } catch (\Throwable $e) {}
}

$controller = new \App\Controllers\Appointments();
$request = \Config\Services::request();
$response = \Config\Services::response();
$logger = \Config\Services::logger();
$controller->initController($request, $response, $logger);

$res = $controller->checkConflicts();
echo "checkConflicts response:\n" . (string)$res->getBody() . "\n";

// Test availableSlots for branch and service
$_POST = ['date' => $date, 'branch_id' => $branchId, 'service_id' => $svcId];
$res2 = $controller->availableSlots();
echo "availableSlots response:\n" . (string)$res2->getBody() . "\n";

$db->transRollback();
