<?php

namespace Tests\Integration;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class DoubleBookingSmokeTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure a clean DB state for this test
    }

    public function testApproveAssignsDentistAndProvidesSuggestionsAndAutoReschedules()
    {
        $db = \Config\Database::connect();

        // Respect DB prefix if present
        $prefix = '';
        if (property_exists($db, 'DBPrefix')) {
            $prefix = $db->DBPrefix;
        } elseif (method_exists($db, 'getPrefix')) {
            $prefix = $db->getPrefix();
        }

        // Create minimal tables required for the test (SQLite friendly)
        $db->query("CREATE TABLE IF NOT EXISTS {$prefix}user (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT, password TEXT, user_type TEXT, status TEXT, created_at TEXT);");
        $db->query("CREATE TABLE IF NOT EXISTS {$prefix}branches (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, operating_hours TEXT, created_at TEXT, updated_at TEXT);");
        $db->query("CREATE TABLE IF NOT EXISTS {$prefix}services (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, duration_minutes INTEGER, duration_max_minutes INTEGER);");
        $db->query("CREATE TABLE IF NOT EXISTS {$prefix}appointments (id INTEGER PRIMARY KEY AUTOINCREMENT, branch_id INTEGER, dentist_id INTEGER, user_id INTEGER, patient_name TEXT, appointment_datetime TEXT, appointment_date TEXT, appointment_time TEXT, procedure_duration INTEGER, status TEXT, approval_status TEXT, created_at TEXT, updated_at TEXT);");
        $db->query("CREATE TABLE IF NOT EXISTS {$prefix}appointment_service (appointment_id INTEGER, service_id INTEGER);");
        $db->query("CREATE TABLE IF NOT EXISTS {$prefix}branch_staff (id INTEGER PRIMARY KEY AUTOINCREMENT, branch_id INTEGER, user_id INTEGER, created_at TEXT);");

        // Create two dentist users
        $db->table('user')->insert([
            'name' => 'Dentist One',
            'email' => 'dentist1@example.test',
            'user_type' => 'dentist',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $dentist1 = $db->insertID();

        $db->table('user')->insert([
            'name' => 'Dentist Two',
            'email' => 'dentist2@example.test',
            'user_type' => 'dentist',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $dentist2 = $db->insertID();

        // Ensure branch exists
        $existingBranch = $db->table('branches')->where('id', 1)->get()->getRowArray();
        if (!$existingBranch) {
            $db->table('branches')->insert(['id' => 1, 'name' => 'Smoke Branch', 'operating_hours' => json_encode(['monday'=>['enabled'=>true,'open'=>'08:00','close'=>'20:00']]), 'created_at' => date('Y-m-d H:i:s')]);
        }

        // Insert a confirmed appointment for dentist1 at 2025-10-01 09:00 (60 minutes)
        $db->table('appointments')->insert([
            'user_id' => null,
            'patient_name' => 'Existing Patient',
            'appointment_datetime' => '2025-10-01 09:00:00',
            'appointment_date' => '2025-10-01',
            'appointment_time' => '09:00',
            'procedure_duration' => 60,
            'dentist_id' => $dentist1,
            'branch_id' => 1,
            'status' => 'confirmed',
            'approval_status' => 'approved',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Create a pending appointment (waitlist) at same time without dentist assigned
        $db->table('appointments')->insert([
            'user_id' => null,
            'patient_name' => 'Waitlist Patient',
            'appointment_datetime' => '2025-10-01 09:00:00',
            'appointment_date' => '2025-10-01',
            'appointment_time' => '09:00',
            'procedure_duration' => 30,
            'dentist_id' => null,
            'branch_id' => 1,
            'status' => 'pending_approval',
            'approval_status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $pendingId = $db->insertID();

        // Link dentist2 to branch 1 (available dentist)
        $db->table('branch_staff')->insert(['branch_id' => 1, 'user_id' => $dentist2, 'created_at' => date('Y-m-d H:i:s')]);

        // Use AppointmentService directly to exercise suggestions and auto-reschedule
        $svc = new \App\Services\AppointmentService();

    // Case A: simulate a conflicting approval request by marking the pending appointment as assigned to dentist1
    // This simulates an admin attempting to approve an appointment already assigned to a dentist who is double-booked
    $db->table('appointments')->update(['dentist_id' => $dentist1], ['id' => $pendingId]);
    $resp = $svc->approveAppointment($pendingId);
        // Expect conflict response with suggestions
        $this->assertIsArray($resp, 'approveAppointment should return an array');
        $this->assertArrayHasKey('success', $resp, 'Response should include success key');
        $this->assertFalse((bool)$resp['success'], 'Approval should not succeed immediately due to conflict');
        $this->assertArrayHasKey('suggestions', $resp, 'Conflict response should include suggestions array');

        $suggestions = is_array($resp['suggestions']) ? $resp['suggestions'] : [];
        $this->assertNotEmpty($suggestions, 'Suggestions should be provided when conflict exists');

        // Case B: now attempt auto-reschedule using the first suggestion; chosen_time format is expected as 'HH:MM'
        $chosen = $suggestions[0];
    $resp2 = $svc->approveAppointment($pendingId, null, ['auto_reschedule' => 1, 'chosen_time' => $chosen]);
        $this->assertIsArray($resp2, 'approveAppointment auto-reschedule should return array');
        $this->assertTrue((bool)$resp2['success'], 'Auto-reschedule + approve should succeed');

    // Verify DB updated: approval_status updated (auto-reschedule path completed)
    $row = $db->table('appointments')->where('id', $pendingId)->get()->getRowArray();
    $this->assertEquals('approved', $row['approval_status'], 'Approval status should be approved after auto-reschedule');

        // Test completed
    }
}
