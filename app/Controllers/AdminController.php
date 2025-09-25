<?php

namespace App\Controllers;

use App\Traits\AdminAuthTrait;
use App\Services\DashboardService;

class AdminController extends BaseAdminController
{
    use AdminAuthTrait;
    
    protected $dashboardService;
    
    public function __construct()
    {
        parent::__construct();
        $this->dashboardService = new DashboardService();
    }

    // ==================== AUTHENTICATION ====================
    protected function getAuthenticatedUser()
    {
        return $this->checkAdminAuth();
    }
    
    protected function getAuthenticatedUserApi()
    {
        return $this->checkAdminAuthApi();
    }

    // ==================== DASHBOARD ====================
    public function dashboard()
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $appointmentData = $this->appointmentService->getDashboardData();
        $statistics = $this->dashboardService->getStatistics();
        $selectedBranchId = session('selected_branch_id');
        // If admin has selected a branch, render a dedicated branch dashboard view
        if (!empty($selectedBranchId)) {
            // Pass appointmentData + statistics into the branch dashboard view so it mirrors staff dashboard
            $branchModel = new \App\Models\BranchModel();
            $branch = $branchModel->find($selectedBranchId);
            // Fetch unread branch notifications for this branch
            $bnModel = new \App\Models\BranchNotificationModel();
            $branchNotifications = $bnModel->where('branch_id', $selectedBranchId)
                                           ->where('sent', 0)
                                           ->orderBy('created_at', 'DESC')
                                           ->findAll();

            // attach to view data
            $viewData = array_merge([
                'user' => $user,
                'selectedBranchId' => $selectedBranchId,
                'branch' => $branch
            ], ['branchNotifications' => $branchNotifications], $appointmentData, $statistics);
            return view('admin/branch_dashboard', $viewData);
        }

