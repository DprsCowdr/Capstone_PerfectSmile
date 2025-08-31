<?php

namespace App\Controllers;

use App\Controllers\Auth;

class AdminCalendarController extends BaseController
{
    protected $service;

    public function __construct()
    {
    // thin wrapper - appointment service is instantiated lazily when needed to avoid DB work during construction
    $this->service = null;
    }

    protected function ensureAdmin()
    {
        $user = Auth::getCurrentUser();
        if (!$user || ($user['user_type'] ?? '') !== 'admin') {
            return false;
        }
        return true;
    }

    public function dayAppointments()
    {
        if (!$this->ensureAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Forbidden'])->setStatusCode(403);
        }
    $appointmentsCtrl = new Appointments();
    return $appointmentsCtrl->dayAppointments();
    }

    public function availableSlots()
    {
        if (!$this->ensureAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Forbidden'])->setStatusCode(403);
        }
    $appointmentsCtrl = new Appointments();
    return $appointmentsCtrl->availableSlots();
    }

    public function checkConflicts()
    {
        if (!$this->ensureAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Forbidden'])->setStatusCode(403);
        }
    $appointmentsCtrl = new Appointments();
    return $appointmentsCtrl->checkConflicts();
    }
}
