<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnhanceProceduresTable extends Migration
{
    public function up()
    {
        // Check if columns already exist before adding them
        $db = \Config\Database::connect();
        
        // Get existing columns
        $existingColumns = $db->getFieldNames('procedures');
        
        // Add new columns only if they don't exist
        if (!in_array('title', $existingColumns)) {
            $this->forge->addColumn('procedures', [
                'title' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'procedure_name'
                ]
            ]);
        }
        
        if (!in_array('category', $existingColumns)) {
            $this->forge->addColumn('procedures', [
                'category' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                    'default' => 'none',
                    'after' => 'description'
                ]
            ]);
        }
        
        if (!in_array('fee', $existingColumns)) {
            $this->forge->addColumn('procedures', [
                'fee' => [
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'null' => true,
                    'after' => 'category'
                ]
            ]);
        }
        
        if (!in_array('treatment_area', $existingColumns)) {
            $this->forge->addColumn('procedures', [
                'treatment_area' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                    'default' => 'Surface',
                    'after' => 'fee'
                ]
            ]);
        }
        
        if (!in_array('status', $existingColumns)) {
            $this->forge->addColumn('procedures', [
                'status' => [
                    'type' => 'ENUM',
                    'constraint' => ['scheduled', 'in_progress', 'completed', 'cancelled'],
                    'default' => 'scheduled',
                    'after' => 'treatment_area'
                ]
            ]);
        }
        
        if (!in_array('created_at', $existingColumns)) {
            $this->forge->addColumn('procedures', [
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'status'
                ]
            ]);
        }
        
        if (!in_array('updated_at', $existingColumns)) {
            $this->forge->addColumn('procedures', [
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'created_at'
                ]
            ]);
        }

        // Add indexes for better performance (only if they don't exist)
        try {
            $db->query('ALTER TABLE procedures ADD INDEX idx_category (category)');
        } catch (\Exception $e) {
            // Index might already exist, ignore error
        }
        
        try {
            $db->query('ALTER TABLE procedures ADD INDEX idx_status (status)');
        } catch (\Exception $e) {
            // Index might already exist, ignore error
        }
        
        try {
            $db->query('ALTER TABLE procedures ADD INDEX idx_procedure_date (procedure_date)');
        } catch (\Exception $e) {
            // Index might already exist, ignore error
        }
    }

    public function down()
    {
        // Remove the added columns
        $this->forge->dropColumn('procedures', [
            'title',
            'category', 
            'fee',
            'treatment_area',
            'status',
            'created_at',
            'updated_at'
        ]);

        // Remove indexes
        $db = \Config\Database::connect();
        try {
            $db->query('ALTER TABLE procedures DROP INDEX idx_category');
        } catch (\Exception $e) {
            // Index might not exist, ignore error
        }
        
        try {
            $db->query('ALTER TABLE procedures DROP INDEX idx_status');
        } catch (\Exception $e) {
            // Index might not exist, ignore error
        }
        
        try {
            $db->query('ALTER TABLE procedures DROP INDEX idx_procedure_date');
        } catch (\Exception $e) {
            // Index might not exist, ignore error
        }
    }
}
