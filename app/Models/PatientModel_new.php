<?php

namespace App\Models;

use CodeIgniter\Model;

class PatientModel extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'name', 'email', 'phone', 'gender', 'address', 'date_of_birth',
        // Medical History Fields
        'previous_dentist', 'last_dental_visit', 'physician_name', 'physician_specialty',
        'physician_phone', 'physician_address', 'good_health', 'under_treatment',
        'treatment_condition', 'serious_illness', 'illness_details', 'hospitalized',
        'hospitalization_where', 'hospitalization_when', 'hospitalization_why',
        'tobacco_use', 'blood_pressure', 'allergies', 'pregnant', 'nursing',
        'birth_control', 'medical_conditions', 'other_conditions', 'medical_history_updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
        'email' => 'required|valid_email|is_unique[user.email,id,{id}]',
        'phone' => 'required|min_length[10]|max_length[15]',
    ];

    protected $validationMessages = [
        'email' => [
            'is_unique' => 'This email is already registered.'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Define casts for automatic type conversion
    protected $casts = [
        'medical_conditions' => 'json'
    ];

    protected $castHandlers = [];

    // Events
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Update patient medical history
     */
    public function updateMedicalHistory($patientId, $medicalData)
    {
        // Add timestamp for medical history update
        $medicalData['medical_history_updated_at'] = date('Y-m-d H:i:s');
        
        // Convert medical conditions array to JSON
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
        // Check if patient exists by email
        $patient = $this->getPatientByEmail($appointmentData['patient_email']);
        
        if ($patient) {
            return $patient;
        }

        // For this system, patients are users, so we should find by user_id instead
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
