<?php

namespace App\Controllers;


use App\Services\DashboardService;
use App\Services\AppointmentService;
use App\Traits\AdminAuthTrait;

class Admin extends BaseController
{
    use AdminAuthTrait;

    protected $appointmentService;
    protected $dashboardService;

    public function __construct()
    {
        $this->appointmentService = new AppointmentService();
        $this->dashboardService = new DashboardService();
    }

    // ==================== REDIRECT TO NEW CONTROLLERS ====================
    
    /**
     * Redirect to new AdminController
     */
    public function dashboard()
    {
        return redirect()->to('/admin/dashboard');
    }

    /**
     * Redirect to new AdminController
     */
    public function patients()
    {
        return redirect()->to('/admin/patients');
    }

    /**
     * Redirect to new AdminController
     */
    public function appointments()
    {
        return redirect()->to('/admin/appointments');
    }

    /**
     * Redirect to new DentalController
     */
    public function dentalRecords()
    {
        return redirect()->to('/admin/dental-records');
    }

    /**
     * Redirect to new DentalController
     */
    public function dentalCharts()
    {
        return redirect()->to('/admin/dental-charts');
    }

    /**
     * Redirect to new AdminController
     */
    public function services()
    {
        return redirect()->to('/admin/services');
    }

    /**
     * Redirect to new AdminController
     */
    public function waitlist()
    {
        return redirect()->to('/admin/waitlist');
    }

    /**
     * Legacy methods - keeping minimal functionality for backward compatibility
     */
    
    public function getPatient($id)
    {
        $userService = new \App\Services\UserService();
        $patient = $userService->getPatient($id);
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON($patient ?: ['error' => 'Patient not found']);
        }
        
        return redirect()->to('/admin/patients');
    }

    public function createAppointment()
    {
        $adminController = new AdminController();
        return $adminController->createAppointment();
    }

    public function approveAppointment($id)
    {
        $adminController = new AdminController();
        return $adminController->approveAppointment($id);
    }

    public function declineAppointment($id)
    {
        $adminController = new AdminController();
        return $adminController->declineAppointment($id);
    }
}
