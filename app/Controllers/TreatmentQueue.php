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
    if (!$user || !in_array($user['user_type'], ['dentist', 'doctor', 'admin', 'staff'])) {
            return redirect()->to('/login');
        }

        // Get checked-in patients waiting for treatment
        $waitingQuery = $this->appointmentModel
            ->select('appointments.*, user.name as patient_name, user.phone as patient_phone, 
                     patient_checkins.checked_in_at,
                     TIMESTAMPDIFF(MINUTE, patient_checkins.checked_in_at, NOW()) as waiting_time')
            ->join('user', 'user.id = appointments.user_id')
            ->join('patient_checkins', 'patient_checkins.appointment_id = appointments.id')
            ->where('DATE(appointment_datetime)', date('Y-m-d'))
            ->where('appointments.status', 'checked_in');

        // Only filter by dentist if the current user is a dentist/doctor
        if (in_array($user['user_type'], ['dentist', 'doctor'])) {
            $waitingQuery = $waitingQuery->where('appointments.dentist_id', $user['id']);
        }

        $waitingPatients = $waitingQuery->orderBy('patient_checkins.checked_in_at', 'ASC')->findAll();

        // Get ongoing treatments
        $ongoingQuery = $this->appointmentModel
            ->select('appointments.*, user.name as patient_name, 
                     treatment_sessions.started_at,
                     TIMESTAMPDIFF(MINUTE, treatment_sessions.started_at, NOW()) as treatment_duration')
            ->join('user', 'user.id = appointments.user_id')
            ->join('treatment_sessions', 'treatment_sessions.appointment_id = appointments.id')
            ->where('DATE(appointment_datetime)', date('Y-m-d'))
            ->where('appointments.status', 'ongoing');

        // Only filter by dentist if the current user is a dentist/doctor
        if (in_array($user['user_type'], ['dentist', 'doctor'])) {
            $ongoingQuery = $ongoingQuery->where('appointments.dentist_id', $user['id']);
        }

        $ongoingTreatments = $ongoingQuery->orderBy('treatment_sessions.started_at', 'ASC')->findAll();

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
        
        // Log that this method was called (protect against null user)
        log_message('debug', "callNext method called for appointment ID: {$appointmentId}, User type: " . ($user ? $user['user_type'] : 'null'));

        if (!$user || !in_array($user['user_type'], ['dentist', 'doctor', 'admin', 'staff'])) {
            log_message('error', "Unauthorized user tried to call patient. User type: " . ($user ? $user['user_type'] : 'null'));
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(401)->setJSON(['success' => false, 'error' => 'Unauthorized']);
            }
            return redirect()->to('/login');
        }

        // Find the appointment
        $appointment = $this->appointmentModel->find($appointmentId);
        if (!$appointment) {
            log_message('error', "Appointment not found: {$appointmentId}");
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'error' => 'Appointment not found']);
            }
            session()->setFlashdata('error', 'Appointment not found');
            return redirect()->back();
        }
        
        // Check if status is valid for calling next
        if ($appointment['status'] !== 'checked_in') {
            log_message('error', "Invalid appointment status: {$appointment['status']}");
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON(['success' => false, 'error' => 'Invalid appointment or patient not checked in']);
            }
            session()->setFlashdata('error', 'Invalid appointment or patient not checked in');
            return redirect()->back();
        }

        try {
            // Start database transaction
            $db = \Config\Database::connect();
            $db->transStart();

            // Update appointment status to ongoing
            $appointmentResult = $this->appointmentModel->update($appointmentId, [
                'status' => 'ongoing'
            ]);

            if (!$appointmentResult) {
                throw new \Exception('Failed to update appointment status');
            }

            // Create treatment session record using model helper
            $treatmentSessionModel = new \App\Models\TreatmentSessionModel();
            $sessionResult = $treatmentSessionModel->startSession(
                $appointmentId,
                $user['id'],
                $appointment['dentist_id'] ?? null,
                'normal',
                null
            );

            if (!$sessionResult) {
                $tErrors = method_exists($treatmentSessionModel, 'errors') ? $treatmentSessionModel->errors() : [];
                log_message('error', 'TreatmentSession insert failed. Model errors: ' . json_encode($tErrors));
                throw new \Exception('Failed to create treatment session');
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            log_message('debug', "Successfully called patient for treatment: {$appointmentId}");

            // Prepare success payload
            $payload = ['success' => true, 'appointmentId' => $appointmentId, 'message' => 'Patient called for treatment'];
            // If AJAX, return JSON so client JS can handle it
            if ($this->request->isAJAX()) {
                log_message('info', "callNext AJAX success for appointment: {$appointmentId}");
                return $this->response->setJSON($payload);
            }

            // If the user is a dentist/doctor, redirect to checkup module
            if (in_array($user['user_type'], ['dentist', 'doctor'])) {
                log_message('info', "Dentist called patient for treatment: {$appointmentId}");
                return redirect()->to("/checkup/patient/{$appointmentId}")
                    ->with('success', 'Patient called for treatment');
            } else {
                // Staff and admin users should be redirected back to the check-in dashboard
                log_message('info', "Staff/admin sent patient to treatment: {$appointmentId}");
                session()->setFlashdata('success', 'Patient sent to treatment queue');
                return redirect()->back();
            }

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', "Exception calling patient: {$appointmentId}. " . $e->getMessage());
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'error' => 'Failed to call patient: ' . $e->getMessage()]);
            }
            session()->setFlashdata('error', 'Failed to call patient: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Get queue status (AJAX)
     */
    public function getQueueStatus()
    {
        $user = Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['dentist', 'doctor', 'admin', 'staff'])) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $waitingQuery = $this->appointmentModel
            ->where('DATE(appointment_datetime)', date('Y-m-d'))
            ->where('appointments.status', 'checked_in');
            
        // Only filter by dentist if the current user is a dentist/doctor
        if (in_array($user['user_type'], ['dentist', 'doctor'])) {
            $waitingQuery = $waitingQuery->where('appointments.dentist_id', $user['id']);
        }
        
        $waitingCount = $waitingQuery->countAllResults();

        $ongoingQuery = $this->appointmentModel
            ->where('DATE(appointment_datetime)', date('Y-m-d'))
            ->where('appointments.status', 'ongoing');
            
        // Only filter by dentist if the current user is a dentist/doctor
        if (in_array($user['user_type'], ['dentist', 'doctor'])) {
            $ongoingQuery = $ongoingQuery->where('appointments.dentist_id', $user['id']);
        }
        
        $ongoingCount = $ongoingQuery->countAllResults();

        return $this->response->setJSON([
            'waiting' => $waitingCount,
            'ongoing' => $ongoingCount,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Complete a treatment
     */
    public function completeTreatment($appointmentId)
    {
        $user = Auth::getCurrentUser();
        
        log_message('debug', "completeTreatment method called for appointment ID: {$appointmentId}");
        
        if (!$user || !in_array($user['user_type'], ['dentist', 'doctor', 'admin', 'staff'])) {
            log_message('error', "Unauthorized user tried to complete treatment. User type: " . ($user ? $user['user_type'] : 'null'));
            return redirect()->to('/login');
        }

        $appointment = $this->appointmentModel->find($appointmentId);
        if (!$appointment) {
            log_message('error', "Appointment not found: {$appointmentId}");
            session()->setFlashdata('error', 'Appointment not found');
            return redirect()->back();
        }

        if ($appointment['status'] !== 'ongoing') {
            log_message('error', "Invalid appointment status for completion: {$appointment['status']}");
            session()->setFlashdata('error', 'Treatment is not currently ongoing');
            return redirect()->back();
        }

        try {
            $data = [
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s'),
                'completed_by' => $user['id']
            ];
            
            log_message('debug', "Completing treatment with data: " . json_encode($data));
            
            $result = $this->appointmentModel->update($appointmentId, $data);

            if ($result) {
                log_message('info', "Treatment completed successfully: {$appointmentId}");
                session()->setFlashdata('success', 'Treatment completed successfully');
            } else {
                log_message('error', "Failed to complete treatment: {$appointmentId}. Validation errors: " . print_r($this->appointmentModel->errors(), true));
                session()->setFlashdata('error', 'Failed to complete treatment: ' . implode(', ', $this->appointmentModel->errors()));
            }
        } catch (\Exception $e) {
            log_message('error', "Exception completing treatment: {$appointmentId}. " . $e->getMessage());
            session()->setFlashdata('error', 'Error: ' . $e->getMessage());
        }

        return redirect()->back();
    }
}
