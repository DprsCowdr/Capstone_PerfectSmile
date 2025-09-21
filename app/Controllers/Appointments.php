<?php

namespace App\Controllers;

use App\Controllers\Auth;
use CodeIgniter\Controller;

class Appointments extends BaseController
{
    /**
     * Return appointments for a given branch and date
     * POST params: branch_id, date (Y-m-d)
     */
    public function dayAppointments()
    {
        $user = Auth::getCurrentUser();
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
        }

    // Resolve branch id via BaseController helper (POST/GET/JSON/session)
    $branchId = $this->resolveBranchId();
    $date = $this->request->getPost('date') ?? $this->request->getGet('date') ?? date('Y-m-d');

        try {
            $appointmentModel = new \App\Models\AppointmentModel();
            $query = $appointmentModel->select('appointments.id, appointments.appointment_datetime, appointments.dentist_id, appointments.user_id, appointments.procedure_duration, user.name as patient_name, dentists.name as dentist_name')
                                     ->join('user', 'user.id = appointments.user_id', 'left')
                                     ->join('user as dentists', 'dentists.id = appointments.dentist_id', 'left')
                                     ->where('DATE(appointments.appointment_datetime)', $date);

            if ($branchId) {
                $query->where('appointments.branch_id', $branchId);
            }

            $results = $query->orderBy('appointments.appointment_datetime', 'ASC')->findAll();

            $out = [];
            foreach ($results as $r) {
                $start = $r['appointment_datetime'];
                $duration = isset($r['procedure_duration']) && $r['procedure_duration'] ? (int)$r['procedure_duration'] : 30;
                $end = date('Y-m-d H:i:s', strtotime($start) + ($duration * 60));
                $out[] = [
                    'id' => $r['id'],
                    'start' => $start,
                    'end' => $end,
                    'procedure_duration' => $duration,
                    'patient_name' => $r['patient_name'] ?? null,
                    'dentist_name' => $r['dentist_name'] ?? null,
                    'dentist_id' => $r['dentist_id'] ?? null,
                ];
            }

            return $this->response->setJSON(['success' => true, 'appointments' => $out]);
        } catch (\Exception $e) {
            log_message('error', 'dayAppointments error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Server error'])->setStatusCode(500);
        }
    }

    /**
     * Return available slots for a branch/date considering existing appointments
     * POST params: branch_id, date (Y-m-d), duration (minutes), dentist_id (optional)
     */
    public function availableSlots()
    {
        $user = Auth::getCurrentUser();
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
        }

    $branchId = $this->resolveBranchId();
    $date = $this->request->getPost('date') ?? $this->request->getGet('date') ?? date('Y-m-d');
        $duration = (int) ($this->request->getPost('duration') ?? 30);
        $dentistId = $this->request->getPost('dentist_id') ?: null;

        try {
            $appointmentModel = new \App\Models\AppointmentModel();
            $existing = $appointmentModel->select('appointment_datetime, procedure_duration')->where('DATE(appointment_datetime)', $date);
            if ($branchId) $existing->where('branch_id', $branchId);
            if ($dentistId) $existing->where('dentist_id', $dentistId);
            $existing = $existing->findAll();

            // Build occupied intervals
            $occupied = [];
            foreach ($existing as $e) {
                $start = strtotime($e['appointment_datetime']);
                $dur = isset($e['procedure_duration']) && $e['procedure_duration'] ? (int)$e['procedure_duration'] : 30;
                $end = $start + ($dur * 60);
                $occupied[] = [$start, $end];
            }

            // Working hours: 08:00 to 17:00
            $dayStart = strtotime($date . ' 08:00:00');
            $dayEnd = strtotime($date . ' 17:00:00');

            $step = 15 * 60; // 15 minutes
            $slots = [];
            for ($t = $dayStart; $t + ($duration * 60) <= $dayEnd; $t += $step) {
                $slotStart = $t;
                $slotEnd = $t + ($duration * 60);

                // check overlap with occupied
                $ok = true;
                foreach ($occupied as $occ) {
                    if ($slotStart < $occ[1] && $slotEnd > $occ[0]) {
                        $ok = false;
                        break;
                    }
                }
                if ($ok) {
                    if ($dentistId) {
                        $slots[] = ['time' => date('H:i', $slotStart), 'dentist_id' => (int)$dentistId];
                    } else {
                        $slots[] = date('H:i', $slotStart);
                    }
                }
                if (count($slots) >= 50) break; // limit
            }

            return $this->response->setJSON(['success' => true, 'slots' => $slots]);
        } catch (\Exception $e) {
            log_message('error', 'availableSlots error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Server error'])->setStatusCode(500);
        }
    }

    /**
     * Check conflicts for a requested appointment
     * POST params: date, time, duration, branch_id, dentist_id
     */
    public function checkConflicts()
    {
        $user = Auth::getCurrentUser();
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
        }

    $date = $this->request->getPost('date');
    $time = $this->request->getPost('time');
    $duration = (int) ($this->request->getPost('duration') ?? 30);
    $branchId = $this->resolveBranchId();
        $dentistId = $this->request->getPost('dentist_id') ?: null;

        if (!$date || !$time) {
            return $this->response->setJSON(['success' => false, 'message' => 'Missing date or time'])->setStatusCode(400);
        }

        try {
            $start = strtotime($date . ' ' . $time . ':00');
            $end = $start + ($duration * 60);

            $db = \Config\Database::connect();
            $builder = $db->table('appointments');
            $builder->select('appointments.id, appointments.appointment_datetime, appointments.procedure_duration, user.name as patient_name')
                    ->join('user', 'user.id = appointments.user_id', 'left')
                    ->where('DATE(appointments.appointment_datetime)', $date)
                    ->where('appointments.status !=', 'cancelled');
            if ($branchId) $builder->where('appointments.branch_id', $branchId);
            if ($dentistId) $builder->where('appointments.dentist_id', $dentistId);

            $rows = $builder->get()->getResultArray();
            $conflicts = [];
            foreach ($rows as $r) {
                $s = strtotime($r['appointment_datetime']);
                $dur = isset($r['procedure_duration']) && $r['procedure_duration'] ? (int)$r['procedure_duration'] : 30;
                $e = $s + ($dur * 60);
                if ($start < $e && $end > $s) {
                    $conflicts[] = [
                        'id' => $r['id'],
                        'patient_name' => $r['patient_name'] ?? null,
                        'start' => date('H:i', $s),
                        'end' => date('H:i', $e),
                        'overlap_minutes' => ceil(min($end, $e) - max($start, $s))/60
                    ];
                }
            }

            return $this->response->setJSON(['success' => true, 'conflicts' => $conflicts, 'hasConflicts' => count($conflicts) > 0]);
        } catch (\Exception $e) {
            log_message('error', 'checkConflicts error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Server error'])->setStatusCode(500);
        }
    }
}
