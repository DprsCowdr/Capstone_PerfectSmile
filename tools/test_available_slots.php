<?php
// tools/test_available_slots.php
// Usage: php tools/test_available_slots.php 2025-09-20
$date = $argv[1] ?? date('Y-m-d');
$base = 'http://localhost:8080/';
$endpoint = $base . 'appointments/available-slots?__debug_noauth=1';

// Basic DB connection - adapt if your local config differs
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=perfectsmile_db-v1', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo "DB connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

$stmt = $pdo->prepare("SELECT id, appointment_datetime, approval_status, status, user_id, dentist_id FROM appointments WHERE DATE(appointment_datetime) = :d AND approval_status = 'approved' AND status NOT IN ('cancelled','rejected','no_show') ORDER BY appointment_datetime");
$stmt->execute([':d' => $date]);
$appts = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Approved appointments for {$date}: " . count($appts) . "\n";
foreach ($appts as $a) {
    echo " - [ID {$a['id']}] {$a['appointment_datetime']} (user {$a['user_id']})\n";
}

// Print DB-stored procedure_duration and aggregate service durations for today's approved appointments
echo "\nAppointment durations (DB):\n";
$idList = array_column($appts, 'id');
if (!empty($idList)) {
    $in = implode(',', array_map('intval', $idList));
    $q = $pdo->query("SELECT a.id, a.procedure_duration, GROUP_CONCAT(CONCAT(s.duration_minutes,':',s.duration_max_minutes) SEPARATOR ';') as svc_durations FROM appointments a LEFT JOIN appointment_service asv ON asv.appointment_id = a.id LEFT JOIN services s ON s.id = asv.service_id WHERE a.id IN ($in) GROUP BY a.id");
    $rows = $q->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        echo " - [ID {$r['id']}] procedure_duration=" . ($r['procedure_duration'] === null ? 'NULL' : $r['procedure_duration']) . ", service_durations={" . ($r['svc_durations'] ?? '') . "}\n";
    }
}

// Collect dentist IDs present in today's approved appointments
$dentists = [];
foreach ($appts as $a) {
    if (!empty($a['dentist_id'])) $dentists[] = $a['dentist_id'];
}
$dentists = array_values(array_unique($dentists));
if (count($dentists) > 0) {
    echo "Dentists assigned to today's appointments: " . implode(',', $dentists) . "\n";
} else {
    echo "No dentist_id assigned to today's appointments\n";
}

// Call available-slots endpoint
$postFields = http_build_query(['date' => $date, 'duration' => 30]);
// Function to run a request with optional dentist_id
function runRequest($endpoint, $postFields, $dentistId = null) {
    $url = $endpoint;
    $opts = [];
    if ($dentistId) {
        // append dentist query param for debug bypass URL
        $url = $endpoint . '&dentist_id=' . urlencode($dentistId);
    }
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, $resp, $err];
}

// First run without dentist filter
list($code, $resp, $err) = runRequest($endpoint, $postFields, null);

if ($err) {
    echo "HTTP request failed: " . $err . "\n";
    exit(1);
}
if ($code >= 400) {
    echo "HTTP error: {$code}\nResponse:\n{$resp}\n";
    exit(1);
}

$json = json_decode($resp, true);
if (!$json) {
    echo "Invalid JSON response:\n" . $resp . "\n";
    exit(1);
}

$all = $json['all_slots'] ?? ($json['slots'] ?? []);
$unavailable = $json['unavailable_slots'] ?? [];
$occupied = $json['occupied_map'] ?? [];
$meta = $json['metadata'] ?? [];

echo "Endpoint returned: total_slots=" . count($all) . ", available_count=" . ($meta['available_count'] ?? count(array_filter($all, function($s){return ($s['available'] ?? true) === true;}))) . ", unavailable_count=" . ($meta['unavailable_count'] ?? count($unavailable)) . "\n";

// Check that each approved appointment appears in occupied_map and blocks slots
$missing = [];
foreach ($appts as $a) {
    $found = false;
    foreach ($occupied as $occ) {
        if (!empty($occ['appointment_id']) && $occ['appointment_id'] == $a['id']) {
            $found = true; break;
        }
    }
    if (!$found) $missing[] = $a['id'];
}

if (count($missing) > 0) {
    echo "Warning: The following approved appointment IDs were NOT found in occupied_map: " . implode(',', $missing) . "\n";
} else {
    echo "All approved appointments appear in occupied_map.\n";
}

