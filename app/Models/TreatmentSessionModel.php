<?php

namespace App\Models;

use CodeIgniter\Model;

class TreatmentSessionModel extends Model
{
    protected $table = 'treatment_sessions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'appointment_id',
        'started_at',
        'ended_at',
        'called_by',
        'dentist_id',
        'treatment_status',
        'treatment_notes',
        'priority',
        'room_number',
        'duration_minutes'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'appointment_id' => 'required|integer',
        'started_at' => 'required|valid_date',
        'treatment_status' => 'in_list[in_progress,completed,paused,cancelled]',
        'priority' => 'in_list[low,normal,high,urgent]'
    ];

    protected $validationMessages = [
        'appointment_id' => [
            'required' => 'Appointment ID is required',
            'integer' => 'Appointment ID must be a valid number'
        ],
        'started_at' => [
            'required' => 'Start time is required',
            'valid_date' => 'Start time must be a valid date'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['calculateDuration'];
    protected $afterInsert = [];
    protected $beforeUpdate = ['calculateDuration'];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Calculate duration when ended_at is set
     */
    protected function calculateDuration(array $data)
    {
        if (isset($data['data']['started_at']) && isset($data['data']['ended_at'])) {
            $start = new \DateTime($data['data']['started_at']);
            $end = new \DateTime($data['data']['ended_at']);
            $data['data']['duration_minutes'] = $end->diff($start)->i + ($end->diff($start)->h * 60);
        }
        
        return $data;
    }

    /**
     * Get treatment session for a specific appointment
     */
    public function getByAppointmentId($appointmentId)
    {
        return $this->where('appointment_id', $appointmentId)->first();
    }

    /**
     * Get active (ongoing) treatment sessions
     */
    public function getActiveSessions()
    {
        return $this->where('treatment_status', 'in_progress')
                   ->orderBy('started_at', 'ASC')
                   ->findAll();
    }

    /**
     * Get sessions with dentist and patient details
     */
    public function getWithDetails($appointmentId = null)
    {
        $builder = $this->db->table($this->table . ' ts')
            ->select('ts.*, d.name as dentist_name, cb.name as called_by_name, 
                     p.name as patient_name, a.appointment_datetime')
            ->join('user d', 'd.id = ts.dentist_id', 'left')
            ->join('user cb', 'cb.id = ts.called_by', 'left')
            ->join('appointments a', 'a.id = ts.appointment_id', 'left')
            ->join('user p', 'p.id = a.user_id', 'left');
            
        if ($appointmentId) {
            $builder->where('ts.appointment_id', $appointmentId);
        }
        
        return $builder->get()->getResultArray();
    }

    /**
     * Start a treatment session
     */
    public function startSession($appointmentId, $calledBy, $dentistId = null, $priority = 'normal', $roomNumber = null)
    {
        $data = [
            'appointment_id' => $appointmentId,
            'started_at' => date('Y-m-d H:i:s'),
            'called_by' => $calledBy,
            'dentist_id' => $dentistId ?: $calledBy,
            'treatment_status' => 'in_progress',
            'priority' => $priority,
            'room_number' => $roomNumber
        ];

        return $this->insert($data);
    }

    /**
     * End a treatment session
     */
    public function endSession($sessionId, $notes = null)
    {
        $data = [
            'ended_at' => date('Y-m-d H:i:s'),
            'treatment_status' => 'completed',
            'treatment_notes' => $notes
        ];

        return $this->update($sessionId, $data);
    }

    /**
     * Update session status
     */
    public function updateStatus($sessionId, $status, $notes = null)
    {
        $data = ['treatment_status' => $status];
        
        if ($notes) {
            $data['treatment_notes'] = $notes;
        }
        
        if ($status === 'completed') {
            $data['ended_at'] = date('Y-m-d H:i:s');
        }

        return $this->update($sessionId, $data);
    }

    /**
     * Get today's treatment sessions
     */
    public function getTodaySessions()
    {
        return $this->where('DATE(started_at)', date('Y-m-d'))
                   ->orderBy('started_at', 'DESC')
                   ->findAll();
    }

    /**
     * Get sessions by dentist
     */
    public function getByDentist($dentistId, $date = null)
    {
        $builder = $this->where('dentist_id', $dentistId);
        
        if ($date) {
            $builder->where('DATE(started_at)', $date);
        }
        
        return $builder->orderBy('started_at', 'DESC')->findAll();
    }
}
