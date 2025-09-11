<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['name' => 'admin', 'description' => 'Administrator with full access', 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'staff', 'description' => 'Front desk and operational staff', 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'dentist', 'description' => 'Dental practitioner', 'created_at' => date('Y-m-d H:i:s')],
        ];

        foreach ($roles as $r) {
            $this->db->table('roles')->insert($r);
        }
    }
}
