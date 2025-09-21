<?php
// Diagnose service duration wiring
// Usage: php diagnose_service_duration.php [service_id]
// This script will:
//  - verify services table has duration_minutes and duration_max_minutes columns
//  - list services with non-null durations
//  - if service_id provided, create a disposable appointment (tomorrow 12:00) with that service,
//    link appointment_service and persist computed procedure_duration, then print the rows.

$host = getenv('DB_HOST') ?: '127.0.0.1';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$db   = getenv('DB_NAME') ?: 'perfectsmile_db-v1';
$port = getenv('DB_PORT') ?: 3306;

$serviceId = $argv[1] ?? null;

$mysqli = new mysqli($host, $user, $pass, $db, (int)$port);
if ($mysqli->connect_errno) {
    echo "DB connect failed: " . $mysqli->connect_error . PHP_EOL;
    exit(1);
}

echo "Connected to DB {$db} at {$host}:{$port}\n\n";

// 1) Check columns
$res = $mysqli->query("SHOW COLUMNS FROM `services`");
if (!$res) {
    echo "Failed to show columns: " . $mysqli->error . PHP_EOL;
    exit(2);
}
$cols = [];
while ($r = $res->fetch_assoc()) {
    $cols[] = $r['Field'];
}
$hasDuration = in_array('duration_minutes', $cols);
$hasDurationMax = in_array('duration_max_minutes', $cols);

echo "services table columns: " . implode(', ', $cols) . PHP_EOL;
echo "duration_minutes present: " . ($hasDuration ? 'YES' : 'NO') . PHP_EOL;
echo "duration_max_minutes present: " . ($hasDurationMax ? 'YES' : 'NO') . PHP_EOL;

// 2) List services with durations
echo "\nServices with durations:\n";
$sql = "SELECT id, name, duration_minutes, duration_max_minutes FROM services ORDER BY id LIMIT 200";
$res = $mysqli->query($sql);
if (!$res) {
    echo "Failed to select services: " . $mysqli->error . PHP_EOL;
    exit(3);
}
$hasAny = false;
while ($r = $res->fetch_assoc()) {
    if (!is_null($r['duration_minutes']) || !is_null($r['duration_max_minutes'])) {
        $hasAny = true;
        echo sprintf("  [%d] %s  -> duration_minutes=%s, duration_max_minutes=%s\n", $r['id'], $r['name'], $r['duration_minutes'] ?? 'NULL', $r['duration_max_minutes'] ?? 'NULL');
    }
}
if (!$hasAny) echo "  (no services with duration fields set)\n";

// 3) If service_id provided: create test appointment and link
if ($serviceId) {
    echo "\n-- Running linking test for service_id={$serviceId} --\n";
    // ensure service exists
    $r = $mysqli->query("SELECT id, name, duration_minutes, duration_max_minutes FROM services WHERE id = " . (int)$serviceId)->fetch_assoc();
    if (!$r) {
        echo "Service id {$serviceId} not found\n";
        exit(4);
    }
    echo "Found service: {$r['name']} durations: duration_minutes={$r['duration_minutes']} duration_max_minutes={$r['duration_max_minutes']}\n";

    // ensure a patient user exists
    $res = $mysqli->query("SELECT id FROM user WHERE user_type='patient' LIMIT 1");
    if ($res && $x = $res->fetch_assoc()) {
        $patientId = $x['id'];
        echo "Using existing patient id {$patientId}\n";
    } else {
        $mysqli->query("INSERT INTO user (name,email,user_type,status,created_at) VALUES ('Diagnose Patient','diag@example.local','patient','active',NOW())");
        $patientId = $mysqli->insert_id;
        echo "Created test patient id {$patientId}\n";
    }

    $date = date('Y-m-d', strtotime('+1 day'));
    $datetime = $date . ' 12:00:00';

    // Insert appointment (minimal safe columns). Use branch_id=1, dentist_id=NULL
    $stmt = $mysqli->prepare("INSERT INTO appointments (user_id, branch_id, dentist_id, appointment_datetime, status, approval_status, created_at) VALUES (?, ?, NULL, ?, 'pending_approval', 'pending', NOW())");
    if (!$stmt) {
        echo "Prepare failed: " . $mysqli->error . PHP_EOL;
        exit(5);
    }
    $branch = 1;
    $stmt->bind_param('iis', $patientId, $branch, $datetime);
    $stmt->execute();
    $apptId = $mysqli->insert_id;
    if (!$apptId) {
        echo "Failed to insert appointment: " . $mysqli->error . PHP_EOL;
        exit(6);
    }
    echo "Inserted appointment id={$apptId} at {$datetime}\n";

    // Insert appointment_service link
    $stmt2 = $mysqli->prepare("INSERT INTO appointment_service (appointment_id, service_id) VALUES (?, ?)");
    if (!$stmt2) {
        echo "Prepare failed (aps): " . $mysqli->error . PHP_EOL;
        // cleanup
        $mysqli->query("DELETE FROM appointments WHERE id = " . (int)$apptId);
        exit(7);
    }
    $stmt2->bind_param('ii', $apptId, $serviceId);
    $stmt2->execute();
    echo "Linked service {$serviceId} to appointment {$apptId}\n";

    // Compute total duration from services for this appointment
    $sql = "SELECT SUM(COALESCE(s.duration_max_minutes, s.duration_minutes, 0)) AS total_service_minutes FROM appointment_service aps JOIN services s ON s.id = aps.service_id WHERE aps.appointment_id = " . (int)$apptId;
    $res = $mysqli->query($sql);
    $sumRow = $res ? $res->fetch_assoc() : null;
    $sum = ($sumRow && $sumRow['total_service_minutes']) ? (int)$sumRow['total_service_minutes'] : 0;
    echo "Computed total_service_minutes={$sum}\n";

    // Persist on appointments.procedure_duration
    if ($sum > 0) {
        $up = $mysqli->query("UPDATE appointments SET procedure_duration = " . (int)$sum . " WHERE id = " . (int)$apptId);
        if ($up) echo "Updated appointments.procedure_duration to {$sum}\n";
        else echo "Failed to update procedure_duration: " . $mysqli->error . PHP_EOL;
    } else {
        echo "Total duration computed as 0 — not updating appointment\n";
    }

    // Print result rows
    $ap = $mysqli->query("SELECT id, appointment_datetime, procedure_duration, status FROM appointments WHERE id = " . (int)$apptId)->fetch_assoc();
    $links = $mysqli->query("SELECT * FROM appointment_service WHERE appointment_id = " . (int)$apptId)->fetch_all(MYSQLI_ASSOC);

    echo "\nAppointment row:\n" . print_r($ap, true) . "\n";
    echo "Linked appointment_service rows:\n" . print_r($links, true) . "\n";

    echo "\nTest complete. You can remove the test appointment with:\n  DELETE FROM appointment_service WHERE appointment_id = {$apptId};\n  DELETE FROM appointments WHERE id = {$apptId};\n";
}

echo "\nDone.\n";
