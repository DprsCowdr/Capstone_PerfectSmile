<?php
/**
 * Test Admin Authentication and Available Slots
 * This script tests if we can access the admin area and call the available slots API
 */

// Start session to simulate being logged in
session_start();

echo "=== Admin Available Slots Test ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

// Test 1: Check if we can include the framework
echo "1. Testing CodeIgniter inclusion...\n";
try {
    require_once 'app/Config/Paths.php';
    $pathsClass = new Config\Paths();
    echo "✅ Paths loaded successfully\n";
} catch (Exception $e) {
    echo "❌ Error loading paths: " . $e->getMessage() . "\n";
}

// Test 2: Simulate admin session
echo "\n2. Setting up admin session...\n";
$_SESSION['user_id'] = 1;
$_SESSION['user_type'] = 'admin';
$_SESSION['username'] = 'admin';
$_SESSION['logged_in'] = true;

echo "✅ Session variables set:\n";
foreach ($_SESSION as $key => $value) {
    echo "   $key: $value\n";
}

// Test 3: Test HTTP request to available slots
echo "\n3. Testing available slots API call...\n";

$postData = [
    'branch_id' => '1',
    'date' => '2025-09-20',
    'service_id' => '1',
    'granularity' => '30'
];

$options = [
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/x-www-form-urlencoded',
            'X-Requested-With: XMLHttpRequest',
            'Cookie: ' . session_name() . '=' . session_id()
        ],
        'content' => http_build_query($postData)
    ]
];

$context = stream_context_create($options);
$url = 'http://localhost:8000/appointments/available-slots';

echo "Making request to: $url\n";
echo "With session cookie: " . session_name() . '=' . session_id() . "\n";

$response = @file_get_contents($url, false, $context);

if ($response === false) {
    echo "❌ Request failed\n";
    echo "Error details:\n";
    print_r($http_response_header ?? 'No response headers');
} else {
    echo "✅ Request successful\n";
    echo "Response length: " . strlen($response) . " bytes\n";
    echo "Response preview: " . substr($response, 0, 200) . "...\n";
    
    // Try to parse as JSON
    $data = json_decode($response, true);
    if ($data) {
        echo "✅ Valid JSON response\n";
        echo "Success: " . ($data['success'] ? 'true' : 'false') . "\n";
        if (isset($data['slots'])) {
            echo "Slots count: " . count($data['slots']) . "\n";
        }
        if (isset($data['metadata'])) {
            echo "Metadata: " . json_encode($data['metadata']) . "\n";
        }
    } else {
        echo "❌ Invalid JSON response\n";
        echo "Raw response: $response\n";
    }
}

// Test 4: Check if the issue is with the button loading
echo "\n4. JavaScript Loading Issue Diagnosis...\n";
echo "The 'Loading...' message appears when:\n";
echo "- availableMenuContent.textContent = 'Loading...'; (line 787 in calendar-admin.js)\n";
echo "- API call is made but fails or takes too long\n";
echo "- Error handling doesn't clear the loading state\n\n";

echo "Solutions:\n";
echo "1. Ensure proper admin authentication\n";
echo "2. Add better error handling in JavaScript\n";
echo "3. Add timeout handling for API calls\n";
echo "4. Check console.log output in browser\n";

echo "\n=== Test Complete ===\n";
?>