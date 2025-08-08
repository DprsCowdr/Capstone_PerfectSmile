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
                                ->orderBy('name', 'ASC');
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        return $query->findAll();
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
        // Validate data
        if (!$this->validatePatientData($data)) {
            return false;
        }
        
        $patientData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'date_of_birth' => $data['date_of_birth'],
            'gender' => $data['gender'],
            'age' => $data['age'] ?? null,
            'occupation' => $data['occupation'] ?? null,
            'nationality' => $data['nationality'] ?? null,
            'user_type' => 'patient',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->userModel->skipValidation(true)->insert($patientData);
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
            'gender' => 'required|in_list[male,female,other]',
            'date_of_birth' => 'required|valid_date'
        ];
        
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
            return false;
        }
        
        $validation->setRules($rules);
        
        if (!$validation->run($data)) {
            $this->validationErrors = $validation->getErrors();
            return false;
        }
        
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
            'total_dentists' => $this->userModel->where('user_type', 'dentist')->countAllResults(),
            'active_dentists' => $this->userModel->where('user_type', 'dentist')->where('status', 'active')->countAllResults(),
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
        $query = $this->userModel->where('user_type', 'dentist');
        
        if ($activeOnly) {
            $query->where('status', 'active');
        }
        
        return $query->findAll();
    }
}
