<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class BackfillDentalBranches extends BaseCommand
{
    protected $group = 'app';
    protected $name = 'app:backfill-dental-branches';
    protected $description = 'Backfill branch_id for dental records based on appointment branch_id';

    protected $usage = 'app:backfill-dental-branches [options]';
    protected $options = [
        '--dry-run' => 'Preview changes without applying them',
        '--apply'   => 'Apply the changes to the database',
    ];

    public function run(array $params)
    {
        $dryRun = CLI::getOption('dry-run');
        $apply = CLI::getOption('apply');

        if (!$dryRun && !$apply) {
            CLI::error('You must specify either --dry-run or --apply');
            CLI::write('Usage:');
            CLI::write('  php spark app:backfill-dental-branches --dry-run    # Preview changes only');
            CLI::write('  php spark app:backfill-dental-branches --apply      # Apply the changes');
            return;
        }

        CLI::write('=== Dental Record Branch ID Backfill Script ===', 'green');
        CLI::write('Date: ' . date('Y-m-d H:i:s'));
        CLI::write('Mode: ' . ($dryRun ? 'DRY RUN (preview only)' : 'APPLY CHANGES'));
        CLI::newLine();

        $db = \Config\Database::connect();

        try {
            // Find records that need updating
            $query = "
                SELECT 
                    dr.id,
                    dr.user_id,
                    dr.appointment_id,
                    dr.branch_id as current_branch_id,
                    a.branch_id as appointment_branch_id,
                    u.name as patient_name,
                    dr.record_date
                FROM dental_record dr
                LEFT JOIN appointments a ON dr.appointment_id = a.id
                LEFT JOIN user u ON dr.user_id = u.id
                WHERE dr.branch_id IS NULL 
                AND a.branch_id IS NOT NULL
                ORDER BY dr.record_date DESC
            ";
            
            $recordsToUpdate = $db->query($query)->getResultArray();
            
            CLI::write("Found " . count($recordsToUpdate) . " dental records that need branch_id backfilled.", 'yellow');
            CLI::newLine();
            
            if (count($recordsToUpdate) === 0) {
                CLI::write("No records need updating. All dental records already have branch_id set or have no linked appointments.", 'green');
                return;
            }
            
            // Show preview
            CLI::write("Preview of changes:");
            CLI::write(str_repeat("-", 100));
            CLI::write(sprintf("%-5s %-10s %-15s %-25s %-12s %-10s", 
                       "ID", "Patient", "Appointment", "Patient Name", "Record Date", "Branch ID"));
            CLI::write(str_repeat("-", 100));
            
            foreach ($recordsToUpdate as $record) {
                CLI::write(sprintf("%-5s %-10s %-15s %-25s %-12s %-10s",
                           $record['id'],
                           $record['user_id'],
                           $record['appointment_id'] ?: 'NULL',
                           substr($record['patient_name'] ?: 'Unknown', 0, 24),
                           $record['record_date'],
                           $record['appointment_branch_id']
                ));
            }
            
            CLI::write(str_repeat("-", 100));
            CLI::newLine();
            
            if ($dryRun) {
                CLI::write("DRY RUN: No changes were applied. Use --apply to execute the updates.", 'yellow');
                CLI::newLine();
                CLI::write("SQL that would be executed:");
                CLI::write("UPDATE dental_record dr");
                CLI::write("JOIN appointments a ON dr.appointment_id = a.id");
                CLI::write("SET dr.branch_id = a.branch_id");
                CLI::write("WHERE dr.branch_id IS NULL AND a.branch_id IS NOT NULL;");
                return;
            }
            
            if ($apply) {
                CLI::write("Applying changes...", 'yellow');
                
                $db->transStart();
                
                $updateQuery = "
                    UPDATE dental_record dr
                    JOIN appointments a ON dr.appointment_id = a.id
                    SET dr.branch_id = a.branch_id
                    WHERE dr.branch_id IS NULL AND a.branch_id IS NOT NULL
                ";
                
                $db->query($updateQuery);
                $affectedRows = $db->affectedRows();
                
                $db->transComplete();
                
                if ($db->transStatus() === FALSE) {
                    CLI::error("Transaction failed. No changes were applied.");
                    return;
                }
                
                CLI::write("SUCCESS: Updated $affectedRows dental records with branch_id.", 'green');
                
                // Verify
                $verifyQuery = "
                    SELECT COUNT(*) as remaining_null
                    FROM dental_record dr
                    LEFT JOIN appointments a ON dr.appointment_id = a.id
                    WHERE dr.branch_id IS NULL AND a.branch_id IS NOT NULL
                ";
                
                $remaining = $db->query($verifyQuery)->getRow()->remaining_null;
                
                if ($remaining > 0) {
                    CLI::write("WARNING: $remaining records still have NULL branch_id.", 'red');
                } else {
                    CLI::write("All applicable records have been updated successfully.", 'green');
                }
                
                // Show stats
                $statsQuery = "
                    SELECT 
                        COUNT(*) as total_records,
                        COUNT(dr.branch_id) as records_with_branch_id,
                        COUNT(*) - COUNT(dr.branch_id) as records_without_branch_id
                    FROM dental_record dr
                ";
                
                $stats = $db->query($statsQuery)->getRow();
                
                CLI::newLine();
                CLI::write("Final Statistics:");
                CLI::write("Total dental records: {$stats->total_records}");
                CLI::write("Records with branch_id: {$stats->records_with_branch_id}");
                CLI::write("Records without branch_id: {$stats->records_without_branch_id}");
            }
            
        } catch (\Exception $e) {
            CLI::error("ERROR: " . $e->getMessage());
            return;
        }

        CLI::newLine();
        CLI::write("Backfill complete!", 'green');
    }
}
