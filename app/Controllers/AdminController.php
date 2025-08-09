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
        return view('admin/dental/all_records', ['user' => $user]);
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

    public function invoice()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        return view('admin/billing/invoice', ['user' => $user]);
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
}
