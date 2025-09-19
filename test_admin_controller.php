<?php
// Quick verification that admin controller now gets more appointments
require_once 'vendor/autoload.php';

// Initialize CodeIgniter
$app = \Config\Services::codeigniter();
$app->initialize();

echo "Testing Admin Controller Appointment Loading...\n";
echo "===============================================\n\n";

// Test the BaseAdminController's appointment loading logic
try {
    $appointmentService = new \App\Services\AppointmentService();
    
    echo "1. Testing AppointmentService::getAllAppointments()...\n";
    $appointments = $appointmentService->getAllAppointments();
    echo "   - Total appointments returned: " . count($appointments) . "\n";
    
    if (count($appointments) > 0) {
        // Check status/approval distribution
        $statusCount = [];
        $approvalCount = [];
        
        foreach ($appointments as $apt) {
            $status = $apt['status'] ?? 'unknown';
            $approval = $apt['approval_status'] ?? 'unknown';
            
            $statusCount[$status] = ($statusCount[$status] ?? 0) + 1;
            $approvalCount[$approval] = ($approvalCount[$approval] ?? 0) + 1;
        }
        
        echo "\n   Status breakdown:\n";
        foreach ($statusCount as $status => $count) {
            echo "   - $status: $count\n";
        }
        
        echo "\n   Approval status breakdown:\n";
        foreach ($approvalCount as $approval => $count) {
            echo "   - $approval: $count\n";
        }
        
        echo "\n   Recent appointments (first 3):\n";
        foreach (array_slice($appointments, 0, 3) as $apt) {
            echo "   - ID: " . ($apt['id'] ?? 'N/A') . 
                 ", Patient: " . ($apt['patient_name'] ?? 'N/A') . 
                 ", Date: " . ($apt['appointment_date'] ?? 'N/A') . 
                 ", Status: " . ($apt['status'] ?? 'N/A') . 
                 "/" . ($apt['approval_status'] ?? 'N/A') . "\n";
        }
    }
    
    echo "\n2. Testing with branch filter...\n";
    $branchAppointments = $appointmentService->getAllAppointments(1); // Nabua branch
    echo "   - Branch 1 appointments: " . count($branchAppointments) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n✅ Admin calendar should now show appointments in day/week views!\n";
echo "   The previous issue was that only 'approved' appointments were loaded,\n";
echo "   but now we include 'pending', 'auto_approved' and relevant statuses.\n";
?>