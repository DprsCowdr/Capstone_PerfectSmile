<?php
// Debug helper: create minimal DB tables and call AppointmentService to see result
require_once __DIR__ . '/../tests/_bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php';

// Ensure testing environment uses sqlite memory if desired
putenv('database.tests.DBDriver=SQLite3');
putenv('database.tests.database=:memory:');

$db = \Config\Database::connect();

// create minimal tables (respect DB prefix)
$prefix = method_exists($db, 'getPrefix') ? $db->getPrefix() : (property_exists($db, 'DBPrefix') ? $db->DBPrefix : '');
$db->query("CREATE TABLE IF NOT EXISTS {$prefix}user (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT, password TEXT, user_type TEXT, status TEXT, created_at TEXT);");
$db->query("CREATE TABLE IF NOT EXISTS {$prefix}branches (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, operating_hours TEXT, created_at TEXT, updated_at TEXT);");
$db->query("CREATE TABLE IF NOT EXISTS {$prefix}services (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, duration_minutes INTEGER, duration_max_minutes INTEGER);");
$db->query("CREATE TABLE IF NOT EXISTS {$prefix}appointments (id INTEGER PRIMARY KEY AUTOINCREMENT, branch_id INTEGER, dentist_id INTEGER, user_id INTEGER, appointment_datetime TEXT, procedure_duration INTEGER, status TEXT, approval_status TEXT, created_at TEXT, updated_at TEXT);");
$db->query("CREATE TABLE IF NOT EXISTS {$prefix}appointment_service (appointment_id INTEGER, service_id INTEGER);");

// Create branch
$branchModel = new \App\Models\BranchModel();
$branchId = $branchModel->insert(['name' => 'Dbg Branch', 'operating_hours' => json_encode(['monday'=>['enabled'=>true,'open'=>'08:00','close'=>'20:00']])]);
var_dump('branchId',$branchId);

// Create service
$serviceModel = new \App\Models\ServiceModel();
$svcId = $serviceModel->insert(['name'=>'DbgSvc','duration_minutes'=>60,'duration_max_minutes'=>90]);
var_dump('svcId',$svcId);

// Create user
$db->table('user')->insert(['name'=>'Dbg User','email'=>'dbg@test','password'=>password_hash('x',PASSWORD_DEFAULT),'user_type'=>'patient','status'=>'active','created_at'=>date('Y-m-d H:i:s')]);
$patientId = $db->insertID();
var_dump('patientId',$patientId);

// Set session
session()->set('isLoggedIn', true);
session()->set('user_id', $patientId);
session()->set('user_type', 'patient');

$svc = new \App\Services\AppointmentService();
$date = date('Y-m-d', strtotime('+2 day'));
$data = [
    'appointment_date' => $date,
    'appointment_time' => '11:00',
    'branch_id' => $branchId,
    'service_id' => $svcId,
    'origin' => 'patient'
];
$res = $svc->createAppointment($data);
var_dump($res);


?>