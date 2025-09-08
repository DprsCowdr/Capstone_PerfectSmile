<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveDiagnosisColumn extends Migration
{
    public function up()
    {
        // Remove diagnosis column from dental_record table
        $this->forge->dropColumn('dental_record', 'diagnosis');
    }

    public function down()
    {
        // Add diagnosis column back if rollback is needed
        $fields = [
            'diagnosis' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'record_date'
            ]
        ];
        
        $this->forge->addColumn('dental_record', $fields);
    }
}
