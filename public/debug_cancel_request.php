<?php
header('Content-Type: application/json');

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
$reason = isset($_REQUEST['reason']) ? $_REQUEST['reason'] : '';

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'missing id']);
    exit;
}

// simulate creating a cancellation request for staff approval
$response = [
    'success' => true,
    'id' => $id,
    'cancel_request' => true,
    'approval_status' => 'pending',
    'reason' => $reason
];

echo json_encode($response);
