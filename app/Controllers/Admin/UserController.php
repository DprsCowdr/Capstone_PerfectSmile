<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseAdminController;
use App\Traits\AdminAuthTrait;

class UserController extends BaseAdminController
{
    use AdminAuthTrait;
    
    public function __construct()
    {
        parent::__construct();
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

    // ==================== USERS MANAGEMENT ====================
    public function index()
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

    public function add()
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

    public function store()
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

    public function edit($id)
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

    public function update($id)
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

    public function toggleStatus($id)
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

    public function delete($id)
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
}
