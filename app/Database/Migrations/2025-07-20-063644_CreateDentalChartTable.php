<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDentalChartTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'dental_record_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'tooth_number' => [
                'type' => 'INT',
                'constraint' => 2,
                'comment' => 'Tooth number (1-32 for permanent teeth, 51-85 for primary teeth)',
            ],
            'tooth_type' => [
                'type' => 'ENUM',
                'constraint' => ['permanent', 'primary'],
                'default' => 'permanent',
            ],
            'condition' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Dental condition (cavity, filling, crown, etc.)',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['healthy', 'needs_treatment', 'treated', 'missing', 'none'],
                'default' => 'healthy',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Additional notes about the tooth',
            ],
            'recommended_service_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Recommended service for treatment',
            ],
            'priority' => [
                'type' => 'ENUM',
                'constraint' => ['low', 'medium', 'high'],
                'default' => 'medium',
            ],
            'estimated_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey('dental_record_id');
        $this->forge->addKey('tooth_number');
        
        $this->forge->createTable('dental_chart');
        
        // Add foreign key constraints
        $this->db->query('ALTER TABLE dental_chart ADD CONSTRAINT fk_dental_chart_record FOREIGN KEY (dental_record_id) REFERENCES dental_record (id) ON DELETE CASCADE ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE dental_chart ADD CONSTRAINT fk_dental_chart_service FOREIGN KEY (recommended_service_id) REFERENCES services (id) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down()
    {
        $this->forge->dropTable('dental_chart');
    }
}
