<?php
// Smoke test for FCFS selection logic
$mysqli = new mysqli('127.0.0.1','root','','perfectsmile_db-v1',3306);
if ($mysqli->connect_errno){ echo 'DB connect failed: '.$mysqli->connect_error; exit(1); }

function ensure_patient($mysqli, $name, $email) {
    $emailEsc = $mysqli->real_escape_string($email);
    $res = $mysqli->query("SELECT id FROM user WHERE email='{$emailEsc}' LIMIT 1");
    if ($res && $r = $res->fetch_assoc()) return $r['id'];
    $now = date('Y-m-d H:i:s');
    $stmt = $mysqli->prepare("INSERT INTO user (name,email,user_type,status,created_at) VALUES (?,?,?,?,?)");
    $type = 'patient'; $status='active';
    $stmt->bind_param('sssss', $name, $email, $type, $status, $now);
    $stmt->execute();
    return $mysqli->insert_id;
}

$date = date('Y-m-d');
$apptA_time = '09:00:00';
$apptB_checkin_time = '08:55:00';
$appt_datetime = $date . ' 09:00:00';
$dentist = 30; // test dentist id used in other tools
$branch = 1;

echo "Preparing smoke test for date: {$date}\n";

// Ensure patients
$patientA = ensure_patient($mysqli, 'Smoke Scheduled A', 'smoke_a@example.local');
$patientB = ensure_patient($mysqli, 'Smoke Walkin B', 'smoke_b@example.local');

// Remove prior test appointments for this dentist/time to avoid duplicates
$mysqli->query("DELETE FROM appointments WHERE dentist_id={$dentist} AND appointment_datetime='".$mysqli->real_escape_string($appt_datetime)."' AND (user_id IN ({$patientA},{$patientB}) OR created_at > DATE_SUB(NOW(), INTERVAL 1 DAY))");

// Insert appointment A (scheduled, not checked-in)
$stmt = $mysqli->prepare("INSERT INTO appointments (user_id,branch_id,dentist_id,appointment_datetime,procedure_duration,status,approval_status,appointment_type,created_at) VALUES (?,?,?,?,?,?,?,?,NOW())");
$statusA = 'scheduled'; $approvalA = 'approved'; $typeA='scheduled'; $dur = 30;
$stmt->bind_param('iiisisss', $patientA, $branch, $dentist, $appt_datetime, $dur, $statusA, $approvalA, $typeA);
$stmt->execute();
$apptA_id = $mysqli->insert_id;
echo "Inserted scheduled appointment A id={$apptA_id} at {$appt_datetime}\n";

// Insert appointment B (walk-in), mark confirmed and auto_approved, then set checked_in
$stmt = $mysqli->prepare("INSERT INTO appointments (user_id,branch_id,dentist_id,appointment_datetime,procedure_duration,status,approval_status,appointment_type,created_at) VALUES (?,?,?,?,?,?,?,?,NOW())");
$statusB = 'confirmed'; $approvalB = 'auto_approved'; $typeB='walkin';
$stmt->bind_param('iiisisss', $patientB, $branch, $dentist, $appt_datetime, $dur, $statusB, $approvalB, $typeB);
$stmt->execute();
$apptB_id = $mysqli->insert_id;
echo "Inserted walk-in appointment B id={$apptB_id} at {$appt_datetime}\n";

// Simulate check-in for B at 08:55 and update appointment status to checked_in
$checked_in_at = $date . ' ' . $apptB_checkin_time;
    $ci_checked_in_at = $mysqli->real_escape_string($checked_in_at);
    $mysqli->query("INSERT INTO patient_checkins (appointment_id, checked_in_at, checked_in_by, self_checkin, checkin_method, created_at) VALUES ({$apptB_id}, '{$ci_checked_in_at}', NULL, 0, 'staff', NOW())");
$mysqli->query("UPDATE appointments SET status='checked_in', updated_at=NOW() WHERE id={$apptB_id}");
echo "Inserted check-in for B at {$checked_in_at} and updated status to checked_in\n";

// Now call the real MVC endpoint to trigger the auto-call flow
echo "Calling /queue/call-auto endpoint to trigger FCFS selection...\n";

// Use local HTTP request - assumes server is reachable at http://localhost and project served accordingly
$candidates = [
    'http://localhost/queue/call-auto',
    'http://localhost/index.php/queue/call-auto',
    'http://localhost/public/index.php/queue/call-auto',
    'http://127.0.0.1/queue/call-auto',
    'http://127.0.0.1/index.php/queue/call-auto',
    'http://127.0.0.1/public/index.php/queue/call-auto',
];

