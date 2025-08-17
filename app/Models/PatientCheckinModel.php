<?php

namespace App\Models;

use CodeIgniter\Model;

class PatientCheckinModel extends Model
{
    protected $table = 'patient_checkins';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'appointment_id',
        'checked_in_at', 
        'checked_in_by',
        'self_checkin',
        'checkin_method',
        'notes'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'appointment_id' => 'required|integer',
        'checked_in_at' => 'required|valid_date',
        'checkin_method' => 'in_list[staff,self,kiosk]'
    ];

    protected $validationMessages = [
        'appointment_id' => [
            'required' => 'Appointment ID is required',
            'integer' => 'Appointment ID must be a valid number'
        ],
        'checked_in_at' => [
            'required' => 'Check-in time is required',
            'valid_date' => 'Check-in time must be a valid date'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
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
     * Get check-in record for a specific appointment
     */
    public function getByAppointmentId($appointmentId)
    {
        return $this->where('appointment_id', $appointmentId)->first();
    }

    /**
     * Get check-ins with staff details
     */
    public function getWithStaffDetails($appointmentId = null)
    {
        $builder = $this->db->table($this->table . ' pc')
            ->select('pc.*, u.name as staff_name, a.appointment_datetime')
            ->join('user u', 'u.id = pc.checked_in_by', 'left')
            ->join('appointments a', 'a.id = pc.appointment_id', 'left');
            
        if ($appointmentId) {
            $builder->where('pc.appointment_id', $appointmentId);
        }
        
        return $builder->get()->getResultArray();
    }

    /**
     * Check in a patient
     */
    public function checkInPatient($appointmentId, $staffId = null, $isSelfCheckin = false, $notes = null)
    {
        $data = [
            'appointment_id' => $appointmentId,
            'checked_in_at' => date('Y-m-d H:i:s'),
            'checked_in_by' => $isSelfCheckin ? null : $staffId,
            'self_checkin' => $isSelfCheckin ? 1 : 0,
            'checkin_method' => $isSelfCheckin ? 'self' : 'staff',
            'notes' => $notes
        ];

        return $this->insert($data);
    }

    /**
     * Get today's check-ins
     */
    public function getTodayCheckins()
    {
        return $this->where('DATE(checked_in_at)', date('Y-m-d'))
                   ->orderBy('checked_in_at', 'DESC')
                   ->findAll();
    }
}
