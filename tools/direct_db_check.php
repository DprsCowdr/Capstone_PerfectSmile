<?php
// tools/direct_db_check.php 
// Minimal database check without CodeIgniter bootstrap

// Read database config from .env
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
    
    echo "=== Database Connection Successful ===\n";
    echo "Host: {$dbConfig['hostname']}\n";
    echo "Database: {$dbConfig['database']}\n\n";
    
    // Check appointments for 2025-09-20
    $stmt = $pdo->prepare("
        SELECT id, appointment_datetime, approval_status, status, dentist_id, branch_id, user_id, procedure_duration, created_at, updated_at
        FROM appointments 
        WHERE DATE(appointment_datetime) = '2025-09-20'
        ORDER BY appointment_datetime
    ");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== Appointments for 2025-09-20 ===\n";
    if (empty($results)) {
        echo "No appointments found for 2025-09-20\n";
    } else {
        echo "Found " . count($results) . " appointments:\n\n";
        foreach ($results as $row) {
            echo "ID: {$row['id']}\n";
            echo "  DateTime: {$row['appointment_datetime']}\n";
            echo "  Approval Status: {$row['approval_status']}\n";
            echo "  Status: {$row['status']}\n";
            echo "  Dentist ID: " . ($row['dentist_id'] ?: 'null') . "\n";
            echo "  Branch ID: {$row['branch_id']}\n";
            echo "  User ID: {$row['user_id']}\n";
            echo "  Duration: " . ($row['procedure_duration'] ?: 'null') . "\n";
            echo "  Created: {$row['created_at']}\n";
            echo "  Updated: {$row['updated_at']}\n";
            echo "  ---\n";
        }
    }
    
    // Check specific IDs
    echo "\n=== Checking specific IDs (245, 252, 263) ===\n";
    $stmt = $pdo->prepare("
        SELECT id, appointment_datetime, approval_status, status, dentist_id, branch_id, user_id, procedure_duration
        FROM appointments 
        WHERE id IN (245, 252, 263)
        ORDER BY id
    ");
    $stmt->execute();
    $specificResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($specificResults)) {
        echo "None of the specific IDs (245, 252, 263) found\n";
    } else {
        foreach ($specificResults as $row) {
            echo "ID {$row['id']}: approval_status='{$row['approval_status']}', status='{$row['status']}', dentist_id=" . ($row['dentist_id'] ?: 'null') . "\n";
        }
    }
    
    // Count approved vs pending for today
    echo "\n=== Summary for 2025-09-20 ===\n";
    $stmt = $pdo->prepare("
        SELECT approval_status, COUNT(*) as count 
        FROM appointments 
        WHERE DATE(appointment_datetime) = '2025-09-20'
        GROUP BY approval_status
    ");
    $stmt->execute();
    $summary = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($summary as $row) {
        echo "Approval Status '{$row['approval_status']}': {$row['count']} appointments\n";
    }
    
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    echo "\nUsing default config. If this fails, check your .env file.\n";
    echo "Expected database config:\n";
    echo "  hostname: {$dbConfig['hostname']}\n";
    echo "  username: {$dbConfig['username']}\n";
    echo "  database: {$dbConfig['database']}\n";
}

echo "\nDone.\n";
?>