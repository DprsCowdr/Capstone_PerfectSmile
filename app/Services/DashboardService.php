<?php

namespace App\Services;

class DashboardService
{
    protected $userModel;
    protected $branchModel;
    
    public function __construct()
    {
        $this->userModel = new \App\Models\UserModel();
        $this->branchModel = new \App\Models\BranchModel();
    }
    
    public function getStatistics()
    {
        return [
            'totalUsers' => $this->userModel->countAll(),
            'totalPatients' => $this->userModel->where('user_type', 'patient')->countAllResults(),
            'totalDentists' => $this->userModel->where('user_type', 'dentist')->countAllResults(),
            'totalBranches' => $this->branchModel->countAll()
        ];
    }
    
    public function getFormData()
    {
        return [
            'patients' => $this->userModel->where('user_type', 'patient')->findAll(),
            'branches' => $this->branchModel->findAll(),
            'dentists' => $this->userModel->where('user_type', 'dentist')->where('status', 'active')->findAll(),
            'availability' => [] // For future implementation
        ];
    }
} 