// For each occupied appointment, check that overlapping slots are marked unavailable
function overlaps($slotTs, $occStart, $occEnd, $blockSeconds) {
    $slotStart = $slotTs;
    $slotEnd = $slotTs + $blockSeconds;
    return ($slotStart < $occEnd && $slotEnd > $occStart);
}

$blockSeconds = (30 + ($json['metadata']['grace_minutes'] ?? 20)) * 60;

$slotTsMap = [];
foreach ($all as $s) {
    if (isset($s['timestamp'])) $slotTsMap[$s['timestamp']] = $s;
}

$slotProblems = [];
foreach ($occupied as $occ) {
    $occStart = strtotime($occ['start']);
    $occEnd = strtotime($occ['end']);
    $occId = $occ['appointment_id'] ?? null;
    $blockedCount = 0;
    foreach ($slotTsMap as $ts => $s) {
        if (overlaps($ts, $occStart, $occEnd, $blockSeconds)) {
            if ($s['available'] === false) $blockedCount++; else $slotProblems[] = [ 'occ' => $occId, 'slot' => $s ];
        }
    }
    echo "Occupied appointment {$occId} overlaps with {$blockedCount} blocked slot candidates\n";
}

if (count($slotProblems) > 0) {
    echo "Problems found: the following overlapping candidate slots were NOT marked unavailable:\n";
    foreach ($slotProblems as $p) {
        echo " - appointment {$p['occ']} slot {$p['slot']['datetime']}\n";
    }
} else {
    echo "All overlapping slots for occupied appointments are marked unavailable.\n";
}

// If dentists exist, run the same test per dentist to check dentist-specific blocking
foreach ($dentists as $did) {
    echo "\n--- Re-testing with dentist_id={$did} ---\n";
    list($code2, $resp2, $err2) = runRequest($endpoint, $postFields, $did);
    if ($err2) { echo "HTTP request failed: " . $err2 . "\n"; continue; }
    if ($code2 >= 400) { echo "HTTP error: {$code2}\nResponse:\n{$resp2}\n"; continue; }
    $j2 = json_decode($resp2, true);
    if (!$j2) { echo "Invalid JSON response for dentist {$did}:\n" . $resp2 . "\n"; continue; }
    $all2 = $j2['all_slots'] ?? ($j2['slots'] ?? []);
    $unavail2 = $j2['unavailable_slots'] ?? [];
    $occ2 = $j2['occupied_map'] ?? [];
    $meta2 = $j2['metadata'] ?? [];
    echo "Dentist {$did}: total_slots=" . count($all2) . ", available_count=" . ($meta2['available_count'] ?? count(array_filter($all2, function($s){return ($s['available'] ?? true) === true;}))) . ", unavailable_count=" . ($meta2['unavailable_count'] ?? count($unavail2)) . "\n";
    // Check occupied_map contains appointments (same logic)
    $missing2 = [];
    foreach ($appts as $a) {
        $found = false;
        foreach ($occ2 as $o) {
            if (!empty($o['appointment_id']) && $o['appointment_id'] == $a['id']) { $found = true; break; }
        }
        if (!$found) $missing2[] = $a['id'];
    }
    if (count($missing2) > 0) echo "Warning: missing in occupied_map for dentist {$did}: " . implode(',',$missing2) . "\n"; else echo "All approved appointments present in occupied_map for dentist {$did}.\n";
    // Now perform blocking overlap checks for this dentist
    $slotTsMap2 = [];
    foreach ($all2 as $s) { if (isset($s['timestamp'])) $slotTsMap2[$s['timestamp']] = $s; }
    $slotProblems2 = [];
    foreach ($occ2 as $occItem) {
        $occStart = strtotime($occItem['start']);
        $occEnd = strtotime($occItem['end']);
        $occId = $occItem['appointment_id'] ?? null;
        $blockedCount = 0;
        foreach ($slotTsMap2 as $ts => $s) {
            if (overlaps($ts, $occStart, $occEnd, $blockSeconds)) {
                if (isset($s['available']) && $s['available'] === false) $blockedCount++; else $slotProblems2[] = ['occ' => $occId, 'slot' => $s];
            }
        }
        echo "[dentist {$did}] Occupied appointment {$occId} overlaps with {$blockedCount} blocked slot candidates\n";
    }
    if (count($slotProblems2) > 0) {
        echo "[dentist {$did}] Problems found: overlapping slots NOT marked unavailable:\n";
        foreach ($slotProblems2 as $p) {
            echo " - appointment {$p['occ']} slot {$p['slot']['datetime']}\n";
        }
    } else {
        echo "[dentist {$did}] All overlapping slots for occupied appointments are marked unavailable.\n";
    }
}

exit(0);
