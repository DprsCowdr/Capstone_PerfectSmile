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
    // Allow admin or staff to call the staff API endpoints (so admins can preview branch dashboards)
    return AuthService::checkAdminOrStaffAuthApi();
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
            // If an admin has selected a branch (session('selected_branch_id')), prefer that
            // as the default shown to staff — but only if the staff user is assigned to that branch.
            $selectedBranchId = session('selected_branch_id') ?? null;
            if (!empty($selectedBranchId) && !empty($branchIds) && in_array((int)$selectedBranchId, $branchIds, true)) {
                // Try to set a friendlier name if possible and place the selected branch first in the list
                $bModel = new \App\Models\BranchModel();
                $sel = $bModel->find((int)$selectedBranchId);
                if ($sel && !empty($sel['name'])) {
                    $assignedBranchName = $sel['name'];
                }
                // reorder branches so selected branch appears first (UI convenience)
                usort($branches, function($a, $b) use ($selectedBranchId) {
                    if ((int)$a['id'] === (int)$selectedBranchId) return -1;
                    if ((int)$b['id'] === (int)$selectedBranchId) return 1;
                    return 0;
                });
                // ensure selectedBranchId is an int for the view
                $selectedBranchId = (int)$selectedBranchId;
            } else {
                $selectedBranchId = null;
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
                ,'selectedBranchId' => $selectedBranchId
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
            log_message('warning', 'StaffController::totals forbidden - user type: ' . ($user['user_type'] ?? 'unknown') . ' user_id: ' . ($user['id'] ?? 'unknown'));
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        // Get branches assigned to this staff user
        $branchUserModel = new \App\Models\BranchUserModel();
        $userBranches = $branchUserModel->getUserBranches($user['id']);
        $branchIds = array_map(function($b) { return (int)$b['branch_id']; }, $userBranches ?: []);

        // Prefer admin-selected branch (if present in session) when the staff user is assigned to it
        $selectedBranchId = session('selected_branch_id') ?? null;
        if (!empty($selectedBranchId) && in_array((int)$selectedBranchId, $branchIds, true)) {
            $branchIds = [(int)$selectedBranchId];
        }

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
            // guard against undefined $requestedBranch by deferring logging until after we resolve request params
            $rb = $this->request->getGet('branch_id');
            log_message('warning', 'StaffController::stats forbidden - user type: ' . ($user['user_type'] ?? 'unknown') . ' user_id: ' . ($user['id'] ?? 'unknown') . ' requested_branch: ' . ($rb ?? 'none'));
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        $scope = $this->request->getGet('scope') ?? 'mine';

        // branches assigned to this staff user
        $branchUserModel = new \App\Models\BranchUserModel();
        $userBranches = $branchUserModel->getUserBranches($user['id']);
        $branchIds = array_map(function($b){ return (int) $b['branch_id']; }, $userBranches ?: []);

        // Allow an explicit ?branch_id= override (admin or link). Otherwise prefer admin-selected branch in session
        $requestedBranch = $this->request->getGet('branch_id');
        $selectedBranchId = session('selected_branch_id') ?? null;
        if (!empty($requestedBranch)) {
            $branchIds = [(int)$requestedBranch];
        } elseif (!empty($selectedBranchId) && in_array((int)$selectedBranchId, $branchIds, true)) {
            $branchIds = [(int)$selectedBranchId];
        }

        $db = \Config\Database::connect();
        $labels = [];
        $counts = [];
        $patientCounts = [];
    $treatmentCounts = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $labels[] = date('D M j', strtotime($d));

            // appointments per day (branch-scoped)
            // Count all appointments for the day (do not restrict by status) so totals match DashboardService
            $qb = $db->table('appointments');
            $qb->where('DATE(appointment_datetime)', $d);
            if (!empty($branchIds)) $qb->whereIn('branch_id', $branchIds);
            $counts[] = (int) $qb->countAllResults();

                // distinct patients per day (count unique users with appointments on the day)
                // Do not restrict by status so this mirrors total patient counts in DashboardService
                $pb = $db->table('appointments');
                $pb->select('COUNT(DISTINCT user_id) as cnt')
                    ->where('DATE(appointment_datetime)', $d);
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
    // Treat appointments as missed if they are more than $graceMinutes past their scheduled time.
    // Use DB-side NOW() subtraction to avoid PHP/DB timezone mismatches.
    $dashboardConfig = new \App\Config\Dashboard();
    $graceMinutes = (int) ($dashboardConfig->nextAppointmentGraceMinutes ?? 15);
    $missedThresholdPhp = date('Y-m-d H:i:s', strtotime("-{$graceMinutes} minutes"));
    $missedExpr = "DATE_SUB(NOW(), INTERVAL {$graceMinutes} MINUTE)";

    $appointmentModel = new \App\Models\AppointmentModel();
    $nextQ = $appointmentModel->select('appointments.*, user.name as patient_name')
                 ->join('user', 'user.id = appointments.user_id')
                 // include appointments scheduled from (DB now - grace) onwards (DB-side expression)
                 ->where("appointments.appointment_datetime >= {$missedExpr}", null, false)
                 ->whereIn('appointments.status', ['confirmed','scheduled'])
                 ->orderBy('appointments.appointment_datetime', 'ASC');
    if (!empty($branchIds)) $nextQ->whereIn('appointments.branch_id', $branchIds);
    // Apply an end-of-day cutoff (clinic last appointment time). Use 16:00 today as the business day's latest slot.
    $endOfDay = date('Y-m-d') . ' 16:00:00';
    $nextQ->where('appointments.appointment_datetime <=', $endOfDay);
    $next = $nextQ->first();

        $nextAppointment = null;
        if ($next) {
            $nextAppointment = [ 'id' => $next['id'], 'patient_name' => $next['patient_name'] ?? null, 'datetime' => $next['appointment_datetime'] ?? null ];
        }

        // Optional debugging info (call /staff/stats?debug_next=1 to include)
        $debugNext = false;
        if ($this->request->getGet('debug_next')) {
            $debugNext = true;
            $dbg = 'php:' . ($missedThresholdPhp ?? '') . ' dbExpr:' . ($missedExpr ?? '') . ' endOfDay:' . ($endOfDay ?? '') ;
            log_message('debug', 'StaffController::stats next debug - threshold: ' . $dbg . ' branchIds: ' . json_encode($branchIds) . ' selected: ' . json_encode($next));
        }

          // total distinct patients in last 7 days for branches (existing behaviour)
          $tb = $db->table('appointments');
          $tb->select('COUNT(DISTINCT appointments.user_id) as total')
              ->where('DATE(appointment_datetime) >=', date('Y-m-d', strtotime('-6 days')))
              ->where('DATE(appointment_datetime) <=', date('Y-m-d'));
          if (!empty($branchIds)) $tb->whereIn('appointments.branch_id', $branchIds);
          $totalRow = $tb->get()->getRowArray();
          $patientTotal = (int) ($totalRow['total'] ?? 0);

          // total distinct patients (ALL-TIME) for these branches — used to compare against DashboardService totals
          $ta = $db->table('appointments');
          $ta->select('COUNT(DISTINCT appointments.user_id) as total_all');
          if (!empty($branchIds)) $ta->whereIn('appointments.branch_id', $branchIds);
          $totalAllRow = $ta->get()->getRowArray();
          $patientTotalAll = (int) ($totalAllRow['total_all'] ?? 0);

        $payload = [
            'labels' => $labels,
            'counts' => $counts,
            'treatmentCounts' => $treatmentCounts,
            'patientCounts' => $patientCounts,
            'statusCounts' => $statusCounts,
            'nextAppointment' => $nextAppointment,
            'patientTotal' => $patientTotal
        ];

        // Add a tiny sanity check comparing DashboardService totals with the timeseries/aggregates
        try {
            $serviceTotals = $this->dashboardService->getBranchTotals($branchIds);
            $lastCounts = [
                'appointments_last' => (int) (count($counts) ? $counts[count($counts)-1] : 0),
                'patients_last' => (int) (count($patientCounts) ? $patientCounts[count($patientCounts)-1] : 0),
                'treatments_last' => (int) (count($treatmentCounts) ? $treatmentCounts[count($treatmentCounts)-1] : 0),
            ];
            $payload['sanity'] = [
                'serviceTotals' => $serviceTotals,
                'timeseriesLast' => $lastCounts,
                // Compare apples-to-apples: serviceTotals are all-time branch-scoped counts, so compare to patientTotalAll
                'patientTotalAll' => $patientTotalAll,
                'patientTotal_matches_service' => ((int)$patientTotalAll === (int)($serviceTotals['total_patients'] ?? 0))
            ];
        } catch (\Exception $e) {
            // ignore sanity on failure
        }

        if ($debugNext) {
            $payload['_debug_next'] = [ 'missedThresholdPhp' => $missedThresholdPhp, 'missedExpr' => $missedExpr, 'endOfDay' => $endOfDay, 'selectedRaw' => $next, 'branchIds' => $branchIds ];
        }

        // expose both last-7 and all-time patient totals in the payload
        $payload['patientTotalAll'] = $patientTotalAll ?? 0;

        return $this->response->setJSON($payload);
    }

    /**
     * Diagnostics endpoint: return DB-side NOW(), missed threshold (DATE_SUB), end_of_day,
     * several counts and a sample next appointment row so we can compare environments.
     * Accessible to authenticated staff via GET. Optional params: branch_id (single), grace (minutes override).
     */
    public function dbDiagnostics()
    {
        $user = $this->getAuthenticatedUserApi();
        if (!is_array($user)) return $user;
        if ($user['user_type'] !== 'staff') {
            $rb = $this->request->getGet('branch_id');
            log_message('warning', 'StaffController::dbDiagnostics forbidden - user type: ' . ($user['user_type'] ?? 'unknown') . ' user_id: ' . ($user['id'] ?? 'unknown') . ' requested_branch: ' . ($rb ?? 'none'));
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        // Resolve branch scope: explicit ?branch_id=, or admin-selected session, or staff-assigned branches
        $requestedBranch = $this->request->getGet('branch_id');
        $branchUserModel = new \App\Models\BranchUserModel();
        $userBranches = $branchUserModel->getUserBranches($user['id']);
        $userBranchIds = array_map(function($b){ return (int) $b['branch_id']; }, $userBranches ?: []);
        $branchIds = [];
        $selectedBranchId = session('selected_branch_id') ?? null;
        if (!empty($requestedBranch)) {
            $branchIds = [(int) $requestedBranch];
        } elseif (!empty($selectedBranchId) && in_array((int)$selectedBranchId, $userBranchIds, true)) {
            // If admin selected a branch and the staff is assigned to it, prefer that
            $branchIds = [(int) $selectedBranchId];
        } elseif (!empty($userBranchIds)) {
            $branchIds = $userBranchIds;
        }

        $dashboardConfig = new \App\Config\Dashboard();
        $graceMinutes = (int) ($this->request->getGet('grace') ?? $dashboardConfig->nextAppointmentGraceMinutes ?? 15);

        $db = \Config\Database::connect();

        // Fetch DB-side now / missed threshold / end_of_day
        $nowRow = $db->query('SELECT NOW() AS now, DATE_SUB(NOW(), INTERVAL ? MINUTE) AS missed, CONCAT(CURDATE(), " 16:00:00") AS end_of_day', [$graceMinutes])->getRowArray();

        // Build optional branch clause/binds
        $branchClause = '';
        $binds = [];
        if (!empty($branchIds)) {
            $placeholders = implode(',', array_fill(0, count($branchIds), '?'));
            $branchClause = ' AND branch_id IN (' . $placeholders . ')';
            foreach ($branchIds as $b) $binds[] = $b;
        }

        // Appointment counts after missed threshold (candidate next appointments)
        $sqlAfterMissed = "SELECT COUNT(*) AS cnt FROM appointments WHERE appointment_datetime >= DATE_SUB(NOW(), INTERVAL ? MINUTE) AND appointment_datetime <= CONCAT(CURDATE(),' 16:00:00') AND status IN ('confirmed','scheduled')" . $branchClause;
        $paramsAfter = array_merge([$graceMinutes], $binds);
        $afterRow = $db->query($sqlAfterMissed, $paramsAfter)->getRowArray();

        // Total appointments in last 7 days
        $sqlLast7 = "SELECT COUNT(*) AS cnt FROM appointments WHERE DATE(appointment_datetime) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND DATE(appointment_datetime) <= CURDATE()" . $branchClause;
        $last7Row = $db->query($sqlLast7, $binds)->getRowArray();

        // Distinct patients in last 7 days
        $sqlPatients7 = "SELECT COUNT(DISTINCT user_id) AS cnt FROM appointments WHERE DATE(appointment_datetime) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND DATE(appointment_datetime) <= CURDATE()" . $branchClause;
        $patients7Row = $db->query($sqlPatients7, $binds)->getRowArray();

        // Treatments in last 7 days
        $sqlTreat7 = "SELECT COUNT(ts.id) AS cnt FROM treatment_sessions ts LEFT JOIN appointments a ON a.id = ts.appointment_id WHERE DATE(a.appointment_datetime) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND DATE(a.appointment_datetime) <= CURDATE()" . $branchClause;
        $treat7Row = $db->query($sqlTreat7, $binds)->getRowArray();

        // Sample the actual next appointment row using same DB-side expression as stats()
        $sqlNext = "SELECT appointments.*, user.name AS patient_name FROM appointments LEFT JOIN user ON user.id = appointments.user_id WHERE appointments.appointment_datetime >= DATE_SUB(NOW(), INTERVAL ? MINUTE) AND appointments.status IN ('confirmed','scheduled')" . $branchClause . " ORDER BY appointments.appointment_datetime ASC LIMIT 1";
        $paramsNext = array_merge([$graceMinutes], $binds);
        $nextRow = $db->query($sqlNext, $paramsNext)->getRowArray();

        $payload = [
            'now' => $nowRow['now'] ?? null,
            'missed_threshold_db' => $nowRow['missed'] ?? null,
            'end_of_day' => $nowRow['end_of_day'] ?? null,
            'grace_minutes' => $graceMinutes,
            'branchIds' => $branchIds,
            'appointments_after_missed' => (int) ($afterRow['cnt'] ?? 0),
            'appointments_last7' => (int) ($last7Row['cnt'] ?? 0),
            'distinct_patients_last7' => (int) ($patients7Row['cnt'] ?? 0),
            'treatments_last7' => (int) ($treat7Row['cnt'] ?? 0),
            'sample_next' => $nextRow ?: null,
            'queries' => [
                'after_missed' => $sqlAfterMissed,
                'last7' => $sqlLast7,
                'patients7' => $sqlPatients7,
                'treat7' => $sqlTreat7,
                'next' => $sqlNext
            ]
        ];

        return $this->response->setJSON($payload);
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
