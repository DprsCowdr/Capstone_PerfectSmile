<?php

namespace App\Controllers;

use App\Services\AuthService;

class StaffController extends BaseAdminController
{
    protected function getAuthenticatedUser()
    {
        return AuthService::checkStaffAuth();
    }
    
    protected function getAuthenticatedUserApi()
    {
        return AuthService::checkStaffAuthApi();
    }

    // ==================== DASHBOARD ====================
    public function dashboard()
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        // Get dashboard data
        $appointmentData = $this->appointmentService->getDashboardData();
        $userStats = $this->userService->getUserStatistics();
        
        // Get branch count
        $branchModel = new \App\Models\BranchModel();
        $totalBranches = $branchModel->countAll();
        
        // Get recent patients
        $recentPatients = $this->userService->getRecentPatients(5);
        
        // Restrict pending approvals to staff-assigned branches
        $branchUserModel = new \App\Models\BranchUserModel();
        $userBranches = $branchUserModel->getUserBranches($user['id']);
        $branchIds = array_map(function($b) { return $b['branch_id']; }, $userBranches ?: []);
        $pendingAppointments = $appointmentData['pendingAppointments'] ?? [];
        if (!empty($branchIds)) {
            $pendingAppointments = array_values(array_filter($pendingAppointments, function($apt) use ($branchIds) {
                return in_array($apt['branch_id'] ?? null, $branchIds);
            }));
        } else {
            $pendingAppointments = [];
        }

        return view('staff/dashboard', [
            'user' => $user,
            'pendingAppointments' => $pendingAppointments,
            'todayAppointments' => $appointmentData['todayAppointments'],
            'totalPatients' => $userStats['total_patients'],
            'totalDentists' => $userStats['total_dentists'],
            'totalBranches' => $totalBranches,
            'recentPatients' => $recentPatients
        ]);
    }

    /**
     * Staff approves a pending appointment (from waitlist)
     */
    public function approveAppointment($id)
    {
        $user = $this->getAuthenticatedUserApi();
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }

        // Only staff allowed
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

        $branchUserModel = new \App\Models\BranchUserModel();
        if (!$branchUserModel->isUserAssignedToBranch($user['id'], $appointment['branch_id'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'You are not authorized to approve appointments for this branch']);
        }

        $result = $this->appointmentService->approveAppointment($id, $dentistId ?: null);

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
        $user = $this->getAuthenticatedUserApi();
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }

        if ($user['user_type'] !== 'staff') {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $reason = $this->request->getPost('reason');
        if (empty($reason)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Decline reason is required']);
        }

        $appointmentModel = new \App\Models\AppointmentModel();
        $appointment = $appointmentModel->find($id);
        if (!$appointment) {
            return $this->response->setJSON(['success' => false, 'message' => 'Appointment not found']);
        }

        $branchUserModel = new \App\Models\BranchUserModel();
        if (!$branchUserModel->isUserAssignedToBranch($user['id'], $appointment['branch_id'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'You are not authorized to decline appointments for this branch']);
        }

        $result = $this->appointmentService->declineAppointment($id, $reason);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON($result);
        }

        session()->setFlashdata($result['success'] ? 'success' : 'error', $result['message']);
        return redirect()->back();
    }

    // ==================== PATIENT MANAGEMENT ====================
    public function patients()
    {
        return $this->getPatientsView('staff/patients');
    }

    public function addPatient()
    {
        return $this->getAddPatientView('staff/addPatient');
    }

    public function storePatient()
    {
        return $this->storePatientLogic('/staff/patients');
    }

    public function getPatient($id)
    {
        return $this->getPatientApi($id);
    }

    public function updatePatient($id)
    {
        return $this->updatePatientLogic($id, '/staff/patients');
    }

    public function toggleStatus($id)
    {
        return $this->togglePatientStatusLogic($id, '/staff/patients');
    }

    // ==================== APPOINTMENT MANAGEMENT ====================
    public function appointments()
    {
        return $this->getAppointmentsView('staff/appointments');
    }

    public function createAppointment()
    {
        return $this->createAppointmentLogic('/staff/appointments', 'staff');
    }

    /**
     * AJAX endpoint to check appointment conflicts
     */
    public function checkConflicts()
    {
        $user = $this->getAuthenticatedUserApi();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $date = $this->request->getPost('date');
        $time = $this->request->getPost('time');
        $dentist_id = $this->request->getPost('dentist_id');
        $branch_id = $this->request->getPost('branch_id');

        if (!$date || !$time) {
            return $this->response->setJSON(['success' => false, 'message' => 'Date and time are required']);
        }

        try {
            $appointmentModel = new \App\Models\AppointmentModel();
            $conflicts = $appointmentModel->checkAppointmentConflicts($date, $time, $dentist_id, null, $branch_id);
            
            if (empty($conflicts)) {
                return $this->response->setJSON([
                    'success' => true, 
                    'hasConflicts' => false,
                    'message' => 'No conflicts found'
                ]);
            }

            // Format conflict information for frontend
            $conflictDetails = [];
            foreach ($conflicts as $conflict) {
                $conflictDetails[] = [
                    'patient_name' => $conflict['patient_name'],
                    'dentist_name' => $conflict['dentist_name'] ?? 'Unassigned',
                    'appointment_time' => $conflict['appointment_time'],
                    'status' => $conflict['status'],
                    'time_diff' => $this->calculateTimeDifference($time, $conflict['appointment_time'])
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'hasConflicts' => true,
                'conflicts' => $conflictDetails,
                'message' => count($conflicts) . ' potential conflict(s) found'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Staff conflict check error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Error checking conflicts'
            ]);
        }
    }

    /**
     * Calculate time difference in minutes between two time strings
     */
    private function calculateTimeDifference($time1, $time2)
    {
        $t1 = strtotime($time1);
        $t2 = strtotime($time2);
        return abs($t1 - $t2) / 60; // Convert to minutes
    }
}
