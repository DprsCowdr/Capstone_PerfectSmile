<?php
// CLI smoke test for AppointmentService flows (dev only)
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap CodeIgniter-ish environment minimally if needed
// If project uses global functions (config(), WRITEPATH), ensure we include app/Config/Paths or use CLI context

// Load necessary classes
use App\Services\AppointmentService;

$service = new AppointmentService();

$results = [];

// 1) Create appointment as patient
$patientData = [
    'user_id' => 99999, // test guest/patient id; use high id to avoid collision
    'appointment_date' => date('Y-m-d', strtotime('+1 day')),
    'appointment_time' => '09:30',
    'branch_id' => 1,
    'dentist_id' => 1,
    'appointment_type' => 'scheduled',
    'created_by_role' => 'patient',
];
$res1 = $service->createAppointment($patientData);
$results['patient_create'] = $res1;

// 2) Create appointment as staff
$staffData = $patientData;
$staffData['appointment_time'] = '10:00';
$staffData['created_by_role'] = 'staff';
$staffData['user_id'] = 88888;
$res2 = $service->createAppointment($staffData);
$results['staff_create'] = $res2;

// 3) Approve the staff-created appointment (use record id if created)
$approveTargetId = null;
if (!empty($res2['record']['id'])) $approveTargetId = $res2['record']['id'];
else if (!empty($res1['record']['id'])) $approveTargetId = $res1['record']['id'];

if ($approveTargetId) {
    $res3 = $service->approveAppointment($approveTargetId, null);
    $results['approve_result'] = $res3;
} else {
    $results['approve_result'] = ['success' => false, 'message' => 'No appointment found to approve'];
}

// 4) Check latest branch notifications
$bnList = [];
if (class_exists('\App\\Models\\BranchNotificationModel')) {
    $bnModel = new \App\Models\BranchNotificationModel();
    $bnList = $bnModel->orderBy('created_at', 'DESC')->limit(10)->findAll();
}
$results['branch_notifications'] = $bnList;

echo json_encode($results, JSON_PRETTY_PRINT);
