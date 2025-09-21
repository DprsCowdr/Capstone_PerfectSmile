<?php
// Standalone smoke runner for double-booking conflict/approve flow
require __DIR__ . '/../../vendor/autoload.php';

// Force testing environment and configure test DB to use sqlite in-memory
putenv('CI_ENVIRONMENT=testing');
putenv('database.tests.DBDriver=SQLite3');
putenv('database.tests.database=:memory:');

// Try to load CI test bootstrap to setup environment constants and autoloading
$bootstrap = __DIR__ . '/../../vendor/codeigniter4/framework/system/Test/bootstrap.php';
if (is_file($bootstrap)) {
    require $bootstrap;
} else {
    echo "Warning: CI test bootstrap not found at {$bootstrap}. Attempting minimal bootstrap...\n";
    // Minimal attempt: load app/Config/Paths and Boot
    if (is_file(__DIR__ . '/../../app/Config/Paths.php')) {
        require __DIR__ . '/../../app/Config/Paths.php';
        $paths = new \Config\Paths();
        require $paths->systemDirectory . '/Boot.php';
        \CodeIgniter\Boot::boot();
    }
}

echo "Starting double-booking smoke runner...\n";

$db = \Config\Database::connect();
// Create minimal tables required for the smoke run (use DB prefix if configured)
$prefix = method_exists($db, 'getPrefix') ? $db->getPrefix() : (property_exists($db, 'DBPrefix') ? $db->DBPrefix : '');
$db->query("CREATE TABLE IF NOT EXISTS {$prefix}user (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT, password TEXT, user_type TEXT, status TEXT, created_at TEXT);");
$db->query("CREATE TABLE IF NOT EXISTS {$prefix}branches (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, operating_hours TEXT, created_at TEXT, updated_at TEXT);");
$db->query("CREATE TABLE IF NOT EXISTS {$prefix}services (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, duration_minutes INTEGER, duration_max_minutes INTEGER);");
$db->query("CREATE TABLE IF NOT EXISTS {$prefix}appointments (id INTEGER PRIMARY KEY AUTOINCREMENT, branch_id INTEGER, dentist_id INTEGER, user_id INTEGER, patient_name TEXT, appointment_datetime TEXT, appointment_date TEXT, appointment_time TEXT, procedure_duration INTEGER, status TEXT, approval_status TEXT, created_at TEXT, updated_at TEXT);");
$db->query("CREATE TABLE IF NOT EXISTS {$prefix}appointment_service (appointment_id INTEGER, service_id INTEGER);");
// Minimal branch_staff table used by getAvailableDentists and other queries
$db->query("CREATE TABLE IF NOT EXISTS {$prefix}branch_staff (id INTEGER PRIMARY KEY AUTOINCREMENT, branch_id INTEGER, user_id INTEGER, created_at TEXT);");

// Create two dentist users
$db->table('user')->insert([
    'name' => 'Smoke Dentist One',
    'email' => 'smokedentist1@example.test',
    'user_type' => 'dentist',
    'status' => 'active',
    'created_at' => date('Y-m-d H:i:s')
]);
$dentist1 = $db->insertID();

$db->table('user')->insert([
    'name' => 'Smoke Dentist Two',
    'email' => 'smokedentist2@example.test',
    'user_type' => 'dentist',
    'status' => 'active',
    'created_at' => date('Y-m-d H:i:s')
]);
$dentist2 = $db->insertID();

echo "Inserted dentists: {$dentist1}, {$dentist2}\n";

// Insert an existing confirmed appointment for dentist1 at 2025-10-01 09:00:00
$db->table('appointments')->insert([
    'user_id' => null,
    'patient_name' => 'Existing Patient',
    'appointment_datetime' => '2025-10-01 09:00:00',
    'appointment_date' => '2025-10-01',
    'appointment_time' => '09:00',
    'procedure_duration' => 60,
    'dentist_id' => $dentist1,
    'branch_id' => 1,
    'status' => 'confirmed',
    'approval_status' => 'approved',
    'created_at' => date('Y-m-d H:i:s')
]);

// Insert a pending appointment (waitlist) at same time without dentist
$db->table('appointments')->insert([
    'user_id' => null,
    'patient_name' => 'Waitlist Patient',
    'appointment_datetime' => '2025-10-01 09:00:00',
    'appointment_date' => '2025-10-01',
    'appointment_time' => '09:00',
    'procedure_duration' => 30,
    'dentist_id' => null,
    'branch_id' => 1,
    'status' => 'pending_approval',
    'approval_status' => 'pending',
    'created_at' => date('Y-m-d H:i:s')
]);
$pendingId = $db->insertID();

echo "Inserted pending appointment id: {$pendingId}\n";

// Ensure a branch row exists (id=1) so branch_staff queries work
$existingBranch = $db->table('branches')->where('id', 1)->get()->getRowArray();
if (!$existingBranch) {
    $db->table('branches')->insert(['id' => 1, 'name' => 'Smoke Branch', 'operating_hours' => json_encode(['monday'=>['enabled'=>true,'open'=>'08:00','close'=>'20:00']]), 'created_at' => date('Y-m-d H:i:s')]);
}

// Link dentist2 to branch 1 (available dentist)
$db->table('branch_staff')->insert(['branch_id' => 1, 'user_id' => $dentist2, 'created_at' => date('Y-m-d H:i:s')]);

// Run approve flow via AppointmentService
$svc = new \App\Services\AppointmentService();

echo "Calling AppointmentService->approveAppointment({$pendingId})...\n";
$res = $svc->approveAppointment($pendingId);

echo "Result:\n";
print_r($res);

$row = $db->table('appointments')->where('id', $pendingId)->get()->getRowArray();

echo "DB row for pending appointment after approve:\n";
print_r($row);

if (!empty($row['dentist_id'])) {
    echo "Smoke runner: dentist assigned: {$row['dentist_id']}\n";
} else {
    echo "Smoke runner: no dentist assigned\n";
}

echo "Done.\n";
