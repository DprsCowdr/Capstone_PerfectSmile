<?php

namespace Tests\Integration;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class AvailabilitySmokeTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Test ad-hoc availability block creation endpoint
     */
    public function testAdHocAvailabilityCreation()
    {
        // Mock authenticated dentist user
        $session = session();
        $session->set('user', [
            'id' => 26,
            'user_type' => 'dentist',
            'name' => 'Test Dentist'
        ]);

        // Test data for ad-hoc block
        $postData = [
            'user_id' => 26,
            'type' => 'day_off',
            'start' => '2025-09-17 09:00:00',
            'end' => '2025-09-17 17:00:00',
            'notes' => 'Smoke test ad-hoc block',
            csrf_token() => csrf_hash()
        ];

        // POST to dentist availability create endpoint
        $result = $this->withSession(['user' => ['id' => 26, 'user_type' => 'dentist', 'name' => 'Test Dentist']])
                       ->post('/dentist/availability/create', $postData);

        // Verify response
        $result->assertStatus(200);
        $response = json_decode($result->getJSON(), true);
        
        $this->assertTrue($response['success'], 'Ad-hoc creation should succeed');
        $this->assertArrayHasKey('id', $response, 'Response should contain created block ID');

        // Verify database insertion
        $db = \Config\Database::connect();
        $query = $db->table('availability')
                   ->where('user_id', 26)
                   ->where('type', 'day_off')
                   ->where('is_recurring', 0)
                   ->where('start_datetime', '2025-09-17 09:00:00')
                   ->get();
        
        $this->assertEquals(1, $query->getNumRows(), 'Ad-hoc block should be inserted into database');
        
        $row = $query->getRowArray();
        $this->assertEquals('Smoke test ad-hoc block', $row['notes']);
        $this->assertEquals('2025-09-17 17:00:00', $row['end_datetime']);

        echo "\n✓ Ad-hoc availability block creation test passed\n";
    }

    /**
     * Test recurring availability creation endpoint
     */
    public function testRecurringAvailabilityCreation()
    {
        // Mock authenticated dentist user
        $session = session();
        $session->set('user', [
            'id' => 26,
            'user_type' => 'dentist',
            'name' => 'Test Dentist'
        ]);

        // Test data for recurring availability
        $postData = [
            'user_id' => 26,
            'day_of_week' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'notes' => 'Smoke test recurring hours',
            csrf_token() => csrf_hash()
        ];

        // POST to dentist recurring availability endpoint
        $result = $this->withSession(['user' => ['id' => 26, 'user_type' => 'dentist', 'name' => 'Test Dentist']])
                       ->post('/dentist/availability/createRecurring', $postData);

        // Verify response
        $result->assertStatus(200);
        $response = json_decode($result->getJSON(), true);
        
        $this->assertTrue($response['success'], 'Recurring creation should succeed');

        // Verify database insertion
        $db = \Config\Database::connect();
        $query = $db->table('availability')
                   ->where('user_id', 26)
                   ->where('day_of_week', 'Monday')
                   ->where('is_recurring', 1)
                   ->where('start_time', '08:00')
                   ->get();
        
        $this->assertEquals(1, $query->getNumRows(), 'Recurring block should be inserted into database');
        
        $row = $query->getRowArray();
        $this->assertEquals('Smoke test recurring hours', $row['notes']);
        $this->assertEquals('17:00', $row['end_time']);
        $this->assertEquals('recurring', $row['type']);

        echo "\n✓ Recurring availability creation test passed\n";
    }

    /**
     * Test availability events endpoint returns both ad-hoc and expanded recurring
     */
    public function testAvailabilityEventsEndpoint()
    {
        // First create test data
        $db = \Config\Database::connect();
        
        // Insert ad-hoc block
        $db->table('availability')->insert([
            'user_id' => 26,
            'type' => 'day_off',
            'start_datetime' => '2025-09-18 10:00:00',
            'end_datetime' => '2025-09-18 15:00:00',
            'is_recurring' => 0,
            'notes' => 'Test ad-hoc',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Insert recurring block (Wednesdays)
        $db->table('availability')->insert([
            'user_id' => 26,
            'type' => 'recurring',
            'day_of_week' => 'Wednesday',
            'start_time' => '09:00',
            'end_time' => '16:00',
            'is_recurring' => 1,
            'notes' => 'Test recurring',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Test events endpoint
        $result = $this->withSession(['user' => ['id' => 26, 'user_type' => 'dentist']])
                       ->get('/calendar/availability-events?start=2025-09-15&end=2025-09-21&dentist_id=26');

        $result->assertStatus(200);
        $response = json_decode($result->getJSON(), true);
        
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('events', $response);
        
        $events = $response['events'];
        $this->assertGreaterThan(0, count($events), 'Should return availability events');

        // Check for ad-hoc event
        $adHocFound = false;
        $recurringFound = false;

        foreach ($events as $event) {
            if ($event['start'] === '2025-09-18 10:00:00') {
                $adHocFound = true;
                $this->assertEquals('Day Off', $event['title']);
            }
            // Check for Wednesday (2025-09-18 is a Wednesday)
            if (strpos($event['start'], '2025-09-18 09:00') === 0) {
                $recurringFound = true;
                $this->assertEquals('Recurring', $event['title']);
            }
        }

        $this->assertTrue($adHocFound, 'Ad-hoc event should be returned');
        $this->assertTrue($recurringFound, 'Recurring event should be expanded and returned');

        echo "\n✓ Availability events endpoint test passed\n";
    }

    /**
     * Test database schema and availability table structure
     */
    public function testAvailabilityTableSchema()
    {
        $db = \Config\Database::connect();
        
        // Check if availability table exists
        $this->assertTrue($db->tableExists('availability'), 'Availability table should exist');

        // Check required columns
        $fields = $db->getFieldData('availability');
        $fieldNames = array_column($fields, 'name');

        $requiredFields = [
            'id', 'user_id', 'type', 'start_datetime', 'end_datetime',
            'is_recurring', 'day_of_week', 'start_time', 'end_time',
            'notes', 'created_by', 'created_at', 'updated_at'
        ];

        foreach ($requiredFields as $field) {
            $this->assertContains($field, $fieldNames, "Field '$field' should exist in availability table");
        }

        echo "\n✓ Availability table schema test passed\n";
    }

    /**
     * Debug method to show current availability data
     */
    public function testShowCurrentAvailabilityData()
    {
        $db = \Config\Database::connect();
        $query = $db->table('availability')
                   ->orderBy('created_at', 'DESC')
                   ->limit(10)
                   ->get();

        $rows = $query->getResultArray();
        
        echo "\n=== Current Availability Table Data (Last 10 rows) ===\n";
        if (empty($rows)) {
            echo "No availability records found in database.\n";
        } else {
            foreach ($rows as $row) {
                echo sprintf(
                    "ID: %s | User: %s | Type: %s | Recurring: %s | Start: %s | End: %s | Day: %s | Notes: %s\n",
                    $row['id'],
                    $row['user_id'],
                    $row['type'] ?? 'NULL',
                    $row['is_recurring'] ? 'YES' : 'NO',
                    $row['start_datetime'] ?? $row['start_time'] ?? 'NULL',
                    $row['end_datetime'] ?? $row['end_time'] ?? 'NULL',
                    $row['day_of_week'] ?? 'NULL',
                    $row['notes'] ?? 'NULL'
                );
            }
        }
        echo "=== End Availability Data ===\n";

        // Always pass this test since it's just for debugging
        $this->assertTrue(true);
    }
}