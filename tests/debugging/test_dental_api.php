<?php

// Test the dental chart API endpoint directly
$patientId = 10; // Marc Aron Gamban's ID

$url = "http://localhost:8080/admin/patient-dental-chart/$patientId";

echo "Testing dental chart API for patient ID: $patientId\n";
echo "URL: $url\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_error($ch)) {
    echo "cURL Error: " . curl_error($ch) . "\n";
} else {
    echo "HTTP Code: $httpCode\n";
    echo "Response:\n";
    
    $data = json_decode($response, true);
    if ($data) {
        echo "Success: " . ($data['success'] ? 'true' : 'false') . "\n";
        if (isset($data['chart'])) {
            echo "Chart entries: " . count($data['chart']) . "\n";
            foreach ($data['chart'] as $entry) {
                echo "- Tooth {$entry['tooth_number']}: {$entry['condition']}\n";
            }
        }
        if (isset($data['teeth_data'])) {
            echo "Teeth data keys: " . implode(', ', array_keys($data['teeth_data'])) . "\n";
        }
    } else {
        echo "Raw response: $response\n";
    }
}

curl_close($ch);
