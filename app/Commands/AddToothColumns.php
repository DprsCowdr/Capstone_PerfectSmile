<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class AddToothColumns extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:add-tooth-columns';
    protected $description = 'Add tooth_number, surface, and notes columns to appointment_service table';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        
        // Check if columns already exist
        $query = $db->query("SHOW COLUMNS FROM appointment_service LIKE 'tooth_number'");
        $result = $query->getResult();
        
        if (count($result) == 0) {
            // Add the columns
            $sql = "ALTER TABLE appointment_service 
                    ADD COLUMN tooth_number VARCHAR(5) NULL AFTER service_id,
                    ADD COLUMN surface VARCHAR(20) NULL AFTER tooth_number,
                    ADD COLUMN notes TEXT NULL AFTER surface";
            
            if ($db->query($sql)) {
                CLI::write('Columns added successfully to appointment_service table', 'green');
            } else {
                CLI::write('Error adding columns: ' . $db->error(), 'red');
            }
        } else {
            CLI::write('Columns already exist in appointment_service table', 'yellow');
        }
    }
}
