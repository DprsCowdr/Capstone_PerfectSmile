<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTreatmentSessionsTable extends Migration
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
            'appointment_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'started_at' => [
                'type' => 'DATETIME',
                'null' => false,
                'comment' => 'When treatment started',
            ],
            'ended_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'When treatment ended',
            ],
            'called_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'Dentist who called the patient',
            ],
            'dentist_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'Primary dentist for this session',
            ],
            'treatment_status' => [
                'type' => 'ENUM',
                'constraint' => ['in_progress', 'completed', 'paused', 'cancelled'],
                'default' => 'in_progress',
                'comment' => 'Current treatment status',
            ],
            'treatment_notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Treatment progress notes',
            ],
            'priority' => [
                'type' => 'ENUM',
                'constraint' => ['low', 'normal', 'high', 'urgent'],
                'default' => 'normal',
            ],
            'room_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'duration_minutes' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'Actual treatment duration in minutes',
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
        $this->forge->addKey('appointment_id');
        $this->forge->addKey('called_by');
        $this->forge->addKey('dentist_id');
        $this->forge->addKey('treatment_status');
        
        // Foreign key constraints
        $this->forge->addForeignKey('appointment_id', 'appointments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('called_by', 'user', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('dentist_id', 'user', 'id', 'SET NULL', 'CASCADE');
        
        $this->forge->createTable('treatment_sessions');
    }

    public function down()
    {
        $this->forge->dropTable('treatment_sessions');
    }
}
