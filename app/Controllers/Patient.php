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
        
    // Get patient's appointments (respect selected branch if set)
    $appointmentModel = new \App\Models\AppointmentModel();
    $selectedBranch = $this->resolveBranchId();
    $baseQuery = $appointmentModel->select('appointments.*, branches.name as branch_name')
                      ->join('branches', 'branches.id = appointments.branch_id', 'left')
                      ->where('appointments.user_id', $user['id']);
    if ($selectedBranch) $baseQuery->where('appointments.branch_id', (int)$selectedBranch);
    $myAppointments = $baseQuery->orderBy('appointments.appointment_datetime', 'DESC')->limit(5)->findAll();
        
        // Get upcoming appointments
    $upcomingQuery = $appointmentModel->select('appointments.*, branches.name as branch_name')
                     ->join('branches', 'branches.id = appointments.branch_id', 'left')
                     ->where('appointments.user_id', $user['id'])
                     ->where('appointments.appointment_datetime >=', date('Y-m-d H:i:s'))
                     ->whereIn('appointments.status', ['confirmed', 'scheduled'])
                     ->orderBy('appointments.appointment_datetime', 'ASC');
    if ($selectedBranch) $upcomingQuery->where('appointments.branch_id', (int)$selectedBranch);
    $upcomingAppointments = $upcomingQuery->limit(3)->findAll();
        
    // Get total appointment counts (respect branch)
    $countBase = $appointmentModel->where('user_id', $user['id']);
    if ($selectedBranch) $countBase->where('branch_id', (int)$selectedBranch);
    $totalAppointments = $countBase->countAllResults();

    $completedBase = $appointmentModel->where('user_id', $user['id'])->where('status', 'completed');
    if ($selectedBranch) $completedBase->where('branch_id', (int)$selectedBranch);
    $completedTreatments = $completedBase->countAllResults();

    $pendingBase = $appointmentModel->where('user_id', $user['id'])->whereIn('status', ['pending', 'scheduled']);
    if ($selectedBranch) $pendingBase->where('branch_id', (int)$selectedBranch);
    $pendingAppointments = $pendingBase->countAllResults();

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
            // Resolve selected branch (may come from session/query) to scope appointments
            $selectedBranch = method_exists($this, 'resolveBranchId') ? $this->resolveBranchId() : null;

            // Load patient's appointments with patient name and branch info for the patient calendar JS
            $appointmentModel = new \App\Models\AppointmentModel();
            $appointments = $appointmentModel->select('appointments.*, user.name as patient_name, branches.name as branch_name, dentists.name as dentist_name')
                                            ->join('user', 'user.id = appointments.user_id', 'left')
                                            ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                            ->join('user as dentists', 'dentists.id = appointments.dentist_id', 'left')
                                            ->where('appointments.user_id', $user['id'])
                                            ->orderBy('appointments.appointment_datetime', 'DESC');
            if ($selectedBranch) $appointments->where('appointments.branch_id', (int)$selectedBranch);
            $appointments = $appointments->findAll();

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
            if (defined('ENVIRONMENT') && ENVIRONMENT !== 'production') {
                log_message('debug', 'Patient::submitAppointment invoked; headers: ' . json_encode($this->request->getHeaders()));
            } else {
                log_message('debug', 'Patient::submitAppointment invoked');
            }
        } catch (\Exception $e) {
            // ignore
        }

        // If client requested a debug echo via X-Debug-Booking header, return the received payload as JSON
        $debugHeader = $this->request->getHeaderLine('X-Debug-Booking');
        if (!empty($debugHeader)) {
            $postData = $this->request->getPost();
            $debugInfo = [
                'received' => $postData,
                'headers' => $this->request->getHeaders(),
                'server_time' => date('Y-m-d H:i:s'),
                'server_timezone' => date_default_timezone_get()
            ];
            
            // Try to parse appointment_datetime like the model does
            if (!empty($postData['appointment_date']) && !empty($postData['appointment_time'])) {
                $appointment_datetime = $postData['appointment_date'] . ' ' . $postData['appointment_time'];
                $debugInfo['parsed_appointment_datetime'] = $appointment_datetime;
                $debugInfo['strtotime_result'] = strtotime($appointment_datetime);
                $debugInfo['strtotime_readable'] = $debugInfo['strtotime_result'] ? date('Y-m-d H:i:s', $debugInfo['strtotime_result']) : 'FAILED';
                $debugInfo['is_past'] = $debugInfo['strtotime_result'] ? ($debugInfo['strtotime_result'] < strtotime('today')) : 'UNKNOWN';
            }
            
            return $this->response->setJSON($debugInfo);
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
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') {
            return redirect()->to('/login');
        }

        $id = (int) $id;
        if (!$id) {
            return redirect()->to('patient/appointments')->with('error', 'Invalid appointment ID');
        }

        $appointmentModel = new \App\Models\AppointmentModel();

        // Get appointment with all related details, ensure it belongs to current patient
        $appointment = $appointmentModel->select('appointments.*, user.name as patient_name, branches.name as branch_name, dentists.name as dentist_name')
            ->join('user', 'user.id = appointments.user_id', 'left')
            ->join('branches', 'branches.id = appointments.branch_id', 'left')
            ->join('user as dentists', 'dentists.id = appointments.dentist_id', 'left')
            ->where('appointments.id', $id)
            ->where('appointments.user_id', $user['id'])
            ->first();

        if (!$appointment) {
            return redirect()->to('patient/appointments')->with('error', 'Appointment not found or access denied');
        }

        // Get services for this appointment
        $db = \Config\Database::connect();
        $servicesQuery = $db->query(
            "SELECT s.id, s.name, s.duration_minutes, s.price 
             FROM appointment_service aps 
             JOIN services s ON s.id = aps.service_id 
             WHERE aps.appointment_id = ?", 
            [$id]
        );
        $services = $servicesQuery->getResultArray();

        $data = [
            'user' => $user,
            'appointment' => $appointment,
            'services' => $services,
            'title' => 'Appointment Details',
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

        // Patient-initiated cancellations are disabled via the dashboard. Patients must contact clinic staff to request cancellations.
        // This prevents accidental or unauthorized immediate cancellations. Keep the endpoint present but refuse patient attempts.
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Online cancellations via the patient dashboard are disabled. Please contact the clinic to request a cancellation.'
            ])->setStatusCode(403);
        }

        return redirect()->back()->with('error', 'Online cancellations via the patient dashboard are disabled. Please contact the clinic to request a cancellation.');
    }

    /**
     * Delete a past appointment owned by the current patient.
     * Only appointments with appointment_datetime in the past may be deleted.
     * Route: POST /patient/appointments/delete/{id}
     */
    public function deleteAppointment($id = null)
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') {
            return redirect()->to('/login');
        }

        $id = (int) $id;
        if (!$id) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Invalid appointment ID'])->setStatusCode(400);
            }
            return redirect()->back()->with('error', 'Invalid appointment ID');
        }

        $appointmentModel = new \App\Models\AppointmentModel();
        $appointment = $appointmentModel->find($id);
        if (!$appointment || (int)$appointment['user_id'] !== (int)$user['id']) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Appointment not found or access denied'])->setStatusCode(404);
            }
            return redirect()->back()->with('error', 'Appointment not found or access denied');
        }

        // Only allow deletion of past appointments OR any appointment already cancelled
        $apptTime = strtotime($appointment['appointment_datetime']);
        $status = strtolower($appointment['status'] ?? '');
        // If appointment is not cancelled and appointment time is in the future (or now), disallow
        if ($status !== 'cancelled' && $apptTime >= time()) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Only past appointments can be deleted'])->setStatusCode(403);
            }
            return redirect()->back()->with('error', 'Only past appointments can be deleted from your account.');
        }

        try {
            // Log context for debugging
            log_message('debug', 'Patient::deleteAppointment invoked for appointment_id=' . $id . ' user_id=' . ($user['id'] ?? 'unknown'));

            // Attempt to clean up linked appointment_service rows to avoid FK constraint errors
            try {
                // Prefer using the AppointmentServiceModel where available for a reliable delete
                if (class_exists('\App\Models\AppointmentServiceModel')) {
                    $asm = new \App\Models\AppointmentServiceModel();
                    log_message('debug', 'Deleting linked appointment_service rows via model for appointment_id=' . $id);
                    $asm->where('appointment_id', $id)->delete();
                } else {
                    // Fallback to raw DB table delete (best-effort)
                    $db = \Config\Database::connect();
                    log_message('debug', 'Deleting linked appointment_service rows via DB for appointment_id=' . $id);
                    $db->table('appointment_service')->where('appointment_id', $id)->delete();
                }
            } catch (\Throwable $t) {
                // Log and continue; absence or permission issues on appointment_service shouldn't block deletion
                log_message('warning', 'Failed to cleanup appointment_service rows for appointment_id=' . $id . ': ' . $t->getMessage());
            }

            // Prefer the AppointmentService if available for deletion logic
            if (class_exists('\App\Services\AppointmentService')) {
                $svc = new \App\Services\AppointmentService();
                $result = $svc->deleteAppointment($id);
                // Some services return boolean or array; normalize
                if ($result === false) {
                    throw new \RuntimeException('AppointmentService::deleteAppointment returned false');
                }
            } else {
                $res = $appointmentModel->delete($id);
                if ($res === false) {
                    throw new \RuntimeException('Model delete returned false');
                }
            }

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => 'Appointment deleted']);
            }

            return redirect()->to('/patient/appointments')->with('success', 'Appointment deleted');
        } catch (\Throwable $e) {
            // Catch Throwable to avoid uncaught Errors causing a 500 without useful JSON
            log_message('error', 'Failed to delete appointment (appointment_id=' . $id . '): ' . $e->getMessage() . '\n' . $e->getTraceAsString());
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete appointment: ' . $e->getMessage()])->setStatusCode(500);
            }
            return redirect()->back()->with('error', 'Failed to delete appointment');
        }
    }

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

    // Patient records page (simple placeholder)
    public function records()
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') {
            return redirect()->to('/login');
        }

        // Gather multiple datasets for the patient "My Records" page.
        $data = ['user' => $user];

        // Appointments (paginated)
        try {
            $appointmentModel = new \App\Models\AppointmentModel();
            $perPage = 10;
            $page = (int) ($this->request->getGet('appointments_page') ?? 1);
            $appointments = $appointmentModel
                ->select('appointments.*, branches.name AS branch_name, dentist_user.name AS dentist_name')
                ->join('branches', 'branches.id = appointments.branch_id', 'left')
                ->join('user AS dentist_user', 'dentist_user.id = appointments.dentist_id', 'left')
                ->where('appointments.user_id', $user['id'])
                ->orderBy('appointments.appointment_datetime', 'DESC')
                ->paginate($perPage, 'appointments', $page);
            $appointmentsPager = $appointmentModel->pager;
        } catch (\Exception $e) {
            log_message('error', 'Failed to load appointments for records page: ' . $e->getMessage());
            $appointments = [];
            $appointmentsPager = null;
        }

        // Dental / Treatment records
        // Treatments (paginated)
        try {
            $dentalRecordModel = new \App\Models\DentalRecordModel();
            $perPage = 8;
            $page = (int) ($this->request->getGet('treatments_page') ?? 1);
            $treatments = $dentalRecordModel->where('user_id', $user['id'])->orderBy('record_date', 'DESC')->paginate($perPage, 'treatments', $page);
            $treatmentsPager = $dentalRecordModel->pager;
        } catch (\Exception $e) {
            log_message('error', 'Failed to load dental records for records page: ' . $e->getMessage());
            $treatments = [];
            $treatmentsPager = null;
        }

        // Prescriptions (and items when available)
        // Prescriptions (paginated). Include prescriptions created by staff/admin/dentist.
        try {
            $prescriptions = [];
            if (class_exists('\App\\Models\\PrescriptionModel')) {
                $presModel = new \App\Models\PrescriptionModel();
                $perPage = 8;
                $page = (int) ($this->request->getGet('prescriptions_page') ?? 1);
                // Only patient_id must match; created_by could be staff/admin/dentist - show all prescs issued to this patient
                $prescriptions = $presModel->where('patient_id', $user['id'])->orderBy('issue_date', 'DESC')->paginate($perPage, 'prescriptions', $page);
                $prescriptionsPager = $presModel->pager;

                // attach items when model exists
                if (!empty($prescriptions)) {
                    foreach ($prescriptions as &$pres) {
                        try {
                            if (class_exists('\App\\Models\\PrescriptionItemModel')) {
                                $itemModel = new \App\Models\PrescriptionItemModel();
                                $pres['items'] = $itemModel->where('prescription_id', $pres['id'])->findAll();
                            } else {
                                $pres['items'] = [];
                            }
                        } catch (\Exception $ie) {
                            log_message('error', 'Failed to load prescription items: ' . $ie->getMessage());
                            $pres['items'] = [];
                        }
                    }
                    unset($pres);
                }
            } else {
                $prescriptionsPager = null;
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to load prescriptions for records page: ' . $e->getMessage());
            $prescriptions = [];
            $prescriptionsPager = null;
        }

        // Invoices (paginated) - use InvoiceModel for invoices
        try {
            $invoices = [];
            if (class_exists('\App\\Models\\InvoiceModel')) {
                $invoiceModel = new \App\Models\InvoiceModel();
                $perPage = 8;
                $page = (int) ($this->request->getGet('invoices_page') ?? 1);
                $invoices = $invoiceModel->where('patient_id', $user['id'])->orderBy('created_at', 'DESC')->paginate($perPage, 'invoices', $page);
                $invoicesPager = $invoiceModel->pager;
            } else {
                $invoicesPager = null;
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to load invoices for records page: ' . $e->getMessage());
            $invoices = [];
            $invoicesPager = null;
        }

    $data['appointments'] = $appointments;
    $data['appointmentsPager'] = $appointmentsPager ?? null;
    $data['treatments'] = $treatments;
    $data['treatmentsPager'] = $treatmentsPager ?? null;
    $data['prescriptions'] = $prescriptions;
    $data['prescriptionsPager'] = $prescriptionsPager ?? null;
    $data['invoices'] = $invoices;
    $data['invoicesPager'] = $invoicesPager ?? null;

        // If this is an AJAX tab request, return only the tab partial so the frontend can inject fresh content
        try {
            $isAjax = (bool) ($this->request->getGet('ajax') ?? false);
            $tab = $this->request->getGet('tab') ?? null;
        } catch (\Exception $e) {
            $isAjax = false;
            $tab = null;
        }

        if ($isAjax && $tab) {
            // Return only the partial for the requested tab. The partial will render the correct pager and items.
            return view('patient/partials/records_tab', array_merge($data, ['tab' => $tab]));
        }

        return view('patient/records', $data);
    }

    /**
     * Show a single invoice (patient-facing) and allow download as PDF.
     * Route: /patient/invoice/{id}
     */
    public function invoice($id = null)
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') {
            return redirect()->to('/login');
        }

        $id = (int) $id;
        if (!$id) {
            return redirect()->to('/patient/billing')->with('error', 'Invoice not found');
        }

        if (!class_exists('\App\\Models\\InvoiceModel')) {
            return redirect()->to('/patient/billing')->with('error', 'Billing module unavailable');
        }

        $invoiceModel = new \App\Models\InvoiceModel();
        $invoice = $invoiceModel->find($id);

        if (!$invoice || (int)$invoice['patient_id'] !== (int)$user['id']) {
            return redirect()->to('/patient/billing')->with('error', 'Invoice not found or access denied');
        }

        // Load invoice items if InvoiceItemModel exists
        $items = [];
        if (class_exists('\App\\Models\\InvoiceItemModel')) {
            try {
                $itemModel = new \App\Models\InvoiceItemModel();
                $items = $itemModel->where('invoice_id', $invoice['id'])->orderBy('id','ASC')->findAll();
            } catch (\Exception $e) { $items = []; }
        }

        return view('patient/invoice_show', ['user' => $user, 'invoice' => $invoice, 'items' => $items]);
    }

    /**
     * Download invoice as PDF if Dompdf is available, otherwise render print-friendly HTML.
     * Route: /patient/invoice/{id}/download
     */
    public function invoiceDownload($id = null)
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') {
            return redirect()->to('/login');
        }

        $id = (int) $id;
        if (!$id) return redirect()->back();

        $invoiceModel = new \App\Models\InvoiceModel();
        $invoice = $invoiceModel->find($id);
        if (!$invoice || (int)$invoice['patient_id'] !== (int)$user['id']) {
            return redirect()->back()->with('error', 'Invoice not found or access denied');
        }

        // Load invoice items via InvoiceItemModel if available
        $items = [];
        if (class_exists('\App\\Models\\InvoiceItemModel')) {
            try {
                $itemModel = new \App\Models\InvoiceItemModel();
                $items = $itemModel->where('invoice_id', $invoice['id'])->orderBy('id','ASC')->findAll();
            } catch (\Exception $e) { $items = []; }
        }

        // Render invoice HTML view (same show view)
        $html = view('patient/invoice_pdf', ['user' => $user, 'invoice' => $invoice, 'items' => $items]);

        // Try to generate PDF via Dompdf if available
        if (class_exists('\Dompdf\\Dompdf')) {
            try {
                // Increase execution time and memory for PDF rendering
                @ini_set('max_execution_time', '300');
                @set_time_limit(300);
                @ini_set('memory_limit', '512M');

                // Prepare a dedicated temp dir for Dompdf under WRITEPATH when available
                $tempDir = (defined('WRITEPATH') ? WRITEPATH : sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'writable' . DIRECTORY_SEPARATOR) . 'dompdf' . DIRECTORY_SEPARATOR;
                if (!is_dir($tempDir)) {
                    @mkdir($tempDir, 0777, true);
                }

                $dompdf = new \Dompdf\Dompdf();
                $dompdf->set_option('isPhpEnabled', true);
                $dompdf->set_option('isRemoteEnabled', true);
                $dompdf->set_option('tempDir', $tempDir);
                $dompdf->set_option('isHtml5ParserEnabled', true);
                // Use A5 landscape to match half-A4 bondpaper size
                $dompdf->setPaper('A5', 'landscape');
                $dompdf->loadHtml($html);
                $dompdf->render();
                $output = $dompdf->output();
                $filename = 'invoice_' . $invoice['id'] . '.pdf';
                return $this->response->setHeader('Content-Type', 'application/pdf')
                                      ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                                      ->setBody($output);
            } catch (\Exception $e) {
                log_message('error', 'Invoice PDF generation failed: ' . $e->getMessage());
                // Fall through to HTML response
            }
        }

        // Fallback: send HTML for printing
        return $html;
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

        // Collect posted fields we support
        $post = $this->request->getPost();
        $name = trim($post['name'] ?? '');
        $email = trim($post['email'] ?? '');
        $phone = trim($post['phone'] ?? '');
        $address = trim($post['address'] ?? '');
        $preferred = $post['preferred_dentist_id'] ?? null;

        // Basic validation
        if ($name === '') {
            return redirect()->back()->with('error', 'Name is required')->withInput();
        }

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

        // Prepare update payload
        $update = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'preferred_dentist_id' => $preferred,
        ];

        // Handle avatar upload (optional)
        try {
            $avatar = $this->request->getFile('avatar');
            if ($avatar && $avatar->isValid() && !$avatar->hasMoved()) {
                // Ensure public uploads dir exists
                if (!defined('FCPATH')) define('FCPATH', realpath(__DIR__ . '/../../public') . DIRECTORY_SEPARATOR);
                $destDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'avatars' . DIRECTORY_SEPARATOR;
                if (!is_dir($destDir)) mkdir($destDir, 0755, true);
                $newName = uniqid('avatar_') . '.' . $avatar->getClientExtension();
                $avatar->move($destDir, $newName);
                $avatarUrl = base_url('uploads/avatars/' . $newName);
                $update['avatar'] = $avatarUrl;
            }
        } catch (\Exception $e) {
            log_message('error', 'Avatar upload failed: ' . $e->getMessage());
            // proceed without avatar change
        }

        try {
            $userModel = new \App\Models\UserModel();
            $userModel->update($user['id'], $update);

            // Update session user data if stored there
            try {
                $session = session();
                $sessionUser = $session->get('user');
                if (is_array($sessionUser)) {
                    foreach ($update as $k => $v) {
                        $sessionUser[$k] = $v;
                    }
                    $session->set('user', $sessionUser);
                }
            } catch (\Exception $e) {
                // ignore session update failures
            }

            return redirect()->back()->with('success', 'Profile updated');
        } catch (\Exception $e) {
            log_message('error', 'Error saving profile: ' . $e->getMessage());
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
            $invoiceModel = new \App\Models\InvoiceModel();

            // Get invoices for the patient
            $bills = $invoiceModel->where('patient_id', $patientId)
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
        if (class_exists('\App\\Models\\InvoiceModel')) {
            try {
                $invoiceModel = new \App\Models\InvoiceModel();
                $bills = $invoiceModel->where('patient_id', $user['id'])->orderBy('created_at', 'DESC')->findAll();
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
     * Account & Security page for patient
     */
    public function security()
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') return redirect()->to('/login');
        return view('patient/security', ['user' => $user]);
    }

    /**
     * Preferences page for patient
     */
    public function preferences()
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') return redirect()->to('/login');
        return view('patient/preferences', ['user' => $user]);
    }

    /**
     * Privacy page for patient
     */
    public function privacy()
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') return redirect()->to('/login');
        return view('patient/privacy', ['user' => $user]);
    }

    /**
     * Support page for patient
     */
    public function support()
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') return redirect()->to('/login');
        return view('patient/support', ['user' => $user]);
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
                // Database column is 'issue_date' in prescriptions table (not 'issued_at')
                $prescriptions = $presModel->where('patient_id', $user['id'])->orderBy('issue_date', 'DESC')->findAll();
            } catch (\Exception $e) {
                log_message('error', 'Error loading prescriptions: ' . $e->getMessage());
                $prescriptions = [];
            }
        }

        return view('patient/prescriptions', ['user' => $user, 'prescriptions' => $prescriptions]);
    }

    /**
     * Show a single prescription to the logged-in patient.
     */
    public function prescription($id = null)
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') return redirect()->to('/login');

        $id = (int) $id;
        if (!$id) return redirect()->to('/patient/prescriptions');

        // Ensure PrescriptionModel exists
        if (!class_exists('\App\\Models\\PrescriptionModel')) {
            return redirect()->back()->with('error', 'Prescription feature unavailable');
        }

        $presModel = new \App\Models\PrescriptionModel();
        $itemModel = new \App\Models\PrescriptionItemModel();

        try {
            $pres = $presModel->find($id);
            if (!$pres) return redirect()->to('/patient/prescriptions')->with('error', 'Prescription not found');

            // Ownership check: patient_id must match logged-in user id
            $patientId = $user['id'] ?? session()->get('user_id');
            if ((int)$pres['patient_id'] !== (int)$patientId) {
                return redirect()->to('/patient/prescriptions')->with('error', 'Access denied');
            }

            $items = $itemModel->where('prescription_id', $id)->findAll();

            // Prefill fields similar to admin controller for consistent display
            $userModel = new \App\Models\UserModel();
            $patient = $userModel->find($pres['patient_id'] ?? null);
            $dentist = $userModel->find($pres['dentist_id'] ?? null);

            $patientAddress = $patient['address'] ?? '';
            if (strpos($patientAddress, '@') !== false || $patientAddress === ($patient['email'] ?? '')) {
                $patientAddress = '';
            }

            $pres['patient_name'] = $patient['name'] ?? 'Unknown';
            $pres['patient_address'] = $patientAddress;
            $pres['patient_age'] = $patient['age'] ?? '';
            $pres['patient_gender'] = $patient['gender'] ?? '';
            $pres['instructions'] = $pres['notes'] ?? null;
            $pres['dentist_name'] = $pres['dentist_name'] ?? ($dentist['name'] ?? 'Unknown');
            $pres['license_no'] = $pres['license_no'] ?? ($dentist['license_no'] ?? '');
            $pres['ptr_no'] = $pres['ptr_no'] ?? ($dentist['ptr_no'] ?? '');

            return view('patient/prescription_show', ['user' => $user, 'prescription' => $pres, 'items' => $items]);
        } catch (\Exception $e) {
            log_message('error', 'Error loading patient prescription: ' . $e->getMessage());
            return redirect()->to('/patient/prescriptions')->with('error', 'Failed to load prescription');
        }
    }

    /**
     * Return HTML-only preview for a patient's prescription (used by modal)
     */
    public function previewPrescription($id = null)
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') return $this->response->setStatusCode(403)->setBody('Forbidden');

        $id = (int)$id;
        if (!$id) return $this->response->setStatusCode(404)->setBody('Not found');

        if (!class_exists('\App\\Models\\PrescriptionModel')) {
            return $this->response->setStatusCode(404)->setBody('Not found');
        }

        $presModel = new \App\Models\PrescriptionModel();
        $itemModel = new \App\Models\PrescriptionItemModel();

        $pres = $presModel->find($id);
        if (!$pres) return $this->response->setStatusCode(404)->setBody('Not found');

        $patientId = $user['id'] ?? session()->get('user_id');
        if ((int)$pres['patient_id'] !== (int)$patientId) {
            return $this->response->setStatusCode(403)->setBody('Access denied');
        }

        $items = $itemModel->where('prescription_id', $id)->findAll();

        // Merge patient/dentist like admin preview
        $userModel = new \App\Models\UserModel();
        $patient = $userModel->find($pres['patient_id'] ?? null);
        $dentist = $userModel->find($pres['dentist_id'] ?? null);
        $patientAddress = $patient['address'] ?? '';
        if (strpos($patientAddress, '@') !== false || $patientAddress === ($patient['email'] ?? '')) {
            $patientAddress = '';
        }
        $pres['patient_name'] = $patient['name'] ?? 'Unknown';
        $pres['patient_address'] = $patientAddress;
        $pres['patient_age'] = $patient['age'] ?? '';
        $pres['patient_gender'] = $patient['gender'] ?? '';
        $pres['instructions'] = $pres['notes'] ?? null;
        $pres['dentist_name'] = $pres['dentist_name'] ?? ($dentist['name'] ?? 'Unknown');
        $pres['license_no'] = $pres['license_no'] ?? ($dentist['license_no'] ?? '');
        $pres['ptr_no'] = $pres['ptr_no'] ?? ($dentist['ptr_no'] ?? '');

        // Return HTML preview (same template used by admin preview)
        return view('prescriptions/pdf', ['prescription' => $pres, 'items' => $items]);
    }

    /**
     * Generate and stream a PDF file for a patient's prescription if Dompdf is available.
     */
    public function downloadPrescriptionFile($id = null)
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') return redirect()->to('/login');

        $id = (int)$id;
        if (!$id) return redirect()->to('/patient/prescriptions')->with('error', 'Not found');

        if (!class_exists('\App\\Models\\PrescriptionModel')) {
            return redirect()->back()->with('error', 'Feature unavailable');
        }

        $presModel = new \App\Models\PrescriptionModel();
        $itemModel = new \App\Models\PrescriptionItemModel();

        $pres = $presModel->find($id);
        if (!$pres) return redirect()->to('/patient/prescriptions')->with('error', 'Not found');

        $patientId = $user['id'] ?? session()->get('user_id');
        if ((int)$pres['patient_id'] !== (int)$patientId) {
            return redirect()->to('/patient/prescriptions')->with('error', 'Access denied');
        }

        $items = $itemModel->where('prescription_id', $id)->findAll();

        // Merge patient/dentist like admin download
        $userModel = new \App\Models\UserModel();
        $patient = $userModel->find($pres['patient_id'] ?? null);
        $dentist = $userModel->find($pres['dentist_id'] ?? null);
        $patientAddress = $patient['address'] ?? '';
        if (strpos($patientAddress, '@') !== false || $patientAddress === ($patient['email'] ?? '')) {
            $patientAddress = '';
        }
        $pres['patient_name'] = $patient['name'] ?? 'Unknown';
        $pres['patient_address'] = $patientAddress;
        $pres['patient_age'] = $patient['age'] ?? '';
        $pres['patient_gender'] = $patient['gender'] ?? '';
        $pres['instructions'] = $pres['notes'] ?? null;
        $pres['dentist_name'] = $pres['dentist_name'] ?? ($dentist['name'] ?? 'Unknown');
        $pres['license_no'] = $pres['license_no'] ?? ($dentist['license_no'] ?? '');
        $pres['ptr_no'] = $pres['ptr_no'] ?? ($dentist['ptr_no'] ?? '');

        $html = view('prescriptions/pdf_download', ['prescription' => $pres, 'items' => $items]);

        if (!class_exists('\Dompdf\\Dompdf')) {
            // If Dompdf not available, show HTML in browser
            return $this->response->setBody($html);
        }

    // Increase execution time and memory for PDF rendering
    @ini_set('max_execution_time', '300');
    @set_time_limit(300);
    @ini_set('memory_limit', '512M');

    // Prepare temp dir for Dompdf
    $tempDir = (defined('WRITEPATH') ? WRITEPATH : sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'writable' . DIRECTORY_SEPARATOR) . 'dompdf' . DIRECTORY_SEPARATOR;
    if (!is_dir($tempDir)) { @mkdir($tempDir, 0777, true); }

    $dompdf = new \Dompdf\Dompdf();
    $dompdf->set_option('isPhpEnabled', true);
    $dompdf->set_option('isRemoteEnabled', true);
    $dompdf->set_option('tempDir', $tempDir);
    $dompdf->set_option('isHtml5ParserEnabled', true);
    // A5 landscape for half-A4 prescription form
    $dompdf->setPaper('A5', 'landscape');
        $dompdf->loadHtml($html);
        $dompdf->render();

        $pdfOutput = $dompdf->output();

        return $this->response->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="prescription_'.$id.'.pdf"')
            ->setBody($pdfOutput);
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
     * Show a single treatment/dental record to the patient.
     */
    public function treatment($id = null)
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') return redirect()->to('/login');

        $id = (int) $id;
        if (!$id) return redirect()->to('/patient/records');

        if (!class_exists('\App\\Models\\DentalRecordModel')) {
            return redirect()->back()->with('error', 'Treatment records unavailable');
        }

        try {
            $dr = new \App\Models\DentalRecordModel();
            $record = $dr->find($id);
            if (!$record) return redirect()->to('/patient/records')->with('error', 'Record not found');

            // Ownership check: ensure record belongs to patient
            if ((int)($record['user_id'] ?? $record['patient_id'] ?? 0) !== (int)$user['id']) {
                return redirect()->to('/patient/records')->with('error', 'Access denied');
            }

            return view('patient/treatment_show', ['user' => $user, 'record' => $record]);
        } catch (\Exception $e) {
            log_message('error', 'Error loading treatment record for patient: ' . $e->getMessage());
            return redirect()->to('/patient/records')->with('error', 'Failed to load record');
        }
    }

    /**
     * Get appointment details for patient (read-only)
     * Ensures patients can only access their own appointment details
     */
    public function getAppointmentDetails($id)
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        $id = (int) $id;
        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid appointment ID'])->setStatusCode(400);
        }

        try {
            $appointmentModel = new \App\Models\AppointmentModel();
            
            // Get appointment with user_id check to ensure patient can only access their own appointments
            $appointment = $appointmentModel->select('appointments.*, user.name as patient_name, branches.name as branch_name, dentists.name as dentist_name')
                                          ->join('user', 'user.id = appointments.user_id', 'left')
                                          ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                          ->join('user as dentists', 'dentists.id = appointments.dentist_id', 'left')
                                          ->where('appointments.id', $id)
                                          ->where('appointments.user_id', $user['id']) // Critical: only own appointments
                                          ->first();

            if (!$appointment) {
                return $this->response->setJSON(['success' => false, 'message' => 'Appointment not found or access denied'])->setStatusCode(404);
            }

            // Get associated services
            $db = \Config\Database::connect();
            $serviceRows = $db->table('appointment_service')
                             ->select('services.id, services.name, services.duration_minutes')
                             ->join('services', 'services.id = appointment_service.service_id', 'left')
                             ->where('appointment_service.appointment_id', $id)
                             ->get()->getResultArray();

            $services = [];
            $totalDuration = 0;
            foreach ($serviceRows as $row) {
                $duration = !empty($row['duration_minutes']) ? (int)$row['duration_minutes'] : 0;
                $services[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'duration_minutes' => $duration
                ];
                $totalDuration += $duration;
            }

            $appointment['services'] = $services;
            $appointment['service_duration'] = $totalDuration;

            return $this->response->setJSON([
                'success' => true,
                'appointment' => $appointment
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error getting patient appointment details: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Server error'
            ])->setStatusCode(500);
        }
    }

    /**
     * Get service details for patient (read-only)
     * Patients can view service information but not modify it
     */
    public function getService($id)
    {
        $user = Auth::getCurrentUser();
        if (!$user || $user['user_type'] !== 'patient') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        $id = (int) $id;
        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid service ID'])->setStatusCode(400);
        }

        try {
            $serviceModel = new \App\Models\ServiceModel();
            $service = $serviceModel->find($id);

            if (!$service) {
                return $this->response->setJSON(['success' => false, 'message' => 'Service not found'])->setStatusCode(404);
            }

            return $this->response->setJSON([
                'success' => true,
                'service' => $service
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error getting service details for patient: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Server error'
            ])->setStatusCode(500);
        }
    }
} 