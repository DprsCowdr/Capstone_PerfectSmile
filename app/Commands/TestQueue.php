<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class TestQueue extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:test-queue';
    protected $description = 'Test if checked-in patients appear in treatment queue';

    public function run(array $params)
    {
        CLI::write("Testing treatment queue visibility...", 'blue');
        
        try {
            $db = Database::connect();
            
            // Check checked-in appointments for today
            $checkedInAppointments = $db->table('appointments')
                ->select('appointments.*, user.name as patient_name')
                ->join('user', 'user.id = appointments.user_id')
                ->where('DATE(appointment_datetime)', date('Y-m-d'))
                ->where('appointments.status', 'checked_in')
                ->get()->getResultArray();
            
            CLI::write("Checked-in appointments for today:", 'green');
            CLI::write("==================================", 'green');
            
            if (empty($checkedInAppointments)) {
                CLI::write("❌ No checked-in appointments found for today.", 'red');
                
                // Check if there are any confirmed appointments we can check in
                $confirmedAppointments = $db->table('appointments')
                    ->select('id, status, user_id')
                    ->where('DATE(appointment_datetime)', date('Y-m-d'))
                    ->where('status', 'confirmed')
                    ->get()->getResultArray();
                    
                if (!empty($confirmedAppointments)) {
                    CLI::write("Found " . count($confirmedAppointments) . " confirmed appointments that can be checked in:", 'yellow');
                    foreach($confirmedAppointments as $apt) {
                        CLI::write("  ID: {$apt['id']} | Status: {$apt['status']}", 'white');
                    }
                }
            } else {
                foreach($checkedInAppointments as $appointment) {
                    CLI::write("ID: {$appointment['id']} | Patient: {$appointment['patient_name']} | Dentist ID: " . ($appointment['dentist_id'] ?: 'Not assigned') . " | Checked in at: {$appointment['checked_in_at']}", 'green');
                }
                
                CLI::write("\n✅ These patients should appear in the treatment queue!", 'green');
            }
            
            // Test the treatment queue query logic
            CLI::write("\nTesting treatment queue query logic:", 'blue');
            CLI::write("====================================", 'blue');
            
            // Simulate admin view (should see all)
            $adminQueueQuery = $db->table('appointments')
                ->select('appointments.*, user.name as patient_name')
                ->join('user', 'user.id = appointments.user_id')
                ->where('DATE(appointment_datetime)', date('Y-m-d'))
                ->where('appointments.status', 'checked_in')
                ->get()->getResultArray();
                
            CLI::write("Admin view (should see all): " . count($adminQueueQuery) . " patients", 'cyan');
            
            // Simulate doctor view (should see assigned + unassigned)
            $doctorQueueQuery = $db->table('appointments')
                ->select('appointments.*, user.name as patient_name')
                ->join('user', 'user.id = appointments.user_id')
                ->where('DATE(appointment_datetime)', date('Y-m-d'))
                ->where('appointments.status', 'checked_in')
                ->groupStart()
                ->where('appointments.dentist_id', 2) // Assuming Dr. Minnie Gonowon has ID 2
                ->orWhere('appointments.dentist_id IS NULL')
                ->groupEnd()
                ->get()->getResultArray();
                
            CLI::write("Doctor view (Dr. Minnie Gonowon): " . count($doctorQueueQuery) . " patients", 'cyan');
            
        } catch (\Exception $e) {
            CLI::write("❌ Error: " . $e->getMessage(), 'red');
        }
    }
}
