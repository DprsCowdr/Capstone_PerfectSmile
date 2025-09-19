<?php
// Direct test of available slots API with proper authentication

// Bootstrap CodeIgniter
require_once __DIR__ . '/../app/Config/Paths.php';
$paths = new Config\Paths();
require_once $paths->systemDirectory . '/bootstrap.php';

// Get the CodeIgniter instance
$app = \Config\Services::codeigniter();
$app->initialize();

// Set up a fake admin session for testing
$session = \Config\Services::session();
$session->start();
$session->set([
    'user_id' => 1,
    'user_type' => 'admin',
    'email' => 'admin@test.com',
    'name' => 'Test Admin',
    'selected_branch_id' => 1
]);

echo "=== Testing Available Slots API ===\n";
echo "Session set: " . json_encode($session->get()) . "\n\n";

// Create a request object with POST data
$request = \Config\Services::request();

// Simulate POST data
$_POST = [
    'branch_id' => '1',
    'date' => '2025-09-20',
    'service_id' => '1',
    'granularity' => '3'
];

echo "POST data: " . json_encode($_POST) . "\n\n";

try {
    // Create appointments controller
    $controller = new \App\Controllers\Appointments();
    
    // Call the available slots method
    $response = $controller->availableSlots();
    
    // Get the response body
    $body = $response->getBody();
    echo "Response: " . $body . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>