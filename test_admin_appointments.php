<?php

// Quick test to verify admin appointment loading
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

// Minimal database connection using the correct database name
$host = $_ENV['database_default_hostname'] ?? 'localhost';
$database = $_ENV['database_default_database'] ?? 'perfectsmile_db-v1';
$username = $_ENV['database_default_username'] ?? 'root';
$password = $_ENV['database_default_password'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

echo "Testing Admin Appointment Data...\n";
echo "=================================\n\n";

// Check what's in the database directly
$stmt = $pdo->query("SELECT COUNT(*) as count FROM appointments");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Total appointments in DB: " . $result['count'] . "\n\n";

if ($result['count'] > 0) {
    // Check status distribution
    $stmt = $pdo->query("SELECT status, approval_status, COUNT(*) as count FROM appointments GROUP BY status, approval_status");
    $statusResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Appointment status distribution:\n";
    echo "--------------------------------\n";
    foreach ($statusResults as $status) {
        echo "Status: {$status['status']}, Approval: {$status['approval_status']}, Count: {$status['count']}\n";
    }
    echo "\n";
    
    // Test the new query conditions (what admin should see)
    $stmt = $pdo->query("
        SELECT appointments.*, user.name as patient_name, branches.name as branch_name
        FROM appointments 
        LEFT JOIN user ON user.id = appointments.user_id
        LEFT JOIN branches ON branches.id = appointments.branch_id
        WHERE appointments.approval_status IN ('approved', 'pending', 'auto_approved')
        AND appointments.status IN ('confirmed', 'scheduled', 'pending', 'pending_approval', 'ongoing')
        ORDER BY appointments.appointment_datetime DESC
        LIMIT 5
    ");
    
    $newResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Appointments matching NEW admin query (" . count($newResults) . " of first 5):\n";
    echo "-------------------------------------------------------------\n";
    
    foreach ($newResults as $apt) {
        $datetime = $apt['appointment_datetime'] ?? '';
        $date = substr($datetime, 0, 10);
        $time = substr($datetime, 11, 5);
        
        echo "ID: " . ($apt['id'] ?? 'N/A') . "\n";
        echo "Patient: " . ($apt['patient_name'] ?? 'N/A') . "\n";
        echo "Date: " . $date . "\n";
        echo "Time: " . $time . "\n";
        echo "Status: " . ($apt['status'] ?? 'N/A') . "\n";
        echo "Approval: " . ($apt['approval_status'] ?? 'N/A') . "\n";
        echo "Branch: " . ($apt['branch_name'] ?? 'N/A') . "\n";
        echo "---\n";
    }
    
    // Also test the old query (what admin was seeing before)
    $stmt = $pdo->query("
        SELECT appointments.*, user.name as patient_name, branches.name as branch_name
        FROM appointments 
        LEFT JOIN user ON user.id = appointments.user_id
        LEFT JOIN branches ON branches.id = appointments.branch_id
        WHERE appointments.approval_status = 'approved'
        ORDER BY appointments.appointment_datetime DESC
        LIMIT 5
    ");
    
    $oldResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nOLD query (approved only) would show: " . count($oldResults) . " appointments\n";
    
} else {
    echo "No appointments found in database!\n";
}

echo "\nTest complete. The NEW query should show more appointments than the OLD query.\n";
