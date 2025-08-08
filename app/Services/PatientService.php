<?php

namespace App\Services;

class PatientService
{
    protected $userModel;
    protected $validation;
    
    public function __construct()
    {
        $this->userModel = new \App\Models\UserModel();
        $this->validation = \Config\Services::validation();
    }
    
    public function getAllPatients()
    {
        return $this->userModel->where('user_type', 'patient')->findAll();
    }
    
    public function getPatient($id)
    {
        $patient = $this->userModel->find($id);
        
        if (!$patient || $patient['user_type'] !== 'patient') {
            return null;
        }
        
        return $patient;
    }
    
    public function validatePatientData($data)
    {
        $this->validation->setRules([
            'name' => 'required|min_length[2]',
            'email' => 'required|valid_email',
            'phone' => 'required',
            'address' => 'required',
            'gender' => 'required',
            'date_of_birth' => 'required|valid_date',
        ]);
        
        return $this->validation->run($data);
    }
    
    public function getValidationErrors()
    {
        return $this->validation->getErrors();
    }
    
    public function createPatient($data)
    {
        $patientData = [
            'name' => $data['name'],
            'address' => $data['address'],
            'date_of_birth' => $data['date_of_birth'],
            'gender' => $data['gender'],
            'age' => $data['age'] ?? null,
            'phone' => $data['phone'],
            'email' => $data['email'],
            'occupation' => $data['occupation'] ?? null,
            'nationality' => $data['nationality'] ?? null,
            'user_type' => 'patient',
            'status' => 'active',
        ];

        return $this->userModel->skipValidation(true)->insert($patientData);
    }
    
    public function updatePatient($id, $data)
    {
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'gender' => $data['gender'],
            'date_of_birth' => $data['date_of_birth'],
            'age' => $data['age'] ?? null,
            'occupation' => $data['occupation'] ?? null,
            'nationality' => $data['nationality'] ?? null,
        ];
        
        return $this->userModel->skipValidation(true)->update($id, $updateData);
    }
    
    public function toggleStatus($id)
    {
        $patient = $this->getPatient($id);
        
        if (!$patient) {
            return false;
        }
        
        $newStatus = ($patient['status'] === 'active') ? 'inactive' : 'active';
        
        return $this->userModel->update($id, ['status' => $newStatus]);
    }
    
    public function createAccount($id, $password)
    {
        if (!$password || strlen($password) < 6) {
            return false;
        }
        
        return $this->userModel->update($id, [
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);
    }
} 