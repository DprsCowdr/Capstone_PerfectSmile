<?php
// Smoke test for FCFS selection logic using HTTP endpoints (login + call-auto)
$mysqli = new mysqli('127.0.0.1','root','','perfectsmile_db-v1',3306);
if ($mysqli->connect_errno){ echo 'DB connect failed: '.$mysqli->connect_error; exit(1); }

function ensure_patient($mysqli, $name, $email) {
    $emailEsc = $mysqli->real_escape_string($email);
    $res = $mysqli->query("SELECT id FROM user WHERE email='{$emailEsc}' LIMIT 1");
    if ($res && $r = $res->fetch_assoc()) return $r['id'];
    $now = date('Y-m-d H:i:s');
    $stmt = $mysqli->prepare("INSERT INTO user (name,email,user_type,status,password,created_at) VALUES (?,?,?,?,?,?)");
    $type = 'patient'; $status='active'; $pwd = password_hash('Password123', PASSWORD_DEFAULT);
    $stmt->bind_param('ssssss', $name, $email, $type, $status, $pwd, $now);
    $stmt->execute();
    return $mysqli->insert_id;
}

function ensure_staff($mysqli, $name, $email) {
    $emailEsc = $mysqli->real_escape_string($email);
    $res = $mysqli->query("SELECT id FROM user WHERE email='{$emailEsc}' LIMIT 1");
    if ($res && $r = $res->fetch_assoc()) return $r['id'];
    $now = date('Y-m-d H:i:s');
    $stmt = $mysqli->prepare("INSERT INTO user (name,email,user_type,status,password,created_at) VALUES (?,?,?,?,?,?)");
    $type = 'staff'; $status='active'; $pwd = password_hash('Password123', PASSWORD_DEFAULT);
    $stmt->bind_param('ssssss', $name, $email, $type, $status, $pwd, $now);
    $stmt->execute();
    return $mysqli->insert_id;
}

$date = date('Y-m-d');
$appt_datetime = $date . ' 09:00:00';
$apptB_checkin_time = '08:55:00';
$dentist = 30; // test dentist id used in other tools
$branch = 1;

echo "Preparing smoke HTTP test for date: {$date}\n";

// Ensure patients
$patientA = ensure_patient($mysqli, 'Smoke Scheduled A', 'smoke_a@example.local');
$patientB = ensure_patient($mysqli, 'Smoke Walkin B', 'smoke_b@example.local');
$staff = ensure_staff($mysqli, 'Smoke Staff', 'smoke_staff@example.local');

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

// Find working base URL
$candidates = [
    'http://localhost',
    'http://localhost/index.php',
    'http://localhost/public/index.php',
    'http://127.0.0.1',
    'http://127.0.0.1/index.php',
    'http://127.0.0.1/public/index.php',
];
$base = null;
foreach ($candidates as $c) {
    $t = $c . '/';
    $chT = curl_init($t);
    curl_setopt($chT, CURLOPT_NOBODY, true);
    curl_setopt($chT, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($chT, CURLOPT_TIMEOUT, 2);
    curl_exec($chT);
    $code = curl_getinfo($chT, CURLINFO_HTTP_CODE);
    curl_close($chT);
    if ($code > 0 && $code != 404) { $base = rtrim($c, '/'); break; }
}
if (!$base) { echo "No reachable base URL found. Start your dev server.\n"; exit(10); }

echo "Using base URL: {$base}\n";

// Perform login to obtain session cookie
$cookieFile = sys_get_temp_dir() . '/smoke_auth_cookies.txt';
$loginCandidates = [
    $base . '/auth/login',
    $base . '/index.php/auth/login',
    $base . '/public/index.php/auth/login',
    $base . '/Auth/login',
];
$res = null; $code = 0; $loginUrl = null;
foreach ($loginCandidates as $lc) {
    $ch = curl_init($lc);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['email' => 'smoke_staff@example.local', 'password' => 'Password123']));
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code > 0 && $code != 404) { $loginUrl = $lc; break; }
}

if ($code >= 400 || !$loginUrl) { echo "Login failed with HTTP {$code}. Response: {$res}\nTried: " . implode(', ', $loginCandidates) . "\n"; exit(11); }

echo "Login succeeded against {$loginUrl}, cookies stored in {$cookieFile}\n";

// Call call-auto with session cookie (try index.php variants)
$callCandidates = [
    $base . '/queue/call-auto',
    $base . '/index.php/queue/call-auto',
    $base . '/public/index.php/queue/call-auto',
];
$resp = null; $code2 = 0; $used = null;
foreach ($callCandidates as $cc) {
    $ch2 = curl_init($cc);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_POST, true);
    curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch2, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch2, CURLOPT_HTTPHEADER, ["X-Requested-With: XMLHttpRequest", 'Content-Type: application/json']);
    curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode(new stdClass()));
    curl_setopt($ch2, CURLOPT_TIMEOUT, 5);
    $resp = curl_exec($ch2);
    $code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    curl_close($ch2);
    echo "Tried {$cc} -> HTTP {$code2}\n";
    if ($code2 > 0 && $code2 != 404) { $used = $cc; break; }
}

echo "call-auto tried, used: " . ($used ?: 'none') . " -> HTTP {$code2}: {$resp}\n";

// Check database for ongoing appointment
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

// Confirm A remains scheduled
$r = $mysqli->query("SELECT id,status FROM appointments WHERE id={$apptA_id}");
$ra = $r->fetch_assoc();
if ($ra['status'] === 'scheduled') {
    echo "ASSERT PASS: Appointment A (id={$apptA_id}) remains scheduled.\n";
} else {
    echo "ASSERT FAIL: Appointment A status is {$ra['status']} (expected 'scheduled').\n";
    exit(8);
}

echo "Smoke HTTP test complete.\n";

?>