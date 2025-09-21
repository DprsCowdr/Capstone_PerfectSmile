<?php

namespace App\Services;

use App\Models\UserModel;

class UserService
{
    protected $userModel;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
    }
    
    /**
     * Get all patients with pagination
     */
    public function getAllPatients($limit = null, $offset = 0)
    {
        $query = $this->userModel->where('user_type', 'patient')
                                ->where('status', 'active')  // Only get active patients
                                ->orderBy('name', 'ASC');
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        $patients = $query->findAll();
        
        // Log patient count for debugging
        log_message('info', 'Retrieved ' . count($patients) . ' active patients (filtered)');
        log_message('info', 'Active patient data: ' . json_encode($patients));
        
        return $patients;
    }
    
    /**
     * Get patient by ID
     */
    public function getPatient($id)
    {
        $patient = $this->userModel->find($id);
        
        if (!$patient || $patient['user_type'] !== 'patient') {
            return null;
        }
        
        return $patient;
    }
    
    /**
     * Create new patient
     */
    public function createPatient($data)
    {
        // Log the incoming data for debugging
        log_message('info', 'Creating patient with data: ' . json_encode($data));
        
        // Validate data
        if (!$this->validatePatientData($data)) {
            log_message('error', 'Patient validation failed: ' . json_encode($this->getValidationErrors()));
            return false;
        }
        
        $patientData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'date_of_birth' => !empty($data['date_of_birth']) ? $data['date_of_birth'] : null,
            'gender' => $data['gender'],
            'age' => !empty($data['age']) && is_numeric($data['age']) ? (int)$data['age'] : null,
            'occupation' => $data['occupation'] ?? null,
            'nationality' => $data['nationality'] ?? null,
            'user_type' => 'patient',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        log_message('info', 'Patient data to insert: ' . json_encode($patientData));
        
        $result = $this->userModel->skipValidation(true)->insert($patientData);
        
        if ($result) {
            log_message('info', 'Patient created successfully with ID: ' . $result);
        } else {
            log_message('error', 'Failed to insert patient data');
        }
        
        return $result;
    }
    
    /**
     * Update patient
     */
    public function updatePatient($id, $data)
    {
        if (!$this->getPatient($id)) {
            return false;
        }
        
        if (!$this->validatePatientData($data, $id)) {
            return false;
        }
        
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'date_of_birth' => $data['date_of_birth'],
            'gender' => $data['gender'],
            'age' => $data['age'] ?? null,
            'occupation' => $data['occupation'] ?? null,
            'nationality' => $data['nationality'] ?? null,
        ];
        
        return $this->userModel->skipValidation(true)->update($id, $updateData);
    }
    
    /**
     * Toggle patient status
     */
    public function togglePatientStatus($id)
    {
        $patient = $this->getPatient($id);
        if (!$patient) {
            return false;
        }
        
        $newStatus = ($patient['status'] === 'active') ? 'inactive' : 'active';
        
        return $this->userModel->update($id, ['status' => $newStatus]);
    }
    
    /**
     * Create user account for patient
     */
    public function createPatientAccount($patientId, $password)
    {
        if (strlen($password) < 6) {
            return false;
        }
        
        $patient = $this->getPatient($patientId);
        if (!$patient) {
            return false;
        }
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        return $this->userModel->update($patientId, [
            'password' => $hashedPassword,
            'account_created' => 1
        ]);
    }
    
    /**
     * Validate patient data
     */
    public function validatePatientData($data, $excludeId = null)
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'name' => 'required|min_length[2]',
            'email' => 'required|valid_email',
            'phone' => 'required',
            'address' => 'required',
            'gender' => 'required|in_list[Male,Female,Other]',
            'date_of_birth' => 'permit_empty|valid_date'
        ];
        
        // Log validation attempt
        log_message('info', 'Validating patient data: ' . json_encode($data));
        
        // Check for unique email (excluding current record if updating)
        if ($excludeId) {
            $existingUser = $this->userModel->where('email', $data['email'])
                                          ->where('id !=', $excludeId)
                                          ->first();
        } else {
            $existingUser = $this->userModel->where('email', $data['email'])->first();
        }
        
        if ($existingUser) {
            $this->validationErrors = ['email' => 'Email already exists'];
            log_message('error', 'Patient validation failed: Email already exists');
            return false;
        }
        
        $validation->setRules($rules);
        
        if (!$validation->run($data)) {
            $this->validationErrors = $validation->getErrors();
            log_message('error', 'Patient validation failed: ' . json_encode($this->validationErrors));
            return false;
        }
        
        log_message('info', 'Patient validation passed');
        return true;
    }
    
    protected $validationErrors = [];
    
    public function getValidationErrors()
    {
        return $this->validationErrors;
    }
    
    /**
     * Get user statistics
     */
    public function getUserStatistics()
    {
        return [
            'total_patients' => $this->userModel->where('user_type', 'patient')->countAllResults(),
            'active_patients' => $this->userModel->where('user_type', 'patient')->where('status', 'active')->countAllResults(),
            'total_dentists' => $this->userModel->where('user_type', 'doctor')->countAllResults(),
            'active_dentists' => $this->userModel->where('user_type', 'doctor')->where('status', 'active')->countAllResults(),
            'total_staff' => $this->userModel->where('user_type', 'staff')->countAllResults()
        ];
    }
    
    /**
     * Get recent patients
     */
    public function getRecentPatients($limit = 5)
    {
        return $this->userModel->where('user_type', 'patient')
                              ->orderBy('created_at', 'DESC')
                              ->limit($limit)
                              ->findAll();
    }
    
    /**
     * Get all dentists
     */
    public function getAllDentists($activeOnly = true)
    {
        $query = $this->userModel->where('user_type', 'doctor');
        
        if ($activeOnly) {
            $query->where('status', 'active');
        }
        
        return $query->findAll();
    }

    /**
     * Get all patients for account activation (includes inactive patients)
     */
    public function getPatientsForActivation()
    {
        $query = $this->userModel->where('user_type', 'patient')
                                ->orderBy('name', 'ASC');
        
        $patients = $query->findAll();
        
        // Log patient count for debugging
        log_message('info', 'Retrieved ' . count($patients) . ' patients for activation');
        
        return $patients;
    }

    /**
     * Activate patient account and generate password
     */
    public function activatePatientAccount($patientId)
    {
        $patient = $this->getPatient($patientId);
        if (!$patient) {
            $this->validationErrors = ['patient' => 'Patient not found'];
            return false;
        }

        // Generate a simple password for prototype
        $tempPassword = 'temp' . rand(1000, 9999);
        
        // Update patient with new password and active status
        $updateData = [
            'password' => password_hash($tempPassword, PASSWORD_DEFAULT),
            'status' => 'active',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $this->userModel->update($patientId, $updateData);
        
        if ($result) {
            log_message('info', "Patient account activated for ID: {$patientId} with temp password: {$tempPassword}");
            
            // For prototype, return the password so admin can share it
            return [
                'success' => true,
                'password' => $tempPassword,
                'patient' => $patient
            ];
        }
        
        return false;
    }

    /**
     * Deactivate patient account
     */
    public function deactivatePatientAccount($patientId)
    {
        $patient = $this->getPatient($patientId);
        if (!$patient) {
            $this->validationErrors = ['patient' => 'Patient not found'];
            return false;
        }

        $updateData = [
            'status' => 'inactive',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $this->userModel->update($patientId, $updateData);
        
        if ($result) {
            log_message('info', "Patient account deactivated for ID: {$patientId}");
            return true;
        }
        
        return false;
    }
}
