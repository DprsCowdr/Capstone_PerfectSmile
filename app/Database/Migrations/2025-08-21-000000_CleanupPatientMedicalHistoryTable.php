<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CleanupPatientMedicalHistoryTable extends Migration
{
    public function up()
    {
        // Drop unnecessary columns that are duplicates or not needed
        $this->forge->dropColumn('patient_medical_history', [
            'previous_dentist_id',      // Not used
            'dentist_name',             // Duplicate of physician_name
            'dentist_specialty',        // Duplicate of physician_specialty
            'dentist_phone',            // Duplicate of physician_phone
            'dentist_address',          // Duplicate of physician_address
            'special_notes',            // Not used
            'medical_history_updated_at' // Redundant with updated_at
        ]);
    }

    public function down()
    {
        // Add back the columns if needed to rollback
        $fields = [
            'previous_dentist_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'dentist_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'dentist_specialty' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'dentist_phone' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'dentist_address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'special_notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'medical_history_updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ];

        foreach ($fields as $fieldName => $fieldDef) {
            $this->forge->addColumn('patient_medical_history', [$fieldName => $fieldDef]);
        }
    }
}
