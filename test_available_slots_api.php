<?php
/**
 * Test Admin Available Slots API
 * Direct test of the /appointments/available-slots endpoint with admin parameters
 */

// Simulate a direct POST request to the available-slots endpoint
$baseUrl = 'http://localhost:8080'; // CodeIgniter development server
$endpoint = '/appointments/available-slots'; // General endpoint

// Test data - using valid branch, service, and date
$testData = [
    'branch_id' => '1', // Assuming Nabua branch
    'date' => '2025-09-20', // Tomorrow (Saturday)
    'service_id' => '1', // Assuming first service
    'granularity' => '3'
];

echo "=== Admin Available Slots API Test ===\n";
echo "Testing endpoint: {$baseUrl}{$endpoint}\n";
echo "Test payload: " . json_encode($testData, JSON_PRETTY_PRINT) . "\n\n";

// Method 1: Test via cURL (simulating browser request)
function testWithCurl($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'X-Requested-With: XMLHttpRequest'
    ]);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'response' => $response,
        'http_code' => $httpCode,
        'error' => $error
    ];
}

// Test the API
$result = testWithCurl($baseUrl . $endpoint, $testData);

echo "=== cURL Test Results ===\n";
echo "HTTP Code: " . $result['http_code'] . "\n";
echo "cURL Error: " . ($result['error'] ?: 'None') . "\n";
echo "Response: " . $result['response'] . "\n\n";

// Try to parse JSON response
if ($result['response']) {
    $json = json_decode($result['response'], true);
    if ($json) {
        echo "=== Parsed JSON Response ===\n";
        echo "Success: " . ($json['success'] ?? 'not set') . "\n";
        echo "Slots count: " . (isset($json['slots']) ? count($json['slots']) : 'No slots key') . "\n";
        echo "Available slots count: " . (isset($json['available_slots']) ? count($json['available_slots']) : 'No available_slots key') . "\n";
        
        if (isset($json['slots']) && is_array($json['slots'])) {
            echo "First few slots: " . json_encode(array_slice($json['slots'], 0, 5)) . "\n";
        }
        
        if (isset($json['metadata'])) {
            echo "Metadata: " . json_encode($json['metadata']) . "\n";
        }
        
        if (isset($json['error'])) {
            echo "Error: " . $json['error'] . "\n";
        }
    } else {
        echo "Failed to parse JSON response\n";
    }
}

// Method 2: Test by including CodeIgniter directly (if this script is in root)
echo "\n=== Direct CodeIgniter Test ===\n";
try {
    // Try to load CodeIgniter if available
    if (file_exists(__DIR__ . '/app/Config/App.php')) {
        echo "CodeIgniter config found, attempting direct test...\n";
        
        // You could add direct model testing here if needed
        echo "Direct model testing would require full CI4 bootstrap\n";
    } else {
        echo "CodeIgniter config not found, skipping direct test\n";
    }
} catch (Exception $e) {
    echo "Direct test error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "If the cURL test shows 'Loading...' or no slots, the issue is in the API endpoint.\n";
echo "If it shows slots, the issue is in the JavaScript implementation.\n";