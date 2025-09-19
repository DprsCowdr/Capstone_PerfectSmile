<?php
// Test available-slots endpoint directly to see if blocking is working
require 'preload.php';

echo "=== Testing Available Slots Endpoint ===\n";
echo "Date: 2025-09-20\n";
echo "Expected blocked times: 08:00, 08:35, 11:55\n\n";

// Initialize CodeIgniter environment
$app = \Config\Services::codeigniter();
$app->initialize();

// Create a mock request for the availableSlots method
$request = \Config\Services::request();
$response = \Config\Services::response();

// Set up POST data
$_POST = [
    'branch_id' => '1',
    'date' => '2025-09-20',
    'service_id' => '2',
    'granularity' => '30'
];

// Create appointments controller instance
$controller = new \App\Controllers\Appointments();
$controller->initController($request, $response, \Config\Services::logger());

// Mock authentication by setting session data
$session = \Config\Services::session();
$session->set([
    'user_id' => 1,
    'user_type' => 'admin',
    'isLoggedIn' => true
]);

try {
    // Call the availableSlots method
    ob_start();
    $result = $controller->availableSlots();
    $output = ob_get_clean();
    
    echo "Controller output:\n";
    echo $output . "\n";
    
    // Get the response body
    $responseBody = $response->getBody();
    echo "Response body:\n";
    echo $responseBody . "\n";
    
    // Try to decode JSON
    $data = json_decode($responseBody, true);
    if ($data) {
        echo "\nDecoded response:\n";
        print_r($data);
        
        if (isset($data['available_slots']) || isset($data['slots'])) {
            $slots = $data['available_slots'] ?? $data['slots'] ?? [];
            echo "\nSlot analysis:\n";
            foreach ($slots as $slot) {
                $time = is_array($slot) ? ($slot['time'] ?? $slot['datetime'] ?? 'unknown') : $slot;
                $available = is_array($slot) ? ($slot['available'] ?? 'unknown') : 'unknown';
                echo "  Time: $time, Available: " . var_export($available, true) . "\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}