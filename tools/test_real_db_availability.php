<?php
// tools/test_real_db_availability.php
// Reads DB credentials from env file (project root .env) and queries real services/branches
// Then runs a simplified availability calculation using the same blockSeconds formula

date_default_timezone_set('Asia/Manila');

function loadEnv($path) {
    if (!file_exists($path)) return [];
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $out = [];
    foreach ($lines as $l) {
        if (strpos(trim($l), '#') === 0) continue;
        if (!strpos($l,'=')) continue;
        list($k,$v) = explode('=', $l, 2);
        $out[trim($k)] = trim($v);
    }
    return $out;
}

$envPath = __DIR__ . '/../.env';
$env = loadEnv($envPath);
$dbHost = $env['database.default.hostname'] ?? ($env['DB_HOST'] ?? '127.0.0.1');
$dbUser = $env['database.default.username'] ?? ($env['DB_USER'] ?? 'root');
$dbPass = $env['database.default.password'] ?? ($env['DB_PASS'] ?? '');
$dbName = $env['database.default.database'] ?? ($env['DB_NAME'] ?? null);
if (!isset($dbName)) {
    echo "Could not find DB name in .env (looked for database.default.database or DB_NAME).\n";
    exit(1);
}

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
} catch (Exception $e) {
    echo "Failed to connect to DB: " . $e->getMessage() . "\n";
    exit(1);
}

// pick first active service and branch
$svc = $pdo->query("SELECT id, name, duration_minutes, duration_max_minutes FROM services ORDER BY id LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$cliBranch = isset($argv[4]) ? (int)$argv[4] : null;
if ($cliBranch) {
    $stmt = $pdo->prepare("SELECT id, name, operating_hours FROM branches WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $cliBranch]);
    $branch = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $branch = $pdo->query("SELECT id, name, operating_hours FROM branches ORDER BY id LIMIT 1")->fetch(PDO::FETCH_ASSOC);
}
if (!$svc) { echo "No services found in DB.\n"; exit(1); }
if (!$branch) { echo "No branches found in DB.\n"; exit(1); }

echo "Using Service ID: {$svc['id']} ({$svc['name']})\n";
$dur = !empty($svc['duration_max_minutes']) ? (int)$svc['duration_max_minutes'] : (int)$svc['duration_minutes'];
echo "Computed duration (prefer max if present): {$dur} minutes\n";

$date = date('Y-m-d', strtotime('+1 day'));
$cliDate = isset($argv[3]) ? $argv[3] : null;
if ($cliDate) {
    // basic validation YYYY-MM-DD
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $cliDate)) {
        $date = $cliDate;
    }
}
$granularity = 5;
// Accept optional CLI args: granularity and dentist_id
$cliGran = isset($argv[1]) ? (int)$argv[1] : null;
if ($cliGran && in_array($cliGran, [1,3,5,10,15,30])) $granularity = $cliGran;
$cliDentist = isset($argv[2]) ? (int)$argv[2] : null;


// read grace
$grace = 20;
$gpPath = __DIR__ . '/../writable/grace_periods.json';
if (is_file($gpPath)) {
    $gp = json_decode(file_get_contents($gpPath), true);
    if (!empty($gp['default'])) $grace = (int)$gp['default'];
}

echo "Grace minutes: {$grace}\n";

// operating hours parse (best-effort)
$dayStart = strtotime($date . ' 08:00:00');
$dayEnd = strtotime($date . ' 20:00:00');
if (!empty($branch['operating_hours'])) {
    $oh = json_decode($branch['operating_hours'], true);
    $weekday = strtolower(date('l', strtotime($date)));
    if (is_array($oh) && isset($oh[$weekday]) && !empty($oh[$weekday]['enabled'])) {
        $open = $oh[$weekday]['open'] ?? '08:00';
        $close = $oh[$weekday]['close'] ?? '20:00';
        $dayStart = strtotime($date . ' ' . $open . ':00');
        $dayEnd = strtotime($date . ' ' . $close . ':00');
    }
}

echo "Branch: {$branch['id']} ({$branch['name']}) operating window: " . date('Y-m-d H:i', $dayStart) . " - " . date('Y-m-d H:i', $dayEnd) . "\n";

