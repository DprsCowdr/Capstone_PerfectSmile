<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class CheckDoctors extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:check-doctors';
    protected $description = 'Check current doctors in database';

    public function run(array $params)
    {
        CLI::write("Checking doctors in database...", 'blue');
        
        try {
            $db = Database::connect();
            
            // Get all users with doctor or dentist user_type
            $doctors = $db->table('user')->whereIn('user_type', ['doctor', 'dentist'])->get()->getResultArray();
            
            CLI::write("Current doctors/dentists in database:", 'green');
            CLI::write("=====================================", 'green');
            
            foreach($doctors as $doctor) {
                CLI::write("ID: {$doctor['id']} | Name: {$doctor['name']} | Email: {$doctor['email']} | Type: {$doctor['user_type']}", 'yellow');
            }
            
            CLI::write("\nTotal found: " . count($doctors), 'green');
            
        } catch (\Exception $e) {
            CLI::write("❌ Error: " . $e->getMessage(), 'red');
        }
    }
}
