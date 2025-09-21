<?php
// tools/admin_approve_test.php
// CLI helper to exercise admin appointment endpoints using PHP (no external dependencies)
// Usage:
// php tools/admin_approve_test.php --baseUrl=http://localhost/ --cookie="PHPSESSID=..." --ids=245,252,263 --dentist=1 --branch=1 --date=2025-09-20

$options = getopt('', ['baseUrl:', 'cookie:', 'ids:', 'dentist::', 'branch::', 'date::']);

function usage() {
    echo "Usage:\n";
    echo "php tools/admin_approve_test.php --baseUrl=http://localhost/ --cookie='PHPSESSID=...' --ids=245,252,263 [--dentist=1] [--branch=1] [--date=YYYY-MM-DD]\n";
    exit(1);
}

if (!isset($options['baseUrl']) || !isset($options['cookie']) || !isset($options['ids'])) {
    usage();
}

$baseUrl = rtrim($options['baseUrl'], '/') . '/';
$cookie = $options['cookie'];
$ids = array_filter(array_map('trim', explode(',', $options['ids'])));
$dentist = $options['dentist'] ?? null;
$branch = $options['branch'] ?? null;
$date = $options['date'] ?? date('Y-m-d');

function post($url, $params, $baseUrl, $cookie) {
    $full = $baseUrl . ltrim($url, '/');

    $ch = curl_init($full);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-Requested-With: XMLHttpRequest',
        'Content-Type: application/x-www-form-urlencoded',
        'Cookie: ' . $cookie
    ]);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err) return ['success' => false, 'error' => $err];
    $decoded = json_decode($resp, true);
    return ['success' => true, 'http_code' => $code, 'raw' => $resp, 'json' => $decoded];
}

// Step 1: fetch day-appointments (before)
$dayPayload = ['date' => $date];
if ($branch) $dayPayload['branch_id'] = $branch;
echo "\n== Fetching appointments/day-appointments (before) ==\n";
$dayBefore = post('appointments/day-appointments', $dayPayload, $baseUrl, $cookie);
var_export($dayBefore);

// Step 1b: fetch available-slots (before)
$availPayload = ['date' => $date, 'granularity' => 3];
if ($branch) $availPayload['branch_id'] = $branch;
echo "\n\n== Fetching appointments/available-slots (before) ==\n";
$availBefore = post('appointments/available-slots', $availPayload, $baseUrl, $cookie);
var_export($availBefore);

// Step 2: Approve each
foreach ($ids as $id) {
    echo "\n\n== Approving appointment ID: $id ==\n";
    $form = [];
    if ($dentist !== null) $form['dentist_id'] = $dentist;
    $approveResp = post("admin/appointments/approve/$id", $form, $baseUrl, $cookie);
    var_export($approveResp);
}

// Step 3: Re-fetch day-appointments and available slots (after)
echo "\n\n== Fetching appointments/day-appointments (after) ==\n";
$dayAfter = post('appointments/day-appointments', $dayPayload, $baseUrl, $cookie);
var_export($dayAfter);

echo "\n\n== Fetching appointments/available-slots (after) ==\n";
$availAfter = post('appointments/available-slots', $availPayload, $baseUrl, $cookie);
var_export($availAfter);

echo "\n\nDone. Inspect the output above for changes to 'approval_status', 'available_slots', 'occupied_map' or 'unavailable_slots'.\n";

?>
