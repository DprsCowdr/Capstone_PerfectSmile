<?php

use CodeIgniter\Test\CIUnitTestCase;

class AvailabilityControllerTest extends CIUnitTestCase
{
    protected $db;
    protected $userModel;
    protected $availabilityModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = \Config\Database::connect();
        $this->db->transStart();

        $this->userModel = new \App\Models\UserModel();
        $this->availabilityModel = new \App\Models\AvailabilityModel();
        if (!method_exists($this->db, 'tableExists') || !$this->db->tableExists('user') || !$this->db->tableExists('availability')) {
            $this->markTestSkipped('Required tables (user, availability) are not present in test DB. Run migrations.');
        }
    }

    protected function tearDown(): void
    {
        $this->db->transRollback();
        parent::tearDown();
    }

    public function test_events_requires_auth()
    {
        // Simulate unauthenticated request by clearing session
        // Clear framework session
        session()->destroy();
        $controller = new \App\Controllers\Availability();
        // Provide the request/response objects to controller initController
        $request = \Config\Services::request();
        $response = \Config\Services::response();
        $logger = \Config\Services::logger();
        $controller->initController($request, $response, $logger);

        // Manually invoke events without setting authenticated session - should return 401
        $result = $controller->events();
        $this->assertEquals(401, $result->getStatusCode());
    }

    public function test_create_and_list_availability()
    {
        // Create a dentist and mark session as logged in
        $userData = [
            'name' => 'Avail Dentist',
            'email' => 'avail+' . time() . '@example.test',
            'password' => password_hash('secret', PASSWORD_DEFAULT),
            'user_type' => 'dentist',
            'phone' => '0000000000',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->table('user')->insert($userData);
        $dentistId = $this->db->insertID();
        $this->assertNotEmpty($dentistId);

        // Fake authentication by populating session used by Auth::getCurrentUser
        // Fake authentication by setting session values used by Auth helper
        session()->set('isLoggedIn', true);
        session()->set('user_id', $dentistId);

        // Create a block via model directly
        $start = date('Y-m-d H:i:00', strtotime('+2 days 08:00'));
        $end = date('Y-m-d H:i:00', strtotime('+2 days 12:00'));
        $this->availabilityModel->createBlock([
            'user_id' => $dentistId,
            'type' => 'day_off',
            'start_datetime' => $start,
            'end_datetime' => $end,
            'notes' => 'Integration test block',
            'created_by' => $dentistId
        ]);

    // Call listForUser controller to retrieve availability
    $controller = new \App\Controllers\Availability();
    // initialize controller with request/response to avoid null responses
    $request = \Config\Services::request();
    $response = \Config\Services::response();
    $logger = \Config\Services::logger();
    $controller->initController($request, $response, $logger);
    $result = $controller->listForUser();

        $this->assertEquals(200, $result->getStatusCode());

        $body = json_decode((string)$result->getBody(), true);
        $this->assertTrue($body['success']);
        $this->assertNotEmpty($body['availability']);
    }
}
