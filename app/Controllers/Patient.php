<?php

namespace App\Controllers;

use App\Controllers\Auth;
use App\Models\PatientModel;
use App\Models\PatientMedicalHistoryModel;

class Patient extends BaseController
{
    public function dashboard()
    {
        // Check if user is logged in and is patient
        if (!Auth::isAuthenticated()) {
            return redirect()->to('/login');
        }

        $user = Auth::getCurrentUser();
        
        // Check if user is patient
        if ($user['user_type'] !== 'patient') {
            return redirect()->to('/dashboard');
        }
        
        // Get patient's appointments
        $appointmentModel = new \App\Models\AppointmentModel();
        $myAppointments = $appointmentModel->select('appointments.*, branches.name as branch_name')
                                          ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                          ->where('appointments.user_id', $user['id'])
                                          ->orderBy('appointments.appointment_datetime', 'DESC')
                                          ->limit(5)
                                          ->findAll();
        
        // Get upcoming appointments
        $upcomingAppointments = $appointmentModel->select('appointments.*, branches.name as branch_name')
                                                ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                                ->where('appointments.user_id', $user['id'])
                                                ->where('appointments.appointment_datetime >=', date('Y-m-d H:i:s'))
                                                ->whereIn('appointments.status', ['confirmed', 'scheduled'])
                                                ->orderBy('appointments.appointment_datetime', 'ASC')
                                                ->limit(3)
                                                ->findAll();
        
        // Get total appointment count
        $totalAppointments = $appointmentModel->where('user_id', $user['id'])->countAllResults();
        
        // Get completed treatments count
        $completedTreatments = $appointmentModel->where('user_id', $user['id'])
                                               ->where('status', 'completed')
                                               ->countAllResults();
        
        // Get pending appointments count
        $pendingAppointments = $appointmentModel->where('user_id', $user['id'])
                                               ->whereIn('status', ['pending', 'scheduled'])
                                               ->countAllResults();

        // Load branches for the dashboard booking panel
        $branches = [];
        if (class_exists('\App\\Models\\BranchModel')) {
            try {
                $branchModel = new \App\Models\BranchModel();
                $branches = $branchModel->orderBy('name', 'ASC')->findAll();
            } catch (\Exception $e) {
                log_message('error', 'Error loading branches for dashboard: ' . $e->getMessage());
                $branches = [];
            }
        }

        // Load dentists for the booking panel (prefer DentistModel, fall back to UserModel filter)
        $dentists = [];
        if (class_exists('\App\\Models\\DentistModel')) {
            try {
                $dentistModel = new \App\Models\DentistModel();
                $dentists = $dentistModel->orderBy('name', 'ASC')->findAll();
            } catch (\Exception $e) {
                log_message('error', 'Error loading dentists for dashboard (DentistModel): ' . $e->getMessage());
                $dentists = [];
            }
        } elseif (class_exists('\App\\Models\\UserModel')) {
            try {
                $userModel = new \App\Models\UserModel();
                $dentists = $userModel->where('user_type', 'dentist')->orderBy('name', 'ASC')->findAll();
                if (empty($dentists)) {
                    // fallback to legacy 'doctor' entries if present
                    try {
                        $dentists = $userModel->whereIn('user_type', ['dentist', 'doctor'])->orderBy('name', 'ASC')->findAll();
                    } catch (\Exception $e) {
                        $dentists = $userModel->where('user_type', 'dentist')->orWhere('user_type', 'doctor')->orderBy('name', 'ASC')->findAll();
                    }
                }
            } catch (\Exception $e) {
                log_message('error', 'Error loading dentists for dashboard (UserModel): ' . $e->getMessage());
                $dentists = [];
            }
        }

        return view('patient/dashboard', [
            'user' => $user,
            'myAppointments' => $myAppointments,
            'upcomingAppointments' => $upcomingAppointments,
            'totalAppointments' => $totalAppointments,
            'completedTreatments' => $completedTreatments,
            'pendingAppointments' => $pendingAppointments,
            'branches' => $branches,
            'dentists' => $dentists
        ]);
    }

