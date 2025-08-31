<?php

namespace App\Controllers;

use App\Controllers\Auth;

class DentistCalendarController extends BaseController
{
    protected $service;

    public function __construct()
    {
    $this->service = new \App\Services\AppointmentService();
    }

    protected function ensureDentist()
    {
        $user = Auth::getCurrentUser();
        if (!$user) return false;
        $t = $user['user_type'] ?? '';
        return in_array($t, ['dentist', 'admin']);
    }

    public function dayAppointments()
    {
        if (!$this->ensureDentist()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Forbidden'])->setStatusCode(403);
        }
    $appointmentsCtrl = new Appointments();
    return $appointmentsCtrl->dayAppointments();
    }

    public function availableSlots()
    {
        if (!$this->ensureDentist()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Forbidden'])->setStatusCode(403);
        }
    $appointmentsCtrl = new Appointments();
    return $appointmentsCtrl->availableSlots();
    }

    public function checkConflicts()
    {
        if (!$this->ensureDentist()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Forbidden'])->setStatusCode(403);
        }
    $appointmentsCtrl = new Appointments();
    return $appointmentsCtrl->checkConflicts();
    }
}
