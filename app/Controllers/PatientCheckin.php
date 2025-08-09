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
        if (!$user || !in_array($user['user_type'], ['staff', 'admin'])) {
            return redirect()->to('/login');
        }

        // Log the check-in attempt
        log_message('info', "Check-in attempt for appointment ID: {$appointmentId} by user: {$user['name']} (ID: {$user['id']})");

        $appointment = $this->appointmentModel->find($appointmentId);
        if (!$appointment) {
            log_message('error', "Appointment not found: {$appointmentId}");
            session()->setFlashdata('error', 'Appointment not found');
            return redirect()->back();
        }

        // Log current appointment status
        log_message('info', "Appointment {$appointmentId} current status: {$appointment['status']}");

        // Check if appointment is confirmed or scheduled
        if (!in_array($appointment['status'], ['confirmed', 'scheduled'])) {
            log_message('warning', "Invalid status for check-in. Appointment {$appointmentId} has status: {$appointment['status']}");
            session()->setFlashdata('error', "Only confirmed or scheduled appointments can be checked in. Current status: {$appointment['status']}");
            return redirect()->back();
        }

        // Prepare update data
        $updateData = [
            'status' => 'checked_in',
            'checked_in_at' => date('Y-m-d H:i:s'),
            'checked_in_by' => $user['id']
        ];

        log_message('info', "Updating appointment {$appointmentId} with data: " . json_encode($updateData));

        // Update appointment status to checked_in (this automatically adds them to treatment queue)
        try {
            $result = $this->appointmentModel->update($appointmentId, $updateData);
            
            if ($result) {
                log_message('info', "Successfully checked in appointment {$appointmentId}");
                session()->setFlashdata('success', 'Patient checked in successfully and added to treatment queue');
            } else {
                log_message('error', "Failed to update appointment {$appointmentId}. Model update returned false.");
                
                // Get more detailed error information
                $db = \Config\Database::connect();
                $error = $db->error();
                log_message('error', "Database error: " . json_encode($error));
                
                session()->setFlashdata('error', 'Failed to check in patient. Please check the logs for details.');
            }
        } catch (\Exception $e) {
            log_message('error', "Exception during check-in for appointment {$appointmentId}: " . $e->getMessage());
            session()->setFlashdata('error', 'An error occurred during check-in: ' . $e->getMessage());
        }

        return redirect()->back();
    }
}
