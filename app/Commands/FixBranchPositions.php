<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class FixBranchPositions extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'fix:branch-positions';
    protected $description = 'Fix "None" positions in branch_user table';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        
        // Get all branch_user records with "None" position
        $query = $db->query("SELECT bu.*, u.user_type FROM branch_user bu 
                            JOIN user u ON bu.user_id = u.id 
                            WHERE bu.position = 'None'");
        $records = $query->getResultArray();
        
        CLI::write("Found " . count($records) . " records with 'None' position", 'yellow');
        
        if (empty($records)) {
            CLI::write("No records to fix!", 'green');
            return;
        }
        
        foreach ($records as $record) {
            $newPosition = 'Staff'; // Default
            
            // Set appropriate position based on user type
            switch ($record['user_type']) {
                case 'admin':
                    $newPosition = 'Administrator';
                    break;
                case 'dentist':
                    $newPosition = 'Dentist';
                    break;
                case 'staff':
                    $newPosition = 'Staff';
                    break;
            }
            
            // Update the position
            $updateQuery = $db->query("UPDATE branch_user SET position = ? WHERE id = ?", 
                                     [$newPosition, $record['id']]);
            
            CLI::write("Updated user ID {$record['user_id']} ({$record['user_type']}) position from 'None' to '{$newPosition}'", 'green');
        }
        
        CLI::write("\nAll 'None' positions have been fixed!", 'green');
    }
} 