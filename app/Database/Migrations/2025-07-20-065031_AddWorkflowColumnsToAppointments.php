<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWorkflowColumnsToAppointments extends Migration
{
    public function up()
    {
        $this->forge->addColumn('appointments', [
            'checked_in_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'When patient checked in'
            ],
            'checked_in_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'Staff who checked in the patient'
            ],
            'self_checkin' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Whether patient checked in themselves'
            ],
            'started_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'When treatment started'
            ],
            'called_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'Dentist who called the patient'
            ],
            'treatment_status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'Current treatment status'
            ],
            'treatment_notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Treatment progress notes'
            ],
            'payment_status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'paid', 'partial', 'waived'],
                'default' => 'pending',
                'comment' => 'Payment status'
            ],
            'payment_method' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'Payment method used'
            ],
            'payment_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
                'comment' => 'Amount paid'
            ],
            'payment_date' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'When payment was made'
            ],
            'payment_received_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'Staff who received payment'
            ],
            'payment_notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Payment notes'
            ]
        ]);

        // Add foreign key constraints
        $this->forge->addForeignKey('checked_in_by', 'user', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('called_by', 'user', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('payment_received_by', 'user', 'id', 'SET NULL', 'SET NULL');
    }

    public function down()
    {
        $this->forge->dropColumn('appointments', [
            'checked_in_at',
            'checked_in_by',
            'self_checkin',
            'started_at',
            'called_by',
            'treatment_status',
            'treatment_notes',
            'payment_status',
            'payment_method',
            'payment_amount',
            'payment_date',
            'payment_received_by',
            'payment_notes'
        ]);
    }
}
