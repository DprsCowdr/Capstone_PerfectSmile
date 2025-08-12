<?php
require_once 'vendor/autoload.php';
require_once 'app/Config/Paths.php';

$paths = new Config\Paths();
$bootstrap = rtrim($paths->systemDirectory, '\\/ ') . '/bootstrap.php';
$app = require realpath($bootstrap) ?: $bootstrap;

try {
    $model = new \App\Models\PatientModel();
    echo "PatientModel loaded successfully\n";
    
    // Test a simple find operation
    $result = $model->find(1);
    echo "Find operation completed\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
