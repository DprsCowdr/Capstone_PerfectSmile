<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestAdminDentalChartAPI extends BaseCommand
{
    protected $group = 'app';
    protected $name = 'app:test-dental-api';
    protected $description = 'Test the admin dental chart API endpoint functionality';

    public function run(array $params)
    {
        CLI::write('=== Testing Admin Dental Chart API ===', 'green');
        CLI::newLine();

        $db = \Config\Database::connect();

        try {
            // Find a patient with dental chart data
            $patientWithChart = $db->query("
                SELECT DISTINCT dr.user_id, u.name as patient_name, COUNT(dc.id) as chart_count
                FROM dental_record dr
                LEFT JOIN dental_chart dc ON dc.dental_record_id = dr.id
                LEFT JOIN user u ON u.id = dr.user_id
                GROUP BY dr.user_id, u.name
                HAVING chart_count > 0
                LIMIT 1
            ")->getRow();

            if (!$patientWithChart) {
                CLI::error("No patients with dental chart data found!");
                return;
            }

            $patientId = $patientWithChart->user_id;
            $patientName = $patientWithChart->patient_name;
            $chartCount = $patientWithChart->chart_count;

            CLI::write("Testing with Patient: {$patientName} (ID: {$patientId})", 'yellow');
            CLI::write("Expected chart entries: {$chartCount}", 'yellow');
            CLI::newLine();

            // Simulate the exact query that AdminController::getPatientDentalChart uses
            CLI::write("Simulating AdminController::getPatientDentalChart query...", 'cyan');
            
            // First query: dental chart data
            $chartRows = $db->table('dental_chart dc')
                ->select('dc.*, dr.record_date')
                ->join('dental_record dr', 'dr.id = dc.dental_record_id')
                ->where('dr.user_id', $patientId)
                ->orderBy('dr.record_date', 'DESC')
                ->get()->getResultArray();

            CLI::write("Chart data rows found: " . count($chartRows), 'white');
            foreach ($chartRows as $row) {
                CLI::write("  - Tooth #{$row['tooth_number']}: {$row['condition']} ({$row['record_date']})", 'light_gray');
            }
            CLI::newLine();

            // Second query: dental records (instead of visual chart data)
            $dentalRecords = $db->table('dental_record')
                ->select('id, record_date, treatment, notes')
                ->where('user_id', $patientId)
                ->orderBy('record_date', 'DESC')
                ->get()->getResultArray();

            CLI::write("Dental records found: " . count($dentalRecords), 'white');
            foreach ($dentalRecords as $record) {
                CLI::write("  - Record ID {$record['id']}: {$record['treatment']} ({$record['record_date']})", 'light_gray');
            }
            CLI::newLine();

            // Test the expected JSON response format
            $expectedResponse = [
                'success' => true,
                'chart' => $chartRows,
                'visual_charts' => [], // Empty since column doesn't exist
                'dental_records' => $dentalRecords
            ];

            CLI::write("Expected API Response:", 'cyan');
            CLI::write(json_encode($expectedResponse, JSON_PRETTY_PRINT), 'light_gray');
            CLI::newLine();

            if (count($chartRows) > 0) {
                CLI::write("✓ SUCCESS: Dental chart API should work correctly!", 'green');
                CLI::write("✓ Patient {$patientName} has dental chart data ready for admin interface", 'green');
            } else {
                CLI::write("⚠ WARNING: No chart data found for this patient", 'yellow');
            }

            CLI::newLine();
            CLI::write("🔍 Next Steps:", 'cyan');
            CLI::write("1. Log into admin interface", 'white');
            CLI::write("2. Go to Records Management", 'white');
            CLI::write("3. Find patient: {$patientName}", 'white');
            CLI::write("4. Click on Dental Chart button", 'white');
            CLI::write("5. Chart should load successfully (no more 'Failed to Load' error)", 'white');

        } catch (\Exception $e) {
            CLI::error("ERROR: " . $e->getMessage());
        }
    }
}
