<?php

/**
 * Test the updated checkConflicts endpoint
 */

$url = 'http://localhost/appointments/check-conflicts';

// Test data that should have conflicts (using the same date/time as existing appointments)
$testData = [
    'date' => '2025-09-23',
    'time' => '08:00', // This time has existing appointments based on our earlier debug
    'duration' => 45,
    'branch_id' => 2,
    'dentist_id' => 30
];

$postFields = http_build_query($testData);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'Content-Length: ' . strlen($postFields)
]);

echo "Testing conflict detection at /appointments/check-conflicts\n";
echo "POST data: " . json_encode($testData, JSON_PRETTY_PRINT) . "\n\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";

// Separate headers and body
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$body = substr($response, $headerSize);

echo "Response body:\n";
echo $body . "\n\n";

// Try to parse JSON
$data = json_decode($body, true);
if ($data) {
    echo "Parsed data:\n";
    echo "Success: " . ($data['success'] ? 'true' : 'false') . "\n";
    echo "Conflict: " . ($data['conflict'] ?? 'not set') . "\n";
    echo "Has conflicts: " . ($data['hasConflicts'] ?? 'not set') . "\n";
    
    if (!empty($data['suggestions'])) {
        echo "Suggestions (" . count($data['suggestions']) . "):\n";
        foreach (array_slice($data['suggestions'], 0, 5) as $i => $suggestion) {
            echo "  " . ($i + 1) . ". " . $suggestion['time'] . " (ends " . $suggestion['ends_at'] . ")\n";
        }
        if (count($data['suggestions']) > 5) {
            echo "  ... and " . (count($data['suggestions']) - 5) . " more\n";
        }
    } else {
        echo "No suggestions provided\n";
    }
    
    if (!empty($data['metadata'])) {
        echo "Metadata:\n";
        foreach ($data['metadata'] as $key => $value) {
            echo "  $key: $value\n";
        }
    }
} else {
    echo "Failed to parse JSON response\n";
}