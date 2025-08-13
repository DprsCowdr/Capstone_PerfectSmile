<?php
// Direct test of AdminController dental chart functionality
require_once 'vendor/autoload.php';

// Initialize CodeIgniter
$paths = \Config\Paths::create(ROOTPATH, APPPATH, WRITEPATH, SYSTEMPATH, FCPATH);
$bootstrap = \CodeIgniter\Boot::bootServices($paths);
$app = \CodeIgniter\Boot::bootWeb($paths, $bootstrap);

echo "Testing AdminController::getPatientDentalChart directly\n\n";

// Create controller instance
$adminController = new \App\Controllers\AdminController();

// Mock authenticated user
$_SESSION['isLoggedIn'] = true;
$_SESSION['user_id'] = 1;
$_SESSION['user_type'] = 'admin';
$_SESSION['user_name'] = 'Admin User';
$_SESSION['user_email'] = 'admin@perfectsmile.com';

$patientId = 10; // Marc Aron Gamban

try {
    // Call the method directly
    $response = $adminController->getPatientDentalChart($patientId);
    
    // Get the response body
    $responseBody = $response->getBody();
    $data = json_decode($responseBody, true);
    
    if ($data) {
        echo "Success: " . ($data['success'] ? 'true' : 'false') . "\n";
        
        if (isset($data['chart'])) {
            echo "Chart entries: " . count($data['chart']) . "\n";
            foreach ($data['chart'] as $entry) {
                echo "- Tooth {$entry['tooth_number']}: {$entry['condition']} (status: {$entry['status']})\n";
            }
        }
        
        if (isset($data['teeth_data'])) {
            echo "\nTeeth data structure:\n";
            foreach ($data['teeth_data'] as $toothNum => $toothData) {
                echo "Tooth $toothNum: " . count($toothData) . " record(s)\n";
                foreach ($toothData as $record) {
                    echo "  - Condition: {$record['condition']}, Date: {$record['record_date']}\n";
                }
            }
        }
        
        echo "\nFull response:\n";
        echo json_encode($data, JSON_PRETTY_PRINT);
        
    } else {
        echo "Failed to decode JSON response\n";
        echo "Raw response: " . $responseBody . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
