<?php
// Simple test to check if our endpoints return data correctly
require_once 'vendor/autoload.php';

// Create a simple HTTP client to test endpoints
function testEndpoint($url) {
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Content-Type: application/json',
            'timeout' => 10
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    return json_decode($response, true);
}

// Test patient ID - let's try ID 1
$patientId = 1;
$baseUrl = 'http://localhost:8080';

echo "Testing Patient Endpoints for Patient ID: $patientId\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// Test Patient Info
echo "1. Testing Patient Info:\n";
$result = testEndpoint("$baseUrl/admin/patient-info/$patientId");
echo "   Success: " . ($result['success'] ? 'YES' : 'NO') . "\n";
if (!$result['success']) {
    echo "   Error: " . ($result['message'] ?? 'Unknown error') . "\n";
}
echo "\n";

// Test Appointments
echo "2. Testing Appointments:\n";
$result = testEndpoint("$baseUrl/admin/patient-appointments/$patientId");
echo "   Success: " . ($result['success'] ? 'YES' : 'NO') . "\n";
if (!$result['success']) {
    echo "   Error: " . ($result['message'] ?? 'Unknown error') . "\n";
} else {
    echo "   Present Appointments: " . count($result['present_appointments'] ?? []) . "\n";
    echo "   Past Appointments: " . count($result['past_appointments'] ?? []) . "\n";
}
echo "\n";

// Test Medical Records
echo "3. Testing Medical Records:\n";
$result = testEndpoint("$baseUrl/admin/patient-medical-records/$patientId");
echo "   Success: " . ($result['success'] ? 'YES' : 'NO') . "\n";
if (!$result['success']) {
    echo "   Error: " . ($result['message'] ?? 'Unknown error') . "\n";
} else {
    echo "   Medical Records: " . count($result['medical_records'] ?? []) . "\n";
    echo "   Diagnoses: " . count($result['diagnoses'] ?? []) . "\n";
}
echo "\n";

echo "Test completed!\n";
?>
