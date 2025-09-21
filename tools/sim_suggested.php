<?php
// tools/sim_suggested.php
// Simulate suggested_slots generation from availableSlots() for various durations

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
$grace = 20;
$durations = [15, 30, 60, 120, 180];

// Fetch existing approved appointments same as controller
$stmt = $pdo->prepare("SELECT id, appointment_datetime, procedure_duration, user_id FROM appointments WHERE DATE(appointment_datetime)=:date AND approval_status='approved' AND status NOT IN ('cancelled','rejected','no_show') AND branch_id=:branch ORDER BY appointment_datetime");
$stmt->execute([':date'=>$date, ':branch'=>$branchId]);
$existing = $stmt->fetchAll(PDO::FETCH_ASSOC);

$existing_intervals = [];
foreach ($existing as $e) {
    $start = strtotime($e['appointment_datetime']);
    $dur = isset($e['procedure_duration']) && $e['procedure_duration'] ? (int)$e['procedure_duration'] : 0;
    if ($dur>0) {
        $existing_intervals[] = [$start, $start + $dur*60, $e['id']];
    } else {
        // lookup service durations for simplicity assume 0
        $existing_intervals[] = [$start, $start, $e['id']];
    }
}

// For each duration compute suggested slots
foreach ($durations as $duration) {
    echo "\n=== Duration: {$duration} min (grace {$grace} min) ===\n";
    $totalBlockSeconds = ($duration + $grace) * 60;
    $dayStart = strtotime($date . ' 08:00:00');
    $dayEnd = strtotime($date . ' 21:00:00');

    // merge blocked intervals from existing_intervals
    $blocked=[];
    foreach ($existing_intervals as $iv) {
        if ($iv[0] < $iv[1]) $blocked[] = [$iv[0], $iv[1]];
    }
    usort($blocked, function($a,$b){return $a[0]-$b[0];});
    $merged=[];
    foreach ($blocked as $iv) {
        if (empty($merged)) { $merged[] = $iv; continue; }
        $last =& $merged[count($merged)-1];
        if ($iv[0] <= $last[1]) { $last[1] = max($last[1], $iv[1]); } else { $merged[]=$iv; }
    }

    // free intervals
    $free=[]; $cursor=$dayStart;
    foreach ($merged as $m) {
        if ($cursor < $m[0]) $free[] = [$cursor, $m[0]];
        $cursor = max($cursor, $m[1]);
    }
    if ($cursor < $dayEnd) $free[] = [$cursor,$dayEnd];

    echo "Merged blocked intervals:\n";
    foreach ($merged as $m) echo "  " . date('H:i',$m[0]) . " - " . date('H:i',$m[1]) . "\n";

    echo "Free intervals:\n";
    foreach ($free as $f) echo "  " . date('H:i',$f[0]) . " - " . date('H:i',$f[1]) . "\n";

    // suggested slots every 30 minutes
    $sgStep = 30*60;
    $suggested = [];
    foreach ($free as $fi) {
        $start = $fi[0]; $end = $fi[1];
        // snap up to nearest 30min
        $offset = ($start - $dayStart) % $sgStep; if ($offset<0) $offset += $sgStep;
        $cand = ($offset===0)?$start:($start + ($sgStep - $offset));
        $safety=0;
        while ($cand + $totalBlockSeconds <= $end && $safety<1000) {
            $suggested[] = $cand;
            $cand += $sgStep; $safety++; if (count($suggested)>200) break;
        }
    }

    echo "Suggested slots (first 10):\n";
    $i=0;
    foreach ($suggested as $s) {
        echo "  " . date('H:i',$s) . " - ends " . date('H:i',$s+$totalBlockSeconds) . "\n";
        $i++; if ($i>=10) break;
    }
    if (empty($suggested)) echo "  (none)\n";
}

echo "\nDone.\n";
?>