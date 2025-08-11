<?php

namespace App\Controllers;

use App\Models\AppointmentModel;
use App\Models\UserModel;
use App\Controllers\Auth;

class PatientCheckin extends BaseController
{
    protected $appointmentModel;
    protected $userModel;

    public function __construct()
    {
        $this->appointmentModel = new AppointmentModel();
        $this->userModel = new UserModel();
    }

    /**
     * Patient check-in dashboard (for reception/staff)
     */
    public function index()
    {
        $user = Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['staff', 'admin'])) {
            return redirect()->to('/login');
        }

        // Get today's confirmed appointments
        $todayAppointments = $this->appointmentModel
            ->select('appointments.*, user.name as patient_name, user.phone as patient_phone, dentist.name as dentist_name, branches.name as branch_name')
            ->join('user', 'user.id = appointments.user_id')
            ->join('user as dentist', 'dentist.id = appointments.dentist_id')
            ->join('branches', 'branches.id = appointments.branch_id', 'left')
            ->where('DATE(appointment_datetime)', date('Y-m-d'))
            ->whereIn('appointments.status', ['scheduled', 'confirmed', 'checked_in', 'ongoing'])
            ->orderBy('appointment_datetime', 'ASC')
            ->findAll();

        return view('checkin/dashboard', [
            'user' => $user,
            'appointments' => $todayAppointments
        ]);
    }

    /**
     * Check in a patient
     */
    public function checkinPatient($appointmentId)
    {
        $user = Auth::getCurrentUser();
        
        // Log that this method was called
        log_message('debug', "checkinPatient method called for appointment ID: {$appointmentId}, User type: {$user['user_type']}");
        
        if (!$user || !in_array($user['user_type'], ['staff', 'admin'])) {
            log_message('error', "Unauthorized user tried to check in patient. User type: " . ($user ? $user['user_type'] : 'null'));
            return redirect()->to('/login');
        }

        $appointment = $this->appointmentModel->find($appointmentId);
        if (!$appointment) {
            log_message('error', "Appointment not found: {$appointmentId}");
            session()->setFlashdata('error', 'Appointment not found');
            return redirect()->back();
        }

        try {
            // Update appointment status to checked_in
            $data = [
                'status' => 'checked_in',
                'checked_in_at' => date('Y-m-d H:i:s'),
                'checked_in_by' => $user['id']
            ];
            
            log_message('debug', "Updating appointment with data: " . json_encode($data));
            
            $result = $this->appointmentModel->update($appointmentId, $data);

            if ($result) {
                log_message('info', "Patient checked in successfully: {$appointmentId}");
                session()->setFlashdata('success', 'Patient checked in successfully');
            } else {
                log_message('error', "Failed to check in patient: {$appointmentId}. Validation errors: " . print_r($this->appointmentModel->errors(), true));
                session()->setFlashdata('error', 'Failed to check in patient: ' . implode(', ', $this->appointmentModel->errors()));
            }
        } catch (\Exception $e) {
            log_message('error', "Exception checking in patient: {$appointmentId}. " . $e->getMessage());
            session()->setFlashdata('error', 'Error: ' . $e->getMessage());
        }

        return redirect()->back();
    }
    
    /**
     * Process check-in request (alias for checkinPatient to match routes)
     */
    public function process($appointmentId)
    {
        // Log that this method was called
        log_message('debug', "Process method called for appointment ID: {$appointmentId}");
        
        return $this->checkinPatient($appointmentId);
    }
}
