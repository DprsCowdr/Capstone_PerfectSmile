<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class DebugAppointments extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:debug-appointments';
    protected $description = 'Debug appointment data for check-in issues';

    public function run(array $params)
    {
        CLI::write("Debugging appointments...", 'blue');
        
        try {
            $db = Database::connect();
            
            // Get today's appointments
            $appointments = $db->table('appointments')
                ->select('appointments.*, user.name as patient_name')
                ->join('user', 'user.id = appointments.user_id')
                ->where('DATE(appointment_datetime)', date('Y-m-d'))
                ->get()->getResultArray();
            
            CLI::write("Today's appointments (" . date('Y-m-d') . "):", 'green');
            CLI::write("=====================================", 'green');
            
            if (empty($appointments)) {
                CLI::write("No appointments found for today.", 'yellow');
                
                // Check if there are any appointments at all
                $allAppointments = $db->table('appointments')->countAllResults();
                CLI::write("Total appointments in database: " . $allAppointments, 'yellow');
                
                if ($allAppointments > 0) {
                    // Show latest appointments
                    $latestAppointments = $db->table('appointments')
                        ->select('id, appointment_datetime, status')
                        ->orderBy('appointment_datetime', 'DESC')
                        ->limit(5)
                        ->get()->getResultArray();
                    
                    CLI::write("\nLatest 5 appointments:", 'blue');
                    foreach($latestAppointments as $apt) {
                        CLI::write("ID: {$apt['id']} | Date: {$apt['appointment_datetime']} | Status: {$apt['status']}", 'white');
                    }
                }
            } else {
                foreach($appointments as $appointment) {
                    $status = $appointment['status'];
                    $statusColor = 'white';
                    switch($status) {
                        case 'confirmed': $statusColor = 'blue'; break;
                        case 'scheduled': $statusColor = 'cyan'; break;
                        case 'checked_in': $statusColor = 'green'; break;
                        case 'ongoing': $statusColor = 'yellow'; break;
                        case 'completed': $statusColor = 'light_gray'; break;
                    }
                    
                    CLI::write("ID: {$appointment['id']} | Patient: {$appointment['patient_name']} | Time: {$appointment['appointment_datetime']} | Status: {$status}", $statusColor);
                }
            }
            
            // Check database structure
            CLI::write("\nChecking appointments table structure...", 'blue');
            $fields = $db->getFieldData('appointments');
            $fieldNames = array_column($fields, 'name');
            
            $requiredFields = ['checked_in_at', 'checked_in_by'];
            foreach($requiredFields as $field) {
                if (in_array($field, $fieldNames)) {
                    CLI::write("✅ Field '{$field}' exists", 'green');
                } else {
                    CLI::write("❌ Field '{$field}' is missing", 'red');
                }
            }
            
        } catch (\Exception $e) {
            CLI::write("❌ Error: " . $e->getMessage(), 'red');
        }
    }
}
