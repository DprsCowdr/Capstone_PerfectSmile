<?php

namespace App\Services;

class AppointmentService
{
    protected $appointmentModel;
    
    public function __construct()
    {
        $this->appointmentModel = new \App\Models\AppointmentModel();
    }
    
    public function getDashboardData()
    {
        // Get pending appointments for approval
        $pendingAppointments = $this->appointmentModel->getPendingApprovalAppointments();
        
        // Get today's approved appointments only
    // Reuse the model method which already applies the proper filters and splitting
    $todayAppointments = $this->appointmentModel->getTodayAppointments();
        
        // The splitDateTime is already handled by the AppointmentModel's findAll method
        
        return [
            'pendingAppointments' => $pendingAppointments,
            'todayAppointments' => $todayAppointments
        ];
    }
    
    public function getAllAppointments($branchId = null)
    {
        if ($branchId) {
            // Only fetch appointments for the given branch
            return array_filter($this->appointmentModel->getAllAppointmentsForAdmin(), function($apt) use ($branchId) {
                return ($apt['branch_id'] ?? null) == $branchId;
            });
        }
        return $this->appointmentModel->getAllAppointmentsForAdmin();
    }
    
    public function createAppointment($data)
    {
        // Validate required fields: need user and either appointment_datetime or both date+time
        if (empty($data['user_id'])) {
            return ['success' => false, 'message' => 'Required fields missing: user_id'];
        }
        $hasDateTime = !empty($data['appointment_datetime']) || (!empty($data['appointment_date']) && !empty($data['appointment_time']));
        if (!$hasDateTime) {
            return ['success' => false, 'message' => 'Required fields missing: appointment_datetime or (appointment_date and appointment_time)'];
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
        // Create walk-in appointment (auto-approved)
        $this->appointmentModel->createWalkInAppointment($data);
        return ['success' => true, 'message' => 'Walk-in appointment created successfully'];
    }
    
    private function createScheduledAppointment($data)
    {
        log_message('info', 'Creating scheduled appointment with data: ' . json_encode($data));
    // Determine duration (minutes) - accept 'duration' or 'duration_minutes' from data
        // Insert appointment and return appropriate message
        // Determine duration (minutes) - accept 'duration' or 'duration_minutes' from data
        $duration = (int)($data['duration'] ?? $data['duration_minutes'] ?? 30);

        // Before inserting, check for conflicts (respecting dentist, branch, and duration)
        $date = $data['appointment_date'] ?? substr($data['appointment_datetime'] ?? '', 0, 10);
        $time = $data['appointment_time'] ?? substr($data['appointment_datetime'] ?? '', 11, 5);
        $dentistId = $data['dentist_id'] ?? null;
        $branchId = $data['branch_id'] ?? null;

    $conflicts = $this->appointmentModel->checkAppointmentConflicts($date, $time, $dentistId, null, $branchId, $duration);
        if (!empty($conflicts)) {
            // Conflicts found — return them without attempting to calculate suggestions
            return [
                'success' => false,
                'message' => 'Conflicting appointment(s) found for the selected time.',
                'conflicts' => $conflicts
            ];
        }

        // No conflicts — proceed as before
        if (isset($data['approval_status']) && $data['approval_status'] === 'approved') {
            $data['status'] = 'confirmed';
            $data['duration_minutes'] = $duration;
            $this->insertAppointment($data);
            log_message('info', 'Admin-created appointment approved with dentist: ' . ($data['dentist_id'] ?? 'none'));
            return ['success' => true, 'message' => 'Appointment created and confirmed successfully.'];
        } else if (isset($data['approval_status']) && $data['approval_status'] === 'pending') {
            $data['status'] = 'pending_approval';
            $data['duration_minutes'] = $duration;
            $this->insertAppointment($data);
            if (!empty($data['dentist_id'])) {
                log_message('info', 'Admin-created appointment pending with dentist assigned');
                return ['success' => true, 'message' => 'Appointment request created with dentist assigned. Please review and approve.'];
            } else {
                log_message('info', 'Admin-created appointment pending without dentist');
                return ['success' => true, 'message' => 'Appointment request created. Please assign a dentist and approve.'];
            }
        } else {
            $data['approval_status'] = 'pending';
            $data['status'] = 'pending_approval';
            $data['duration_minutes'] = $duration;
            log_message('info', 'Scheduled appointment marked as pending approval - will go through waitlist');
            $this->insertAppointment($data);
            if (!empty($data['dentist_id'])) {
                return ['success' => true, 'message' => 'Appointment request submitted successfully with dentist assigned. It will be reviewed and approved by admin/staff.'];
            } else {
                return ['success' => true, 'message' => 'Appointment request submitted successfully. It will be reviewed and approved by admin/staff.'];
            }
        }
    }
    // Helper: Check if a dentist is available for a given date/time/branch
    private function isDentistAvailable($date, $time, $branchId, $dentistId)
    {
    // Default duration to 30 minutes when not provided
    $availableDentists = $this->appointmentModel->getAvailableDentists($date, $time, $branchId, 30);
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
        // Use DB transaction to reduce race conditions and optionally persist duration
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Defensive: only include duration_minutes if the DB column exists (some dev environments
            // may not have run migrations). If the column is missing, drop it from payload so insert
            // doesn't fail with SQL errors.
            if (isset($data['duration_minutes'])) {
                try {
                    $res = $db->query("SHOW COLUMNS FROM `appointments` LIKE 'duration_minutes'");
                    if (!($res && $res->getNumRows() > 0)) {
                        unset($data['duration_minutes']);
                    }
                } catch (\Exception $e) {
                    // If schema check fails for any reason, remove the field to be safe and log.
                    log_message('error', 'Schema check for duration_minutes failed: ' . $e->getMessage());
                    unset($data['duration_minutes']);
                }
            }
            if (($data['appointment_type'] ?? '') === 'walkin') {
                $this->appointmentModel->createWalkInAppointment($data);
            } else {
                $this->appointmentModel->insert($data);
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to insert appointment: ' . $e->getMessage());
            $db->transRollback();
            throw $e;
        }

        $db->transComplete();
        return $db->transStatus();
    }

    // Server-side suggestion algorithm removed. Suggestion responsibilities live on the client now.
    
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
                    $appointment['branch_id'],
                    $appointment['duration_minutes'] ?? 30
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
                // Pass duration consistently when checking availability
                $availableDentists = $this->appointmentModel->getAvailableDentists(
                    $appointment['appointment_date'] ?? substr($appointment['appointment_datetime'], 0, 10),
                    $appointment['appointment_time'] ?? substr($appointment['appointment_datetime'], 11, 5),
                    $appointment['branch_id'],
                    $appointment['duration_minutes'] ?? 30
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
                return ['success' => true, 'message' => 'Appointment declined and removed successfully'];
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
            'user_id' => $data['patient'] ?? null,
            'branch_id' => $data['branch'] ?? null,
            'appointment_date' => $data['date'] ?? null,
            'appointment_time' => $data['time'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // If date/time/duration changed, enforce conflict check
        $date = $updateData['appointment_date'] ?? null;
        $time = $updateData['appointment_time'] ?? null;
        $duration = (int)($data['duration'] ?? $data['duration_minutes'] ?? 30);
        $branchId = $updateData['branch_id'] ?? null;

        if ($date && $time) {
            $conflicts = $this->appointmentModel->checkAppointmentConflicts($date, $time, $data['dentist'] ?? $data['dentist_id'] ?? null, $id, $branchId, $duration);
            if (!empty($conflicts)) {
                // Return conflicts only; server-side suggestion algorithm removed.
                return ['success' => false, 'message' => 'Conflicting appointment(s) found for the selected time.', 'conflicts' => $conflicts];
            }
            // Only set duration when date/time changed and conflict check passed
            $updateData['duration_minutes'] = $duration;
        }

        $result = $this->appointmentModel->update($id, $updateData);
        return ['success' => (bool)$result, 'message' => $result ? 'Appointment updated' : 'Failed to update'];
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

    // Default duration to 30 minutes for available dentists lookup
    $availableDentists = $this->appointmentModel->getAvailableDentists($date, $time, $branchId, 30);

        return [
            'success' => true,
            'dentists' => $availableDentists
        ];
    }
    
    public function getPatientAppointments($patientId)
    {
        try {
            log_message('debug', "AppointmentService: Loading appointments for patient ID: {$patientId}");
            
            $appointments = $this->appointmentModel->getPatientAppointments($patientId);
            log_message('debug', "AppointmentService: Found " . count($appointments) . " appointments");
            log_message('debug', "AppointmentService: Raw appointments data: " . json_encode($appointments));
            
            // Categorize appointments into present (upcoming) and past
            $currentDateTime = date('Y-m-d H:i:s');
            $presentAppointments = [];
            $pastAppointments = [];
            
            foreach ($appointments as $appointment) {
                // Normalize composed datetime to include seconds when built from date/time
                if (isset($appointment['appointment_datetime'])) {
                    $appointmentDateTime = $appointment['appointment_datetime'];
                } else {
                    $appointmentDateTime = ($appointment['appointment_date'] ?? '') . ' ' . ($appointment['appointment_time'] ?? '') . ':00';
                }
                
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
        // TODO: Implement email/SMS notification
        // For now, we'll just log the notification
        $datetime = $appointment['appointment_datetime'] ?? 'Unknown';
        $date = isset($appointment['appointment_date']) ? $appointment['appointment_date'] : (isset($appointment['appointment_datetime']) ? substr($appointment['appointment_datetime'], 0, 10) : 'Unknown');
        $time = isset($appointment['appointment_time']) ? $appointment['appointment_time'] : (isset($appointment['appointment_datetime']) ? substr($appointment['appointment_datetime'], 11, 5) : 'Unknown');
        
        log_message('info', "Admin {$action} appointment: Patient ID {$appointment['user_id']}, DateTime: {$datetime}, Date: {$date}, Time: {$time}");
        
        if ($action === 'declined' && $reason) {
            log_message('info', "Admin decline reason: {$reason}");
        }
    }
} 