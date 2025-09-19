<?php
// Smoke test: insert a confirmed appointment for dentist_id=30 then create an overlapping availability block
// WARNING: This will modify local DB data in development. Use with caution.

require __DIR__ . '/../../vendor/autoload.php';

$db = \Config\Database::connect();

// Insert a patient if none exists
$userTb = $db->table('user');
$patient = $userTb->where('user_type', 'patient')->get(1)->getRowArray();
if (!$patient) {
    $userTb->insert(['name'=>'Smoke Test Patient','email'=>'smoketest@example.local','user_type'=>'patient','status'=>'active']);
    $patientId = $db->insertID();
} else {
    $patientId = $patient['id'];
}

// Create a confirmed appointment tomorrow at 10:00 for dentist 30
$apptTb = $db->table('appointments');
$date = date('Y-m-d', strtotime('+1 day'));
$datetime = $date . ' 10:00:00';

// Clean any existing test appointment at same time
$apptTb->where('appointment_datetime', $datetime)->where('dentist_id', 30)->delete();

$apptTb->insert([
    'user_id' => $patientId,
    'branch_id' => 1,
    'dentist_id' => 30,
    'appointment_datetime' => $datetime,
    'procedure_duration' => 30,
    'status' => 'confirmed',
    'approval_status' => 'approved',
    'created_at' => date('Y-m-d H:i:s')
]);
$appointmentId = $db->insertID();

echo "Inserted appointment id: {$appointmentId} at {$datetime}\n";

// Now call the availability create endpoint via internal controller invocation (avoid HTTP request complexity)
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'user_id' => 30,
    'type' => 'day_off',
    'start' => $date . ' 09:30:00',
    'end' => $date . ' 11:30:00',
    'notes' => 'Smoke test block'
];

// Boot CodeIgniter environment and call controller
$request = \Config\Services::request();
$response = \Config\Services::response();

$controller = new \App\Controllers\Availability();
$result = $controller->create();

// If result is an instance of Response, get its body
if ($result instanceof \CodeIgniter\HTTP\ResponseInterface) {
    $body = $result->getBody();
    echo "Controller response:\n" . $body . "\n";
} else {
    echo "Controller returned: " . json_encode($result) . "\n";
}
