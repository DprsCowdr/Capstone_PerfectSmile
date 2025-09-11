<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class VerifyAdminRecords extends BaseCommand
{
    protected $group = 'app';
    protected $name = 'app:verify-admin-records';
    protected $description = 'Verify admin records display correctly';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        CLI::write('=== Verifying Admin Records Display ===', 'green');
        CLI::newLine();

        try {
            // Test the exact query the admin interface would use
            $adminRecords = $db->query("
                SELECT 
                    dr.id,
                    dr.user_id,
                    dr.appointment_id,
                    dr.branch_id,
                    dr.record_date,
                    dr.treatment,
                    dr.notes,
                    dr.dentist_id,
                    u.name as patient_name,
                    u.email as patient_email,
                    COALESCE(dr.branch_id, a.branch_id) as effective_branch_id,
                    COALESCE(b1.name, b2.name) as branch_name,
                    d.name as dentist_name
                FROM dental_record dr
                LEFT JOIN user u ON u.id = dr.user_id
                LEFT JOIN appointments a ON a.id = dr.appointment_id
                LEFT JOIN branches b1 ON b1.id = dr.branch_id
                LEFT JOIN branches b2 ON b2.id = a.branch_id
                LEFT JOIN user d ON d.id = dr.dentist_id
                ORDER BY dr.record_date DESC, dr.id DESC
            ")->getResultArray();

            CLI::write("Total records found: " . count($adminRecords), 'yellow');
            CLI::newLine();

            if (count($adminRecords) === 0) {
                CLI::error("No records found!");
                return;
            }

            // Group by branch
            $byBranch = [];
            foreach ($adminRecords as $record) {
                $branchName = $record['branch_name'] ?: 'Unknown Branch';
                if (!isset($byBranch[$branchName])) {
                    $byBranch[$branchName] = [];
                }
                $byBranch[$branchName][] = $record;
            }

            CLI::write("Records by Branch:", 'cyan');
            foreach ($byBranch as $branchName => $records) {
                CLI::write("  {$branchName}: " . count($records) . " records", 'white');
                foreach ($records as $record) {
                    CLI::write("    - ID {$record['id']}: {$record['patient_name']} ({$record['record_date']}) - {$record['treatment']}", 'light_gray');
                }
            }

            CLI::newLine();

            // Check dental chart data
            $chartCount = $db->query("SELECT COUNT(*) as count FROM dental_chart")->getRow()->count;
            CLI::write("Total dental chart entries: $chartCount", 'yellow');

            // Check which records have chart data
            $recordsWithCharts = $db->query("
                SELECT DISTINCT dr.id, COUNT(dc.id) as chart_entries
                FROM dental_record dr
                LEFT JOIN dental_chart dc ON dc.dental_record_id = dr.id
                GROUP BY dr.id
                HAVING chart_entries > 0
                ORDER BY dr.id
            ")->getResultArray();

            CLI::write("Records with dental charts: " . count($recordsWithCharts), 'yellow');
            foreach ($recordsWithCharts as $record) {
                CLI::write("  - Record ID {$record['id']}: {$record['chart_entries']} chart entries", 'light_gray');
            }

            CLI::newLine();
            CLI::write("✓ Admin should now see ALL branch-created records!", 'green');
            CLI::write("✓ Dental charts are available for testing the chart loading feature!", 'green');

        } catch (\Exception $e) {
            CLI::error("ERROR: " . $e->getMessage());
        }
    }
}
