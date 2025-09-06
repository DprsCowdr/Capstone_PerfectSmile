<?php

namespace App\Models;

use CodeIgniter\Model;

class AppointmentModel extends Model
{
    protected $table = 'appointments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id', 
        'branch_id', 
        'dentist_id',
        'appointment_datetime', // Use this as the main field
    'procedure_duration',
        'status', 
        'appointment_type',
        'approval_status',
        'decline_reason',
        'remarks',
        'created_at',
        'updated_at',
        // Workflow/queue fields
        'checked_in_at',
        'checked_in_by',
        'self_checkin',
        'started_at',
        'called_by',
        'treatment_status',
        'treatment_notes',
        'payment_status',
        'payment_method',
        'payment_amount',
        'payment_date',
        'payment_received_by',
        'payment_notes',
    ];

    // Dates - ENABLE TIMESTAMPS
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'user_id' => 'required|integer',
        'branch_id' => 'required|integer',
        'dentist_id' => 'permit_empty|integer',
        'appointment_datetime' => 'required',
        'status' => 'permit_empty|in_list[pending_approval,pending,scheduled,confirmed,checked_in,ongoing,completed,cancelled,no_show]',
        'appointment_type' => 'permit_empty|in_list[scheduled,walkin]',
        'approval_status' => 'permit_empty|in_list[pending,approved,declined,auto_approved]',
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'Patient is required',
            'integer' => 'Invalid patient ID'
        ],
        'branch_id' => [
            'required' => 'Branch is required',
            'integer' => 'Invalid branch ID'
        ],
        'appointment_datetime' => [
            'required' => 'Appointment date/time is required',
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['setDefaultValues'];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Set default values for new appointments
     */
    protected function setDefaultValues(array $data)
    {
        if (!isset($data['data']['status'])) {
            $data['data']['status'] = 'pending';
        }
        if (!isset($data['data']['appointment_type'])) {
            $data['data']['appointment_type'] = 'scheduled';
        }
        if (!isset($data['data']['approval_status'])) {
            $data['data']['approval_status'] = 'pending';
        }
        
        // Combine appointment_date and appointment_time if present
        if (isset($data['data']['appointment_date']) && isset($data['data']['appointment_time'])) {
            $data['data']['appointment_datetime'] = $data['data']['appointment_date'] . ' ' . $data['data']['appointment_time'] . ':00';
            unset($data['data']['appointment_date'], $data['data']['appointment_time']);
        }
        
        return $data;
    }

    /**
     * Override insert to handle date/time combination and validate business rules
     */
    public function insert($data = null, bool $returnID = true)
    {
        if (isset($data['appointment_date']) && isset($data['appointment_time'])) {
            $data['appointment_datetime'] = $data['appointment_date'] . ' ' . $data['appointment_time'] . ':00';
            unset($data['appointment_date'], $data['appointment_time']);
        }
        
        // Backend validation: no past dates, only 08:00-17:00
        if (isset($data['appointment_datetime'])) {
            $dt = strtotime($data['appointment_datetime']);
            if ($dt < strtotime(date('Y-m-d 00:00:00'))) {
                throw new \Exception('Cannot book an appointment in the past.');
            }
            $hour = (int)date('H', $dt);
            // Allow bookings from 08:00 to 20:00 to match UI range
            if ($hour < 8 || $hour > 20) {
                throw new \Exception('Appointments can only be booked between 08:00 and 20:00.');
            }
        }
        
        return parent::insert($data, $returnID);
    }

    /**
     * Override update to handle date/time combination and validate business rules
     */
    public function update($id = null, $data = null): bool
    {
        if (isset($data['appointment_date']) && isset($data['appointment_time'])) {
            $data['appointment_datetime'] = $data['appointment_date'] . ' ' . $data['appointment_time'] . ':00';
            unset($data['appointment_date'], $data['appointment_time']);
        }
        // Backend validation: no past dates, only 08:00-17:00
        if (isset($data['appointment_datetime'])) {
            $dt = strtotime($data['appointment_datetime']);
            if ($dt < strtotime(date('Y-m-d 00:00:00'))) {
                throw new \Exception('Cannot book an appointment in the past.');
            }
            $hour = (int)date('H', $dt);
            if ($hour < 8 || $hour > 17) {
                throw new \Exception('Appointments can only be booked between 08:00 and 17:00.');
            }
        }
        return parent::update($id, $data);
    }

    /**
     * Helper to split appointment_datetime into date/time for all fetches
     */
    protected function splitDateTime(array $row)
    {
        if (isset($row['appointment_datetime'])) {
            $row['appointment_date'] = substr($row['appointment_datetime'], 0, 10);
            $row['appointment_time'] = substr($row['appointment_datetime'], 11, 5);
        }
        return $row;
    }

    /**
     * Override findAll/find/find etc. to always split
     */
    public function findAll($limit = 0, $offset = 0)
    {
        $rows = parent::findAll($limit, $offset);
        return array_map([$this, 'splitDateTime'], $rows);
    }
    public function find($id = null)
    {
        $row = parent::find($id);
        if ($row) return $this->splitDateTime($row);
        return $row;
    }
    public function where($key = null, $value = null)
    {
        $this->tempReturnType = 'array';
        return parent::where($key, $value);
    }

    /**
     * Get appointments by branch
     */
    public function getAppointmentsByBranch($branchId)
    {
        return $this->where('branch_id', $branchId)->findAll();
    }

    /**
     * Get appointments by date
     */
    public function getAppointmentsByDate($date)
    {
        return $this->where('DATE(appointment_datetime)', $date)->findAll();
    }

    /**
     * Get appointments with user, branch, and dentist info (only approved appointments)
     */
    public function getAppointmentsWithDetails()
    {
        $results = $this->select('appointments.*, 
                             user.name as patient_name, 
                             user.email as patient_email, 
                             branches.name as branch_name,
                             dentists.name as dentist_name,
                             dentists.email as dentist_email')
                    ->join('user', 'user.id = appointments.user_id')
                    ->join('branches', 'branches.id = appointments.branch_id', 'left')
                    ->join('user as dentists', 'dentists.id = appointments.dentist_id', 'left')
                    ->where('appointments.approval_status', 'approved') // Only show approved appointments
                    ->orderBy('appointments.appointment_datetime', 'DESC')
                    ->findAll();
        
        // Apply splitDateTime to each result
        return array_map([$this, 'splitDateTime'], $results);
    }

    /**
     * Get patient appointments (only approved appointments)
     */
    public function getPatientAppointments($patientId)
    {
        log_message('info', 'Getting appointments for patient ID: ' . $patientId);
        
        $results = $this->select('appointments.*, branches.name as branch_name, dentists.name as dentist_name')
                    ->join('branches', 'branches.id = appointments.branch_id', 'left')
                    ->join('user as dentists', 'dentists.id = appointments.dentist_id', 'left')
                    ->where('appointments.user_id', $patientId)
                    // Removed approval_status filter - show all appointments (pending, approved, rejected)
                    ->orderBy('appointments.appointment_datetime', 'DESC')
                    ->findAll();
        
        // Apply splitDateTime to each result
        $results = array_map([$this, 'splitDateTime'], $results);
        
        log_message('info', 'Found ' . count($results) . ' appointments for patient ' . $patientId);
        log_message('info', 'Appointments data: ' . json_encode($results));
        
        return $results;
    }

    /**
     * Get appointments pending dentist approval
     */
    public function getPendingApprovalAppointments($dentistId = null)
    {
        $query = $this->select('appointments.*, 
                               user.name as patient_name, 
                               user.email as patient_email, 
                               branches.name as branch_name,
                               dentists.name as dentist_name')
                      ->join('user', 'user.id = appointments.user_id')
                      ->join('branches', 'branches.id = appointments.branch_id', 'left')
                      ->join('user as dentists', 'dentists.id = appointments.dentist_id', 'left')
                      ->where('appointments.approval_status', 'pending') // Fixed: Use approval_status = 'pending'
                      ->where('appointments.appointment_type', 'scheduled');
        
        if ($dentistId) {
            $query->where('appointments.dentist_id', $dentistId);
        }
        
        $results = $query->orderBy('appointments.appointment_datetime', 'ASC')
                     ->findAll();
        
        // Apply splitDateTime to each result
        return array_map([$this, 'splitDateTime'], $results);
    }

    /**
     * Get today's approved appointments for checkup management
     */
    public function getTodayAppointments($dentistId = null)
    {
        $today = date('Y-m-d');
        log_message('info', "Getting today's appointments for date: {$today}, dentist: " . ($dentistId ?: 'all'));
        
        $query = $this->select('appointments.*, 
                               user.name as patient_name, 
                               user.email as patient_email, 
                               user.phone as patient_phone,
                               branches.name as branch_name')
                      ->join('user', 'user.id = appointments.user_id')
                      ->join('branches', 'branches.id = appointments.branch_id', 'left')
                      ->where('DATE(appointments.appointment_datetime)', $today)
                      ->where('appointments.approval_status', 'approved')
                      ->whereIn('appointments.status', ['confirmed', 'scheduled', 'ongoing', 'checked_in']) // Include more statuses
                      ->orderBy('appointments.appointment_datetime', 'ASC');
        
        if ($dentistId) {
            $query->where('appointments.dentist_id', $dentistId);
        }
        
        $results = $query->findAll();
        log_message('info', "Found " . count($results) . " appointments for today");
        
        if (count($results) === 0) {
            // Debug: Check what appointments exist with different criteria
            $debugQuery = $this->select('id, appointment_datetime, status, approval_status, dentist_id')
                               ->where('DATE(appointments.appointment_datetime)', $today)
                               ->findAll();
            log_message('info', "Debug - All appointments for today (any status): " . json_encode($debugQuery));
        }
        
        return array_map([$this, 'splitDateTime'], $results);
    }

    /**
     * Start checkup - mark appointment as ongoing
     */
    public function startCheckup($appointmentId, $dentistId)
    {
        return $this->update($appointmentId, [
            'status' => 'ongoing',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Complete checkup - mark appointment as completed
     */
    public function completeCheckup($appointmentId)
    {
        return $this->update($appointmentId, [
            'status' => 'completed',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Mark patient as no-show
     */
    public function markNoShow($appointmentId)
    {
        return $this->update($appointmentId, [
            'status' => 'no_show',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Cancel appointment
     */
    public function cancelAppointment($appointmentId, $reason = null)
    {
        $data = [
            'status' => 'cancelled',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($reason) {
            $data['decline_reason'] = $reason;
        }
        
        return $this->update($appointmentId, $data);
    }

    /**
     * Auto-update appointment statuses based on time
     */
    public function autoUpdateStatuses()
    {
        $now = date('Y-m-d H:i:s');
        $today = date('Y-m-d');
        
        // Mark confirmed appointments as no-show if they're more than 30 minutes past their time
        $this->where('DATE(appointment_datetime)', $today)
             ->where('status', 'confirmed')
             ->where('appointment_datetime <', date('Y-m-d H:i:s', strtotime('-30 minutes')))
             ->set(['status' => 'no_show', 'updated_at' => $now])
             ->update();
        
        // Mark ongoing appointments as completed if they're more than 2 hours old
        $this->where('DATE(appointment_datetime)', $today)
             ->where('status', 'ongoing')
             ->where('appointment_datetime <', date('Y-m-d H:i:s', strtotime('-2 hours')))
             ->set(['status' => 'completed', 'updated_at' => $now])
             ->update();
    }

    /**
     * Get appointment with patient details for checkup
     */
    public function getAppointmentForCheckup($appointmentId)
    {
        $result = $this->select('appointments.*, 
                                user.name as patient_name, 
                                user.email as patient_email, 
                                user.phone as patient_phone,
                                user.date_of_birth as patient_dob,
                                user.gender as patient_gender,
                                user.address as patient_address,
                                branches.name as branch_name,
                                dentists.name as dentist_name')
                       ->join('user', 'user.id = appointments.user_id')
                       ->join('branches', 'branches.id = appointments.branch_id', 'left')
                       ->join('user as dentists', 'dentists.id = appointments.dentist_id', 'left')
                       ->find($appointmentId);
        
        if ($result) {
            return $this->splitDateTime($result);
        }
        
        return null;
    }

    /**
     * Check for appointment time conflicts
     * Simple and clean implementation
     */
    public function checkTimeConflicts($date, $time, $dentistId = null, $excludeId = null)
    {
        // Convert to proper datetime format
        $targetDateTime = $date . ' ' . $time . ':00';
        $targetTimestamp = strtotime($targetDateTime);
        
        // Define 30-minute conflict window
        $windowMinutes = 30;
        $startTime = date('Y-m-d H:i:s', $targetTimestamp - ($windowMinutes * 60));
        $endTime = date('Y-m-d H:i:s', $targetTimestamp + ($windowMinutes * 60));
        
        // Build query for conflicts
        $query = $this->select('appointments.id, appointments.appointment_datetime, user.name as patient_name, dentist.name as dentist_name')
                     ->join('user', 'user.id = appointments.user_id')
                     ->join('user as dentist', 'dentist.id = appointments.dentist_id', 'left')
                     ->where('appointment_datetime >=', $startTime)
                     ->where('appointment_datetime <=', $endTime)
                     ->whereIn('status', ['confirmed', 'checked_in', 'ongoing'])
                     ->whereIn('approval_status', ['approved', 'auto_approved']);
        
        // Filter by dentist if specified
        if ($dentistId) {
            $query->where('appointments.dentist_id', $dentistId);
        }
        
        // Exclude current appointment if updating
        if ($excludeId) {
            $query->where('appointments.id !=', $excludeId);
        }
        
        $conflicts = $query->findAll();
        
        // Return formatted conflict data
        $result = [];
        foreach ($conflicts as $conflict) {
            $result[] = [
                'id' => $conflict['id'],
                'patient_name' => $conflict['patient_name'],
                'dentist_name' => $conflict['dentist_name'] ?? 'Unassigned',
                'appointment_time' => date('H:i', strtotime($conflict['appointment_datetime'])),
                'time_difference' => abs($targetTimestamp - strtotime($conflict['appointment_datetime'])) / 60
            ];
        }
        
        return $result;
    }

    /**
     * Approve appointment
     */
    public function approveAppointment($appointmentId, $dentistId = null)
    {
        log_message('info', "AppointmentModel::approveAppointment - ID: {$appointmentId}, Dentist ID: " . ($dentistId ?: 'null'));
        
        // Get the original appointment data
        $appointment = parent::find($appointmentId);
        if (!$appointment) {
            log_message('error', "Appointment {$appointmentId} not found");
            return false;
        }
        
        $updateData = [
            'approval_status' => 'approved',
            'status' => 'confirmed', // Change from pending_approval to confirmed
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Only set dentist_id if provided and appointment doesn't already have one
        if ($dentistId) {
            if (!$appointment || empty($appointment['dentist_id'])) {
                $updateData['dentist_id'] = $dentistId;
                log_message('info', "Setting dentist_id to {$dentistId} for appointment {$appointmentId}");
            } else {
                log_message('info', "Appointment {$appointmentId} already has dentist_id: {$appointment['dentist_id']}");
            }
        }
        
        log_message('info', "Update data for appointment {$appointmentId}: " . json_encode($updateData));
        
        $result = $this->update($appointmentId, $updateData);
        
        if ($result) {
            log_message('info', "Appointment {$appointmentId} approved successfully - Status: confirmed, Approval: approved");
        } else {
            log_message('error', "Failed to approve appointment {$appointmentId}");
        }
        
        return $result;
    }

    /**
     * Decline appointment - DELETE from database instead of just marking as declined
     */
    public function declineAppointment($appointmentId, $reason)
    {
        // Log the decline reason before deleting
        log_message('info', "Appointment {$appointmentId} declined with reason: {$reason}");
        
        // Delete the appointment completely from database
        return $this->delete($appointmentId);
    }

    /**
     * Create walk-in appointment
     */
    public function createWalkInAppointment($data)
    {
        $data['appointment_type'] = 'walkin';
        $data['approval_status'] = 'auto_approved';
        $data['status'] = 'confirmed';
        
        return $this->insert($data);
    }

    /**
     * Get all appointments with user, branch, and dentist info (for admin - excludes pending_approval)
     */
    public function getAllAppointmentsForAdmin()
    {
        $results = $this->select('appointments.*, 
                             user.name as patient_name, 
                             user.email as patient_email, 
                             branches.name as branch_name,
                             dentists.name as dentist_name,
                             dentists.email as dentist_email')
                    ->join('user', 'user.id = appointments.user_id')
                    ->join('branches', 'branches.id = appointments.branch_id', 'left')
                    ->join('user as dentists', 'dentists.id = appointments.dentist_id', 'left')
                    ->where('appointments.status !=', 'pending_approval') // Exclude pending approval appointments
                    ->where('appointments.approval_status !=', 'pending') // Also exclude pending approval status
                    ->orderBy('appointments.appointment_datetime', 'DESC')
                    ->findAll();
        
        // Apply splitDateTime to each result
        return array_map([$this, 'splitDateTime'], $results);
    }

    /**
     * Get available dentists for a specific date, time, and branch
     *
     * @param string $date (Y-m-d format)
     * @param string $time (H:i format)
     * @param int $branchId
     * @return array Available dentists
     */
    public function getAvailableDentists($date, $time, $branchId)
    {
        $datetime = $date . ' ' . $time . ':00';
        
        // Get all dentists from the branch
        $db = \Config\Database::connect();
        $builder = $db->table('user');
        $builder->select('user.id, user.name, user.email')
                ->join('branch_user', 'branch_user.user_id = user.id')
                ->where('user.user_type', 'doctor')
                ->where('user.status', 'active')
                ->where('branch_user.branch_id', $branchId);
        
        $allDentists = $builder->get()->getResultArray();
        
        $availableDentists = [];
        
        foreach ($allDentists as $dentist) {
            // Check if dentist has any conflicting appointments
            $conflictBuilder = $db->table('appointments');
            $conflictBuilder->where('dentist_id', $dentist['id'])
                           ->where('appointment_datetime <=', $datetime)
                           ->where('DATE_ADD(appointment_datetime, INTERVAL 30 MINUTE) >', $datetime)
                           ->where('status !=', 'cancelled')
                           ->where('status !=', 'completed');
            
            $conflicts = $conflictBuilder->countAllResults();
            
            if ($conflicts == 0) {
                $availableDentists[] = $dentist;
            }
        }
        
        return $availableDentists;
    }
}