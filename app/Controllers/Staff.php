<?php

namespace App\Controllers;

use App\Controllers\Auth;

class Staff extends BaseController
{
    public function dashboard()
    {
        // Check if user is logged in and is staff
        if (!Auth::isAuthenticated()) {
            return redirect()->to('/login');
        }

        $user = Auth::getCurrentUser();
        
        // Check if user is staff
        if ($user['user_type'] !== 'staff') {
            return redirect()->to('/dashboard');
        }

        // Get appointments created by staff (pending approval) and limit to user's branches
        $appointmentModel = new \App\Models\AppointmentModel();
        $pendingAppointments = $appointmentModel->getPendingApprovalAppointments();

        // Restrict pending appointments to branches assigned to this staff user
    $branchUserModel = new \App\Models\BranchStaffModel();
        $userBranches = $branchUserModel->getUserBranches($user['id']);
        $branchIds = array_map(function($b) { return $b['branch_id']; }, $userBranches ?: []);
        if (!empty($branchIds)) {
            $pendingAppointments = array_values(array_filter($pendingAppointments, function($apt) use ($branchIds) {
                return in_array($apt['branch_id'] ?? null, $branchIds);
            }));
        } else {
            // If staff has no branch assignments, show no pending approvals
            $pendingAppointments = [];
        }
        
        // Get today's appointments
        $todayAppointments = $appointmentModel->select('appointments.*, user.name as patient_name, user.email as patient_email, branches.name as branch_name')
                                             ->join('user', 'user.id = appointments.user_id')
                                             ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                             ->where('DATE(appointments.appointment_datetime)', date('Y-m-d'))
                                             ->whereIn('appointments.status', ['confirmed', 'scheduled'])
                                             ->orderBy('appointments.appointment_datetime', 'ASC')
                                             ->findAll();
        
        // Get user counts
        $userModel = new \App\Models\UserModel();
        $totalPatients = $userModel->where('user_type', 'patient')->countAllResults();
        $totalDentists = $userModel->where('user_type', 'doctor')->countAllResults();
        
        // Get branch count
        $branchModel = new \App\Models\BranchModel();
        $totalBranches = $branchModel->countAll();
        
        // Get recent patients (last 5)
        $recentPatients = $userModel->where('user_type', 'patient')
                                   ->orderBy('created_at', 'DESC')
                                   ->limit(5)
                                   ->findAll();
        
        // Get total appointments count
        $totalAppointments = $appointmentModel->countAll();
        
        // Get this month's appointments
        $currentMonth = date('Y-m');
        $monthlyAppointments = $appointmentModel->where('DATE_FORMAT(appointment_datetime, "%Y-%m")', $currentMonth)->countAllResults();
        // Fetch unread branch notifications for this staff's branches (fallback messages when email failed)
        $branchNotifications = [];
        if (!empty($branchIds)) {
            $bnModel = new \App\Models\BranchNotificationModel();
            $branchNotifications = $bnModel->whereIn('branch_id', $branchIds)
                                         ->where('sent', 0)
                                         ->orderBy('created_at', 'DESC')
                                         ->findAll();
            // attach appointment details when possible
            $appointmentModel = new \App\Models\AppointmentModel();
            foreach ($branchNotifications as &$bn) {
                $bn['appointment'] = null;
                if (!empty($bn['appointment_id'])) {
                    $apt = $appointmentModel->find($bn['appointment_id']);
                    if ($apt) $bn['appointment'] = $apt;
                }
            }
            unset($bn);
        }

        // Fetch pending change requests for this staff's branches
        $pendingChangeRequests = [];
        if (!empty($branchIds)) {
            $pendingChangeRequests = $appointmentModel->select('appointments.*, user.name as patient_name, user.email as patient_email, branches.name as branch_name')
                                                     ->join('user', 'user.id = appointments.user_id')
                                                     ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                                     ->where('appointments.pending_change', 1)
                                                     ->whereIn('appointments.branch_id', $branchIds)
                                                     ->orderBy('appointments.updated_at', 'DESC')
                                                     ->findAll();
        }

        // Fetch recently cancelled appointments (last 24 hours) for quick visibility
        $recentCancelledAppointments = [];
        if (!empty($branchIds)) {
            $since = date('Y-m-d H:i:s', strtotime('-24 hours'));
            $recentCancelledAppointments = $appointmentModel->select('appointments.*, user.name as patient_name, user.email as patient_email, branches.name as branch_name')
                                                            ->join('user', 'user.id = appointments.user_id')
                                                            ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                                            ->where('appointments.status', 'cancelled')
                                                            ->whereIn('appointments.branch_id', $branchIds)
                                                            ->where('appointments.updated_at >=', $since)
                                                            ->orderBy('appointments.updated_at', 'DESC')
                                                            ->findAll();
        }

        return view('staff/dashboard', [
            'user' => $user,
            'pendingAppointments' => $pendingAppointments,
            'pendingChangeRequests' => $pendingChangeRequests,
            'recentCancelledAppointments' => $recentCancelledAppointments,
            'todayAppointments' => $todayAppointments,
            'totalPatients' => $totalPatients,
            'totalDentists' => $totalDentists,
            'totalBranches' => $totalBranches,
            'totalAppointments' => $totalAppointments,
            'monthlyAppointments' => $monthlyAppointments,
            'recentPatients' => $recentPatients,
            'branchNotifications' => $branchNotifications
        ]);
    }

