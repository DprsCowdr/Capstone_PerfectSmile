<?php
/**
 * Test available slots API directly using CodeIgniter
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Change to public directory and bootstrap CodeIgniter
chdir(__DIR__ . '/public');

// Set up environment for API call
$_POST = [
    'date' => date('Y-m-d', strtotime('+1 day')), // Tomorrow
    'branch_id' => 1, // Perfect Smile - Nabua Branch
    'service_id' => 1, // Use first service
    'granularity' => 15
];

$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/appointments/availableSlots';

// Capture output
ob_start();

try {
    // Include the front controller
    require 'index.php';
    $output = ob_get_contents();
} catch (Exception $e) {
    ob_end_clean();
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit;
}

ob_end_clean();

// Parse the JSON response
$response = json_decode($output, true);

if (!$response) {
    echo "Failed to parse JSON response\n";
    echo "Raw output: " . htmlspecialchars($output) . "\n";
    exit;
}

echo "<h2>Available Slots Test Results</h2>\n";
echo "<h3>Test Parameters:</h3>\n";
echo "Date: " . $_POST['date'] . " (" . date('l', strtotime($_POST['date'])) . ")<br>\n";
echo "Branch ID: " . $_POST['branch_id'] . " (Perfect Smile - Nabua Branch)<br>\n";
echo "Service ID: " . $_POST['service_id'] . "<br>\n";
echo "Granularity: " . $_POST['granularity'] . " minutes<br>\n";

echo "<h3>Expected Operating Hours for " . date('l', strtotime($_POST['date'])) . ":</h3>\n";
$weekday = strtolower(date('l', strtotime($_POST['date'])));
if ($weekday === 'saturday') {
    echo "Saturday: 09:00 - 15:00<br>\n";
} else {
    echo "Weekdays: 08:00 - 17:00<br>\n";
}

echo "<h3>API Response:</h3>\n";
echo "Success: " . ($response['success'] ? 'true' : 'false') . "<br>\n";

if (isset($response['message'])) {
    echo "Message: " . htmlspecialchars($response['message']) . "<br>\n";
}

if (isset($response['slots']) && !empty($response['slots'])) {
    $slots = $response['slots'];
    echo "Number of slots: " . count($slots) . "<br>\n";
    
    echo "<h4>First 20 slots:</h4>\n";
    foreach (array_slice($slots, 0, 20) as $i => $slot) {
        $time = is_array($slot) ? $slot['time'] : $slot;
        echo "  " . ($i + 1) . ". {$time}<br>\n";
    }
    
    echo "<h4>Last 20 slots:</h4>\n";
    $lastSlots = array_slice($slots, -20);
    foreach ($lastSlots as $i => $slot) {
        $time = is_array($slot) ? $slot['time'] : $slot;
        echo "  " . (count($slots) - 20 + $i + 1) . ". {$time}<br>\n";
    }
    
    // Analyze operating hours compliance
    echo "<h4>Operating Hours Compliance Analysis:</h4>\n";
    $violations = [];
    $earliestTime = null;
    $latestTime = null;
    
    foreach ($slots as $slot) {
        $time = is_array($slot) ? $slot['time'] : $slot;
        
        // Parse time - try different formats
        $timeObj = null;
        if (preg_match('/(\d{1,2}):(\d{2})\s*(AM|PM)/i', $time, $matches)) {
            $hour = (int)$matches[1];
            $minute = (int)$matches[2];
            $ampm = strtoupper($matches[3]);
            
            if ($ampm === 'PM' && $hour !== 12) {
                $hour += 12;
            } elseif ($ampm === 'AM' && $hour === 12) {
                $hour = 0;
            }
            
            $totalMinutes = $hour * 60 + $minute;
        } elseif (preg_match('/(\d{1,2}):(\d{2})/', $time, $matches)) {
            $hour = (int)$matches[1];
            $minute = (int)$matches[2];
            $totalMinutes = $hour * 60 + $minute;
        } else {
            continue; // Skip unparseable times
        }
        
        // Track earliest and latest times
        if ($earliestTime === null || $totalMinutes < $earliestTime) {
            $earliestTime = $totalMinutes;
        }
        if ($latestTime === null || $totalMinutes > $latestTime) {
            $latestTime = $totalMinutes;
        }
        
        // Check against expected operating hours
        $expectedOpen = ($weekday === 'saturday') ? 540 : 480; // 9:00 AM or 8:00 AM in minutes
        $expectedClose = ($weekday === 'saturday') ? 900 : 1020; // 3:00 PM or 5:00 PM in minutes
        
        if ($totalMinutes < $expectedOpen || $totalMinutes >= $expectedClose) {
            $violations[] = [
                'time' => $time,
                'minutes' => $totalMinutes,
                'reason' => $totalMinutes < $expectedOpen ? 'before opening' : 'after closing'
            ];
        }
    }
    
    echo "Earliest slot: " . floor($earliestTime / 60) . ":" . sprintf("%02d", $earliestTime % 60) . "<br>\n";
    echo "Latest slot: " . floor($latestTime / 60) . ":" . sprintf("%02d", $latestTime % 60) . "<br>\n";
    
    if (empty($violations)) {
        echo "✅ All slots respect operating hours!<br>\n";
    } else {
        echo "❌ Found " . count($violations) . " slots outside operating hours:<br>\n";
        foreach (array_slice($violations, 0, 10) as $violation) {
            echo "  - {$violation['time']} ({$violation['reason']})<br>\n";
        }
        if (count($violations) > 10) {
            echo "  ... and " . (count($violations) - 10) . " more<br>\n";
        }
    }
    
} else {
    echo "No slots returned<br>\n";
    if (isset($response['debug'])) {
        echo "Debug info: " . print_r($response['debug'], true) . "<br>\n";
    }
}

echo "<h3>Test Complete</h3>\n";
?>