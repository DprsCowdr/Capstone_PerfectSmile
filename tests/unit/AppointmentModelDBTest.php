<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class AppointmentModelDBTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true; // ensure fresh DB per test

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure sqlite3 extension exists for running tests
        if (!extension_loaded('sqlite3')) {
            $this->markTestSkipped('SQLite3 extension not available');
            return;
        }

                // Create appointments table in test DB (in-memory)
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

    public function testInsertViaModelPersistsProcedureDurationAndSplitDateTime()
    {
        $model = new \App\Models\AppointmentModel();

        $data = [
            'user_id' => 1,
            'branch_id' => 1,
            'appointment_date' => '2025-09-01',
            'appointment_time' => '09:00',
            'procedure_duration' => 45,
            'status' => 'pending',
            'appointment_type' => 'scheduled',
            'approval_status' => 'pending'
        ];

        $insertId = $model->insert($data);
        $this->assertIsInt($insertId);

        $row = $model->find($insertId);
        $this->assertIsArray($row);

        // procedure_duration persisted
        $this->assertArrayHasKey('procedure_duration', $row);
        $this->assertEquals(45, (int)$row['procedure_duration']);

        // appointment_time derived
        $this->assertArrayHasKey('appointment_time', $row);
        $this->assertEquals('09:00', $row['appointment_time']);

        // Server-side computed end and end_with_interval
        $startTs = strtotime($row['appointment_datetime']);
        $endTs = $startTs + ((int)$row['procedure_duration'] * 60);
        $this->assertEquals('09:45', date('H:i', $endTs));
        $this->assertEquals('10:15', date('H:i', $endTs + (30 * 60)));
    }
}
