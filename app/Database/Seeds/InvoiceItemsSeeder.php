<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InvoiceItemsSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'invoice_id' => 2,
                'procedure_id' => 1,
                'description' => 'Dental Cleaning',
                'quantity' => 1,
                'unit_price' => 150.00,
                'total' => 150.00,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'invoice_id' => 2,
                'procedure_id' => 2,
                'description' => 'Tooth Filling',
                'quantity' => 2,
                'unit_price' => 75.00,
                'total' => 150.00,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'invoice_id' => 3,
                'procedure_id' => 3,
                'description' => 'Root Canal',
                'quantity' => 1,
                'unit_price' => 800.00,
                'total' => 800.00,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert sample invoice items
        $this->db->table('invoice_items')->insertBatch($data);
    }
}
