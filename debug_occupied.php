<?php
// Debug the getOccupiedIntervals method
require_once 'c:\Users\John bert\OneDrive\Documents\GitHub\Capstone_PerfectSmile\vendor\autoload.php';

$date = '2025-09-23';
echo "Debugging getOccupiedIntervals for date: $date\n";

// Direct DB query to see what appointments exist
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=perfectsmile_db-v1', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "\nDirect DB query for appointments:\n";
    $stmt = $pdo->prepare("SELECT id, appointment_datetime, approval_status, status, procedure_duration, branch_id, dentist_id FROM appointments WHERE DATE(appointment_datetime) = :d ORDER BY appointment_datetime");
    $stmt->execute([':d' => $date]);
    $appts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($appts as $apt) {
        echo "  ID {$apt['id']}: {$apt['appointment_datetime']} | status={$apt['status']} | approval={$apt['approval_status']} | duration={$apt['procedure_duration']} | branch={$apt['branch_id']} | dentist={$apt['dentist_id']}\n";
    }
    
    echo "\nFiltered to approved only:\n";
    $stmt2 = $pdo->prepare("SELECT id, appointment_datetime, approval_status, status, procedure_duration, branch_id, dentist_id FROM appointments WHERE DATE(appointment_datetime) = :d AND approval_status IN ('approved', 'auto_approved') AND status NOT IN ('cancelled','rejected','no_show') ORDER BY appointment_datetime");
    $stmt2->execute([':d' => $date]);
    $appts2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($appts2 as $apt) {
        echo "  ID {$apt['id']}: {$apt['appointment_datetime']} | status={$apt['status']} | approval={$apt['approval_status']} | duration={$apt['procedure_duration']} | branch={$apt['branch_id']} | dentist={$apt['dentist_id']}\n";
    }
    
    echo "\nTest with service duration join:\n";
    $query = "SELECT a.id, a.appointment_datetime, COALESCE(svc.total_service_minutes, a.procedure_duration, 0) AS duration_minutes, a.user_id
              FROM appointments a
              LEFT JOIN (
                  SELECT aps.appointment_id, SUM(COALESCE(s.duration_max_minutes, s.duration_minutes, 0)) AS total_service_minutes
                  FROM appointment_service aps
                  JOIN services s ON s.id = aps.service_id
                  GROUP BY aps.appointment_id
              ) svc ON svc.appointment_id = a.id
              WHERE DATE(a.appointment_datetime) = :d
              AND a.approval_status IN ('approved','auto_approved')
              AND a.status NOT IN ('cancelled','rejected','no_show')";
    
    $stmt3 = $pdo->prepare($query);
    $stmt3->execute([':d' => $date]);
    $appts3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($appts3 as $apt) {
        echo "  ID {$apt['id']}: {$apt['appointment_datetime']} | duration={$apt['duration_minutes']} | user={$apt['user_id']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>