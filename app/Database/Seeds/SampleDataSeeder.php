<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // Sample Services
        $services = [
            [
                'name' => 'Dental Checkup',
                'description' => 'Comprehensive dental examination and cleaning',
                'price' => 75.00
            ],
            [
                'name' => 'Teeth Whitening',
                'description' => 'Professional teeth whitening treatment',
                'price' => 150.00
            ],
            [
                'name' => 'Cavity Filling',
                'description' => 'Dental filling for cavities',
                'price' => 120.00
            ],
            [
                'name' => 'Root Canal',
                'description' => 'Root canal treatment',
                'price' => 800.00
            ],
            [
                'name' => 'Dental Crown',
                'description' => 'Dental crown placement',
                'price' => 600.00
            ],
            [
                'name' => 'Tooth Extraction',
                'description' => 'Simple tooth extraction',
                'price' => 200.00
            ]
        ];

        foreach ($services as $service) {
            $db->table('services')->insert($service);
        }

        // Sample Branches
        $branches = [
            [
                'name' => 'Perfect Smile - Main Branch',
                'address' => '123 Main Street, Downtown, City Center',
                'contact_number' => '+1 (555) 123-4567'
            ],
            [
                'name' => 'Perfect Smile - North Branch',
                'address' => '456 North Avenue, North District',
                'contact_number' => '+1 (555) 234-5678'
            ],
            [
                'name' => 'Perfect Smile - South Branch',
                'address' => '789 South Boulevard, South District',
                'contact_number' => '+1 (555) 345-6789'
            ],
            [
                'name' => 'Perfect Smile - East Branch',
                'address' => '321 East Road, East District',
                'contact_number' => '+1 (555) 456-7890'
            ],
            [
                'name' => 'Perfect Smile - West Branch',
                'address' => '654 West Street, West District',
                'contact_number' => '+1 (555) 567-8901'
            ]
        ];

        foreach ($branches as $branch) {
            $db->table('branches')->insert($branch);
        }
    }
} 