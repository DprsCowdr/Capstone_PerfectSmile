<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMedicalHistoryFields extends Migration
{
    public function up()
    {
        // Add medical history fields to user table (patients are stored in user table)
        $fields = [
            // Dental History
            'previous_dentist' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'last_dental_visit' => [
                'type' => 'DATE',
                'null' => true,
            ],
            
            // Medical History
            'physician_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'physician_specialty' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'physician_phone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'physician_address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            
            // Health Questions
            'good_health' => [
                'type' => 'ENUM',
                'constraint' => ['yes', 'no'],
                'null' => true,
            ],
            'under_treatment' => [
                'type' => 'ENUM',
                'constraint' => ['yes', 'no'],
                'null' => true,
            ],
            'treatment_condition' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'serious_illness' => [
                'type' => 'ENUM',
                'constraint' => ['yes', 'no'],
                'null' => true,
            ],
            'illness_details' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'hospitalized' => [
                'type' => 'ENUM',
                'constraint' => ['yes', 'no'],
                'null' => true,
            ],
            'hospitalization_where' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'hospitalization_when' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'hospitalization_why' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'tobacco_use' => [
                'type' => 'ENUM',
                'constraint' => ['yes', 'no'],
                'null' => true,
            ],
            'blood_pressure' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'allergies' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            
            // For Women Only
            'pregnant' => [
                'type' => 'ENUM',
                'constraint' => ['yes', 'no', 'na'],
                'null' => true,
            ],
            'nursing' => [
                'type' => 'ENUM',
                'constraint' => ['yes', 'no', 'na'],
                'null' => true,
            ],
            'birth_control' => [
                'type' => 'ENUM',
                'constraint' => ['yes', 'no', 'na'],
                'null' => true,
            ],
            
            // Medical Conditions
            'medical_conditions' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'other_conditions' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'medical_history_updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ];

        $this->forge->addColumn('user', $fields);
    }

    public function down()
    {
        $columns = [
            'previous_dentist', 'last_dental_visit', 'physician_name', 'physician_specialty',
            'physician_phone', 'physician_address', 'good_health', 'under_treatment',
            'treatment_condition', 'serious_illness', 'illness_details', 'hospitalized',
            'hospitalization_where', 'hospitalization_when', 'hospitalization_why',
            'tobacco_use', 'blood_pressure', 'allergies', 'pregnant', 'nursing',
            'birth_control', 'medical_conditions', 'other_conditions', 'medical_history_updated_at'
        ];

        $this->forge->dropColumn('user', $columns);
    }
}
