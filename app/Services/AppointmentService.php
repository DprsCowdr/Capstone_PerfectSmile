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
        $todayAppointments = $this->appointmentModel->select('appointments.*, user.name as patient_name, user.email as patient_email, branches.name as branch_name')
                                             ->join('user', 'user.id = appointments.user_id')
                                             ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                             ->where('DATE(appointments.appointment_datetime)', date('Y-m-d'))
                                             ->where('appointments.approval_status', 'approved') // Only approved appointments
                                             ->whereIn('appointments.status', ['confirmed', 'scheduled'])
                                             ->orderBy('appointments.appointment_datetime', 'ASC')
                                             ->findAll();
        
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
        // Validate required fields
        if (empty($data['user_id']) || empty($data['appointment_date']) || empty($data['appointment_time'])) {
            return ['success' => false, 'message' => 'Required fields missing'];
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
    // If dentist is assigned, allow booking regardless of dentist availability
        // Insert appointment and return appropriate message
        if (isset($data['approval_status']) && $data['approval_status'] === 'approved') {
            $data['status'] = 'confirmed';
            $this->insertAppointment($data);
            log_message('info', 'Admin-created appointment approved with dentist: ' . ($data['dentist_id'] ?? 'none'));
            return ['success' => true, 'message' => 'Appointment created and confirmed successfully.'];
        } else if (isset($data['approval_status']) && $data['approval_status'] === 'pending') {
            $data['status'] = 'pending_approval';
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
        // If walk-in, use special model method, else use insert
        if (($data['appointment_type'] ?? '') === 'walkin') {
            $this->appointmentModel->createWalkInAppointment($data);
        } else {
            $this->appointmentModel->insert($data);
        }
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
            $appointments = $this->appointmentModel->getPatientAppointments($patientId);
            
            return [
                'success' => true,
                'appointments' => $appointments
            ];
        } catch (\Exception $e) {
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