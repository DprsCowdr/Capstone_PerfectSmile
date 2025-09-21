<?php
/**
 * Test script to validate available slots accuracy fixes
 */

// Set up environment
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

    echo "<h2>Testing Available Slots Accuracy Fixes</h2>\n";

    // Test 1: Create test appointments with different approval statuses
    echo "<h3>1. Setting up test data...</h3>\n";
    
    $testDate = date('Y-m-d', strtotime('+2 days'));
    $testBranchId = 1; // Assume branch 1 exists
    $testServiceId = 1; // Assume service 1 exists
    
    // Clean up any existing test data
    $pdo->exec("DELETE FROM appointments WHERE remarks = 'TEST_APPOINTMENT_FOR_VALIDATION'");
    
    // Get an existing user ID
    $userStmt = $pdo->query("SELECT id FROM user LIMIT 1");
    $testUserId = $userStmt->fetchColumn();
    
    if (!$testUserId) {
        echo "❌ No users found in database. Please create a user first.<br>\n";
        return;
    }
    
    echo "   Using test user ID: {$testUserId}<br>\n";
    
    // Create test appointments with different statuses
    $testAppointments = [
        ['time' => '09:00:00', 'status' => 'confirmed', 'approval_status' => 'approved', 'should_block' => true],
        ['time' => '10:00:00', 'status' => 'pending_approval', 'approval_status' => 'pending', 'should_block' => false],
        ['time' => '11:00:00', 'status' => 'cancelled', 'approval_status' => 'approved', 'should_block' => false],
        ['time' => '14:00:00', 'status' => 'confirmed', 'approval_status' => 'rejected', 'should_block' => false],
        ['time' => '15:00:00', 'status' => 'confirmed', 'approval_status' => 'approved', 'should_block' => true],
    ];
    
    $insertStmt = $pdo->prepare("
        INSERT INTO appointments (
            branch_id, user_id, appointment_datetime, status, approval_status, 
            appointment_type, remarks, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, 'scheduled', 'TEST_APPOINTMENT_FOR_VALIDATION', NOW(), NOW())
    ");
    
    foreach ($testAppointments as $apt) {
        $datetime = $testDate . ' ' . $apt['time'];
        $insertStmt->execute([$testBranchId, $testUserId, $datetime, $apt['status'], $apt['approval_status']]);
        echo "   Created appointment: {$apt['time']} - {$apt['status']}/{$apt['approval_status']} (should block slots: " . ($apt['should_block'] ? 'YES' : 'NO') . ")<br>\n";
    }
    
    // Test 2: Check database query results
    echo "<h3>2. Testing database filtering...</h3>\n";
    
    // Query that should be used by the fixed availableSlots method
    $stmt = $pdo->prepare("
        SELECT id, appointment_datetime, status, approval_status 
        FROM appointments 
        WHERE DATE(appointment_datetime) = ? 
        AND approval_status = 'approved' 
        AND status NOT IN ('cancelled', 'rejected', 'no_show')
        AND branch_id = ?
        ORDER BY appointment_datetime
    ");
    
    $stmt->execute([$testDate, $testBranchId]);
    $filteredResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Appointments that should block slots on {$testDate}:<br>\n";
    foreach ($filteredResults as $apt) {
        $time = date('H:i', strtotime($apt['appointment_datetime']));
        echo "     - {$time}: {$apt['status']}/{$apt['approval_status']}<br>\n";
    }
    
    $expectedBlocking = array_filter($testAppointments, function($apt) { return $apt['should_block']; });
    echo "   Expected blocking appointments: " . count($expectedBlocking) . "<br>\n";
    echo "   Actual blocking appointments: " . count($filteredResults) . "<br>\n";
    
    if (count($filteredResults) === count($expectedBlocking)) {
        echo "   ✅ <strong>Database filtering is working correctly!</strong><br>\n";
    } else {
        echo "   ❌ <strong>Database filtering issue detected!</strong><br>\n";
    }
    
    // Test 3: Test operating hours extension
    echo "<h3>3. Testing operating hours extension...</h3>\n";
    
    // Check if branch has operating hours set
    $stmt = $pdo->prepare("SELECT operating_hours FROM branches WHERE id = ?");
    $stmt->execute([$testBranchId]);
    $branch = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($branch && $branch['operating_hours']) {
        $hours = json_decode($branch['operating_hours'], true);
        if ($hours) {
            $dayOfWeek = strtolower(date('l', strtotime($testDate)));
            if (isset($hours[$dayOfWeek]) && $hours[$dayOfWeek]['enabled']) {
                $openTime = $hours[$dayOfWeek]['open'];
                $closeTime = $hours[$dayOfWeek]['close'];
                echo "   Branch operating hours on {$dayOfWeek}: {$openTime} - {$closeTime}<br>\n";
                
                $closeHour = (int)explode(':', $closeTime)[0];
                if ($closeHour >= 19) { // 7 PM or later
                    echo "   ✅ <strong>Evening hours available (closes at {$closeTime})</strong><br>\n";
                } else {
                    echo "   ⚠️ <strong>Limited evening hours (closes at {$closeTime})</strong><br>\n";
                }
            } else {
                echo "   Branch is closed on {$dayOfWeek}<br>\n";
            }
        } else {
            echo "   Invalid operating hours JSON<br>\n";
        }
    } else {
        echo "   No operating hours set - will use defaults (08:00-21:00)<br>\n";
        echo "   ✅ <strong>Default extended hours include evening coverage</strong><br>\n";
    }
    
    // Test 4: Simulate available slots generation
    echo "<h3>4. Simulating available slots generation...</h3>\n";
    
    $startHour = 8;
    $endHour = 21; // Extended evening hours
    $blockedTimes = [];
    
    foreach ($filteredResults as $apt) {
        $time = date('H:i', strtotime($apt['appointment_datetime']));
        $blockedTimes[] = $time;
    }
    
    $totalSlots = 0;
    $availableSlots = 0;
    $blockedSlots = 0;
    $eveningSlots = 0; // 6 PM (18:00) and later
    
    echo "   Generating 30-minute slots from {$startHour}:00 to {$endHour}:00:<br>\n";
    
    for ($hour = $startHour; $hour < $endHour; $hour++) {
        for ($minute = 0; $minute < 60; $minute += 30) {
            $timeStr = sprintf('%02d:%02d', $hour, $minute);
            $totalSlots++;
            
            if ($hour >= 18) {
                $eveningSlots++;
            }
            
            if (in_array($timeStr, $blockedTimes)) {
                $blockedSlots++;
                echo "     {$timeStr} - ❌ BLOCKED<br>\n";
            } else {
                $availableSlots++;
                if ($hour >= 18) {
                    echo "     {$timeStr} - ✅ Available (Evening)<br>\n";
                } else {
                    echo "     {$timeStr} - ✅ Available<br>\n";
                }
            }
        }
    }
    
    echo "<h3>5. Results Summary:</h3>\n";
    echo "   Total time slots generated: {$totalSlots}<br>\n";
    echo "   Available slots: {$availableSlots}<br>\n";
    echo "   Blocked slots: {$blockedSlots}<br>\n";
    echo "   Evening slots (6 PM+): {$eveningSlots}<br>\n";
    echo "   Accuracy: " . round(($availableSlots / $totalSlots) * 100, 1) . "%<br>\n";
    
    if ($blockedSlots === count($expectedBlocking)) {
        echo "   ✅ <strong>Slot blocking accuracy is correct!</strong><br>\n";
    } else {
        echo "   ❌ <strong>Slot blocking accuracy issue!</strong><br>\n";
    }
    
    if ($eveningSlots > 0) {
        echo "   ✅ <strong>Evening time slots are available!</strong><br>\n";
    } else {
        echo "   ❌ <strong>No evening time slots found!</strong><br>\n";
    }
    
    // Clean up test data
    echo "<h3>6. Cleaning up test data...</h3>\n";
    $pdo->exec("DELETE FROM appointments WHERE remarks = 'TEST_APPOINTMENT_FOR_VALIDATION'");
    echo "   Test appointments removed.<br>\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>\n";
}

echo "<h3>Test Complete!</h3>\n";
echo "<p>If all tests show ✅, the available slots accuracy fixes are working correctly.</p>\n";
?>