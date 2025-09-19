<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\UserService;
use App\Services\AppointmentService;

abstract class BaseAdminController extends BaseController
{
    protected $userService;
    protected $appointmentService;
    protected $authService;
    
    public function __construct()
    {
        $this->userService = new UserService();
        $this->appointmentService = new AppointmentService();
        $this->authService = new AuthService();
    }
    
    /**
     * Get patients view - shared logic
     */
    protected function getPatientsView($viewPath)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        
        // Use UserService to get patients (includes active status filter)
        $patients = $this->userService->getAllPatients();
        
        // Log for debugging
        log_message('info', 'Patients view: Retrieved ' . count($patients) . ' patients');
        
        // Filter by selected branch if admin has chosen one
        $selectedBranchId = session('selected_branch_id');
        if ($selectedBranchId && $user['user_type'] === 'admin') {
            // Only filter out patients explicitly assigned to another branch.
            // Show unassigned patients (no branch_id) so patient lists do not disappear.
            $patients = array_filter($patients, function($patient) use ($selectedBranchId) {
                $b = $patient['branch_id'] ?? null;
                if ($b === null || $b === '' ) return true; // show unassigned patients
                return (string)$b === (string)$selectedBranchId;
            });
        }
        
        $data = [
            'user' => $user,
            'patients' => $patients,
            'selectedBranchId' => $selectedBranchId
        ];
        
