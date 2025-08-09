<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $userModel = new \App\Models\UserModel();

        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@perfectsmile.com',
                'password' => 'admin123',
                'user_type' => 'admin',
                'phone' => '1234567890',
                'address' => '123 Admin Street',
                'date_of_birth' => '1990-01-01',
                'gender' => 'male'
            ],
            [
                'name' => 'Dr. Minnie Gonowon',
                'email' => 'dr.gonowon@perfectsmile.com',
                'password' => 'drminnie123',
                'user_type' => 'doctor',
                'phone' => '09171234567',
                'address' => 'Perfect Smile Dental Clinic, Main Branch',
                'date_of_birth' => '1980-03-15',
                'gender' => 'female'
            ],
            [
                'name' => 'Patient Jane Doe',
                'email' => 'patient@perfectsmile.com',
                'password' => 'patient123',
                'user_type' => 'patient',
                'phone' => '1234567892',
                'address' => '789 Patient Road',
                'date_of_birth' => '1995-10-20',
                'gender' => 'female'
            ],
            [
                'name' => 'Staff Member',
                'email' => 'staff@perfectsmile.com',
                'password' => 'staff123',
                'user_type' => 'staff',
                'phone' => '1234567893',
                'address' => '321 Staff Lane',
                'date_of_birth' => '1992-03-12',
                'gender' => 'female'
            ]
        ];

        foreach ($users as $user) {
            // Check if user exists by email
            $existingUser = $userModel->where('email', $user['email'])->first();
            
            if ($existingUser) {
                // Update existing user using update method
                $userModel->update($existingUser['id'], $user);
                echo "Updated user: " . $user['name'] . "\n";
            } else {
                // Create new user
                $userModel->save($user);
                echo "Created user: " . $user['name'] . "\n";
            }
        }
    }
} 