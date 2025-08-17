<?php

namespace App\Models;

use CodeIgniter\Model;

class PatientModel extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'id';
    
    protected $allowedFields = array(
        'name', 'email', 'phone', 'gender', 'address', 'date_of_birth',
        'previous_dentist', 'last_dental_visit', 'physician_name', 'physician_specialty',
        'physician_phone', 'physician_address', 'good_health', 'under_treatment',
        'treatment_condition', 'serious_illness', 'illness_details', 'hospitalized',
        'hospitalization_where', 'hospitalization_when', 'hospitalization_why',
        'tobacco_use', 'blood_pressure', 'allergies', 'pregnant', 'nursing',
        'birth_control', 'other_conditions', 'medical_history_updated_at'
    );
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Update patient medical history
     */
    public function updateMedicalHistory($patientId, $medicalData)
    {
        $medicalData['medical_history_updated_at'] = date('Y-m-d H:i:s');
        
        if (isset($medicalData['medical_conditions']) && is_array($medicalData['medical_conditions'])) {
            $medicalData['medical_conditions'] = json_encode($medicalData['medical_conditions']);
        }

        return $this->update($patientId, $medicalData);
    }

    /**
     * Get patient with medical history
     */
    public function getPatientWithMedicalHistory($patientId)
    {
        $patient = $this->find($patientId);
        
        if ($patient && !empty($patient['medical_conditions'])) {
            $patient['medical_conditions'] = json_decode($patient['medical_conditions'], true);
        }
        
        return $patient;
    }

    /**
     * Get patient by email
     */
    public function getPatientByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Get or create patient from appointment data
     */
    public function getOrCreatePatient($appointmentData)
    {
        $patient = $this->getPatientByEmail($appointmentData['patient_email']);
        
        if ($patient) {
            return $patient;
        }

        if (isset($appointmentData['user_id'])) {
            return $this->find($appointmentData['user_id']);
        }

        return null;
    }

    /**
     * Get all patients (users with patient role)
     */
    public function getAllPatients()
    {
        return $this->where('user_type', 'patient')->findAll();
    }
}
