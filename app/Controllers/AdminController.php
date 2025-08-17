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
            log_message('info', "Admin approving appointment ID: {$id}, Dentist ID: " . ($dentistId ?: 'null'));
            
            // Get appointment details before approval for logging
            $appointmentModel = new \App\Models\AppointmentModel();
            $appointment = $appointmentModel->find($id);
            if ($appointment) {
                log_message('info', "Appointment before approval: " . json_encode($appointment));
            }
            
            $result = $this->appointmentService->approveAppointment($id, $dentistId);
            
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
        return view('admin/management/services', ['user' => $user]);
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
        
        // Get all dental records with complete patient and medical history information
        $records = $dentalRecordModel->getRecordsWithPatientInfo(50, 0);

        // Calculate statistics
        $totalRecords = $dentalRecordModel->countAll();
        
        // Count active patients (patients with records in last 6 months)
        $activePatients = $dentalRecordModel->select('DISTINCT user_id')
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
            'records' => $records,
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
        return view('admin/management/roles', ['user' => $user]);
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
        $branchUserModel = new \App\Models\BranchUserModel();
        $branchAssignments = [];
        foreach ($users as $userData) {
            $assignments = $branchUserModel->select('branch_user.*, branches.name as branch_name')
                                          ->join('branches', 'branches.id = branch_user.branch_id')
                                          ->where('branch_user.user_id', $userData['id'])
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
        $branchUserModel = new \App\Models\BranchUserModel();

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

        $userId = $userModel->insert($userData);

        if ($userId) {
            // Assign branches
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
                    'user_id' => $userId,
                    'branch_id' => $branchId,
                    'position' => $position
                ]);
            }

            return redirect()->to('/admin/users')->with('success', 'User created successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create user.');
    }

    public function editUser($id)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $userModel = new \App\Models\UserModel();
        $branchModel = new \App\Models\BranchModel();
        $branchUserModel = new \App\Models\BranchUserModel();

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
        $branchUserModel = new \App\Models\BranchUserModel();

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
        $branchUserModel = new \App\Models\BranchUserModel();

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
        // ... existing update logic
    }

    public function deleteAppointment($id)
    {
        // ... existing delete logic
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
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $branchId = $this->request->getJSON()->branch_id ?? '';
        
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

    // ==================== PATIENT RECORDS POPUP ====================
    
    /**
     * Get comprehensive patient information for popup including medical history, dental summary, etc.
     */
    public function getPatientInfo($patientId)
    {
        $user = $this->getAuthenticatedUserApi();
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }

        try {
            $userModel = new \App\Models\UserModel();
            $dentalRecordModel = new \App\Models\DentalRecordModel();
            $appointmentModel = new \App\Models\AppointmentModel();
            
            // Get basic patient info
            $patient = $userModel->where('id', $patientId)
                                ->where('user_type', 'patient')
                                ->first();

            if (!$patient) {
                return $this->response->setJSON(['success' => false, 'message' => 'Patient not found']);
            }

            // Get dental records summary
            $dentalRecords = $dentalRecordModel->select('dental_record.*, user.name as dentist_name')
                                             ->join('user', 'user.id = dental_record.dentist_id', 'left')
                                             ->where('dental_record.user_id', $patientId)
                                             ->orderBy('dental_record.record_date', 'DESC')
                                             ->limit(5)
                                             ->findAll();

            // Get appointment statistics
            $totalVisits = $appointmentModel->where('user_id', $patientId)
                                          ->where('status', 'completed')
                                          ->countAllResults();

            $lastVisit = $appointmentModel->where('user_id', $patientId)
                                        ->where('status', 'completed')
                                        ->orderBy('appointment_datetime', 'DESC')
                                        ->first();

            $nextAppointment = $appointmentModel->where('user_id', $patientId)
                                              ->where('status', 'scheduled')
                                              ->where('appointment_datetime >=', date('Y-m-d H:i:s'))
                                              ->orderBy('appointment_datetime', 'ASC')
                                              ->first();

            // Get last diagnosis from most recent dental record
            $lastDiagnosis = '';
            if (!empty($dentalRecords)) {
                $lastDiagnosis = $dentalRecords[0]['diagnosis'] ?? '';
            }

            // Format comprehensive patient data (using only available fields)
            $patientData = [
                // Basic Information (from user table)
                'id' => $patient['id'],
                'name' => $patient['name'],
                'email' => $patient['email'],
                'phone' => $patient['phone'],
                'address' => $patient['address'],
                'date_of_birth' => $patient['date_of_birth'],
                'gender' => $patient['gender'],
                'age' => $patient['age'],
                'occupation' => $patient['occupation'],
                'nationality' => $patient['nationality'],
                'status' => $patient['status'],
                'created_at' => $patient['created_at'],

                // Visit Statistics
                'total_visits' => $totalVisits,
                'last_visit_date' => $lastVisit['appointment_datetime'] ?? null,
                'next_appointment_date' => $nextAppointment['appointment_datetime'] ?? null,
                'last_diagnosis' => $lastDiagnosis,

                // Special Notes (from user table)
                'special_notes' => $patient['special_notes'] ?? null,
            ];

            return $this->response->setJSON([
                'success' => true,
                'patient' => $patientData,
                'recent_records' => $dentalRecords
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error fetching comprehensive patient info: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error fetching patient information: ' . $e->getMessage()]);
        }
    }

    /**
     * Get patient dental records
     */
    public function getPatientDentalRecords($patientId)
    {
        $user = $this->getAuthenticatedUserApi();
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }

        try {
            $dentalRecordModel = new \App\Models\DentalRecordModel();
            
            $records = $dentalRecordModel->select('dental_record.*, dentist.name as dentist_name')
                                       ->join('user as dentist', 'dentist.id = dental_record.dentist_id')
                                       ->where('dental_record.user_id', $patientId)
                                       ->orderBy('record_date', 'DESC')
                                       ->findAll();

            return $this->response->setJSON([
                'success' => true,
                'records' => $records
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error fetching dental records: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error fetching dental records: ' . $e->getMessage()]);
        }
    }

    /**
     * Get patient dental chart with complete tooth information
     */
    public function getPatientDentalChart($patientId)
    {
        $user = $this->getAuthenticatedUserApi();
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }

        try {
            $dentalChartModel = new \App\Models\DentalChartModel();
            $dentalRecordModel = new \App\Models\DentalRecordModel();
            $serviceModel = new \App\Models\ServiceModel();
            
            // Get dental chart data with related record and service information
            $chartData = $dentalChartModel->select('dental_chart.*,
                                                  dental_record.record_date,
                                                  dental_record.diagnosis,
                                                  dentist.name as dentist_name,
                                                  services.name as recommended_service,
                                                  services.price as service_price')
                                        ->join('dental_record', 'dental_record.id = dental_chart.dental_record_id', 'left')
                                        ->join('user as dentist', 'dentist.id = dental_record.dentist_id', 'left')
                                        ->join('services', 'services.id = dental_chart.recommended_service_id', 'left')
                                        ->where('dental_record.user_id', $patientId)
                                        ->orderBy('dental_chart.tooth_number', 'ASC')
                                        ->orderBy('dental_record.record_date', 'DESC')
                                        ->findAll();

            // Group by tooth number for better organization
            $teethData = [];
            foreach ($chartData as $chart) {
                $toothNumber = $chart['tooth_number'];
                if (!isset($teethData[$toothNumber])) {
                    $teethData[$toothNumber] = [];
                }
                $teethData[$toothNumber][] = $chart;
            }

            return $this->response->setJSON([
                'success' => true,
                'chart' => $chartData,
                'teeth_data' => $teethData
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error fetching dental chart: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error fetching dental chart: ' . $e->getMessage()]);
        }
    }

    /**
     * Get patient appointments for modal with present and past appointments separated
     */
    public function getPatientAppointmentsModal($patientId)
    {
        $user = $this->getAuthenticatedUserApi();
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }

        try {
            $appointmentModel = new \App\Models\AppointmentModel();
            $appointmentServiceModel = new \App\Models\AppointmentServiceModel();
            
            // Get all appointments with services and costs
            $appointments = $appointmentModel->select('appointments.*, 
                                                     dentist.name as dentist_name,
                                                     branches.name as branch_name,
                                                     GROUP_CONCAT(services.name SEPARATOR ", ") as services,
                                                     SUM(services.price) as total_cost')
                                           ->join('user as dentist', 'dentist.id = appointments.dentist_id', 'left')
                                           ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                           ->join('appointment_service', 'appointment_service.appointment_id = appointments.id', 'left')
                                           ->join('services', 'services.id = appointment_service.service_id', 'left')
                                           ->where('appointments.user_id', $patientId)
                                           ->groupBy('appointments.id')
                                           ->orderBy('appointment_datetime', 'DESC')
                                           ->findAll();

            // Separate present/future and past appointments
            $currentDateTime = new \DateTime();
            $presentAppointments = [];
            $pastAppointments = [];

            foreach ($appointments as $appointment) {
                $appointmentDateTime = new \DateTime($appointment['appointment_datetime']);
                if ($appointmentDateTime >= $currentDateTime) {
                    $presentAppointments[] = $appointment;
                } else {
                    $pastAppointments[] = $appointment;
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'present_appointments' => $presentAppointments,
                'past_appointments' => $pastAppointments,
                'total_appointments' => count($appointments)
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error fetching appointments: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error fetching appointments: ' . $e->getMessage()]);
        }
    }

    /**
     * Get patient treatment records with doctor, procedures, amounts, and status
     */
    public function getPatientTreatments($patientId)
    {
        $user = $this->getAuthenticatedUserApi();
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }

        try {
            $dentalRecordModel = new \App\Models\DentalRecordModel();
            $procedureModel = new \App\Models\ProcedureModel();
            $appointmentModel = new \App\Models\AppointmentModel();
            
            // Get comprehensive treatment information from multiple sources
            $treatments = [];
            
            // 1. Get treatments from dental records
            $dentalTreatments = $dentalRecordModel->select('dental_record.id,
                                                          dental_record.record_date,
                                                          dental_record.treatment,
                                                          dental_record.diagnosis,
                                                          dental_record.notes,
                                                          dental_record.next_appointment_date,
                                                          dentist.name as doctor_name,
                                                          "dental_record" as source_type')
                                                ->join('user as dentist', 'dentist.id = dental_record.dentist_id')
                                                ->where('dental_record.user_id', $patientId)
                                                ->where('dental_record.treatment IS NOT NULL')
                                                ->where('dental_record.treatment !=', '')
                                                ->orderBy('record_date', 'DESC')
                                                ->findAll();
            
            // 2. Get procedures done
            $procedures = $procedureModel->select('procedures.id,
                                                 procedures.procedure_date as record_date,
                                                 procedures.procedure_name as treatment,
                                                 procedures.description as diagnosis,
                                                 "" as notes,
                                                 "" as next_appointment_date,
                                                 user.name as doctor_name,
                                                 "procedure" as source_type')
                                       ->join('user', 'user.id = procedures.user_id', 'left')
                                       ->where('procedures.user_id', $patientId)
                                       ->orderBy('procedure_date', 'DESC')
                                       ->findAll();
            
            // 3. Get completed appointments with services and costs
            $completedAppointments = $appointmentModel->select('appointments.id,
                                                              appointments.appointment_datetime as record_date,
                                                              GROUP_CONCAT(services.name SEPARATOR ", ") as treatment,
                                                              appointments.appointment_type as diagnosis,
                                                              appointments.remarks as notes,
                                                              "" as next_appointment_date,
                                                              dentist.name as doctor_name,
                                                              SUM(services.price) as amount,
                                                              appointments.status,
                                                              "appointment" as source_type')
                                                    ->join('user as dentist', 'dentist.id = appointments.dentist_id', 'left')
                                                    ->join('appointment_service', 'appointment_service.appointment_id = appointments.id', 'left')
                                                    ->join('services', 'services.id = appointment_service.service_id', 'left')
                                                    ->where('appointments.user_id', $patientId)
                                                    ->where('appointments.status', 'completed')
                                                    ->groupBy('appointments.id')
                                                    ->orderBy('appointment_datetime', 'DESC')
                                                    ->findAll();
            
            // Combine all treatments and add status and amount information
            $allTreatments = [];
            
            // Process dental records
            foreach ($dentalTreatments as $treatment) {
                $treatment['amount'] = 0; // Default amount, could be enhanced with billing data
                $treatment['status'] = $treatment['next_appointment_date'] ? 'ongoing' : 'completed';
                $allTreatments[] = $treatment;
            }
            
            // Process procedures
            foreach ($procedures as $procedure) {
                $procedure['amount'] = 0; // Default amount
                $procedure['status'] = 'completed';
                $allTreatments[] = $procedure;
            }
            
            // Process completed appointments
            foreach ($completedAppointments as $appointment) {
                $appointment['amount'] = $appointment['amount'] ?? 0;
                $allTreatments[] = $appointment;
            }
            
            // Sort all treatments by date
            usort($allTreatments, function($a, $b) {
                return strtotime($b['record_date']) - strtotime($a['record_date']);
            });

            return $this->response->setJSON([
                'success' => true,
                'treatments' => $allTreatments,
                'total_treatments' => count($allTreatments)
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error fetching treatments: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error fetching treatments: ' . $e->getMessage()]);
        }
    }

    /**
     * Get comprehensive patient medical records
     */
    public function getPatientMedicalRecords($patientId)
    {
        $user = $this->getAuthenticatedUserApi();
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }

        try {
            $dentalRecordModel = new \App\Models\DentalRecordModel();
            $userModel = new \App\Models\UserModel();
            
            // Get patient basic medical information
            $patient = $userModel->find($patientId);
            
            // Get comprehensive medical records from dental records
            $medicalRecords = $dentalRecordModel->select('dental_record.id,
                                                        dental_record.record_date,
                                                        dental_record.diagnosis,
                                                        dental_record.treatment,
                                                        dental_record.notes,
                                                        dental_record.xray_image_url,
                                                        dentist.name as recorded_by,
                                                        dentist.user_type as recorder_role')
                                              ->join('user as dentist', 'dentist.id = dental_record.dentist_id')
                                              ->where('dental_record.user_id', $patientId)
                                              ->orderBy('record_date', 'DESC')
                                              ->findAll();

            // Organize medical data
            $medicalHistory = [];
            $diagnoses = [];
            $xrays = [];
            
            foreach ($medicalRecords as $record) {
                // Group diagnoses
                if (!empty($record['diagnosis'])) {
                    $diagnoses[] = [
                        'date' => $record['record_date'],
                        'diagnosis' => $record['diagnosis'],
                        'treatment' => $record['treatment'],
                        'doctor' => $record['recorded_by']
                    ];
                }
                
                // Group X-rays
                if (!empty($record['xray_image_url'])) {
                    $xrays[] = [
                        'date' => $record['record_date'],
                        'image_url' => $record['xray_image_url'],
                        'notes' => $record['notes'],
                        'doctor' => $record['recorded_by']
                    ];
                }
                
                $medicalHistory[] = $record;
            }

            return $this->response->setJSON([
                'success' => true,
                'medical_records' => $medicalHistory,
                'patient_info' => [
                    'name' => $patient['name'],
                    'age' => $patient['age'],
                    'gender' => $patient['gender'],
                    'date_of_birth' => $patient['date_of_birth']
                ],
                'diagnoses' => $diagnoses,
                'xrays' => $xrays,
                'summary' => [
                    'total_records' => count($medicalHistory),
                    'total_diagnoses' => count($diagnoses),
                    'total_xrays' => count($xrays)
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error fetching medical records: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error fetching medical records: ' . $e->getMessage()]);
        }
    }

    /**
     * Update patient special notes
     */
    public function updatePatientNotes($patientId)
    {
        $user = $this->getAuthenticatedUserApi();
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }

        try {
            $userModel = new \App\Models\UserModel();
            $input = $this->request->getJSON(true);
            
            if (!isset($input['notes'])) {
                return $this->response->setJSON(['success' => false, 'message' => 'Notes are required']);
            }

            // Update the user table with special notes
            $existingUser = $userModel->where('id', $patientId)
                                     ->where('user_type', 'patient')
                                     ->first();
            
            if ($existingUser) {
                $userModel->update($patientId, ['special_notes' => $input['notes']]);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Patient not found']);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Patient notes updated successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error updating patient notes: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error updating notes: ' . $e->getMessage()]);
        }
    }
}
