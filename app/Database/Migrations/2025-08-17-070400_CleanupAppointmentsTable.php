<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CleanupAppointmentsTable extends Migration
{
    public function up()
    {
        // Remove the columns that we've moved to other tables
        $fields = [
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
        ];
        
        foreach ($fields as $field) {
            // Check if field exists before trying to drop it
            try {
                $this->forge->dropColumn('appointments', $field);
            } catch (\Exception $e) {
                // Field might not exist, continue with next field
                log_message('info', "Column {$field} might not exist in appointments table: " . $e->getMessage());
            }
        }
    }

    public function down()
    {
        // Re-add the columns if we need to rollback
        $this->forge->addColumn('appointments', [
            'checked_in_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'When patient checked in',
                'after' => 'updated_at'
            ],
            'checked_in_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'Staff who checked in the patient',
                'after' => 'checked_in_at'
            ],
            'self_checkin' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Whether patient checked in themselves',
                'after' => 'checked_in_by'
            ],
            'started_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'When treatment started',
                'after' => 'self_checkin'
            ],
            'called_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'Dentist who called the patient',
                'after' => 'started_at'
            ],
            'treatment_status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'Current treatment status',
                'after' => 'called_by'
            ],
            'treatment_notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Treatment progress notes',
                'after' => 'treatment_status'
            ],
            'payment_status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'paid', 'partial', 'waived'],
                'default' => 'pending',
                'comment' => 'Payment status',
                'after' => 'treatment_notes'
            ],
            'payment_method' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'Payment method used',
                'after' => 'payment_status'
            ],
            'payment_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
                'comment' => 'Amount paid',
                'after' => 'payment_method'
            ],
            'payment_date' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'When payment was made',
                'after' => 'payment_amount'
            ],
            'payment_received_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'Staff who received payment',
                'after' => 'payment_date'
            ],
            'payment_notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Payment notes',
                'after' => 'payment_received_by'
            ]
        ]);
    }
}