    public function patients()
    {
        $user = Auth::getCurrentUser();
        if ($user['user_type'] !== 'staff') {
            return redirect()->to('/dashboard');
        }
        $userModel = new \App\Models\UserModel();
        $patients = $userModel->where('user_type', 'patient')->findAll();
        return view('staff/patients', [
            'user' => $user,
            'patients' => $patients,
        ]);
    }

    public function addPatient()
    {
        $user = Auth::getCurrentUser();
        if ($user['user_type'] !== 'staff') {
            return redirect()->to('/dashboard');
        }
        return view('staff/addPatient', ['user' => $user]);
    }

    public function storePatient()
    {
        $user = Auth::getCurrentUser();
        if ($user['user_type'] !== 'staff') {
            return redirect()->to('/dashboard');
        }

        $name = $this->request->getPost('name');

        // Server-side validation
        $validation =  \Config\Services::validation();
        $validation->setRules([
            'name'          => 'required',
            'address'       => 'required',
            'date_of_birth' => 'required',
            'gender'        => 'required|in_list[male,female,other]',
            'phone'         => 'required',
            'email'         => 'required|valid_email',
        ]);
        $formData = [
            'name'          => $name,
            'address'       => $this->request->getPost('address'),
            'date_of_birth' => $this->request->getPost('date_of_birth'),
            'gender'        => $this->request->getPost('gender'),
            'phone'         => $this->request->getPost('phone'),
            'email'         => $this->request->getPost('email'),
        ];
        if (!$validation->run($formData)) {
            return redirect()->back()->withInput()->with('error', $validation->getErrors());
        }

        $data = [
            'name'          => $name,
            'address'       => $this->request->getPost('address'),
            'date_of_birth' => $this->request->getPost('date_of_birth'),
            'gender'        => $this->request->getPost('gender'),
            'age'           => $this->request->getPost('age'),
            'phone'         => $this->request->getPost('phone'),
            'email'         => $this->request->getPost('email'),
            'occupation'    => $this->request->getPost('occupation'),
            'nationality'   => $this->request->getPost('nationality'),
            'user_type'     => 'patient',
            'status'        => 'active',
        ];

        $userModel = new \App\Models\UserModel();
        $userModel->skipValidation(true)->insert($data);

        return redirect()->to('/staff/patients')->with('success', 'Patient added successfully.');
    }

