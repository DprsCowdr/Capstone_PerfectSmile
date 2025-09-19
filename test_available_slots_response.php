<?php
// Test the available-slots endpoint with authentication to see the full response
echo "=== Testing Available Slots with Authentication ===\n";
echo "Date: 2025-09-20\n\n";

// Simulate authenticated request
$cookieJar = sys_get_temp_dir() . '/psm_test_cookies.txt';

function authenticatedPost($url, $data, $cookieJar) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-Requested-With: XMLHttpRequest",
        "Content-Type: application/x-www-form-urlencoded"
    ]);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'body' => $response,
        'error' => $error
    ];
}

// Test available-slots endpoint
$payload = [
    'branch_id' => '1',
    'date' => '2025-09-20',
    'service_id' => '2',
    'granularity' => '30'
];

$result = authenticatedPost('http://localhost:8080/appointments/available-slots', $payload, $cookieJar);

echo "HTTP Status: {$result['code']}\n";
if ($result['error']) {
    echo "cURL Error: {$result['error']}\n";
}

echo "Response Body:\n";
$responseData = json_decode($result['body'], true);
if ($responseData) {
    echo "Success: " . ($responseData['success'] ? 'true' : 'false') . "\n";
    
    if (isset($responseData['all_slots'])) {
        echo "All slots count: " . count($responseData['all_slots']) . "\n";
        echo "Available slots count: " . count($responseData['available_slots'] ?? []) . "\n";
        echo "Unavailable slots count: " . count($responseData['unavailable_slots'] ?? []) . "\n";
        
        echo "\nFirst few all_slots:\n";
        foreach (array_slice($responseData['all_slots'], 0, 10) as $slot) {
            $time = $slot['time'] ?? 'unknown';
            $available = $slot['available'] ?? 'unknown';
            echo "  $time - Available: " . var_export($available, true) . "\n";
        }
        
        if (isset($responseData['unavailable_slots']) && count($responseData['unavailable_slots']) > 0) {
            echo "\nUnavailable slots:\n";
            foreach ($responseData['unavailable_slots'] as $slot) {
                $time = $slot['time'] ?? 'unknown';
                $blockingInfo = isset($slot['blocking_info']) ? 
                    " (blocked by {$slot['blocking_info']['type']} from {$slot['blocking_info']['start']} to {$slot['blocking_info']['end']})" : 
                    "";
                echo "  $time$blockingInfo\n";
            }
        }
    } else {
        echo "No all_slots in response\n";
        echo "Response keys: " . implode(', ', array_keys($responseData)) . "\n";
    }
} else {
    echo "Failed to decode JSON response:\n";
    echo substr($result['body'], 0, 1000) . "\n";
}