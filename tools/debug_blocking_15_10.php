<?php
// tools/debug_blocking_15_10.php
// Debug why 8am-1:24pm slots aren't blocked when appointment at 15:10 exists

$envPath = __DIR__ . '/../.env';
$dbConfig = [
    'hostname' => '127.0.0.1',
    'username' => 'root',
    'password' => '',
    'database' => 'perfectsmile_db-v1',
    'port' => 3306
];
if (file_exists($envPath)) {
    $env = file_get_contents($envPath);
    $lines = explode("\n", $env);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, 'database.default.hostname') === 0 && strpos($line, '#') !== 0) {
            $dbConfig['hostname'] = trim(explode('=', $line, 2)[1], " \t\n\r\0\x0B\"'");
        } elseif (strpos($line, 'database.default.username') === 0 && strpos($line, '#') !== 0) {
            $dbConfig['username'] = trim(explode('=', $line, 2)[1], " \t\n\r\0\x0B\"'");
        } elseif (strpos($line, 'database.default.password') === 0 && strpos($line, '#') !== 0) {
            $dbConfig['password'] = trim(explode('=', $line, 2)[1], " \t\n\r\0\x0B\"'");
        } elseif (strpos($line, 'database.default.database') === 0 && strpos($line, '#') !== 0) {
            $dbConfig['database'] = trim(explode('=', $line, 2)[1], " \t\n\r\0\x0B\"'");
        } elseif (strpos($line, 'database.default.port') === 0 && strpos($line, '#') !== 0) {
            $dbConfig['port'] = (int)trim(explode('=', $line, 2)[1], " \t\n\r\0\x0B\"'");
        }
    }
}

try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['hostname']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset=utf8mb4",
        $dbConfig['username'],
        $dbConfig['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    echo "DB connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

$date = '2025-09-20';
$branchId = 2;

echo "=== Debug Blocking Logic for {$date} ===\n\n";

// Check if appointment ID 272 exists and its approval status
$stmt = $pdo->prepare("SELECT id, appointment_datetime, procedure_duration, approval_status, status, user_id FROM appointments WHERE id = 272");
$stmt->execute();
$appt272 = $stmt->fetch(PDO::FETCH_ASSOC);

if ($appt272) {
    echo "Appointment 272 details:\n";
    echo "  datetime: {$appt272['appointment_datetime']}\n";
    echo "  duration: {$appt272['procedure_duration']} minutes\n"; 
    echo "  approval_status: {$appt272['approval_status']}\n";
    echo "  status: {$appt272['status']}\n";
    echo "  user_id: {$appt272['user_id']}\n\n";
} else {
    echo "Appointment 272 not found!\n\n";
}

// Fetch all approved appointments for the date (like the controller does)
$stmt = $pdo->prepare("SELECT id, appointment_datetime, procedure_duration, user_id, approval_status, status FROM appointments WHERE DATE(appointment_datetime)=:date AND approval_status='approved' AND status NOT IN ('cancelled','rejected','no_show') AND branch_id=:branch ORDER BY appointment_datetime");
$stmt->execute([':date'=>$date, ':branch'=>$branchId]);
$approved = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "All approved appointments for {$date}:\n";
if (empty($approved)) {
    echo "  (none found)\n";
} else {
    foreach ($approved as $a) {
        echo "  ID {$a['id']}: {$a['appointment_datetime']} ({$a['procedure_duration']}min) - {$a['approval_status']}/{$a['status']}\n";
    }
}
echo "\n";

// Simulate checking if 8:00 AM would be blocked
echo "=== Simulating slot blocking for 8:00 AM ===\n";
$slotStart = strtotime($date . ' 08:00:00');
$slotDuration = 15; // dental checkup duration
$grace = 15;
$slotEnd = $slotStart + (($slotDuration + $grace) * 60);

echo "Slot: " . date('H:i', $slotStart) . " - " . date('H:i', $slotEnd) . " (duration+grace: " . ($slotDuration + $grace) . "min)\n\n";

$blocked = false;
foreach ($approved as $a) {
    $appStart = strtotime($a['appointment_datetime']);
    $appDur = $a['procedure_duration'] ? (int)$a['procedure_duration'] : 0;
    $appEnd = $appStart + ($appDur * 60);
    
    echo "Checking against appointment {$a['id']}:\n";
    echo "  App: " . date('H:i', $appStart) . " - " . date('H:i', $appEnd) . "\n";
    echo "  Overlap check: slotStart({$slotStart}) < appEnd({$appEnd}) && slotEnd({$slotEnd}) > appStart({$appStart})\n";
    echo "  Result: " . (($slotStart < $appEnd && $slotEnd > $appStart) ? "BLOCKED" : "clear") . "\n\n";
    
    if ($slotStart < $appEnd && $slotEnd > $appStart) {
        $blocked = true;
        break;
    }
}

echo "8:00 AM slot would be: " . ($blocked ? "BLOCKED" : "AVAILABLE") . "\n\n";

// Check a few more morning times
$testTimes = ['08:30', '09:00', '10:00', '11:00', '12:00', '13:00'];
foreach ($testTimes as $t) {
    $ts = strtotime($date . ' ' . $t . ':00');
    $te = $ts + (($slotDuration + $grace) * 60);
    $isBlocked = false;
    foreach ($approved as $a) {
        $as = strtotime($a['appointment_datetime']);
        $ae = $as + (($a['procedure_duration'] ?: 0) * 60);
        if ($ts < $ae && $te > $as) {
            $isBlocked = true;
            break;
        }
    }
    echo $t . ": " . ($isBlocked ? "BLOCKED" : "available") . "\n";
}

echo "\nDone.\n";
?>