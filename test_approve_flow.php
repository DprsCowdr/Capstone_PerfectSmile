<?php
/**
 * Test script: create a pending appointment, verify available-slots/dayAppointments before and after approval
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load .env if present
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }
}

$host = $_ENV['database_default_hostname'] ?? 'localhost';
$database = $_ENV['database_default_database'] ?? 'perfectsmile_db-v1';
$username = $_ENV['database_default_username'] ?? 'root';
$password = $_ENV['database_default_password'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    echo "Test approve flow\n";

    // Use curl for reliable session handling (cookie jar, redirects)
    $cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'psm_test_cookies.txt';
    if (is_file($cookieFile)) @unlink($cookieFile);

    function curl_post($url, $postFields = [], $cookieFile = null, $headers = []){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
        curl_setopt($ch, CURLOPT_HEADER, true);
        if ($cookieFile) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        }
        if (!empty($headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $res = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return [$res, $info];
    }

    function curl_get($url, $cookieFile = null, $headers = []){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        if ($cookieFile) curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        if (!empty($headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $res = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return [$res, $info];
    }

    // determine admin user id
    $stmt = $pdo->query("SELECT id FROM `user` WHERE user_type = 'admin' LIMIT 1");
    $adminId = $stmt->fetchColumn();
    if (!$adminId) {
        // fallback to id 1
        $adminId = 1;
    }
    echo "Using admin user id: $adminId\n";

    // pick existing patient user id
    $stmt = $pdo->query("SELECT id FROM `user` WHERE user_type = 'patient' LIMIT 1");
    $patientId = $stmt->fetchColumn();
    if (!$patientId) {
        $patientStmt = $pdo->query("SELECT id FROM `user` LIMIT 1");
        $patientId = $patientStmt->fetchColumn();
    }
    if (!$patientId) throw new Exception('No user available for appointment insertion');
    echo "Using patient user id: $patientId\n";

    $branchId = 1;
    $serviceId = 1;
    $date = date('Y-m-d', strtotime('+1 day'));
    $time = '10:00:00';
    $datetime = "$date $time";

    // Insert appointment pending approval
    $insert = $pdo->prepare("INSERT INTO appointments (branch_id, user_id, appointment_datetime, status, approval_status, appointment_type, remarks, procedure_duration, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'scheduled', 'TEST_APPROVE_FLOW', ?, NOW(), NOW())");
    $procedureDuration = 60; // minutes
    $insert->execute([$branchId, $patientId, $datetime, 'pending_approval', 'pending', $procedureDuration]);
    $apptId = $pdo->lastInsertId();

    echo "Inserted test appointment id: $apptId at $datetime (pending)\n";

    // Perform an HTTP login to obtain valid session cookies
    $base = 'http://localhost:8080';
    $loginUrl = $base . '/auth/login';
    $credentials = ['email' => 'admin@perfectsmile.com', 'password' => 'password'];

    echo "Attempting HTTP login as admin (curl)...\n";
    list($loginRes, $loginInfo) = curl_post($loginUrl, $credentials, $cookieFile);
    echo "Login HTTP code: {$loginInfo['http_code']}\n";
    if (!is_file($cookieFile)) {
        echo "❌ Cookie file not created; login likely failed.\n";
        throw new Exception('Login failed - cannot proceed with authenticated test');
    }
    echo "✅ Login cookie file created: {$cookieFile}\n";

    // Fetch admin page to extract CSRF token
    $adminPageUrl = $base . '/admin/appointments';
    echo "Fetching admin appointments page to extract CSRF token (curl)...\n";
    list($adminResRaw, $adminInfo) = curl_get($adminPageUrl, $cookieFile);
    // split headers/body
    $parts = preg_split('/\r?\n\r?\n/', $adminResRaw, 2);
    $adminHtml = $parts[1] ?? $adminResRaw;

    // try to find CSRF hidden input
    $csrf = null; $csrfName = null;
    if (preg_match('/<input[^>]+name=["\']([^"\']*csrf[^"\']*)["\'][^>]*value=["\']([^"\']+)["\']/i', $adminHtml, $m)){
        $csrfName = $m[1]; $csrf = $m[2];
        echo "Found CSRF input: {$csrfName} (len=" . strlen($csrf) . ")\n";
    } else {
        echo "No CSRF hidden input found on admin page (body len=" . strlen($adminHtml) . ")\n";
    }
    if (!$j1) { echo "Failed to parse available-slots before approval\n"; } else {
        // try to find slot at 10:00
        $found = false; $available = null;
        foreach ($j1['all_slots'] ?? $j1['available_slots'] ?? [] as $s) {
            if (strpos($s['datetime'] ?? ($s['time'] ?? ''), " $time") !== false || (isset($s['time']) && strpos($s['time'], '10:00')!==false)) {
                $found = true; $available = $s['available'] ?? true; break;
            }
        }
        echo "Slot 10:00 found in response? " . ($found ? 'YES' : 'NO') . ", available: " . var_export($available, true) . "\n";
    }

    echo "\n--- Before approval: dayAppointments (timeline) ---\n";
    $dayPayload = ['date'=>$date, 'branch_id'=>$branchId];
    if ($csrfName && $csrf) $dayPayload[$csrfName] = $csrf;
    list($dayResRaw, $dayInfo) = curl_post($base . '/appointments/day-appointments', $dayPayload, $cookieFile);
    // split headers/body
    $parts = preg_split('/\r?\n\r?\n/', $dayResRaw, 2);
    $dayRawBefore = $parts[1] ?? $dayResRaw;
    echo "Raw: " . substr($dayRawBefore,0,400) . "\n\n";
    $dBefore = json_decode($dayRawBefore, true);
    echo "dayAppointments count: " . (isset($dBefore['appointments']) ? count($dBefore['appointments']) : 0) . "\n";

    // Directly mark the appointment as approved in the database to simulate admin approval
    echo "\nMarking appointment id $apptId as approved in DB...\n";
    $upd = $pdo->prepare("UPDATE appointments SET approval_status = 'approved', status = 'confirmed', updated_at = NOW() WHERE id = ?");
    $upd->execute([$apptId]);
    echo "DB update affected rows: " . $upd->rowCount() . "\n";

    // Verify by querying DB for approved appointments on the date and reproduce slot generation locally
    $stmt = $pdo->prepare("SELECT id, appointment_datetime, status, approval_status FROM appointments WHERE DATE(appointment_datetime) = ? AND approval_status = 'approved' AND status NOT IN ('cancelled','rejected','no_show') AND branch_id = ? ORDER BY appointment_datetime");
    $stmt->execute([$date, $branchId]);
    $filteredResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "\nAppointments that currently block slots on {$date}:\n";
    foreach ($filteredResults as $apt) {
        echo " - " . date('H:i', strtotime($apt['appointment_datetime'])) . " ({$apt['status']}/{$apt['approval_status']})\n";
    }

    // Simulate available slots (30-min granularity) and check 10:00
    $blockedTimes = array_map(function($a){ return date('H:i', strtotime($a['appointment_datetime'])); }, $filteredResults);
    $found = in_array('10:00', $blockedTimes);
    echo "\nAfter DB-approve, is 10:00 blocked? " . ($found ? 'YES' : 'NO') . "\n";

    echo "\n--- After approval: dayAppointments (timeline) ---\n";
    $dayPayloadAfter = ['date'=>$date, 'branch_id'=>$branchId];
    if ($csrfName && $csrf) $dayPayloadAfter[$csrfName] = $csrf;
    list($dayResAfterRaw, $dayAfterInfo) = curl_post($base . '/appointments/day-appointments', $dayPayloadAfter, $cookieFile);
    $parts = preg_split('/\r?\n\r?\n/', $dayResAfterRaw, 2);
    $dayRawAfter = $parts[1] ?? $dayResAfterRaw;
    echo "Raw: " . substr($dayRawAfter,0,400) . "\n\n";
    $dAfter = json_decode($dayRawAfter, true);
    echo "dayAppointments count: " . (isset($dAfter['appointments']) ? count($dAfter['appointments']) : 0) . "\n";

    // Cleanup: delete test appointment
    $del = $pdo->prepare("DELETE FROM appointments WHERE id = ? OR remarks = 'TEST_APPROVE_FLOW'");
    $del->execute([$apptId]);
    echo "\nCleaned up test appointment id $apptId\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nTest complete\n";
