<?php
header('Content-Type: application/json');

$branch = isset($_REQUEST['branch_id']) ? $_REQUEST['branch_id'] : 1;
$date = isset($_REQUEST['date']) ? $_REQUEST['date'] : date('Y-m-d');
$duration = isset($_REQUEST['duration']) ? (int)$_REQUEST['duration'] : 30;

$suggested = [
    ['start' => '08:00', 'end' => '08:30'],
    ['start' => '08:30', 'end' => '09:00'],
    ['start' => '09:30', 'end' => '10:00'],
    ['start' => '10:30', 'end' => '11:00']
];

$response = [
    'debug' => true,
    'branch_id' => $branch,
    'date' => $date,
    'duration' => $duration,
    'suggested' => $suggested
];

echo json_encode($response);
