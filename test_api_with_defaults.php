<?php
// Test script to verify API with proper branch/service IDs
echo "Testing API with proper fallback values...\n";

$url = 'http://localhost:8080/appointments/available-slots';
$data = [
    'branch_id' => '1',
    'date' => '2025-09-19', 
    'service_id' => '1',
    'granularity' => 30
];

$postdata = http_build_query($data);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/x-www-form-urlencoded',
            'X-Requested-With: XMLHttpRequest'
        ],
        'content' => $postdata
    ]
]);

echo "Making request to: $url\n";
echo "Payload: " . json_encode($data, JSON_PRETTY_PRINT) . "\n\n";

$result = file_get_contents($url, false, $context);
$headers = $http_response_header ?? [];

echo "Response Headers:\n";
foreach ($headers as $header) {
    echo "  $header\n";
}

echo "\nResponse Body:\n";
echo $result . "\n";

if ($result) {
    $decoded = json_decode($result, true);
    if ($decoded) {
        echo "\nDecoded Response:\n";
        echo json_encode($decoded, JSON_PRETTY_PRINT) . "\n";
        
        if (isset($decoded['slots'])) {
            echo "\nSlots Summary:\n";
            echo "Total slots: " . count($decoded['slots']) . "\n";
            echo "Available slots: " . count($decoded['available_slots'] ?? []) . "\n";
            echo "Unavailable slots: " . count($decoded['unavailable_slots'] ?? []) . "\n";
        }
    }
}
?>