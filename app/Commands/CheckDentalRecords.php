<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CheckDentalRecords extends BaseCommand
{
    protected $group = 'app';
    protected $name = 'app:check-dental-records';
    protected $description = 'Check dental records in the database';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        CLI::write('=== Dental Records Database Check ===', 'green');
        CLI::newLine();

        try {
            // Check total dental records
            $totalRecords = $db->table('dental_record')->countAll();
            CLI::write("Total dental records: $totalRecords");

            // Check records with branch_id
            $withBranchId = $db->table('dental_record')->where('branch_id IS NOT NULL')->countAllResults();
            CLI::write("Records with branch_id: $withBranchId");

            // Check records without branch_id
            $withoutBranchId = $db->table('dental_record')->where('branch_id IS NULL')->countAllResults();
            CLI::write("Records without branch_id: $withoutBranchId");

            CLI::newLine();

            // Check appointments
            $totalAppointments = $db->table('appointments')->countAll();
            CLI::write("Total appointments: $totalAppointments");

            // Check appointments with branch_id
            $appointmentsWithBranch = $db->table('appointments')->where('branch_id IS NOT NULL')->countAllResults();
            CLI::write("Appointments with branch_id: $appointmentsWithBranch");

            CLI::newLine();

            // Check branches
            $totalBranches = $db->table('branches')->countAll();
            CLI::write("Total branches: $totalBranches");

            CLI::newLine();

            // Show sample records
            if ($totalRecords > 0) {
                CLI::write("Sample dental records:");
                $sampleRecords = $db->query("
                    SELECT 
                        dr.id,
                        dr.user_id,
                        dr.appointment_id,
                        dr.branch_id,
                        dr.record_date,
                        u.name as patient_name,
                        a.branch_id as appointment_branch_id
                    FROM dental_record dr
                    LEFT JOIN user u ON dr.user_id = u.id
                    LEFT JOIN appointments a ON dr.appointment_id = a.id
                    ORDER BY dr.id DESC
                    LIMIT 5
                ")->getResultArray();

                CLI::write(str_repeat("-", 80));
                CLI::write(sprintf("%-5s %-10s %-15s %-12s %-12s %-20s", 
                           "ID", "User ID", "Appointment", "Branch ID", "Apt Branch", "Patient"));
                CLI::write(str_repeat("-", 80));

                foreach ($sampleRecords as $record) {
                    CLI::write(sprintf("%-5s %-10s %-15s %-12s %-12s %-20s",
                               $record['id'],
                               $record['user_id'],
                               $record['appointment_id'] ?: 'NULL',
                               $record['branch_id'] ?: 'NULL',
                               $record['appointment_branch_id'] ?: 'NULL',
                               substr($record['patient_name'] ?: 'Unknown', 0, 19)
                    ));
                }
                CLI::write(str_repeat("-", 80));
            }

        } catch (\Exception $e) {
            CLI::error("ERROR: " . $e->getMessage());
        }
    }
}
