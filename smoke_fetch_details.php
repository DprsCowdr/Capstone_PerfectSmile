<?php
// Quick smoke test: request the patient appointment details endpoint via HTTP (local) and display JSON
$ch = curl_init();
$aptId = 244;
$base = 'http://localhost:8080/';
$url = $base . 'patient/appointments/details/' . $aptId;

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
// If running locally with cookies or session, this simple test may not be authenticated; we can still test DB query output via test_fixed_endpoint.php
$response = curl_exec($ch);
if ($response === false) {
    echo "cURL error: " . curl_error($ch) . "\n";
} else {
    echo "Raw response:\n" . $response . "\n";
}
curl_close($ch);
?>