        return view('admin/dashboard', array_merge([
            'user' => $user,
            'selectedBranchId' => $selectedBranchId
        ], $appointmentData, $statistics));
    }

    // ==================== PATIENT MANAGEMENT ====================
    public function patients()
    {
        return $this->getPatientsView('admin/patients');
    }

    public function addPatient()
    {
        return $this->getAddPatientView('admin/patients/add');
    }

    public function storePatient()
    {
        return $this->storePatientLogic('/admin/patients');
    }

    public function getPatient($id)
    {
        return $this->getPatientApi($id);
    }

    public function updatePatient($id)
    {
        return $this->updatePatientLogic($id, '/admin/patients');
    }

    public function toggleStatus($id)
    {
        return $this->togglePatientStatusLogic($id, '/admin/patients');
    }

    // ==================== PATIENT ACCOUNT ACTIVATION ====================
    public function patientActivation()
    {
        return $this->getPatientActivationView('admin/patients/activation');
    }

    public function activatePatientAccount($id)
    {
        return $this->activatePatientAccountLogic($id, '/admin/patients/activation');
    }

    public function deactivatePatientAccount($id)
    {
        return $this->deactivatePatientAccountLogic($id, '/admin/patients/activation');
    }

    public function createAccount($id)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        
        $patient = $this->userService->getPatient($id);
        if (!$patient) {
            return redirect()->to('/admin/patients')->with('error', 'Patient not found.');
        }
        
        return view('admin/patients/create', ['user' => $user, 'patient' => $patient]);
    }

    public function saveAccount($id)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        
        if (!$this->userService->getPatient($id)) {
            return redirect()->to('/admin/patients')->with('error', 'Patient not found.');
        }
        
        $password = $this->request->getPost('password');
        
        if ($this->userService->createPatientAccount($id, $password)) {
            return redirect()->to('/admin/patients')->with('success', 'Account created for patient.');
        }
        
        return redirect()->back()->with('error', 'Password must be at least 6 characters.');
    }

    public function getPatientAppointments($patientId)
    {
        $user = $this->getAuthenticatedUserApi();
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }

        $result = $this->appointmentService->getPatientAppointments($patientId);
        return $this->response->setJSON($result);
    }

    // ==================== INVOICES (placeholder) ====================
    public function invoices()
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        return view('admin/invoices/index', ['user' => $user]);
    }

    /**
     * Admin-only preview endpoint to fetch branch-scoped totals + next appointment.
     * Query: /admin/preview-branch-stats?branch_id=2
     */
    public function previewBranchStats()
    {
        $user = $this->getAuthenticatedUserApi();
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) return $user;

        // only admins allowed
        if ($user['user_type'] !== 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        $branchId = $this->request->getGet('branch_id');
        if (!$branchId) return $this->response->setStatusCode(400)->setJSON(['error' => 'branch_id required']);

        $dashboardService = new DashboardService();
        $totals = $dashboardService->getBranchTotals([(int)$branchId]);

        // next appointment for branch (reuse logic similar to StaffController)
        $appointmentModel = new \App\Models\AppointmentModel();
        $dashboardConfig = new \App\Config\Dashboard();
        $grace = (int) ($dashboardConfig->nextAppointmentGraceMinutes ?? 15);
        $missedExpr = "DATE_SUB(NOW(), INTERVAL {$grace} MINUTE)";
        $endOfDay = date('Y-m-d') . ' 16:00:00';

        $nextQ = $appointmentModel->select('appointments.*, user.name as patient_name')
                    ->join('user', 'user.id = appointments.user_id')
                    ->where("appointments.appointment_datetime >= {$missedExpr}", null, false)
                    ->whereIn('appointments.status', ['confirmed','scheduled'])
                    ->where('appointments.branch_id', (int)$branchId)
                    ->where('appointments.appointment_datetime <=', $endOfDay)
                    ->orderBy('appointments.appointment_datetime', 'ASC');
        $next = $nextQ->first();

        $nextAppointment = null;
        if ($next) $nextAppointment = ['id' => $next['id'], 'patient_name' => $next['patient_name'] ?? null, 'datetime' => $next['appointment_datetime'] ?? null];

        // Build 7-day timeseries to match StaffController::stats()
    $db = \Config\Database::connect();
        $labels = [];
        $counts = [];
        $patientCounts = [];
        $treatmentCounts = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $labels[] = date('D M j', strtotime($d));

            // appointments per day
            $qb = $db->table('appointments');
            $qb->where('DATE(appointment_datetime)', $d);
            $qb->where('branch_id', (int)$branchId);
            $counts[] = (int) $qb->countAllResults();

            // distinct patients
            $pb = $db->table('appointments');
            $pb->select('COUNT(DISTINCT user_id) as cnt')->where('DATE(appointment_datetime)', $d)->where('branch_id', (int)$branchId);
            $row = $pb->get()->getRowArray();
            $patientCounts[] = (int) ($row['cnt'] ?? 0);

            // treatments per day
            $tb = $db->table('treatment_sessions ts')
                     ->select('COUNT(ts.id) as cnt')
                     ->join('appointments a', 'a.id = ts.appointment_id', 'left')
                     ->where('DATE(a.appointment_datetime)', $d)
                     ->where('a.branch_id', (int)$branchId);
            $trow = $tb->get()->getRowArray();
            $treatmentCounts[] = (int) ($trow['cnt'] ?? 0);

            // revenue per day (invoices created) and payments received per day
            try {
                $ib = $db->table('invoices')
                         ->select('COALESCE(SUM(total_amount),0) as total')
                         ->where('DATE(created_at)', $d)
                         ->where('branch_id', (int)$branchId);
                $irow = $ib->get()->getRowArray();
                $revenues[] = (float) ($irow['total'] ?? 0);
            } catch (\Exception $e) {
                $revenues[] = 0.0;
            }
            try {
                $pb = $db->table('payments')
                         ->select('COALESCE(SUM(amount),0) as total')
                         ->where('DATE(created_at)', $d)
                         ->where('branch_id', (int)$branchId);
                $prow = $pb->get()->getRowArray();
                $paymentTotals[] = (float) ($prow['total'] ?? 0);
            } catch (\Exception $e) {
                $paymentTotals[] = 0.0;
            }
        }

        // status counts for last 7 days
        $statusList = ['confirmed','scheduled','ongoing','completed','no_show','cancelled'];
        $statusCounts = [];
        foreach ($statusList as $s) {
            $sb = $db->table('appointments');
            $sb->where('status', $s)
               ->where('DATE(appointment_datetime) >=', date('Y-m-d', strtotime('-6 days')))
               ->where('DATE(appointment_datetime) <=', date('Y-m-d'))
               ->where('branch_id', (int)$branchId);
            $statusCounts[$s] = (int) $sb->countAllResults();
        }

        // estimate avgPerDay and peakDay
        $avgPerDay = (count($counts) ? (array_sum($counts) / count($counts)) : 0);
        $peakDay = count($counts) ? $labels[array_search(max($counts), $counts)] : null;

        // compute revenue totals for the period
        $totalRevenue = array_sum($revenues ?? []);
        $totalPayments = array_sum($paymentTotals ?? []);

        $mergedTotals = array_merge((array)$totals, [
            'total_revenue' => $totalRevenue,
            'total_payments' => $totalPayments
        ]);

        return $this->response->setJSON([
            'success' => true,
            'totals' => $mergedTotals,
            'nextAppointment' => $nextAppointment,
            'labels' => $labels,
            'counts' => $counts,
            'patientCounts' => $patientCounts,
            'treatmentCounts' => $treatmentCounts,
            'revenueTotals' => $revenues ?? [],
            'paymentTotals' => $paymentTotals ?? [],
            'statusCounts' => $statusCounts,
            'avgPerDay' => round($avgPerDay, 1),
            'peakDay' => $peakDay
        ]);
    }

    // ==================== APPOINTMENT MANAGEMENT ====================
    public function appointments()
    {
        return $this->getAppointmentsView('admin/appointments/index');
    }

    public function createAppointment()
    {
        return $this->createAppointmentLogic('/admin/appointments', 'admin');
    }

    // Keep existing appointment approval/decline methods
    public function approveAppointment($id)
    {
        try {
            // Check authentication first
            $user = $this->getAuthenticatedUser();
            if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Authentication failed']);
                }
                return $user;
            }

            $dentistId = $this->request->getPost('dentist_id');
            // Optional: allow client to request auto-reschedule if conflicts detected
            $autoReschedule = $this->request->getPost('auto_reschedule') ? true : false;
            $chosenTime = $this->request->getPost('chosen_time') ?: null;
            $chosenDate = $this->request->getPost('chosen_date') ?: null;
            $chosenTimestamp = $this->request->getPost('chosen_timestamp') ?: null;
            $chosenDatetime = $this->request->getPost('chosen_datetime') ?: null;
            log_message('info', "Admin approving appointment ID: {$id}, Dentist ID: " . ($dentistId ?: 'null'));
            
            // Get appointment details before approval for logging
            $appointmentModel = new \App\Models\AppointmentModel();
            $appointment = $appointmentModel->find($id);
            if ($appointment) {
                log_message('info', "Appointment before approval: " . json_encode($appointment));
            }
            
            $options = ['auto_reschedule' => $autoReschedule, 'chosen_time' => $chosenTime];
            if ($chosenDate) $options['chosen_date'] = $chosenDate;
            if ($chosenTimestamp) $options['chosen_timestamp'] = $chosenTimestamp;
            if ($chosenDatetime) $options['chosen_datetime'] = $chosenDatetime;
            $result = $this->appointmentService->approveAppointment($id, $dentistId, $options);
            
            log_message('info', "Appointment approval result: " . json_encode($result));
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON($result);
            }
            
            session()->setFlashdata($result['success'] ? 'success' : 'error', $result['message']);
            return redirect()->back();
        } catch (\Exception $e) {
            log_message('error', "Exception in approveAppointment: " . $e->getMessage());
            log_message('error', "Stack trace: " . $e->getTraceAsString());
            
            $errorResult = ['success' => false, 'message' => 'An error occurred while approving the appointment: ' . $e->getMessage()];
            
            // Always return JSON for AJAX requests, even on exceptions
            if ($this->request->isAJAX()) {
                return $this->response->setJSON($errorResult);
            }
            
            session()->setFlashdata('error', $errorResult['message']);
            return redirect()->back();
        }
    }

    public function declineAppointment($id)
    {
        try {
            // Check authentication first
            $user = $this->getAuthenticatedUser();
            if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Authentication failed']);
                }
                return $user;
            }

            $reason = $this->request->getPost('reason');
            if (empty($reason)) {
                $errorResult = ['success' => false, 'message' => 'Decline reason is required'];
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON($errorResult);
                }
                session()->setFlashdata('error', $errorResult['message']);
                return redirect()->back();
            }

            log_message('info', "Admin declining appointment ID: {$id}, Reason: {$reason}");
            
            $result = $this->appointmentService->declineAppointment($id, $reason);
            
            log_message('info', "Appointment decline result: " . json_encode($result));
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON($result);
            }
            
            session()->setFlashdata($result['success'] ? 'success' : 'error', $result['message']);
            return redirect()->back();
        } catch (\Exception $e) {
            log_message('error', "Exception in declineAppointment: " . $e->getMessage());
            log_message('error', "Stack trace: " . $e->getTraceAsString());
            
            $errorResult = ['success' => false, 'message' => 'An error occurred while declining the appointment: ' . $e->getMessage()];
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON($errorResult);
            }
            
            session()->setFlashdata('error', $errorResult['message']);
            return redirect()->back();
        }
    }

    // ==================== MANAGEMENT VIEWS ====================
    
    public function services()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $serviceModel = new \App\Models\ServiceModel();
        $services = $serviceModel->findAll();

        return view('admin/management/services', [
            'user' => $user,
            'services' => $services
        ]);
    }

    public function storeService()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $serviceModel = new \App\Models\ServiceModel();
        
        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'price' => $this->request->getPost('price')
        ];

        // Parse duration inputs (accepts '1.5h', '90', '60m')
        $durationInput = $this->request->getPost('duration');
        $parsed = $this->parseDurationInput($durationInput);
        if ($parsed !== null) {
            $data['duration_minutes'] = $parsed;
        }

        $maxInput = $this->request->getPost('duration_max_minutes');
        $maxParsed = $this->parseDurationInput($maxInput);
        if ($maxParsed !== null) {
            $data['duration_max_minutes'] = $maxParsed;
        }

        if ($serviceModel->insert($data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Service created successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create service',
                'errors' => $serviceModel->errors()
            ]);
        }
    }

    public function getService($id)
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $serviceModel = new \App\Models\ServiceModel();
        $service = $serviceModel->find($id);

        if ($service) {
            return $this->response->setJSON([
                'success' => true,
                'service' => $service
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Service not found'
            ]);
        }
    }

    public function updateService($id)
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $serviceModel = new \App\Models\ServiceModel();
        
        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'price' => $this->request->getPost('price')
        ];

        // Parse duration inputs for update
        $durationInput = $this->request->getPost('duration');
        $parsed = $this->parseDurationInput($durationInput);
        if ($parsed !== null) {
            $data['duration_minutes'] = $parsed;
        }

        $maxInput = $this->request->getPost('duration_max_minutes');
        $maxParsed = $this->parseDurationInput($maxInput);
        if ($maxParsed !== null) {
            $data['duration_max_minutes'] = $maxParsed;
        }

        if ($serviceModel->update($id, $data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Service updated successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update service',
                'errors' => $serviceModel->errors()
            ]);
        }
    }

    public function deleteService($id)
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $serviceModel = new \App\Models\ServiceModel();
        
        // Check if service is being used in any appointments
        $appointmentServiceModel = new \App\Models\AppointmentServiceModel();
        $usageCount = $appointmentServiceModel->where('service_id', $id)->countAllResults();
        
        if ($usageCount > 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => "Cannot delete service. It is currently used in {$usageCount} appointment(s)."
            ]);
        }

        if ($serviceModel->delete($id)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Service deleted successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to delete service'
            ]);
        }
    }

    /**
     * Parse duration input that may contain hours (e.g., '2h') or plain minutes (e.g., '120')
     * Returns integer minutes or null for empty input.
     */
    protected function parseDurationInput($input)
    {
        if ($input === null) return null;
        $input = trim((string)$input);
        if ($input === '') return null;

        // Accept formats like '2h', '2.5h', '150', '120m'
        // Normalize
        $lower = strtolower($input);

        // If contains 'h' treat as hours
        if (strpos($lower, 'h') !== false) {
            // extract numeric part
            $num = floatval(str_replace('h', '', $lower));
            if ($num <= 0) return null;
            return (int) round($num * 60);
        }

        // If contains 'm' remove it
        if (strpos($lower, 'm') !== false) {
            $lower = str_replace('m', '', $lower);
        }

        // fallback to integer minutes
        $minutes = intval($lower);
        if ($minutes <= 0) return null;
        return $minutes;
    }

    /**
     * Format minutes to readable string, e.g., 150 -> '2h 30m' or '30m'
     */
    protected function formatMinutesReadable($minutes)
    {
        if ($minutes === null) return 'Not set';
        $m = intval($minutes);
        if ($m <= 0) return 'Not set';
        $hours = intdiv($m, 60);
        $rem = $m % 60;
        if ($hours > 0) {
            return $hours . 'h' . ($rem ? ' ' . $rem . 'm' : '');
        }
        return $rem . 'm';
    }

    public function procedures()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        return view('admin/management/procedures', ['user' => $user]);
    }

    public function records()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        // Load models
        $dentalRecordModel = new \App\Models\DentalRecordModel();
        $userModel = new \App\Models\UserModel();
        $branchModel = new \App\Models\BranchModel();
        
    // Get all dental records with complete patient and medical history information AND branch info
    // No limit so admin sees historical records from all branches
    $records = $dentalRecordModel->getRecordsWithPatientAndBranchInfo();

        // Get all branches for categorization
        $branches = $branchModel->where('status', 'active')->orderBy('name', 'ASC')->findAll();
        
        // Categorize records by branch
        $recordsByBranch = [];
        $unassignedRecords = [];
        
        foreach ($records as $record) {
            $branchId = $record['branch_id'] ?? null;
            if ($branchId) {
                if (!isset($recordsByBranch[$branchId])) {
                    $recordsByBranch[$branchId] = [
                        'branch' => null,
                        'records' => []
                    ];
                    // Find branch info
                    foreach ($branches as $branch) {
                        if ($branch['id'] == $branchId) {
                            $recordsByBranch[$branchId]['branch'] = $branch;
                            break;
                        }
                    }
                }
                $recordsByBranch[$branchId]['records'][] = $record;
            } else {
                $unassignedRecords[] = $record;
            }
        }

        // Calculate statistics
        $totalRecords = $dentalRecordModel->countAll();
        
        // Count active patients (patients with records in last 6 months)
    // Use distinct() + select() so DISTINCT is not quoted as a column name
    $activePatients = $dentalRecordModel->distinct()
                      ->select('user_id')
                      ->where('record_date >=', date('Y-m-d', strtotime('-6 months')))
                      ->countAllResults();
        
        // Count records with X-rays
        $withXrays = $dentalRecordModel->where('xray_image_url IS NOT NULL')
                                     ->where('xray_image_url !=', '')
                                     ->countAllResults();
        
        // Count pending follow-ups (records with next_appointment_date in future)
        $pendingFollowups = $dentalRecordModel->where('next_appointment_date >=', date('Y-m-d'))
                                            ->countAllResults();

        $data = [
            'user' => $user,
            'records' => $records, // Keep original for backward compatibility
            'recordsByBranch' => $recordsByBranch,
            'unassignedRecords' => $unassignedRecords,
            'branches' => $branches,
            'stats' => [
                'total_records' => $totalRecords,
                'active_patients' => $activePatients,
                'with_xrays' => $withXrays,
                'pending_followups' => $pendingFollowups
            ]
        ];

        return view('admin/dental/all_records', $data);
    }

    public function deleteRecord($recordId)
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        try {
            $dentalRecordModel = new \App\Models\DentalRecordModel();
            
            // Check if record exists
            $record = $dentalRecordModel->find($recordId);
            if (!$record) {
                return $this->response->setJSON(['success' => false, 'message' => 'Record not found']);
            }

            // Delete the record
            if ($dentalRecordModel->delete($recordId)) {
                return $this->response->setJSON(['success' => true, 'message' => 'Record deleted successfully']);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete record']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error deleting dental record: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'An error occurred while deleting the record']);
        }
    }

    public function waitlist()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        // Get pending appointments that need approval
        $appointmentModel = new \App\Models\AppointmentModel();
        $pendingAppointments = $appointmentModel->getPendingApprovalAppointments();

        // Get form data for creating appointments
        $formData = $this->dashboardService->getFormData();
        
        return view('admin/appointments/waitlist', array_merge([
            'user' => $user,
            'pendingAppointments' => $pendingAppointments
        ], $formData));
    }


    public function rolePermission()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
    // Redirect to RoleController index which prepares and renders the roles list
    return redirect()->to(site_url('admin/roles'));
    }

    public function branches()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        return view('admin/management/branches', ['user' => $user]);
    }

    public function settings()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        return view('admin/management/settings', ['user' => $user]);
    }

    // ==================== USERS MANAGEMENT ====================
    public function users()
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $userModel = new \App\Models\UserModel();
        $branchModel = new \App\Models\BranchModel();
        
        // Get all users except patients (they're managed separately)
        $users = $userModel->whereNotIn('user_type', ['patient'])->findAll();
        $branches = $branchModel->findAll();
        
        // Get branch assignments for each user with branch names
    $branchUserModel = new \App\Models\BranchStaffModel();
        $branchAssignments = [];
        foreach ($users as $userData) {
            $assignments = $branchUserModel->select('branch_staff.*, branches.name as branch_name')
                                          ->join('branches', 'branches.id = branch_staff.branch_id')
                                          ->where('branch_staff.user_id', $userData['id'])
                                          ->findAll();
            $branchAssignments[$userData['id']] = $assignments;
        }
        
        $selectedBranchId = session('selected_branch_id');
        
        return view('admin/users/index', [
            'user' => $user,
            'users' => $users,
            'branches' => $branches,
            'branchAssignments' => $branchAssignments,
            'selectedBranchId' => $selectedBranchId
        ]);
    }

    public function addUser()
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $branchModel = new \App\Models\BranchModel();
        $branches = $branchModel->findAll();
        
        return view('admin/users/add', [
            'user' => $user,
            'branches' => $branches
            //done
        ]);
    }

    public function storeUser()
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $formData = $this->request->getPost();
        
        // Validate user data
        $validation = \Config\Services::validation();
        $validation->setRules([
            'name' => 'required|min_length[2]',
            'email' => 'required|valid_email|is_unique[user.email,id,{id}]',
            'phone' => 'required|min_length[10]',
            'gender' => 'permit_empty|in_list[Male,Female,Other,male,female,other]',
            'user_type' => 'required|in_list[admin,staff,dentist]',
            'password' => 'required|min_length[6]',
            'branches' => 'required'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('error', $validation->getErrors());
        }

        $userModel = new \App\Models\UserModel();
    $branchUserModel = new \App\Models\BranchStaffModel();

        // Create user
        $userData = [
            'name' => $formData['name'],
            'email' => $formData['email'],
            'phone' => $formData['phone'],
            'user_type' => $formData['user_type'],
            'password' => password_hash($formData['password'], PASSWORD_DEFAULT),
            'gender' => $formData['gender'] ?? null,
            'status' => 'active',
            'occupation' => $formData['occupation'] ?? null,
            'nationality' => $formData['nationality'] ?? null,
            'date_of_birth' => !empty($formData['date_of_birth']) ? $formData['date_of_birth'] : null,
            'age' => !empty($formData['age']) ? (int)$formData['age'] : null
        ];

        try {
            $userId = $userModel->insert($userData, true); // second param TRUE returns inserted ID or throws
        } catch (\Throwable $e) {
            log_message('error', 'storeUser DB exception: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Database exception: ' . $e->getMessage());
        }

        if ($userId) {
            $branches = $formData['branches'] ?? [];
            $position = $formData['position'] ?? '';

            if (empty($position)) {
                switch ($formData['user_type']) {
                    case 'dentist':
                        $position = 'Dentist';
                        break;
                    case 'admin':
                        $position = 'Administrator';
                        break;
                    default:
                        $position = 'Staff';
                        break;
                }
            }

            // Assign branches with error capture
            foreach ($branches as $branchId) {
                if (!$branchUserModel->insert([
                    'user_id' => $userId,
                    'branch_id' => $branchId,
                    'position' => $position
                ])) {
                    log_message('error', 'Branch assignment failed for user ' . $userId . ' branch ' . $branchId . ' errors: ' . json_encode($branchUserModel->errors()));
                }
            }

            return redirect()->to('/admin/users')->with('success', 'User created successfully.');
        }

        // Gather model errors & DB error
        $modelErrors = $userModel->errors();
        $dbError = $userModel->db()->error();
        $errorParts = [];
        if (!empty($modelErrors)) {
            $errorParts[] = 'Validation: ' . implode('; ', array_filter($modelErrors));
        }
        if (!empty($dbError['code'])) {
            $errorParts[] = 'DB[' . $dbError['code'] . ']: ' . $dbError['message'];
        }
        if (empty($errorParts)) {
            $errorParts[] = 'Unknown insert failure.';
        }
        $detail = implode(' | ', $errorParts);
        log_message('error', 'User insert failed: ' . $detail . ' Data: ' . json_encode($userData));
        return redirect()->back()->withInput()->with('error', 'Failed to create user. ' . $detail);
    }

    public function editUser($id)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $userModel = new \App\Models\UserModel();
        $branchModel = new \App\Models\BranchModel();
    $branchUserModel = new \App\Models\BranchStaffModel();

        $userData = $userModel->find($id);
        if (!$userData) {
            return redirect()->to('/admin/users')->with('error', 'User not found.');
        }

        $branches = $branchModel->findAll();
        $assignedBranches = $branchUserModel->where('user_id', $id)->findAll();
        $assignedBranchIds = array_column($assignedBranches, 'branch_id');

        return view('admin/users/edit', [
            'user' => $user,
            'userData' => $userData,
            'branches' => $branches,
            'assignedBranchIds' => $assignedBranchIds
        ]);
    }

    public function updateUser($id)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $formData = $this->request->getPost();
        
        $userModel = new \App\Models\UserModel();
    $branchUserModel = new \App\Models\BranchStaffModel();

        $userData = $userModel->find($id);
        if (!$userData) {
            return redirect()->to('/admin/users')->with('error', 'User not found.');
        }

        // Update user data
        $updateData = [
            'name' => $formData['name'],
            'email' => $formData['email'],
            'phone' => $formData['phone'],
            'user_type' => $formData['user_type'],
            'gender' => $formData['gender'] ?? 'male',
            'occupation' => $formData['occupation'] ?? null,
            'nationality' => $formData['nationality'] ?? null
        ];

        // Update password if provided
        if (!empty($formData['password'])) {
            $updateData['password'] = password_hash($formData['password'], PASSWORD_DEFAULT);
        }

        if ($userModel->update($id, $updateData)) {
            // Update branch assignments
            $branchUserModel->where('user_id', $id)->delete();
            
            $branches = $formData['branches'] ?? [];
            $position = $formData['position'] ?? 'Staff';
            
            // Set default position based on user type if position is empty
            if (empty($position)) {
                switch ($formData['user_type']) {
                    case 'dentist':
                        $position = 'Dentist';
                        break;
                    case 'admin':
                        $position = 'Administrator';
                        break;
                    default:
                        $position = 'Staff';
                        break;
                }
            }
            
            foreach ($branches as $branchId) {
                $branchUserModel->insert([
                    'user_id' => $id,
                    'branch_id' => $branchId,
                    'position' => $position
                ]);
            }

            return redirect()->to('/admin/users')->with('success', 'User updated successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update user.');
    }

    public function toggleUserStatus($id)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $userModel = new \App\Models\UserModel();
        $userData = $userModel->find($id);
        
        if (!$userData) {
            return redirect()->to('/admin/users')->with('error', 'User not found.');
        }

        $newStatus = $userData['status'] === 'active' ? 'inactive' : 'active';
        $userModel->update($id, ['status' => $newStatus]);

        return redirect()->to('/admin/users')->with('success', 'User status updated successfully.');
    }

    public function deleteUser($id)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $userModel = new \App\Models\UserModel();
    $branchUserModel = new \App\Models\BranchStaffModel();

        // Delete branch assignments first
        $branchUserModel->where('user_id', $id)->delete();
        
        // Delete user
        if ($userModel->delete($id)) {
            return redirect()->to('/admin/users')->with('success', 'User deleted successfully.');
        }

        return redirect()->to('/admin/users')->with('error', 'Failed to delete user.');
    }


    public function updateAppointment($id)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $data = [
            'patient' => $this->request->getPost('patient'),
            'branch' => $this->request->getPost('branch'),
            'date' => $this->request->getPost('date'),
            'time' => $this->request->getPost('time'),
            'remarks' => $this->request->getPost('remarks'),
        ];

        $result = $this->appointmentService->updateAppointment($id, $data);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => (bool)$result, 'message' => $result ? 'Appointment updated successfully.' : 'Failed to update appointment.']);
        }

        session()->setFlashdata($result ? 'success' : 'error', $result ? 'Appointment updated successfully.' : 'Failed to update appointment.');
        return redirect()->to('/admin/appointments');
    }


    public function deleteAppointment($id)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $result = $this->appointmentService->deleteAppointment($id);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => (bool)$result, 'message' => $result ? 'Appointment deleted successfully.' : 'Failed to delete appointment.']);
        }

        session()->setFlashdata($result ? 'success' : 'error', $result ? 'Appointment deleted successfully.' : 'Failed to delete appointment.');
        return redirect()->to('/admin/appointments');
    }

    public function getAvailableDentists()
    {
        // ... existing logic
    }

    /**
     * Switch branch context for admin
     */
    public function switchBranch()
    {
        // Accept AJAX JSON or traditional POST form submissions
        $branchId = null;
        if ($this->request->isAJAX()) {
            $json = $this->request->getJSON(true);
            $branchId = $json['branch_id'] ?? null;
        } else {
            $branchId = $this->request->getPost('branch_id') ?: $this->request->getGet('branch_id');
        }
        
        // Validate branch exists
        if ($branchId) {
            $branchModel = new \App\Models\BranchModel();
            $branch = $branchModel->find($branchId);
            
            if (!$branch) {
                return $this->response->setJSON(['success' => false, 'message' => 'Branch not found']);
            }
        }
        
        // Save to session
        session()->set('selected_branch_id', $branchId);
        
        return $this->response->setJSON([
            'success' => true, 
            'message' => $branchId ? 'Branch switched successfully' : 'All branches selected'
        ]);
    }

    /**
     * Get current selected branch ID
     */
    protected function getSelectedBranchId()
    {
        return session('selected_branch_id');
    }

    /**
     * Filter data by selected branch
     */
    protected function filterByBranch($query, $branchColumn = 'branch_id')
    {
        $selectedBranchId = $this->getSelectedBranchId();
        if ($selectedBranchId) {
            $query->where($branchColumn, $selectedBranchId);
        }
        return $query;
    }

    // ==================== PATIENT MODAL API ENDPOINTS ====================
    public function getPatientInfo($id)
    {
        $auth = $this->checkAdminAuth();
        if ($auth instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $this->response->setJSON(['error' => 'Unauthorized'], 401);
        }

        $userModel = new \App\Models\UserModel();
        $patient = $userModel->find($id);
        if (!$patient || ($patient['user_type'] ?? null) !== 'patient') {
            return $this->response->setJSON(['error' => 'Patient not found'], 404);
        }

        $medicalFields = [
            'previous_dentist','last_dental_visit','physician_name','physician_specialty',
            'physician_phone','good_health','under_treatment','treatment_condition',
            'serious_illness','illness_details','hospitalized','hospitalization_where',
            'hospitalization_when','hospitalization_why','tobacco_use','blood_pressure',
            'allergies','pregnant','nursing','birth_control','medical_conditions',
            'other_conditions','special_notes'
        ];
        $medical = [];
        foreach ($medicalFields as $f) {
            $medical[$f] = $patient[$f] ?? null;
        }

        return $this->response->setJSON([
            'success' => true,
            'patient' => [
                'id' => $patient['id'],
                'name' => $patient['name'],
                'email' => $patient['email'],
                'phone' => $patient['phone'],
                'gender' => $patient['gender'],
                'address' => $patient['address'],
                'age' => $patient['age'],
            ],
            'medical' => $medical
        ]);
    }

    public function updatePatientNotes($id)
    {
        $auth = $this->checkAdminAuth();
        if ($auth instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $notes = $this->request->getPost('special_notes');
        $userModel = new \App\Models\UserModel();
        if (!$userModel->find($id)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Patient not found'], 404);
        }
        $userModel->update($id, ['special_notes' => $notes]);
        return $this->response->setJSON(['success' => true]);
    }

    public function getPatientDentalRecords($id)
    {
        $auth = $this->checkAdminAuth();
        if ($auth instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $this->response->setJSON(['error' => 'Unauthorized'], 401);
        }
        $model = new \App\Models\DentalRecordModel();
        $records = $model->select('dental_record.*, dentist.name as dentist_name')
            ->join('user as dentist', 'dentist.id = dental_record.dentist_id', 'left')
            ->where('dental_record.user_id', $id)
            ->orderBy('record_date', 'DESC')
            ->findAll();
        return $this->response->setJSON(['success' => true, 'records' => $records]);
    }

    public function getPatientDentalChart($id)
    {
        $auth = $this->checkAdminAuth();
        if ($auth instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $this->response->setJSON(['error' => 'Unauthorized'], 401);
        }
        $db = \Config\Database::connect();
        
        // Get dental chart data
        $rows = $db->table('dental_chart dc')
            ->select('dc.*, dr.record_date')
            ->join('dental_record dr', 'dr.id = dc.dental_record_id')
            ->where('dr.user_id', $id)
            ->orderBy('dr.record_date', 'DESC')
            ->get()->getResultArray();
        
        // Get dental records for this patient (alternative to visual chart data)
        $dentalRecords = $db->table('dental_record')
            ->select('id, record_date, treatment, notes')
            ->where('user_id', $id)
            ->orderBy('record_date', 'DESC')
            ->get()->getResultArray();
        
        return $this->response->setJSON([
            'success' => true, 
            'chart' => $rows,
            'visual_charts' => [], // Empty array since column doesn't exist
            'dental_records' => $dentalRecords
        ]);
    }

    public function getPatientAppointmentsModal($id)
    {
        $auth = $this->checkAdminAuth();
        if ($auth instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $this->response->setJSON(['error' => 'Unauthorized'], 401);
        }
        
        // Use the updated appointment service that returns categorized format
        $result = $this->appointmentService->getPatientAppointments($id);
        
        // Return the full result (which now includes present_appointments, past_appointments, etc.)
        return $this->response->setJSON($result);
    }

    public function getPatientTreatments($id)
    {
        // Placeholder until treatment tracking implemented
        return $this->response->setJSON(['success' => true, 'treatments' => []]);
    }

    public function getPatientMedicalRecords($id)
    {
        $userModel = new \App\Models\UserModel();
        $p = $userModel->find($id);
        if (!$p) {
            return $this->response->setJSON(['success' => false, 'message' => 'Not found'], 404);
        }
        return $this->response->setJSON([
            'success' => true,
            'medical' => [
                'previous_dentist' => $p['previous_dentist'] ?? null,
                'last_dental_visit' => $p['last_dental_visit'] ?? null,
                'blood_pressure' => $p['blood_pressure'] ?? null,
                'allergies' => $p['allergies'] ?? null,
                'medical_conditions' => $p['medical_conditions'] ?? null,
                'special_notes' => $p['special_notes'] ?? null
            ]
        ]);
    }

    public function getPatientInvoiceHistory($id)
    {
        $auth = $this->checkAdminAuth();
        if ($auth instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $this->response->setJSON(['error' => 'Unauthorized'], 401);
        }
        
        $db = \Config\Database::connect();
        
        // Get invoices for this patient
        $invoices = $db->table('invoices i')
            ->select('i.*, pr.procedure_name, u.name as patient_name')
            ->join('procedures pr', 'pr.id = i.procedure_id', 'left')
            ->join('user u', 'u.id = i.patient_id', 'left')
            ->where('i.patient_id', $id)
            ->orderBy('i.created_at', 'DESC')
            ->get()->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'invoices' => $invoices
        ]);
    }

    public function getPatientPrescriptions($id)
    {
        $auth = $this->checkAdminAuth();
        if ($auth instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $this->response->setJSON(['error' => 'Unauthorized'], 401);
        }
        
        $db = \Config\Database::connect();
        
        // Get prescriptions for this patient
        $prescriptions = $db->table('prescriptions pr')
            ->select('pr.*, u.name as patient_name, d.name as dentist_name')
            ->join('user u', 'u.id = pr.patient_id', 'left')
            ->join('user d', 'd.id = pr.dentist_id', 'left')
            ->where('pr.patient_id', $id)
            ->orderBy('pr.issue_date', 'DESC')
            ->get()->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'prescriptions' => $prescriptions
        ]);
    }

    /**
     * Get appointment details for admin modal
     */
    public function getAppointmentDetails($id)
    {
        $user = $this->getAuthenticatedUserApi();
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }

        try {
            $appointmentModel = new \App\Models\AppointmentModel();
            $appointment = $appointmentModel->select('appointments.*, user.name as patient_name, user.email as patient_email, branches.name as branch_name')
                                  ->join('user', 'user.id = appointments.user_id', 'left')
                                  ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                  ->where('appointments.id', $id)
                                  ->first();

            if (!$appointment) {
                return $this->response->setJSON(['success' => false, 'message' => 'Appointment not found'])->setStatusCode(404);
            }

            // Fetch services linked via appointment_service pivot (appointments table does not have service_id)
            $db = \Config\Database::connect();
            $svcRows = $db->table('appointment_service as aps')
                          ->select('s.id, s.name, s.duration_minutes, s.duration_max_minutes')
                          ->join('services s', 's.id = aps.service_id')
                          ->where('aps.appointment_id', (int)$id)
                          ->get()
                          ->getResultArray();

            $services = [];
            $totalServiceMinutes = 0;
            foreach ($svcRows as $s) {
                $dur = $s['duration_max_minutes'] ?? $s['duration_minutes'] ?? null;
                if ($dur !== null) $totalServiceMinutes += (int)$dur;
                $services[] = [
                    'id' => $s['id'],
                    'name' => $s['name'],
                    'duration_minutes' => $s['duration_minutes'] ?? null,
                    'duration_max_minutes' => $s['duration_max_minutes'] ?? null,
                ];
            }

            // Attach a friendly summary: service_name (first) and aggregated service_duration
            $appointment['services'] = $services;
            if (count($services) === 1) {
                $appointment['service_name'] = $services[0]['name'];
            } else if (count($services) > 1) {
                $appointment['service_name'] = implode(', ', array_column($services, 'name'));
            } else {
                $appointment['service_name'] = null;
            }

            $appointment['service_duration'] = $totalServiceMinutes > 0 ? $totalServiceMinutes : null;

            return $this->response->setJSON(['success' => true, 'appointment' => $appointment]);
        } catch (\Exception $e) {
            log_message('error', 'getAppointmentDetails exception: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Server error', 'error' => $e->getMessage()])->setStatusCode(500);
        }
    }

    /**
     * Check appointment conflicts for admin waitlist module
     * Provides retry/approve/reschedule options with clean suggestions
     * POST params: date, time, duration, branch_id, dentist_id, appointment_id (for updates)
     */
    public function checkAppointmentConflicts()
    {
        $user = $this->getAuthenticatedUserApi();
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }

        try {
            // Get parameters from POST request
            $date = $this->request->getPost('date');
            $time = $this->request->getPost('time');
            $duration = (int)($this->request->getPost('duration') ?? 30);
            $branchId = $this->request->getPost('branch_id') ?? null;
            $dentistId = $this->request->getPost('dentist_id') ?? null;
            $appointmentId = $this->request->getPost('appointment_id') ?? null; // For updates/reschedules
            $serviceId = $this->request->getPost('service_id') ?? null;

            // Validate required parameters
            if (!$date || !$time) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Missing required parameters: date and time'
                ])->setStatusCode(400);
            }

            // Get service duration if service_id is provided
            if ($serviceId && !$duration) {
                try {
                    $serviceModel = new \App\Models\ServiceModel();
                    $service = $serviceModel->find($serviceId);
                    if ($service) {
                        $duration = $service['duration_max_minutes'] ?? $service['duration_minutes'] ?? 30;
                    }
                } catch (\Exception $e) {
                    log_message('warning', 'Could not fetch service duration: ' . $e->getMessage());
                }
            }

            // Check for conflicts using appointment model
            $appointmentModel = new \App\Models\AppointmentModel();
            $requestedDatetime = $date . ' ' . $time . ':00';
            
            // Use grace period for conflict detection
            $grace = 15; // Default grace period
            try {
                $gpPath = WRITEPATH . 'grace_periods.json';
                if (is_file($gpPath)) {
                    $gp = json_decode(file_get_contents($gpPath), true);
                    if (!empty($gp['default'])) $grace = (int)$gp['default'];
                }
            } catch (\Exception $e) {
                // Use default grace
            }

            // Check for strict conflicts using model's conflict detection
            $strictConflict = $appointmentModel->isTimeConflictingWithGrace(
                $requestedDatetime, 
                $grace, 
                $dentistId, 
                $appointmentId, // Exclude current appointment if updating
                $duration
            );

            // Check availability blocks for dentist
            $availabilityConflicts = [];
            if ($dentistId) {
                try {
                    $availModel = new \App\Models\AvailabilityModel();
                    $blocks = $availModel->getBlocksBetween($date . ' 00:00:00', $date . ' 23:59:59', $dentistId);
                    foreach ($blocks as $b) {
                        $s = strtotime($b['start_datetime']);
                        $e = strtotime($b['end_datetime']);
                        if (strtotime($requestedDatetime) < $e && (strtotime($requestedDatetime) + ($duration + $grace) * 60) > $s) {
                            $availabilityConflicts[] = [
                                'type' => $b['type'], 
                                'start' => date('g:i A', $s), 
                                'end' => date('g:i A', $e), 
                                'notes' => $b['notes'] ?? ''
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    log_message('error', 'checkAppointmentConflicts availability error: ' . $e->getMessage());
                }
            }

            $hasConflicts = $strictConflict || !empty($availabilityConflicts);
            $suggestions = [];

            // If there are conflicts, generate reschedule suggestions using AvailabilityService
            if ($hasConflicts) {
                try {
                    $availabilityService = new \App\Services\AvailabilityService();
                    
                    $params = [
                        'date' => $date,
                        'branch_id' => $branchId,
                        'dentist_id' => $dentistId,
                        'duration' => $duration,
                        'granularity' => 5, // 5-minute increments for flexibility
                        'max_suggestions' => 15 // More suggestions for admin use
                    ];

                    $availabilityResult = $availabilityService->getAvailableSlots($params);
                    
                    if ($availabilityResult['success'] && !empty($availabilityResult['suggestions'])) {
                        // Filter suggestions to only include available slots
                        $suggestions = array_filter($availabilityResult['suggestions'], function($suggestion) {
                            return $suggestion['available'] === true;
                        });

                        // Limit suggestions but keep more for admin flexibility
                        $suggestions = array_slice($suggestions, 0, 15);
                    }

                    // If AvailabilityService didn't provide enough suggestions, try fallback method
                    if (count($suggestions) < 5) {
                        try {
                            $lookahead = 240; // Look ahead 4 hours
                            $next = $appointmentModel->findNextAvailableSlot($date, $time, $grace, $lookahead, $dentistId, $duration);
                            if ($next && !in_array($next['time'], array_column($suggestions, 'time'))) {
                                $suggestions[] = $next;
                            }
                        } catch (\Exception $e) {
                            log_message('warning', 'checkAppointmentConflicts: fallback suggestion lookup failed: ' . $e->getMessage()); 
                        }
                    }

                } catch (\Exception $e) {
                    log_message('error', 'checkAppointmentConflicts: AvailabilityService failed: ' . $e->getMessage());
                    
                    // Fallback to original suggestion method if AvailabilityService fails
                    try {
                        $lookahead = 240;
                        $next = $appointmentModel->findNextAvailableSlot($date, $time, $grace, $lookahead, $dentistId, $duration);
                        if ($next) $suggestions[] = $next;
                    } catch (\Exception $e2) {
                        log_message('warning', 'checkAppointmentConflicts: fallback suggestion lookup failed: ' . $e2->getMessage()); 
                    }
                }

                // Return conflict response with admin-specific options
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Scheduling conflict detected',
                    'conflict' => true,
                    'conflicts' => $strictConflict ? ['conflict' => true] : [],
                    'availability_conflicts' => $availabilityConflicts,
                    'hasConflicts' => $hasConflicts,
                    'suggestions' => $suggestions,
                    'admin_options' => [
                        'can_approve_anyway' => true, // Admin can override conflicts
                        'can_reschedule' => true,
                        'can_retry' => true
                    ],
                    'metadata' => [
                        'requested_time' => $time,
                        'requested_date' => $date,
                        'duration_minutes' => $duration,
                        'grace_minutes' => $grace,
                        'alternative_count' => count($suggestions),
                        'dentist_id' => $dentistId,
                        'branch_id' => $branchId
                    ]
                ]);
            }

            // No conflicts found
            return $this->response->setJSON([
                'success' => true,
                'message' => 'No conflicts detected',
                'conflict' => false,
                'conflicts' => [],
                'availability_conflicts' => $availabilityConflicts,
                'hasConflicts' => false,
                'suggestions' => [],
                'admin_options' => [
                    'can_approve' => true
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'checkAppointmentConflicts error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Server error while checking conflicts',
                'error_details' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}
