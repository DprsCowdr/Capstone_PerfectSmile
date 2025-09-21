<?php
// Quick debug script to test patient dental chart API
require_once 'vendor/autoload.php';

// Set basic environment
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/patient/dental-chart';
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

// Create a CodeIgniter instance
$app = \CodeIgniter\Config\Services::codeigniter();

try {
    echo "Testing patient dental chart API...\n";
    echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
    echo "Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
    echo "XHR: " . ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? 'not set') . "\n\n";
    
    // Load the Patient controller directly
    $controller = new \App\Controllers\Patient();
    
    // Check if getDentalChart method exists
    if (method_exists($controller, 'getDentalChart')) {
        echo "✅ getDentalChart method exists\n";
        
        // For testing, let's see what happens when we call it
        // Note: This might fail due to authentication, but we'll see the error
        try {
            $result = $controller->getDentalChart();
            echo "✅ Method executed\n";
            echo "Result: " . $result . "\n";
        } catch (Exception $e) {
            echo "❌ Method execution failed: " . $e->getMessage() . "\n";
            echo "This is likely due to authentication - which is expected\n";
        }
    } else {
        echo "❌ getDentalChart method does not exist\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
