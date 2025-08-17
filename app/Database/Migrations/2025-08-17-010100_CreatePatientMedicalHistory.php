<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePatientMedicalHistory extends Migration
{
    public function up()
    {
        // Create table only if it doesn't already exist
        try {
            $exists = $this->db->query('SHOW TABLES LIKE "patient_medical_history"')->getResultArray();
        } catch (\Throwable $e) {
            $exists = [];
        }
        if (!empty($exists)) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            // Dental History
            'previous_dentist' => [ 'type' => 'VARCHAR', 'constraint' => 255, 'null' => true ],
            'last_dental_visit' => [ 'type' => 'DATE', 'null' => true ],

            // Physician
            'physician_name' => [ 'type' => 'VARCHAR', 'constraint' => 255, 'null' => true ],
            'physician_specialty' => [ 'type' => 'VARCHAR', 'constraint' => 255, 'null' => true ],
            'physician_phone' => [ 'type' => 'VARCHAR', 'constraint' => 20, 'null' => true ],
            'physician_address' => [ 'type' => 'TEXT', 'null' => true ],

            // Health Questions
            'good_health' => [ 'type' => 'ENUM', 'constraint' => ['yes','no'], 'null' => true ],
            'under_treatment' => [ 'type' => 'ENUM', 'constraint' => ['yes','no'], 'null' => true ],
            'treatment_condition' => [ 'type' => 'TEXT', 'null' => true ],
            'serious_illness' => [ 'type' => 'ENUM', 'constraint' => ['yes','no'], 'null' => true ],
            'illness_details' => [ 'type' => 'TEXT', 'null' => true ],
            'hospitalized' => [ 'type' => 'ENUM', 'constraint' => ['yes','no'], 'null' => true ],
            'hospitalization_where' => [ 'type' => 'VARCHAR', 'constraint' => 255, 'null' => true ],
            'hospitalization_when' => [ 'type' => 'VARCHAR', 'constraint' => 255, 'null' => true ],
            'hospitalization_why' => [ 'type' => 'VARCHAR', 'constraint' => 255, 'null' => true ],
            'tobacco_use' => [ 'type' => 'ENUM', 'constraint' => ['yes','no'], 'null' => true ],
            'blood_pressure' => [ 'type' => 'VARCHAR', 'constraint' => 20, 'null' => true ],
            'allergies' => [ 'type' => 'TEXT', 'null' => true ],

            // Women Only
            'pregnant' => [ 'type' => 'ENUM', 'constraint' => ['yes','no','na'], 'null' => true ],
            'nursing' => [ 'type' => 'ENUM', 'constraint' => ['yes','no','na'], 'null' => true ],
            'birth_control' => [ 'type' => 'ENUM', 'constraint' => ['yes','no','na'], 'null' => true ],

            // Medical Conditions (multi-select)
            'medical_conditions' => [ 'type' => 'JSON', 'null' => true ],
            'other_conditions' => [ 'type' => 'TEXT', 'null' => true ],

            // Consolidated/notes
            'current_treatment' => [ 'type' => 'TEXT', 'null' => true ],
            'hospitalization_details' => [ 'type' => 'TEXT', 'null' => true ],
            'special_notes' => [ 'type' => 'TEXT', 'null' => true ],

            'created_at' => [ 'type' => 'DATETIME', 'null' => true ],
            'updated_at' => [ 'type' => 'DATETIME', 'null' => true ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('user_id');
        $this->forge->createTable('patient_medical_history');

        // Add FK best-effort
        try {
            $this->db->query('ALTER TABLE `patient_medical_history` ADD CONSTRAINT `fk_pmh_user` FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE ON UPDATE CASCADE');
        } catch (\Throwable $e) {
            // ignore
        }
    }

    public function down()
    {
        $this->forge->dropTable('patient_medical_history', true);
    }
}
