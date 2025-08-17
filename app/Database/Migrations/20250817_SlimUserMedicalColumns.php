<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SlimUserMedicalColumns extends Migration
{
    protected function columnExists(string $table, string $column): bool
    {
        $result = $this->db->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'")->getResultArray();
        return !empty($result);
    }

    public function up()
    {
        // 1) Add consolidated columns if they don't exist
        $addFields = [];
        if (!$this->columnExists('user', 'current_treatment')) {
            $addFields['current_treatment'] = [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'physician_phone',
            ];
        }
        if (!$this->columnExists('user', 'hospitalization_details')) {
            $addFields['hospitalization_details'] = [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'illness_details',
            ];
        }
        if (!empty($addFields)) {
            $this->forge->addColumn('user', $addFields);
        }

        // 2) Backfill consolidated columns from existing data where possible
        // current_treatment from treatment_condition
        if ($this->columnExists('user', 'treatment_condition') && $this->columnExists('user', 'current_treatment')) {
            $this->db->query('UPDATE `user` SET `current_treatment` = COALESCE(`current_treatment`, `treatment_condition`)');
        }
        // hospitalization_details from hospitalized/where/when/why
        if ($this->columnExists('user', 'hospitalization_details')) {
            $parts = [];
            if ($this->columnExists('user', 'hospitalized')) {
                $parts[] = "CONCAT('Hospitalized: ', COALESCE(hospitalized,''))";
            }
            if ($this->columnExists('user', 'hospitalization_where')) {
                $parts[] = "CONCAT('Where: ', COALESCE(hospitalization_where,''))";
            }
            if ($this->columnExists('user', 'hospitalization_when')) {
                $parts[] = "CONCAT('When: ', COALESCE(hospitalization_when,''))";
            }
            if ($this->columnExists('user', 'hospitalization_why')) {
                $parts[] = "CONCAT('Why: ', COALESCE(hospitalization_why,''))";
            }
            if (!empty($parts)) {
                $concat = 'TRIM(BOTH " | " FROM CONCAT_WS(" | ",' . implode(',', $parts) . '))';
                $this->db->query("UPDATE `user` SET `hospitalization_details` = {$concat} WHERE (`hospitalization_details` IS NULL OR `hospitalization_details` = '')");
            }
        }

        // 3) Drop redundant columns if they exist
        $dropCols = [
            'good_health',
            'under_treatment',
            'treatment_condition',
            'serious_illness',
            'hospitalized',
            'hospitalization_where',
            'hospitalization_when',
            'hospitalization_why',
            'physician_specialty',
            'physician_address',
            'tobacco_use',
            'blood_pressure',
            'pregnant',
            'nursing',
            'birth_control',
        ];
        foreach ($dropCols as $col) {
            if ($this->columnExists('user', $col)) {
                $this->forge->dropColumn('user', $col);
            }
        }
    }

    public function down()
    {
        // Recreate previously dropped columns (without data recovery beyond what we backfilled)
        $reAdd = [];
        if (!$this->columnExists('user', 'good_health')) {
            $reAdd['good_health'] = ['type' => 'ENUM', 'constraint' => ['yes','no'], 'null' => true];
        }
        if (!$this->columnExists('user', 'under_treatment')) {
            $reAdd['under_treatment'] = ['type' => 'ENUM', 'constraint' => ['yes','no'], 'null' => true];
        }
        if (!$this->columnExists('user', 'treatment_condition')) {
            $reAdd['treatment_condition'] = ['type' => 'TEXT', 'null' => true];
        }
        if (!$this->columnExists('user', 'serious_illness')) {
            $reAdd['serious_illness'] = ['type' => 'ENUM', 'constraint' => ['yes','no'], 'null' => true];
        }
        if (!$this->columnExists('user', 'hospitalized')) {
            $reAdd['hospitalized'] = ['type' => 'ENUM', 'constraint' => ['yes','no'], 'null' => true];
        }
        if (!$this->columnExists('user', 'hospitalization_where')) {
            $reAdd['hospitalization_where'] = ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true];
        }
        if (!$this->columnExists('user', 'hospitalization_when')) {
            $reAdd['hospitalization_when'] = ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true];
        }
        if (!$this->columnExists('user', 'hospitalization_why')) {
            $reAdd['hospitalization_why'] = ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true];
        }
        if (!$this->columnExists('user', 'physician_specialty')) {
            $reAdd['physician_specialty'] = ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true];
        }
        if (!$this->columnExists('user', 'physician_address')) {
            $reAdd['physician_address'] = ['type' => 'TEXT', 'null' => true];
        }
        if (!$this->columnExists('user', 'tobacco_use')) {
            $reAdd['tobacco_use'] = ['type' => 'ENUM', 'constraint' => ['yes','no'], 'null' => true];
        }
        if (!$this->columnExists('user', 'blood_pressure')) {
            $reAdd['blood_pressure'] = ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true];
        }
        if (!$this->columnExists('user', 'pregnant')) {
            $reAdd['pregnant'] = ['type' => 'ENUM', 'constraint' => ['yes','no','na'], 'null' => true];
        }
        if (!$this->columnExists('user', 'nursing')) {
            $reAdd['nursing'] = ['type' => 'ENUM', 'constraint' => ['yes','no','na'], 'null' => true];
        }
        if (!$this->columnExists('user', 'birth_control')) {
            $reAdd['birth_control'] = ['type' => 'ENUM', 'constraint' => ['yes','no','na'], 'null' => true];
        }
        if (!empty($reAdd)) {
            $this->forge->addColumn('user', $reAdd);
        }

        // Drop consolidated columns
        if ($this->columnExists('user', 'current_treatment')) {
            $this->forge->dropColumn('user', 'current_treatment');
        }
        if ($this->columnExists('user', 'hospitalization_details')) {
            $this->forge->dropColumn('user', 'hospitalization_details');
        }
    }
}
