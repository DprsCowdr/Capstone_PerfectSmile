<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BranchesSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name' => 'Nabua Branch',
                'address' => 'Nabua,Camarines Sur',
                'contact_number' => '+1 (555) 123-4567'
            ],
            [
                'name' => 'Iriga Branch',
                'address' => 'Iriga City,Camarines Sur',
                'contact_number' => '+1 (555) 234-5678'
            ],
        ];

        $this->db->table('branches')->insertBatch($data);
    }
}
