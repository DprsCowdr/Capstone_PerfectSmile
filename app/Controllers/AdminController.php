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

        // AppointmentService::updateAppointment may return structured response with conflicts
        if (is_array($result)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON($result);
            }

            if (!empty($result['success'])) {
                session()->setFlashdata('success', $result['message'] ?? 'Appointment updated successfully.');
            } else {
                session()->setFlashdata('error', $result['message'] ?? 'Failed to update appointment.');
            }
            return redirect()->to('/admin/appointments');
        }

        // Fallback: boolean
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
        $rows = $db->table('dental_chart dc')
            ->select('dc.*, dr.record_date')
            ->join('dental_record dr', 'dr.id = dc.dental_record_id')
            ->where('dr.user_id', $id)
            ->orderBy('dr.record_date', 'DESC')
            ->get()->getResultArray();
        return $this->response->setJSON(['success' => true, 'chart' => $rows]);
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
}
