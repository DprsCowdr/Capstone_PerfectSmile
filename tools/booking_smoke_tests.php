<?php
/**
 * Booking Logic Smoke Tests
 * 
 * Tests the appointment booking system end-to-end:
 * 1. Available slots API with long duration services
 * 2. Multi-dentist branch availability logic
 * 3. Per-dentist strict blocking
 * 4. Appointment creation and service linking
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap CodeIgniter
$app = \Config\Services::codeigniter();
$app->initialize();

class BookingSmokeTests
{
    private $db;
    private $appointmentService;
    private $appointmentModel;
    private $userModel;
    private $serviceModel;
    private $branchModel;
    
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->appointmentService = new \App\Services\AppointmentService();
        $this->appointmentModel = new \App\Models\AppointmentModel();
        $this->userModel = new \App\Models\UserModel();
        $this->serviceModel = new \App\Models\ServiceModel();
        $this->branchModel = new \App\Models\BranchModel();
    }
    
    public function runAllTests()
    {
        echo "=== Booking Logic Smoke Tests ===\n\n";
        
        try {
            $this->testUserTypeNormalization();
            $this->testAvailableSlotsLongDuration();
            $this->testMultiDentistAvailability();
            $this->testPerDentistBlocking();
            $this->testAppointmentCreation();
            
            echo "\n✅ All tests completed successfully!\n";
        } catch (\Exception $e) {
            echo "\n❌ Test failed: " . $e->getMessage() . "\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
        }
    }
    
    /**
     * Test 1: Verify user_type normalization - all dentist queries should use 'dentist'
     */
    private function testUserTypeNormalization()
    {
        echo "🧪 Test 1: User type normalization...\n";
        
        // Count dentists in system
        $dentistCount = $this->userModel->where('user_type', 'dentist')->countAllResults();
        $doctorCount = $this->userModel->where('user_type', 'doctor')->countAllResults();
        
        echo "   - Dentists (user_type='dentist'): {$dentistCount}\n";
        echo "   - Doctors (user_type='doctor'): {$doctorCount}\n";
        
        // Test branch dentist counting (used by availableSlots)
        $branchId = $this->getTestBranchId();
        if ($branchId) {
            $branchDentistCount = $this->db->table('user')
                ->join('branch_staff', 'branch_staff.user_id = user.id')
                ->where('user.user_type', 'dentist')
                ->where('user.status', 'active')
                ->where('branch_staff.branch_id', $branchId)
                ->countAllResults();
            
            echo "   - Active dentists in branch {$branchId}: {$branchDentistCount}\n";
            
            if ($branchDentistCount === 0) {
                echo "   ⚠️  Warning: No active dentists found in test branch\n";
            }
        }
        
        echo "   ✅ User type normalization check complete\n\n";
    }
    
    /**
     * Test 2: Test available slots with long duration service (should return many candidates)
     */
    private function testAvailableSlotsLongDuration()
    {
        echo "🧪 Test 2: Available slots with long duration service...\n";
        
        $testDate = date('Y-m-d', strtotime('+1 day'));
        $branchId = $this->getTestBranchId();
        $longServiceId = $this->getOrCreateLongDurationService();
        
        if (!$branchId || !$longServiceId) {
            echo "   ⚠️  Skipping test - missing test data\n\n";
            return;
        }
        
        // Simulate POST request to available slots endpoint
        $controller = new \App\Controllers\Appointments();
        
        // Mock request data
        $_POST = [
            'branch_id' => $branchId,
            'date' => $testDate,
            'service_id' => $longServiceId,
            'granularity' => 1  // 1-minute granularity for dense candidates
        ];
        
        // Mock authenticated user
        $this->mockAuthenticatedUser();
        
        ob_start();
        $response = $controller->availableSlots();
        $output = ob_get_clean();
        
        // Parse JSON response
        $jsonData = json_decode($response->getBody(), true);
        
        if ($jsonData && isset($jsonData['available_slots'])) {
            $slotCount = count($jsonData['available_slots']);
            echo "   - Available slots returned: {$slotCount}\n";
            echo "   - Service duration: " . ($jsonData['metadata']['duration_minutes'] ?? 'unknown') . " minutes\n";
            
            if ($slotCount >= 10) {
                echo "   ✅ Good slot density for long duration service\n";
            } else {
                echo "   ⚠️  Low slot count - may indicate sparse candidate generation\n";
            }
        } else {
            echo "   ❌ Invalid response format or error\n";
            echo "   Response: " . json_encode($jsonData, JSON_PRETTY_PRINT) . "\n";
        }
        
        echo "\n";
    }
    
    /**
     * Test 3: Test multi-dentist availability (no dentist_id specified)
     */
    private function testMultiDentistAvailability()
    {
        echo "🧪 Test 3: Multi-dentist branch availability...\n";
        
        $testDate = date('Y-m-d', strtotime('+1 day'));
        $branchId = $this->getTestBranchId();
        $serviceId = $this->getTestServiceId();
        
        if (!$branchId || !$serviceId) {
            echo "   ⚠️  Skipping test - missing test data\n\n";
            return;
        }
        
        // Create a test appointment for one dentist
        $dentists = $this->getTestDentists($branchId);
        if (count($dentists) < 2) {
            echo "   ⚠️  Need at least 2 dentists for this test\n\n";
            return;
        }
        
        $dentist1 = $dentists[0];
        $testTime = '10:00';
        
        // Create appointment for dentist 1
        $appointmentData = [
            'user_id' => $this->getTestPatientId(),
            'branch_id' => $branchId,
            'dentist_id' => $dentist1['id'],
            'appointment_date' => $testDate,
            'appointment_time' => $testTime,
            'service_id' => $serviceId,
            'status' => 'confirmed',
            'approval_status' => 'approved'
        ];
        
        $appointmentId = $this->appointmentModel->insert($appointmentData);
        
        try {
            // Test availability without specifying dentist (should show slots since dentist 2 is free)
            $_POST = [
                'branch_id' => $branchId,
                'date' => $testDate,
                'service_id' => $serviceId,
                'granularity' => 5
            ];
            
            $controller = new \App\Controllers\Appointments();
            $this->mockAuthenticatedUser();
            
            ob_start();
            $response = $controller->availableSlots();
            ob_get_clean();
            
            $jsonData = json_decode($response->getBody(), true);
            
            if ($jsonData && isset($jsonData['available_slots'])) {
                $slotCount = count($jsonData['available_slots']);
                echo "   - Available slots with multi-dentist logic: {$slotCount}\n";
                
                // Check if 10:00 slot is still available (should be, since only 1 dentist busy)
                $tenAmAvailable = false;
                foreach ($jsonData['available_slots'] as $slot) {
                    if (isset($slot['time']) && strpos($slot['time'], '10:00') !== false) {
                        $tenAmAvailable = true;
                        break;
                    }
                }
                
                if ($tenAmAvailable) {
                    echo "   ✅ 10:00 AM slot available despite dentist 1 being busy (multi-dentist logic working)\n";
                } else {
                    echo "   ⚠️  10:00 AM slot not available - may indicate over-restrictive blocking\n";
                }
            }
            
        } finally {
            // Clean up test appointment
            if ($appointmentId) {
                $this->appointmentModel->delete($appointmentId);
            }
        }
        
        echo "\n";
    }
    
    /**
     * Test 4: Test per-dentist strict blocking
     */
    private function testPerDentistBlocking()
    {
        echo "🧪 Test 4: Per-dentist strict blocking...\n";
        
        $testDate = date('Y-m-d', strtotime('+1 day'));
        $branchId = $this->getTestBranchId();
        $serviceId = $this->getTestServiceId();
        $dentists = $this->getTestDentists($branchId);
        
        if (!$branchId || !$serviceId || empty($dentists)) {
            echo "   ⚠️  Skipping test - missing test data\n\n";
            return;
        }
        
        $dentist1 = $dentists[0];
        $testTime = '11:00';
        
        // Create appointment for specific dentist
        $appointmentData = [
            'user_id' => $this->getTestPatientId(),
            'branch_id' => $branchId,
            'dentist_id' => $dentist1['id'],
            'appointment_date' => $testDate,
            'appointment_time' => $testTime,
            'service_id' => $serviceId,
            'status' => 'confirmed',
            'approval_status' => 'approved'
        ];
        
        $appointmentId = $this->appointmentModel->insert($appointmentData);
        
        try {
            // Test availability FOR THE SAME DENTIST (should block 11:00)
            $_POST = [
                'branch_id' => $branchId,
                'date' => $testDate,
                'service_id' => $serviceId,
                'dentist_id' => $dentist1['id'],
                'granularity' => 5
            ];
            
            $controller = new \App\Controllers\Appointments();
            $this->mockAuthenticatedUser();
            
            ob_start();
            $response = $controller->availableSlots();
            ob_get_clean();
            
            $jsonData = json_decode($response->getBody(), true);
            
            if ($jsonData && isset($jsonData['available_slots'])) {
                // Check if 11:00 slot is blocked for this dentist
                $elevenAmBlocked = true;
                foreach ($jsonData['available_slots'] as $slot) {
                    if (isset($slot['time']) && strpos($slot['time'], '11:00') !== false) {
                        $elevenAmBlocked = false;
                        break;
                    }
                }
                
                if ($elevenAmBlocked) {
                    echo "   ✅ 11:00 AM slot correctly blocked for busy dentist\n";
                } else {
                    echo "   ⚠️  11:00 AM slot not blocked - per-dentist blocking may be faulty\n";
                }
            }
            
        } finally {
            // Clean up
            if ($appointmentId) {
                $this->appointmentModel->delete($appointmentId);
            }
        }
        
        echo "\n";
    }
    
    /**
     * Test 5: Test appointment creation and service linking
     */
    private function testAppointmentCreation()
    {
        echo "🧪 Test 5: Appointment creation and service linking...\n";
        
        $branchId = $this->getTestBranchId();
        $serviceId = $this->getTestServiceId();
        $patientId = $this->getTestPatientId();
        
        if (!$branchId || !$serviceId || !$patientId) {
            echo "   ⚠️  Skipping test - missing test data\n\n";
            return;
        }
        
        // Test appointment creation via service
        $appointmentData = [
            'user_id' => $patientId,
            'branch_id' => $branchId,
            'appointment_date' => date('Y-m-d', strtotime('+2 days')),
            'appointment_time' => '14:00',
            'service_id' => $serviceId,
            'created_by_role' => 'admin',
            'approval_status' => 'approved'
        ];
        
        $result = $this->appointmentService->createAppointment($appointmentData);
        
        if ($result['success']) {
            echo "   ✅ Appointment created successfully\n";
            
            $appointmentId = $result['record']['id'] ?? null;
            
            if ($appointmentId) {
                // Check if service was linked
                $linkedServices = $this->db->table('appointment_service')
                    ->where('appointment_id', $appointmentId)
                    ->get()->getResultArray();
                
                echo "   - Linked services: " . count($linkedServices) . "\n";
                
                // Check if procedure_duration was set
                $appointment = $this->appointmentModel->find($appointmentId);
                if ($appointment && !empty($appointment['procedure_duration'])) {
                    echo "   ✅ Procedure duration set: " . $appointment['procedure_duration'] . " minutes\n";
                } else {
                    echo "   ⚠️  Procedure duration not set\n";
                }
                
                // Clean up
                $this->appointmentModel->delete($appointmentId);
            }
        } else {
            echo "   ❌ Appointment creation failed: " . $result['message'] . "\n";
        }
        
        echo "\n";
    }
    
    // Helper methods
    
    private function getTestBranchId()
    {
        $branch = $this->branchModel->first();
        return $branch ? $branch['id'] : null;
    }
    
    private function getTestServiceId()
    {
        $service = $this->serviceModel->first();
        return $service ? $service['id'] : null;
    }
    
    private function getOrCreateLongDurationService()
    {
        // Look for existing long service (90+ minutes)
        $service = $this->serviceModel->where('duration_minutes >=', 90)->first();
        
        if (!$service) {
            // Create test service
            $serviceId = $this->serviceModel->insert([
                'name' => 'Test Long Duration Service',
                'description' => 'Test service for smoke tests',
                'duration_minutes' => 120,
                'duration_max_minutes' => 150,
                'price' => 100.00,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            return $serviceId;
        }
        
        return $service['id'];
    }
    
    private function getTestPatientId()
    {
        $patient = $this->userModel->where('user_type', 'patient')->first();
        
        if (!$patient) {
            // Create test patient
            $patientId = $this->userModel->insert([
                'name' => 'Test Patient',
                'email' => 'testpatient@example.com',
                'user_type' => 'patient',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            return $patientId;
        }
        
        return $patient['id'];
    }
    
    private function getTestDentists($branchId)
    {
        return $this->db->table('user')
            ->select('user.id, user.name')
            ->join('branch_staff', 'branch_staff.user_id = user.id')
            ->where('user.user_type', 'dentist')
            ->where('user.status', 'active')
            ->where('branch_staff.branch_id', $branchId)
            ->get()->getResultArray();
    }
    
    private function mockAuthenticatedUser()
    {
        // Mock session for authentication
        if (!session()->has('user_id')) {
            session()->set([
                'user_id' => 1,
                'user_type' => 'admin',
                'logged_in' => true
            ]);
        }
    }
}

// Run tests if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $tests = new BookingSmokeTests();
    $tests->runAllTests();
}