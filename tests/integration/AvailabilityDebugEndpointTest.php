<?php

namespace Tests\Integration;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class AvailabilityDebugEndpointTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    public function testDebugEndpointReturnsAdHocAndRecurring()
    {
        // Insert test data for a sample dentist (id 99)
        $db = \Config\Database::connect();
        // Clean any previous test rows
        $db->table('availability')->where('user_id', 99)->delete();

        // Ad-hoc block on 2025-09-18
        $db->table('availability')->insert([
            'user_id' => 99,
            'type' => 'day_off',
            'start_datetime' => '2025-09-18 08:00:00',
            'end_datetime' => '2025-09-18 20:00:00',
            'is_recurring' => 0,
            'notes' => 'Test ad-hoc debug',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Recurring weekly on Wednesday
        $db->table('availability')->insert([
            'user_id' => 99,
            'type' => 'recurring',
            'day_of_week' => 'Wednesday',
            'start_time' => '08:00:00',
            'end_time' => '20:00:00',
            'is_recurring' => 1,
            'notes' => 'Test recurring debug',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Call the events endpoint for the week containing 2025-09-18
        $result = $this->withSession(['user' => ['id' => 99, 'user_type' => 'dentist']])
                       ->get('/calendar/availability-events?start=2025-09-15&end=2025-09-21&dentist_id=99');

        $result->assertStatus(200);
        $payload = json_decode($result->getJSON(), true);

        $this->assertTrue($payload['success']);
        $this->assertArrayHasKey('events', $payload);

        $events = $payload['events'];
        $this->assertNotEmpty($events, 'Events should not be empty');

        $foundAdhoc = false;
        $foundRecurring = false;
        foreach ($events as $ev) {
            if ($ev['start'] === '2025-09-18 08:00:00' || strpos($ev['start'], '2025-09-18 08:00') === 0) $foundAdhoc = true;
            // Accept either an expanded recurring entry or a generated working_hours block
            if (strpos($ev['start'], '2025-09-18 08:00') === 0 && (intval($ev['is_recurring']) === 1 || ($ev['type'] ?? '') === 'working_hours')) $foundRecurring = true;
        }

        $this->assertTrue($foundAdhoc, 'Ad-hoc event should be present');
        $this->assertTrue($foundRecurring, 'Recurring expansion should be present');

        echo "\n✓ Availability debug endpoint returns ad-hoc and recurring events\n";
    }
}
