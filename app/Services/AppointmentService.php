<?php

namespace App\Services;

class AppointmentService
{
    protected $appointmentModel;
    // Flag set when insertAppointment finds an existing appointment instead of inserting
    protected $lastInsertWasDuplicate = false;
    
    public function __construct()
    {
        $this->appointmentModel = new \App\Models\AppointmentModel();
    }
    
    public function getDashboardData()
    {
        // Get pending appointments for approval
        // Respect selected branch if set
        $selectedBranch = session('selected_branch_id') ?: null;
        if ($selectedBranch) {
            $pendingAppointments = $this->appointmentModel->getPendingApprovalAppointments(null);
            // Filter pending appointments by branch using model query
            $pendingAppointments = array_filter($pendingAppointments, function($a) use ($selectedBranch) { return (isset($a['branch_id']) && $a['branch_id'] == $selectedBranch); });
        } else {
            $pendingAppointments = $this->appointmentModel->getPendingApprovalAppointments();
        }

        // Get upcoming approved appointments starting from today (exclude past appointments)
        $todayStart = date('Y-m-d') . ' 00:00:00';
        $query = $this->appointmentModel->select('appointments.*, user.name as patient_name, user.email as patient_email, branches.name as branch_name')
                         ->join('user', 'user.id = appointments.user_id')
                         ->join('branches', 'branches.id = appointments.branch_id', 'left')
                         ->where('appointments.appointment_datetime >=', $todayStart)
                         ->where('appointments.approval_status', 'approved') // Only approved appointments
                         ->whereIn('appointments.status', ['confirmed', 'scheduled', 'ongoing'])
                         ->orderBy('appointments.appointment_datetime', 'ASC');
        if ($selectedBranch) {
            $query->where('appointments.branch_id', (int)$selectedBranch);
        }
        $todayAppointments = $query->findAll();
        
        // The splitDateTime is already handled by the AppointmentModel's findAll method
        
        return [
            'pendingAppointments' => $pendingAppointments,
            'todayAppointments' => $todayAppointments
        ];
    }
    
    public function getAllAppointments($branchId = null)
    {
        // If branchId provided, query model for branch results for better performance
        if ($branchId) {
            return $this->appointmentModel->select('appointments.*, user.name as patient_name, branches.name as branch_name')
                        ->join('user', 'user.id = appointments.user_id', 'left')
                        ->join('branches', 'branches.id = appointments.branch_id', 'left')
                        ->where('appointments.branch_id', (int)$branchId)
                        ->whereIn('appointments.approval_status', ['approved', 'pending', 'auto_approved'])
                        ->whereIn('appointments.status', ['confirmed', 'scheduled', 'pending', 'pending_approval', 'ongoing'])
                        ->orderBy('appointments.appointment_datetime', 'DESC')
                        ->findAll();
        }

        return $this->appointmentModel->getAppointmentsWithDetails();
    }
    
