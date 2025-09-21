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
        // This is for local testing only and should be removed before deploying to production.
        $allowDebug = false;
        try {
            $allowDebug = $this->request->getGet('__debug_noauth') && in_array($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1', ['127.0.0.1', '::1']);
        } catch (\Exception $e) {
            $allowDebug = false;
        }

        $user = Auth::getCurrentUser();
        if (!$user && !$allowDebug) {
            log_message('debug', 'availableSlots: No authenticated user found');
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized (Appointments:110)'])->setStatusCode(401);
        }

        if ($allowDebug) {
            log_message('debug', 'availableSlots: DEBUG bypass enabled (no auth) from ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        } else {
            log_message('debug', 'availableSlots: User authenticated: ' . json_encode(['id' => $user['id'], 'type' => $user['user_type']]));
        }

    $branchId = $this->resolveBranchId();
    $date = $this->request->getPost('date') ?? $this->request->getGet('date') ?? date('Y-m-d');
    // Default duration; patients should not be able to override duration used for slot calculations
    $requestedDuration = $this->request->getPost('duration') ?? null;
    // No magic 30-minute fallback: if no duration is provided it will be treated as 0 (or computed from service_id)
    $duration = ($requestedDuration !== null) ? (int)$requestedDuration : 0;
    // If service_id provided, compute duration from service server-side (authoritative)
    // Prefer duration_max_minutes when present (conservative) otherwise duration_minutes
    $serviceId = $this->request->getPost('service_id') ?: null;
    if ($serviceId) {
        try {
            $svcModel = new \App\Models\ServiceModel();
            $svc = $svcModel->find($serviceId);
            if ($svc) {
                if (!empty($svc['duration_max_minutes'])) {
                    $duration = (int)$svc['duration_max_minutes'];
                } elseif (!empty($svc['duration_minutes'])) {
                    $duration = (int)$svc['duration_minutes'];
                }
            }
        } catch (\Exception $e) {
            // ignore and leave duration as-is (no magic fallback)
        }
    }
    
    // Do NOT apply a silent fallback duration here. Duration must come from service or explicit request.
    // If no duration is available at this point, return no slots so callers must supply/choose a service.
    if ($duration <= 0) {
        log_message('debug', "availableSlots: no duration available (serviceId={$serviceId}), returning no slots");
        return $this->response->setJSON(['success' => true, 'slots' => [], 'available_slots' => [], 'unavailable_slots' => [], 'metadata' => ['total_slots_checked' => 0, 'available_count' => 0, 'unavailable_count' => 0]]);
    }

    log_message('debug', "availableSlots: serviceId={$serviceId}, final duration={$duration}min");
    
        $dentistId = $this->request->getPost('dentist_id') ?: null;

        try {
            $appointmentModel = new \App\Models\AppointmentModel();
            // Use SQL-aggregated occupied intervals to avoid per-appointment service lookups
            $occupied = $appointmentModel->getOccupiedIntervals($date, $branchId, $dentistId);
            log_message('debug', "availableSlots: Found " . count($occupied) . " occupied intervals for date {$date}");

            // If dentist specified, load availability blocks and mark as occupied
            $availabilityBlocks = [];
            if ($dentistId) {
                try {
                    $availModel = new \App\Models\AvailabilityModel();
                    $blocks = $availModel->getBlocksBetween($date . ' 00:00:00', $date . ' 23:59:59', $dentistId);
                    foreach ($blocks as $b) {
                        $s = strtotime($b['start_datetime']);
                        $e = strtotime($b['end_datetime']);
                        $availabilityBlocks[] = [$s, $e];
                    }
                } catch (\Exception $e) {
                    log_message('error', 'availableSlots load availability error: ' . $e->getMessage());
                }
            }

            // Determine working hours from branch.operating_hours if available (safe fallback to 08:00-21:00 for evening coverage)
            $dayStart = strtotime($date . ' 08:00:00');
            $dayEnd = strtotime($date . ' 20:00:00'); // Default operating hours: 08:00-20:00
            try {
                if ($branchId) {
                    $db = \Config\Database::connect();
                    $b = $db->table('branches')->select('operating_hours')->where('id', $branchId)->get()->getRowArray();
                    if ($b && !empty($b['operating_hours'])) {
                        $oh = json_decode($b['operating_hours'], true);
                                    if (is_array($oh)) {
                                        $weekday = strtolower(date('l', strtotime($date)));
                                        if (isset($oh[$weekday]) && isset($oh[$weekday]['enabled']) && $oh[$weekday]['enabled']) {
                                            $open = isset($oh[$weekday]['open']) ? $oh[$weekday]['open'] : '08:00';
                                            $close = isset($oh[$weekday]['close']) ? $oh[$weekday]['close'] : '20:00';
                                            // Validate HH:MM format
                                            if (preg_match('/^([01]?\\d|2[0-3]):[0-5]\\d$/', $open) && preg_match('/^([01]?\\d|2[0-3]):[0-5]\\d$/', $close)) {
                                                // Heuristic: if close is an early-hour like '1:26' and open is morning (08:00+),
                                                // it's likely stored without AM/PM and should be PM. Adjust accordingly.
                                                $openParts = explode(':', $open);
                                                $closeParts = explode(':', $close);
                                                $openHour = (int)$openParts[0];
                                                $closeHour = (int)$closeParts[0];
                                                $closeMin = isset($closeParts[1]) ? (int)$closeParts[1] : 0;
                                                if ($closeHour < 8 && $openHour >= 8) {
                                                    // Interpret as PM
                                                    log_message('info', "availableSlots: interpreting branch close time '{$close}' as PM because open={$open}");
                                                    $closeHour += 12;
                                                    $close = str_pad((string)$closeHour, 2, '0', STR_PAD_LEFT) . ':' . str_pad((string)$closeMin, 2, '0', STR_PAD_LEFT);
                                                }
                                                $dayStart = strtotime($date . ' ' . $open . ':00');
                                                $dayEnd = strtotime($date . ' ' . $close . ':00');
                                                    // guard: ensure dayEnd is not before dayStart
                                                    if ($dayEnd <= $dayStart) {
                                                        log_message('warning', "availableSlots: branch operating_hours close <= open ({$open} <= {$close}), falling back to 20:00");
                                                        $dayEnd = strtotime($date . ' 20:00:00');
                                                    }
                                                    // Enforce a sensible clinic window: do not start before 08:00 and ensure at least a 20:00 end
                                                    $minDayStart = strtotime($date . ' 08:00:00');
                                                    $minDayEnd = strtotime($date . ' 20:00:00');
                                                    // Do not allow dayStart before 08:00
                                                    if ($dayStart < $minDayStart) {
                                                        log_message('info', "availableSlots: branch open before 08:00 ({$open}), clamping to 08:00");
                                                        $dayStart = $minDayStart;
                                                    }
                                                    // Ensure dayEnd is at least 20:00 so early-closing data doesn't truncate availability
                                                    if ($dayEnd < $minDayEnd) {
                                                        log_message('info', "availableSlots: branch close before 20:00 ({$close}), clamping to 20:00");
                                                        $dayEnd = $minDayEnd;
                                                    }
                                            }
                                        } else {
                                            // If branch closed that weekday, make no slots
                                            return $this->response->setJSON(['success' => true, 'slots' => []]);
                                        }
                                    }
                    }
                }
            } catch (\Exception $e) {
                // If anything goes wrong, keep defaults and log
                log_message('warning', 'availableSlots failed to read branch operating_hours: ' . $e->getMessage());
            }

            // determine grace minutes to reserve after appointment start so staff have buffer
            $grace = 0;
            try {
                $gpPath = WRITEPATH . 'grace_periods.json';
                if (is_file($gpPath)) {
                    $gp = json_decode(file_get_contents($gpPath), true);
                    if (!empty($gp['default'])) $grace = (int)$gp['default'];
                }
            } catch (\Exception $e) { $grace = 0; }
            
            log_message('debug', "availableSlots: grace period={$grace}min, operating hours=" . date('H:i', $dayStart) . "-" . date('H:i', $dayEnd));
            
            // Prepare occupied map so frontend can mark which intervals are owned by current user
            $occupied_map = [];
            foreach ($occupied as $occ) {
                $entry = [
                    'start' => date('Y-m-d H:i:s', $occ[0]), 
                    'end' => date('Y-m-d H:i:s', $occ[1]), 
                    'appointment_id' => $occ[2] ?? null,
                    'user_id' => $occ[3] ?? null
                ];
                // Mark ownership for current user
                try {
                    $sessionUser = session('user_id');
                    $entry['owned_by_current_user'] = ($sessionUser && $sessionUser == $entry['user_id']);
                } catch (\Exception $e) { 
                    $entry['owned_by_current_user'] = false; 
                }
                $occupied_map[] = $entry;
            }

            $slots = [];
            $currentUser = null;
            try { $currentUser = session('user_id'); } catch (\Exception $e) {}

            // Use block-based slots but also allow candidate starts at leftover-minute boundaries
            // (for example, when a previous appointment ends at 08:35 we want to consider 08:35 as a start)
            $blockSeconds = ($duration + $grace) * 60;
            if ($blockSeconds <= 0) {
                return $this->response->setJSON(['success' => true, 'slots' => [], 'available_slots' => [], 'unavailable_slots' => [], 'metadata' => ['total_slots_checked' => 0, 'available_count' => 0, 'unavailable_count' => 0]]);
            }

            // Handle granularity: allow caller to request snapping to a minute granularity.
            // Default granularity is 5 minutes (sensible clinic default).
            $granularity = (int)($this->request->getPost('granularity') ?? 5);
            // include 3-minute granularity option as requested previously
            $allowed = [1,3,5,10,15];
            if (!in_array($granularity, $allowed)) $granularity = 5;

            // helper to round a timestamp to the requested minute granularity.
            // By default we round to the nearest granularity (reduces gap rounding); caller can still request different granularity.
            $roundToGranularity = function($ts, $gran, $mode = 'nearest') {
                // anchor at midnight for the same date as $ts
                $midnight = strtotime(date('Y-m-d', $ts) . ' 00:00:00');
                $minutesSinceMid = ($ts - $midnight) / 60.0;
                if ($mode === 'nearest') {
                    $roundedMinutes = (int)round($minutesSinceMid / $gran) * $gran;
                } else {
                    // fallback to round-up behaviour
                    $roundedMinutes = (int)ceil($minutesSinceMid / $gran) * $gran;
                }
                return $midnight + ($roundedMinutes * 60);
            };

            // Build a set of candidate start timestamps. Include the regular grid (rounded to granularity)
            // and also include the end times of existing occupied appointments so leftover-minute starts are considered.
            // Track which candidates were derived directly from appointment end-times for debugging/verification.
            $candidates = [];
            $endDerivedCandidates = [];
            // start grid at dayStart rounded to the preferred granularity (nearest), but ensure we do not start before real dayStart
            $gridStart = $roundToGranularity($dayStart, $granularity, 'nearest');
            if ($gridStart < $dayStart) {
                // if nearest rounding pushed before business open, bump forward to the next snapped-up time
                $gridStart = $roundToGranularity($dayStart, $granularity, 'up');
            }
            // Step the grid by the requested granularity (in seconds). Using blockSeconds here
            // previously caused very sparse candidates for long durations. Use granularity so
            // candidate start times slide across the day and produce more options.
            $step = $granularity * 60;
            for ($t = $gridStart; $t + $blockSeconds <= $dayEnd; $t += $step) {
                $candidates[] = $t;
            }
            foreach ($occupied as $occ) {
                // $occ is [start, end, id, user_id]
                $endTs = (int)$occ[1];
                // include the exact end timestamp as a candidate so leftover-minute starts are considered verbatim
                if ($endTs >= $dayStart && ($endTs + $blockSeconds) <= $dayEnd) {
                    $candidates[] = $endTs;
                    $endDerivedCandidates[] = $endTs;
                }
                // also include a snapped-up variant to honor requested granularity, but don't drop the exact end
                $snapped = $roundToGranularity($endTs, $granularity, 'nearest');
                if ($snapped >= $dayStart && ($snapped + $blockSeconds) <= $dayEnd) {
                    $candidates[] = $snapped;
                    // mark snapped candidates differently for debugging clarity
                    if ($snapped !== $endTs) $endDerivedCandidates[] = $snapped;
                }
            }

            // Deduplicate and sort candidates
            $candidates = array_values(array_unique($candidates));
            sort($candidates);

            // Log end-derived candidate timestamps to help smoke tests verify leftover-minute behavior
            if (!empty($endDerivedCandidates)) {
                $uniqueEnds = array_values(array_unique($endDerivedCandidates));
                sort($uniqueEnds);
                $readable = array_map(function($ts){ return date('H:i', $ts); }, $uniqueEnds);
                log_message('debug', 'availableSlots: end-derived candidates = ' . implode(',', $readable));
            }

            // Determine number of active dentists in the branch when dentist isn't specified.
            $dentistsCount = 1;
            if (!$dentistId) {
                try {
                    $db = \Config\Database::connect();
                    $cnt = $db->table('user')
                              ->join('branch_staff', 'branch_staff.user_id = user.id')
                              ->where('user.user_type', 'dentist')
                              ->where('user.status', 'active');
                    if ($branchId) $cnt->where('branch_staff.branch_id', $branchId);
                    $dentistsCount = (int)$cnt->countAllResults();
                    if ($dentistsCount < 1) $dentistsCount = 1;
                } catch (\Exception $e) {
                    $dentistsCount = 1;
                }
            }

            foreach ($candidates as $slotStart) {
                if ($slotStart + $blockSeconds > $dayEnd) continue;

                $slotEnd = $slotStart + $blockSeconds;

                // check overlap with occupied and collect blocking details
                $isAvailable = true;
                $blockingInfo = null;
                $ownedByCurrentUser = false;

                if ($dentistId) {
                    // When a dentist is specified, any overlap with that dentist blocks the slot
                    foreach ($occupied as $occ) {
                        if ($slotStart < $occ[1] && $slotEnd > $occ[0]) {
                            $isAvailable = false;
                            // Collect info about what's blocking this slot
                            $blockingInfo = [
                                'type' => 'appointment',
                                'start' => date('g:i A', $occ[0]),
                                'end' => date('g:i A', $occ[1]),
                                'appointment_id' => $occ[2] ?? null
                            ];
                            // Check if blocked by current user's own appointment
                            if (!empty($occ[3]) && $currentUser && $occ[3] == $currentUser) {
                                $ownedByCurrentUser = true;
                                $blockingInfo['owned_by_current_user'] = true;
                            }
                            break;
                        }
                    }
                } else {
                    // No specific dentist: count overlapping appointments for this slot.
                    // If overlapping appointments >= number of active dentists in branch, block the slot.
                    $overlaps = 0;
                    $lastBlocking = null;
                    foreach ($occupied as $occ) {
                        if ($slotStart < $occ[1] && $slotEnd > $occ[0]) {
                            $overlaps++;
                            $lastBlocking = $occ;
                        }
                    }
                    if ($overlaps >= $dentistsCount) {
                        $isAvailable = false;
                        if ($lastBlocking) {
                            $blockingInfo = [
                                'type' => 'appointment',
                                'start' => date('g:i A', $lastBlocking[0]),
                                'end' => date('g:i A', $lastBlocking[1]),
                                'appointment_id' => $lastBlocking[2] ?? null
                            ];
                            if (!empty($lastBlocking[3]) && $currentUser && $lastBlocking[3] == $currentUser) {
                                $ownedByCurrentUser = true;
                                $blockingInfo['owned_by_current_user'] = true;
                            }
                        }
                    }
                }

                // check overlap with availability blocks if any
                if ($isAvailable && !empty($availabilityBlocks)) {
                    foreach ($availabilityBlocks as $occ) {
                        if ($slotStart < $occ[1] && $slotEnd > $occ[0]) {
                            $isAvailable = false;
                            $blockingInfo = [
                                'type' => 'availability_block',
                                'start' => date('g:i A', $occ[0]),
                                'end' => date('g:i A', $occ[1])
                            ];
                            break;
                        }
                    }
                }

                // Build rich slot information for frontend
                $slotInfo = [
                    'time' => date('g:i A', $slotStart),
                    'timestamp' => $slotStart,
                    'datetime' => date('Y-m-d H:i:s', $slotStart),
                    'available' => $isAvailable,
                    'duration_minutes' => $duration,
                    'grace_minutes' => $grace,
                    'ends_at' => date('g:i A', $slotEnd)
                ];

                if ($dentistId) {
                    $slotInfo['dentist_id'] = (int)$dentistId;
                }

                if (!$isAvailable && $blockingInfo) {
                    $slotInfo['blocking_info'] = $blockingInfo;
                    $slotInfo['owned_by_current_user'] = $ownedByCurrentUser;
                }

                // Add all slots (available and unavailable) so frontend has complete picture
                $slots[] = $slotInfo;

                if (count($slots) >= 100) break; // increased limit for richer data
            }

            // Build richer response including separated available/unavailable slots and occupied intervals
            // Enhance occupied_map with appointment metadata (patient names) where possible
            try {
                $db = \Config\Database::connect();
                $ids = [];
                foreach ($occupied as $occ) { if (!empty($occ[2])) $ids[] = $occ[2]; }
                if (!empty($ids)) {
                    $rows = $db->table('appointments')
                              ->select('appointments.id, appointments.user_id, user.name as patient_name')
                              ->join('user', 'user.id = appointments.user_id', 'left')
                              ->whereIn('appointments.id', $ids)
                              ->get()->getResultArray();
                    $meta = [];
                    foreach ($rows as $r) $meta[$r['id']] = $r;
                    foreach ($occupied_map as &$m) {
                        $aid = $m['appointment_id'] ?? null;
                        if ($aid && isset($meta[$aid])) {
                            $m['patient_name'] = $meta[$aid]['patient_name'] ?? null;
                        }
                    }
                    unset($m);
                }
            } catch (\Exception $e) {
                // ignore metadata enrichment errors
            }

            // Separate available and unavailable slots for easier frontend consumption
            $available_slots = array_values(array_filter($slots, function($slot) { return $slot['available']; }));
            $unavailable_slots = array_values(array_filter($slots, function($slot) { return !$slot['available']; }));

            // Ensure available slots are ordered by timestamp
            usort($available_slots, function($a, $b){ return $a['timestamp'] <=> $b['timestamp']; });

            // Provide first available slot explicitly for frontend prefill
            $first_available = null;
            if (!empty($available_slots)) {
                $first_available = $available_slots[0];
            }

            $response = [
                'success' => true,
                'slots' => $available_slots,  // backward compatibility: only available slots
                'all_slots' => $slots,        // complete slot information
                'available_slots' => $available_slots,
                'unavailable_slots' => $unavailable_slots,
                'occupied_map' => $occupied_map,
                'metadata' => [
                    'total_slots_checked' => count($slots),
                    'available_count' => count($available_slots),
                    'unavailable_count' => count($unavailable_slots),
                    'duration_minutes' => $duration,
                    'grace_minutes' => $grace,
                    'day_start' => date('g:i A', $dayStart),
                    'day_end' => date('g:i A', $dayEnd),
                    'first_available' => $first_available
                ]
            ];

            // If no available slots within the computed lookahead/operating window, include a friendly note
            if (empty($available_slots)) {
                $response['note'] = 'No available slots within lookahead window. Please choose another date or time.';
            }

            return $this->response->setJSON($response);
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
                // Attempt to find nearby suggestions (lookahead 180 minutes by default)
                try {
                    $lookahead = 180;
                    $next = $appointmentModel->findNextAvailableSlot($date, $time, $grace, $lookahead, $dentistId, $duration);
                    if ($next) $suggestions[] = $next;
                } catch (\Exception $e) { log_message('warning', 'checkConflicts: suggestion lookup failed: ' . $e->getMessage()); }
            }

            return $this->response->setJSON(['success' => true, 'conflicts' => $strictConflict ? ['conflict' => true] : [], 'availability_conflicts' => $availabilityConflicts, 'hasConflicts' => $hasConflicts, 'suggestions' => $suggestions]);
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