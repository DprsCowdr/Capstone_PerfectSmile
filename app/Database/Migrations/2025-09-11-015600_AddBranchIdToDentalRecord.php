<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBranchIdToDentalRecord extends Migration
{
    public function up()
    {
        // Add branch_id column to dental_record table
        $this->forge->addColumn('dental_record', [
            'branch_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'appointment_id'
            ]
        ]);

        // Add foreign key constraint (optional, but recommended)
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'SET NULL', 'CASCADE', 'dental_record_branch_fk');
    }

    public function down()
    {
        // Drop foreign key first, then column
        $this->forge->dropForeignKey('dental_record', 'dental_record_branch_fk');
        $this->forge->dropColumn('dental_record', 'branch_id');
    }
}
