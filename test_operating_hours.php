<?php
/**
 * Test available slots to verify operating hours compliance
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

    echo "<h2>Testing Available Slots Operating Hours Compliance</h2>\n";

    // Clean up any test data first
    echo "<h3>1. Cleaning up test data...</h3>\n";
    $pdo->exec("DELETE FROM branches WHERE name LIKE 'Test%'");

    // Create test branch with specific operating hours
    echo "<h3>2. Creating test branch with operating hours...</h3>\n";
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $weekday = strtolower(date('l', strtotime('+1 day')));

    $operatingHours = [
        $weekday => [
            'enabled' => true,
            'open' => '09:00',
            'close' => '17:00'
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO branches (name, address, contact_number, email, status, operating_hours, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([
        'Test OH Branch',
        'Test Address',
        '123-456-7890',
        'test@example.com',
        'active',
        json_encode($operatingHours),
        date('Y-m-d H:i:s'),
        date('Y-m-d H:i:s')
    ]);

    $branchId = $pdo->lastInsertId();
    echo "Created branch ID: {$branchId}<br>\n";
    echo "Operating hours for {$weekday}: " . json_encode($operatingHours[$weekday]) . "<br>\n";

    // Get an existing service ID
    $serviceStmt = $pdo->query("SELECT id FROM services LIMIT 1");
    $serviceRow = $serviceStmt->fetch(PDO::FETCH_ASSOC);
    $serviceId = $serviceRow ? $serviceRow['id'] : 1;

    // Test the available slots endpoint using HTTP
    echo "<h3>3. Testing available slots endpoint...</h3>\n";
    
    $postData = http_build_query([
        'date' => $tomorrow,
        'branch_id' => $branchId,
        'service_id' => $serviceId,
        'granularity' => 15
    ]);

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'content' => $postData
        ]
    ]);

    // Determine the base URL
    $baseUrl = 'http://localhost/Capstone_PerfectSmile/public';
    if (isset($_ENV['app_baseURL'])) {
        $baseUrl = rtrim($_ENV['app_baseURL'], '/');
    }

    $url = $baseUrl . '/appointments/availableSlots';
    echo "Testing URL: {$url}<br>\n";

    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        echo "❌ Failed to call available slots endpoint. Trying direct file access...<br>\n";
        
        // Try direct approach by simulating the request
        $_POST = [
            'date' => $tomorrow,
            'branch_id' => $branchId,
            'service_id' => $serviceId,
            'granularity' => 15
        ];
        
        // Include the CodeIgniter bootstrap
        chdir(__DIR__);
        require_once 'public/index.php';
        
    } else {
        $body = json_decode($response, true);
        
        if ($body && isset($body['success'])) {
            echo "Success: " . ($body['success'] ? 'true' : 'false') . "<br>\n";
            
            if (isset($body['slots']) && !empty($body['slots'])) {
                echo "Number of slots: " . count($body['slots']) . "<br>\n";
                echo "First 10 slots:<br>\n";
                
                foreach (array_slice($body['slots'], 0, 10) as $i => $slot) {
                    $time = is_array($slot) ? $slot['time'] : $slot;
                    echo "  Slot " . ($i + 1) . ": {$time}<br>\n";
                }
                
                // Check if slots respect operating hours (9:00 AM - 5:00 PM)
                echo "<br><h4>Operating Hours Compliance Check:</h4>\n";
                $violations = 0;
                
                foreach ($body['slots'] as $slot) {
                    $time = is_array($slot) ? $slot['time'] : $slot;
                    
                    // Parse time (format could be "9:00 AM" or "09:00")
                    $timeObj = DateTime::createFromFormat('g:i A', $time);
                    if (!$timeObj) {
                        $timeObj = DateTime::createFromFormat('H:i', $time);
                    }
                    
                    if ($timeObj) {
                        $hour = (int)$timeObj->format('H');
                        $minute = (int)$timeObj->format('i');
                        $totalMinutes = $hour * 60 + $minute;
                        
                        // Check if time is within 9:00 AM (540 minutes) to 5:00 PM (1020 minutes)
                        if ($totalMinutes < 540 || $totalMinutes >= 1020) {
                            echo "  VIOLATION: Slot {$time} is outside operating hours (9:00 AM - 5:00 PM)<br>\n";
                            $violations++;
                        }
                    }
                }
                
                if ($violations == 0) {
                    echo "  ✅ All slots respect operating hours!<br>\n";
                } else {
                    echo "  ❌ Found {$violations} slot(s) outside operating hours<br>\n";
                }
                
            } else {
                echo "No slots returned or error occurred<br>\n";
                if (isset($body['message'])) {
                    echo "Message: " . $body['message'] . "<br>\n";
                }
            }
        } else {
            echo "Invalid response format<br>\n";
            echo "Response: " . htmlspecialchars($response) . "<br>\n";
        }
    }

    // Clean up
    echo "<h3>4. Cleaning up...</h3>\n";
    $pdo->exec("DELETE FROM branches WHERE id = $branchId");
    echo "Test branch deleted.<br>\n";

} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "<br>\n";
}

echo "<h3>Test Complete</h3>\n";
?>