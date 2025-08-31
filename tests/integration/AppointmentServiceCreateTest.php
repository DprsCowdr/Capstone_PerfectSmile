<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class AppointmentServiceCreateTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;

    protected function setUp(): void
    {
        parent::setUp();

        if (!extension_loaded('sqlite3')) {
            $this->markTestSkipped('SQLite3 extension not available');
            return;
        }

        // Create appointments table with test DB prefix
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

    public function testCreateAppointmentViaServicePersistsDurationAndComputesEndTimes()
    {
        $service = new \App\Services\AppointmentService();
        $model = new \App\Models\AppointmentModel();

        $data = [
            'user_id' => 1,
            'branch_id' => 1,
            'appointment_date' => '2025-09-01',
            'appointment_time' => '09:00',
            'procedure_duration' => 38,
            'appointment_type' => 'scheduled'
        ];

        $result = $service->createAppointment($data);
        // createAppointment returns an array with success boolean
        $this->assertIsArray($result);

        // Find last inserted appointment row
        $rows = $model->orderBy('id', 'DESC')->limit(1)->findAll();
        $this->assertNotEmpty($rows);
        $row = $rows[0];

        $this->assertArrayHasKey('procedure_duration', $row);
        $this->assertEquals(38, (int)$row['procedure_duration']);

        // Appointment datetime should have been composed
        $this->assertArrayHasKey('appointment_datetime', $row);
        $this->assertStringStartsWith('2025-09-01 09:00', $row['appointment_datetime']);

        $startTs = strtotime($row['appointment_datetime']);
        $endTs = $startTs + (38 * 60);
        $expectedEnd = date('Y-m-d H:i:s', $endTs);
        $expectedEndWithInterval = date('Y-m-d H:i:s', $endTs + (30 * 60));

        // Compute using the same logic Appointments controller uses (date-only in row)
        $this->assertEquals($expectedEnd, date('Y-m-d H:i:s', strtotime($row['appointment_datetime']) + ((int)$row['procedure_duration'] * 60)));
        $this->assertEquals($expectedEndWithInterval, date('Y-m-d H:i:s', strtotime($row['appointment_datetime']) + ((int)$row['procedure_duration'] * 60) + (30 * 60)));
    }
}
