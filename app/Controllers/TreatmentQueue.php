<?php

namespace App\Controllers;

use App\Models\AppointmentModel;
use App\Models\UserModel;
use App\Controllers\Auth;

class TreatmentQueue extends BaseController
{
    protected $appointmentModel;
    protected $userModel;

    public function __construct()
    {
        $this->appointmentModel = new AppointmentModel();
        $this->userModel = new UserModel();
    }

    /**
     * Treatment queue dashboard (for dentists)
     */
    public function index()
    {
        $user = Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['doctor', 'admin'])) {
            return redirect()->to('/login');
        }

        // Get checked-in patients waiting for treatment
        $waitingPatients = $this->appointmentModel
            ->select('appointments.*, user.name as patient_name, user.phone as patient_phone, 
                     TIMESTAMPDIFF(MINUTE, checked_in_at, NOW()) as waiting_time')
            ->join('user', 'user.id = appointments.user_id')
            ->where('DATE(appointment_datetime)', date('Y-m-d'))
            ->where('appointments.status', 'checked_in');
            
        // Only filter by dentist if the current user is a doctor
        if ($user['user_type'] === 'doctor') {
            $waitingPatients = $waitingPatients->where('appointments.dentist_id', $user['id']);
        }
        
        $waitingPatients = $waitingPatients->orderBy('checked_in_at', 'ASC')
            ->findAll();

        // Get ongoing treatments
        $ongoingTreatments = $this->appointmentModel
            ->select('appointments.*, user.name as patient_name, 
                     TIMESTAMPDIFF(MINUTE, started_at, NOW()) as treatment_duration')
            ->join('user', 'user.id = appointments.user_id')
            ->where('DATE(appointment_datetime)', date('Y-m-d'))
            ->where('appointments.status', 'ongoing');
            
        // Only filter by dentist if the current user is a doctor
        if ($user['user_type'] === 'doctor') {
            $ongoingTreatments = $ongoingTreatments->where('appointments.dentist_id', $user['id']);
        }
            
        $ongoingTreatments = $ongoingTreatments->orderBy('started_at', 'ASC')
            ->findAll();

        return view('queue/dashboard', [
            'user' => $user,
            'waitingPatients' => $waitingPatients,
            'ongoingTreatments' => $ongoingTreatments
        ]);
    }

    /**
     * Call next patient for treatment
     */
    public function callNext($appointmentId)
    {
        $user = Auth::getCurrentUser();
        
        // Log that this method was called
        log_message('debug', "callNext method called for appointment ID: {$appointmentId}, User type: {$user['user_type']}");
        
        if (!$user || !in_array($user['user_type'], ['doctor', 'admin', 'staff'])) {
            log_message('error', "Unauthorized user tried to call patient. User type: " . ($user ? $user['user_type'] : 'null'));
            return redirect()->to('/login');
        }

        // Find the appointment
        $appointment = $this->appointmentModel->find($appointmentId);
        if (!$appointment) {
            log_message('error', "Appointment not found: {$appointmentId}");
            session()->setFlashdata('error', 'Appointment not found');
            return redirect()->back();
        }
        
        // Check if status is valid for calling next
        if ($appointment['status'] !== 'checked_in') {
            log_message('error', "Invalid appointment status: {$appointment['status']}");
            session()->setFlashdata('error', 'Invalid appointment or patient not checked in');
            return redirect()->back();
        }

        try {
            // Update status to ongoing
            $data = [
                'status' => 'ongoing',
                'started_at' => date('Y-m-d H:i:s'),
                'called_by' => $user['id']
            ];
            
            log_message('debug', "Updating appointment with data: " . json_encode($data));
            
            $result = $this->appointmentModel->update($appointmentId, $data);

            if ($result) {
                // If the user is a doctor, redirect to checkup module
                if ($user['user_type'] === 'doctor') {
                    log_message('info', "Doctor called patient for treatment: {$appointmentId}");
                    return redirect()->to("/checkup/patient/{$appointmentId}")
                        ->with('success', 'Patient called for treatment');
                } else {
                    // Staff and admin users should be redirected back to the check-in dashboard
                    log_message('info', "Staff/admin sent patient to treatment: {$appointmentId}");
                    session()->setFlashdata('success', 'Patient sent to treatment queue');
                    return redirect()->back();
                }
            } else {
                log_message('error', "Failed to call patient: {$appointmentId}. Validation errors: " . print_r($this->appointmentModel->errors(), true));
                session()->setFlashdata('error', 'Failed to call patient: ' . implode(', ', $this->appointmentModel->errors()));
                return redirect()->back();
            }
        } catch (\Exception $e) {
            log_message('error', "Exception calling patient: {$appointmentId}. " . $e->getMessage());
            session()->setFlashdata('error', 'Error: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Get queue status (AJAX)
     */
    public function getQueueStatus()
    {
        $user = Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['doctor', 'admin'])) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $waitingQuery = $this->appointmentModel
            ->where('DATE(appointment_datetime)', date('Y-m-d'))
            ->where('appointments.status', 'checked_in');
            
        // Only filter by dentist if the current user is a doctor
        if ($user['user_type'] === 'doctor') {
            $waitingQuery = $waitingQuery->where('appointments.dentist_id', $user['id']);
        }
        
        $waitingCount = $waitingQuery->countAllResults();

        $ongoingQuery = $this->appointmentModel
            ->where('DATE(appointment_datetime)', date('Y-m-d'))
            ->where('appointments.status', 'ongoing');
            
        // Only filter by dentist if the current user is a doctor
        if ($user['user_type'] === 'doctor') {
            $ongoingQuery = $ongoingQuery->where('appointments.dentist_id', $user['id']);
        }
        
        $ongoingCount = $ongoingQuery->countAllResults();

        return $this->response->setJSON([
            'waiting' => $waitingCount,
            'ongoing' => $ongoingCount,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
