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
        $today = date('Y-m-d');
        log_message('info', "PatientCheckin: Checking appointments for date: " . $today);
        
        // Get all approved appointments for today that are ready for check-in
        $todayAppointments = $this->appointmentModel
            ->select('appointments.*, user.name as patient_name, user.phone as patient_phone, dentist.name as dentist_name, branches.name as branch_name')
            ->join('user', 'user.id = appointments.user_id')
            ->join('user as dentist', 'dentist.id = appointments.dentist_id', 'left')
            ->join('branches', 'branches.id = appointments.branch_id', 'left')
            ->where('DATE(appointment_datetime)', $today)
            ->whereIn('appointments.approval_status', ['approved', 'auto_approved'])  // Accept both approved and auto_approved
            ->whereIn('appointments.status', ['scheduled', 'confirmed', 'checked_in', 'ongoing'])
            ->orderBy('appointment_datetime', 'ASC')
            ->findAll();
            
        // Enhanced logging for debugging
        log_message('info', "PatientCheckin: Found " . count($todayAppointments) . " appointments for today after filtering");
        foreach ($todayAppointments as $appt) {
            log_message('info', "PatientCheckin: Appointment ID: " . $appt['id'] . 
                       ", Status: " . $appt['status'] . 
                       ", Approval: " . $appt['approval_status'] . 
                       ", DateTime: " . $appt['appointment_datetime'] . 
                       ", Patient: " . $appt['patient_name'] .
                       ", Dentist: " . ($appt['dentist_name'] ?? 'Not assigned'));
        }

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
        log_message('info', "PatientCheckin::checkinPatient called with ID: " . $appointmentId);
        log_message('info', "Session data: " . json_encode(session()->get()));
        log_message('info', "Is logged in: " . (session()->get('isLoggedIn') ? 'YES' : 'NO'));
        log_message('info', "Request method: " . $this->request->getMethod());
        
        // Try to get current user, but don't fail if not available
        $user = Auth::getCurrentUser();
        log_message('info', "Auth::getCurrentUser result: " . json_encode($user));
        
        // If no user from Auth class, try to get from session directly
        if (!$user && session()->get('isLoggedIn')) {
            $user = [
                'id' => session()->get('user_id'),
                'name' => session()->get('name'),
                'user_type' => session()->get('user_type')
            ];
            log_message('info', "Using session user data: " . json_encode($user));
        }
        
        // Check if we have any user data and they're staff/admin
        if (!$user || !in_array($user['user_type'], ['staff', 'admin'])) {
            log_message('error', "Unauthorized access to checkinPatient. User: " . json_encode($user));
            
            // Handle AJAX requests
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Authentication required - please log in as staff or admin']);
            }
            
            session()->setFlashdata('error', 'Authentication required - please log in as staff or admin');
            return redirect()->to('/login');
        }

        log_message('info', "User authorized: " . $user['name'] . " (Type: " . $user['user_type'] . ")");

        $appointment = $this->appointmentModel->find($appointmentId);
        if (!$appointment) {
            log_message('error', "Appointment not found: " . $appointmentId);
            
            // Handle AJAX requests
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Appointment not found']);
            }
            
            session()->setFlashdata('error', 'Appointment not found');
            return redirect()->to('/checkin');
        }

        log_message('info', "Appointment found: " . json_encode($appointment));

        // Check if appointment is in correct status for check-in
        if (!in_array($appointment['status'], ['confirmed', 'scheduled'])) {
            log_message('warning', "Appointment {$appointmentId} status is {$appointment['status']}, not eligible for check-in");
            
            // Handle AJAX requests
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'This appointment is not eligible for check-in (current status: ' . $appointment['status'] . ')']);
            }
            
            session()->setFlashdata('error', 'This appointment is not eligible for check-in (current status: ' . $appointment['status'] . ')');
            return redirect()->to('/checkin');
        }

        // Update appointment status to checked_in
        $updateData = [
            'status' => 'checked_in',
            'checked_in_at' => date('Y-m-d H:i:s'),
            'checked_in_by' => $user['id']
        ];
        
        log_message('info', "Updating appointment " . $appointmentId . " with data: " . json_encode($updateData));
        
        $result = $this->appointmentModel->update($appointmentId, $updateData);

        log_message('info', "Update result: " . ($result ? 'SUCCESS' : 'FAILED'));
        
        if (!$result) {
            // Log validation errors if any
            $errors = $this->appointmentModel->errors();
            if (!empty($errors)) {
                log_message('error', "Validation errors: " . json_encode($errors));
            }
        }

        if ($result) {
            session()->setFlashdata('success', 'Patient checked in successfully! They will now appear in the treatment queue.');
            log_message('info', "Patient checked in successfully. Appointment ID: " . $appointmentId);
            
            // Handle AJAX requests
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => 'Patient checked in successfully']);
            }
        } else {
            session()->setFlashdata('error', 'Failed to check in patient');
            log_message('error', "Failed to check in patient. Appointment ID: " . $appointmentId);
            
            // Handle AJAX requests
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Failed to check in patient']);
            }
        }

        return redirect()->to('/checkin');
    }
}
