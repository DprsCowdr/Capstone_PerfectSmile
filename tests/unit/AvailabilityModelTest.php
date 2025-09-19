<?php

use CodeIgniter\Test\CIUnitTestCase;

final class AvailabilityModelTest extends CIUnitTestCase
{
    protected $db;
    protected $availabilityModel;
    protected $userModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = \Config\Database::connect();
        $this->db->transStart();

        $this->availabilityModel = new \App\Models\AvailabilityModel();
        // Ensure required tables exist in the test DB; otherwise skip tests
        if (!method_exists($this->db, 'tableExists') || !$this->db->tableExists('user') || !$this->db->tableExists('availability')) {
            $this->markTestSkipped('Required tables (user, availability) are not present in test DB. Run migrations.');
        }
        $this->userModel = new \App\Models\UserModel();
    }

    protected function tearDown(): void
    {
        $this->db->transRollback();
        parent::tearDown();
    }

    public function test_createBlock_throws_on_missing_fields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->availabilityModel->createBlock([]);
    }

    public function test_create_and_query_block_and_isBlocked()
    {
        // Create a dentist user
        $userData = [
            'name' => 'Test Dentist',
            'email' => 'dentist+' . time() . '@example.test',
            'password' => password_hash('secret', PASSWORD_DEFAULT),
            'user_type' => 'dentist',
            'phone' => '0000000000',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->table('user')->insert($userData);
        $dentistId = $this->db->insertID();
        $this->assertNotEmpty($dentistId);

        $start = date('Y-m-d H:i:00', strtotime('+1 day 09:00'));
        $end = date('Y-m-d H:i:00', strtotime('+1 day 11:00'));

        $id = $this->availabilityModel->createBlock([
            'user_id' => $dentistId,
            'type' => 'day_off',
            'start_datetime' => $start,
            'end_datetime' => $end,
            'notes' => 'Unit test block',
            'created_by' => $dentistId
        ]);

        $this->assertNotFalse($id, 'Insert should return new id');

        $blocks = $this->availabilityModel->getBlocksBetween(
            date('Y-m-d H:i:00', strtotime('+1 day 00:00')),
            date('Y-m-d H:i:00', strtotime('+1 day 23:59')),
            $dentistId
        );

        $this->assertNotEmpty($blocks, 'Blocks should be returned for the dentist');

        // Check isBlocked for a time inside the block
        $checkTime = date('Y-m-d H:i:00', strtotime('+1 day 10:00'));
        $this->assertTrue($this->availabilityModel->isBlocked($dentistId, $checkTime, 30));
    }
}
