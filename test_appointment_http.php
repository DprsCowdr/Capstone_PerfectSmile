<?php
/**
 * Simple test script to test appointment creation via HTTP
 * This bypasses CodeIgniter bootstrap issues and tests the actual endpoint
 */

echo "Testing Appointment Creation via HTTP\n";
echo "=====================================\n\n";

// Test data for appointment creation
$testData = [
    'appointment_date' => date('Y-m-d', strtotime('+3 days')),
    'appointment_time' => '10:00',
    'branch_id' => 1,
    'service_id' => 1,
    'remarks' => 'Test appointment for error checking',
    'appointment_type' => 'scheduled'
];

echo "Test data:\n";
foreach ($testData as $key => $value) {
    echo "  {$key}: {$value}\n";
}
echo "\n";

// Test 1: Test Guest appointment creation (no authentication required)
echo "1. Testing Guest Appointment Creation...\n";
$guestUrl = 'http://localhost:8081/guest/book-appointment';
$guestData = array_merge($testData, [
    'first_name' => 'Test',
    'last_name' => 'Patient',
    'email' => 'test@example.com',
    'phone' => '555-0123'
]);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $guestUrl,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($guestData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/x-www-form-urlencoded',
        'X-Requested-With: XMLHttpRequest',
        'Accept: application/json'
    ],
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "   ERROR: " . $error . "\n";
} else {
    echo "   HTTP Status: " . $httpCode . "\n";
    
    // Try to parse JSON response
    $jsonResponse = json_decode($response, true);
    if ($jsonResponse) {
        echo "   Response: " . json_encode($jsonResponse, JSON_PRETTY_PRINT) . "\n";
        
        if (isset($jsonResponse['success'])) {
            echo "   Result: " . ($jsonResponse['success'] ? "SUCCESS" : "FAILED") . "\n";
            if (isset($jsonResponse['message'])) {
                echo "   Message: " . $jsonResponse['message'] . "\n";
            }
            if (isset($jsonResponse['errors'])) {
                echo "   Errors: " . json_encode($jsonResponse['errors']) . "\n";
            }
        }
    } else {
        echo "   Raw Response (first 500 chars): " . substr($response, 0, 500) . "\n";
    }
}

echo "\n";

// Test 2: Test Patient appointment creation (requires authentication)
echo "2. Testing Patient Appointment Creation (will likely fail without login)...\n";
$patientUrl = 'http://localhost:8081/patient/book-appointment';

$ch2 = curl_init();
curl_setopt_array($ch2, [
    CURLOPT_URL => $patientUrl,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($testData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/x-www-form-urlencoded',
        'X-Requested-With: XMLHttpRequest',
        'Accept: application/json'
    ],
    CURLOPT_TIMEOUT => 30
]);

$response2 = curl_exec($ch2);
$httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
$error2 = curl_error($ch2);
curl_close($ch2);

if ($error2) {
    echo "   ERROR: " . $error2 . "\n";
} else {
    echo "   HTTP Status: " . $httpCode2 . "\n";
    
    if ($httpCode2 === 302) {
        echo "   Result: REDIRECT (expected - requires authentication)\n";
    } else {
        $jsonResponse2 = json_decode($response2, true);
        if ($jsonResponse2) {
            echo "   Response: " . json_encode($jsonResponse2, JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "   Raw Response (first 500 chars): " . substr($response2, 0, 500) . "\n";
        }
    }
}

echo "\n";

// Test 3: Check if database tables exist
echo "3. Testing Database Connection...\n";
try {
    $host = 'localhost';
    $dbname = 'perfectsmile_db-v1';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host={$host};dbname={$dbname}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "   Database connection: SUCCESS\n";
    
    // Check required tables
    $tables = ['appointments', 'user', 'services', 'branches'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
        $exists = $stmt->rowCount() > 0;
        echo "   Table '{$table}': " . ($exists ? "EXISTS" : "MISSING") . "\n";
    }
    
    // Check sample data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM services");
    $serviceCount = $stmt->fetch()['count'];
    echo "   Services in database: {$serviceCount}\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM branches");
    $branchCount = $stmt->fetch()['count'];
    echo "   Branches in database: {$branchCount}\n";
    
} catch (PDOException $e) {
    echo "   Database ERROR: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 50) . "\n";
echo "This test checked appointment creation endpoints and database connectivity.\n";
echo "Results should help identify any obvious errors in the appointment system.\n";