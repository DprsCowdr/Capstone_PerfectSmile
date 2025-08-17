<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePaymentsTable extends Migration
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
            'patient_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'payment_status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'paid', 'partial', 'waived', 'refunded'],
                'default' => 'pending',
                'comment' => 'Payment status',
            ],
            'payment_method' => [
                'type' => 'ENUM',
                'constraint' => ['cash', 'card', 'bank_transfer', 'gcash', 'paymaya', 'insurance'],
                'null' => true,
                'comment' => 'Payment method used',
            ],
            'total_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => false,
                'default' => '0.00',
                'comment' => 'Total amount due',
            ],
            'paid_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => false,
                'default' => '0.00',
                'comment' => 'Amount actually paid',
            ],
            'balance_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => false,
                'default' => '0.00',
                'comment' => 'Remaining balance',
            ],
            'payment_date' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'When payment was made',
            ],
            'payment_received_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'Staff who received payment',
            ],
            'payment_notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Payment notes',
            ],
            'invoice_number' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'receipt_number' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'transaction_reference' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'External payment reference (e.g., bank transaction ID)',
            ],
            'discount_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => '0.00',
            ],
            'discount_reason' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
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
        $this->forge->addKey('appointment_id');
        $this->forge->addKey('patient_id');
        $this->forge->addKey('payment_received_by');
        $this->forge->addKey('payment_status');
        $this->forge->addKey('invoice_number');
        
        // Foreign key constraints
        $this->forge->addForeignKey('appointment_id', 'appointments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('patient_id', 'user', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('payment_received_by', 'user', 'id', 'SET NULL', 'CASCADE');
        
        $this->forge->createTable('payments');
    }

    public function down()
    {
        $this->forge->dropTable('payments');
    }
}
