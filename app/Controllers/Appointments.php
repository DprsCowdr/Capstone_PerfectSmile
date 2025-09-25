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
            $query = $appointmentModel->select('appointments.id, appointments.appointment_datetime, appointments.dentist_id, appointments.user_id, appointments.procedure_duration, appointments.status, appointments.approval_status, user.name as patient_name, dentists.name as dentist_name')
                                     ->join('user', 'user.id = appointments.user_id', 'left')
                                     ->join('user as dentists', 'dentists.id = appointments.dentist_id', 'left')
                                     ->where('DATE(appointments.appointment_datetime)', $date)
                                     ->where('appointments.approval_status', 'approved') // Only show approved appointments in timeline
                                     ->whereNotIn('appointments.status', ['cancelled', 'rejected']); // Exclude cancelled/rejected

            if ($branchId) {
                $query->where('appointments.branch_id', $branchId);
            }

            // Patient restriction: only show their own appointments
            if ($user['user_type'] === 'patient') {
                $query->where('appointments.user_id', $user['id']);
            }

            $results = $query->orderBy('appointments.appointment_datetime', 'ASC')->findAll();

            $out = [];
            // Collect appointment ids that need service-duration lookup
            $lookupIds = [];
            foreach ($results as $r) {
                if (empty($r['procedure_duration']) || $r['procedure_duration'] === null) {
                    $lookupIds[] = $r['id'];
                }
            }

            $serviceDurations = [];
            if (!empty($lookupIds)) {
                try {
                    $db = \Config\Database::connect();
                    $rows = $db->table('appointment_service')
                               ->select('appointment_service.appointment_id, services.duration_minutes, services.duration_max_minutes')
                               ->join('services', 'services.id = appointment_service.service_id', 'left')
                               ->whereIn('appointment_service.appointment_id', $lookupIds)
                               ->get()->getResultArray();
                    // aggregate per appointment, prefer max when present
                    $agg = [];
                    foreach ($rows as $row) {
                        $aid = $row['appointment_id'];
                        if (!isset($agg[$aid])) $agg[$aid] = ['sum_min' => 0, 'sum_max' => 0, 'has_max' => false];
                        $min = !empty($row['duration_minutes']) ? (int)$row['duration_minutes'] : 0;
                        $max = !empty($row['duration_max_minutes']) ? (int)$row['duration_max_minutes'] : 0;
                        $agg[$aid]['sum_min'] += $min;
                        if ($max > 0) { $agg[$aid]['sum_max'] += $max; $agg[$aid]['has_max'] = true; }
                    }
                    foreach ($agg as $aid => $vals) {
                        if ($vals['has_max'] && $vals['sum_max'] > 0) $serviceDurations[$aid] = $vals['sum_max'];
                        else $serviceDurations[$aid] = $vals['sum_min'];
                    }
                } catch (\Exception $e) {
                    // ignore lookup errors and leave serviceDurations empty
                }
            }

            foreach ($results as $r) {
                $start = $r['appointment_datetime'];
                $duration = 0;
                if (!empty($r['procedure_duration'])) {
                    $duration = (int)$r['procedure_duration'];
                } elseif (isset($serviceDurations[$r['id']])) {
                    $duration = (int)$serviceDurations[$r['id']];
                }
                $end = date('Y-m-d H:i:s', strtotime($start) + ($duration * 60));
                $out[] = [
                    'id' => $r['id'],
                    'start' => $start,
                    'end' => $end,
                    'duration_minutes' => $duration,
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
        log_message('debug', 'Appointments::availableSlots() called');

        // TEMPORARY DEBUG BYPASS: Allow unauthenticated localhost calls when __debug_noauth=1 is present.
        $allowDebug = false;
        try {
            $allowDebug = $this->request->getGet('__debug_noauth') && in_array($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1', ['127.0.0.1', '::1']);
        } catch (\Exception $e) { $allowDebug = false; }

        $user = Auth::getCurrentUser();
        if (!$user && !$allowDebug) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized (Appointments:110)'])->setStatusCode(401);
        }

        // Collect params from GET/POST/raw input and ensure branch_id is set
        $params = $this->request->getGet();
        if (empty($params)) $params = $this->request->getPost() ?? $this->request->getRawInput() ?? [];
        $params['branch_id'] = $this->resolveBranchId();

        $svc = new \App\Services\AvailabilityService();
        $result = $svc->getAvailableSlots($params);
        return $this->response->setJSON($result);
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
    // Do not accept client-supplied durations as authoritative. If provided, use it; otherwise prefer service durations.
    $requestedDuration = $this->request->getPost('duration') ?? null;
    $duration = ($requestedDuration !== null) ? (int)$requestedDuration : 0;
    // If a service_id is provided, prefer service.duration_max_minutes for conservative conflict detection
    $serviceId = $this->request->getPost('service_id') ?: null;
    if ($serviceId) {
        try {
            $svcModel = new \App\Models\ServiceModel();
            $svc = $svcModel->find($serviceId);
            if ($svc) {
                if (!empty($svc['duration_max_minutes'])) $duration = (int)$svc['duration_max_minutes'];
                elseif (!empty($svc['duration_minutes'])) $duration = (int)$svc['duration_minutes'];
            }
        } catch (\Exception $e) { /* ignore and use requested/default */ }
    }
    // Determine grace minutes: prefer explicit param, otherwise read writable grace_periods.json default
    $requestedGrace = $this->request->getPost('grace_minutes') ?? null;
    $grace = $requestedGrace !== null ? (int)$requestedGrace : 0;
    if ($grace === 0) {
        try {
            $gpPath = WRITEPATH . 'grace_periods.json';
            if (is_file($gpPath)) {
                $gp = json_decode(file_get_contents($gpPath), true);
                if (!empty($gp['default'])) $grace = (int)$gp['default'];
            }
        } catch (\Exception $e) {
            $grace = 15;
        }
    }
    // Do not force patients to a magic default here; server will compute duration from service when available.
    $branchId = $this->resolveBranchId();
        $dentistId = $this->request->getPost('dentist_id') ?: null;

        if (!$date || !$time) {
            return $this->response->setJSON(['success' => false, 'message' => 'Missing date or time'])->setStatusCode(400);
        }

        try {
            // Use SQL-based strict conflict detection where possible
            $requestedDatetime = $date . ' ' . $time . ':00';
            $appointmentModel = new \App\Models\AppointmentModel();
            // Use model's SQL-first conflict detection (isTimeConflictingWithGrace)
            $strictConflict = $appointmentModel->isTimeConflictingWithGrace($requestedDatetime, $grace, $dentistId, null, $duration);

            $availabilityConflicts = [];
            // Still check availability blocks for dentist (separately)
            if ($dentistId) {
                try {
                    $availModel = new \App\Models\AvailabilityModel();
                    $blocks = $availModel->getBlocksBetween($date . ' 00:00:00', $date . ' 23:59:59', $dentistId);
                    foreach ($blocks as $b) {
                        $s = strtotime($b['start_datetime']);
                        $e = strtotime($b['end_datetime']);
                        if (strtotime($requestedDatetime) < $e && (strtotime($requestedDatetime) + ($duration + $grace) * 60) > $s) {
                            $availabilityConflicts[] = ['type' => $b['type'], 'start' => date('g:i A', $s), 'end' => date('g:i A', $e), 'notes' => $b['notes'] ?? ''];
                        }
                    }
                } catch (\Exception $e) {
                    log_message('error', 'checkConflicts availability error: ' . $e->getMessage());
                }
            }

            $hasConflicts = $strictConflict || !empty($availabilityConflicts);

            $suggestions = [];
            if ($hasConflicts) {
                // Generate reschedule suggestions using AvailabilityService
                try {
                    $availabilityService = new \App\Services\AvailabilityService();
                    
                    $params = [
                        'date' => $date,
                        'branch_id' => $branchId,
                        'dentist_id' => $dentistId,
                        'duration' => $duration,
                        'granularity' => 5, // 5-minute increments for more options
                        'max_suggestions' => 10 // Limit suggestions to avoid overwhelming UI
                    ];

                    $availabilityResult = $availabilityService->getAvailableSlots($params);
                    
                    if ($availabilityResult['success'] && !empty($availabilityResult['suggestions'])) {
                        // Filter suggestions to only include available slots
                        $suggestions = array_filter($availabilityResult['suggestions'], function($suggestion) {
                            return $suggestion['available'] === true;
                        });

                        // Limit to first 10 suitable suggestions
                        $suggestions = array_slice($suggestions, 0, 10);
                    }
                } catch (\Exception $e) { 
                    log_message('warning', 'checkConflicts: AvailabilityService suggestion lookup failed: ' . $e->getMessage()); 
                    
                    // Fallback to original suggestion method
                    try {
                        $lookahead = 180;
                        $next = $appointmentModel->findNextAvailableSlot($date, $time, $grace, $lookahead, $dentistId, $duration);
                        if ($next) $suggestions[] = $next;
                    } catch (\Exception $e2) { 
                        log_message('warning', 'checkConflicts: fallback suggestion lookup failed: ' . $e2->getMessage()); 
                    }
                }

                // Return conflict response
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'Scheduling conflict detected',
                    'conflict' => true,
                    'conflicts' => $strictConflict ? ['conflict' => true] : [], 
                    'availability_conflicts' => $availabilityConflicts, 
                    'hasConflicts' => $hasConflicts, 
                    'suggestions' => $suggestions,
                    'metadata' => [
                        'requested_time' => $time,
                        'requested_date' => $date,
                        'duration_minutes' => $duration,
                        'grace_minutes' => $grace,
                        'alternative_count' => count($suggestions)
                    ]
                ]);
            }

            // No conflicts found
            return $this->response->setJSON([
                'success' => true, 
                'message' => 'No conflicts detected',
                'conflict' => false,
                'conflicts' => [], 
                'availability_conflicts' => $availabilityConflicts, 
                'hasConflicts' => $hasConflicts, 
                'suggestions' => []
            ]);
        } catch (\Exception $e) {
            log_message('error', 'checkConflicts error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Server error'])->setStatusCode(500);
        }
    }

    /**
     * Get operating hours for a branch
     * POST/GET params: branch_id
     */
    public function getOperatingHours()
    {
        $user = Auth::getCurrentUser();
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        $branchId = $this->resolveBranchId();
        if (!$branchId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Branch ID required'])->setStatusCode(400);
        }

        try {
            $db = \Config\Database::connect();
            $branch = $db->table('branches')->select('operating_hours, name')->where('id', $branchId)->get()->getRowArray();
            
            if (!$branch) {
                return $this->response->setJSON(['success' => false, 'message' => 'Branch not found'])->setStatusCode(404);
            }

            // Default fallback hours
            $defaultHours = [
                'monday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00'],
                'tuesday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00'],
                'wednesday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00'],
                'thursday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00'],
                'friday' => ['enabled' => true, 'open' => '08:00', 'close' => '20:00'],
                'saturday' => ['enabled' => false, 'open' => '09:00', 'close' => '15:00'],
                'sunday' => ['enabled' => false, 'open' => '09:00', 'close' => '15:00']
            ];

            $operatingHours = $defaultHours;
            if (!empty($branch['operating_hours'])) {
                $saved = json_decode($branch['operating_hours'], true);
                if (is_array($saved)) {
                    // Merge saved hours with defaults to ensure all days are present
                    foreach ($saved as $day => $hours) {
                        if (isset($defaultHours[$day])) {
                            $operatingHours[$day] = array_merge($defaultHours[$day], $hours);
                        }
                    }
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'branch_id' => $branchId,
                'branch_name' => $branch['name'],
                'operating_hours' => $operatingHours
            ]);

        } catch (\Exception $e) {
            log_message('error', 'getOperatingHours error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Server error'])->setStatusCode(500);
        }
    }
}