    public function toggleStatus($id)
    {
        $user = Auth::getCurrentUser();
        if ($user['user_type'] !== 'staff') {
            return redirect()->to('/dashboard');
        }
        
        $userModel = new \App\Models\UserModel();
        $patient = $userModel->find($id);
        
        if (!$patient || $patient['user_type'] !== 'patient') {
            return redirect()->to('/staff/patients')->with('error', 'Patient not found.');
        }
        
        // Toggle status between active and inactive
        $newStatus = ($patient['status'] === 'active') ? 'inactive' : 'active';
        
        $userModel->update($id, ['status' => $newStatus]);
        
        $statusText = ucfirst($newStatus);
        return redirect()->to('/staff/patients')->with('success', "Patient account status changed to {$statusText}.");
    }

    public function getPatient($id)
    {
        $user = Auth::getCurrentUser();
        if ($user['user_type'] !== 'staff') {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }
        
        $userModel = new \App\Models\UserModel();
        $patient = $userModel->find($id);
        
        if (!$patient || $patient['user_type'] !== 'patient') {
            return $this->response->setJSON(['error' => 'Patient not found']);
        }
        
        return $this->response->setJSON($patient);
    }

    public function updatePatient($id)
    {
        $user = Auth::getCurrentUser();
        if ($user['user_type'] !== 'staff') {
            return redirect()->to('/dashboard');
        }
        
        $userModel = new \App\Models\UserModel();
        $patient = $userModel->find($id);
        
        if (!$patient || $patient['user_type'] !== 'patient') {
            return redirect()->to('/staff/patients')->with('error', 'Patient not found.');
        }
        
        // Debug: Log the incoming data
        log_message('info', 'Staff Update Patient - ID: ' . $id);
        log_message('info', 'POST Data: ' . json_encode($this->request->getPost()));
        
        // Server-side validation
        $validation = \Config\Services::validation();
        $validation->setRules([
            'name' => 'required|min_length[2]',
            'email' => 'required|valid_email',
            'phone' => 'required',
            'address' => 'required',
            'gender' => 'required|in_list[male,female,other]',
            'date_of_birth' => 'required|valid_date',
        ]);
        
        $formData = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'address' => $this->request->getPost('address'),
            'gender' => $this->request->getPost('gender'),
            'date_of_birth' => $this->request->getPost('date_of_birth'),
        ];
        
        if (!$validation->run($formData)) {
            log_message('error', 'Staff Validation failed: ' . json_encode($validation->getErrors()));
            return redirect()->back()->withInput()->with('error', $validation->getErrors());
        }
        
