<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CreateTestDentalChart extends BaseCommand
{
    protected $group = 'app';
    protected $name = 'app:create-test-chart';
    protected $description = 'Create a test dental chart for the test record';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        CLI::write('=== Creating Test Dental Chart ===', 'green');
        CLI::newLine();

        try {
            // Check if we have the test dental record
            $dentalRecord = $db->table('dental_record')->where('id', 47)->get()->getRow();
            
            if (!$dentalRecord) {
                CLI::error("Test dental record (ID 47) not found. Run php spark app:create-test-record first.");
                return;
            }

            CLI::write("Found dental record ID 47 for patient ID: {$dentalRecord->user_id}");
            
            // Create sample dental chart data
            $chartData = [
                [
                    'dental_record_id' => 47,
                    'tooth_number' => '11',
                    'tooth_type' => 'permanent',
                    'condition' => 'cavity',
                    'status' => 'needs_treatment',
                    'notes' => 'Small cavity on mesial surface',
                    'recommended_service_id' => null,
                    'priority' => 'medium',
                    'estimated_cost' => 150.00,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                [
                    'dental_record_id' => 47,
                    'tooth_number' => '12',
                    'tooth_type' => 'permanent',
                    'condition' => 'healthy',
                    'status' => 'healthy',
                    'notes' => 'Healthy tooth, no issues',
                    'recommended_service_id' => null,
                    'priority' => 'low',
                    'estimated_cost' => null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                [
                    'dental_record_id' => 47,
                    'tooth_number' => '21',
                    'tooth_type' => 'permanent',
                    'condition' => 'plaque',
                    'status' => 'needs_cleaning',
                    'notes' => 'Plaque buildup, needs professional cleaning',
                    'recommended_service_id' => null,
                    'priority' => 'low',
                    'estimated_cost' => 75.00,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            ];

            $db->transStart();

            foreach ($chartData as $chart) {
                $result = $db->table('dental_chart')->insert($chart);
                if (!$result) {
                    CLI::error("Failed to insert chart entry for tooth {$chart['tooth_number']}");
                    return;
                }
            }

            $db->transComplete();

            if ($db->transStatus() === FALSE) {
                CLI::error("Transaction failed. No chart data was inserted.");
                return;
            }

            CLI::write("SUCCESS: Created dental chart with " . count($chartData) . " tooth entries", 'green');
            CLI::newLine();

            // Verify the chart was created
            $chartCount = $db->table('dental_chart')->where('dental_record_id', 47)->countAllResults();
            CLI::write("Verification: Found $chartCount chart entries for dental record 47");

            CLI::newLine();
            CLI::write("You can now test the dental chart loading in the admin interface.", 'yellow');

        } catch (\Exception $e) {
            CLI::error("ERROR: " . $e->getMessage());
        }
    }
}