        return view($viewPath, $data);
    }
    
    /**
     * Add patient form - shared between Admin and Staff
     */
    protected function getAddPatientView($viewPath)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        
        return view($viewPath, ['user' => $user]);
    }
    
    /**
     * Store patient - shared logic
     */
    protected function storePatientLogic($redirectPath)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $formData = $this->request->getPost();
        
        if (!$this->userService->validatePatientData($formData)) {
            return redirect()->back()->withInput()->with('error', $this->userService->getValidationErrors());
        }

        if ($this->userService->createPatient($formData)) {
            return redirect()->to($redirectPath)->with('success', 'Patient added successfully.');
        }
        
        return redirect()->back()->withInput()->with('error', 'Failed to add patient.');
    }
    
    /**
     * Get patient for API - shared logic
     */
    protected function getPatientApi($id)
    {
        $user = $this->getAuthenticatedUserApi();
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }
        
        $patient = $this->userService->getPatient($id);
        
        if (!$patient) {
            return $this->response->setJSON(['error' => 'Patient not found']);
        }
        
        return $this->response->setJSON($patient);
    }
    
    /**
     * Update patient - shared logic
     */
    protected function updatePatientLogic($id, $redirectPath)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        
        $patient = $this->userService->getPatient($id);
        if (!$patient) {
            return redirect()->to($redirectPath)->with('error', 'Patient not found.');
        }
        
        $formData = $this->request->getPost();
        
        if (!$this->userService->validatePatientData($formData, $id)) {
            return redirect()->back()->withInput()->with('error', $this->userService->getValidationErrors());
        }
        
        if ($this->userService->updatePatient($id, $formData)) {
            return redirect()->to($redirectPath)->with('success', 'Patient updated successfully.');
        }
        
        return redirect()->back()->withInput()->with('error', 'Failed to update patient.');
    }
    
    /**
     * Toggle patient status - shared logic
     */
    protected function togglePatientStatusLogic($id, $redirectPath)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        
        if (!$this->userService->getPatient($id)) {
            return redirect()->to($redirectPath)->with('error', 'Patient not found.');
        }
        
        if ($this->userService->togglePatientStatus($id)) {
            return redirect()->to($redirectPath)->with('success', 'Patient status updated successfully.');
        }
        
        return redirect()->to($redirectPath)->with('error', 'Failed to update patient status.');
    }

    /**
     * Get patient activation view - shared logic
     */
    protected function getPatientActivationView($viewPath)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        
        // Get all patients (both active and inactive) for activation management
        $patients = $this->userService->getPatientsForActivation();
        
        $data = [
            'user' => $user,
            'patients' => $patients
        ];
        
        return view($viewPath, $data);
    }

    /**
     * Activate patient account - shared logic
     */
    protected function activatePatientAccountLogic($id, $redirectPath)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        
        $result = $this->userService->activatePatientAccount($id);
        
        if ($result && is_array($result)) {
            $message = "Patient account activated successfully! Temporary password: " . $result['password'];
            return redirect()->to($redirectPath)->with('success', $message);
        }
        
        $errors = $this->userService->getValidationErrors();
        $errorMessage = is_array($errors) ? implode(', ', $errors) : 'Failed to activate patient account.';
        
        return redirect()->to($redirectPath)->with('error', $errorMessage);
    }

    /**
     * Deactivate patient account - shared logic
     */
    protected function deactivatePatientAccountLogic($id, $redirectPath)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        
        if ($this->userService->deactivatePatientAccount($id)) {
            return redirect()->to($redirectPath)->with('success', 'Patient account deactivated successfully.');
        }
        
        $errors = $this->userService->getValidationErrors();
        $errorMessage = is_array($errors) ? implode(', ', $errors) : 'Failed to deactivate patient account.';
        
        return redirect()->to($redirectPath)->with('error', $errorMessage);
    }
    
    /**
     * Get appointments view - shared logic
     */
    protected function getAppointmentsView($viewPath, $additionalData = [])
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        
        // Get form data for dropdowns
        $userModel = new \App\Models\UserModel();
        $branchModel = new \App\Models\BranchModel();
        
        $patients = $userModel->where('user_type', 'patient')->findAll();
        $branches = $branchModel->findAll();
        $dentists = $userModel->where('user_type', 'dentist')->where('status', 'active')->findAll();
        
        // Get appointments with branch filtering
        $selectedBranchId = session('selected_branch_id');
        if ($selectedBranchId && $user['user_type'] === 'admin') {
            $appointments = $this->appointmentService->getAllAppointments($selectedBranchId);
        } else {
            $appointments = $this->appointmentService->getAllAppointments();
        }
        
        $data = array_merge([
            'user' => $user,
            'appointments' => $appointments,
            'patients' => $patients,
            'branches' => $branches,
            'dentists' => $dentists,
            'selectedBranchId' => $selectedBranchId
        ], $additionalData);
        
        return view($viewPath, $data);
    }
    
    /**
     * Create appointment - shared logic
     */
    protected function createAppointmentLogic($redirectPath, $userType = 'admin')
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        // Accept either legacy or explicit field names so both patient and admin forms work.
        $data = [
            // branch may be posted as 'branch' (admin form) or 'branch_id' elsewhere
            'branch_id' => $this->request->getPost('branch') ?: $this->request->getPost('branch_id'),
            // user/patient field may be named 'patient' or 'user_id'
            'user_id' => $this->request->getPost('patient') ?: $this->request->getPost('user_id'),
            // dentist may be named 'dentist' or 'dentist_id'
            'dentist_id' => $this->request->getPost('dentist') ?: $this->request->getPost('dentist_id') ?: null,
            // Date/time may come as 'date'/'time' (admin) or as 'appointment_date'/'appointment_time' (patient)
            'appointment_date' => $this->request->getPost('appointment_date') ?: $this->request->getPost('date'),
            'appointment_time' => $this->request->getPost('appointment_time') ?: $this->request->getPost('time'),
            // Pass service_id and procedure_duration when present so server can compute duration correctly
            'service_id' => $this->request->getPost('service_id') ?: null,
            'procedure_duration' => $this->request->getPost('procedure_duration') ?: null,
            'appointment_type' => $this->request->getPost('appointment_type') ?? 'scheduled',
            'remarks' => $this->request->getPost('remarks')
        ];

        // Set approval logic based on user type and appointment type
        if ($data['appointment_type'] === 'walkin') {
            $data['approval_status'] = 'auto_approved';
            $data['status'] = 'confirmed';
        } else {
            // Scheduled appointments
            if ($userType === 'admin') {
                $data['approval_status'] = 'pending';
                $data['status'] = 'pending_approval';
            } else {
                // Staff created appointments always go to pending
                $data['approval_status'] = 'pending';
                $data['status'] = 'pending';
            }
        }

    // Annotate who created this appointment so AppointmentService can select message template
    $data['created_by_role'] = $userType === 'admin' ? 'admin' : 'staff';
    $result = $this->appointmentService->createAppointment($data);

        // If the request is AJAX (fetch/XHR), return JSON so client can update UI immediately
        if ($this->request->isAJAX()) {
            // Ensure response includes the saved record when available
            return $this->response->setJSON($result);
        }

        // Otherwise use flash messages and redirect as before
        session()->setFlashdata($result['success'] ? 'success' : 'error', $result['message']);
        return redirect()->to($redirectPath);
    }
    
    /**
     * Abstract method to get authenticated user - implement in child classes
     */
    abstract protected function getAuthenticatedUser();
    
    /**
     * Abstract method to get authenticated user for API - implement in child classes
     */
    abstract protected function getAuthenticatedUserApi();
}
