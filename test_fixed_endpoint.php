<?php
// Test the fixed Patient appointment details endpoint
echo "Patient Appointment Details Endpoint Test\n";
echo "=========================================\n\n";

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "perfectsmile_db-v1";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test the fixed services query for appointment 244
    echo "Testing fixed services query for appointment 244:\n";
    $serviceQuery = "SELECT services.id, services.name, services.duration_minutes
                     FROM appointment_service 
                     LEFT JOIN services ON services.id = appointment_service.service_id
                     WHERE appointment_service.appointment_id = 244";
    
    $serviceStmt = $pdo->prepare($serviceQuery);
    $serviceStmt->execute();
    $services = $serviceStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($services)) {
        echo "⚠️ No services found for appointment 244\n";
    } else {
        echo "✅ Found " . count($services) . " service(s):\n";
        $totalDuration = 0;
        foreach ($services as $service) {
            $duration = !empty($service['duration_minutes']) ? (int)$service['duration_minutes'] : 0;
            echo "   - Service ID: " . ($service['id'] ?? 'NULL') . "\n";
            echo "     Name: " . ($service['name'] ?? 'NULL') . "\n";
            echo "     Duration: " . $duration . " minutes\n";
            $totalDuration += $duration;
        }
        echo "   Total Duration: " . $totalDuration . " minutes\n";
    }
    
    // Test the complete appointment details query
    echo "\nTesting complete appointment details query:\n";
    $completeQuery = "SELECT appointments.*, branches.name as branch_name, dentists.name as dentist_name
                      FROM appointments 
                      LEFT JOIN branches ON branches.id = appointments.branch_id
                      LEFT JOIN user as dentists ON dentists.id = appointments.dentist_id
                      WHERE appointments.id = 244 AND appointments.user_id = 28";
    
    $completeStmt = $pdo->prepare($completeQuery);
    $completeStmt->execute();
    $appointment = $completeStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$appointment) {
        echo "❌ Appointment not found or access denied\n";
    } else {
        echo "✅ Appointment details retrieved successfully:\n";
        echo "   ID: " . $appointment['id'] . "\n";
        echo "   User ID: " . $appointment['user_id'] . "\n";
        echo "   Branch: " . ($appointment['branch_name'] ?? 'NULL') . "\n";
        echo "   Dentist: " . ($appointment['dentist_name'] ?? 'NULL') . "\n";
        echo "   Date/Time: " . $appointment['appointment_datetime'] . "\n";
        echo "   Status: " . ($appointment['status'] ?? 'NULL') . "\n";
    }
    
    echo "\n✅ All queries should now work without errors!\n";
    
} catch(PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
}
?>