$ch = null;
$url = null;
foreach ($candidates as $candidate) {
    $chTest = curl_init($candidate);
    curl_setopt($chTest, CURLOPT_NOBODY, true);
    curl_setopt($chTest, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($chTest, CURLOPT_TIMEOUT, 3);
    $res = curl_exec($chTest);
    $code = curl_getinfo($chTest, CURLINFO_HTTP_CODE);
    curl_close($chTest);
    if ($res !== false && $code > 0 && $code != 404) {
        $url = $candidate;
        break;
    }
}

if (!$url) {
    echo "Could not locate a running web server for /queue/call-auto. Tried candidates:\n" . implode("\n", $candidates) . "\n";
    echo "Ensure your app is being served (php -S or Apache) and the base URL is correct.\n";
    exit(5);
}

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-Requested-With: XMLHttpRequest", 'Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(new stdClass()));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-Requested-With: XMLHttpRequest", 'Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(new stdClass()));

$response = curl_exec($ch);
if ($response === false) {
    echo "HTTP request failed: " . curl_error($ch) . "\n";
    curl_close($ch);
    exit(5);
}
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Endpoint returned HTTP {$httpCode}: {$response}\n";

// Parse JSON response if any
$resp = json_decode($response, true);

// After the controller runs, query DB for which appointment became ongoing
$r2 = $mysqli->query("SELECT id,status FROM appointments WHERE status='ongoing' AND DATE(appointment_datetime)='{$date}' AND dentist_id={$dentist} LIMIT 1");
$ongoing = $r2 ? $r2->fetch_assoc() : null;

if ($ongoing) {
    echo "Ongoing appointment after endpoint call: id={$ongoing['id']} status={$ongoing['status']}\n";
    if (intval($ongoing['id']) === intval($apptB_id)) {
        echo "TEST PASS: Walk-in B was started via MVC endpoint.\n";
    } else {
        echo "TEST FAIL: Expected B (id={$apptB_id}) to be started, got id={$ongoing['id']}\n";
        exit(6);
    }
} else {
    echo "TEST FAIL: No appointment was started.\n";
    exit(7);
}

// Confirm A remains scheduled (not marked as no_show)
$r = $mysqli->query("SELECT id,status FROM appointments WHERE id={$apptA_id}");
$ra = $r->fetch_assoc();
if ($ra['status'] === 'scheduled') {
    echo "ASSERT PASS: Appointment A (id={$apptA_id}) remains scheduled.\n";
} else {
    echo "ASSERT FAIL: Appointment A status is {$ra['status']} (expected 'scheduled').\n";
    exit(8);
}

// Demonstrate a reschedule option for A: find next available slot after 09:00 (simple 5-min step up to +120 mins)
function find_next_slot($mysqli, $date, $preferredTime, $dentistId, $graceMinutes=15, $lookaheadMinutes=120) {
    $preferredTs = strtotime($date . ' ' . $preferredTime);
    $step = 5 * 60;
    $limitTs = $preferredTs + ($lookaheadMinutes * 60);
    for ($ts = $preferredTs; $ts <= $limitTs; $ts += $step) {
        $candidate = date('Y-m-d H:i:s', $ts);
        // check conflicts within +/- grace (we'll use 15min window)
        $startWin = date('Y-m-d H:i:s', $ts - ($graceMinutes*60));
        $endWin = date('Y-m-d H:i:s', $ts + ($graceMinutes*60));
        $sql = "SELECT COUNT(*) as c FROM appointments WHERE appointment_datetime >= '{$startWin}' AND appointment_datetime <= '{$endWin}' AND dentist_id = {$dentistId} AND status IN ('confirmed','checked_in','ongoing') AND approval_status IN ('approved','auto_approved')";
        $r = $mysqli->query($sql);
        $cnt = ($r->fetch_assoc())['c'];
        if ($cnt == 0) return date('H:i', $ts);
    }
    return null;
}

$nextSlot = find_next_slot($mysqli, $date, '09:00:00', $dentist);
if ($nextSlot) {
    echo "Suggested next available slot for A: {$nextSlot}\n";
    // Optionally perform reschedule (commented out by default)
    // $newDt = $date . ' ' . $nextSlot . ':00';
    // $mysqli->query("UPDATE appointments SET appointment_datetime='{$newDt}', updated_at=NOW() WHERE id={$apptA_id}");
    // echo "Rescheduled A to {$newDt}\n";
} else {
    echo "No slot found in lookahead window.\n";
}

echo "Smoke test complete.\n";

?>