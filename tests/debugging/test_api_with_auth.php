<?php
// Test the dental chart API with authentication
session_start();

$baseUrl = 'http://localhost:8080';
$patientId = 10; // Marc Aron Gamban

// First, let's try to login
$loginData = [
    'email' => 'admin@perfectsmile.com',
    'password' => 'password123'
];

$ch = curl_init();

// Step 1: Login
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/auth/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($loginData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, ''); // Enable cookie handling
curl_setopt($ch, CURLOPT_COOKIEJAR, ''); // Store cookies

$loginResponse = curl_exec($ch);
$loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "Login attempt:\n";
echo "HTTP Code: $loginHttpCode\n";
if ($loginHttpCode == 302 || $loginHttpCode == 200) {
    echo "Login appears successful\n\n";
} else {
    echo "Login may have failed\n\n";
}

// Step 2: Try to access the dental chart API
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/patient-dental-chart/' . $patientId);
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest'
]);

$apiResponse = curl_exec($ch);
$apiHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "API Request to: " . $baseUrl . '/admin/patient-dental-chart/' . $patientId . "\n";
echo "HTTP Code: $apiHttpCode\n";

if ($apiHttpCode == 200) {
    echo "API Response:\n";
    $data = json_decode($apiResponse, true);
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
        echo "Response is not valid JSON:\n";
        echo substr($apiResponse, 0, 500) . "...\n";
    }
} else {
    echo "API request failed\n";
    echo "Response: " . substr($apiResponse, 0, 500) . "...\n";
}

curl_close($ch);
?>
