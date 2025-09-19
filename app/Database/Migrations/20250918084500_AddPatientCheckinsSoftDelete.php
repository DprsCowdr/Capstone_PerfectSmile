<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPatientCheckinsSoftDelete extends Migration
{
    public function up()
    {
        $fields = [
            'removed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'removed_by' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
            ],
            'removed_reason' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
        ];

        $this->forge->addColumn('patient_checkins', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('patient_checkins', 'removed_at');
        $this->forge->dropColumn('patient_checkins', 'removed_by');
        $this->forge->dropColumn('patient_checkins', 'removed_reason');
    }
}
