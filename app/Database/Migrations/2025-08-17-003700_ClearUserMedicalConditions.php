<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ClearUserMedicalConditions extends Migration
{
    public function up()
    {
        // Clear the problematic medical_conditions data from user table
        // since we now use patient_medical_history table
        $this->db->query('UPDATE user SET medical_conditions = NULL WHERE medical_conditions IS NOT NULL');
        
        log_message('info', 'Cleared medical_conditions data from user table');
    }

    public function down()
    {
        // Nothing to do - we don't want to restore the problematic data
    }
}
