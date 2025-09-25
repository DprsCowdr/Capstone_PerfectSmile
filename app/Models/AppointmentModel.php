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
        // Guest booking fields
        'patient_email',
        'patient_phone',
        'patient_name',
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
        'user_id' => 'permit_empty|integer',  // Allow guest bookings
        'branch_id' => 'required|integer',
        'dentist_id' => 'permit_empty|integer',
        'appointment_datetime' => 'required',
        'status' => 'permit_empty|in_list[pending_approval,pending,scheduled,confirmed,checked_in,ongoing,completed,cancelled,no_show]',
        'appointment_type' => 'permit_empty|in_list[scheduled,walkin]',
        'approval_status' => 'permit_empty|in_list[pending,approved,declined,auto_approved]',
        // Guest booking validation - require either user_id OR contact info
        'patient_email' => 'permit_empty|valid_email',
        'patient_phone' => 'permit_empty|string',
        'patient_name' => 'permit_empty|string',
    ];

    protected $validationMessages = [
        'user_id' => [
            'integer' => 'Invalid patient ID'
        ],
        'branch_id' => [
            'required' => 'Branch is required',
            'integer' => 'Invalid branch ID'
        ],
        'appointment_datetime' => [
            'required' => 'Appointment date/time is required',
        ],
        'patient_email' => [
            'valid_email' => 'Please provide a valid email address'
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
        
        // Custom validation: require either user_id OR guest contact info
        if (empty($data['user_id']) && empty($data['patient_email']) && empty($data['patient_phone'])) {
            throw new \Exception('Either patient ID or contact information (email/phone) is required for booking.');
        }
        
        // Backend validation: no past dates, only 08:00-20:00
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
        // Backend validation: no past dates, only 08:00-20:00 (unified with insert)
        if (isset($data['appointment_datetime'])) {
            $dt = strtotime($data['appointment_datetime']);
            if ($dt < strtotime(date('Y-m-d 00:00:00'))) {
                throw new \Exception('Cannot book an appointment in the past.');
            }
            $hour = (int)date('H', $dt);
            if ($hour < 8 || $hour > 20) {
                throw new \Exception('Appointments can only be booked between 08:00 and 20:00.');
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
     * Get appointments with user, branch, and dentist info (for admin/staff calendar views)
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
                    ->whereIn('appointments.approval_status', ['approved', 'pending', 'auto_approved']) // Include pending and auto-approved
                    ->whereIn('appointments.status', ['confirmed', 'scheduled', 'pending', 'pending_approval', 'ongoing']) // Include relevant statuses
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
     * Get appointments pending dentist approval (both patient appointments and guest bookings)
     */
    public function getPendingApprovalAppointments($dentistId = null)
    {
        $query = $this->select('appointments.*, 
                               COALESCE(user.name, appointments.patient_name) as patient_name, 
                               COALESCE(user.email, appointments.patient_email) as patient_email, 
                               COALESCE(user.phone, appointments.patient_phone) as patient_phone,
                               branches.name as branch_name,
                               dentists.name as dentist_name')
                      ->join('user', 'user.id = appointments.user_id', 'left') // Left join to include guest bookings
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
        
        // Debug: Log the raw result
        log_message('debug', 'AppointmentModel getAppointmentForCheckup raw result: ' . json_encode($result));
        
        if ($result) {
            $processed = $this->splitDateTime($result);
            log_message('debug', 'AppointmentModel getAppointmentForCheckup processed result keys: ' . implode(', ', array_keys($processed)));
            log_message('debug', 'AppointmentModel getAppointmentForCheckup patient_name exists: ' . (isset($processed['patient_name']) ? 'YES' : 'NO'));
            return $processed;
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
     * Check if a given datetime conflicts with existing appointments within a grace window.
     * Returns true if a conflict exists.
     * @param string $datetime Y-m-d H:i:s
     * @param int $graceMinutes minutes to treat as buffer for conflict detection
     * @param int|null $dentistId optional dentist filter
     * @param int|null $excludeId optional appointment id to exclude from check
     * @return bool
     */
    public function isTimeConflictingWithGrace($datetime, $graceMinutes = 15, $dentistId = null, $excludeId = null, $requiredDurationMinutes = null)
    {
        // Candidate range: start = $datetime; end = start + requiredDuration (if provided) else start
        $candidateStart = $datetime;
        if ($requiredDurationMinutes && is_numeric($requiredDurationMinutes) && (int)$requiredDurationMinutes > 0) {
            $candidateEnd = date('Y-m-d H:i:s', strtotime($candidateStart) + ((int)$requiredDurationMinutes * 60));
        } else {
            // If no required duration supplied, treat candidateEnd as candidateStart (we'll still check any appointment that overlaps by grace)
            $candidateEnd = $candidateStart;
        }

        // Build query that finds any appointment where:
        // appointment_datetime < candidateEnd
        // AND DATE_ADD(appointment_datetime, INTERVAL (COALESCE(svc.total_service_minutes, a.procedure_duration, 0) + grace) MINUTE) > candidateStart
        // This ensures we consider the appointment's real occupied window (service duration + grace)

        $db = \Config\Database::connect();
        // Build driver-aware SQL to avoid DATE_ADD/INTERVAL incompatibilities (SQLite vs MySQL)
        $driver = strtolower($db->DBDriver ?? '');
        if (strpos($driver, 'sqlite') !== false) {
            // SQLite: use datetime(a.appointment_datetime, '+' || (minutes) || ' minutes')
            $sql = "SELECT a.id FROM appointments a
                LEFT JOIN (
                    SELECT aps.appointment_id, SUM(COALESCE(s.duration_max_minutes, s.duration_minutes, 0)) AS total_service_minutes
                    FROM appointment_service aps
                    JOIN services s ON s.id = aps.service_id
                    GROUP BY aps.appointment_id
                ) svc ON svc.appointment_id = a.id
                WHERE a.appointment_datetime < ?
                AND datetime(a.appointment_datetime, '+' || (COALESCE(svc.total_service_minutes, a.procedure_duration, 0) + ?) || ' minutes') > ?
                AND a.status IN ('confirmed','scheduled','ongoing')
                AND a.approval_status IN ('approved','auto_approved')";
        } else {
            // MySQL/Postgres compatible using DATE_ADD (Postgres has different syntax, but most CI setups use MySQL)
            $sql = "SELECT a.id FROM appointments a
                LEFT JOIN (
                    SELECT aps.appointment_id, SUM(COALESCE(s.duration_max_minutes, s.duration_minutes, 0)) AS total_service_minutes
                    FROM appointment_service aps
                    JOIN services s ON s.id = aps.service_id
                    GROUP BY aps.appointment_id
                ) svc ON svc.appointment_id = a.id
                WHERE a.appointment_datetime < ?
                AND DATE_ADD(a.appointment_datetime, INTERVAL (COALESCE(svc.total_service_minutes, a.procedure_duration, 0) + ?) MINUTE) > ?
                AND a.status IN ('confirmed','scheduled','ongoing')
                AND a.approval_status IN ('approved','auto_approved')";
        }

        $params = [$candidateEnd, (int)$graceMinutes, $candidateStart];
        if ($dentistId) {
            $sql .= " AND a.dentist_id = ?";
            $params[] = $dentistId;
        }
        if ($excludeId) {
            $sql .= " AND a.id != ?";
            $params[] = $excludeId;
        }

        // Attempt SQL-first path and fall back to PHP-based check if anything fails
        try {
            $query = $db->query($sql, $params);
            if ($query === false) {
                throw new \Exception('SQL query returned false');
            }
            $rows = $query->getResultArray();
            return !empty($rows);
        } catch (\Throwable $e) {
            // Fallback: fetch potentially conflicting appointments and compute overlap in PHP
            try {
                $builder = $db->table('appointments a')
                              ->select('a.id, a.appointment_datetime, a.procedure_duration')
                              ->where('a.appointment_datetime <', $candidateEnd)
                              ->whereIn('a.status', ['confirmed','scheduled','ongoing'])
                              ->whereIn('a.approval_status', ['approved','auto_approved']);
                if ($dentistId) $builder->where('a.dentist_id', $dentistId);
                if ($excludeId) $builder->where('a.id !=', $excludeId);

                $candidates = $builder->get()->getResultArray();
                if (empty($candidates)) return false;

                foreach ($candidates as $appt) {
                    $apptStart = strtotime($appt['appointment_datetime']);
                    $duration = 0;
                    // If procedure_duration present, use it
                    if (!empty($appt['procedure_duration'])) {
                        $duration = (int)$appt['procedure_duration'];
                    } else {
                        // Sum linked service durations
                        try {
                            $svcBuilder = $db->table('appointment_service')->select('services.duration_minutes, services.duration_max_minutes')
                                              ->join('services', 'services.id = appointment_service.service_id', 'left')
                                              ->where('appointment_service.appointment_id', $appt['id']);
                            $services = $svcBuilder->get()->getResultArray();
                            $totalDuration = 0;
                            foreach ($services as $service) {
                                if (!empty($service['duration_max_minutes'])) $totalDuration += (int)$service['duration_max_minutes'];
                                elseif (!empty($service['duration_minutes'])) $totalDuration += (int)$service['duration_minutes'];
                            }
                            $duration = $totalDuration;
                        } catch (\Throwable $inner) {
                            $duration = 30; // conservative default
                        }
                    }

                    $apptEnd = $apptStart + ($duration * 60);
                    $candStartTs = strtotime($candidateStart);

                    // Apply grace buffer: treat appointment as extended by grace minutes
                    $apptEndWithGrace = $apptEnd + ($graceMinutes * 60);

                    // If the appointment start is before candidate end AND appointment end+grace is after candidate start -> conflict
                    if ($apptStart < strtotime($candidateEnd) && $apptEndWithGrace > $candStartTs) {
                        return true;
                    }
                }

                return false;
            } catch (\Throwable $final) {
                // If fallback also fails, be conservative and report a conflict to avoid double-booking
                log_message('error', 'isTimeConflictingWithGrace fallback failed: ' . $final->getMessage());
                return true;
            }
        }
    }

    /**
     * Mark scheduled/confirmed appointments as no-show if they haven't checked in and
     * have passed their appointment time plus a grace period.
     * @param int $graceMinutes
     * @return int number of rows updated
     */
    public function expireOverdueScheduled($graceMinutes = 15)
    {
        $now = date('Y-m-d H:i:s');
        $threshold = date('Y-m-d H:i:s', strtotime("-{$graceMinutes} minutes"));

        // Appointments where appointment_datetime < threshold, status is scheduled/confirmed,
        // approval_status is approved/auto_approved, and patient hasn't checked in
        $builder = $this->builder();
        $builder->where('appointment_datetime <', $threshold)
                ->whereIn('status', ['scheduled', 'confirmed'])
                ->whereIn('approval_status', ['approved', 'auto_approved'])
                ->where('checked_in_at IS NULL', null, false)
                ->set(['status' => 'no_show', 'updated_at' => $now]);

        return $builder->update();
    }

    /**
     * Get the next appointment to call for a given dentist.
     * Priority:
     *  1) Patients who have checked-in (ordered by checked_in_at ASC)
     *  2) If none checked-in, return the earliest scheduled/confirmed appointment for today
     * This supports FCFS behavior because walk-ins that check in will appear in checked-in set
     * and will be selected based on their arrival time. Overdue scheduled appointments
     * should be expired first using expireOverdueScheduled().
     * @param int|null $dentistId
     * @return array|null appointment row or null
     */
    public function getNextAppointmentForDentist($dentistId = null)
    {
        $today = date('Y-m-d');

        // 1) Prefer checked-in patients (waiting list)
        $qb = $this->select('appointments.*')
                   ->join('patient_checkins', 'patient_checkins.appointment_id = appointments.id')
                   ->where('DATE(appointments.appointment_datetime)', $today)
                   ->where('appointments.status', 'checked_in')
                   ->whereIn('appointments.approval_status', ['approved', 'auto_approved'])
                   ->orderBy('patient_checkins.checked_in_at', 'ASC')
                   ->orderBy('appointments.appointment_datetime', 'ASC');

        if ($dentistId) $qb->where('appointments.dentist_id', $dentistId);

        $row = $qb->limit(1)->get()->getRowArray();
        if ($row) return $row;

        // 2) No one checked-in: return earliest scheduled/confirmed appointment for today
        $qb2 = $this->select('appointments.*')
                    ->where('DATE(appointments.appointment_datetime)', $today)
                    ->whereIn('appointments.status', ['scheduled', 'confirmed'])
                    ->whereIn('appointments.approval_status', ['approved', 'auto_approved'])
                    ->orderBy('appointments.appointment_datetime', 'ASC');

        if ($dentistId) $qb2->where('appointments.dentist_id', $dentistId);

        return $qb2->limit(1)->get()->getRowArray();
    }

    /**
     * Find the next available slot (time string H:i) on the given date that does not conflict
     * with existing appointments when considering a grace period. Searches forward in 5-minute
     * steps up to $lookaheadMinutes.
     * Returns null if no slot found within lookahead.
     * @param string $date Y-m-d
     * @param string $preferredTime H:i
     * @param int $graceMinutes
     * @param int $lookaheadMinutes
     * @param int|null $dentistId
     * @return string|null time in H:i
     */
    public function findNextAvailableSlot($date, $preferredTime, $graceMinutes = 15, $lookaheadMinutes = 120, $dentistId = null, $requiredDurationMinutes = null)
    {
        $preferredTs = strtotime($date . ' ' . $preferredTime . ':00');
    $step = 1 * 60; // 1 minute step to allow minute-granular booking
        $limitTs = $preferredTs + ($lookaheadMinutes * 60);

        for ($ts = $preferredTs; $ts <= $limitTs; $ts += $step) {
            $candidate = date('Y-m-d H:i:s', $ts);
            if (!$this->isTimeConflictingWithGrace($candidate, $graceMinutes, $dentistId, null, $requiredDurationMinutes)) {
                return date('H:i', $ts);
            }
        }

        return null;
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
     * Decline appointment - mark as declined instead of deleting
     * This avoids foreign key constraint issues with related tables (e.g. appointment_service)
     */
    public function declineAppointment($appointmentId, $reason)
    {
        // Log the decline reason
        log_message('info', "Appointment {$appointmentId} declined with reason: {$reason}");

        $updateData = [
            'approval_status' => 'declined',
            'status' => 'cancelled',
            'decline_reason' => $reason,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Update the appointment record instead of deleting it to preserve related rows
        try {
            $result = $this->update($appointmentId, $updateData);
            if ($result) {
                log_message('info', "Appointment {$appointmentId} marked as declined successfully");
            } else {
                log_message('error', "Failed to mark appointment {$appointmentId} as declined");
            }
            return $result;
        } catch (\Exception $e) {
            log_message('error', "Exception while declining appointment {$appointmentId}: " . $e->getMessage());
            return false;
        }
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
                ->join('branch_staff', 'branch_staff.user_id = user.id')
                ->where('user.user_type', 'dentist')
                ->where('user.status', 'active')
                ->where('branch_staff.branch_id', $branchId);
        
        $allDentists = $builder->get()->getResultArray();
        
        $availableDentists = [];
        
        foreach ($allDentists as $dentist) {
            // Check if dentist has any conflicting appointments using proper duration logic
            $conflictBuilder = $db->table('appointments a');
            $conflictBuilder->select('a.id, a.appointment_datetime, a.procedure_duration')
                           ->where('a.dentist_id', $dentist['id'])
                           ->where('DATE(a.appointment_datetime)', $date)
                           ->where('a.status !=', 'cancelled')
                           ->where('a.status !=', 'completed')
                           ->where('a.status !=', 'declined');
            
            $conflictingAppointments = $conflictBuilder->get()->getResultArray();
            
            $hasConflict = false;
            $targetTimestamp = strtotime($datetime);
            
            foreach ($conflictingAppointments as $appt) {
                $apptStart = strtotime($appt['appointment_datetime']);
                $duration = 0;
                
                // Use procedure_duration if available
                if (!empty($appt['procedure_duration'])) {
                    $duration = (int)$appt['procedure_duration'];
                } else {
                    // Aggregate linked service durations (same logic as other methods)
                    try {
                        $serviceBuilder = $db->table('appointment_service')
                                            ->select('services.duration_minutes, services.duration_max_minutes')
                                            ->join('services', 'services.id = appointment_service.service_id', 'left')
                                            ->where('appointment_service.appointment_id', $appt['id']);
                        $services = $serviceBuilder->get()->getResultArray();
                        
                        $totalDuration = 0;
                        foreach ($services as $service) {
                            // Prefer duration_max_minutes when present (conservative scheduling)
                            if (!empty($service['duration_max_minutes'])) {
                                $totalDuration += (int)$service['duration_max_minutes'];
                            } elseif (!empty($service['duration_minutes'])) {
                                $totalDuration += (int)$service['duration_minutes'];
                            }
                        }
                        $duration = $totalDuration;
                    } catch (\Exception $e) {
                        // If service lookup fails, use a conservative 30-minute default
                        $duration = 30;
                    }
                }
                
                $apptEnd = $apptStart + ($duration * 60);
                
                // Check for overlap with 30-minute buffer around target time
                $targetStart = $targetTimestamp - (15 * 60);  // 15 minutes before
                $targetEnd = $targetTimestamp + (15 * 60);    // 15 minutes after
                
                if ($apptStart < $targetEnd && $apptEnd > $targetStart) {
                    $hasConflict = true;
                    break;
                }
            }
            
            if (!$hasConflict) {
                $availableDentists[] = $dentist;
            }
        }
        
        return $availableDentists;
    }

    /**
     * Return occupied intervals for a given date, aggregating service durations in SQL.
     * This avoids per-appointment service lookups in PHP and keeps duration aggregation in the DB.
     * Returns array of [start_ts, end_ts, appointment_id, user_id]
     */
    public function getOccupiedIntervals($date, $branchId = null, $dentistId = null)
    {
        $db = \Config\Database::connect();

        // DEBUG: Let's first check what appointments exist for this date
        $debugBuilder = $db->table('appointments a')
                           ->select('a.id, a.appointment_datetime, a.approval_status, a.status, a.branch_id')
                           ->where('DATE(a.appointment_datetime)', $date);
        if ($branchId) $debugBuilder->where('a.branch_id', $branchId);
        $debugRows = $debugBuilder->get()->getResultArray();
        log_message('debug', "getOccupiedIntervals debug for date=$date, branch=$branchId: " . json_encode($debugRows));

        // Build subquery that aggregates service durations per appointment
        $sub = "(SELECT aps.appointment_id, SUM(COALESCE(s.duration_max_minutes, s.duration_minutes, 0)) AS total_service_minutes
                  FROM appointment_service aps
                  JOIN services s ON s.id = aps.service_id
                  GROUP BY aps.appointment_id) svc";

        $builder = $db->table('appointments a')
                      ->select('a.id, a.appointment_datetime, COALESCE(svc.total_service_minutes, a.procedure_duration, 0) AS duration_minutes, a.user_id, a.approval_status, a.status')
                      ->join($sub, 'svc.appointment_id = a.id', 'left')
                      ->where('DATE(a.appointment_datetime)', $date)
                      ->whereIn('a.approval_status', ['approved','auto_approved'])
                      ->whereNotIn('a.status', ['cancelled','rejected','no_show']);

        if ($branchId) $builder->where('a.branch_id', $branchId);
        if ($dentistId) $builder->where('a.dentist_id', $dentistId);

        $rows = $builder->get()->getResultArray();
        log_message('debug', "getOccupiedIntervals filtered results: " . json_encode($rows));
        
        $out = [];
        foreach ($rows as $r) {
            $start = strtotime($r['appointment_datetime']);
            $dur = isset($r['duration_minutes']) ? (int)$r['duration_minutes'] : 0;
            $end = $start + ($dur * 60);
            $out[] = [$start, $end, $r['id'], $r['user_id'] ?? null];
        }
        log_message('debug', "getOccupiedIntervals final intervals: " . json_encode($out));
        return $out;
    }
}