<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class TestConnection extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:test-8889';
    protected $description = 'Test database connection on port 8889';

    public function run(array $params)
    {
        CLI::write("Testing connection to port 8889...", 'blue');
        
        try {
            $db = Database::connect();
            
            CLI::write("✅ Connected successfully!", 'green');
            CLI::write("Host: " . $db->hostname, 'yellow');
            CLI::write("Port: " . $db->port, 'yellow');
            CLI::write("Database: " . $db->database, 'yellow');
            
            // Count users
            $userCount = $db->table('user')->countAllResults();
            CLI::write("Users in database: " . $userCount, 'green');
            
            // Check for admin@gmail.com
            $adminUser = $db->table('user')->where('email', 'admin@gmail.com')->get()->getRow();
            if ($adminUser) {
                CLI::write("✅ admin@gmail.com found!", 'green');
            } else {
                CLI::write("❌ admin@gmail.com not found", 'red');
            }
            
        } catch (\Exception $e) {
            CLI::write("❌ Connection failed: " . $e->getMessage(), 'red');
        }
    }
} 