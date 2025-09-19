<?php
/**
 * Simple test to check operating hours data and logic
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

    echo "<h2>Operating Hours Analysis</h2>\n";

    // 1. Check existing branches and their operating hours
    echo "<h3>1. Existing Branches Operating Hours</h3>\n";
    $stmt = $pdo->query("SELECT id, name, operating_hours FROM branches WHERE operating_hours IS NOT NULL");
    $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($branches)) {
        foreach ($branches as $branch) {
            echo "<strong>Branch {$branch['id']}: {$branch['name']}</strong><br>\n";
            if ($branch['operating_hours']) {
                $oh = json_decode($branch['operating_hours'], true);
                if ($oh) {
                    foreach ($oh as $day => $hours) {
                        $status = $hours['enabled'] ? 'Open' : 'Closed';
                        $times = $hours['enabled'] ? " ({$hours['open']} - {$hours['close']})" : '';
                        echo "  {$day}: {$status}{$times}<br>\n";
                    }
                } else {
                    echo "  Invalid JSON: " . htmlspecialchars($branch['operating_hours']) . "<br>\n";
                }
            } else {
                echo "  No operating hours set<br>\n";
            }
            echo "<br>\n";
        }
    } else {
        echo "No branches with operating hours found.<br>\n";
    }

    // 2. Test the logic that would be used in availableSlots
    echo "<h3>2. Testing Operating Hours Logic</h3>\n";
    
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $weekday = strtolower(date('l', strtotime('+1 day')));
    echo "Testing for date: {$tomorrow} ({$weekday})<br>\n";

    if (!empty($branches)) {
        $testBranch = $branches[0]; // Use first branch
        echo "Using branch: {$testBranch['name']}<br>\n";
        
        if ($testBranch['operating_hours']) {
            $oh = json_decode($testBranch['operating_hours'], true);
            
            if (isset($oh[$weekday])) {
                $dayHours = $oh[$weekday];
                echo "Found operating hours for {$weekday}: ";
                
                if ($dayHours['enabled']) {
                    $open = $dayHours['open'];
                    $close = $dayHours['close'];
                    echo "Open {$open} - {$close}<br>\n";
                    
                    // Validate time format (same logic as in availableSlots)
                    $openValid = preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $open);
                    $closeValid = preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $close);
                    
                    if ($openValid && $closeValid) {
                        echo "  ✅ Time format is valid<br>\n";
                        
                        // Convert to minutes for comparison
                        list($openH, $openM) = explode(':', $open);
                        list($closeH, $closeM) = explode(':', $close);
                        $openMinutes = (int)$openH * 60 + (int)$openM;
                        $closeMinutes = (int)$closeH * 60 + (int)$closeM;
                        
                        echo "  Open at minute {$openMinutes} ({$open})<br>\n";
                        echo "  Close at minute {$closeMinutes} ({$close})<br>\n";
                        
                        if ($openMinutes < $closeMinutes) {
                            echo "  ✅ Open time is before close time<br>\n";
                        } else {
                            echo "  ❌ Open time is not before close time<br>\n";
                        }
                        
                    } else {
                        echo "  ❌ Invalid time format (open: {$openValid}, close: {$closeValid})<br>\n";
                    }
                } else {
                    echo "Closed<br>\n";
                }
            } else {
                echo "No operating hours defined for {$weekday}<br>\n";
                echo "Available days: " . implode(', ', array_keys($oh)) . "<br>\n";
            }
        }
    }

    // 3. Check if there are any grace periods configured
    echo "<h3>3. Grace Periods Configuration</h3>\n";
    if (file_exists('writable/grace_periods.json')) {
        $gracePeriods = json_decode(file_get_contents('writable/grace_periods.json'), true);
        if ($gracePeriods) {
            echo "Grace periods found:<br>\n";
            foreach ($gracePeriods as $key => $value) {
                echo "  {$key}: {$value}<br>\n";
            }
        } else {
            echo "Invalid grace periods JSON<br>\n";
        }
    } else {
        echo "No grace periods file found (writable/grace_periods.json)<br>\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>\n";
}

echo "<h3>Analysis Complete</h3>\n";
?>