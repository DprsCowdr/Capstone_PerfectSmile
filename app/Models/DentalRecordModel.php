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
    'branch_id',
        'record_date',
        'treatment',
        'notes',
        'xray_image_url',
        'next_appointment_date',
        'next_appointment_id',
        'dentist_id',
        'visual_chart_data'
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
        // The schema in some environments doesn't include next_appointment_id on dental_record.
        // Avoid joining on that missing column; return the next_appointment_date stored on the record instead.
        $builder = $this->select('dental_record.*, user.name as patient_name, user.email as patient_email, user.phone as patient_phone, dentist.name as dentist_name, appointments.appointment_datetime, dental_record.next_appointment_date')
            ->join('user', 'user.id = dental_record.user_id')
            ->join('user as dentist', 'dentist.id = dental_record.dentist_id')
            ->join('appointments', 'appointments.id = dental_record.appointment_id', 'left')
            ->orderBy('dental_record.record_date', 'DESC');

        if ($limit) {
            $builder->limit($limit, $offset);
        }

        return $builder->findAll();
    }

    /**
     * Get dental records with complete patient, medical history AND branch information
     */
    public function getRecordsWithPatientAndBranchInfo($limit = null, $offset = 0)
    {
        // The schema in some environments doesn't include next_appointment_id on dental_record.
        // Avoid joining on that missing column; return the next_appointment_date stored on the record instead.
        // Prefer a branch_id stored on the dental_record itself; fall back to the appointment's branch_id
        $builder = $this->select('dental_record.*, user.name as patient_name, user.email as patient_email, user.phone as patient_phone, dentist.name as dentist_name, appointments.appointment_datetime, COALESCE(dental_record.branch_id, appointments.branch_id) as branch_id, branches.name as branch_name, dental_record.next_appointment_date')
            ->join('user', 'user.id = dental_record.user_id')
            ->join('user as dentist', 'dentist.id = dental_record.dentist_id')
            ->join('appointments', 'appointments.id = dental_record.appointment_id', 'left')
            ->join('branches', 'branches.id = COALESCE(dental_record.branch_id, appointments.branch_id)', 'left')
            ->orderBy('dental_record.record_date', 'DESC');        if ($limit) {
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
    // Avoid joining on next_appointment_id which may not exist; return next_appointment_date instead.
    $record = $this->select('dental_record.*, user.name as patient_name, dentist.name as dentist_name, appointments.appointment_datetime, dental_record.next_appointment_date')
              ->join('user', 'user.id = dental_record.user_id')
              ->join('user as dentist', 'dentist.id = dental_record.dentist_id')
              ->join('appointments', 'appointments.id = dental_record.appointment_id', 'left')
              ->find($recordId);

        if ($record) {
            $dentalChartModel = new \App\Models\DentalChartModel();
            $record['dental_chart'] = $dentalChartModel->getRecordChart($recordId);
        }

        return $record;
    }
}
