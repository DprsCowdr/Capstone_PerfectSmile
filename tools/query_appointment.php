<?php
// Query an appointment and its linked services for debugging
$host = getenv('DB_HOST') ?: '127.0.0.1';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$db   = getenv('DB_NAME') ?: 'perfectsmile_db-v1';
$port = getenv('DB_PORT') ?: 3306;
$argvId = $argv[1] ?? null;
if (!$argvId) {
    echo "Usage: php query_appointment.php <appointment_id>\n";
    exit(1);
}
$appointmentId = (int)$argvId;
$mysqli = new mysqli($host, $user, $pass, $db, (int)$port);
if ($mysqli->connect_errno) {
    echo "DB connect failed: " . $mysqli->connect_error . PHP_EOL;
    exit(1);
}
$appt = $mysqli->query("SELECT * FROM appointments WHERE id = " . $appointmentId)->fetch_assoc();
echo "APPOINTMENT:\n";
print_r($appt);
$rows = $mysqli->query("SELECT * FROM appointment_service WHERE appointment_id = " . $appointmentId)->fetch_all(MYSQLI_ASSOC);
echo "\nAPPOINTMENT_SERVICE rows:\n";
print_r($rows);
if (!empty($rows)) {
    $svcIds = array_map(function($r){ return $r['service_id']; }, $rows);
    $in = implode(',', array_map('intval', $svcIds));
    $svcs = $mysqli->query("SELECT id, name, duration_minutes, duration_max_minutes FROM services WHERE id IN (" . $in . ")")->fetch_all(MYSQLI_ASSOC);
    echo "\nSERVICES:\n";
    print_r($svcs);
    // compute sum
    $sumRes = $mysqli->query("SELECT SUM(COALESCE(s.duration_max_minutes, s.duration_minutes, 0)) AS total FROM appointment_service aps JOIN services s ON s.id = aps.service_id WHERE aps.appointment_id = " . $appointmentId);
    $sumRow = $sumRes ? $sumRes->fetch_assoc() : null;
    echo "\nComputed total_service_minutes: " . ($sumRow['total'] ?? 'NULL') . "\n";
}
$gpPath = __DIR__ . '/../writable/grace_periods.json';
$gp = null;
if (is_file($gpPath)) {
    $g = json_decode(file_get_contents($gpPath), true);
    $gp = $g;
}
echo "\nGrace config (writable/grace_periods.json):\n";
print_r($gp);

echo "\nDone.\n";
