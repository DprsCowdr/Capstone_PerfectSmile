<?php
/**
 * Available Slots API Test
 * Tests the /appointments/available-slots endpoint for slot density verification
 */

echo "=== Available Slots API Test ===\n\n";

// Test parameters
$baseUrl = 'http://localhost:8080';
$endpoint = '/appointments/available-slots';
$testDate = '2025-09-26'; // Same date as our test appointment
$branchId = 1;
$serviceId = 2; // Teeth Whitening (120 min)
$granularity = 15; // 15-minute intervals

echo "🧪 Testing available slots API...\n";
echo "   Service ID: {$serviceId} (Teeth Whitening - 120 minutes)\n";
echo "   Date: {$testDate}\n";
echo "   Branch: {$branchId}\n";
echo "   Granularity: {$granularity} minutes\n\n";

// Make the API request
$postData = json_encode([
    'date' => $testDate,
    'branch_id' => $branchId,
    'service_id' => $serviceId,
    'granularity' => $granularity
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'timeout' => 10,
        'header' => [
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: BookingTest/1.0',
            'Content-Length: ' . strlen($postData)
        ],
        'content' => $postData
    ]
]);

$apiUrl = $baseUrl . $endpoint;
echo "API URL: {$apiUrl}\n";
echo "POST Data: {$postData}\n\n";

echo "📡 Making API request...\n";
$response = file_get_contents($apiUrl, false, $context);

if ($response === false) {
    echo "❌ Failed to make API request\n";
    echo "   This could indicate:\n";
    echo "   - Development server not running\n";
    echo "   - Authentication required\n";
    echo "   - Route not accessible\n\n";
    
    // Try a simpler test
    echo "🔍 Testing basic connectivity...\n";
    $basicTest = file_get_contents($baseUrl);
    if ($basicTest === false) {
        echo "❌ Cannot connect to {$baseUrl}\n";
    } else {
        echo "✅ Base URL accessible\n";
        echo "   Response length: " . strlen($basicTest) . " characters\n";
    }
    
    exit(1);
}

echo "✅ API response received\n";
echo "   Response length: " . strlen($response) . " characters\n\n";

// Parse the JSON response
$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ Invalid JSON response\n";
    echo "   JSON Error: " . json_last_error_msg() . "\n";
    echo "   Raw response (first 500 chars):\n";
    echo substr($response, 0, 500) . "\n";
    exit(1);
}

echo "✅ JSON parsed successfully\n\n";

// Analyze the response
echo "📊 API Response Analysis:\n";

if (isset($data['success']) && $data['success'] === false) {
    echo "❌ API returned error:\n";
    echo "   Message: " . ($data['message'] ?? 'Unknown error') . "\n";
    if (isset($data['error'])) {
        echo "   Error: " . $data['error'] . "\n";
    }
    exit(1);
}

// Check for available slots
if (isset($data['slots'])) {
    $slots = $data['slots'];
    $slotCount = count($slots);
    
    echo "✅ Available slots found: {$slotCount}\n";
    
    if ($slotCount > 0) {
        echo "\n🕐 Sample slots (first 10):\n";
        $sampleSlots = array_slice($slots, 0, 10);
        foreach ($sampleSlots as $i => $slot) {
            if (is_array($slot)) {
                $time = $slot['time'] ?? $slot['start_time'] ?? 'Unknown';
                $available = isset($slot['available']) ? ($slot['available'] ? 'Available' : 'Unavailable') : 'Available';
                echo "   " . ($i + 1) . ". {$time} - {$available}\n";
            } else {
                echo "   " . ($i + 1) . ". {$slot}\n";
            }
        }
        
        if ($slotCount > 10) {
            echo "   ... and " . ($slotCount - 10) . " more slots\n";
        }
        
        // Analyze slot density
        echo "\n📈 Slot Density Analysis:\n";
        
        // Expected slots calculation
        $serviceDuration = 120; // minutes
        $businessHours = 12 * 60; // 08:00-20:00 = 12 hours = 720 minutes
        $expectedSlots = floor(($businessHours - $serviceDuration) / $granularity) + 1;
        
        echo "   Expected maximum slots: ~{$expectedSlots}\n";
        echo "   Actual slots returned: {$slotCount}\n";
        
        $densityRatio = ($slotCount / $expectedSlots) * 100;
        echo "   Slot density: " . round($densityRatio, 1) . "%\n";
        
        if ($densityRatio > 80) {
            echo "   ✅ Excellent slot density - system showing many candidates\n";
        } elseif ($densityRatio > 50) {
            echo "   ✅ Good slot density - reasonable number of candidates\n";
        } elseif ($densityRatio > 20) {
            echo "   ⚠️  Moderate slot density - some restrictions may apply\n";
        } else {
            echo "   ❌ Low slot density - may indicate conflicts or restrictions\n";
        }
        
    } else {
        echo "⚠️  No available slots found for this date/service combination\n";
        echo "   This could indicate:\n";
        echo "   - All time slots are booked\n";
        echo "   - Service duration too long for available time\n";
        echo "   - No dentists available for this service\n";
    }
    
} else {
    echo "⚠️  No 'slots' field in API response\n";
    echo "   Available fields: " . implode(', ', array_keys($data)) . "\n";
}

// Check for additional response data
if (isset($data['dentists'])) {
    $dentistCount = is_array($data['dentists']) ? count($data['dentists']) : 0;
    echo "\n👥 Dentists involved: {$dentistCount}\n";
}

if (isset($data['conflicts'])) {
    $conflictCount = is_array($data['conflicts']) ? count($data['conflicts']) : 0;
    echo "⚠️  Conflicts detected: {$conflictCount}\n";
}

echo "\n=== Slot Density Test Summary ===\n";
echo "✅ API Connectivity: Working\n";
echo "✅ Long-duration Service: Tested (120 minutes)\n";
echo "✅ Slot Generation: " . (isset($slots) && count($slots) > 0 ? "Working" : "Needs investigation") . "\n";
echo "✅ Dense Candidates: " . (isset($densityRatio) && $densityRatio > 50 ? "Confirmed" : "Needs optimization") . "\n";

echo "\n📝 Next Steps:\n";
echo "1. Test UI appointment creation with these slots\n";
echo "2. Verify different service durations show appropriate slot counts\n";
echo "3. Test multi-dentist scenarios\n";
echo "4. Test guest booking workflow\n";