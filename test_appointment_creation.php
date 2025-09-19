<?php
// Test appointment creation for errors
require_once __DIR__ . '/vendor/autoload.php';

// Load CodeIgniter environment
defined('APPPATH') || define('APPPATH', realpath(__DIR__ . '/app/') . DIRECTORY_SEPARATOR);
defined('ROOTPATH') || define('ROOTPATH', realpath(__DIR__ . '/') . DIRECTORY_SEPARATOR);
defined('FCPATH') || define('FCPATH', realpath(__DIR__ . '/public/') . DIRECTORY_SEPARATOR);
defined('SYSTEMPATH') || define('SYSTEMPATH', realpath(__DIR__ . '/vendor/codeigniter4/framework/system/') . DIRECTORY_SEPARATOR);
defined('WRITEPATH') || define('WRITEPATH', realpath(__DIR__ . '/writable/') . DIRECTORY_SEPARATOR);

// Load Constants
require_once APPPATH . 'Config/Constants.php';

echo "=== Testing Appointment Creation ===\n\n";

try {
    // Initialize database connection
    $db = \Config\Database::connect();
    echo "✓ Database connection successful\n";
    
    // Test 1: Initialize AppointmentService
    echo "\n1. Testing AppointmentService initialization...\n";
    $appointmentService = new \App\Services\AppointmentService();
    echo "✓ AppointmentService initialized successfully\n";
    
    // Test 2: Check required tables exist
    echo "\n2. Checking required tables...\n";
    $requiredTables = ['appointments', 'user', 'services', 'branches'];
    foreach ($requiredTables as $table) {
        if ($db->tableExists($table)) {
            echo "✓ Table '$table' exists\n";
        } else {
            echo "❌ Table '$table' MISSING\n";
        }
    }
    
    // Test 3: Check for test users
    echo "\n3. Checking for test users...\n";
    $patients = $db->table('user')->where('user_type', 'patient')->get(5)->getResultArray();
    if (count($patients) > 0) {
        echo "✓ Found " . count($patients) . " patient users\n";
        $testPatientId = $patients[0]['id'];
        echo "  Using patient ID: $testPatientId (Name: {$patients[0]['name']})\n";
    } else {
        echo "❌ No patient users found - creating test patient...\n";
        $testPatientId = $db->table('user')->insert([
            'name' => 'Test Patient',
            'email' => 'test.patient.' . time() . '@example.com',
            'user_type' => 'patient',
            'status' => 'active',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        if ($testPatientId) {
            echo "✓ Created test patient with ID: $testPatientId\n";
        } else {
            echo "❌ Failed to create test patient\n";
            exit(1);
        }
    }
    
    // Test 4: Check for services
    echo "\n4. Checking for services...\n";
    $services = $db->table('services')->get(3)->getResultArray();
    if (count($services) > 0) {
        echo "✓ Found " . count($services) . " services\n";
        foreach ($services as $service) {
            echo "  Service ID {$service['id']}: {$service['name']} (Duration: {$service['duration_minutes']}min, Max: {$service['duration_max_minutes']}min)\n";
        }
        $testServiceId = $services[0]['id'];
    } else {
        echo "❌ No services found - creating test service...\n";
        $testServiceId = $db->table('services')->insert([
            'name' => 'Test Service',
            'duration_minutes' => 30,
            'duration_max_minutes' => 45,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        if ($testServiceId) {
            echo "✓ Created test service with ID: $testServiceId\n";
        } else {
            echo "❌ Failed to create test service\n";
            $testServiceId = null;
        }
    }
    
    // Test 5: Check for branches
    echo "\n5. Checking for branches...\n";
    $branches = $db->table('branches')->get(3)->getResultArray();
    if (count($branches) > 0) {
        echo "✓ Found " . count($branches) . " branches\n";
        foreach ($branches as $branch) {
            echo "  Branch ID {$branch['id']}: {$branch['name']}\n";
        }
        $testBranchId = $branches[0]['id'];
    } else {
        echo "❌ No branches found - creating test branch...\n";
        $testBranchId = $db->table('branches')->insert([
            'name' => 'Test Branch',
            'operating_hours' => json_encode([
                'monday' => ['enabled' => true, 'open' => '08:00', 'close' => '17:00']
            ]),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        if ($testBranchId) {
            echo "✓ Created test branch with ID: $testBranchId\n";
        } else {
            echo "❌ Failed to create test branch\n";
            exit(1);
        }
    }
    
    // Test 6: Try creating an appointment
    echo "\n6. Testing appointment creation...\n";
    
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $appointmentData = [
        'user_id' => $testPatientId,
        'appointment_date' => $tomorrow,
        'appointment_time' => '10:00',
        'appointment_type' => 'scheduled',
        'branch_id' => $testBranchId,
        'service_id' => $testServiceId,
        'created_by_role' => 'patient'
    ];
    
    echo "Creating appointment with data:\n";
    echo "  Patient ID: $testPatientId\n";
    echo "  Date: $tomorrow\n";
    echo "  Time: 10:00\n";
    echo "  Branch ID: $testBranchId\n";
    echo "  Service ID: $testServiceId\n";
    
    $result = $appointmentService->createAppointment($appointmentData);
    
    echo "\nAppointment creation result:\n";
    if ($result['success']) {
        echo "✓ SUCCESS: " . $result['message'] . "\n";
        if (isset($result['record']['id'])) {
            echo "  Appointment ID: " . $result['record']['id'] . "\n";
            echo "  Status: " . $result['record']['status'] . "\n";
            echo "  Approval Status: " . $result['record']['approval_status'] . "\n";
            
            // Check if appointment was actually saved
            $savedAppt = $db->table('appointments')->where('id', $result['record']['id'])->get()->getRowArray();
            if ($savedAppt) {
                echo "✓ Appointment successfully saved to database\n";
                echo "  Saved datetime: " . $savedAppt['appointment_datetime'] . "\n";
                echo "  Procedure duration: " . ($savedAppt['procedure_duration'] ?? 'NULL') . " minutes\n";
            } else {
                echo "❌ Appointment not found in database\n";
            }
        }
    } else {
        echo "❌ FAILED: " . $result['message'] . "\n";
        echo "Full result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    }
    
    // Test 7: Test conflict detection
    echo "\n7. Testing conflict detection...\n";
    
    $conflictData = [
        'user_id' => $testPatientId,
        'appointment_date' => $tomorrow,
        'appointment_time' => '10:15', // 15 minutes after first appointment
        'appointment_type' => 'scheduled',
        'branch_id' => $testBranchId,
        'service_id' => $testServiceId,
        'created_by_role' => 'patient'
    ];
    
    echo "Attempting to create conflicting appointment at 10:15...\n";
    $conflictResult = $appointmentService->createAppointment($conflictData);
    
    if ($conflictResult['success']) {
        echo "✓ Appointment created (may have been rescheduled): " . $conflictResult['message'] . "\n";
        if (isset($conflictResult['record']['appointment_datetime'])) {
            echo "  Final datetime: " . $conflictResult['record']['appointment_datetime'] . "\n";
        }
    } else {
        echo "❌ FAILED: " . $conflictResult['message'] . "\n";
    }
    
    // Test 8: Check grace periods configuration
    echo "\n8. Checking grace periods configuration...\n";
    $gracePath = WRITEPATH . 'grace_periods.json';
    if (file_exists($gracePath)) {
        $graceConfig = json_decode(file_get_contents($gracePath), true);
        echo "✓ Grace periods file exists\n";
        echo "  Config: " . json_encode($graceConfig) . "\n";
    } else {
        echo "❌ Grace periods file missing - creating default...\n";
        file_put_contents($gracePath, json_encode(['default' => 20]));
        echo "✓ Created default grace periods file\n";
    }
    
    echo "\n=== Test Complete ===\n";
    echo "All appointment creation tests completed!\n";
    
} catch (\Exception $e) {
    echo "\n❌ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}