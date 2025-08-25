<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\DashboardService;

class StaffController extends BaseAdminController
{
    protected $dashboardService;
    
    public function __construct()
    {
        parent::__construct();
        $this->dashboardService = new DashboardService();
    }
    
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

        // Derive a friendly assigned branch name for the staff user (used in the UI).
        // Assumption: if the staff user is assigned to multiple branches, show the first branch with a small +N hint.
        $assignedBranchName = 'All Branches';
        if (!empty($branchIds)) {
            $branchModel = new \App\Models\BranchModel();
            $branches = $branchModel->whereIn('id', $branchIds)->findAll();
            $names = array_values(array_filter(array_column($branches, 'name')));
            if (count($names) === 1) {
                $assignedBranchName = $names[0];
            } elseif (count($names) > 1) {
                $assignedBranchName = $names[0] . ' (+' . (count($names) - 1) . ')';
            }
        } else {
            $branches = [];
        }

        return view('staff/dashboard', [
            'user' => $user,
            'pendingAppointments' => $pendingAppointments,
            'todayAppointments' => $appointmentData['todayAppointments'],
            'totalPatients' => $userStats['total_patients'],
            'totalDentists' => $userStats['total_dentists'],
            'totalBranches' => $totalBranches,
            'recentPatients' => $recentPatients
            ,'assignedBranchName' => $assignedBranchName
            ,'assignedBranches' => $branches
        ]);
    }

    /**
     * AJAX API: return branch-scoped totals for current staff user
     */
    public function totals()
    {
        $user = $this->getAuthenticatedUserApi();
        if (!is_array($user)) {
            return $user; // auth helper returns JSON response on failure
        }

        if ($user['user_type'] !== 'staff') {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        // Get branches assigned to this staff user
        $branchUserModel = new \App\Models\BranchUserModel();
        $userBranches = $branchUserModel->getUserBranches($user['id']);
        $branchIds = array_map(function($b) { return $b['branch_id']; }, $userBranches ?: []);

        $dashboardService = new \App\Services\DashboardService();
        $totals = $dashboardService->getBranchTotals($branchIds);

        return $this->response->setJSON(['success' => true, 'totals' => $totals, 'fetched_at' => date('c')]);
    }

    /**
     * Optional richer timeseries endpoint for staff dashboards.
     * Returns labels, counts, patientCounts, statusCounts and nextAppointment similar to Dentist::stats()
     */
    public function stats()
    {
        $user = $this->getAuthenticatedUserApi();
        if (!is_array($user)) return $user;
        if ($user['user_type'] !== 'staff') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        $scope = $this->request->getGet('scope') ?? 'mine';

        // branches assigned to this staff user
        $branchUserModel = new \App\Models\BranchUserModel();
        $userBranches = $branchUserModel->getUserBranches($user['id']);
        $branchIds = array_map(function($b){ return (int) $b['branch_id']; }, $userBranches ?: []);

        $db = \Config\Database::connect();
        $labels = [];
        $counts = [];
        $patientCounts = [];
    $treatmentCounts = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $labels[] = date('D M j', strtotime($d));

            // appointments per day (branch-scoped)
            $qb = $db->table('appointments');
            $qb->where('DATE(appointment_datetime)', $d)
               ->whereIn('status', ['confirmed','scheduled','ongoing','checked_in']);
            if (!empty($branchIds)) $qb->whereIn('branch_id', $branchIds);
            $counts[] = (int) $qb->countAllResults();

            // distinct patients per day
            $pb = $db->table('appointments');
            $pb->select('COUNT(DISTINCT user_id) as cnt')
               ->where('DATE(appointment_datetime)', $d)
               ->whereIn('status', ['confirmed','scheduled','ongoing','checked_in']);
            if (!empty($branchIds)) $pb->whereIn('branch_id', $branchIds);
            $row = $pb->get()->getRowArray();
            $patientCounts[] = (int) ($row['cnt'] ?? 0);

            // treatments per day (count treatment_sessions where appointment date matches)
            $tb = $db->table('treatment_sessions ts')
                     ->select('COUNT(ts.id) as cnt')
                     ->join('appointments a', 'a.id = ts.appointment_id', 'left')
                     ->where('DATE(a.appointment_datetime)', $d);
            if (!empty($branchIds)) $tb->whereIn('a.branch_id', $branchIds);
            $trow = $tb->get()->getRowArray();
            $treatmentCounts[] = (int) ($trow['cnt'] ?? 0);
        }

        // statusCounts for last 7 days
        $statusList = ['confirmed','scheduled','ongoing','completed','no_show','cancelled'];
        $statusCounts = [];
        foreach ($statusList as $s) {
            $sb = $db->table('appointments');
            $sb->where('status', $s)
               ->where('DATE(appointment_datetime) >=', date('Y-m-d', strtotime('-6 days')))
               ->where('DATE(appointment_datetime) <=', date('Y-m-d'));
            if (!empty($branchIds)) $sb->whereIn('branch_id', $branchIds);
            $statusCounts[$s] = (int) $sb->countAllResults();
        }

        // next upcoming appointment for these branches
        $appointmentModel = new \App\Models\AppointmentModel();
        $nextQ = $appointmentModel->select('appointments.*, user.name as patient_name')
                                 ->join('user', 'user.id = appointments.user_id')
                                 ->where('DATE(appointment_datetime) >=', date('Y-m-d'))
                                 ->whereIn('appointments.status', ['confirmed','scheduled'])
                                 ->orderBy('appointments.appointment_datetime', 'ASC');
        if (!empty($branchIds)) $nextQ->whereIn('appointments.branch_id', $branchIds);
        $next = $nextQ->first();

        $nextAppointment = null;
        if ($next) {
            $nextAppointment = [ 'id' => $next['id'], 'patient_name' => $next['patient_name'] ?? null, 'datetime' => $next['appointment_datetime'] ?? null ];
        }

        // total distinct patients in last 7 days for branches
        $tb = $db->table('appointments');
        $tb->select('COUNT(DISTINCT appointments.user_id) as total')
           ->where('DATE(appointment_datetime) >=', date('Y-m-d', strtotime('-6 days')))
           ->where('DATE(appointment_datetime) <=', date('Y-m-d'));
        if (!empty($branchIds)) $tb->whereIn('appointments.branch_id', $branchIds);
        $totalRow = $tb->get()->getRowArray();
        $patientTotal = (int) ($totalRow['total'] ?? 0);

        return $this->response->setJSON([
            'labels' => $labels,
            'counts' => $counts,
            'treatmentCounts' => $treatmentCounts,
            'patientCounts' => $patientCounts,
            'statusCounts' => $statusCounts,
            'nextAppointment' => $nextAppointment,
            'patientTotal' => $patientTotal
        ]);
    }

    /**
     * Staff approves a pending appointment (from waitlist)
     */
    public function approveAppointment($id)
    {
        $user = $this->getAuthenticatedUserApi();
        if (!is_array($user)) {
            // Authentication failed, return the response (JSON error)
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
        if (!is_array($user)) {
            // Authentication failed, return the response (JSON error)
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
     * Staff waitlist (pending approvals) - branch-scoped
     */
    public function waitlist()
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        // Get pending appointments, limited to branches the staff is assigned to
        $appointmentModel = new \App\Models\AppointmentModel();
        $pendingAppointments = $appointmentModel->getPendingApprovalAppointments();

        $branchUserModel = new \App\Models\BranchUserModel();
        $userBranches = $branchUserModel->getUserBranches($user['id']);
        $branchIds = array_map(function($b) { return $b['branch_id']; }, $userBranches ?: []);
        if (!empty($branchIds)) {
            $pendingAppointments = array_values(array_filter($pendingAppointments, function($apt) use ($branchIds) {
                return in_array($apt['branch_id'] ?? null, $branchIds);
            }));
        } else {
            $pendingAppointments = [];
        }

        $formData = $this->dashboardService->getFormData();

        return view('admin/appointments/waitlist', array_merge([
            'user' => $user,
            'pendingAppointments' => $pendingAppointments,
            'isStaff' => true
        ], $formData));
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
