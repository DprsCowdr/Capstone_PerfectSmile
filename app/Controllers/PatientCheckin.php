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

        $appointment = $this->appointmentModel->find($appointmentId);
        if (!$appointment) {
            session()->setFlashdata('error', 'Appointment not found');
            return redirect()->back();
        }

        // Update appointment status to checked_in
        $result = $this->appointmentModel->update($appointmentId, [
            'status' => 'checked_in',
            'checked_in_at' => date('Y-m-d H:i:s'),
            'checked_in_by' => $user['id']
        ]);

        if ($result) {
            session()->setFlashdata('success', 'Patient checked in successfully');
        } else {
            session()->setFlashdata('error', 'Failed to check in patient');
        }

        return redirect()->back();
    }
}
