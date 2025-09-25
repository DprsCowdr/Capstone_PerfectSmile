<?php
// tools/compare_frontend_db_availability.php
// Compute availability from DB and compare against the frontend /appointments/available-slots response
// Usage: php tools/compare_frontend_db_availability.php [granularity] [dentist_id] [date YYYY-MM-DD] [branch_id]

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
$baseUrl = rtrim($env['APP_URL'] ?? $env['BASE_URL'] ?? '', '/') . '/';
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

// parse CLI args
$granularity = isset($argv[1]) ? (int)$argv[1] : 5;
$cliDentist = isset($argv[2]) ? (int)$argv[2] : null;
$cliDate = isset($argv[3]) ? $argv[3] : null;
$cliBranch = isset($argv[4]) ? (int)$argv[4] : null;
// optional explicit frontend base URL override (e.g. http://localhost/yourapp/)
$endpointOverride = isset($argv[5]) ? trim($argv[5]) : null;
if ($endpointOverride) {
    // ensure trailing slash
    $baseUrl = rtrim($endpointOverride, '/') . '/';
}

// pick first active service and branch
$svc = $pdo->query("SELECT id, name, duration_minutes, duration_max_minutes FROM services ORDER BY id LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$svc) { echo "No services found in DB.\n"; exit(1); }
$dur = !empty($svc['duration_max_minutes']) ? (int)$svc['duration_max_minutes'] : (int)$svc['duration_minutes'];

$date = date('Y-m-d');
if ($cliDate && preg_match('/^\d{4}-\d{2}-\d{2}$/', $cliDate)) $date = $cliDate;

$branch = null;
if ($cliBranch) {
    $stmt = $pdo->prepare("SELECT id, name, operating_hours FROM branches WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $cliBranch]);
    $branch = $stmt->fetch(PDO::FETCH_ASSOC);
}
if (!$branch) {
    $branch = $pdo->query("SELECT id, name, operating_hours FROM branches ORDER BY id LIMIT 1")->fetch(PDO::FETCH_ASSOC);
}
if (!$branch) { echo "No branches found in DB.\n"; exit(1); }

// read grace
$grace = 20;
$gpPath = __DIR__ . '/../writable/grace_periods.json';
if (is_file($gpPath)) {
    $gp = json_decode(file_get_contents($gpPath), true);
    if (!empty($gp['default'])) $grace = (int)$gp['default'];
}

echo "Comparing DB availability vs frontend for branch {$branch['id']} ({$branch['name']}) date {$date} service {$svc['id']} (duration {$dur}) granularity {$granularity}\n";

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

// fetch occupied appointments same as test_real_db_availability
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
    $end = $start + ($durMinutes * 60);
    $userId = $r['user_id'] ?? null;
    $patientName = '(unknown)';
    if ($userId) {
        $userStmt->execute([':id'=>$userId]);
        $urow = $userStmt->fetch(PDO::FETCH_ASSOC);
        if ($urow) {
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

// compute candidate slots
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
    foreach ($occupied as $occ) {
        if ($slotStart < $occ['end'] && $slotEnd > $occ['start']) { $isAvailable = false; $blockedBy = $occ; break; }
    }
    $slots[] = [ 'time'=>date('g:i A',$slotStart), 'timestamp'=>$slotStart, 'ends_at'=>date('g:i A',$slotEnd), 'available'=>$isAvailable, 'blocked_by'=>$blockedBy];
}

$available = array_values(array_filter($slots, function($s){ return $s['available']; }));

// Build payload for frontend endpoint
$payload = [
    'date' => $date,
    'branch_id' => $branch['id'],
    'service_id' => $svc['id'],
    'duration' => $dur,
    'granularity' => $granularity
];

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\nX-Requested-With: XMLHttpRequest\r\n",
        'method'  => 'POST',
        'content' => http_build_query($payload),
        'timeout' => 10
    ]
];

$endpoint = $baseUrl . 'appointments/available-slots';
if (empty($baseUrl) || $baseUrl === '/') {
    echo "APP_URL not set in .env and no endpoint override provided - cannot POST to frontend endpoint.\n";
    echo "You can still run this script to print DB computed slots; to compare, either set APP_URL in .env or pass a frontend base URL as the 5th argument.\n";
    echo "Example:\n  php tools/compare_frontend_db_availability.php 5 0 2025-09-24 2 http://localhost/\n";
    echo "You can still run this script to print DB computed slots; to compare, set APP_URL in .env to your local app base (e.g. http://localhost/).\n";
    // Print DB results and exit
    echo "\nDB computed available slots (count: " . count($available) . "):\n";
    foreach ($available as $a) echo " - {$a['time']} (ts: {$a['timestamp']})\n";
    exit(0);
}

$context  = stream_context_create($options);
$result = @file_get_contents($endpoint, false, $context);
if ($result === FALSE) {
    echo "Failed to POST to endpoint {$endpoint}. Check APP_URL and that the site is accessible.\n";
    echo "DB computed available slots (count: " . count($available) . "):\n";
    foreach ($available as $a) echo " - {$a['time']} (ts: {$a['timestamp']})\n";
    exit(1);
}

$resp = json_decode($result, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Frontend response was not valid JSON:\n" . $result . "\n";
    exit(1);
}

// Extract frontend available timestamps (attempt multiple possible shapes)
$frontendSlots = [];
if (!empty($resp['available_slots']) && is_array($resp['available_slots'])) {
    foreach ($resp['available_slots'] as $fs) {
        if (is_array($fs) && isset($fs['timestamp'])) $frontendSlots[] = (int)$fs['timestamp'];
        elseif (is_array($fs) && isset($fs['datetime'])) $frontendSlots[] = (int)(strtotime($fs['datetime']));
        elseif (is_string($fs)) {
            $dt = strtotime($date . ' ' . $fs);
            if ($dt) $frontendSlots[] = $dt;
        }
    }
} elseif (!empty($resp['slots']) && is_array($resp['slots'])) {
    foreach ($resp['slots'] as $fs) {
        if (is_array($fs) && isset($fs['timestamp'])) $frontendSlots[] = (int)$fs['timestamp'];
        elseif (is_array($fs) && isset($fs['datetime'])) $frontendSlots[] = (int)(strtotime($fs['datetime']));
        elseif (is_string($fs)) {
            $dt = strtotime($date . ' ' . $fs);
            if ($dt) $frontendSlots[] = $dt;
        }
    }
}

$frontendSlots = array_values(array_unique($frontendSlots));
sort($frontendSlots);

$dbSlots = array_values(array_unique(array_map(function($s){ return (int)$s['timestamp']; }, $available)));
sort($dbSlots);

// Compare lists
$onlyInDb = array_diff($dbSlots, $frontendSlots);
$onlyInFrontend = array_diff($frontendSlots, $dbSlots);

echo "\nComparison results:\n";
echo " - DB available slots: " . count($dbSlots) . "\n";
echo " - Frontend available slots: " . count($frontendSlots) . "\n";
echo " - Only in DB (" . count($onlyInDb) . "):\n";
foreach ($onlyInDb as $ts) echo "   - " . date('g:i A', $ts) . " (ts: {$ts})\n";
echo " - Only in Frontend (" . count($onlyInFrontend) . "):\n";
foreach ($onlyInFrontend as $ts) echo "   - " . date('g:i A', $ts) . " (ts: {$ts})\n";

if (count($onlyInDb) === 0 && count($onlyInFrontend) === 0) {
    echo "\nSUCCESS: DB and frontend available slots match exactly.\n";
} else {
    echo "\nDIFFERENCES found between DB and frontend.\n";
    echo "See lists above for details.\n";
}

// finished

