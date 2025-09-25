<?php
// Test the POST endpoint directly
$date = date('Y-m-d');
$url = 'http://localhost:8080/appointments/available-slots';

$data = [
    'date' => $date,
    'duration' => 45,
    'branch_id' => 1
];

$postData = http_build_query($data);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'X-Requested-With: XMLHttpRequest'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if ($data && isset($data['success']) && $data['success']) {
        echo "✓ Available slots endpoint working!\n";
        echo "Total slots: " . count($data['all_slots'] ?? []) . "\n";
        echo "Available slots: " . count($data['available_slots'] ?? []) . "\n";
        echo "Suggestions: " . count($data['suggestions'] ?? []) . "\n";
    } else {
        echo "✗ Response indicates error\n";
    }
} else {
    echo "✗ HTTP error\n";
}
?>