    // Render patient calendar page
    public function calendar()
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') {
            return redirect()->to('/login');
        }

        // If the calendar refactor feature is enabled, render the new patient calendar
        $appConfig = config('App');
        if (!empty($appConfig->enableCalendarRefactor)) {
            // Load patient's appointments and branch list for the patient calendar JS
            $appointmentModel = new \App\Models\AppointmentModel();
            $appointments = $appointmentModel->where('user_id', $user['id'])
                                             ->orderBy('appointment_datetime', 'DESC')
                                             ->findAll();

            // Load branches (if Branch model exists)
            $branches = [];
            if (class_exists('\App\\Models\\BranchModel')) {
                try {
                    $branchModel = new \App\Models\BranchModel();
                    $branches = $branchModel->orderBy('name', 'ASC')->findAll();
                } catch (\Exception $e) {
                    // ignore branch loading errors; frontend can handle empty list
                    log_message('error', 'Error loading branches for patient calendar: ' . $e->getMessage());
                    $branches = [];
                }
            }

            // Load dentists for patient calendar (prefer DentistModel, fall back to UserModel)
            $dentists = [];
            if (class_exists('\App\\Models\\DentistModel')) {
                try {
                    $dentistModel = new \App\Models\DentistModel();
                    $dentists = $dentistModel->orderBy('name', 'ASC')->findAll();
                } catch (\Exception $e) {
                    log_message('error', 'Error loading dentists for patient calendar (DentistModel): ' . $e->getMessage());
                    $dentists = [];
                }
            } elseif (class_exists('\App\\Models\\UserModel')) {
                try {
                    $userModel = new \App\Models\UserModel();
                    $dentists = $userModel->where('user_type', 'dentist')->orderBy('name', 'ASC')->findAll();
                    if (empty($dentists)) {
                        // fallback to legacy 'doctor' entries
                        try {
                            $dentists = $userModel->whereIn('user_type', ['dentist', 'doctor'])->orderBy('name', 'ASC')->findAll();
                        } catch (\Exception $e) {
                            $dentists = $userModel->where('user_type', 'dentist')->orWhere('user_type', 'doctor')->orderBy('name', 'ASC')->findAll();
                        }
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Error loading dentists for patient calendar (UserModel): ' . $e->getMessage());
                    $dentists = [];
                }
            }

            // Optional selected date param
            $selectedDate = $this->request->getGet('date') ?? date('Y-m-d');

            return view('patient/calendar', [
                'user' => $user,
                'appointments' => $appointments,
                'branches' => $branches,
                'dentists' => $dentists,
                'selectedDate' => $selectedDate,
            ]);
        }

        // Fallback to legacy view if feature flag is off
        return view('patient/calendar', ['user' => $user]);
    }

    // Show book appointment form
    public function bookAppointment()
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') {
            return redirect()->to('/login');
        }

        // Load branches and dentists for the booking form if available; guard errors so view still renders.
        $branches = [];
        if (class_exists('\App\\Models\\BranchModel')) {
            try {
                $branchModel = new \App\Models\BranchModel();
                $branches = $branchModel->orderBy('name', 'ASC')->findAll();
            } catch (\Exception $e) {
                log_message('error', 'Error loading branches for bookAppointment: ' . $e->getMessage());
                $branches = [];
            }
        }

        $dentists = [];
        // Prefer a DentistModel, but fall back to User model filtered by user_type if not present
        if (class_exists('\App\\Models\\DentistModel')) {
            try {
                $dentistModel = new \App\Models\DentistModel();
                $dentists = $dentistModel->orderBy('name', 'ASC')->findAll();
            } catch (\Exception $e) {
                log_message('error', 'Error loading dentists for bookAppointment (DentistModel): ' . $e->getMessage());
                $dentists = [];
            }
        } elseif (class_exists('\App\\Models\\UserModel')) {
            try {
                $userModel = new \App\Models\UserModel();
                // Prefer explicit 'dentist' type, but also accept legacy 'doctor' entries
                $dentists = $userModel->where('user_type', 'dentist')->orderBy('name', 'ASC')->findAll();
                if (empty($dentists)) {
                    // fallback: include legacy 'doctor' user_type values
                    try {
                        $dentists = $userModel->whereIn('user_type', ['dentist', 'doctor'])->orderBy('name', 'ASC')->findAll();
                    } catch (\Exception $e) {
                        // some DBs or versions may not support whereIn in the same way; try OR clause
                        $dentists = $userModel->where('user_type', 'dentist')->orWhere('user_type', 'doctor')->orderBy('name', 'ASC')->findAll();
                    }
                }
            } catch (\Exception $e) {
                log_message('error', 'Error loading dentists for bookAppointment (UserModel): ' . $e->getMessage());
                $dentists = [];
            }
        }
        // Load services so patient booking can select a service (Guest submit expects 'service'/'service_id')
        $services = [];
        if (class_exists('\App\\Models\\ServiceModel')) {
            try {
                $serviceModel = new \App\Models\ServiceModel();
                $services = $serviceModel->orderBy('name', 'ASC')->findAll();
            } catch (\Exception $e) {
                log_message('error', 'Error loading services for bookAppointment: ' . $e->getMessage());
                $services = [];
            }
        }

        return view('patient/book_appointment', ['user' => $user, 'branches' => $branches, 'dentists' => $dentists, 'services' => $services]);
    }

    // Accept appointment submission (simple wrapper to existing guest flow)
    public function submitAppointment()
    {
        // For now, reuse Guest controller logic if available.
        // If we call Guest from another controller, ensure it has request/response objects
        // Add lightweight logging to confirm this method is hit for patient POSTs
        try {
            log_message('debug', 'Patient::submitAppointment invoked; headers: ' . json_encode($this->request->getHeaders()));
        } catch (\Exception $e) {
            // ignore
        }

        // If client requested a debug echo via X-Debug-Booking header, return the received payload as JSON
        $debugHeader = $this->request->getHeaderLine('X-Debug-Booking');
        if (!empty($debugHeader)) {
            return $this->response->setJSON([
                'received' => $this->request->getPost(),
                'headers' => $this->request->getHeaders()
            ]);
        }

        $guest = new \App\Controllers\Guest();
        if (isset($this->request)) $guest->request = $this->request;
        if (isset($this->response)) $guest->response = $this->response;
        return $guest->submitAppointment();
    }

    // Render patient appointments list
    public function appointments()
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') {
            return redirect()->to('/login');
        }

    $appointmentModel = new \App\Models\AppointmentModel();
    // Join branch and dentist/user to include readable names for the view
    $appointments = $appointmentModel
            ->select('appointments.*, branches.name AS branch_name, dentist_user.name AS dentist_name')
            ->join('branches', 'branches.id = appointments.branch_id', 'left')
            ->join('user AS dentist_user', 'dentist_user.id = appointments.dentist_id', 'left')
            ->where('appointments.user_id', $user['id'])
            ->orderBy('appointments.appointment_datetime', 'DESC')
            ->findAll();

    return view('patient/appointments', ['user' => $user, 'appointments' => $appointments]);
    }

    /**
     * Display a read-only appointment details page for the patient.
     * URL: /appointments/view/{id}
     */
    public function viewAppointment($id = null)
    {
        $id = (int) $id;
        if (! $id) {
            return redirect()->to('/appointments');
        }

        $appointmentModel = new \App\Models\AppointmentModel();

        // Ensure we only show appointments belonging to the logged-in patient
        $patientId = session()->get('user_id');

        $appointment = $appointmentModel->select('appointments.*, branches.name as branch_name, users.first_name as dentist_first_name, users.last_name as dentist_last_name')
            ->join('branches', 'branches.id = appointments.branch_id', 'left')
            ->join('users', 'users.id = appointments.dentist_id', 'left')
            ->where('appointments.id', $id)
            ->where('appointments.patient_id', $patientId)
            ->first();

        if (! $appointment) {
            return redirect()->to('/appointments')->with('error', 'Appointment not found.');
        }

        $data = [
            'appointment' => $appointment,
            'title' => 'Appointment details',
        ];

        return view('patient/view_appointment', $data);
    }

    // editAppointment removed: patient edits are handled via the change-request workflow (view-only UI)

    /**
     * Cancel appointment owned by the current patient
     */
    public function cancelAppointment($id)
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') {
            return redirect()->to('/login');
        }

        $appointmentModel = new \App\Models\AppointmentModel();
        $appointment = $appointmentModel->find((int)$id);
        if (!$appointment || (int)$appointment['user_id'] !== (int)$user['id']) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Appointment not found or access denied'])->setStatusCode(404);
            }
            return redirect()->back()->with('error', 'Appointment not found or access denied');
        }

        $reason = $this->request->getPost('reason') ?? null;

        try {
            // Use model helper which supports a cancel reason
            if (method_exists($appointmentModel, 'cancelAppointment')) {
                $appointmentModel->cancelAppointment((int)$id, $reason);
            } else {
                $appointmentModel->update((int)$id, ['status' => 'cancelled', 'decline_reason' => $reason]);
            }

            // Create a branch notification for staff to review the cancellation (best-effort)
            try {
                if (class_exists('\App\\Models\\BranchNotificationModel')) {
                    $bnModel = new \App\Models\BranchNotificationModel();
                    $payload = json_encode([
                        'type' => 'appointment_cancellation',
                        'appointment_id' => (int)$id,
                        'user_id' => (int)$user['id'],
                        'reason' => $reason,
                    ]);
                    // branch_id may be null; handle gracefully
                    $bnModel->insert([
                        'branch_id' => $appointment['branch_id'] ?? null,
                        'appointment_id' => (int)$id,
                        'payload' => $payload,
                        'sent' => 0,
                    ]);
                }
            } catch (\Exception $e) {
                log_message('error', 'Branch notification error (cancel): ' . $e->getMessage());
            }

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => 'Appointment cancelled']);
            }

            return redirect()->back()->with('success', 'Appointment cancelled');
        } catch (\Exception $e) {
            log_message('error', 'Error cancelling appointment: ' . $e->getMessage());
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to cancel appointment'])->setStatusCode(500);
            }
            return redirect()->back()->with('error', 'Failed to cancel appointment');
        }
    }

    // deleteAppointment removed: deletions are not allowed from patient UI; cancellations remain supported

    /**
     * Handle appointment update (patient editing their own appointment)
     */
    public function updateAppointment($id)
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') {
            return redirect()->to('/login');
        }

        $appointmentModel = new \App\Models\AppointmentModel();
        $appointment = $appointmentModel->find((int)$id);
        if (!$appointment || (int)$appointment['user_id'] !== (int)$user['id']) {
            return redirect()->back()->with('error', 'Appointment not found or access denied');
        }

        $post = $this->request->getPost();

        // Normalize dentist_id
        $dentistRaw = $post['dentist_id'] ?? null;
        $dentistId = null;
        if ($dentistRaw !== null && $dentistRaw !== '') {
            if (!is_numeric($dentistRaw)) {
                return redirect()->back()->with('error', 'Invalid dentist selection')->withInput();
            }
            $dentistId = (int)$dentistRaw;
        }

        // Build requested changes payload
        $requestedChanges = [
            'branch_id' => $post['branch_id'] ?? $appointment['branch_id'] ?? null,
            'dentist_id' => $dentistId ?? $appointment['dentist_id'] ?? null,
            'appointment_date' => $post['appointment_date'] ?? $appointment['appointment_date'] ?? null,
            'appointment_time' => $post['appointment_time'] ?? $appointment['appointment_time'] ?? null,
            'remarks' => $post['remarks'] ?? $appointment['remarks'] ?? null,
            'duration' => $post['duration'] ?? $appointment['duration_minutes'] ?? null,
            'service_id' => $post['service_id'] ?? $appointment['service_id'] ?? null,
        ];

        try {
            // Instead of applying the changes immediately, mark as pending and queue for staff approval
            $appointmentModel->update((int)$id, [
                'pending_change' => 1,
                'approval_status' => 'pending',
                'status' => 'pending_approval',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Insert branch notification for staff to review the change
            try {
                if (class_exists('\App\\Models\\BranchNotificationModel')) {
                    $bnModel = new \App\Models\BranchNotificationModel();
                    $payload = json_encode([
                        'type' => 'appointment_change_request',
                        'appointment_id' => (int)$id,
                        'user_id' => (int)$user['id'],
                        'requested_changes' => $requestedChanges,
                    ]);
                    $bnModel->insert([
                        'branch_id' => $appointment['branch_id'] ?? null,
                        'appointment_id' => (int)$id,
                        'payload' => $payload,
                        'sent' => 0,
                    ]);
                }
            } catch (\Exception $e) {
                log_message('error', 'Branch notification error (update request): ' . $e->getMessage());
            }

            return redirect()->to('/patient/appointments')->with('success', 'Appointment change submitted for review. A staff member will approve or decline this change.');
        } catch (\Exception $e) {
            log_message('error', 'Error creating appointment change request: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to submit appointment change')->withInput();
        }
    }

    // Patient records page with dental chart data
    public function records()
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        
        // Get dental records
        $dentalRecordModel = new \App\Models\DentalRecordModel();
        $records = $dentalRecordModel->select('dental_record.*, dentist.name as dentist_name')
            ->join('user as dentist', 'dentist.id = dental_record.dentist_id', 'left')
            ->where('dental_record.user_id', $user['id'])
            ->orderBy('dental_record.record_date', 'DESC')
            ->findAll();

        // Get dental chart data for tooth conditions
        $dentalChart = $db->table('dental_chart dc')
            ->select('dc.*, dr.record_date')
            ->join('dental_record dr', 'dr.id = dc.dental_record_id')
            ->where('dr.user_id', $user['id'])
            ->orderBy('dr.record_date', 'DESC')
            ->get()->getResultArray();

        // Get visual charts
        $visualCharts = $db->table('dental_record')
            ->select('id, record_date, visual_chart_data')
            ->where('user_id', $user['id'])
            ->where('visual_chart_data IS NOT NULL')
            ->where('visual_chart_data !=', '')
            ->orderBy('record_date', 'DESC')
            ->get()->getResultArray();

        // Process tooth conditions for latest record
        $toothConditions = [];
        $latestDate = '';
        if (!empty($dentalChart)) {
            $latestDate = $dentalChart[0]['record_date'];
            $latestChart = array_filter($dentalChart, function($row) use ($latestDate) {
                return $row['record_date'] === $latestDate;
            });
            
            foreach ($latestChart as $tooth) {
                $toothConditions[$tooth['tooth_number']] = $tooth['condition'] ?? 'healthy';
            }
        }

        // Calculate statistics
        $totalTeeth = 32;
        $teethWithData = count($toothConditions);
        $healthyTeeth = $totalTeeth - $teethWithData; // Assume unmarked teeth are healthy
        $treatmentCounts = [
            'filled' => 0,
            'crown' => 0,
            'root-canal' => 0,
            'cavity' => 0,
            'extracted' => 0
        ];
        
        foreach ($toothConditions as $condition) {
            if (isset($treatmentCounts[strtolower($condition)])) {
                $treatmentCounts[strtolower($condition)]++;
            }
        }

        return view('patient/records', [
            'user' => $user, 
            'records' => $records,
            'dentalChart' => $dentalChart,
            'visualCharts' => $visualCharts,
            'toothConditions' => $toothConditions,
            'latestDate' => $latestDate,
            'healthyTeeth' => $healthyTeeth,
            'treatmentCounts' => $treatmentCounts
        ]);
    }

    // Profile page (placeholder)
    public function profile()
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') {
            return redirect()->to('/login');
        }

        // Load dentists for profile preference selector
        $dentists = [];
        if (class_exists('\App\\Models\\UserModel')) {
            try {
                $userModel = new \App\Models\UserModel();
                $dentists = $userModel->where('user_type', 'dentist')->orderBy('name', 'ASC')->findAll();
            } catch (\Exception $e) {
                log_message('error', 'Error loading dentists for profile: ' . $e->getMessage());
                $dentists = [];
            }
        }

        return view('patient/profile', ['user' => $user, 'dentists' => $dentists]);
    }

    /**
     * Save profile changes including preferred dentist
     */
    public function saveProfile()
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') {
            return redirect()->to('/login');
        }

        $post = $this->request->getPost();
        $preferred = $post['preferred_dentist_id'] ?? null;

        // Validate preferred dentist if provided
        if (!empty($preferred)) {
            if (!is_numeric($preferred)) {
                return redirect()->back()->with('error', 'Invalid dentist selection')->withInput();
            }
            $userModel = new \App\Models\UserModel();
            $dentist = $userModel->find((int)$preferred);
            if (!$dentist || ($dentist['user_type'] ?? '') !== 'dentist') {
                return redirect()->back()->with('error', 'Selected dentist not found')->withInput();
            }
        } else {
            $preferred = null; // clear
        }

        try {
            $userModel = new \App\Models\UserModel();
            $userModel->update($user['id'], ['preferred_dentist_id' => $preferred]);
            // Update session user data if stored there
            try {
                $session = session();
                $sessionUser = $session->get('user');
                if (is_array($sessionUser)) {
                    $sessionUser['preferred_dentist_id'] = $preferred;
                    $session->set('user', $sessionUser);
                }
            } catch (\Exception $e) {
                // ignore session update failures
            }
            return redirect()->back()->with('success', 'Profile updated');
        } catch (\Exception $e) {
            log_message('error', 'Error saving profile preferred dentist: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update profile');
        }
    }

    /**
     * Save patient medical history via AJAX
     */
    public function saveMedicalHistory()
    {
        // Check if user is logged in and has appropriate permissions
        $user = Auth::getCurrentUser();
        // Get patient ID from request
        $patientId = $this->request->getPost('patient_id');
        if (!$patientId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Patient ID is required'
            ])->setStatusCode(400);
        }

        // Authorize: admin/staff/doctor can save for any patient; a patient can save their own history
        $isAuthorized = $user && (
            in_array($user['user_type'], ['admin', 'staff', 'doctor']) ||
            ($user['user_type'] === 'patient' && (int) $user['id'] === (int) $patientId)
        );

        if (!$isAuthorized) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access'
            ])->setStatusCode(403);
        }

        try {
            $patientModel = new PatientModel();
            
            // Collect medical history data from the form
            $medicalHistoryData = [
                'previous_dentist' => $this->request->getPost('previous_dentist'),
                'last_dental_visit' => $this->request->getPost('last_dental_visit'),
                'physician_name' => $this->request->getPost('physician_name'),
                'physician_specialty' => $this->request->getPost('physician_specialty'),
                'physician_phone' => $this->request->getPost('physician_phone'),
                'physician_address' => $this->request->getPost('physician_address'),
                'good_health' => $this->request->getPost('good_health'),
                'under_treatment' => $this->request->getPost('under_treatment'),
                'treatment_condition' => $this->request->getPost('treatment_condition'),
                'serious_illness' => $this->request->getPost('serious_illness'),
                'illness_details' => $this->request->getPost('illness_details'),
                'hospitalized' => $this->request->getPost('hospitalized'),
                'hospitalization_where' => $this->request->getPost('hospitalization_where'),
                'hospitalization_when' => $this->request->getPost('hospitalization_when'),
                'hospitalization_why' => $this->request->getPost('hospitalization_why'),
                'tobacco_use' => $this->request->getPost('tobacco_use'),
                'blood_pressure' => $this->request->getPost('blood_pressure'),
                'allergies' => $this->request->getPost('allergies'),
                'pregnant' => $this->request->getPost('pregnant'),
                'nursing' => $this->request->getPost('nursing'),
                'birth_control' => $this->request->getPost('birth_control'),
                'medical_conditions' => $this->request->getPost('medical_conditions') ?: [],
                'other_conditions' => $this->request->getPost('other_conditions'),
            ];

            // Remove empty values to avoid unnecessary database updates
            $medicalHistoryData = array_filter($medicalHistoryData, function($value) {
                return $value !== null && $value !== '' && $value !== [];
            });

            // Update patient medical history
            $result = $patientModel->updateMedicalHistory($patientId, $medicalHistoryData);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Medical history saved successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to save medical history'
                ])->setStatusCode(500);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error saving medical history: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while saving medical history'
            ])->setStatusCode(500);
        }
    }

    /**
     * Get patient medical history via AJAX
     */
    public function getMedicalHistory($patientId)
    {
        // Check if user is logged in and has appropriate permissions
        $user = Auth::getCurrentUser();
        $isAuthorized = $user && (
            in_array($user['user_type'], ['admin', 'staff', 'doctor']) ||
            ($user['user_type'] === 'patient' && (int) $user['id'] === (int) $patientId)
        );

        if (!$isAuthorized) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access'
            ])->setStatusCode(403);
        }

        try {
            $patientModel = new PatientModel();
            
            // Get patient with medical history
            $patient = $patientModel->getPatientWithMedicalHistory($patientId);
            
            if (!$patient) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Patient not found'
                ])->setStatusCode(404);
            }

            // Extract medical history data
            $medicalHistory = [];
            $medicalHistoryFields = [
                'previous_dentist', 'last_dental_visit',
                'physician_name', 'physician_specialty', 'physician_phone', 'physician_address',
                'good_health', 'under_treatment', 'treatment_condition', 'serious_illness',
                'illness_details', 'hospitalized', 'hospitalization_where', 'hospitalization_when', 'hospitalization_why',
                'tobacco_use', 'blood_pressure', 'allergies',
                'pregnant', 'nursing', 'birth_control',
                'medical_conditions', 'other_conditions'
            ];

            foreach ($medicalHistoryFields as $field) {
                if (isset($patient[$field])) {
                    $medicalHistory[$field] = $patient[$field];
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'medical_history' => $medicalHistory
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error getting medical history: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while getting medical history'
            ])->setStatusCode(500);
        }
    }

    /**
     * Test database connection and table structure
     */
    public function testDatabase()
    {
        try {
            $db = \Config\Database::connect();
            
            // Test if we can connect to the database
            $result = $db->query("SELECT COUNT(*) as count FROM dental_record")->getRow();
            
            // Test if we can get records for patient ID 3
            $result2 = $db->query("SELECT COUNT(*) as count FROM dental_record WHERE user_id = 3")->getRow();
            
            // Get a sample record
            $result3 = $db->query("SELECT * FROM dental_record WHERE user_id = 3 LIMIT 1")->getRow();
            
            return $this->response->setJSON([
                'success' => true,
                'total_records' => $result->count,
                'patient_3_records' => $result2->count,
                'sample_record' => $result3
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Test method to check if dental records endpoint is working
     */
    public function testTreatmentsEndpoint()
    {
        try {
            $dentalRecordModel = new \App\Models\DentalRecordModel();
            
            // Test with patient ID 3 (which we know has records)
            $patientId = 3;
            $treatments = $dentalRecordModel->where('user_id', $patientId)
                                          ->orderBy('record_date', 'DESC')
                                          ->findAll();
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Endpoint is working',
                'patient_id' => $patientId,
                'count' => count($treatments),
                'treatments' => $treatments
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Get patient treatments (dental records) via AJAX
     */
    public function getPatientTreatments($patientId)
    {
        // Check if user is logged in and has appropriate permissions
        $user = Auth::getCurrentUser();
        $isAuthorized = $user && (
            in_array($user['user_type'], ['admin', 'staff', 'doctor']) ||
            ($user['user_type'] === 'patient' && (int) $user['id'] === (int) $patientId)
        );

        if (!$isAuthorized) {
            log_message('error', 'Unauthorized access attempt to getPatientTreatments');
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access'
            ])->setStatusCode(403);
        }

        try {
            $dentalRecordModel = new \App\Models\DentalRecordModel();
            
            // Ensure patient ID is an integer
            $patientId = (int) $patientId;
            
            // Debug: Log the patient ID being searched
            log_message('info', "Searching for dental records with patient ID: {$patientId}");
            
            // Get dental records for the patient
            $treatments = $dentalRecordModel->where('user_id', $patientId)
                                          ->orderBy('record_date', 'DESC')
                                          ->findAll();

            // Debug: Log the results
            log_message('info', "Found " . count($treatments) . " dental records for patient ID: {$patientId}");
            if (count($treatments) > 0) {
                log_message('info', "First treatment: " . json_encode($treatments[0]));
            } else {
                log_message('info', "No treatments found for patient ID: {$patientId}");
            }

            // Debug: Log the response being sent
            $response = [
                'success' => true,
                'treatments' => $treatments
            ];
            log_message('info', "Sending response: " . json_encode($response));

            return $this->response->setJSON($response);

        } catch (\Exception $e) {
            log_message('error', 'Error getting patient treatments: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while getting patient treatments'
            ])->setStatusCode(500);
        }
    }

    /**
     * Get patient appointments via AJAX
     */
    public function getPatientAppointments($patientId)
    {
        // Check if user is logged in and has appropriate permissions
        $user = Auth::getCurrentUser();
        $isAuthorized = $user && (
            in_array($user['user_type'], ['admin', 'staff', 'doctor']) ||
            ($user['user_type'] === 'patient' && (int) $user['id'] === (int) $patientId)
        );

        if (!$isAuthorized) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access'
            ])->setStatusCode(403);
        }

        try {
            $appointmentModel = new \App\Models\AppointmentModel();
            
            // Get appointments for the patient
            $appointments = $appointmentModel->where('user_id', $patientId)
                                           ->orderBy('appointment_datetime', 'DESC')
                                           ->findAll();

            return $this->response->setJSON([
                'success' => true,
                'appointments' => $appointments
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error getting patient appointments: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while getting patient appointments'
            ])->setStatusCode(500);
        }
    }

    /**
     * Get patient bills via AJAX
     */
    public function getPatientBills($patientId)
    {
        // Check if user is logged in and has appropriate permissions
        $user = Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['admin', 'staff', 'doctor'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access'
            ])->setStatusCode(403);
        }

        try {
            $paymentModel = new \App\Models\PaymentModel();
            
            // Get payments for the patient
            $bills = $paymentModel->where('patient_id', $patientId)
                                 ->orderBy('created_at', 'DESC')
                                 ->findAll();

            return $this->response->setJSON([
                'success' => true,
                'bills' => $bills
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error getting patient bills: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while getting patient bills'
            ])->setStatusCode(500);
        }
    }

    /**
     * Billing page for patient
     */
    public function billing()
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') {
            return redirect()->to('/login');
        }

        $bills = [];
        if (class_exists('\App\\Models\\PaymentModel')) {
            try {
                $paymentModel = new \App\Models\PaymentModel();
                $bills = $paymentModel->where('patient_id', $user['id'])->orderBy('created_at', 'DESC')->findAll();
            } catch (\Exception $e) {
                log_message('error', 'Error loading bills for patient: ' . $e->getMessage());
                $bills = [];
            }
        }

        return view('patient/billing', ['user' => $user, 'bills' => $bills]);
    }

    /**
     * Secure messaging center (simple list)
     */
    public function messages()
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') {
            return redirect()->to('/login');
        }

        $messages = [];
        if (class_exists('\App\\Models\\MessageModel')) {
            try {
                $messageModel = new \App\Models\MessageModel();
                $messages = $messageModel->where('recipient_id', $user['id'])->orderBy('created_at', 'DESC')->findAll();
            } catch (\Exception $e) {
                log_message('error', 'Error loading messages for patient: ' . $e->getMessage());
                $messages = [];
            }
        }

        return view('patient/messages', ['user' => $user, 'messages' => $messages]);
    }

    /**
     * Forms page (medical history, consent)
     */
    public function forms()
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') {
            return redirect()->to('/login');
        }

        $medicalHistory = [];
        try {
            $patientModel = new PatientModel();
            $patient = $patientModel->getPatientWithMedicalHistory($user['id']);
            $medicalHistory = $patient ?? [];
        } catch (\Exception $e) {
            log_message('error', 'Error loading medical history for forms: ' . $e->getMessage());
            $medicalHistory = [];
        }

        return view('patient/forms', ['user' => $user, 'medicalHistory' => $medicalHistory]);
    }

    /**
     * Prescriptions / medication history
     */
    public function prescriptions()
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') {
            return redirect()->to('/login');
        }

        $prescriptions = [];
        if (class_exists('\App\\Models\\PrescriptionModel')) {
            try {
                $presModel = new \App\Models\PrescriptionModel();
                $prescriptions = $presModel->where('patient_id', $user['id'])->orderBy('issued_at', 'DESC')->findAll();
            } catch (\Exception $e) {
                log_message('error', 'Error loading prescriptions: ' . $e->getMessage());
                $prescriptions = [];
            }
        }

        return view('patient/prescriptions', ['user' => $user, 'prescriptions' => $prescriptions]);
    }

    /**
     * Treatment plan & progress
     */
    public function treatmentPlan()
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') {
            return redirect()->to('/login');
        }

        $plan = [];
        if (class_exists('\App\\Models\\TreatmentPlanModel')) {
            try {
                $tp = new \App\Models\TreatmentPlanModel();
                $plan = $tp->where('patient_id', $user['id'])->orderBy('created_at', 'DESC')->findAll();
            } catch (\Exception $e) {
                log_message('error', 'Error loading treatment plan: ' . $e->getMessage());
                $plan = [];
            }
        }

        return view('patient/treatment_plan', ['user' => $user, 'plan' => $plan]);
    }

    /**
     * Get patient's own dental chart data (API endpoint)
     */
    public function getDentalChart()
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') {
            return $this->response->setJSON(['error' => 'Unauthorized'], 401);
        }
        
        $db = \Config\Database::connect();
        
        // Get dental chart data
        $rows = $db->table('dental_chart dc')
            ->select('dc.*, dr.record_date')
            ->join('dental_record dr', 'dr.id = dc.dental_record_id')
            ->where('dr.user_id', $user['id'])
            ->orderBy('dr.record_date', 'DESC')
            ->get()->getResultArray();
        
        // Get visual chart data from dental records
        $visualChartRecords = $db->table('dental_record')
            ->select('id, record_date, visual_chart_data')
            ->where('user_id', $user['id'])
            ->where('visual_chart_data IS NOT NULL')
            ->where('visual_chart_data !=', '')
            ->orderBy('record_date', 'DESC')
            ->get()->getResultArray();
        
        return $this->response->setJSON([
            'success' => true, 
            'chart' => $rows,
            'visual_charts' => $visualChartRecords
        ]);
    }
} 