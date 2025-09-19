<?php
/**
 * Test Available Slots Algorithm
 * Verify the available slots calculation with operating hours, service duration, and grace period
 */

echo "=== Available Slots Algorithm Test ===\n";
echo "Date: 2025-09-20 (Saturday)\n\n";

// Test data based on your metadata
$testData = [
    'branch_id' => '1', // Nabua branch
    'service_id' => '1', // Service with 180 minutes duration
    'date' => '2025-09-20',
    'duration_minutes' => 180,
    'grace_minutes' => 20,
    'operating_hours' => [
        'start' => '09:00',
        'end' => '21:00'
    ]
];

echo "=== Test Configuration ===\n";
echo "Service Duration: {$testData['duration_minutes']} minutes (3 hours)\n";
echo "Grace Period: {$testData['grace_minutes']} minutes\n";
echo "Total Appointment Length: " . ($testData['duration_minutes'] + $testData['grace_minutes']) . " minutes\n";
echo "Operating Hours: {$testData['operating_hours']['start']} - {$testData['operating_hours']['end']}\n\n";

// Calculate appointment end time for different start times
function calculateAppointmentEnd($startTime, $durationMinutes, $graceMinutes) {
    $totalMinutes = $durationMinutes + $graceMinutes;
    $endTimestamp = strtotime($startTime) + ($totalMinutes * 60);
    return date('H:i', $endTimestamp);
}

echo "=== Appointment Length Calculation Examples ===\n";
$sampleTimes = ['09:00', '09:30', '10:00', '13:50', '14:00', '15:00'];

foreach ($sampleTimes as $time) {
    $endTime = calculateAppointmentEnd("2025-09-20 $time:00", $testData['duration_minutes'], $testData['grace_minutes']);
    $endTime12 = date('g:i A', strtotime("2025-09-20 $endTime:00"));
    $start12 = date('g:i A', strtotime("2025-09-20 $time:00"));
    
    echo "Start: $start12 → End: $endTime12 (Duration: 3h + Grace: 20m = 3h 20m)\n";
}

echo "\n=== Problem Analysis ===\n";
echo "Based on your metadata, here are the issues:\n\n";

echo "1. FILTERING ISSUE:\n";
echo "   - Total slots checked: 100\n";
echo "   - Available slots: 3\n";
echo "   - Problem: Too restrictive filtering\n\n";

echo "2. OPERATING HOURS ISSUE:\n";
echo "   - Operating hours: 9:00 AM - 9:00 PM\n";
echo "   - First available slot: 1:50 PM\n";
echo "   - Problem: Not showing morning slots\n\n";

echo "3. BLOCKING APPOINTMENTS:\n";
echo "   - Appointment #239: 10:50 AM - 1:50 PM (3h appointment)\n";
echo "   - Appointment #240: 9:00 AM - 9:15 AM (15m appointment)\n";
echo "   - Gap: 9:15 AM - 10:50 AM (1h 35m available)\n\n";

echo "4. SERVICE DURATION ANALYSIS:\n";
echo "   - Service needs 3h 20m total time\n";
echo "   - Gap of 1h 35m is too short\n";
echo "   - Next available: after 1:50 PM\n\n";

echo "=== Recommended Solutions ===\n";
echo "1. IMPROVE SLOT GENERATION:\n";
echo "   - Generate slots every 15-30 minutes instead of 3 minutes\n";
echo "   - Show slots that can fit the full appointment length\n";
echo "   - Filter by: start_time + duration + grace <= operating_end\n\n";

echo "2. BETTER UI DISPLAY:\n";
echo "   - Show only truly available slots (available: true)\n";
echo "   - Group slots by time periods (Morning, Afternoon, Evening)\n";
echo "   - Show appointment end time for each slot\n\n";

echo "3. FLEXIBLE OPTIONS:\n";
echo "   - Offer alternative service durations if available\n";
echo "   - Show next available day if current day is full\n";
echo "   - Allow splitting long appointments if clinic policy permits\n\n";

// Generate better slot suggestions
echo "=== Better Slot Generation Example ===\n";
$operatingStart = strtotime("2025-09-20 09:00:00");
$operatingEnd = strtotime("2025-09-20 21:00:00");
$appointmentLength = ($testData['duration_minutes'] + $testData['grace_minutes']) * 60; // in seconds

// Existing appointments (from your metadata)
$blockedPeriods = [
    ['start' => strtotime("2025-09-20 09:00:00"), 'end' => strtotime("2025-09-20 09:15:00")],
    ['start' => strtotime("2025-09-20 10:50:00"), 'end' => strtotime("2025-09-20 13:50:00")]
];

echo "Looking for slots that can fit a " . round($appointmentLength/60) . "-minute appointment:\n\n";

$currentTime = $operatingStart;
$slotInterval = 30 * 60; // 30-minute intervals
$availableSlots = [];

while ($currentTime <= $operatingEnd) {
    $slotEnd = $currentTime + $appointmentLength;
    
    // Check if appointment would end before operating hours close
    if ($slotEnd <= $operatingEnd) {
        // Check if slot conflicts with existing appointments
        $hasConflict = false;
        foreach ($blockedPeriods as $blocked) {
            if (($currentTime < $blocked['end']) && ($slotEnd > $blocked['start'])) {
                $hasConflict = true;
                break;
            }
        }
        
        if (!$hasConflict) {
            $startTime12 = date('g:i A', $currentTime);
            $endTime12 = date('g:i A', $slotEnd);
            $availableSlots[] = [
                'start' => $startTime12,
                'end' => $endTime12,
                'duration' => round($appointmentLength/60) . ' minutes'
            ];
            echo "✅ Available: $startTime12 - $endTime12\n";
        } else {
            $startTime12 = date('g:i A', $currentTime);
            echo "❌ Blocked: $startTime12 (conflicts with existing appointment)\n";
        }
    } else {
        $startTime12 = date('g:i A', $currentTime);
        echo "❌ Too late: $startTime12 (appointment would end after closing)\n";
    }
    
    $currentTime += $slotInterval;
}

echo "\n=== Summary ===\n";
echo "Total available slots found: " . count($availableSlots) . "\n";
echo "Each slot allows for 3-hour service + 20-minute grace period\n";
echo "Slots are generated every 30 minutes for better selection\n\n";

if (count($availableSlots) > 0) {
    echo "First available slot: {$availableSlots[0]['start']} - {$availableSlots[0]['end']}\n";
    echo "Last available slot: " . end($availableSlots)['start'] . " - " . end($availableSlots)['end'] . "\n";
} else {
    echo "No available slots for this service duration on this date.\n";
    echo "Recommend:\n";
    echo "- Choose a different date\n";
    echo "- Select a shorter service\n";
    echo "- Consider splitting into multiple appointments\n";
}

echo "\n=== JavaScript Fix Needed ===\n";
echo "The admin calendar JavaScript should:\n";
echo "1. Filter response.slots to only show available: true\n";
echo "2. Display slots in a user-friendly format\n";
echo "3. Show appointment end times\n";
echo "4. Group slots by time periods\n";
echo "5. Handle empty results gracefully\n";
?>