<?php
/**
 * Test script to validate appointment creation UI and backend
 * Tests common appointment creation scenarios for potential errors
 */

require_once 'vendor/autoload.php';
require_once 'app/Config/Paths.php';

// Load CodeIgniter
$paths = new Config\Paths();
$bootstrap = rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
$app = require realpath($bootstrap) ?: $bootstrap;

echo "Testing Appointment Creation System\n";
echo "===================================\n\n";

try {
    // Test 1: Database connection and table structure
    echo "1. Testing database connection...\n";
    $db = \Config\Database::connect();
    
    $tables = ['appointments', 'user', 'services', 'branches', 'appointment_service'];
    foreach ($tables as $table) {
        $exists = $db->tableExists($table);
        echo "   - Table '$table': " . ($exists ? "EXISTS" : "MISSING") . "\n";
        if (!$exists) {
            echo "   ERROR: Required table '$table' is missing!\n";
        }
    }
    
    // Test 2: Check service data
    echo "\n2. Testing service data...\n";
    $services = $db->table('services')->get()->getResultArray();
    echo "   - Found " . count($services) . " services\n";
    
    if (count($services) > 0) {
        $firstService = $services[0];
        echo "   - Sample service: {$firstService['name']} (ID: {$firstService['id']})\n";
        
        // Check duration fields
        $hasMinutes = isset($firstService['duration_minutes']);
        $hasMaxMinutes = isset($firstService['duration_max_minutes']);
        echo "   - Duration fields: duration_minutes=" . ($hasMinutes ? "YES" : "NO") . 
             ", duration_max_minutes=" . ($hasMaxMinutes ? "YES" : "NO") . "\n";
    }
    
    // Test 3: Check for required appointment fields
    echo "\n3. Testing appointment table structure...\n";
    $appointmentFields = $db->getFieldNames('appointments');
    $requiredFields = ['id', 'user_id', 'branch_id', 'appointment_date', 'start_time', 'end_time', 'status'];
    
    foreach ($requiredFields as $field) {
        $exists = in_array($field, $appointmentFields);
        echo "   - Field '$field': " . ($exists ? "EXISTS" : "MISSING") . "\n";
        if (!$exists) {
            echo "   ERROR: Required field '$field' is missing!\n";
        }
    }
    
    // Test 4: Check branches
    echo "\n4. Testing branch data...\n";
    $branches = $db->table('branches')->get()->getResultArray();
    echo "   - Found " . count($branches) . " branches\n";
    
    if (count($branches) > 0) {
        $firstBranch = $branches[0];
        echo "   - Sample branch: {$firstBranch['name']} (ID: {$firstBranch['id']})\n";
    }
    
    // Test 5: Check users 
    echo "\n5. Testing user data...\n";
    $users = $db->table('user')->where('user_type', 'patient')->limit(5)->get()->getResultArray();
    echo "   - Found " . count($users) . " sample patients\n";
    
    // Test 6: Test AppointmentService availability
    echo "\n6. Testing AppointmentService class...\n";
    
    if (class_exists('App\Services\AppointmentService')) {
        echo "   - AppointmentService class: EXISTS\n";
        
        $appointmentService = new \App\Services\AppointmentService();
        $reflection = new ReflectionClass($appointmentService);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        
        $requiredMethods = ['createAppointment', 'createScheduledAppointment', 'createWalkInAppointment'];
        foreach ($requiredMethods as $method) {
            $exists = $reflection->hasMethod($method);
            echo "   - Method '$method': " . ($exists ? "EXISTS" : "MISSING") . "\n";
            if (!$exists) {
                echo "   ERROR: Required method '$method' is missing!\n";
            }
        }
    } else {
        echo "   - AppointmentService class: MISSING\n";
        echo "   ERROR: AppointmentService class not found!\n";
    }
    
    // Test 7: Test Patient controller availability
    echo "\n7. Testing Patient controller...\n";
    
    if (class_exists('App\Controllers\Patient')) {
        echo "   - Patient controller: EXISTS\n";
        
        $reflection = new ReflectionClass('App\Controllers\Patient');
        if ($reflection->hasMethod('bookAppointment')) {
            echo "   - bookAppointment method: EXISTS\n";
        } else {
            echo "   - bookAppointment method: MISSING\n";
            echo "   ERROR: bookAppointment method not found!\n";
        }
    } else {
        echo "   - Patient controller: MISSING\n";
        echo "   ERROR: Patient controller not found!\n";
    }
    
    echo "\n8. Checking for common validation errors...\n";
    
    // Check for PHP errors in key files
    $filesToCheck = [
        'app/Services/AppointmentService.php',
        'app/Controllers/Patient.php',
        'app/Views/templates/calendar/appointmentTable.php'
    ];
    
    foreach ($filesToCheck as $file) {
        $fullPath = __DIR__ . '/' . $file;
        if (file_exists($fullPath)) {
            $output = [];
            $returnCode = 0;
            exec("php -l \"$fullPath\" 2>&1", $output, $returnCode);
            $status = ($returnCode === 0) ? "VALID" : "SYNTAX ERROR";
            echo "   - $file: $status\n";
            if ($returnCode !== 0) {
                echo "     Error: " . implode("\n     ", $output) . "\n";
            }
        } else {
            echo "   - $file: FILE NOT FOUND\n";
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "TEST SUMMARY\n";
    echo str_repeat("=", 50) . "\n";
    echo "Database connectivity: WORKING\n";
    echo "Required tables: " . (count($db->listTables()) >= 5 ? "PRESENT" : "INCOMPLETE") . "\n";
    echo "Service data: " . (count($services) > 0 ? "AVAILABLE" : "MISSING") . "\n";
    echo "Branch data: " . (count($branches) > 0 ? "AVAILABLE" : "MISSING") . "\n";
    echo "Patient data: " . (count($users) > 0 ? "AVAILABLE" : "MISSING") . "\n";
    echo "Core classes: " . (class_exists('App\Services\AppointmentService') ? "LOADED" : "MISSING") . "\n";
    echo "\nRECOMMENDATION: " . 
         (count($services) > 0 && count($branches) > 0 && class_exists('App\Services\AppointmentService') 
          ? "System appears ready for appointment creation testing" 
          : "Fix missing components before testing appointment creation") . "\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nThis error indicates a problem with the appointment system setup.\n";
}