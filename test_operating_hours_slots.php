<?php
/**
 * Test script to validate that available slots match operating hours
 */

// Load CodeIgniter
require_once 'app/Config/Paths.php';
$paths = new Config\Paths();
require_once $paths->systemDirectory . '/bootstrap.php';

use CodeIgniter\Database\Config;

// Get database connection
$db = Config::connect();

echo "Testing Operating Hours and Available Slots Alignment\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// Test 1: Check if branches have operating hours set
echo "1. Checking branch operating hours:\n";
$branches = $db->table('branches')->select('id, name, operating_hours')->get()->getResultArray();

foreach ($branches as $branch) {
    echo "   Branch {$branch['id']}: {$branch['name']}\n";
    
    if ($branch['operating_hours']) {
        $hours = json_decode($branch['operating_hours'], true);
        if ($hours) {
            foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday'] as $day) {
                $dayHours = $hours[$day] ?? null;
                if ($dayHours && $dayHours['enabled']) {
                    echo "     {$day}: {$dayHours['open']} - {$dayHours['close']}\n";
                } else {
                    echo "     {$day}: CLOSED\n";
                }
            }
        } else {
            echo "     ERROR: Invalid JSON in operating_hours\n";
        }
    } else {
        echo "     No operating hours set (will use defaults 08:00-20:00)\n";
    }
    echo "\n";
}

// Test 2: Simulate available slots generation for today
echo "2. Testing available slots generation for today:\n";
$today = date('Y-m-d');
echo "   Date: {$today}\n";
echo "   Day of week: " . date('l', strtotime($today)) . "\n\n";

foreach ($branches as $branch) {
    echo "   Branch {$branch['id']} ({$branch['name']}):\n";
    
    // Determine operating hours for today
    $dayOfWeek = strtolower(date('l', strtotime($today)));
    $startHour = 8;
    $endHour = 20;
    $closed = false;
    
    if ($branch['operating_hours']) {
        $hours = json_decode($branch['operating_hours'], true);
        if ($hours && isset($hours[$dayOfWeek])) {
            $dayHours = $hours[$dayOfWeek];
            if ($dayHours['enabled']) {
                $startHour = (int)explode(':', $dayHours['open'])[0];
                $endHour = (int)explode(':', $dayHours['close'])[0];
            } else {
                $closed = true;
            }
        }
    }
    
    if ($closed) {
        echo "     Branch is CLOSED on {$dayOfWeek}\n";
    } else {
        echo "     Operating hours: {$startHour}:00 - {$endHour}:00\n";
        echo "     Expected available slots:\n";
        
        // Generate expected time slots (30-minute intervals)
        for ($hour = $startHour; $hour < $endHour; $hour++) {
            for ($minute = 0; $minute < 60; $minute += 30) {
                $timeStr = sprintf('%02d:%02d', $hour, $minute);
                echo "       {$timeStr}\n";
            }
        }
    }
    echo "\n";
}

// Test 3: Check if any appointments exist that are outside operating hours
echo "3. Checking for appointments outside operating hours:\n";
$appointments = $db->table('appointments')
    ->select('id, branch_id, appointment_date, appointment_time')
    ->where('appointment_date >=', date('Y-m-d', strtotime('-7 days')))
    ->get()
    ->getResultArray();

$outsideHours = [];
foreach ($appointments as $apt) {
    $branch = null;
    foreach ($branches as $b) {
        if ($b['id'] == $apt['branch_id']) {
            $branch = $b;
            break;
        }
    }
    
    if (!$branch) continue;
    
    $dayOfWeek = strtolower(date('l', strtotime($apt['appointment_date'])));
    $appointmentHour = (int)explode(':', $apt['appointment_time'])[0];
    
    $startHour = 8;
    $endHour = 20;
    $closed = false;
    
    if ($branch['operating_hours']) {
        $hours = json_decode($branch['operating_hours'], true);
        if ($hours && isset($hours[$dayOfWeek])) {
            $dayHours = $hours[$dayOfWeek];
            if ($dayHours['enabled']) {
                $startHour = (int)explode(':', $dayHours['open'])[0];
                $endHour = (int)explode(':', $dayHours['close'])[0];
            } else {
                $closed = true;
            }
        }
    }
    
    if ($closed || $appointmentHour < $startHour || $appointmentHour >= $endHour) {
        $outsideHours[] = [
            'id' => $apt['id'],
            'branch' => $branch['name'],
            'date' => $apt['appointment_date'],
            'time' => $apt['appointment_time'],
            'day' => $dayOfWeek,
            'expected_hours' => $closed ? 'CLOSED' : "{$startHour}:00-{$endHour}:00"
        ];
    }
}

if (empty($outsideHours)) {
    echo "   ✓ All appointments are within operating hours\n";
} else {
    echo "   ⚠ Found " . count($outsideHours) . " appointments outside operating hours:\n";
    foreach ($outsideHours as $apt) {
        echo "     Appointment {$apt['id']}: {$apt['branch']} on {$apt['date']} at {$apt['time']} ({$apt['day']}, expected: {$apt['expected_hours']})\n";
    }
}

echo "\nTest completed!\n";
?>