<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PatientMedicalHistoryDummySeeder extends Seeder
{
    public function run()
    {
        $db = $this->db;

        // Find any patient user or create one
        $user = $db->table('user')->select('id')->where('user_type', 'patient')->get()->getRowArray();
        if (!$user) {
            $now = date('Y-m-d H:i:s');
            $db->table('user')->insert([
                'user_type' => 'patient',
                'name' => 'Dummy Patient',
                'email' => 'dummy.patient+' . time() . '@example.com',
                'phone' => '1234567890',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $userId = (int) $db->insertID();
        } else {
            $userId = (int) $user['id'];
        }

        // Prepare dummy medical history
        $medicalConditions = json_encode(['diabetes', 'asthma']);
        $now = date('Y-m-d H:i:s');
        $data = [
            'user_id' => $userId,
            'previous_dentist' => 'Dr. Example',
            'last_dental_visit' => date('Y-m-d', strtotime('-6 months')),
            'physician_name' => 'Dr. Care',
            'physician_specialty' => 'General Medicine',
            'physician_phone' => '555-0100',
            'physician_address' => '123 Health St, Wellness City',
            'good_health' => 'yes',
            'under_treatment' => 'no',
            'treatment_condition' => null,
            'serious_illness' => 'no',
            'illness_details' => null,
            'hospitalized' => 'no',
            'hospitalization_where' => null,
            'hospitalization_when' => null,
            'hospitalization_why' => null,
            'tobacco_use' => 'no',
            'blood_pressure' => '120/80',
            'allergies' => 'Penicillin',
            'pregnant' => 'na',
            'nursing' => 'na',
            'birth_control' => 'na',
            'medical_conditions' => $medicalConditions,
            'other_conditions' => 'None',
            'current_treatment' => null,
            'hospitalization_details' => null,
            'special_notes' => 'Dummy record for testing',
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // Upsert by user_id (unique)
        $existing = $db->table('patient_medical_history')->where('user_id', $userId)->get()->getRowArray();
        if ($existing) {
            $db->table('patient_medical_history')->where('id', $existing['id'])->update($data);
            echo "Updated dummy medical history for user_id={$userId}\n";
        } else {
            $db->table('patient_medical_history')->insert($data);
            echo "Inserted dummy medical history for user_id={$userId}\n";
        }
    }
}
