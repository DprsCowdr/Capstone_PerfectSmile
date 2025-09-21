<?php
require_once __DIR__ . '/../vendor/autoload.php';
if (is_file(__DIR__ . '/../tests/_bootstrap.php')) require_once __DIR__ . '/../tests/_bootstrap.php';

try { $db = \Config\Database::connect(); } catch (Exception $e) { echo "DB connect failed: " . $e->getMessage() . "\n"; exit(1); }

// Create branch/service/user/appointment similar to smoke
$date = date('Y-m-d', strtotime('+1 day'));
$db->table('branches')->insert(['name' => 'CheckSQL Branch', 'operating_hours' => json_encode(['monday'=>['enabled'=>true,'open'=>'08:00','close'=>'20:00']]), 'created_at' => date('Y-m-d H:i:s')]);
$branchId = $db->insertID();
$db->table('services')->insert(['name' => 'CheckSQL Service', 'duration_minutes' => 30, 'duration_max_minutes' => 30, 'created_at' => date('Y-m-d H:i:s')]);
$svcId = $db->insertID();
$db->table('user')->insert(['name' => 'CS1', 'email' => 'cs1+' . time() . '@test', 'password' => password_hash('x', PASSWORD_DEFAULT), 'user_type' => 'patient', 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')]);
$u1 = $db->insertID();

// insert an approved appointment at 10:00
$db->table('appointments')->insert(['user_id' => $u1, 'branch_id' => $branchId, 'appointment_datetime' => $date . ' 10:00:00', 'status' => 'confirmed', 'approval_status' => 'approved', 'created_at' => date('Y-m-d H:i:s')]);
$aid = $db->insertID();
$db->table('appointment_service')->insert(['appointment_id' => $aid, 'service_id' => $svcId]);

// Now test the SQL-based conflict check
$am = new \App\Models\AppointmentModel();
$requested = $date . ' 10:00:00';
$conflict = $am->isTimeConflictingWithGrace($requested, 15, null, null, 30);
$next = $am->findNextAvailableSlot($date, '10:00', 15, 180, null, 30);

echo "isTimeConflictingWithGrace returned: " . ($conflict ? 'true' : 'false') . "\n";
echo "findNextAvailableSlot returned: " . var_export($next, true) . "\n";

// Cleanup
$db->transRollback();
