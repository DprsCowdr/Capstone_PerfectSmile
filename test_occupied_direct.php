<?php
// Test the AppointmentModel getOccupiedIntervals method directly

// Load CodeIgniter
putenv('CI_ENVIRONMENT=development');
define('FCPATH', 'c:\Users\John bert\OneDrive\Documents\GitHub\Capstone_PerfectSmile' . DIRECTORY_SEPARATOR);
require_once FCPATH . 'vendor/autoload.php';

$app = \Config\Services::codeigniter();
$app->initialize();

$model = new \App\Models\AppointmentModel();

$date = '2025-09-23';

echo "Testing getOccupiedIntervals directly:\n";

echo "\n1. No filters (branch_id=null, dentist_id=null):\n";
$intervals1 = $model->getOccupiedIntervals($date, null, null);
echo "Found " . count($intervals1) . " intervals:\n";
foreach ($intervals1 as $int) {
    echo "  Start: " . date('Y-m-d H:i:s', $int[0]) . " End: " . date('Y-m-d H:i:s', $int[1]) . " Apt: {$int[2]} User: {$int[3]}\n";
}

echo "\n2. Branch filter (branch_id=2):\n";
$intervals2 = $model->getOccupiedIntervals($date, 2, null);
echo "Found " . count($intervals2) . " intervals:\n";
foreach ($intervals2 as $int) {
    echo "  Start: " . date('Y-m-d H:i:s', $int[0]) . " End: " . date('Y-m-d H:i:s', $int[1]) . " Apt: {$int[2]} User: {$int[3]}\n";
}

echo "\n3. Dentist filter (dentist_id=30):\n";
$intervals3 = $model->getOccupiedIntervals($date, null, 30);
echo "Found " . count($intervals3) . " intervals:\n";
foreach ($intervals3 as $int) {
    echo "  Start: " . date('Y-m-d H:i:s', $int[0]) . " End: " . date('Y-m-d H:i:s', $int[1]) . " Apt: {$int[2]} User: {$int[3]}\n";
}

echo "\n4. Both filters (branch_id=2, dentist_id=30):\n";
$intervals4 = $model->getOccupiedIntervals($date, 2, 30);
echo "Found " . count($intervals4) . " intervals:\n";
foreach ($intervals4 as $int) {
    echo "  Start: " . date('Y-m-d H:i:s', $int[0]) . " End: " . date('Y-m-d H:i:s', $int[1]) . " Apt: {$int[2]} User: {$int[3]}\n";
}
?>