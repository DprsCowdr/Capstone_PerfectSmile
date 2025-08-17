<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePatientCheckinsTable extends Migration
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
            'checked_in_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'checked_in_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'Staff who checked in the patient (null if self check-in)',
            ],
            'self_checkin' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Whether patient checked in themselves',
            ],
            'checkin_method' => [
                'type' => 'ENUM',
                'constraint' => ['staff', 'self', 'kiosk'],
                'default' => 'staff',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Check-in notes or special instructions',
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
        $this->forge->addKey('checked_in_by');
        
        // Foreign key constraints
        $this->forge->addForeignKey('appointment_id', 'appointments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('checked_in_by', 'user', 'id', 'SET NULL', 'CASCADE');
        
        $this->forge->createTable('patient_checkins');
    }

    public function down()
    {
        $this->forge->dropTable('patient_checkins');
    }
}
