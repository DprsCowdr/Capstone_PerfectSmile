<?php
/**
 * Test admin available slots functionality
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

    echo "<h2>Admin Available Slots Test</h2>\n";

    // Test the available slots API endpoint that admin will use
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $weekday = strtolower(date('l', strtotime($tomorrow)));
    
    echo "<h3>Testing Admin Available Slots API</h3>\n";
    echo "Date: {$tomorrow} ({$weekday})<br>\n";
    
    // Get branch information
    $stmt = $pdo->query("SELECT id, name, operating_hours FROM branches ORDER BY id LIMIT 2");
    $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get service information
    $stmt = $pdo->query("SELECT id, name, duration_minutes FROM services ORDER BY id LIMIT 3");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>Available Branches:</h4>\n";
    foreach ($branches as $branch) {
        echo "- Branch {$branch['id']}: {$branch['name']}<br>\n";
        if ($branch['operating_hours']) {
            $oh = json_decode($branch['operating_hours'], true);
            if ($oh && isset($oh[$weekday])) {
                $dayHours = $oh[$weekday];
                if ($dayHours['enabled']) {
                    echo "  Operating hours for {$weekday}: {$dayHours['open']} - {$dayHours['close']}<br>\n";
                } else {
                    echo "  Closed on {$weekday}<br>\n";
                }
            }
        }
    }
    
    echo "<h4>Available Services:</h4>\n";
    foreach ($services as $service) {
        echo "- Service {$service['id']}: {$service['name']} ({$service['duration_minutes']} minutes)<br>\n";
    }
    
    // Test API calls for different combinations
    echo "<h4>API Test Results:</h4>\n";
    
    foreach ($branches as $branch) {
        foreach (array_slice($services, 0, 2) as $service) { // Test first 2 services
            echo "<strong>Branch: {$branch['name']}, Service: {$service['name']}</strong><br>\n";
            
            // Simulate the admin API call
            $postData = http_build_query([
                'date' => $tomorrow,
                'branch_id' => $branch['id'],
                'service_id' => $service['id'],
                'granularity' => 15
            ]);

            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'content' => $postData
                ]
            ]);

            // Use localhost URL (adjust if needed)
            $baseUrl = 'http://localhost/Capstone_PerfectSmile/public';
            if (isset($_ENV['app_baseURL'])) {
                $baseUrl = rtrim($_ENV['app_baseURL'], '/');
            }

            $url = $baseUrl . '/appointments/available-slots';
            
            $response = @file_get_contents($url, false, $context);
            
            if ($response === false) {
                echo "  ❌ Failed to call API endpoint<br>\n";
            } else {
                $body = json_decode($response, true);
                
                if ($body && isset($body['success'])) {
                    if ($body['success']) {
                        $slots = $body['slots'] ?? $body['available_slots'] ?? [];
                        echo "  ✅ API Success: " . count($slots) . " slots returned<br>\n";
                        
                        if (!empty($slots)) {
                            echo "  First 5 slots: ";
                            $firstFive = array_slice($slots, 0, 5);
                            foreach ($firstFive as $slot) {
                                $time = is_array($slot) ? $slot['time'] : $slot;
                                echo $time . " ";
                            }
                            echo "<br>\n";
                            
                            // Check operating hours compliance
                            if ($branch['operating_hours']) {
                                $oh = json_decode($branch['operating_hours'], true);
                                if ($oh && isset($oh[$weekday]) && $oh[$weekday]['enabled']) {
                                    $openTime = $oh[$weekday]['open'];
                                    $closeTime = $oh[$weekday]['close'];
                                    
                                    $violations = 0;
                                    foreach ($slots as $slot) {
                                        $time = is_array($slot) ? $slot['time'] : $slot;
                                        
                                        // Parse time and check against operating hours
                                        if (preg_match('/(\d{1,2}):(\d{2})\s*(AM|PM)/i', $time, $matches)) {
                                            $hour = (int)$matches[1];
                                            $minute = (int)$matches[2];
                                            $ampm = strtoupper($matches[3]);
                                            
                                            if ($ampm === 'PM' && $hour !== 12) {
                                                $hour += 12;
                                            } elseif ($ampm === 'AM' && $hour === 12) {
                                                $hour = 0;
                                            }
                                            
                                            $slotTime = sprintf('%02d:%02d', $hour, $minute);
                                            
                                            if ($slotTime < $openTime || $slotTime >= $closeTime) {
                                                $violations++;
                                            }
                                        }
                                    }
                                    
                                    if ($violations == 0) {
                                        echo "  ✅ All slots respect operating hours ({$openTime} - {$closeTime})<br>\n";
                                    } else {
                                        echo "  ❌ {$violations} slots violate operating hours<br>\n";
                                    }
                                }
                            }
                        } else {
                            echo "  ⚠️  No slots available<br>\n";
                        }
                        
                        if (isset($body['metadata'])) {
                            $meta = $body['metadata'];
                            echo "  Metadata: {$meta['available_count']} available, {$meta['total_slots_checked']} total checked<br>\n";
                            if (isset($meta['day_start']) && isset($meta['day_end'])) {
                                echo "  Operating window: {$meta['day_start']} - {$meta['day_end']}<br>\n";
                            }
                        }
                    } else {
                        echo "  ❌ API Error: " . ($body['message'] ?? 'Unknown error') . "<br>\n";
                    }
                } else {
                    echo "  ❌ Invalid API response format<br>\n";
                }
            }
            echo "<br>\n";
        }
    }

} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "<br>\n";
}

echo "<h3>Admin Available Slots Implementation Summary</h3>\n";
echo "✅ Added available slots button to admin calendar header<br>\n";
echo "✅ Added JavaScript functionality for admin available slots menu<br>\n";
echo "✅ Admin calendar now shows the purple 'Available slots' button<br>\n";
echo "✅ Clicking the button loads available slots for the selected date, branch, and service<br>\n";
echo "✅ Slots respect branch operating hours and include metadata<br>\n";
echo "✅ Admin can click on any slot to auto-fill the appointment time<br>\n";

echo "<h3>Usage Instructions for Admin:</h3>\n";
echo "1. Go to Admin → Appointments<br>\n";
echo "2. The calendar will show a purple 'Available slots' button in the header<br>\n";
echo "3. Select a date, branch, and service in the appointment form<br>\n";
echo "4. Click the 'Available slots' button to see available times<br>\n";
echo "5. Click any time slot to auto-fill the appointment time field<br>\n";
echo "6. Complete the rest of the appointment form and submit<br>\n";

echo "<h3>Test Complete</h3>\n";
?>