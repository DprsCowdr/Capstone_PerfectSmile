<?php
// Test: walk-in A arrives earlier than scheduled booking B (both around 9:00)
// Scenario: A checks-in at 08:55 (walk-in), B has online booking at 09:00.
// Expectation: A gets priority (checked-in earlier). The system should suggest next available slot for B (closest open slot) and allow rescheduling.

$mysqli = new mysqli('127.0.0.1','root','','perfectsmile_db-v1',3306);
if ($mysqli->connect_errno){ echo 'DB connect failed: '.$mysqli->connect_error; exit(1); }

function ensure_user($mysqli, $name, $email, $type = 'patient') {
    $emailEsc = $mysqli->real_escape_string($email);
    $res = $mysqli->query("SELECT id FROM user WHERE email='{$emailEsc}' LIMIT 1");
    if ($res && $r = $res->fetch_assoc()) return $r['id'];
    $now = date('Y-m-d H:i:s');
    $pwd = password_hash('Password123', PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("INSERT INTO user (name,email,user_type,status,password,created_at) VALUES (?,?,?,?,?,?)");
    $status='active';
    $stmt->bind_param('ssssss', $name, $email, $type, $status, $pwd, $now);
    $stmt->execute();
    return $mysqli->insert_id;
}

$date = date('Y-m-d');
$appt_datetime = $date . ' 09:00:00';
$walkin_checkin_ts = $date . ' 08:55:00';
$dentist = 30;
$branch = 1;

echo "Setting up test users and appointments...\n";
$A = ensure_user($mysqli, 'Walkin A', 'walkin_a@example.local');
$B = ensure_user($mysqli, 'Online B', 'online_b@example.local');

// Clean up old test rows (best-effort)
$mysqli->query("DELETE FROM appointments WHERE user_id IN ({$A},{$B}) AND DATE(appointment_datetime) = '{$date}'");
$mysqli->query("DELETE FROM patient_checkins WHERE appointment_id NOT IN (SELECT id FROM appointments)");

// Create walk-in A appointment (walkin, auto_approved, confirmed)
$stmt = $mysqli->prepare("INSERT INTO appointments (user_id,branch_id,dentist_id,appointment_datetime,procedure_duration,status,approval_status,appointment_type,created_at) VALUES (?,?,?,?,?,?,?,?,NOW())");
$dur = 30; $status = 'confirmed'; $approval='auto_approved'; $atype='walkin';
$stmt->bind_param('iiisisss', $A, $branch, $dentist, $appt_datetime, $dur, $status, $approval, $atype);
$stmt->execute();
$A_appt = $mysqli->insert_id;
echo "Inserted walk-in A appointment id={$A_appt} at {$appt_datetime}\n";

// Create online booking B at 09:00 (scheduled)
$stmt = $mysqli->prepare("INSERT INTO appointments (user_id,branch_id,dentist_id,appointment_datetime,procedure_duration,status,approval_status,appointment_type,created_at) VALUES (?,?,?,?,?,?,?,?,NOW())");
$statusB = 'scheduled'; $approvalB = 'approved'; $typeB='scheduled';
$stmt->bind_param('iiisisss', $B, $branch, $dentist, $appt_datetime, $dur, $statusB, $approvalB, $typeB);
$stmt->execute();
$B_appt = $mysqli->insert_id;
echo "Inserted online booking B appointment id={$B_appt} at {$appt_datetime}\n";

// Simulate A checking in at 08:55
$ci = $mysqli->real_escape_string($walkin_checkin_ts);
$mysqli->query("INSERT INTO patient_checkins (appointment_id, checked_in_at, checked_in_by, self_checkin, checkin_method, created_at) VALUES ({$A_appt}, '{$ci}', NULL, 0, 'staff', NOW())");
$mysqli->query("UPDATE appointments SET status='checked_in', updated_at=NOW() WHERE id={$A_appt}");

echo "Walk-in A checked in at {$walkin_checkin_ts}\n";

// Now selection logic (what the system would do):
// 1) expire scheduled overdue (skipped here because it's right at 9:00)
// 2) prefer checked-in patients ordered by checked_in_at

$sql = "SELECT a.id, a.user_id, u.name as patient_name, pc.checked_in_at, a.status FROM appointments a JOIN patient_checkins pc ON pc.appointment_id = a.id JOIN user u ON u.id = a.user_id WHERE DATE(a.appointment_datetime) = '{$date}' AND a.status = 'checked_in' ORDER BY pc.checked_in_at ASC LIMIT 1";
$res = $mysqli->query($sql);
$chosen = $res ? $res->fetch_assoc() : null;
if ($chosen) {
    echo "Chosen by checked-in rule: id={$chosen['id']}, patient={$chosen['patient_name']}, checked_in_at={$chosen['checked_in_at']}\n";
} else {
    echo "No checked-in patient found (unexpected)\n";
}

// Assert chosen is A
if (!$chosen || intval($chosen['id']) !== intval($A_appt)) {
    echo "TEST FAIL: Expected walk-in A (id={$A_appt}) to be chosen, got: "; var_export($chosen); echo "\n"; exit(2);
} else {
    echo "TEST PASS: Walk-in A selected as expected.\n";
}

// Next: compute next available slot for B using a 5-minute step search, respecting a 15-min grace window
function find_next_slot_local($mysqli, $date, $preferredTime, $dentistId, $graceMinutes = 15, $lookaheadMinutes = 180) {
    $preferredTs = strtotime($date . ' ' . $preferredTime);
    $step = 5 * 60;
    $limitTs = $preferredTs + ($lookaheadMinutes * 60);
    for ($ts = $preferredTs; $ts <= $limitTs; $ts += $step) {
        $candidate = date('Y-m-d H:i:s', $ts);
        $startWin = date('Y-m-d H:i:s', $ts - ($graceMinutes*60));
        $endWin = date('Y-m-d H:i:s', $ts + ($graceMinutes*60));
        $sql = "SELECT COUNT(*) as c FROM appointments WHERE appointment_datetime >= '{$startWin}' AND appointment_datetime <= '{$endWin}' AND dentist_id = {$dentistId} AND status IN ('confirmed','checked_in','ongoing') AND approval_status IN ('approved','auto_approved')";
        $r = $mysqli->query($sql);
        $cnt = ($r->fetch_assoc())['c'];
        if ($cnt == 0) return date('H:i', $ts);
    }
    return null;
}

$nextSlot = find_next_slot_local($mysqli, $date, '09:00:00', $dentist);
if ($nextSlot) {
    echo "Suggested next available slot for B: {$nextSlot}\n";
    // Simulate reschedule to that slot
    $newDt = $date . ' ' . $nextSlot . ':00';
    $mysqli->query("UPDATE appointments SET appointment_datetime='{$newDt}', status='scheduled', updated_at=NOW() WHERE id={$B_appt}");
    echo "Rescheduled B (id={$B_appt}) to {$newDt}\n";
} else {
    echo "No available slot found within lookahead window.\n";
}

// Show final statuses
$r = $mysqli->query("SELECT id, status, appointment_datetime FROM appointments WHERE id IN ({$A_appt}, {$B_appt})");
while ($row = $r->fetch_assoc()) {
    echo "Appointment {$row['id']} -> status={$row['status']} at {$row['appointment_datetime']}\n";
}

echo "Test complete.\n";

?>