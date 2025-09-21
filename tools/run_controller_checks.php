<?php
require_once __DIR__ . '/../vendor/autoload.php';
// Avoid loading test bootstrap to prevent switching to in-memory test DB with prefixes.
// if (is_file(__DIR__ . '/../tests/_bootstrap.php')) require_once __DIR__ . '/../tests/_bootstrap.php';

// init DB - use default connect (other scripts do the same); if your ENVIRONMENT is 'testing' this may use the test DB
try {
	$db = \Config\Database::connect();
} catch (\Exception $e) {
	echo "DB connect failed: " . $e->getMessage() . "\n";
	exit(1);
}
// basic session init
if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
try { session()->set('isLoggedIn', true); } catch (Throwable $e) {}

$date = date('Y-m-d', strtotime('+1 day'));
// Insert minimal branch/service/user/appointments as smoke_patient_appointment does but keep small
try {
	$db->table('branches')->insert(['name' => 'RunCtrl Branch', 'operating_hours' => json_encode(['monday'=>['enabled'=>true,'open'=>'08:00','close'=>'20:00']]), 'created_at' => date('Y-m-d H:i:s')]);
} catch (\Exception $e) {
	echo "DB insert failed: " . $e->getMessage() . "\n";
	echo "If this is a test environment with table prefixes (e.g. db_), ensure your test DB schema is present or run in a non-testing environment.\n";
	exit(1);
}
$branchId = $db->insertID();
$db->table('services')->insert(['name' => 'RunCtrl Service', 'duration_minutes' => 30, 'duration_max_minutes' => 30, 'created_at' => date('Y-m-d H:i:s')]);
$svcId = $db->insertID();

// create two users
$db->table('user')->insert(['name'=>'RC1','email'=>'rc1+' . time() . '@test','password'=>password_hash('x',PASSWORD_DEFAULT),'user_type'=>'patient','status'=>'active','created_at'=>date('Y-m-d H:i:s')]);
$u1 = $db->insertID();
$db->table('user')->insert(['name'=>'RC2','email'=>'rc2+' . time() . '@test','password'=>password_hash('x',PASSWORD_DEFAULT),'user_type'=>'patient','status'=>'active','created_at'=>date('Y-m-d H:i:s')]);
$u2 = $db->insertID();

// insert an approved appointment at 10:00
$db->table('appointments')->insert(['user_id'=>$u1,'branch_id'=>$branchId,'appointment_datetime'=>$date . ' 10:00:00','status'=>'confirmed','approval_status'=>'approved','created_at'=>date('Y-m-d H:i:s')]);
$aid = $db->insertID();
$db->table('appointment_service')->insert(['appointment_id'=>$aid,'service_id'=>$svcId]);

// set session user to u2
try { session()->set('user_id', $u2); session()->set('isLoggedIn', true); } catch (Throwable $e) { $_SESSION['user_id'] = $u2; $_SESSION['isLoggedIn'] = true; }

// Prepare controllers
$request = \Config\Services::request();
$response = \Config\Services::response();
$logger = \Config\Services::logger();

// 1) availableSlots
$controller = new \App\Controllers\Appointments();
$controller->initController($request, $response, $logger);
$_GET['__debug_noauth'] = 1; $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_POST = ['date' => $date, 'branch_id' => $branchId, 'service_id' => $svcId];
$res = $controller->availableSlots();
echo "availableSlots response:\n" . (string)$res->getBody() . "\n\n";

// 2) checkConflicts via Patient API (does not require admin auth)
$patientController = new \App\Controllers\PatientAppointments();
$patientController->initController($request, $response, $logger);
$_POST = ['date' => $date, 'time' => '10:00', 'branch_id' => $branchId, 'service_id' => $svcId];
$res2 = $patientController->checkConflicts();
echo "patient checkConflicts response:\n" . (string)$res2->getBody() . "\n\n";

// 3) admin/staff checkConflicts - set Auth::getCurrentUser to a fake admin via session
try { session()->set('user_id', 1); session()->set('isLoggedIn', true); session()->set('user_type','admin'); } catch (Throwable $e) { $_SESSION['user_id'] = 1; $_SESSION['isLoggedIn'] = true; $_SESSION['user_type']='admin'; }
$adminController = new \App\Controllers\Appointments();
$adminController->initController($request, $response, $logger);
$_POST = ['date' => $date, 'time' => '10:00', 'branch_id' => $branchId, 'service_id' => $svcId];
// Attach a fake admin user record if needed by Auth::getCurrentUser
// Create admin user if not exists
$exists = $db->table('user')->where('user_type','admin')->get()->getRowArray();
if (!$exists) { $db->table('user')->insert(['name'=>'Admin', 'email'=>'admin+' . time() . '@test', 'password'=>password_hash('x',PASSWORD_DEFAULT),'user_type'=>'admin','status'=>'active','created_at'=>date('Y-m-d H:i:s')]); }
$adminRes = $adminController->checkConflicts();
echo "admin checkConflicts response:\n" . (string)$adminRes->getBody() . "\n\n";

// cleanup
$db->transRollback();

echo "run_controller_checks done.\n";
