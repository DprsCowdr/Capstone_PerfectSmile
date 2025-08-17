<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNextAppointmentIdToDentalRecord extends Migration
{
    public function up()
    {
        // Add column next_appointment_id (nullable) if missing
        try {
            $exists = $this->db->query("SHOW COLUMNS FROM `dental_record` LIKE 'next_appointment_id'")
                               ->getResultArray();
        } catch (\Throwable $e) {
            $exists = [];
        }

        if (empty($exists)) {
            $fields = [
                'next_appointment_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                    'after' => 'next_appointment_date'
                ],
            ];
            $this->forge->addColumn('dental_record', $fields);
        }

        // Add foreign key constraint (safe attempt)
        try {
            $this->db->query('ALTER TABLE `dental_record` ADD CONSTRAINT `fk_dental_record_next_appointment` FOREIGN KEY (`next_appointment_id`) REFERENCES `appointments`(`id`) ON DELETE SET NULL ON UPDATE CASCADE');
        } catch (\Exception $e) {
            log_message('error', 'Could not add foreign key fk_dental_record_next_appointment: ' . $e->getMessage());
        }
    }

    public function down()
    {
        try {
            $this->db->query('ALTER TABLE `dental_record` DROP FOREIGN KEY `fk_dental_record_next_appointment`');
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            $exists = $this->db->query("SHOW COLUMNS FROM `dental_record` LIKE 'next_appointment_id'")
                               ->getResultArray();
            if (!empty($exists)) {
                $this->forge->dropColumn('dental_record', 'next_appointment_id');
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
