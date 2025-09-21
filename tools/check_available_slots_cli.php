<?php
// tools/check_available_slots_cli.php
// Usage: php check_available_slots_cli.php <base_url> <branch_id> <date> <service_id> <granularity> <session_cookie>
// Example: php check_available_slots_cli.php http://localhost 2 2025-09-20 1 3 "CI_SESSION=abcd1234"

if ($argc < 7) {
    echo "Usage: php check_available_slots_cli.php <base_url> <branch_id> <date> <service_id> <granularity> <session_cookie>\n";
    exit(1);
}
$base = rtrim($argv[1], '/');
$branch = $argv[2];
$date = $argv[3];
$service = $argv[4];
$gran = $argv[5];
$cookie = $argv[6];

$url = $base . '/appointments/available-slots';
$payload = json_encode([
    'branch_id' => (int)$branch,
    'date' => $date,
    'service_id' => (int)$service,
    'granularity' => (int)$gran
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_COOKIE, $cookie);

$response = curl_exec($ch);
if ($response === false) {
    echo "cURL error: " . curl_error($ch) . "\n";
    exit(2);
}
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$header = substr($response, 0, $header_size);
$body = substr($response, $header_size);

echo "HTTP Status: $status\n\n";
echo "Response Headers:\n" . $header . "\n";

// pretty-print JSON body if possible
$json = json_decode($body, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "Response JSON:\n" . json_encode($json, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "Response Body:\n" . $body . "\n";
}

curl_close($ch);

?>