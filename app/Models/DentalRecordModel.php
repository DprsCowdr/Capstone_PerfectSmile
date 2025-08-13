<?php

namespace App\Models;

use CodeIgniter\Model;

class DentalRecordModel extends Model
{
    protected $table            = 'dental_record';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'patient_id',
        'appointment_id',
        'record_date',
        'diagnosis',
        'treatment',
        'notes',
        'xray_image_url',
        'next_appointment_date',
        'next_appointment_id',
        'dentist_id'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'user_id'     => 'required|integer',
        'record_date' => 'required|valid_date',
        'dentist_id'  => 'required|integer'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Get dental records for a specific patient with appointment info
     */
    public function getPatientRecords($patientId)
    {
        return $this->select('dental_record.*, user.name as dentist_name, appointments.appointment_datetime')
                   ->join('user', 'user.id = dental_record.dentist_id')
                   ->join('appointments', 'appointments.id = dental_record.appointment_id', 'left')
                   ->where('dental_record.user_id', $patientId)
                   ->orderBy('record_date', 'DESC')
                   ->findAll();
    }

    /**
     * Get dental records with complete patient and medical history
     */
    public function getRecordsWithPatientInfo($limit = null, $offset = 0)
    {
        $builder = $this->select('
                dental_record.*, 
                user.name as patient_name, 
                user.email as patient_email,
                user.phone as patient_phone,
                user.previous_dentist,
                user.last_dental_visit,
                user.medical_conditions,
                user.allergies,
                user.blood_pressure,
                user.tobacco_use,
                user.medical_history_updated_at,
                dentist.name as dentist_name, 
                appointments.appointment_datetime,
                next_appt.appointment_datetime as next_appointment_datetime
            ')
            ->join('user', 'user.id = dental_record.user_id')
            ->join('user as dentist', 'dentist.id = dental_record.dentist_id')
            ->join('appointments', 'appointments.id = dental_record.appointment_id', 'left')
            ->join('appointments as next_appt', 'next_appt.id = dental_record.next_appointment_id', 'left')
            ->orderBy('dental_record.record_date', 'DESC');
            
        if ($limit) {
            $builder->limit($limit, $offset);
        }
        
        return $builder->findAll();
    }

    /**
     * Get dental records by dentist with patient and appointment info
     */
    public function getRecordsByDentist($dentistId)
    {
        return $this->select('dental_record.*, user.name as patient_name, appointments.appointment_datetime')
                   ->join('user', 'user.id = dental_record.user_id')
                   ->join('appointments', 'appointments.id = dental_record.appointment_id', 'left')
                   ->where('dental_record.dentist_id', $dentistId)
                   ->orderBy('record_date', 'DESC')
                   ->findAll();
    }

    /**
     * Create new dental record after checkup
     */
    public function createRecord($data)
    {
        $data['record_date'] = date('Y-m-d');
        return $this->insert($data);
    }

    /**
     * Get record with dental chart
     */
    public function getRecordWithChart($recordId)
    {
        $record = $this->select('dental_record.*, user.name as patient_name, dentist.name as dentist_name, appointments.appointment_datetime, next_appt.appointment_datetime as next_appointment_datetime')
                      ->join('user', 'user.id = dental_record.user_id')
                      ->join('user as dentist', 'dentist.id = dental_record.dentist_id')
                      ->join('appointments', 'appointments.id = dental_record.appointment_id', 'left')
                      ->join('appointments as next_appt', 'next_appt.id = dental_record.next_appointment_id', 'left')
                      ->find($recordId);

        if ($record) {
            $dentalChartModel = new \App\Models\DentalChartModel();
            $record['dental_chart'] = $dentalChartModel->getRecordChart($recordId);
        }

        return $record;
    }
}
