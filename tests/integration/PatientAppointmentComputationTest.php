<?php

use CodeIgniter\Test\CIUnitTestCase;

class PatientAppointmentComputationTest extends CIUnitTestCase
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

		// ensure required tables exist
		if (!method_exists($this->db, 'tableExists') || !$this->db->tableExists('appointments') || !$this->db->tableExists('services') || !$this->db->tableExists('branches')) {
			$this->markTestSkipped('Required tables (appointments, services, branches) are not present in test DB. Run migrations.');
		}

		// Backup existing grace_periods.json if present
		$this->gpPath = WRITEPATH . 'grace_periods.json';
		$this->gpBackup = null;
		if (is_file($this->gpPath)) {
			$this->gpBackup = file_get_contents($this->gpPath);
		}
	}

	protected function tearDown(): void
	{
		// Restore grace_periods.json
		if ($this->gpBackup !== null) {
			file_put_contents($this->gpPath, $this->gpBackup);
		} else {
			if (is_file($this->gpPath)) @unlink($this->gpPath);
		}

		$this->db->transRollback();
		parent::tearDown();
	}

	public function test_conflict_detection_uses_service_max_and_grace()
	{
		// Arrange: create a branch with operating hours covering 08:00-20:00
		$oh = [
			'monday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00'],
			'tuesday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00'],
			'wednesday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00'],
			'thursday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00'],
			'friday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00'],
			'saturday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00'],
			'sunday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00'],
		];
		$branchId = $this->branchModel->insert(['name' => 'Test Branch', 'operating_hours' => json_encode($oh)]);
		$this->assertNotFalse($branchId);

		// Create a service: duration_minutes = 60, duration_max_minutes = 90
		$svcId = $this->serviceModel->insert(['name' => 'Test Service', 'duration_minutes' => 60, 'duration_max_minutes' => 90]);
		$this->assertNotFalse($svcId);

		// Create two patients
		$uTbl = $this->db->table('user');
		$uTbl->insert(['name' => 'Patient One', 'email' => 'p1+' . time() . '@test', 'password' => password_hash('secret', PASSWORD_DEFAULT), 'user_type' => 'patient', 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')]);
		$otherId = $this->db->insertID();
		$uTbl->insert(['name' => 'Patient Two', 'email' => 'p2+' . time() . '@test', 'password' => password_hash('secret', PASSWORD_DEFAULT), 'user_type' => 'patient', 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')]);
		$patientId = $this->db->insertID();

		// Set grace period file to default 20 minutes
		file_put_contents($this->gpPath, json_encode(['default' => 20]));

		// Create existing appointment for other patient at 10:00 tomorrow linked to the service
		$date = date('Y-m-d', strtotime('+1 day'));
		$existingTime = '10:00';
		$appointmentDatetime = $date . ' ' . $existingTime . ':00';
		$aid = $this->appointmentModel->insert(['user_id' => $otherId, 'branch_id' => $branchId, 'appointment_datetime' => $appointmentDatetime, 'status' => 'confirmed', 'approval_status' => 'approved']);
		$this->assertNotFalse($aid);
		// link service
		$this->db->table('appointment_service')->insert(['appointment_id' => $aid, 'service_id' => $svcId]);

		// Fake authentication (patient trying to book)
		session()->set('isLoggedIn', true);
		session()->set('user_id', $patientId);
		session()->set('user_type', 'patient');

		// Simulate POST to checkConflicts for requested time 10:30 with same service
		$_POST['date'] = $date;
		$_POST['time'] = '10:30';
		$_POST['service_id'] = $svcId;
		// ensure no client-sent duration/grace override
		unset($_POST['duration']);
		unset($_POST['grace_minutes']);

		$controller = new \App\Controllers\Appointments();
		$request = \Config\Services::request();
		$response = \Config\Services::response();
		$logger = \Config\Services::logger();
		$controller->initController($request, $response, $logger);

		$result = $controller->checkConflicts();
		$this->assertEquals(200, $result->getStatusCode());
		$body = json_decode((string)$result->getBody(), true);
		$this->assertTrue($body['success']);
		$this->assertTrue($body['hasConflicts'], 'Expected conflict due to existing appointment using service max duration and grace');
		$this->assertNotEmpty($body['conflicts']);

		// Verify that overlap reported corresponds to existing appointment id
		$found = false;
		foreach ($body['conflicts'] as $c) {
			if ($c['id'] == $aid) { $found = true; break; }
		}
		$this->assertTrue($found, 'Existing appointment should appear in conflict list');
	}

	public function test_available_slots_respect_branch_operating_hours()
	{
		// Arrange: create branch with specific operating hour for tomorrow
		$weekday = strtolower(date('l', strtotime('+1 day')));
		$oh = [$weekday => ['enabled' => true, 'open' => '09:00', 'close' => '17:00']];
		$branchId = $this->branchModel->insert(['name' => 'OH Branch', 'operating_hours' => json_encode($oh)]);
		$this->assertNotFalse($branchId);

		// Create a service with short duration to allow slots
		$svcId = $this->serviceModel->insert(['name' => 'Short Service', 'duration_minutes' => 15]);
		$this->assertNotFalse($svcId);

		// Fake authenticated patient
		$uTbl = $this->db->table('user');
		$uTbl->insert(['name' => 'Slot Patient', 'email' => 'slot+' . time() . '@test', 'password' => password_hash('secret', PASSWORD_DEFAULT), 'user_type' => 'patient', 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')]);
		$patientId = $this->db->insertID();
		session()->set('isLoggedIn', true);
		session()->set('user_id', $patientId);
		session()->set('user_type', 'patient');

		$_POST = [];
		$date = date('Y-m-d', strtotime('+1 day'));
		$_POST['date'] = $date;
		$_POST['branch_id'] = $branchId;
		$_POST['service_id'] = $svcId;

		$controller = new \App\Controllers\Appointments();
		$request = \Config\Services::request();
		$response = \Config\Services::response();
		$logger = \Config\Services::logger();
		$controller->initController($request, $response, $logger);

		$result = $controller->availableSlots();
		$this->assertEquals(200, $result->getStatusCode());
		$body = json_decode((string)$result->getBody(), true);
		$this->assertTrue($body['success']);
		$this->assertNotEmpty($body['slots']);

		// Earliest slot should be no earlier than 9:00 AM (branch open time) and latest slot before 5:00 PM
		$first = $body['slots'][0];
		$firstTime = is_array($first) ? $first['time'] : $first;
		$this->assertStringContainsString('AM', $firstTime);
		$this->assertTrue(strtotime($date . ' 09:00:00') <= strtotime(date('Y-m-d ' . date('H:i:s', strtotime($firstTime)))), 'First slot should be at or after branch open time');
	}
}

