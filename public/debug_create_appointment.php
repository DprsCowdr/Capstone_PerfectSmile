<?php
header('Content-Type: application/json');

// Accept POST or GET for convenience in testing
$branch = isset($_REQUEST['branch_id']) ? $_REQUEST['branch_id'] : (isset($_REQUEST['branch']) ? $_REQUEST['branch'] : 1);
$date = isset($_REQUEST['date']) ? $_REQUEST['date'] : date('Y-m-d');
$time = isset($_REQUEST['time']) ? $_REQUEST['time'] : (isset($_REQUEST['start_time']) ? $_REQUEST['start_time'] : '09:00');
$duration = isset($_REQUEST['duration']) ? (int)$_REQUEST['duration'] : 30;
$patient = isset($_REQUEST['patient_name']) ? $_REQUEST['patient_name'] : (isset($_REQUEST['patient']) ? $_REQUEST['patient'] : 'Test Patient');
$procedure = isset($_REQUEST['procedure']) ? $_REQUEST['procedure'] : '';
$notes = isset($_REQUEST['notes']) ? $_REQUEST['notes'] : '';

// compute end time
$startParts = explode(':', $time);
$hh = isset($startParts[0]) ? (int)$startParts[0] : 9;
$mm = isset($startParts[1]) ? (int)$startParts[1] : 0;
$dt = mktime($hh, $mm, 0, 1, 1, 2000);
$dtEnd = $dt + ($duration * 60);
$endTime = date('H:i', $dtEnd);

// create a fake ID
$id = rand(1000, 9999);

$response = [
    'success' => true,
    'id' => $id,
    'branch_id' => $branch,
    'date' => $date,
    'start' => $time,
    'end' => $endTime,
    'duration' => $duration,
    'patient_name' => $patient,
    'procedure' => $procedure,
    'notes' => $notes,
    'status' => 'Pending'
];

echo json_encode($response);
