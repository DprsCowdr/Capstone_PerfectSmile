<?php

namespace App\Controllers;

use App\Services\AuthService;

class StaffController extends BaseAdminController
{
    protected function getAuthenticatedUser()
    {
        return AuthService::checkStaffAuth();
    }
    
    protected function getAuthenticatedUserApi()
    {
        return AuthService::checkStaffAuthApi();
    }

    // ==================== DASHBOARD ====================
    public function dashboard()
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        // Get dashboard data
        $appointmentData = $this->appointmentService->getDashboardData();
        $userStats = $this->userService->getUserStatistics();
        
        // Get branch count
        $branchModel = new \App\Models\BranchModel();
        $totalBranches = $branchModel->countAll();
        
        // Get recent patients
        $recentPatients = $this->userService->getRecentPatients(5);
        
        return view('staff/dashboard', [
            'user' => $user,
            'pendingAppointments' => $appointmentData['pendingAppointments'],
            'todayAppointments' => $appointmentData['todayAppointments'],
            'totalPatients' => $userStats['total_patients'],
            'totalDentists' => $userStats['total_dentists'],
            'totalBranches' => $totalBranches,
            'recentPatients' => $recentPatients
        ]);
    }

    // ==================== PATIENT MANAGEMENT ====================
    public function patients()
    {
        return $this->getPatientsView('staff/patients');
    }

    public function addPatient()
    {
        return $this->getAddPatientView('staff/addPatient');
    }

    public function storePatient()
    {
        return $this->storePatientLogic('/staff/patients');
    }

    public function getPatient($id)
    {
        return $this->getPatientApi($id);
    }

    public function updatePatient($id)
    {
        return $this->updatePatientLogic($id, '/staff/patients');
    }

    public function toggleStatus($id)
    {
        return $this->togglePatientStatusLogic($id, '/staff/patients');
    }

    // ==================== APPOINTMENT MANAGEMENT ====================
    public function appointments()
    {
        return $this->getAppointmentsView('staff/appointments');
    }

    public function createAppointment()
    {
        return $this->createAppointmentLogic('/staff/appointments', 'staff');
    }
}
