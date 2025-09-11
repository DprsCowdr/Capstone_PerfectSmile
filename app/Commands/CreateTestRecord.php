<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CreateTestRecord extends BaseCommand
{
    protected $group = 'app';
    protected $name = 'app:create-test-record';
    protected $description = 'Create a test dental record to verify admin display';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        CLI::write('=== Creating Test Dental Record ===', 'green');
        CLI::newLine();

        try {
            // First, check if we have any appointments and patients
            $appointments = $db->table('appointments')->limit(1)->get()->getResultArray();
            $patients = $db->table('user')->where('user_type', 'patient')->limit(1)->get()->getResultArray();
            $dentists = $db->table('user')->where('user_type', 'dentist')->limit(1)->get()->getResultArray();

            if (empty($appointments)) {
                CLI::error("No appointments found. Please create an appointment first.");
                return;
            }

            if (empty($patients)) {
                CLI::error("No patients found. Please create a patient first.");
                return;
            }

            if (empty($dentists)) {
                CLI::error("No dentists found. Please create a dentist first.");
                return;
            }

            $appointment = $appointments[0];
            $patient = $patients[0];
            $dentist = $dentists[0];

            CLI::write("Using appointment ID: {$appointment['id']} (Branch ID: {$appointment['branch_id']})");
            CLI::write("Patient: {$patient['name']} (ID: {$patient['id']})");
            CLI::write("Dentist: {$dentist['name']} (ID: {$dentist['id']})");
            CLI::newLine();

            // Create a test dental record
            $recordData = [
                'user_id' => $patient['id'],
                'appointment_id' => $appointment['id'],
                'branch_id' => $appointment['branch_id'], // Explicitly set branch_id
                'record_date' => date('Y-m-d'),
                'treatment' => 'Routine cleaning and examination',
                'notes' => 'Patient had routine cleaning. No issues found. Recommended regular checkups every 6 months.',
                'dentist_id' => $dentist['id'],
                'xray_image_url' => null,
                'next_appointment_date' => date('Y-m-d', strtotime('+6 months'))
            ];

            $result = $db->table('dental_record')->insert($recordData);
            $recordId = $db->insertID();

            if ($result && $recordId) {
                CLI::write("SUCCESS: Created test dental record with ID: $recordId", 'green');
                CLI::write("Record details:");
                CLI::write("- Patient: {$patient['name']}");
                CLI::write("- Branch ID: {$appointment['branch_id']}");
                CLI::write("- Treatment: {$recordData['treatment']}");
                CLI::write("- Date: {$recordData['record_date']}");
                CLI::newLine();
                CLI::write("You can now check the admin records page to see this record categorized by branch.", 'yellow');
            } else {
                CLI::error("Failed to create test dental record.");
            }

        } catch (\Exception $e) {
            CLI::error("ERROR: " . $e->getMessage());
        }
    }
}