        $updateData = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'address' => $this->request->getPost('address'),
            'gender' => $this->request->getPost('gender'),
            'date_of_birth' => $this->request->getPost('date_of_birth'),
            'age' => $this->request->getPost('age'),
            'occupation' => $this->request->getPost('occupation'),
            'nationality' => $this->request->getPost('nationality'),
        ];
        
        log_message('info', 'Staff Update Data: ' . json_encode($updateData));
        
        // Skip validation for update since we're not changing password or user_type
        if ($userModel->skipValidation(true)->update($id, $updateData)) {
            log_message('info', 'Staff Patient Update Success');
            return redirect()->to('/staff/patients')->with('success', 'Patient updated successfully.');
        } else {
            log_message('error', 'Staff Patient Update Failed');
            return redirect()->back()->withInput()->with('error', 'Failed to update patient.');
        }
    }

    public function appointments()
    {
        $user = Auth::getCurrentUser();
        if ($user['user_type'] !== 'staff') {
            return redirect()->to('/dashboard');
        }
        
        // Get appointments with details
        $appointmentModel = new \App\Models\AppointmentModel();
        $appointments = $appointmentModel->getAppointmentsWithDetails();
        
        // Get patients, branches, and dentists for the form
        $userModel = new \App\Models\UserModel();
        $branchModel = new \App\Models\BranchModel();
        
        $patients = $userModel->where('user_type', 'patient')->findAll();
        $branches = $branchModel->findAll();
        $dentists = $userModel->where('user_type', 'doctor')->where('status', 'active')->findAll();
        
        return view('staff/appointments', [
            'user' => $user,
            'appointments' => $appointments,
            'patients' => $patients,
            'branches' => $branches,
            'dentists' => $dentists
        ]);
    }

    /**
     * Staff approves a pending appointment (from waitlist)
     */
    public function approveAppointment($id)
    {
        if (!Auth::isAuthenticated()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $user = Auth::getCurrentUser();
        if ($user['user_type'] !== 'staff') {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $dentistId = $this->request->getPost('dentist_id');

        // Load appointment and check branch assignment
        $appointmentModel = new \App\Models\AppointmentModel();
        $appointment = $appointmentModel->find($id);
        if (!$appointment) {
            return $this->response->setJSON(['success' => false, 'message' => 'Appointment not found']);
        }

    $branchUserModel = new \App\Models\BranchStaffModel();
        if (!$branchUserModel->isUserAssignedToBranch($user['id'], $appointment['branch_id'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'You are not authorized to approve appointments for this branch']);
        }

        $appointmentService = new \App\Services\AppointmentService();
        $result = $appointmentService->approveAppointment($id, $dentistId ?: null);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON($result);
        }

        session()->setFlashdata($result['success'] ? 'success' : 'error', $result['message']);
        return redirect()->back();
    }

    /**
     * Staff declines a pending appointment (from waitlist)
     */
    public function declineAppointment($id)
    {
        if (!Auth::isAuthenticated()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $user = Auth::getCurrentUser();
        if ($user['user_type'] !== 'staff') {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $reason = $this->request->getPost('reason');
        if (empty($reason)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Decline reason is required']);
        }

        // Load appointment and check branch assignment
        $appointmentModel = new \App\Models\AppointmentModel();
        $appointment = $appointmentModel->find($id);
        if (!$appointment) {
            return $this->response->setJSON(['success' => false, 'message' => 'Appointment not found']);
        }

    $branchUserModel = new \App\Models\BranchStaffModel();
        if (!$branchUserModel->isUserAssignedToBranch($user['id'], $appointment['branch_id'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'You are not authorized to decline appointments for this branch']);
        }

        $appointmentService = new \App\Services\AppointmentService();
        $result = $appointmentService->declineAppointment($id, $reason);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON($result);
        }

        session()->setFlashdata($result['success'] ? 'success' : 'error', $result['message']);
        return redirect()->back();
    }

    public function createAppointment()
    {
        $user = Auth::getCurrentUser();
        if ($user['user_type'] !== 'staff') {
            return redirect()->to('/dashboard');
        }

        $appointmentModel = new \App\Models\AppointmentModel();
        
        // Get form data
        $appointmentType = $this->request->getPost('appointment_type') ?? 'scheduled';
        $dentistId = $this->request->getPost('doctor') ?: null;
        
        $data = [
            'branch_id' => $this->request->getPost('branch'),
            'user_id' => $this->request->getPost('patient'),
            'dentist_id' => $dentistId,
            'appointment_date' => $this->request->getPost('date'),
            'appointment_time' => $this->request->getPost('time'),
            'appointment_type' => $appointmentType,
            'remarks' => $this->request->getPost('remarks')
        ];

        // Validate required fields
        if (empty($data['user_id']) || empty($data['appointment_date']) || empty($data['appointment_time'])) {
            session()->setFlashdata('error', 'Required fields missing');
            return redirect()->back();
        }

        try {
            if ($appointmentType === 'walkin') {
                // For walk-in appointments, auto-approve
                $appointmentModel->createWalkInAppointment($data);
                session()->setFlashdata('success', 'Walk-in appointment created successfully');
            } else {
                // For scheduled appointments, always leave as pending for approval
                $data['approval_status'] = 'pending';
                $data['status'] = 'pending';
                
                $appointmentModel->insert($data);
                session()->setFlashdata('success', 'Scheduled appointment created successfully. Waiting for admin/dentist approval.');
            }
            
            return redirect()->to('/staff/appointments');
            
        } catch (\Exception $e) {
            session()->setFlashdata('error', 'Failed to create appointment: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Mark a branch notification as handled by staff
     */
    public function markNotificationHandled($id)
    {
        if (!Auth::isAuthenticated()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $user = Auth::getCurrentUser();
        if ($user['user_type'] !== 'staff') {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $bnModel = new \App\Models\BranchNotificationModel();
        $note = $bnModel->find($id);
        if (!$note) return $this->response->setJSON(['success' => false, 'message' => 'Notification not found']);

        // Verify staff is assigned to the branch
    $branchUserModel = new \App\Models\BranchStaffModel();
        if (!$branchUserModel->isUserAssignedToBranch($user['id'], $note['branch_id'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'You are not authorized for this branch']);
        }

        $bnModel->update($id, ['sent' => 1, 'sent_at' => date('Y-m-d H:i:s')]);
        return $this->response->setJSON(['success' => true, 'message' => 'Marked handled']);
    }

    /**
     * Staff approves a patient's change request for an appointment
     */
    public function approveChangeRequest($appointmentId)
    {
        if (!Auth::isAuthenticated()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $user = Auth::getCurrentUser();
        if ($user['user_type'] !== 'staff') {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $appointmentModel = new \App\Models\AppointmentModel();
        $appointment = $appointmentModel->find($appointmentId);
        if (!$appointment) return $this->response->setJSON(['success' => false, 'message' => 'Appointment not found']);

        // Check branch assignment
    $branchUserModel = new \App\Models\BranchStaffModel();
        if (!$branchUserModel->isUserAssignedToBranch($user['id'], $appointment['branch_id'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'You are not authorized for this branch']);
        }

        // Look up a branch notification for this appointment with change request
        $bnModel = new \App\Models\BranchNotificationModel();
        $bn = $bnModel->where('appointment_id', $appointmentId)->where('payload LIKE', '%appointment_change_request%')->orderBy('created_at', 'DESC')->first();

        $requestedChanges = null;
        if ($bn && !empty($bn['payload'])) {
            $payload = json_decode($bn['payload'], true);
            $requestedChanges = $payload['requested_changes'] ?? null;
        }

        if (!$requestedChanges) {
            return $this->response->setJSON(['success' => false, 'message' => 'No requested changes found']);
        }

        // Apply requested changes
        $updateData = [];
        if (isset($requestedChanges['branch_id'])) $updateData['branch_id'] = $requestedChanges['branch_id'];
        if (isset($requestedChanges['dentist_id'])) $updateData['dentist_id'] = $requestedChanges['dentist_id'];
        if (isset($requestedChanges['appointment_date']) || isset($requestedChanges['appointment_time'])) {
            if (isset($requestedChanges['appointment_date'])) $updateData['appointment_date'] = $requestedChanges['appointment_date'];
            if (isset($requestedChanges['appointment_time'])) $updateData['appointment_time'] = $requestedChanges['appointment_time'];
        }
        if (isset($requestedChanges['remarks'])) $updateData['remarks'] = $requestedChanges['remarks'];
        if (isset($requestedChanges['service_id'])) $updateData['service_id'] = $requestedChanges['service_id'];

        // Final workflow fields
        $updateData['pending_change'] = 0;
        $updateData['approval_status'] = 'approved';
        $updateData['status'] = 'confirmed';
        $updateData['updated_at'] = date('Y-m-d H:i:s');

        try {
            $appointmentModel->update($appointmentId, $updateData);

            // Mark notification handled if exists
            if ($bn) {
                $bnModel->update($bn['id'], ['sent' => 1, 'sent_at' => date('Y-m-d H:i:s')]);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Change request approved and applied']);
        } catch (\Exception $e) {
            log_message('error', 'Error applying change request: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to apply change request']);
        }
    }

    /**
     * Staff rejects a patient's change request
     */
    public function rejectChangeRequest($appointmentId)
    {
        if (!Auth::isAuthenticated()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $user = Auth::getCurrentUser();
        if ($user['user_type'] !== 'staff') {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $reason = $this->request->getPost('reason') ?: 'No reason provided';

        $appointmentModel = new \App\Models\AppointmentModel();
        $appointment = $appointmentModel->find($appointmentId);
        if (!$appointment) return $this->response->setJSON(['success' => false, 'message' => 'Appointment not found']);

        // Check branch assignment
    $branchUserModel = new \App\Models\BranchStaffModel();
        if (!$branchUserModel->isUserAssignedToBranch($user['id'], $appointment['branch_id'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'You are not authorized for this branch']);
        }

        try {
            // Clear pending change and mark approval_status as declined
            $appointmentModel->update($appointmentId, [
                'pending_change' => 0,
                'approval_status' => 'declined',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Add a branch notification marking the rejection for record
            if (class_exists('\App\\Models\\BranchNotificationModel')) {
                $bnModel = new \App\Models\BranchNotificationModel();
                $payload = json_encode([
                    'type' => 'appointment_change_rejected',
                    'appointment_id' => (int)$appointmentId,
                    'staff_id' => (int)$user['id'],
                    'reason' => $reason,
                ]);
                $bnModel->insert([
                    'branch_id' => $appointment['branch_id'] ?? null,
                    'appointment_id' => (int)$appointmentId,
                    'payload' => $payload,
                    'sent' => 0,
                ]);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Change request rejected']);
        } catch (\Exception $e) {
            log_message('error', 'Error rejecting change request: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to reject change request']);
        }
    }

    /**
     * Check for appointment conflicts - Clean implementation
     */
    public function checkConflicts()
    {
        // Check authentication
        if (!Auth::isAuthenticated()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $user = Auth::getCurrentUser();
        if ($user['user_type'] !== 'staff') {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        // Get input data
        $date = $this->request->getPost('appointment_date') ?? $this->request->getPost('date');
        $time = $this->request->getPost('appointment_time') ?? $this->request->getPost('time');
        $dentistId = $this->request->getPost('dentist_id');
        $excludeId = $this->request->getPost('exclude_id');

        // Validate required fields
        if (!$date || !$time) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Date and time are required'
            ]);
        }

        try {
            $appointmentModel = new \App\Models\AppointmentModel();
            
            // Simple conflict check - find appointments within 30-minute window
            $conflicts = $this->findTimeConflicts($date, $time, $dentistId, $excludeId);
            
            return $this->response->setJSON([
                'success' => true,
                'hasConflicts' => !empty($conflicts),
                'conflicts' => $conflicts,
                'message' => empty($conflicts) ? 'No conflicts found' : count($conflicts) . ' conflict(s) detected'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Staff conflict check error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Error checking conflicts: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Simple helper method to find time conflicts
     */
    private function findTimeConflicts($date, $time, $dentistId = null, $excludeId = null)
    {
        $appointmentModel = new \App\Models\AppointmentModel();
        
        // Create time window (30 minutes before and after)
        $requestedDateTime = $date . ' ' . $time . ':00';
        $timestamp = strtotime($requestedDateTime);
        $windowStart = date('Y-m-d H:i:s', $timestamp - (30 * 60));
        $windowEnd = date('Y-m-d H:i:s', $timestamp + (30 * 60));
        
        // Find appointments in the time window
        $query = $appointmentModel->select('appointments.*, user.name as patient_name')
                                  ->join('user', 'user.id = appointments.user_id')
                                  ->where('appointment_datetime >=', $windowStart)
                                  ->where('appointment_datetime <=', $windowEnd)
                                  ->whereIn('status', ['confirmed', 'scheduled', 'checked_in', 'ongoing']);
        
        if ($dentistId) {
            $query->where('dentist_id', $dentistId);
        }
        
        if ($excludeId) {
            $query->where('appointments.id !=', $excludeId);
        }
        
        $conflicts = $query->findAll();
        
        // Format conflicts for response
        $formattedConflicts = [];
        foreach ($conflicts as $conflict) {
            $conflictTime = date('H:i', strtotime($conflict['appointment_datetime']));
            $timeDiff = abs(strtotime($time) - strtotime($conflictTime)) / 60;
            
            $formattedConflicts[] = [
                'patient_name' => $conflict['patient_name'],
                'appointment_time' => $conflictTime,
                'time_diff' => round($timeDiff),
                'status' => $conflict['status']
            ];
        }
        
        return $formattedConflicts;
    }
} 