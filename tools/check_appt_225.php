<?php
require __DIR__ . '/../vendor/autoload.php';
$db = \Config\Database::connect();
$appointmentId = 225;
$appt = $db->table('appointments')->where('id', $appointmentId)->get()->getRowArray();
echo "APPOINTMENT:\n";
print_r($appt);
$rows = $db->table('appointment_service')->where('appointment_id', $appointmentId)->get()->getResultArray();
echo "\nAPPOINTMENT_SERVICE rows:\n";
print_r($rows);
if (!empty($rows)) {
    $svcIds = array_map(function($r){ return $r['service_id']; }, $rows);
    $svcs = $db->table('services')->whereIn('id', $svcIds)->get()->getResultArray();
    echo "\nSERVICES:\n";
    print_r($svcs);
}
