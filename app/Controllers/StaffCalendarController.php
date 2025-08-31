<?php

namespace App\Controllers;

use App\Controllers\Auth;

class StaffCalendarController extends BaseController
{
    protected $service;

    public function __construct()
    {
    // Lazy instantiate to avoid database access during unit test construction
    $this->service = null;
    }

    protected function ensureStaff()
    {
        $user = Auth::getCurrentUser();
        if (!$user) return false;
        $t = $user['user_type'] ?? '';
        return in_array($t, ['staff', 'admin']); // admin also allowed
    }

    public function dayAppointments()
    {
        if (!$this->ensureStaff()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Forbidden'])->setStatusCode(403);
        }
        $appointmentsCtrl = new Appointments();
        // Ensure the delegated controller has the same request/response context as this controller
        if (method_exists($appointmentsCtrl, 'initController')) {
            $appointmentsCtrl->initController($this->request, $this->response, service('logger'));
        }
        return $appointmentsCtrl->dayAppointments();
    }

    public function availableSlots()
    {
        if (!$this->ensureStaff()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Forbidden'])->setStatusCode(403);
        }
        $appointmentsCtrl = new Appointments();
        if (method_exists($appointmentsCtrl, 'initController')) {
            $appointmentsCtrl->initController($this->request, $this->response, service('logger'));
        }
        return $appointmentsCtrl->availableSlots();
    }

    public function checkConflicts()
    {
        if (!$this->ensureStaff()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Forbidden'])->setStatusCode(403);
        }
        $appointmentsCtrl = new Appointments();
        if (method_exists($appointmentsCtrl, 'initController')) {
            $appointmentsCtrl->initController($this->request, $this->response, service('logger'));
        }
        return $appointmentsCtrl->checkConflicts();
    }
}
