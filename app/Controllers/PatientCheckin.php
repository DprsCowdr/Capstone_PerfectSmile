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
        if (!$user || !in_array($user['user_type'], ['staff', 'admin'])) {
            return redirect()->to('/login');
        }

        log_message('info', "User authorized: " . $user['name'] . " (Type: " . $user['user_type'] . ")");

        $appointment = $this->appointmentModel->find($appointmentId);
        if (!$appointment) {
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

        // Update appointment status to checked_in and create check-in record
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Update appointment status
            $appointmentResult = $this->appointmentModel->update($appointmentId, [
                'status' => 'checked_in'
            ]);

            if (!$appointmentResult) {
                throw new \Exception('Failed to update appointment status');
            }

            // Create patient check-in record
            $patientCheckinModel = new \App\Models\PatientCheckinModel();
            $checkinResult = $patientCheckinModel->insert([
                'appointment_id' => $appointmentId,
                'patient_id' => $appointment['user_id'],
                'checked_in_at' => date('Y-m-d H:i:s'),
                'checked_in_by' => $user['id'],
                'status' => 'checked_in',
                'notes' => 'Patient checked in via admin interface'
            ]);

            if (!$checkinResult) {
                throw new \Exception('Failed to create check-in record');
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            session()->setFlashdata('success', 'Patient checked in successfully');
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Check-in failed: ' . $e->getMessage());
            session()->setFlashdata('error', 'Failed to check in patient: ' . $e->getMessage());
        }

        return redirect()->to('/checkin');
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
