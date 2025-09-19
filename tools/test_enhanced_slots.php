<?php
// Simple test of enhanced availableSlots() response without session complexity
require_once __DIR__ . '/../vendor/autoload.php';

// Basic CI bootstrap without session initialization
defined('APPPATH') || define('APPPATH', realpath(__DIR__ . '/../app/') . DIRECTORY_SEPARATOR);
defined('ROOTPATH') || define('ROOTPATH', realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR);
defined('FCPATH') || define('FCPATH', realpath(__DIR__ . '/../public/') . DIRECTORY_SEPARATOR);
defined('SYSTEMPATH') || define('SYSTEMPATH', realpath(__DIR__ . '/../vendor/codeigniter4/framework/system/') . DIRECTORY_SEPARATOR);
defined('WRITEPATH') || define('WRITEPATH', realpath(__DIR__ . '/../writable/') . DIRECTORY_SEPARATOR);

// Load CodeIgniter constants
require_once APPPATH . 'Config/Constants.php';

$db = \Config\Database::connect();

echo "Testing enhanced availableSlots() response structure...\n";

// Create a simple mock request data
$_POST = [
    'date' => date('Y-m-d', strtotime('+1 day')),
    'branch_id' => 1,
    'service_id' => 1, // assume service exists
    'duration' => 60   // 1 hour
];

// Test the availability computation logic directly (without full controller auth)
$appointmentModel = new \App\Models\AppointmentModel();
$date = $_POST['date'];

// Get existing appointments for the date
$existing = $appointmentModel->select('appointments.id, appointment_datetime, procedure_duration, user_id')
                             ->where('DATE(appointment_datetime)', $date)
                             ->where('branch_id', $_POST['branch_id'])
                             ->findAll();

echo "Found " . count($existing) . " existing appointments on {$date}\n";

// Simulate occupied time calculation
$occupied = [];
foreach ($existing as $e) {
    $start = strtotime($e['appointment_datetime']);
    $dur = isset($e['procedure_duration']) && $e['procedure_duration'] ? (int)$e['procedure_duration'] : 60; // assume 60min if missing
    $end = $start + ($dur * 60);
    $occupied[] = [$start, $end, $e['id'], $e['user_id']];
    
    echo "  Appointment {$e['id']}: " . date('g:i A', $start) . " - " . date('g:i A', $end) . " (User: {$e['user_id']})\n";
}

// Test slot generation logic
$dayStart = strtotime($date . ' 08:00:00');
$dayEnd = strtotime($date . ' 17:00:00');
// Use block-based stepping: each slot is duration + grace (non-overlapping)
$duration = 60; // 1 hour
$grace = 15; // 15 minutes grace

$blockSeconds = ($duration + $grace) * 60;
$slots = [];
$sampleCount = 0;
for ($t = $dayStart; $t + $blockSeconds <= $dayEnd && $sampleCount < 10; $t += $blockSeconds) {
    $slotStart = $t;
    $slotEnd = $t + $blockSeconds;

    // Check overlap with occupied
    $isAvailable = true;
    $blockingInfo = null;
    $ownedByCurrentUser = false;
    
    foreach ($occupied as $occ) {
        if ($slotStart < $occ[1] && $slotEnd > $occ[0]) {
            $isAvailable = false;
            $blockingInfo = [
                'type' => 'appointment',
                'start' => date('g:i A', $occ[0]),
                'end' => date('g:i A', $occ[1]),
                'appointment_id' => $occ[2] ?? null,
                'blocking_user_id' => $occ[3] ?? null
            ];
            // Would check current user here: $ownedByCurrentUser = ($currentUser && $occ[3] == $currentUser);
            break;
        }
    }
    
    // Build rich slot information
    $slotInfo = [
        'time' => date('g:i A', $slotStart),
        'timestamp' => $slotStart,
        'datetime' => date('Y-m-d H:i:s', $slotStart),
        'available' => $isAvailable,
        'duration_minutes' => $duration,
        'grace_minutes' => $grace,
        'ends_at' => date('g:i A', $slotEnd)
    ];
    
    if (!$isAvailable && $blockingInfo) {
        $slotInfo['blocking_info'] = $blockingInfo;
        $slotInfo['owned_by_current_user'] = $ownedByCurrentUser;
    }
    
    $slots[] = $slotInfo;
    $sampleCount++;
}

echo "\nSample enhanced slot data:\n";
foreach ($slots as $slot) {
    $status = $slot['available'] ? 'AVAILABLE' : 'BLOCKED';
    $blocking = '';
    if (!$slot['available'] && isset($slot['blocking_info'])) {
        $blocking = " (blocked by appointment {$slot['blocking_info']['appointment_id']} from {$slot['blocking_info']['start']} to {$slot['blocking_info']['end']})";
    }
    echo "  {$slot['time']} - {$slot['ends_at']} ({$slot['duration_minutes']}min + {$slot['grace_minutes']}min grace): {$status}{$blocking}\n";
}

// Test response structure
$available_slots = array_filter($slots, function($slot) { return $slot['available']; });
$unavailable_slots = array_filter($slots, function($slot) { return !$slot['available']; });

echo "\nResponse structure summary:\n";
echo "  Total slots checked: " . count($slots) . "\n";
echo "  Available slots: " . count($available_slots) . "\n";
echo "  Unavailable slots: " . count($unavailable_slots) . "\n";

echo "\nEnhanced availableSlots() structure test completed.\n";