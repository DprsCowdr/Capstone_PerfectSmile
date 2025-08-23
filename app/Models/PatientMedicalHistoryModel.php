<?php

namespace App\Models;

use CodeIgniter\Model;

class PatientMedicalHistoryModel extends Model
{
    protected $table = 'patient_medical_history';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'user_id',
        'previous_dentist', 'last_dental_visit',
        'physician_name', 'physician_specialty', 'physician_phone', 'physician_address',
        'good_health', 'under_treatment', 'treatment_condition', 'serious_illness',
        'illness_details', 'hospitalized', 'hospitalization_where', 'hospitalization_when', 'hospitalization_why',
        'tobacco_use', 'blood_pressure', 'allergies',
        'pregnant', 'nursing', 'birth_control',
        'medical_conditions', 'other_conditions',
        'created_at', 'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

}
