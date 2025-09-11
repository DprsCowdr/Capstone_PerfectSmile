<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CreateMoreTestRecords extends BaseCommand
{
    protected $group = 'app';
    protected $name = 'app:create-more-records';
    protected $description = 'Create more test dental records from appointments';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        CLI::write('=== Creating More Test Dental Records ===', 'green');
        CLI::newLine();

        try {
            // Get appointments that don't have dental records
            $appointmentsWithoutRecords = $db->query("
                SELECT 
                    a.id as appointment_id,
                    a.user_id,
                    a.branch_id,
                    a.dentist_id,
                    a.appointment_datetime,
                    u.name as patient_name,
                    d.name as dentist_name,
                    b.name as branch_name
                FROM appointments a
                LEFT JOIN user u ON u.id = a.user_id
                LEFT JOIN user d ON d.id = a.dentist_id
                LEFT JOIN branches b ON b.id = a.branch_id
                LEFT JOIN dental_record dr ON dr.appointment_id = a.id
                WHERE dr.id IS NULL 
                AND a.branch_id IS NOT NULL
                AND a.dentist_id IS NOT NULL
                ORDER BY a.appointment_datetime DESC
                LIMIT 5
            ")->getResultArray();

            CLI::write("Found " . count($appointmentsWithoutRecords) . " appointments without dental records");
            CLI::newLine();

            if (count($appointmentsWithoutRecords) === 0) {
                CLI::write("No appointments need dental records.", 'yellow');
                return;
            }

            $treatments = [
                'Routine cleaning and examination',
                'Tooth extraction',
                'Filling cavity',
                'Root canal treatment',
                'Teeth whitening',
                'Gum treatment',
                'Crown placement',
                'Dental implant consultation'
            ];

            $notes = [
                'Patient had routine checkup. Everything looks good.',
                'Some plaque buildup, recommended better oral hygiene.',
                'Patient complained of tooth sensitivity.',
                'No issues found. Recommended regular checkups.',
                'Patient needs follow-up in 3 months.',
                'Minor cavity found and filled successfully.',
                'Patient education provided on proper brushing.',
                'Recommended fluoride treatment.'
            ];

            $created = 0;
            foreach ($appointmentsWithoutRecords as $apt) {
                $treatment = $treatments[array_rand($treatments)];
                $note = $notes[array_rand($notes)];
                
                $recordData = [
                    'user_id' => $apt['user_id'],
                    'appointment_id' => $apt['appointment_id'],
                    'branch_id' => $apt['branch_id'], // Explicitly set branch_id
                    'record_date' => date('Y-m-d', strtotime($apt['appointment_datetime'])),
                    'treatment' => $treatment,
                    'notes' => $note,
                    'dentist_id' => $apt['dentist_id'],
                    'xray_image_url' => null,
                    'next_appointment_date' => rand(0, 1) ? date('Y-m-d', strtotime('+' . rand(30, 180) . ' days')) : null
                ];

                $result = $db->table('dental_record')->insert($recordData);
                $recordId = $db->insertID();

                if ($result && $recordId) {
                    CLI::write("✓ Created record ID $recordId for {$apt['patient_name']} at {$apt['branch_name']} (Appointment {$apt['appointment_id']})");
                    $created++;
                    
                    // Create a simple dental chart for some records
                    if (rand(0, 1)) {
                        $chartData = [
                            'dental_record_id' => $recordId,
                            'tooth_number' => (string)(10 + rand(1, 8)),
                            'tooth_type' => 'permanent',
                            'condition' => rand(0, 1) ? 'healthy' : 'cavity',
                            'status' => rand(0, 1) ? 'healthy' : 'needs_treatment',
                            'notes' => 'Random generated chart data',
                            'priority' => ['low', 'medium', 'high'][rand(0, 2)],
                            'estimated_cost' => rand(0, 1) ? rand(50, 300) : null,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                        $db->table('dental_chart')->insert($chartData);
                        CLI::write("  + Added dental chart entry");
                    }
                } else {
                    CLI::error("Failed to create record for appointment {$apt['appointment_id']}");
                }
            }

            CLI::newLine();
            CLI::write("SUCCESS: Created $created dental records!", 'green');
            CLI::newLine();
            CLI::write("Now you can test both staff and admin interfaces:", 'yellow');
            CLI::write("- Staff will see these records in their branch");
            CLI::write("- Admin will see all records categorized by branch");

        } catch (\Exception $e) {
            CLI::error("ERROR: " . $e->getMessage());
        }
    }
}
