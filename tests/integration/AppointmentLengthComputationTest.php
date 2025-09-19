<?php

use CodeIgniter\Test\CIUnitTestCase;

class AppointmentLengthComputationTest extends CIUnitTestCase
{
    protected $db;
    protected $appointmentModel;
    protected $serviceModel;
    protected $branchModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = \Config\Database::connect();
        $this->db->transStart();

        $this->appointmentModel = new \App\Models\AppointmentModel();
        $this->serviceModel = new \App\Models\ServiceModel();
        $this->branchModel = new \App\Models\BranchModel();

        // Ensure minimal tables exist for in-memory test DB (SQLite)
        try {
            // Respect DB prefix (some CI test DB configs add a prefix like 'db_')
            $prefix = method_exists($this->db, 'getPrefix') ? $this->db->getPrefix() : (property_exists($this->db, 'DBPrefix') ? $this->db->DBPrefix : '');
            // user
            $this->db->query("CREATE TABLE IF NOT EXISTS {$prefix}user (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT, password TEXT, user_type TEXT, status TEXT, created_at TEXT);");
            // branches (include timestamps to satisfy model's useTimestamps)
            $this->db->query("CREATE TABLE IF NOT EXISTS {$prefix}branches (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, operating_hours TEXT, created_at TEXT, updated_at TEXT);");
            // services
            $this->db->query("CREATE TABLE IF NOT EXISTS {$prefix}services (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, duration_minutes INTEGER, duration_max_minutes INTEGER);");
            // appointments
            $this->db->query("CREATE TABLE IF NOT EXISTS {$prefix}appointments (id INTEGER PRIMARY KEY AUTOINCREMENT, branch_id INTEGER, dentist_id INTEGER, user_id INTEGER, appointment_datetime TEXT, procedure_duration INTEGER, status TEXT, approval_status TEXT, created_at TEXT, updated_at TEXT);");
            // appointment_service (link)
            $this->db->query("CREATE TABLE IF NOT EXISTS {$prefix}appointment_service (appointment_id INTEGER, service_id INTEGER);");
        } catch (\Exception $e) {
            $this->markTestSkipped('Could not create minimal test tables: ' . $e->getMessage());
        }

