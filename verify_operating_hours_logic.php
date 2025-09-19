<?php
/**
 * Simple verification of operating hours logic
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load environment variables
$env = file_exists('.env') ? '.env' : '.env.example';
if (file_exists($env)) {
    $lines = file($env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }
}

// Database connection
$host = $_ENV['database_default_hostname'] ?? 'localhost';
$database = $_ENV['database_default_database'] ?? 'perfectsmile_db-v1';
$username = $_ENV['database_default_username'] ?? 'root';
$password = $_ENV['database_default_password'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>Operating Hours Verification</h2>\n";

    // Test logic for branch 1 (Nabua Branch) for different days
    $branchId = 1;
    $stmt = $pdo->prepare("SELECT operating_hours FROM branches WHERE id = ?");
    $stmt->execute([$branchId]);
    $branch = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$branch || empty($branch['operating_hours'])) {
        echo "❌ No operating hours found for branch {$branchId}<br>\n";
        exit;
    }

    $oh = json_decode($branch['operating_hours'], true);
    if (!$oh) {
        echo "❌ Invalid operating hours JSON<br>\n";
        exit;
    }

    echo "<h3>Testing Branch {$branchId} Operating Hours</h3>\n";
    echo "Raw operating hours: " . htmlspecialchars($branch['operating_hours']) . "<br>\n";

    // Test for next 7 days
    for ($i = 0; $i < 7; $i++) {
        $testDate = date('Y-m-d', strtotime("+{$i} days"));
        $weekday = strtolower(date('l', strtotime($testDate)));
        
        echo "<br><strong>{$testDate} ({$weekday}):</strong><br>\n";
        
        // Simulate the logic from availableSlots()
        $dayStart = strtotime($testDate . ' 08:00:00'); // Default
        $dayEnd = strtotime($testDate . ' 20:00:00');   // Default
        
        if (isset($oh[$weekday]) && isset($oh[$weekday]['enabled']) && $oh[$weekday]['enabled']) {
            $open = isset($oh[$weekday]['open']) ? $oh[$weekday]['open'] : '08:00';
            $close = isset($oh[$weekday]['close']) ? $oh[$weekday]['close'] : '17:00';
            
            // Validate HH:MM format (same regex as in the code)
            if (preg_match('/^([01]?\\d|2[0-3]):[0-5]\\d$/', $open) && preg_match('/^([01]?\\d|2[0-3]):[0-5]\\d$/', $close)) {
                $dayStart = strtotime($testDate . ' ' . $open . ':00');
                $dayEnd = strtotime($testDate . ' ' . $close . ':00');
                echo "  ✅ Open from {$open} to {$close}<br>\n";
                echo "  Day start timestamp: {$dayStart} (" . date('Y-m-d H:i:s', $dayStart) . ")<br>\n";
                echo "  Day end timestamp: {$dayEnd} (" . date('Y-m-d H:i:s', $dayEnd) . ")<br>\n";
            } else {
                echo "  ❌ Invalid time format: open='{$open}', close='{$close}'<br>\n";
            }
        } else {
            if (isset($oh[$weekday]) && isset($oh[$weekday]['enabled']) && !$oh[$weekday]['enabled']) {
                echo "  ❌ Closed (branch disabled for this day)<br>\n";
            } else {
                echo "  ⚠️  No operating hours defined, using defaults (08:00-20:00)<br>\n";
            }
        }
        
        // Simulate slot generation logic
        $duration = 30; // 30 minute appointment
        $grace = 20;    // 20 minute grace period
        $granularity = 15; // 15 minute granularity
        $blockSeconds = ($duration + $grace) * 60;
        
        echo "  Block duration: {$blockSeconds} seconds (" . ($blockSeconds/60) . " minutes)<br>\n";
        
        // Generate a few sample slots
        $step = $granularity * 60;
        $slots = [];
        $slotCount = 0;
        
        for ($t = $dayStart; $t + $blockSeconds <= $dayEnd && $slotCount < 5; $t += $step) {
            $slots[] = [
                'time' => date('g:i A', $t),
                'timestamp' => $t,
                'end_time' => date('g:i A', $t + $blockSeconds)
            ];
            $slotCount++;
        }
        
        if (!empty($slots)) {
            echo "  Sample slots:<br>\n";
            foreach ($slots as $slot) {
                echo "    - {$slot['time']} (ends at {$slot['end_time']})<br>\n";
            }
        } else {
            echo "  ❌ No slots can be generated<br>\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>\n";
}

echo "<br><h3>Conclusion</h3>\n";
echo "The operating hours logic should properly constrain available slots to business hours.<br>\n";
echo "If slots are appearing outside operating hours, the issue may be:<br>\n";
echo "1. Frontend display formatting<br>\n";
echo "2. Timezone handling<br>\n";
echo "3. Client-side filtering not respecting the server constraints<br>\n";
?>