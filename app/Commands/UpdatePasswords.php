<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class UpdatePasswords extends BaseCommand
{
    protected $group       = 'Auth';
    protected $name        = 'auth:update-passwords';
    protected $description = 'Update user passwords with proper hashing';

    public function run(array $params)
    {
        $db = Database::connect();
        
        // Update admin password
        $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
        $db->query("UPDATE user SET password = ? WHERE id = 1", [$adminHash]);
        CLI::write("Updated admin password", 'yellow');
        
        // Update doctor password
        $doctorHash = password_hash('doctor123', PASSWORD_DEFAULT);
        $db->query("UPDATE user SET password = ? WHERE id = 2", [$doctorHash]);
        CLI::write("Updated doctor password", 'yellow');
        
        // Update patient password
        $patientHash = password_hash('patient123', PASSWORD_DEFAULT);
        $db->query("UPDATE user SET password = ? WHERE id = 3", [$patientHash]);
        CLI::write("Updated patient password", 'yellow');
        
        // Update staff password
        $staffHash = password_hash('staff123', PASSWORD_DEFAULT);
        $db->query("UPDATE user SET password = ? WHERE id = 4", [$staffHash]);
        CLI::write("Updated staff password", 'yellow');
        
        CLI::write('All passwords have been updated!', 'green');
    }
} 