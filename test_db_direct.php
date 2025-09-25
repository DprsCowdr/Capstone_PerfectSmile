<?php
// Simple test of appointment data structure
echo "Patient Appointment Data Structure Test\n";
echo "======================================\n\n";

// Connect to database directly for testing
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "perfectsmile_db-v1";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test the join query that should be used in Patient controller
    $query = "SELECT appointments.*, user.name as patient_name, branches.name as branch_name, dentists.name as dentist_name 
              FROM appointments 
              LEFT JOIN user ON user.id = appointments.user_id 
              LEFT JOIN branches ON branches.id = appointments.branch_id
              LEFT JOIN user as dentists ON dentists.id = appointments.dentist_id
              WHERE appointments.user_id = 28 
              ORDER BY appointments.appointment_datetime DESC 
              LIMIT 5";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($appointments) . " appointments for user_id 28:\n\n";
    
    foreach ($appointments as $apt) {
        echo "ID: " . $apt['id'] . "\n";
        echo "Patient Name: " . ($apt['patient_name'] ?? 'NULL') . "\n";
        echo "User ID: " . ($apt['user_id'] ?? 'NULL') . "\n";
        echo "Branch: " . ($apt['branch_name'] ?? 'NULL') . "\n";
        echo "Date/Time: " . ($apt['appointment_datetime'] ?? 'NULL') . "\n";
        echo "---\n";
    }
    
    // Check appointment 295 specifically
    echo "\nChecking appointment 295:\n";
    $query295 = "SELECT appointments.*, user.name as patient_name 
                 FROM appointments 
                 LEFT JOIN user ON user.id = appointments.user_id 
                 WHERE appointments.id = 295";
    
    $stmt295 = $pdo->prepare($query295);
    $stmt295->execute();
    $apt295 = $stmt295->fetch(PDO::FETCH_ASSOC);
    
    if ($apt295) {
        echo "Appointment 295: user_id=" . $apt295['user_id'] . ", patient_name=" . ($apt295['patient_name'] ?? 'NULL') . "\n";
    } else {
        echo "Appointment 295 not found\n";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>