// load occupied intervals for that branch on date using appointment logic
$sql = "SELECT a.id, a.appointment_datetime, COALESCE(svc.total_service_minutes, a.procedure_duration, 0) AS duration_minutes, a.user_id,
                                 GROUP_CONCAT(DISTINCT s.name SEPARATOR ', ') AS services
                FROM appointments a
                LEFT JOIN (
                    SELECT aps.appointment_id, SUM(COALESCE(s.duration_max_minutes, s.duration_minutes, 0)) AS total_service_minutes
                    FROM appointment_service aps
                    JOIN services s ON s.id = aps.service_id
                    GROUP BY aps.appointment_id
                ) svc ON svc.appointment_id = a.id
                LEFT JOIN appointment_service aps ON aps.appointment_id = a.id
                LEFT JOIN services s ON s.id = aps.service_id
                WHERE DATE(a.appointment_datetime) = :d
                    AND a.branch_id = :b
                    AND a.approval_status IN ('approved','auto_approved')
                    AND a.status NOT IN ('cancelled','rejected','no_show')
                GROUP BY a.id
                ORDER BY a.appointment_datetime";
$stmt = $pdo->prepare($sql);
$stmt->execute([':d'=>$date,':b'=>$branch['id']]);
$occupied = [];
$userStmt = $pdo->prepare("SELECT * FROM `user` WHERE id = :id LIMIT 1");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $start = strtotime($r['appointment_datetime']);
    $durMinutes = (int)$r['duration_minutes'];
    if ($durMinutes <= 0) $durMinutes = 0;
    $end = $start + ($durMinutes * 60);
    $userId = $r['user_id'] ?? null;
    $patientName = '(unknown)';
    if ($userId) {
        $userStmt->execute([':id'=>$userId]);
        $urow = $userStmt->fetch(PDO::FETCH_ASSOC);
        if ($urow) {
            // find a sensible display name from available columns
            if (!empty($urow['name'])) $patientName = $urow['name'];
            elseif (!empty($urow['full_name'])) $patientName = $urow['full_name'];
            elseif (!empty($urow['first_name']) || !empty($urow['last_name'])) $patientName = trim(($urow['first_name'] ?? '') . ' ' . ($urow['last_name'] ?? ''));
            elseif (!empty($urow['username'])) $patientName = $urow['username'];
            else $patientName = '(user#' . $userId . ')';
        }
    }
    $services = $r['services'] ?? '';
    $occupied[] = ['start'=>$start,'end'=>$end,'appointment_id'=>$r['id'],'patient'=>$patientName,'services'=>$services,'appt_start'=>date('Y-m-d H:i:s',$start),'appt_end'=>date('Y-m-d H:i:s',$end)];
}

echo "Occupied appointments found: " . count($occupied) . "\n";

if (!empty($occupied)) {
    echo "\nOccupied appointments:\n";
    foreach ($occupied as $o) {
        echo " - appt #" . $o['appointment_id'] . " | " . $o['patient'] . " | services: " . ($o['services'] ?: '(none)') . " | " . $o['appt_start'] . " - " . $o['appt_end'] . "\n";
    }
    echo "\n";
}

// compute candidates same as AvailabilityService
$blockSeconds = ($dur + $grace) * 60;
$roundToGranularity = function($ts, $gran) {
    $midnight = strtotime(date('Y-m-d', $ts) . ' 00:00:00');
    $minutesSinceMid = ($ts - $midnight) / 60.0;
    $roundedMinutes = (int)round($minutesSinceMid / $gran) * $gran;
    return $midnight + ($roundedMinutes * 60);
};

$gridStart = $roundToGranularity($dayStart, $granularity);
if ($gridStart < $dayStart) $gridStart = $roundToGranularity($dayStart, $granularity) + ($granularity*60);

$candidates = [];
$step = $granularity * 60;
for ($t = $gridStart; $t + $blockSeconds <= $dayEnd; $t += $step) $candidates[] = $t;

// also add end-derived candidates
foreach ($occupied as $occ) {
    $endTs = (int)$occ['end'];
    if ($endTs >= $dayStart && ($endTs + $blockSeconds) <= $dayEnd) $candidates[] = $endTs;
    $snapped = $roundToGranularity($endTs, $granularity);
    if ($snapped >= $dayStart && ($snapped + $blockSeconds) <= $dayEnd) $candidates[] = $snapped;
}