        // set default grace file to known value for deterministic tests
        $this->gpPath = WRITEPATH . 'grace_periods.json';
        $this->gpBackup = null;
        if (is_file($this->gpPath)) {
            $this->gpBackup = file_get_contents($this->gpPath);
        }
        file_put_contents($this->gpPath, json_encode(['default' => 20]));
    }

    protected function tearDown(): void
    {
        if ($this->gpBackup !== null) {
            file_put_contents($this->gpPath, $this->gpBackup);
        } else {
            if (is_file($this->gpPath)) @unlink($this->gpPath);
        }
        $this->db->transRollback();
        parent::tearDown();
    }

    public function test_patient_booking_computes_length_from_services()
    {
        // Create branch
        $oh = ['monday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00']];
        $branchId = $this->branchModel->insert(['name' => 'Len Branch', 'operating_hours' => json_encode($oh)]);
        $this->assertNotFalse($branchId);

        // Create service with duration_minutes=60 and duration_max_minutes=90
    $svcId = $this->serviceModel->insert(['name' => 'Len Service', 'price' => 100, 'duration_minutes' => 60, 'duration_max_minutes' => 90]);
        $this->assertNotFalse($svcId);

        // Create patient user
        $uTbl = $this->db->table('user');
        $uTbl->insert(['name' => 'Patient Test', 'email' => 'ptest+' . time() . '@test', 'password' => password_hash('secret', PASSWORD_DEFAULT), 'user_type' => 'patient', 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')]);
        $patientId = $this->db->insertID();

        // Fake session as patient
        session()->set('isLoggedIn', true);
        session()->set('user_id', $patientId);
        session()->set('user_type', 'patient');

        $svc = new \App\Services\AppointmentService();
        $date = date('Y-m-d', strtotime('+2 day'));
        $data = [
            'appointment_date' => $date,
            'appointment_time' => '11:00',
            'branch_id' => $branchId,
            'service_id' => $svcId,
            'origin' => 'patient'
        ];

        $res = $svc->createAppointment($data);
        $this->assertTrue(!empty($res['success']) && $res['success'] === true, 'Expected patient booking to succeed');
        // expected appointment length = duration_max_minutes (90) + grace (20) = 110
        $this->assertArrayHasKey('appointment_length_minutes', $res);
        $this->assertEquals(110, (int)$res['appointment_length_minutes']);
    }

    public function test_staff_booking_allows_explicit_duration()
    {
        // Create branch
        $oh = ['monday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00']];
        $branchId = $this->branchModel->insert(['name' => 'Staff Branch', 'operating_hours' => json_encode($oh)]);
        $this->assertNotFalse($branchId);

        // Create staff user
        $uTbl = $this->db->table('user');
        $uTbl->insert(['name' => 'Staff Test', 'email' => 'staff+' . time() . '@test', 'password' => password_hash('secret', PASSWORD_DEFAULT), 'user_type' => 'staff', 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')]);
        $staffId = $this->db->insertID();

        session()->set('isLoggedIn', true);
        session()->set('user_id', $staffId);
        session()->set('user_type', 'staff');

        $svc = new \App\Services\AppointmentService();
        $date = date('Y-m-d', strtotime('+3 day'));
        $data = [
            'appointment_date' => $date,
            'appointment_time' => '14:00',
            'branch_id' => $branchId,
            // staff may provide explicit duration
            'procedure_duration' => 45,
            'origin' => 'staff'
        ];

        $res = $svc->createAppointment($data);
        $this->assertTrue(!empty($res['success']) && $res['success'] === true, 'Expected staff booking to succeed');
        // expected appointment length = explicit 45 + grace 20 = 65
        $this->assertArrayHasKey('appointment_length_minutes', $res);
        $this->assertEquals(65, (int)$res['appointment_length_minutes']);
    }

    public function test_admin_booking_prefers_service_over_explicit_duration()
    {
        // Branch
        $oh = ['monday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00']];
        $branchId = $this->branchModel->insert(['name' => 'Admin Branch', 'operating_hours' => json_encode($oh)]);
        $this->assertNotFalse($branchId);

        // Service (duration_minutes 30, duration_max_minutes 40)
    $svcId = $this->serviceModel->insert(['name' => 'Admin Service', 'price' => 100, 'duration_minutes' => 30, 'duration_max_minutes' => 40]);
        $this->assertNotFalse($svcId);

        // Admin user
        $uTbl = $this->db->table('user');
        $uTbl->insert(['name' => 'Admin Test', 'email' => 'admin+' . time() . '@test', 'password' => password_hash('secret', PASSWORD_DEFAULT), 'user_type' => 'admin', 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')]);
        $adminId = $this->db->insertID();

        session()->set('isLoggedIn', true);
        session()->set('user_id', $adminId);
        session()->set('user_type', 'admin');

        $svc = new \App\Services\AppointmentService();
        $date = date('Y-m-d', strtotime('+4 day'));
        $data = [
            'appointment_date' => $date,
            'appointment_time' => '09:00',
            'branch_id' => $branchId,
            'service_id' => $svcId,
            // admin tries to override with small explicit duration but should be ignored
            'procedure_duration' => 10,
            'origin' => 'admin'
        ];

        $res = $svc->createAppointment($data);
        $this->assertTrue(!empty($res['success']) && $res['success'] === true, 'Expected admin booking to succeed');
        // expected appointment length = duration_max_minutes (40) + grace 20 = 60
        $this->assertArrayHasKey('appointment_length_minutes', $res);
        $this->assertEquals(60, (int)$res['appointment_length_minutes']);

        // ensure record persisted computed procedure_duration equals service total (40)
        if (!empty($res['record']) && isset($res['record']['procedure_duration'])) {
            $this->assertEquals(40, (int)$res['record']['procedure_duration']);
        }
    }
}
