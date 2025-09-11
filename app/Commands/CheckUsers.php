<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CheckUsers extends BaseCommand
{
    protected $group = 'app';
    protected $name = 'app:check-users';
    protected $description = 'Check what users exist in the database';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        CLI::write('=== Users Database Check ===', 'green');
        CLI::newLine();

        try {
            // Check all users
            $users = $db->table('user')->select('id, name, email, user_type, status')->get()->getResultArray();
            
            CLI::write("Total users: " . count($users));
            CLI::newLine();

            if (count($users) > 0) {
                CLI::write("User details:");
                CLI::write(str_repeat("-", 80));
                CLI::write(sprintf("%-5s %-20s %-25s %-12s %-10s", 
                           "ID", "Name", "Email", "Type", "Status"));
                CLI::write(str_repeat("-", 80));

                foreach ($users as $user) {
                    CLI::write(sprintf("%-5s %-20s %-25s %-12s %-10s",
                               $user['id'],
                               substr($user['name'] ?: 'N/A', 0, 19),
                               substr($user['email'] ?: 'N/A', 0, 24),
                               $user['user_type'] ?: 'N/A',
                               $user['status'] ?: 'N/A'
                    ));
                }
                CLI::write(str_repeat("-", 80));

                // Count by type
                $typeCount = [];
                foreach ($users as $user) {
                    $type = $user['user_type'] ?: 'unknown';
                    $typeCount[$type] = ($typeCount[$type] ?? 0) + 1;
                }

                CLI::newLine();
                CLI::write("Users by type:");
                foreach ($typeCount as $type => $count) {
                    CLI::write("- $type: $count");
                }
            }

            CLI::newLine();

            // Check appointments
            $appointments = $db->table('appointments')->select('id, user_id, branch_id, status')->limit(5)->get()->getResultArray();
            CLI::write("Sample appointments: " . count($appointments));
            if (count($appointments) > 0) {
                CLI::write(str_repeat("-", 50));
                CLI::write(sprintf("%-5s %-10s %-12s %-15s", "ID", "User ID", "Branch ID", "Status"));
                CLI::write(str_repeat("-", 50));
                foreach ($appointments as $apt) {
                    CLI::write(sprintf("%-5s %-10s %-12s %-15s",
                               $apt['id'],
                               $apt['user_id'],
                               $apt['branch_id'] ?: 'NULL',
                               $apt['status'] ?: 'N/A'
                    ));
                }
                CLI::write(str_repeat("-", 50));
            }

        } catch (\Exception $e) {
            CLI::error("ERROR: " . $e->getMessage());
        }
    }
}
