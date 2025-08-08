<?php

namespace App\Controllers;

use App\Services\DashboardService;
use App\Services\AppointmentService;
use App\Traits\AdminAuthTrait;

/**
 * DEPRECATED: This is the old Admin controller
 * 
 * This file is kept for reference only.
 * The functionality has been split into:
 * - AdminController.php (admin-specific operations)
 * - DentalController.php (dental records and charts)
 * - BaseAdminController.php (shared functionality)
 * 
 * Routes now point to the new controllers.
 * This file can be deleted once testing is complete.
 */
class AdminOld extends BaseController
{
    use AdminAuthTrait;

    protected $appointmentService;
    protected $dashboardService;

    public function __construct()
    {
        $this->appointmentService = new AppointmentService();
        $this->dashboardService = new DashboardService();
    }

    /**
     * DEPRECATED: Use AdminController::dashboard() instead
     */
    public function dashboard()
    {
        return redirect()->to('/admin/dashboard');
    }

    /**
     * DEPRECATED: Use AdminController methods instead
     */
    public function patients()
    {
        return redirect()->to('/admin/patients');
    }

    /**
     * DEPRECATED: Use AdminController methods instead
     */
    public function appointments()
    {
        return redirect()->to('/admin/appointments');
    }

    /**
     * DEPRECATED: Use DentalController methods instead
     */
    public function dentalRecords()
    {
        return redirect()->to('/admin/dental-records');
    }

    /**
     * DEPRECATED: Use DentalController methods instead
     */
    public function dentalCharts()
    {
        return redirect()->to('/admin/dental-charts');
    }
}
