<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

final class StaffControllerCreateTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure sqlite3 available for DB-backed parts of the app if needed
        if (!extension_loaded('sqlite3')) {
            $this->markTestSkipped('SQLite3 extension not available');
            return;
        }
        // Provide a minimal appointments table so controller code that queries it won't fail
        $table = ($this->db->DBPrefix ?? '') . 'appointments';
        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          appointment_datetime TEXT,
          procedure_duration INTEGER,
          status TEXT,
          approval_status TEXT,
          appointment_type TEXT,
          user_id INTEGER,
          branch_id INTEGER,
          dentist_id INTEGER,
          remarks TEXT,
          created_at TEXT,
          updated_at TEXT
        );";
        $this->db->query($sql);
    }

    public function testStaffCreateAppointmentDebugJsonReturnsComputedTimes()
    {
        // Simulate CI session auth for the FeatureTest request
        $session = [
            'isLoggedIn' => true,
            'user_id' => 10,
            'user_name' => 'Test Staff',
            'user_email' => 'staff@example.test',
            'user_type' => 'staff',
            // Also provide a session-backed 'user' array so Auth::getCurrentUser() short-circuits DB lookup
            'user' => [
                'id' => 10,
                'name' => 'Test Staff',
                'email' => 'staff@example.test',
                'user_type' => 'staff'
            ]
        ];

        // Build POST payload
        $payload = [
            'branch' => 1,
            'patient' => 1,
            'date' => '2025-09-01',
            'time' => '09:00',
            'procedure_duration' => 45,
            'debug_json' => 1
        ];

        // Perform POST request to the staff create route
    $result = $this->withSession($session)
               ->withHeaders(['Accept' => 'application/json'])
               ->post('/staff/appointments/create', $payload);

        // Some test environments may wrap JSON in HTML; extract JSON object from body if necessary
        $body = (string) $result->getBody();
        $this->assertNotEmpty($body, "Expected non-empty response body from debug_json endpoint");

        // Try getJSON() first
        $json = $result->getJSON();
        if (!$json || (is_string($json) && trim($json) === '')) {
            // Fallback: extract first JSON object-looking substring from body
            $start = strpos($body, '{');
            $end = strrpos($body, '}');
            $this->assertNotFalse($start, "Failed to find JSON start in body: {$body}");
            $this->assertNotFalse($end, "Failed to find JSON end in body: {$body}");
            $json = substr($body, $start, $end - $start + 1);
        }

        $data = is_string($json) ? json_decode($json, true) : $json;
        $this->assertIsArray($data, 'Expected decoded JSON array from debug endpoint');
        $this->assertArrayHasKey('debug', $data);
        $this->assertTrue($data['debug']);
        $this->assertEquals('09:00', $data['start']);
        $this->assertEquals('09:00', $data['start']);
        $this->assertEquals('09:45', $data['end']);
        $this->assertEquals('10:15', $data['end_with_interval']);
    }
}
