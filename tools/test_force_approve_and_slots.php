<?php
// Simple smoke test: call debug force-approve, then call available-slots
// Usage: php test_force_approve_and_slots.php <appointment_id> <branch_id> <service_id> <date> [base_url]

$options = getopt('', []);
$args = $argv;
array_shift($args);
if (count($args) < 4) {
    echo "Usage: php test_force_approve_and_slots.php <appointment_id> <branch_id> <service_id> <date (YYYY-MM-DD)>\n";
    exit(1);
}
list($aptId, $branchId, $serviceId, $date) = $args;
$base = 'http://localhost/'; // default base URL
// Optional 5th arg is base URL
if (!empty($args[4])) {
    $base = rtrim($args[4], '/') . '/';
}
$cookieJar = sys_get_temp_dir() . '/psm_test_cookies.txt';

function http_get($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $GLOBALS['cookieJar']);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $GLOBALS['cookieJar']);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => $res, 'error' => $err];
}

function http_post($url, $data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-Requested-With: XMLHttpRequest"]);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $GLOBALS['cookieJar']);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $GLOBALS['cookieJar']);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => $res, 'error' => $err];
}

// 1) Force approve
$forceUrl = rtrim($base, '/') . '/debug/approve-test/' . $aptId . '?force=1';
echo "Calling: $forceUrl\n";
$r = http_get($forceUrl);
echo "HTTP {$r['code']}\n";
if ($r['error']) echo "Error: {$r['error']}\n";
echo "Body:\n" . substr($r['body'],0,2000) . "\n\n";

// 2) Call available-slots for the date
$slotsUrl = rtrim($base, '/') . '/appointments/available-slots';
$payload = [
    'branch_id' => $branchId,
    'service_id' => $serviceId,
    'date' => $date,
    'granularity' => 3
];
echo "Posting to: $slotsUrl with "; print_r($payload);
$s = http_post($slotsUrl, $payload);
echo "HTTP {$s['code']}\n";
if ($s['error']) echo "Error: {$s['error']}\n";
echo "Body:\n" . substr($s['body'],0,8000) . "\n";

return 0;
