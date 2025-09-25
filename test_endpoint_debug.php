<?php
// Test the endpoint directly with curl
$url = 'http://localhost:8080/appointments/available-slots?__debug_noauth=1&date=2025-09-23&duration=45&branch_id=2';

echo "Testing: $url\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-Requested-With: XMLHttpRequest'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response:\n$response\n\n";

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if ($data) {
        echo "Parsed data:\n";
        echo "Success: " . ($data['success'] ? 'true' : 'false') . "\n";
        echo "Total slots: " . count($data['all_slots'] ?? []) . "\n";
        echo "Available: " . count($data['available_slots'] ?? []) . "\n";
        echo "Unavailable: " . count($data['unavailable_slots'] ?? []) . "\n";
        echo "Occupied map: " . count($data['occupied_map'] ?? []) . "\n";
        echo "Suggestions: " . count($data['suggestions'] ?? []) . "\n";
        
        if (!empty($data['occupied_map'])) {
            echo "\nOccupied intervals:\n";
            foreach ($data['occupied_map'] as $occ) {
                echo "  {$occ['start']} - {$occ['end']} (apt: {$occ['appointment_id']})\n";
            }
        }
        
        if (!empty($data['metadata'])) {
            echo "\nMetadata:\n";
            foreach ($data['metadata'] as $key => $value) {
                echo "  $key: $value\n";
            }
        }
    }
} else {
    echo "HTTP Error\n";
}
?>