<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CompareStaffAdminRecords extends BaseCommand
{
    protected $group = 'app';
    protected $name = 'app:compare-staff-admin';
    protected $description = 'Compare what records staff sees vs admin sees';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        CLI::write('=== Staff vs Admin Records Comparison ===', 'green');
        CLI::newLine();

        try {
            // Check what records admin sees (using the actual admin query)
            CLI::write("1. Records Admin Sees (via DentalRecordModel):");
            CLI::write(str_repeat("-", 80));
            
            $adminQuery = "
                SELECT 
                    dental_record.*,
                    user.name as patient_name,
                    dentist.name as dentist_name,
                    appointments.appointment_datetime,
                    COALESCE(dental_record.branch_id, appointments.branch_id) as branch_id,
                    branches.name as branch_name
                FROM dental_record
                LEFT JOIN user ON user.id = dental_record.user_id
                LEFT JOIN user as dentist ON dentist.id = dental_record.dentist_id
                LEFT JOIN appointments ON appointments.id = dental_record.appointment_id
                LEFT JOIN branches ON branches.id = COALESCE(dental_record.branch_id, appointments.branch_id)
                ORDER BY dental_record.record_date DESC
            ";
            
            $adminRecords = $db->query($adminQuery)->getResultArray();
            
            CLI::write("Admin sees " . count($adminRecords) . " dental records:");
            if (count($adminRecords) > 0) {
                CLI::write(sprintf("%-5s %-15s %-12s %-15s %-20s", "ID", "Patient", "Branch ID", "Branch Name", "Date"));
                CLI::write(str_repeat("-", 80));
                foreach ($adminRecords as $record) {
                    CLI::write(sprintf("%-5s %-15s %-12s %-15s %-20s",
                               $record['id'],
                               substr($record['patient_name'] ?: 'Unknown', 0, 14),
                               $record['branch_id'] ?: 'NULL',
                               substr($record['branch_name'] ?: 'No Branch', 0, 14),
                               $record['record_date']
                    ));
                }
            }
            
            CLI::newLine();
            
            // Check what records staff would see (appointments-based)
            CLI::write("2. What Staff Sees (via Appointments in their branches):");
            CLI::write(str_repeat("-", 80));
            
            // Get all appointments with branch_id
            $staffQuery = "
                SELECT 
                    a.id as appointment_id,
                    a.user_id,
                    a.branch_id,
                    a.appointment_datetime,
                    a.status,
                    u.name as patient_name,
                    b.name as branch_name,
                    dr.id as dental_record_id
                FROM appointments a
                LEFT JOIN user u ON u.id = a.user_id
                LEFT JOIN branches b ON b.id = a.branch_id
                LEFT JOIN dental_record dr ON dr.appointment_id = a.id
                WHERE a.branch_id IS NOT NULL
                ORDER BY a.appointment_datetime DESC
            ";
            
            $staffAppointments = $db->query($staffQuery)->getResultArray();
            
            CLI::write("Staff sees " . count($staffAppointments) . " appointments:");
            if (count($staffAppointments) > 0) {
                CLI::write(sprintf("%-5s %-15s %-12s %-15s %-15s %-10s", "Apt ID", "Patient", "Branch ID", "Branch Name", "Date", "Has Record"));
                CLI::write(str_repeat("-", 90));
                foreach ($staffAppointments as $apt) {
                    CLI::write(sprintf("%-5s %-15s %-12s %-15s %-15s %-10s",
                               $apt['appointment_id'],
                               substr($apt['patient_name'] ?: 'Unknown', 0, 14),
                               $apt['branch_id'],
                               substr($apt['branch_name'] ?: 'No Branch', 0, 14),
                               substr($apt['appointment_datetime'], 0, 10),
                               $apt['dental_record_id'] ? 'YES' : 'NO'
                    ));
                }
            }
            
            CLI::newLine();
            
            // Check for appointments that have no dental records
            CLI::write("3. Appointments WITHOUT Dental Records:");
            CLI::write(str_repeat("-", 80));
            
            $appointmentsWithoutRecords = array_filter($staffAppointments, function($apt) {
                return empty($apt['dental_record_id']);
            });
            
            CLI::write("Found " . count($appointmentsWithoutRecords) . " appointments without dental records:");
            if (count($appointmentsWithoutRecords) > 0) {
                foreach (array_slice($appointmentsWithoutRecords, 0, 10) as $apt) {
                    CLI::write("- Appointment {$apt['appointment_id']}: {$apt['patient_name']} at {$apt['branch_name']} on {$apt['appointment_datetime']}");
                }
                if (count($appointmentsWithoutRecords) > 10) {
                    CLI::write("... and " . (count($appointmentsWithoutRecords) - 10) . " more");
                }
            }
            
            CLI::newLine();
            
            // Check dental charts
            CLI::write("4. Dental Charts Status:");
            CLI::write(str_repeat("-", 80));
            
            $chartsQuery = "
                SELECT 
                    COUNT(*) as total_charts,
                    COUNT(DISTINCT dental_record_id) as records_with_charts
                FROM dental_chart
            ";
            
            $chartStats = $db->query($chartsQuery)->getRow();
            CLI::write("Total dental chart entries: {$chartStats->total_charts}");
            CLI::write("Dental records with charts: {$chartStats->records_with_charts}");
            
            // Check if our test record has a chart
            $testRecordChartQuery = "
                SELECT dr.id, dr.user_id, COUNT(dc.id) as chart_count
                FROM dental_record dr
                LEFT JOIN dental_chart dc ON dc.dental_record_id = dr.id
                WHERE dr.id = 47
                GROUP BY dr.id
            ";
            
            $testChart = $db->query($testRecordChartQuery)->getRow();
            if ($testChart) {
                CLI::write("Test record (ID 47) has {$testChart->chart_count} chart entries");
            }

        } catch (\Exception $e) {
            CLI::error("ERROR: " . $e->getMessage());
        }
    }
}
