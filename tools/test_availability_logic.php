<?php
// tools/test_availability_logic.php
// Test the availability logic directly to see why approved appointments aren't blocking slots

// Database config (from .env)
$envPath = __DIR__ . '/../.env';
$dbConfig = [
    'hostname' => '127.0.0.1',
    'username' => 'root', 
    'password' => '',
    'database' => 'perfectsmile_db-v1',
    'port' => 3306
];

if (file_exists($envPath)) {
    $env = file_get_contents($envPath);
    $lines = explode("\n", $env);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, 'database.default.hostname') === 0 && strpos($line, '#') !== 0) {
            $dbConfig['hostname'] = trim(explode('=', $line, 2)[1], " \t\n\r\0\x0B\"'");
        } elseif (strpos($line, 'database.default.username') === 0 && strpos($line, '#') !== 0) {
            $dbConfig['username'] = trim(explode('=', $line, 2)[1], " \t\n\r\0\x0B\"'");
        } elseif (strpos($line, 'database.default.password') === 0 && strpos($line, '#') !== 0) {
            $dbConfig['password'] = trim(explode('=', $line, 2)[1], " \t\n\r\0\x0B\"'");
        } elseif (strpos($line, 'database.default.database') === 0 && strpos($line, '#') !== 0) {
            $dbConfig['database'] = trim(explode('=', $line, 2)[1], " \t\n\r\0\x0B\"'");
        } elseif (strpos($line, 'database.default.port') === 0 && strpos($line, '#') !== 0) {
            $dbConfig['port'] = (int)trim(explode('=', $line, 2)[1], " \t\n\r\0\x0B\"'");
        }
    }
}

try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['hostname']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset=utf8mb4",
        $dbConfig['username'],
        $dbConfig['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "=== Testing Availability Logic for 2025-09-20 ===\n\n";
    
    // Simulate the key parameters the UI would send
    $date = '2025-09-20';
    $branchId = 2;  // From the database results above
    $duration = 180; // 3 hours (typical service duration)
    $grace = 20;     // Default grace
    
    echo "Test parameters:\n";
    echo "  Date: $date\n";
    echo "  Branch ID: $branchId\n";
    echo "  Duration: $duration minutes\n";
    echo "  Grace: $grace minutes\n\n";
    
    // Step 1: Query existing appointments (mimicking the controller logic)
    echo "=== Step 1: Query existing appointments ===\n";
    $stmt = $pdo->prepare("
        SELECT id, appointment_datetime, procedure_duration, user_id, status, approval_status
        FROM appointments
        WHERE DATE(appointment_datetime) = :date
        AND approval_status = 'approved'
        AND status NOT IN ('cancelled', 'rejected', 'no_show')
        AND branch_id = :branch_id
        ORDER BY appointment_datetime
    ");
    $stmt->execute([':date' => $date, ':branch_id' => $branchId]);
    $existing = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($existing) . " approved appointments:\n";
    foreach ($existing as $appt) {
        echo "  ID {$appt['id']}: {$appt['appointment_datetime']}, duration={$appt['procedure_duration']}min, approval_status={$appt['approval_status']}\n";
    }
    
    // Step 2: Build occupied intervals (mimicking controller logic)
    echo "\n=== Step 2: Build occupied intervals ===\n";
    $occupied = [];
    foreach ($existing as $e) {
        $start = strtotime($e['appointment_datetime']);
        $dur = isset($e['procedure_duration']) && $e['procedure_duration'] ? (int)$e['procedure_duration'] : 0;
        if ($dur > 0) {
            $end = $start + ($dur * 60);
            $occupied[] = [
                'start' => $start,
                'end' => $end,
                'appointment_id' => $e['id'],
                'user_id' => $e['user_id'],
                'start_readable' => date('H:i', $start),
                'end_readable' => date('H:i', $end)
            ];
        }
    }
    
    echo "Occupied intervals:\n";
    foreach ($occupied as $occ) {
        echo "  Appointment {$occ['appointment_id']}: {$occ['start_readable']} - {$occ['end_readable']}\n";
    }
    
    // Step 3: Test specific time slots that should be unavailable
    echo "\n=== Step 3: Test specific time slots ===\n";
    
    $testSlots = [
        '08:00', // Should be blocked by appointment 245 (08:00, 15min duration)
        '08:30', // Should be blocked by appointment 252 (08:35, 180min duration)  
        '09:00', // Should be blocked by appointment 252 (08:35-11:35)
        '11:00', // Should be blocked by appointment 252 (08:35-11:35)
        '11:55', // Should be blocked by appointment 263 (11:55, 180min duration)
        '12:00', // Should be blocked by appointment 263 (11:55-14:55)
        '15:00', // Should be free
    ];
    
    $totalBlockSeconds = ($duration + $grace) * 60;
    
    foreach ($testSlots as $testTime) {
        $slotStart = strtotime($date . ' ' . $testTime . ':00');
        $slotEnd = $slotStart + $totalBlockSeconds;
        
        $isAvailable = true;
        $blockingAppt = null;
        
        // Check overlap with occupied intervals
        foreach ($occupied as $occ) {
            if ($slotStart < $occ['end'] && $slotEnd > $occ['start']) {
                $isAvailable = false;
                $blockingAppt = $occ['appointment_id'];
                break;
            }
        }
        
        $status = $isAvailable ? "AVAILABLE" : "BLOCKED by appointment $blockingAppt";
        echo "  $testTime (slot ends " . date('H:i', $slotEnd) . "): $status\n";
    }
    
    // Step 4: Check if the service_id would affect duration
    echo "\n=== Step 4: Check service durations ===\n";
    $stmt = $pdo->prepare("SELECT id, name, duration_minutes, duration_max_minutes FROM services LIMIT 5");
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Sample services:\n";
    foreach ($services as $svc) {
        echo "  ID {$svc['id']}: {$svc['name']}, duration={$svc['duration_minutes']}min, max={$svc['duration_max_minutes']}min\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nDone.\n";
?>