<?php
use CodeIgniter\Config\Services;
use CodeIgniter\I18n\Time;

class BranchOperatingHoursTest extends \CIUnitTestCase
{
    protected $branchModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->branchModel = new \App\Models\BranchModel();

        // Use DB seeding/transaction if available; keep test isolated
        $this->db = \Config\Database::connect();
        $this->db->transStart();
    }

    protected function tearDown(): void
    {
        // rollback DB changes
        $this->db->transRollback();
        parent::tearDown();
    }

    public function test_operating_hours_persist_and_render()
    {
        // Arrange: create branch with operating hours
        $oh = [
            'monday' => ['enabled' => true, 'open' => '08:30', 'close' => '16:30'],
            'tuesday' => ['enabled' => true, 'open' => '09:00', 'close' => '17:00'],
            'wednesday' => ['enabled' => false, 'open' => '09:00', 'close' => '17:00'],
            'thursday' => ['enabled' => true, 'open' => '09:00', 'close' => '17:00'],
            'friday' => ['enabled' => true, 'open' => '09:00', 'close' => '17:00'],
            'saturday' => ['enabled' => false, 'open' => '09:00', 'close' => '12:00'],
            'sunday' => ['enabled' => false, 'open' => '00:00', 'close' => '00:00'],
        ];

        $data = [
            'name' => 'Test Branch OH',
            'address' => '123 Test St',
            'operating_hours' => json_encode($oh),
        ];

        // Act: insert via model
        $id = $this->branchModel->insert($data);
        $this->assertNotFalse($id, 'Insert failed');

        $branch = $this->branchModel->find($id);
        $this->assertNotEmpty($branch);
        $this->assertArrayHasKey('operating_hours', $branch);

        $decoded = json_decode($branch['operating_hours'], true);
        $this->assertIsArray($decoded);
        $this->assertEquals('08:30', $decoded['monday']['open']);

        // Render the show view and ensure formatted time appears
        $controller = new \App\Controllers\BranchController();
        // emulate authenticated admin by stubbing getAuthenticatedUser - but keep simple: call show() which will return Response
        $response = $controller->show($id);
        $output = '';
        if (is_string($response)) {
            $output = $response;
        } elseif (method_exists($response, 'getBody')) {
            $output = (string) $response->getBody();
        }

        // Expect to see formatted time '8:30 AM' in output
        $this->assertStringContainsString('8:30 AM', $output);
    }
}
