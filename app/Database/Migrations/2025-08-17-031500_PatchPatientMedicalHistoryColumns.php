<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PatchPatientMedicalHistoryColumns extends Migration
{
    private string $table = 'patient_medical_history';

    public function up()
    {
        // If table doesn't exist, nothing to patch (creation handled by earlier migration)
        try {
            $exists = $this->db->query('SHOW TABLES LIKE "' . $this->table . '"')->getResultArray();
        } catch (\Throwable $e) {
            $exists = [];
        }
        if (empty($exists)) {
            return;
        }

        $missing = [];
        $addIfMissing = function (string $name, array $definition) use (&$missing) {
            $col = $this->db->query('SHOW COLUMNS FROM `' . $this->table . '` LIKE ' . $this->db->escape($name))->getResultArray();
            if (empty($col)) {
                $missing[$name] = $definition;
            }
        };

        // Define all intended columns and add those missing
        $addIfMissing('previous_dentist', [ 'type' => 'VARCHAR', 'constraint' => 255, 'null' => true ]);
        $addIfMissing('last_dental_visit', [ 'type' => 'DATE', 'null' => true ]);
        $addIfMissing('physician_name', [ 'type' => 'VARCHAR', 'constraint' => 255, 'null' => true ]);
        $addIfMissing('physician_specialty', [ 'type' => 'VARCHAR', 'constraint' => 255, 'null' => true ]);
        $addIfMissing('physician_phone', [ 'type' => 'VARCHAR', 'constraint' => 20, 'null' => true ]);
        $addIfMissing('physician_address', [ 'type' => 'TEXT', 'null' => true ]);

        $addIfMissing('good_health', [ 'type' => 'ENUM', 'constraint' => ['yes','no'], 'null' => true ]);
        $addIfMissing('under_treatment', [ 'type' => 'ENUM', 'constraint' => ['yes','no'], 'null' => true ]);
        $addIfMissing('treatment_condition', [ 'type' => 'TEXT', 'null' => true ]);
        $addIfMissing('serious_illness', [ 'type' => 'ENUM', 'constraint' => ['yes','no'], 'null' => true ]);
        $addIfMissing('illness_details', [ 'type' => 'TEXT', 'null' => true ]);
        $addIfMissing('hospitalized', [ 'type' => 'ENUM', 'constraint' => ['yes','no'], 'null' => true ]);
        $addIfMissing('hospitalization_where', [ 'type' => 'VARCHAR', 'constraint' => 255, 'null' => true ]);
        $addIfMissing('hospitalization_when', [ 'type' => 'VARCHAR', 'constraint' => 255, 'null' => true ]);
        $addIfMissing('hospitalization_why', [ 'type' => 'VARCHAR', 'constraint' => 255, 'null' => true ]);
        $addIfMissing('tobacco_use', [ 'type' => 'ENUM', 'constraint' => ['yes','no'], 'null' => true ]);
        $addIfMissing('blood_pressure', [ 'type' => 'VARCHAR', 'constraint' => 20, 'null' => true ]);
        $addIfMissing('allergies', [ 'type' => 'TEXT', 'null' => true ]);

        $addIfMissing('pregnant', [ 'type' => 'ENUM', 'constraint' => ['yes','no','na'], 'null' => true ]);
        $addIfMissing('nursing', [ 'type' => 'ENUM', 'constraint' => ['yes','no','na'], 'null' => true ]);
        $addIfMissing('birth_control', [ 'type' => 'ENUM', 'constraint' => ['yes','no','na'], 'null' => true ]);

        // MySQL versions <5.7 won't support JSON; but our base migration used JSON. We'll match that.
        $addIfMissing('medical_conditions', [ 'type' => 'JSON', 'null' => true ]);
        $addIfMissing('other_conditions', [ 'type' => 'TEXT', 'null' => true ]);

        $addIfMissing('current_treatment', [ 'type' => 'TEXT', 'null' => true ]);
        $addIfMissing('hospitalization_details', [ 'type' => 'TEXT', 'null' => true ]);
        $addIfMissing('special_notes', [ 'type' => 'TEXT', 'null' => true ]);

        // Timestamps
        $addIfMissing('created_at', [ 'type' => 'DATETIME', 'null' => true ]);
        $addIfMissing('updated_at', [ 'type' => 'DATETIME', 'null' => true ]);

        if (!empty($missing)) {
            $this->forge->addColumn($this->table, $missing);
        }
    }

    public function down()
    {
        // Drop only the columns we added if they exist
        $maybeDrop = [
            'previous_dentist','last_dental_visit','physician_name','physician_specialty','physician_phone','physician_address',
            'good_health','under_treatment','treatment_condition','serious_illness','illness_details','hospitalized','hospitalization_where','hospitalization_when','hospitalization_why','tobacco_use','blood_pressure','allergies',
            'pregnant','nursing','birth_control',
            'medical_conditions','other_conditions',
            'current_treatment','hospitalization_details','special_notes',
            'created_at','updated_at',
        ];
        foreach ($maybeDrop as $colName) {
            try {
                $col = $this->db->query('SHOW COLUMNS FROM `' . $this->table . '` LIKE ' . $this->db->escape($colName))->getResultArray();
                if (!empty($col)) {
                    $this->forge->dropColumn($this->table, $colName);
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }
}
