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
            
        // If user is a doctor, only show their assigned patients OR unassigned patients
        if ($user['user_type'] === 'doctor') {
            $waitingPatients = $waitingPatients->groupStart()
                ->where('appointments.dentist_id', $user['id'])
                ->orWhere('appointments.dentist_id IS NULL')
                ->groupEnd();
        }
        // If user is admin, show all waiting patients
        
        $waitingPatients = $waitingPatients->orderBy('checked_in_at', 'ASC')->findAll();

        // Get ongoing treatments
        $ongoingTreatments = $this->appointmentModel
            ->select('appointments.*, user.name as patient_name, 
                     TIMESTAMPDIFF(MINUTE, started_at, NOW()) as treatment_duration')
            ->join('user', 'user.id = appointments.user_id')
            ->where('DATE(appointment_datetime)', date('Y-m-d'))
            ->where('appointments.status', 'ongoing');
            
        // If user is a doctor, only show their ongoing treatments
        if ($user['user_type'] === 'doctor') {
            $ongoingTreatments = $ongoingTreatments->where('appointments.dentist_id', $user['id']);
        }
        // If user is admin, show all ongoing treatments
        
        $ongoingTreatments = $ongoingTreatments->orderBy('started_at', 'ASC')->findAll();

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
        if (!$user || !in_array($user['user_type'], ['doctor', 'admin'])) {
            return redirect()->to('/login');
        }

        $appointment = $this->appointmentModel->find($appointmentId);
        if (!$appointment || $appointment['status'] !== 'checked_in') {
            session()->setFlashdata('error', 'Invalid appointment or patient not checked in');
            return redirect()->back();
        }

        // Update status to ongoing
        $result = $this->appointmentModel->update($appointmentId, [
            'status' => 'ongoing',
            'started_at' => date('Y-m-d H:i:s'),
            'called_by' => $user['id']
        ]);

        if ($result) {
            // Redirect to checkup module
            return redirect()->to("/checkup/patient/{$appointmentId}")
                ->with('success', 'Patient called for treatment');
        } else {
            session()->setFlashdata('error', 'Failed to call patient');
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

        $waitingCountQuery = $this->appointmentModel
            ->where('DATE(appointment_datetime)', date('Y-m-d'))
            ->where('appointments.status', 'checked_in');
            
        // If user is a doctor, count their assigned + unassigned patients
        if ($user['user_type'] === 'doctor') {
            $waitingCountQuery = $waitingCountQuery->groupStart()
                ->where('appointments.dentist_id', $user['id'])
                ->orWhere('appointments.dentist_id IS NULL')
                ->groupEnd();
        }
        
        $waitingCount = $waitingCountQuery->countAllResults();

        $ongoingCountQuery = $this->appointmentModel
            ->where('DATE(appointment_datetime)', date('Y-m-d'))
            ->where('appointments.status', 'ongoing');
            
        // If user is a doctor, only count their ongoing treatments
        if ($user['user_type'] === 'doctor') {
            $ongoingCountQuery = $ongoingCountQuery->where('appointments.dentist_id', $user['id']);
        }
        
        $ongoingCount = $ongoingCountQuery->countAllResults();

        return $this->response->setJSON([
            'waiting' => $waitingCount,
            'ongoing' => $ongoingCount,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
