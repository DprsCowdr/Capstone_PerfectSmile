<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class UpdateDrJohnSmith extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:update-john-smith';
    protected $description = 'Update Dr. John Smith to Dr. Minnie Gonowon';

    public function run(array $params)
    {
        CLI::write("Updating Dr. John Smith to Dr. Minnie Gonowon...", 'blue');
        
        try {
            $db = Database::connect();
            
            // Update the Dr. John Smith record (ID: 2)
            $updateData = [
                'name' => 'Dr. Minnie Gonowon',
                'email' => 'dr.gonowon@perfectsmile.com',
                'phone' => '09171234567',
                'address' => 'Perfect Smile Dental Clinic, Main Branch',
                'date_of_birth' => '1980-03-15',
                'gender' => 'female',
                'user_type' => 'doctor'
            ];
            
            $result = $db->table('user')->where('id', 2)->update($updateData);
            
            if ($result) {
                CLI::write("✅ Successfully updated Dr. John Smith to Dr. Minnie Gonowon!", 'green');
                
                // Show updated record
                $updated = $db->table('user')->where('id', 2)->get()->getRowArray();
                CLI::write("Updated record: ID: {$updated['id']} | Name: {$updated['name']} | Email: {$updated['email']}", 'yellow');
            } else {
                CLI::write("❌ Failed to update record", 'red');
            }
            
        } catch (\Exception $e) {
            CLI::write("❌ Error: " . $e->getMessage(), 'red');
        }
    }
}
