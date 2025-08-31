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

        $branchId = $this->request->getPost('branch_id');
        $date = $this->request->getPost('date') ?? date('Y-m-d');

        try {
            $appointmentModel = new \App\Models\AppointmentModel();
            // Include service name/procedure where available so clients can label booked slots
            $query = $appointmentModel->select('appointments.id, appointments.appointment_datetime, appointments.dentist_id, appointments.user_id, appointments.procedure_duration, appointments.service_id, user.name as patient_name, dentists.name as dentist_name, services.name as service_name')
                                     ->join('user', 'user.id = appointments.user_id', 'left')
                                     ->join('user as dentists', 'dentists.id = appointments.dentist_id', 'left')
                                     ->join('services', 'services.id = appointments.service_id', 'left')
                                     ->where('DATE(appointments.appointment_datetime)', $date);

            if ($branchId) {
                $query->where('appointments.branch_id', $branchId);
            }

            $results = $query->orderBy('appointments.appointment_datetime', 'ASC')->findAll();

            $out = [];
            $currentUserId = $user['id'] ?? null;
            $currentUserType = $user['user_type'] ?? null;
            foreach ($results as $r) {
                $start = $r['appointment_datetime'];
                $duration = isset($r['procedure_duration']) && $r['procedure_duration'] ? (int)$r['procedure_duration'] : 30;
                $end = date('Y-m-d H:i:s', strtotime($start) + ($duration * 60));
                $isOwner = ($currentUserId && isset($r['user_id']) && $r['user_id'] == $currentUserId);

                // For patient sessions, do not expose other patients' identifying info
                $patientNameOut = $r['patient_name'] ?? null;
                $userIdOut = $r['user_id'] ?? null;
                if ($currentUserType === 'patient' && !$isOwner) {
                    $patientNameOut = null;
                    $userIdOut = null;
                }

                // Prefer service_name/procedure labels when available
                $procLabel = $r['service_name'] ?? ($r['procedure_name'] ?? ($r['procedure'] ?? null));

                $out[] = [
                    'id' => $r['id'],
                    'start' => $start,
                    'end' => $end,
                    'procedure_duration' => $duration,
                    'patient_name' => $patientNameOut,
                    'dentist_name' => $r['dentist_name'] ?? null,
                    'dentist_id' => $r['dentist_id'] ?? null,
                    'user_id' => $userIdOut,
                    'procedure' => $procLabel,
                    'is_owner' => $isOwner,
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

        $branchId = $this->request->getPost('branch_id');
        $date = $this->request->getPost('date') ?? date('Y-m-d');
    $duration = (int) ($this->request->getPost('duration') ?? 30);
    $dentistId = $this->request->getPost('dentist_id') ?: null;
    // Granularity defines the step (in minutes) used to compute possible starting slots
    // e.g., 30 => starting slots every 30 minutes. Default 30.
    $granularity = (int) ($this->request->getPost('granularity') ?? 30);

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

            // Working hours: try to use branch-specific start_time/end_time if present, else default 08:00-20:00
            $dayStartStr = '08:00:00';
            $dayEndStr = '20:00:00';
            if ($branchId) {
                // Attempt to read start_time/end_time columns from branches. If columns don't exist, the DB will throw; catch and ignore.
                try {
                    $db = \Config\Database::connect();
                    $b = $db->table('branches')->select('start_time, end_time')->where('id', $branchId)->get()->getRowArray();
                    if ($b) {
                        if (!empty($b['start_time'])) $dayStartStr = $b['start_time'];
                        if (!empty($b['end_time'])) $dayEndStr = $b['end_time'];
                    }
                } catch (\Exception $ex) {
                    // Branch table has no start_time/end_time columns or query failed; fallback to defaults.
                }
            }
            $dayStart = strtotime($date . ' ' . $dayStartStr);
            $dayEnd = strtotime($date . ' ' . $dayEndStr);

            // Step/granularity in seconds
            $step = max(1, $granularity) * 60;
            $slots = [];
            // Compute total possible starting slots for the day given granularity
            $totalPossibleStarting = (int) floor(($dayEnd - $dayStart) / $step);
            // Iterate each possible start time and check if the requested duration fits
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
                if (count($slots) >= 500) break; // larger limit
            }

            // remaining starting slots is the number of slots that fit the requested duration
            $remainingStarting = count($slots);

            return $this->response->setJSON([
                'success' => true,
                'slots' => $slots,
                'total_possible_starting_slots' => $totalPossibleStarting,
                'remaining_starting_slots' => $remainingStarting,
                'granularity' => $granularity,
            ]);
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
        $branchId = $this->request->getPost('branch_id') ?: null;
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
            $sessionUserId = $user['id'] ?? null;
            $sessionUserType = $user['user_type'] ?? null;
            foreach ($rows as $r) {
                $s = strtotime($r['appointment_datetime']);
                $dur = isset($r['procedure_duration']) && $r['procedure_duration'] ? (int)$r['procedure_duration'] : 30;
                $e = $s + ($dur * 60);
                if ($start < $e && $end > $s) {
                    $isOwner = ($sessionUserId && isset($r['user_id']) && $r['user_id'] == $sessionUserId);
                    $pname = $r['patient_name'] ?? null;
                    if ($sessionUserType === 'patient' && !$isOwner) {
                        $pname = null; // hide patient names from patients
                    }
                    $conflicts[] = [
                        'id' => $r['id'],
                        'patient_name' => $pname,
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
