<?php
// Test to see if appointments from other users exist and might be leaking through
echo "All Users Appointment Check\n";
echo "===========================\n\n";

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "perfectsmile_db-v1";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all recent appointments to see if there are other patients
    $query = "SELECT appointments.id, appointments.user_id, user.name as patient_name, appointments.appointment_datetime
              FROM appointments 
              LEFT JOIN user ON user.id = appointments.user_id 
              ORDER BY appointments.appointment_datetime DESC 
              LIMIT 10";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Recent appointments (all users):\n\n";
    
    foreach ($appointments as $apt) {
        echo "ID: " . $apt['id'] . " | User ID: " . $apt['user_id'] . " | Patient: " . ($apt['patient_name'] ?? 'NULL') . " | Date: " . $apt['appointment_datetime'] . "\n";
    }
    
    // Count appointments by user
    echo "\n\nAppointment count by user:\n";
    $countQuery = "SELECT appointments.user_id, user.name as patient_name, COUNT(*) as count
                   FROM appointments 
                   LEFT JOIN user ON user.id = appointments.user_id 
                   GROUP BY appointments.user_id, user.name
                   ORDER BY count DESC";
    
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute();
    $counts = $countStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($counts as $count) {
        echo "User " . $count['user_id'] . " (" . ($count['patient_name'] ?? 'NULL') . "): " . $count['count'] . " appointments\n";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>