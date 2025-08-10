<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class UpdateAppointmentDates extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'update:appointment-dates';
    protected $description = 'Update appointment dates to today for testing';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        
        // Update first few appointments to today's date
        $today = date('Y-m-d');
        
        $sql = "UPDATE appointments SET appointment_datetime = CONCAT(?, ' ', TIME(appointment_datetime)), status = 'confirmed' WHERE id IN (1, 2) AND DATE(appointment_datetime) != ?";
        
        $result = $db->query($sql, [$today, $today]);
        
        if ($result) {
            CLI::write('Successfully updated appointments to today\'s date!', 'green');
        } else {
            CLI::write('Failed to update appointments.', 'red');
        }
        
        // Show updated appointments
        $appointments = $db->query("SELECT id, user_id, appointment_datetime, status FROM appointments WHERE DATE(appointment_datetime) = ?", [$today])->getResultArray();
        
        CLI::write('Appointments for today:');
        foreach ($appointments as $apt) {
            CLI::write("ID: {$apt['id']}, User: {$apt['user_id']}, DateTime: {$apt['appointment_datetime']}, Status: {$apt['status']}");
        }
    }
}
