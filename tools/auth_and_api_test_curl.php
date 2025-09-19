<?php
// Curl-based Authentication and Protected API Test
$baseUrl = 'http://localhost:8080';
$loginUrl = $baseUrl . '/auth/login';
$credentials = [
    'email' => 'admin@perfectsmile.com',
    'password' => 'admin123'
];

$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'psm_cookies.txt';
@unlink($cookieFile);

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

function curl_get($url, $cookieFile = null){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }
    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return [$res, $info];
}

echo "=== CURL Authentication Test ===\n";

// Login
echo "Logging in...\n";
[ $loginRes, $loginInfo ] = curl_post($loginUrl, $credentials, $cookieFile);
echo "Login HTTP code: {$loginInfo['http_code']}\n";

// Show cookie file content for debugging
if (is_file($cookieFile)){
    echo "Cookies saved to: {$cookieFile}\n";
    echo "Cookie file contents:\n" . file_get_contents($cookieFile) . "\n";
} else {
    echo "No cookie file created.\n";
}

// Using only the real login flow. Do NOT write session files directly in this script.

// Fetch admin page to extract CSRF token
$adminUrl = $baseUrl . '/admin/appointments';
echo "Fetching admin page to extract CSRF token...\n";
[ $adminRes, $adminInfo ] = curl_get($adminUrl, $cookieFile);
// split headers/body
$parts = preg_split('/\r?\n\r?\n/', $adminRes, 2);
$adminBody = $parts[1] ?? $adminRes;

$csrfName = null; $csrfVal = null;
if (preg_match('/<input[^>]+name=["\']([^"\']*csrf[^"\']*)["\'][^>]*value=["\']([^"\']+)["\']/i', $adminBody, $m)){
    $csrfName = $m[1];
    $csrfVal = $m[2];
    echo "Found CSRF hidden input: {$csrfName} => " . substr($csrfVal,0,40) . "...\n";
} else {
    echo "CSRF hidden input not found in admin page (body length: " . strlen($adminBody) . ")\n";
}

// POST to available-slots
$slotsUrl = $baseUrl . '/appointments/available-slots';
$slotsPayload = [
    'date' => date('Y-m-d', strtotime('+7 days')),
    'branch_id' => 1,
    'service_id' => 2,
    'granularity' => 15
];
if ($csrfName && $csrfVal) $slotsPayload[$csrfName] = $csrfVal;

echo "Posting to available-slots...\n";
[ $slotsRes, $slotsInfo ] = curl_post($slotsUrl, $slotsPayload, $cookieFile);
echo "available-slots HTTP code: {$slotsInfo['http_code']}\n";
// try to find body
$parts = preg_split('/\r?\n\r?\n/', $slotsRes, 2);
$slotsBody = $parts[1] ?? $slotsRes;
echo "available-slots response body (first 1000 chars):\n" . substr($slotsBody, 0, 1000) . "\n";

// Note: debug/session endpoint removed. Use server logs or application tests for session inspection.

// Try admin create appointment
$createUrl = $baseUrl . '/admin/appointments/create';
$createPayload = [
    'user_id' => 3,
    'branch_id' => 1,
    'dentist_id' => 30,
    'appointment_datetime' => date('Y-m-d H:i:s', strtotime('+8 days 09:00:00')),
    'procedure_duration' => 60,
    'approval_status' => 'approved',
    'status' => 'confirmed'
];
if ($csrfName && $csrfVal) $createPayload[$csrfName] = $csrfVal;

echo "Posting to admin create...\n";
[ $createRes, $createInfo ] = curl_post($createUrl, $createPayload, $cookieFile);
echo "admin create HTTP code: {$createInfo['http_code']}\n";
$parts = preg_split('/\r?\n\r?\n/', $createRes, 2);
$createBody = $parts[1] ?? $createRes;
echo "admin create response body (first 1000 chars):\n" . substr($createBody, 0, 1000) . "\n";

echo "CURL Authentication test completed.\n";

?>