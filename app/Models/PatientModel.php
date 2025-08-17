<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\PatientMedicalHistoryModel;

class PatientModel extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'id';
    
    protected $allowedFields = array(
        'name', 'email', 'phone', 'gender', 'address', 'date_of_birth',
        'previous_dentist', 'last_dental_visit', 'physician_name',
        // Consolidated fields
        'current_treatment', 'hospitalization_details',
        // Kept details
        'illness_details', 'allergies', 'other_conditions',
        'special_notes', 'medical_history_updated_at'
    );
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Update patient medical history
     */
    public function updateMedicalHistory($userId, $medicalData)
    {
        // Normalize JSON field
        if (isset($medicalData['medical_conditions']) && is_array($medicalData['medical_conditions'])) {
            $medicalData['medical_conditions'] = json_encode(array_values($medicalData['medical_conditions']));
        }

        $mh = new PatientMedicalHistoryModel();
        $existing = $mh->where('user_id', $userId)->first();

        if ($existing) {
            return (bool) $mh->update($existing['id'], $medicalData);
        }

        $medicalData['user_id'] = $userId;
        return (bool) $mh->insert($medicalData);
    }

    /**
     * Get patient with medical history
     */
    public function getPatientWithMedicalHistory($patientId)
    {
        $patient = $this->find($patientId);
        if (!$patient) {
            return null;
        }

        // Fetch raw row to avoid automatic JSON casting issues
        $db = \Config\Database::connect();
        $history = $db->table('patient_medical_history')
            ->where('user_id', $patientId)
            ->get()
            ->getRowArray();
        if ($history) {
            // Normalize medical_conditions into an array
            if (array_key_exists('medical_conditions', $history)) {
                $mc = $history['medical_conditions'];
                if (is_string($mc) && $mc !== '') {
                    try {
                        $decoded = json_decode($mc, true, 512, JSON_THROW_ON_ERROR);
                        // If decoded is a string (e.g., "\"foo\""), wrap into array
                        if (is_string($decoded)) {
                            $history['medical_conditions'] = [$decoded];
                        } elseif (is_array($decoded)) {
                            $history['medical_conditions'] = $decoded;
                        } else {
                            $history['medical_conditions'] = [];
                        }
                    } catch (\Throwable $e) {
                        // Fallback: if comma-separated or plain string
                        $history['medical_conditions'] = strpos($mc, ',') !== false
                            ? array_map('trim', explode(',', $mc))
                            : ($mc !== '' ? [$mc] : []);
                    }
                } elseif (is_array($mc)) {
                    // already array
                } else {
                    $history['medical_conditions'] = [];
                }
            }
            $patient = array_merge($patient, $history);
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