    public function createAppointment($data)
    {
        // Validate required fields
        // user_id may be null for guest bookings; require date/time at minimum
        if (empty($data['appointment_date']) || empty($data['appointment_time'])) {
            return ['success' => false, 'message' => 'Required fields missing (AppointmentService:71)'];
        }

        try {
            $appointmentType = $data['appointment_type'] ?? 'scheduled';
            
            if ($appointmentType === 'walkin') {
                return $this->createWalkInAppointment($data);
            } else {
                return $this->createScheduledAppointment($data);
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to create appointment: ' . $e->getMessage()];
        }
    }
    
    private function createWalkInAppointment($data)
    {
    // For walk-in appointments, allow booking regardless of dentist availability
        // Ensure we associate the appointment with the session user when available
        if (empty($data['user_id']) && session()->has('user_id')) {
            $data['user_id'] = session()->get('user_id');
        }
        // Create walk-in appointment (auto-approved)
        $id = $this->appointmentModel->createWalkInAppointment($data);
        $record = $id ? $this->appointmentModel->find($id) : null;
        // Prepare metadata early so we can include a human-readable length in the patient message
        $datetime = $this->resolveAppointmentDatetimeFromRecordOrData($record, $data);
        $grace = isset($data['grace_minutes']) ? (int)$data['grace_minutes'] : 15;
        $role = $this->determineCreatorRole($data);
        $meta = $this->gatherAppointmentMeta($record) ?: [];
        $meta['appointment_length_minutes'] = ($meta['total_service_minutes'] ?? 0) + (int)$grace;
        $message = $this->buildCreatedMessage($role, $datetime, $grace, null, $meta['appointment_length_minutes']);

        // Enrich response with linked services and computed totals
        return array_merge(['success' => true, 'message' => $message, 'appointment_datetime' => $datetime, 'grace_minutes' => $grace, 'record' => $record], $meta);
    }
    
    private function createScheduledAppointment($data)
    {
        log_message('info', 'Creating scheduled appointment with data: ' . json_encode($data));
        // If caller didn't provide user_id (relying on session), inject it so model validation passes
        if (empty($data['user_id']) && session()->has('user_id')) {
            $data['user_id'] = session()->get('user_id');
        }
        // FCFS logic: attempt to schedule at requested time; if conflict, find next slot within lookahead
        // Determine grace minutes: prefer explicit param, otherwise read writable grace_periods.json default
        $grace = 15;
        if (isset($data['grace_minutes']) && $data['grace_minutes'] !== null) {
            $grace = (int)$data['grace_minutes'];
        } else {
            try {
                $gpPath = WRITEPATH . 'grace_periods.json';
                if (is_file($gpPath)) {
                    $gp = json_decode(file_get_contents($gpPath), true);
                    if (!empty($gp['default_grace'])) $grace = (int)$gp['default_grace'];
                    elseif (!empty($gp['default'])) $grace = (int)$gp['default'];
                }
            } catch (\Exception $e) {
                // ignore and use default 15
            }
        }
        $lookahead = isset($data['lookahead_minutes']) ? (int)$data['lookahead_minutes'] : 120; // default 2 hours

        $date = $data['appointment_date'];
        $time = $data['appointment_time'];
        $dentistId = $data['dentist_id'] ?? null;

        // Compute required duration (in minutes) using server-authoritative sources.
        // Priority: linked service durations (prefer duration_max_minutes) -> explicit
        // procedure_duration provided by an admin/staff (privileged override) -> null.
        $requiredDurationMinutes = null;
        $role = $this->determineCreatorRole($data);
        $allowExplicitDuration = in_array($role, ['admin', 'staff']);

        if (!empty($data['service_id'])) {
            try {
                $serviceIds = is_array($data['service_id']) ? $data['service_id'] : [$data['service_id']];
                $svcModel = new \App\Models\ServiceModel();
                $sum = 0;
                foreach ($serviceIds as $sid) {
                    if (empty($sid)) continue;
                    $svc = $svcModel->find($sid);
                    if ($svc) {
                        if (!empty($svc['duration_max_minutes'])) {
                            $sum += (int)$svc['duration_max_minutes'];
                        } elseif (!empty($svc['duration_minutes'])) {
                            $sum += (int)$svc['duration_minutes'];
                        }
                    }
                }
                if ($sum > 0) {
                    $requiredDurationMinutes = $sum;
                    log_message('info', 'Computed requiredDurationMinutes ' . $requiredDurationMinutes . ' from requested service_id(s)');
                }
                // If client also provided a procedure_duration, prefer the service sums and ignore client's value
                if (!empty($data['procedure_duration'])) {
                    log_message('info', 'Ignoring client-provided procedure_duration because service_id(s) were supplied');
                    unset($data['procedure_duration']);
                }
            } catch (\Exception $e) {
                log_message('warning', 'Failed to compute required duration from service_id(s): ' . $e->getMessage());
            }
        } else {
            // No services specified: allow privileged explicit duration if creator is admin/staff
            if (!empty($data['procedure_duration']) && $allowExplicitDuration) {
                $pd = (int)$data['procedure_duration'];
                if ($pd > 0) {
                    $requiredDurationMinutes = $pd;
                    log_message('info', 'Using explicit procedure_duration ' . $pd . ' minutes provided by ' . $role);
                }
            } else {
                // If a non-privileged client provided procedure_duration, ignore it
                if (!empty($data['procedure_duration']) && !$allowExplicitDuration) {
                    log_message('info', 'Ignoring client-provided procedure_duration from non-privileged user');
                    unset($data['procedure_duration']);
                }
            }
        }

        // Check conflict for requested datetime
    $requestedDatetime = $date . ' ' . $time . ':00';
    $conflict = $this->appointmentModel->isTimeConflictingWithGrace($requestedDatetime, $grace, $dentistId, null, $requiredDurationMinutes);

        if ($conflict) {
            // Try to find next available slot
            $next = $this->appointmentModel->findNextAvailableSlot($date, $time, $grace, $lookahead, $dentistId, $requiredDurationMinutes);
            if ($next) {
                // Adjust requested time to next available
                log_message('info', "FCFS: requested {$time} conflicted, scheduling at {$next} instead (grace {$grace}m)");
                $data['appointment_time'] = $next;
                // If approval_status mandates auto-approval (walkin or admin-approved), set status accordingly
                if (isset($data['approval_status']) && $data['approval_status'] === 'approved') {
                    $data['status'] = 'confirmed';
                } else {
                    $data['approval_status'] = $data['approval_status'] ?? 'pending';
                    $data['status'] = ($data['approval_status'] === 'approved') ? 'confirmed' : 'pending_approval';
                }
                $saved = $this->insertAppointment($data);
                // Use saved record datetime (if available) to ensure message is authoritative
                $datetime = $this->resolveAppointmentDatetimeFromRecordOrData($saved, $data);
                $role = $this->determineCreatorRole($data);
                // Gather meta so we can include appointment length in the displayed message
                $meta = $this->gatherAppointmentMeta($saved) ?: [];
                $meta['appointment_length_minutes'] = ($meta['total_service_minutes'] ?? 0) + (int)$grace;
                // If insert returned a duplicate, avoid passing adjusted_time into the message to prevent mismatched text
                $adjustedParam = $this->lastInsertWasDuplicate ? null : $data['appointment_time'];
                $message = $this->buildCreatedMessage($role, $datetime, $grace, $adjustedParam, $meta['appointment_length_minutes']);
                if ($this->lastInsertWasDuplicate) {
                    $cfg = config('AppointmentMessages');
                    $note = $cfg->getTemplate('duplicate_note') ?? ' (Note: an identical appointment was already on file; no duplicate was created.)';
                    $message .= ' ' . $note;
                }
                // Append the adjusted note only when we actually adjusted time and did insert a new appointment
                if (!$this->lastInsertWasDuplicate && !empty($data['appointment_time'])) {
                    $cfg = config('AppointmentMessages');
                    $adj = $cfg->getTemplate('adjusted_note');
                    if ($adj) {
                        $message .= ' ' . strtr($adj, ['{adjusted_time}' => $data['appointment_time']]);
                    }
                }
                $meta = $this->gatherAppointmentMeta($saved) ?: [];
                $meta['appointment_length_minutes'] = ($meta['total_service_minutes'] ?? 0) + (int)$grace;
                return array_merge(['success' => true, 'message' => $message, 'adjusted_time' => ($this->lastInsertWasDuplicate ? null : $data['appointment_time']), 'grace_minutes' => $grace, 'appointment_datetime' => $datetime, 'record' => $saved, 'duplicate' => $this->lastInsertWasDuplicate], $meta);
            } else {
                log_message('info', "FCFS: requested {$time} conflicted and no slot found within lookahead {$lookahead} minutes");
                return ['success' => false, 'message' => 'No available slots within lookahead window. Please choose another date or time.'];
            }
        }

        // No conflict: proceed with normal insertion/approval flow
        if (isset($data['approval_status']) && $data['approval_status'] === 'approved') {
            $data['status'] = 'confirmed';
                $saved = $this->insertAppointment($data);
                $datetime = $this->resolveAppointmentDatetimeFromRecordOrData($saved, $data);
                $role = $this->determineCreatorRole($data);
                $meta = $this->gatherAppointmentMeta($saved) ?: [];
                $meta['appointment_length_minutes'] = ($meta['total_service_minutes'] ?? 0) + (int)$grace;
                $message = $this->buildCreatedMessage($role, $datetime, $grace, null, $meta['appointment_length_minutes']);
                if ($this->lastInsertWasDuplicate) {
                    $cfg = config('AppointmentMessages');
                    $note = $cfg->getTemplate('duplicate_note') ?? ' (Note: an identical appointment was already on file; no duplicate was created.)';
                    $message .= ' ' . $note;
                }
            $meta = $this->gatherAppointmentMeta($saved) ?: [];
            $meta['appointment_length_minutes'] = ($meta['total_service_minutes'] ?? 0) + (int)$grace;
            return array_merge(['success' => true, 'message' => $message, 'appointment_datetime' => $datetime, 'grace_minutes' => $grace, 'record' => $saved, 'duplicate' => $this->lastInsertWasDuplicate], $meta);
        } else if (isset($data['approval_status']) && $data['approval_status'] === 'pending') {
            $data['status'] = 'pending_approval';
            $saved = $this->insertAppointment($data);
            $datetime = $this->resolveAppointmentDatetimeFromRecordOrData($saved, $data);
            $role = $this->determineCreatorRole($data);
            $meta = $this->gatherAppointmentMeta($saved) ?: [];
            $meta['appointment_length_minutes'] = ($meta['total_service_minutes'] ?? 0) + (int)$grace;
            $message = $this->buildCreatedMessage($role, $datetime, $grace, null, $meta['appointment_length_minutes']);
            if ($this->lastInsertWasDuplicate) {
                $cfg = config('AppointmentMessages');
                $note = $cfg->getTemplate('duplicate_note') ?? ' (Note: an identical appointment was already on file; no duplicate was created.)';
                $message .= ' ' . $note;
            }
            $meta = $this->gatherAppointmentMeta($saved) ?: [];
            $meta['appointment_length_minutes'] = ($meta['total_service_minutes'] ?? 0) + (int)$grace;
            return array_merge(['success' => true, 'message' => $message, 'appointment_datetime' => $datetime, 'grace_minutes' => $grace, 'record' => $saved, 'duplicate' => $this->lastInsertWasDuplicate], $meta);
        } else {
            $data['approval_status'] = 'pending';
            $data['status'] = 'pending_approval';
            $saved = $this->insertAppointment($data);
            $datetime = $this->resolveAppointmentDatetimeFromRecordOrData($saved, $data);
            $role = $this->determineCreatorRole($data);
            $meta = $this->gatherAppointmentMeta($saved) ?: [];
            $meta['appointment_length_minutes'] = ($meta['total_service_minutes'] ?? 0) + (int)$grace;
            $message = $this->buildCreatedMessage($role, $datetime, $grace, null, $meta['appointment_length_minutes']);
            if ($this->lastInsertWasDuplicate) {
                $message .= ' (Note: an identical appointment was already on file; no duplicate was created.)';
            }
            return array_merge(['success' => true, 'message' => $message, 'appointment_datetime' => $datetime, 'grace_minutes' => $grace, 'record' => $saved, 'duplicate' => $this->lastInsertWasDuplicate], $meta);
        }
    }

    // Determine the creator role for message tailoring
    private function determineCreatorRole($data)
    {
        // Priority: explicit origin -> created_by_role -> session user_type -> fallback 'staff'
        if (!empty($data['origin'])) {
            $o = strtolower($data['origin']);
            if (in_array($o, ['guest', 'patient'])) return 'patient';
            if (in_array($o, ['staff', 'branch_staff'])) return 'staff';
            if (in_array($o, ['admin', 'administrator'])) return 'admin';
            if (in_array($o, ['dentist', 'doctor'])) return 'staff';
        }

        if (!empty($data['created_by_role'])) {
            $r = strtolower($data['created_by_role']);
            if (in_array($r, ['patient', 'guest'])) return 'patient';
            if (in_array($r, ['staff', 'dentist'])) return 'staff';
            if (in_array($r, ['admin'])) return 'admin';
        }

        try {
            $sessionRole = session('user_type');
            if ($sessionRole === 'patient') return 'patient';
            if ($sessionRole === 'staff') return 'staff';
            if (in_array($sessionRole, ['admin', 'administrator'])) return 'admin';
            if (in_array($sessionRole, ['dentist', 'doctor'])) return 'staff';
        } catch (\Exception $e) {
            // ignore session errors
        }

        // Default to 'staff' for created-by users in admin interfaces
        return 'staff';
    }

    // Build a role-specific created message using templates from config/writable JSON.
    private function buildCreatedMessage($role, $datetime = null, $grace = 15, $adjustedTime = null, $appointment_length_minutes = null)
    {
        // Load templates from config
        $cfg = config('AppointmentMessages');

        // Format date/time nicely if provided. Include end time for clarity when duration available.
        $when = '';
        if (!empty($datetime)) {
            $ts = strtotime($datetime);
            if ($ts !== false) {
                // Default display: "September 18, 2025 at 08:00" or range if we can compute an end time
                $startText = date('F j, Y', $ts) . ' at ' . date('g:i A', $ts);
                $endText = '';
                // If we can determine procedure_duration from the appointment record or from provided meta,
                // compute end time and present a range to avoid ambiguity for long appointments.
                $durMinutes = null;
                // if a record was provided with procedure_duration, use it
                if (!empty($adjustedTime) && preg_match('/^\d{1,2}:\d{2}$/', $adjustedTime)) {
                    // adjustedTime is just a time string; use the provided datetime for start
                }
                // Prefer appointment_length_minutes if provided for end time calculation; avoid accessing out-of-scope variables here
                // If appointment_length_minutes passed in explicitly, prefer it for end time calculation
                if (!empty($appointment_length_minutes) && is_numeric($appointment_length_minutes)) {
                    $durMinutes = (int)$appointment_length_minutes;
                }
                if (!empty($durMinutes) && $durMinutes > 0) {
                    $endTs = $ts + ($durMinutes * 60);
                    $endText = date('g:i A', $endTs);
                    $when = $startText . ' — ' . $endText;
                } else {
                    $when = $startText;
                }
            } else {
                if (!empty($adjustedTime) && strlen($adjustedTime) >= 4) {
                    $when = $adjustedTime;
                } else {
                    $when = $datetime;
                }
            }
        }

    $graceText = (int)$grace;

        // Choose template key based on role
        $key = 'admin';
        if ($role === 'patient') $key = 'patient';
        elseif ($role === 'staff') $key = 'staff';

        $template = $cfg->getTemplate($key) ?? '';

        // Replace placeholders: {when}, {grace}, {adjusted_time}
        $replacements = [
            // Provide only the formatted date/time. Templates should include any surrounding words like "on".
            '{when}' => $when ?: '',
            '{grace}' => $graceText,
            '{adjusted_time}' => $adjustedTime ?? ''
        ];

        $message = strtr($template, $replacements);

        // If this is a patient-facing message and we have a computed appointment length,
        // prefer replacing a {appointment_length} placeholder if present in the template so admins
        // can control placement. If the placeholder is absent, fall back to appending the sentence.
        if ($role === 'patient' && !empty($appointment_length_minutes) && is_numeric($appointment_length_minutes)) {
            $mins = (int)$appointment_length_minutes;
            $hours = intdiv($mins, 60);
            $rem = $mins % 60;
            $parts = [];
            if ($hours > 0) $parts[] = $hours . 'h';
            if ($rem > 0) $parts[] = $rem . 'm';
            $durText = $parts ? implode('', $parts) : ($mins . 'm');
            $lengthText = 'Your appointment is ' . $durText . ' including grace.';

            if (strpos($template, '{appointment_length}') !== false) {
                $message = str_replace('{appointment_length}', $lengthText, $message);
            } else {
                // If the template didn't include {appointment_length}, prefer to avoid redundancy by
                // ensuring the {when} already contains the range including an end time. If it does not,
                // append the length sentence.
                if (strpos($when, '—') === false) {
                    $message .= ' ' . $lengthText;
                } else {
                    // when already shows the time range, don't append the length to avoid redundancy
                }
            }
        }

        return $message;
    }

    // Resolve appointment datetime string from saved record or provided data
    private function resolveAppointmentDatetimeFromRecordOrData($record, $data)
    {
        // Prefer the stored record datetime if available
        if (!empty($record) && !empty($record['appointment_datetime'])) {
            return $record['appointment_datetime'];
        }

        // If an explicit appointment_datetime was provided in the payload, use it
        if (!empty($data['appointment_datetime'])) {
            return $data['appointment_datetime'];
        }

        // Otherwise, try to build from appointment_date and appointment_time
        if (!empty($data['appointment_date']) && !empty($data['appointment_time'])) {
            $date = $data['appointment_date'];
            $time = $data['appointment_time'];

            // Normalize time to HH:MM
            if (strpos($time, ':') === false && strlen($time) === 4) {
                $time = substr($time, 0, 2) . ':' . substr($time, 2, 2);
            }
            // If time is already HH:MM or other acceptable format, leave it as-is

            return $date . ' ' . $time . ':00';
        }

        return null;
    }
    // Helper: Check if a dentist is available for a given date/time/branch
    private function isDentistAvailable($date, $time, $branchId, $dentistId)
    {
        // First check explicit availability blocks (day off, emergency, urgent)
        try {
            $availabilityModel = new \App\Models\AvailabilityModel();
            $datetime = $date . ' ' . $time . ':00';
            // If blocked for the dentist, consider them unavailable
            if ($availabilityModel->isBlocked($dentistId, $datetime)) {
                log_message('info', "AppointmentService: Dentist {$dentistId} blocked at {$datetime}");
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'AppointmentService availability check error: ' . $e->getMessage());
            // continue to fallback check
        }

        // Fallback: check conflicts with existing appointments
        $availableDentists = $this->appointmentModel->getAvailableDentists($date, $time, $branchId);
        foreach ($availableDentists as $dentist) {
            if ($dentist['id'] == $dentistId) {
                return true;
            }
        }
        return false;
    }

    // Helper: Insert appointment (handles both walk-in and scheduled)
    private function insertAppointment($data)
    {
        // Reset duplicate flag
        $this->lastInsertWasDuplicate = false;

        // Deduplicate: if user_id and appointment datetime and branch_id are provided,
        // and an appointment already exists with the same keys, return it instead of inserting.
        try {
            $checkDatetime = null;
            if (!empty($data['appointment_datetime'])) {
                $checkDatetime = $data['appointment_datetime'];
            } elseif (!empty($data['appointment_date']) && !empty($data['appointment_time'])) {
                $time = (strlen($data['appointment_time']) === 5) ? $data['appointment_time'] : substr($data['appointment_time'],0,5);
                $checkDatetime = $data['appointment_date'] . ' ' . $time . ':00';
            }

            if (!empty($data['user_id']) && !empty($checkDatetime)) {
                $existing = $this->appointmentModel->where('user_id', $data['user_id'])
                            ->where('appointment_datetime', $checkDatetime)
                            ->whereIn('status', ['pending','pending_approval','confirmed','scheduled','ongoing'])
                            ->orderBy('created_at', 'DESC')
                            ->first();
                if ($existing) {
                    $this->lastInsertWasDuplicate = true;
                    return $existing;
                }
            }
        } catch (\Exception $e) {
            // if check fails for some reason, continue to attempt insert
            log_message('error', 'AppointmentService dedupe check failed: ' . $e->getMessage());
        }
        // If walk-in, use special model method, else use insert
        if (($data['appointment_type'] ?? '') === 'walkin') {
            $id = $this->appointmentModel->createWalkInAppointment($data);
        } else {
            $id = $this->appointmentModel->insert($data);
        }
        // Return the saved appointment record if available
        if ($id) {
            $record = $this->appointmentModel->find($id);
        } else {
            // insert did not return an id (some model implementations may return false on failure)
            // attempt to locate the inserted appointment by matching appointment_datetime, branch and contact info
            try {
                $datetime = null;
                if (!empty($data['appointment_date']) && !empty($data['appointment_time'])) {
                    $datetime = $data['appointment_date'] . ' ' . (strlen($data['appointment_time']) === 5 ? $data['appointment_time'] : substr($data['appointment_time'],0,5)) . ':00';
                } elseif (!empty($data['appointment_datetime'])) {
                    $datetime = $data['appointment_datetime'];
                }

                $query = $this->appointmentModel;
                if ($datetime) $query = $query->where('appointment_datetime', $datetime);
                if (!empty($data['branch_id'])) $query = $query->where('branch_id', $data['branch_id']);
                // Prefer searching by unique guest/email if present
                if (!empty($data['patient_email'])) $query = $query->where('patient_email', $data['patient_email']);
                if (!empty($data['patient_phone'])) $query = $query->where('patient_phone', $data['patient_phone']);

                $fallback = $query->orderBy('created_at', 'DESC')->first();
                if ($fallback) {
                    $record = $fallback;
                    // Ensure we have the inserted appointment ID so subsequent linking
                    // of services uses the correct appointment_id and computed durations
                    // can be persisted. Some model insert implementations return false
                    // or null on insert, so fallback find should populate $id.
                    if (empty($id) && isset($fallback['id'])) {
                        $id = $fallback['id'];
                    }
                } else {
                    $record = null;
                }
            } catch (\Exception $e) {
                log_message('error', 'AppointmentService fallback find failed: ' . $e->getMessage());
                $record = null;
            }
        }
            // If a service_id was provided in the payload, link it in the appointment_services table
            try {
                    if (!empty($data['service_id'])) {
                        // If client provided a procedure_duration while also providing service_id(s),
                        // ignore the client-provided duration and compute server-side from services.
                        if (isset($data['procedure_duration'])) {
                            log_message('info', 'Ignoring client-provided procedure_duration in favor of service durations for appointment creation');
                            unset($data['procedure_duration']);
                        }
                    $asm = new \App\Models\AppointmentServiceModel();
                    // Support either single id or an array of ids
                    $serviceIds = is_array($data['service_id']) ? $data['service_id'] : [$data['service_id']];
                    $totalDuration = 0;
                    $linked = [];
                    foreach ($serviceIds as $sid) {
                        if (empty($sid)) continue;
                        $asm->insert(['appointment_id' => $id, 'service_id' => $sid]);
                        $linked[] = $sid;
                        try {
                            $svcModel = new \App\Models\ServiceModel();
                            $svc = $svcModel->find($sid);
                            if ($svc) {
                                // Prefer duration_max_minutes when present for conservative scheduling
                                if (!empty($svc['duration_max_minutes'])) {
                                    $totalDuration += (int)$svc['duration_max_minutes'];
                                } elseif (!empty($svc['duration_minutes'])) {
                                    $totalDuration += (int)$svc['duration_minutes'];
                                }
                            }
                        } catch (\Exception $e) {
                            log_message('warning', 'Failed to read service duration while linking (service_id:' . $sid . '): ' . $e->getMessage());
                        }
                    }

                    // Minimal info-level audit log: appointment id, linked service ids and computed duration
                    try {
                        log_message('info', 'Linked services for appointment ' . ($id ?? 'unknown') . ': [' . implode(',', $linked) . '] (computed_duration_minutes=' . $totalDuration . ')');
                    } catch (\Exception $e) {
                        // best-effort logging; don't break booking flow
                    }

                    // After linking services: persist a computed procedure_duration on the appointment
                    // for display/backwards-compatibility. The value is computed conservatively
                    // by preferring duration_max_minutes when present. If no services were linked,
                    // allow persisting an explicit privileged procedure_duration if present.
                    if (!empty($totalDuration) && $id) {
                        try {
                            $this->appointmentModel->update($id, ['procedure_duration' => $totalDuration]);
                            log_message('info', 'Persisted computed procedure_duration ' . $totalDuration . ' for appointment ' . $id);
                        } catch (\Exception $e) {
                            log_message('error', 'Failed to persist computed procedure_duration for appointment ' . $id . ': ' . $e->getMessage());
                        }
                    } else {
                        if (!empty($totalDuration)) {
                            log_message('info', 'Linked services totalDuration for appointment ' . $id . ': ' . $totalDuration);
                        } else {
                            // No services linked. If creator provided a privileged explicit duration,
                            // persist it to the appointment record so later availability checks and
                            // messages use the authoritative duration.
                            if (!empty($data['procedure_duration'])) {
                                try {
                                    $pd = (int)$data['procedure_duration'];
                                    if ($pd > 0 && $id) {
                                        $this->appointmentModel->update($id, ['procedure_duration' => $pd]);
                                        log_message('info', 'Persisted explicit privileged procedure_duration ' . $pd . ' for appointment ' . $id);
                                    }
                                } catch (\Exception $e) {
                                    log_message('error', 'Failed to persist explicit procedure_duration for appointment ' . $id . ': ' . $e->getMessage());
                                }
                            }
                        }
                    }
                }
                // Do NOT persist grace_minutes on the appointment here. Grace periods are
                // managed centrally by admin and applied at runtime where needed. Persisting
                // a default would recreate the old fallback behaviour which we're removing.
            } catch (\Exception $e) {
                // non-fatal: log and continue
                log_message('error', 'Failed to link appointment service: ' . $e->getMessage());
            }

            // Re-fetch the record to reflect any persisted updates (procedure_duration etc.)
            if ($id) {
                try {
                    $record = $this->appointmentModel->find($id);
                } catch (\Exception $e) {
                    // if re-fetch fails, retain previously obtained $record
                    log_message('warning', 'Failed to re-fetch appointment record after linking services: ' . $e->getMessage());
                }
            }
            return $record;
        }

    public function approveAppointment($id, $dentistId = null)
    {
        try {
            // Get appointment details for notification
            $appointment = $this->appointmentModel->find($id);
            
            if (!$appointment) {
                return ['success' => false, 'message' => 'Appointment not found'];
            }
            
            $assignedDentistName = '';
            
            // If no dentist is assigned and no dentist provided, try to auto-assign
            if (!$appointment['dentist_id'] && empty($dentistId)) {
                $availableDentists = $this->appointmentModel->getAvailableDentists(
                    $appointment['appointment_date'] ?? substr($appointment['appointment_datetime'], 0, 10),
                    $appointment['appointment_time'] ?? substr($appointment['appointment_datetime'], 11, 5),
                    $appointment['branch_id']
                );
                
                if (!empty($availableDentists)) {
                    // Auto-assign the first available dentist
                    $dentistId = $availableDentists[0]['id'];
                    $assignedDentistName = $availableDentists[0]['name'];
                    log_message('info', "Auto-assigning dentist {$assignedDentistName} (ID: {$dentistId}) to appointment {$id}");
                } else {
                    return ['success' => false, 'message' => 'No dentists available at this time. Cannot approve appointment.'];
                }
            }
            
            // If dentist is being assigned, check availability
            if ($dentistId && !$appointment['dentist_id']) {
                $availableDentists = $this->appointmentModel->getAvailableDentists(
                    $appointment['appointment_date'] ?? substr($appointment['appointment_datetime'], 0, 10),
                    $appointment['appointment_time'] ?? substr($appointment['appointment_datetime'], 11, 5),
                    $appointment['branch_id']
                );
                
                $isDentistAvailable = false;
                foreach ($availableDentists as $dentist) {
                    if ($dentist['id'] == $dentistId) {
                        $isDentistAvailable = true;
                        $assignedDentistName = $dentist['name'];
                        break;
                    }
                }
                
                if (!$isDentistAvailable) {
                    return ['success' => false, 'message' => 'Selected dentist is not available at this time'];
                }
            }
            
            if ($this->appointmentModel->approveAppointment($id, $dentistId)) {
                $this->sendAppointmentNotification($appointment, 'approved');
                $message = 'Appointment approved successfully';
                if ($dentistId && !$appointment['dentist_id'] && !empty($assignedDentistName)) {
                    $message .= '. Dentist ' . $assignedDentistName . ' was assigned.';
                }
                return ['success' => true, 'message' => $message];
            } else {
                return ['success' => false, 'message' => 'Failed to approve appointment'];
            }
        } catch (\Exception $e) {
            log_message('error', 'Exception in approveAppointment: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return ['success' => false, 'message' => 'Failed to approve appointment: ' . $e->getMessage()];
        }
    }
    
    public function declineAppointment($id, $reason)
    {
        if (empty($reason)) {
            return ['success' => false, 'message' => 'Decline reason is required'];
        }

        try {
            // Get appointment details for notification before deleting
            $appointment = $this->appointmentModel->find($id);
            
            if ($this->appointmentModel->declineAppointment($id, $reason)) {
                $this->sendAppointmentNotification($appointment, 'declined', $reason);
                return ['success' => true, 'message' => 'Appointment declined successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to decline appointment'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to decline appointment: ' . $e->getMessage()];
        }
    }
    
    public function updateAppointment($id, $data)
    {
        $updateData = [
            'patient_id' => $data['patient'] ?? null,
            'branch_id' => $data['branch'] ?? null,
            'appointment_date' => $data['date'] ?? null,
            'appointment_time' => $data['time'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->appointmentModel->update($id, $updateData);
    }
    
    public function deleteAppointment($id)
    {
        return $this->appointmentModel->delete($id);
    }
    
    public function getAvailableDentists($date, $time, $branchId)
    {
        if (empty($date) || empty($time) || empty($branchId)) {
            return ['success' => false, 'message' => 'Date, time, and branch are required'];
        }

        $availableDentists = $this->appointmentModel->getAvailableDentists($date, $time, $branchId);

        return [
            'success' => true,
            'dentists' => $availableDentists
        ];
    }
    
    public function getPatientAppointments($patientId)
    {
        try {
            log_message('debug', "AppointmentService: Loading appointments for patient ID: {$patientId}");
            
            // Respect branch context if set
            $selectedBranch = session('selected_branch_id') ?: null;
            if ($selectedBranch) {
                $appointments = $this->appointmentModel->select('appointments.*, branches.name as branch_name, dentists.name as dentist_name')
                                    ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                    ->join('user as dentists', 'dentists.id = appointments.dentist_id', 'left')
                                    ->where('appointments.user_id', $patientId)
                                    ->where('appointments.branch_id', (int)$selectedBranch)
                                    ->orderBy('appointments.appointment_datetime', 'DESC')
                                    ->findAll();
            } else {
                $appointments = $this->appointmentModel->getPatientAppointments($patientId);
            }
            log_message('debug', "AppointmentService: Found " . count($appointments) . " appointments");
            log_message('debug', "AppointmentService: Raw appointments data: " . json_encode($appointments));
            
            // Categorize appointments into present (upcoming) and past
            $currentDateTime = date('Y-m-d H:i:s');
            $presentAppointments = [];
            $pastAppointments = [];
            
            foreach ($appointments as $appointment) {
                $appointmentDateTime = $appointment['appointment_datetime'] ?? ($appointment['appointment_date'] . ' ' . $appointment['appointment_time']);
                
                if ($appointmentDateTime >= $currentDateTime) {
                    $presentAppointments[] = $appointment;
                } else {
                    $pastAppointments[] = $appointment;
                }
            }
            
            log_message('debug', "AppointmentService: Categorized into " . count($presentAppointments) . " present and " . count($pastAppointments) . " past appointments");
            
            $result = [
                'success' => true,
                'present_appointments' => $presentAppointments,
                'past_appointments' => $pastAppointments,
                'total_appointments' => count($appointments)
            ];
            
            log_message('debug', "AppointmentService: Final result: " . json_encode($result));
            return $result;
            
        } catch (\Exception $e) {
            log_message('error', "AppointmentService: Exception in getPatientAppointments: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to load appointments'];
        }
    }
    
    private function sendAppointmentNotification($appointment, $action, $reason = null)
    {
        // Build a role-specific message for the patient (and persist a branch notification where applicable)
        try {
            $datetime = $appointment['appointment_datetime'] ?? 'Unknown';
            $date = isset($appointment['appointment_date']) ? $appointment['appointment_date'] : (isset($appointment['appointment_datetime']) ? substr($appointment['appointment_datetime'], 0, 10) : 'Unknown');
            $time = isset($appointment['appointment_time']) ? $appointment['appointment_time'] : (isset($appointment['appointment_datetime']) ? substr($appointment['appointment_datetime'], 11, 5) : 'Unknown');

            // Determine grace minutes: prefer appointment value, fallback to stored grace_periods.json or 15
            $grace = 15;
            if (!empty($appointment['grace_minutes'])) {
                $grace = (int)$appointment['grace_minutes'];
            } else {
                try {
                    $gpPath = WRITEPATH . 'grace_periods.json';
                    if (is_file($gpPath)) {
                        $gp = json_decode(file_get_contents($gpPath), true);
                        if (!empty($gp['default_grace'])) $grace = (int)$gp['default_grace'];
                    }
                } catch (\Exception $e) {
                    // ignore and use default
                }
            }

            // For patient-facing notifications use the patient template
            // Gather meta to include computed appointment length when available
            $meta = $this->gatherAppointmentMeta($appointment) ?: [];
            $meta['appointment_length_minutes'] = ($meta['total_service_minutes'] ?? 0) + (int)$grace;
            $patientMessage = $this->buildCreatedMessage('patient', $appointment['appointment_datetime'] ?? null, $grace, $appointment['appointment_time'] ?? null, $meta['appointment_length_minutes']);

            // Append decline reason when applicable
            if ($action === 'declined' && $reason) {
                $patientMessage .= ' Reason: ' . $reason;
            }

            // Log the constructed message for auditing
            log_message('info', strtoupper($action) . " notification for appointment {$appointment['id']}: " . $patientMessage);

            // Persist a branch notification record so staff/admin dashboards can surface it (best-effort)
            if (class_exists('App\\Models\\BranchNotificationModel')) {
                try {
                    $bnModel = new \App\Models\BranchNotificationModel();
                    $payload = json_encode([
                        'type' => 'appointment_' . $action,
                        'appointment_id' => (int)($appointment['id'] ?? 0),
                        'patient_id' => (int)($appointment['user_id'] ?? 0),
                        'message' => $patientMessage,
                    ]);

                    $bnModel->insert([
                        'branch_id' => $appointment['branch_id'] ?? null,
                        'appointment_id' => (int)($appointment['id'] ?? 0),
                        'payload' => $payload,
                        'sent' => 0,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                } catch (\Exception $e) {
                    log_message('error', 'Failed to persist branch notification: ' . $e->getMessage());
                }
            }

            // TODO: Wire up actual email/SMS sending here — keep a placeholder so future implementations can call out
        } catch (\Exception $e) {
            log_message('error', 'Error building/sending appointment notification: ' . $e->getMessage());
        }
    }

    // Gather appointment-related metadata: appointment_services rows, services, computed totals, and grace config
    private function gatherAppointmentMeta($appointment)
    {
        if (empty($appointment) || empty($appointment['id'])) return null;
        $apptId = $appointment['id'];
        $meta = [];
        try {
            // Appointment services (raw rows)
            $asm = new \App\Models\AppointmentServiceModel();
            $asRows = $asm->where('appointment_id', $apptId)->findAll();
            $meta['appointment_services'] = $asRows ?: [];

            // Services details and compute total
            $svcModel = new \App\Models\ServiceModel();
            $services = [];
            $total = 0;
            foreach ($asRows as $r) {
                if (empty($r['service_id'])) continue;
                $s = $svcModel->find($r['service_id']);
                if ($s) {
                    $services[] = $s;
                    if (!empty($s['duration_max_minutes'])) $total += (int)$s['duration_max_minutes'];
                    elseif (!empty($s['duration_minutes'])) $total += (int)$s['duration_minutes'];
                }
            }
            $meta['services'] = $services;
            $meta['total_service_minutes'] = $total;

            // Grace config (writeable JSON)
            $meta['grace_config'] = [];
            try {
                $gpPath = WRITEPATH . 'grace_periods.json';
                if (is_file($gpPath)) {
                    $gp = json_decode(file_get_contents($gpPath), true);
                    if (is_array($gp)) $meta['grace_config'] = $gp;
                }
            } catch (\Exception $e) {
                // ignore
            }
        } catch (\Exception $e) {
            log_message('warning', 'gatherAppointmentMeta failed for appointment ' . $apptId . ': ' . $e->getMessage());
        }
        return $meta;
    }
} 