$candidates = array_values(array_unique($candidates));
sort($candidates);

$slots = [];
foreach ($candidates as $slotStart) {
    $slotEnd = $slotStart + $blockSeconds;
    $isAvailable = true;
    $blockedBy = null;
    // If a dentist was specified, filter out slots that overlap any appointment for that dentist only
    if ($cliDentist) {
        // fetch dentist-specific occupied intervals if not already filtered
        // For simplicity, assume occupied already contains branch-wide occupied; we will filter overlaps by dentist via DB query
        $pdostmt = $pdo->prepare("SELECT a.id, a.appointment_datetime, COALESCE(svc.total_service_minutes, a.procedure_duration, 0) AS duration_minutes FROM appointments a LEFT JOIN (SELECT aps.appointment_id, SUM(COALESCE(s.duration_max_minutes, s.duration_minutes, 0)) AS total_service_minutes FROM appointment_service aps JOIN services s ON s.id = aps.service_id GROUP BY aps.appointment_id) svc ON svc.appointment_id = a.id WHERE DATE(a.appointment_datetime) = :d AND a.branch_id = :b AND a.approval_status IN ('approved','auto_approved') AND a.status NOT IN ('cancelled','rejected','no_show') AND a.dentist_id = :dent ORDER BY a.appointment_datetime");
        $pdostmt->execute([':d'=>$date, ':b'=>$branch['id'], ':dent'=>$cliDentist]);
        $dentistOccupied = [];
        while ($rr = $pdostmt->fetch(PDO::FETCH_ASSOC)) {
            $s = strtotime($rr['appointment_datetime']);
            $dm = (int)$rr['duration_minutes'];
            $dentistOccupied[] = ['start'=>$s,'end'=>$s + ($dm * 60)];
        }
        foreach ($dentistOccupied as $occ) {
            if ($slotStart < $occ['end'] && $slotEnd > $occ['start']) { $isAvailable = false; $blockedBy = $occ; break; }
        }
    } else {
        foreach ($occupied as $occ) {
            if ($slotStart < $occ['end'] && $slotEnd > $occ['start']) { $isAvailable = false; $blockedBy = $occ; break; }
        }
    }
    $slots[] = [ 'time'=>date('g:i A',$slotStart), 'timestamp'=>$slotStart, 'ends_at'=>date('g:i A',$slotEnd), 'available'=>$isAvailable, 'blocked_by'=>$blockedBy];
}

$available = array_values(array_filter($slots, function($s){ return $s['available']; }));

echo "Total candidates: " . count($candidates) . "\n";
echo "Available slots: " . count($available) . "\n";
foreach ($available as $a) echo " - " . $a['time'] . " (ends: " . $a['ends_at'] . ")\n";

$blocked = array_values(array_filter($slots, function($s){ return !$s['available']; }));
echo "\nBlocked candidate slots (occupied by appointments): " . count($blocked) . "\n";
foreach ($blocked as $b) {
    $by = $b['blocked_by'];
    if ($by) {
        $apptId = $by['appointment_id'] ?? '(id?)';
        $p = $by['patient'] ?? '(patient?)';
        $svcs = $by['services'] ?? '';
        echo " - " . $b['time'] . " (ends: " . $b['ends_at'] . ") blocked by appt #{$apptId} ({$p}) services: {$svcs} at {$by['appt_start']} - {$by['appt_end']}\n";
    } else {
        echo " - " . $b['time'] . " (ends: " . $b['ends_at'] . ") blocked by unknown appointment\n";
    }
}

// Done. Next: Frontend check instructions

echo "\nTo verify in frontend:\n";
echo " - Open the booking UI for branch ID {$branch['id']} and the chosen service and date {$date}.\n";
echo " - In the browser devtools Network tab watch the POST to /appointments/available-slots. Ensure the POST body includes either 'service_id'={$svc['id']} or 'duration'={$dur}.\n";
echo " - Compare the returned 'available_slots' timestamps with the list above - they should match.\n";

echo "\nScript finished.\n";
