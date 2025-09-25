<?php
namespace App\Services;

use Config\Services;

class AvailabilityService
{
    /**
     * Compute available slots and metadata for a given date/branch/duration.
     * Returns an array compatible with the previous Appointments::availableSlots response.
     */
    public function getAvailableSlots(array $params)
    {
        // params: date, branch_id, duration, dentist_id, granularity, service_id
        $date = $params['date'] ?? date('Y-m-d');
        $branchId = $params['branch_id'] ?? null;
        $duration = isset($params['duration']) ? (int)$params['duration'] : 0;
        $dentistId = $params['dentist_id'] ?? null;
        $granularity = isset($params['granularity']) ? (int)$params['granularity'] : 5;
        $serviceId = $params['service_id'] ?? null;

        // If service_id given, prefer duration_max_minutes
        if ($serviceId && $duration <= 0) {
            try {
                $svcModel = new \App\Models\ServiceModel();
                $svc = $svcModel->find($serviceId);
                if ($svc) {
                    if (!empty($svc['duration_max_minutes'])) $duration = (int)$svc['duration_max_minutes'];
                    elseif (!empty($svc['duration_minutes'])) $duration = (int)$svc['duration_minutes'];
                }
            } catch (\Exception $e) { /* ignore */ }
        }

        if ($duration <= 0) {
            return ['success' => true, 'slots' => [], 'all_slots' => [], 'available_slots' => [], 'unavailable_slots' => [], 'occupied_map' => [], 'suggestions' => [], 'metadata' => ['total_slots_checked' => 0, 'available_count' => 0, 'unavailable_count' => 0]];
        }

    // normalize granularity - prefer commonly used clinic steps (5,10,15,30)
    $allowed = [1,3,5,10,15,30];
    // If caller requested 1 or 3 explicitly keep it, otherwise prefer 5-minute base
    if (!in_array($granularity, $allowed)) $granularity = 5;

        // Prepare models
        $appointmentModel = new \App\Models\AppointmentModel();
        $occupied = $appointmentModel->getOccupiedIntervals($date, $branchId, $dentistId);

        // load availability blocks for dentist if provided
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
            } catch (\Exception $e) { /* ignore */ }
        }

        // Determine operating hours
        $dayStart = strtotime($date . ' 08:00:00');
        $dayEnd = strtotime($date . ' 20:00:00');
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
                            if (preg_match('/^([01]?\\d|2[0-3]):[0-5]\\d$/', $open) && preg_match('/^([01]?\\d|2[0-3]):[0-5]\\d$/', $close)) {
                                $openParts = explode(':', $open);
                                $closeParts = explode(':', $close);
                                $openHour = (int)$openParts[0];
                                $closeHour = (int)$closeParts[0];
                                $closeMin = isset($closeParts[1]) ? (int)$closeParts[1] : 0;
                                if ($closeHour < 8 && $openHour >= 8) {
                                    $closeHour += 12;
                                    $close = str_pad((string)$closeHour, 2, '0', STR_PAD_LEFT) . ':' . str_pad((string)$closeMin, 2, '0', STR_PAD_LEFT);
                                }
                                $dayStart = strtotime($date . ' ' . $open . ':00');
                                $dayEnd = strtotime($date . ' ' . $close . ':00');
                                if ($dayEnd <= $dayStart) $dayEnd = strtotime($date . ' 20:00:00');
                                $minDayStart = strtotime($date . ' 08:00:00');
                                $minDayEnd = strtotime($date . ' 20:00:00');
                                if ($dayStart < $minDayStart) $dayStart = $minDayStart;
                                if ($dayEnd < $minDayEnd) $dayEnd = $minDayEnd;
                            }
                        } else {
                            return ['success' => true, 'slots' => []];
                        }
                    }
                }
            }
        } catch (\Exception $e) { /* ignore and keep defaults */ }

        // grace period
        $grace = 0;
        try {
            $gpPath = WRITEPATH . 'grace_periods.json';
            if (is_file($gpPath)) {
                $gp = json_decode(file_get_contents($gpPath), true);
                if (!empty($gp['default'])) $grace = (int)$gp['default'];
            }
        } catch (\Exception $e) { $grace = 0; }

        // build occupied_map for frontend
        $occupied_map = [];
        foreach ($occupied as $occ) {
            $entry = [
                'start' => date('Y-m-d H:i:s', $occ[0]),
                'end' => date('Y-m-d H:i:s', $occ[1]),
                'appointment_id' => $occ[2] ?? null,
                'user_id' => $occ[3] ?? null
            ];
            try { $sessionUser = session('user_id'); $entry['owned_by_current_user'] = ($sessionUser && $sessionUser == $entry['user_id']); } catch (\Exception $e) { $entry['owned_by_current_user'] = false; }
            $occupied_map[] = $entry;
        }

        $slots = [];
        $currentUser = null;
        try { $currentUser = session('user_id'); } catch (\Exception $e) {}

    $blockSeconds = ($duration + $grace) * 60;
        if ($blockSeconds <= 0) {
            return ['success' => true, 'slots' => [], 'all_slots' => [], 'available_slots' => [], 'unavailable_slots' => [], 'occupied_map' => $occupied_map, 'suggestions' => [], 'metadata' => ['total_slots_checked' => 0, 'available_count' => 0, 'unavailable_count' => 0]];
        }

        $roundToGranularity = function($ts, $gran, $mode = 'nearest') {
            $midnight = strtotime(date('Y-m-d', $ts) . ' 00:00:00');
            $minutesSinceMid = ($ts - $midnight) / 60.0;
            if ($mode === 'nearest') $roundedMinutes = (int)round($minutesSinceMid / $gran) * $gran;
            else $roundedMinutes = (int)ceil($minutesSinceMid / $gran) * $gran;
            return $midnight + ($roundedMinutes * 60);
        };

        $candidates = [];
        $endDerivedCandidates = [];
        $gridStart = $roundToGranularity($dayStart, $granularity, 'nearest');
        if ($gridStart < $dayStart) $gridStart = $roundToGranularity($dayStart, $granularity, 'up');
        $step = $granularity * 60;
        for ($t = $gridStart; $t + $blockSeconds <= $dayEnd; $t += $step) $candidates[] = $t;
        foreach ($occupied as $occ) {
            $endTs = (int)$occ[1];
            if ($endTs >= $dayStart && ($endTs + $blockSeconds) <= $dayEnd) { $candidates[] = $endTs; $endDerivedCandidates[] = $endTs; }
            $snapped = $roundToGranularity($endTs, $granularity, 'nearest');
            if ($snapped >= $dayStart && ($snapped + $blockSeconds) <= $dayEnd) { $candidates[] = $snapped; if ($snapped !== $endTs) $endDerivedCandidates[] = $snapped; }
        }

        $candidates = array_values(array_unique($candidates));
        sort($candidates);

        // count dentists
        $dentistsCount = 1;
        if (!$dentistId) {
            try {
                $db = \Config\Database::connect();
                $cnt = $db->table('user')->join('branch_staff', 'branch_staff.user_id = user.id')->where('user.user_type', 'dentist')->where('user.status', 'active');
                if ($branchId) $cnt->where('branch_staff.branch_id', $branchId);
                $dentistsCount = (int)$cnt->countAllResults(); if ($dentistsCount < 1) $dentistsCount = 1;
            } catch (\Exception $e) { $dentistsCount = 1; }
        }

        foreach ($candidates as $slotStart) {
            if ($slotStart + $blockSeconds > $dayEnd) continue;
            $slotEnd = $slotStart + $blockSeconds;
            $isAvailable = true; $blockingInfo = null; $ownedByCurrentUser = false;
            if ($dentistId) {
                foreach ($occupied as $occ) {
                    if ($slotStart < $occ[1] && $slotEnd > $occ[0]) { $isAvailable = false; $blockingInfo = ['type'=>'appointment','start'=>date('g:i A',$occ[0]),'end'=>date('g:i A',$occ[1]),'appointment_id'=>$occ[2]??null]; if (!empty($occ[3]) && $currentUser && $occ[3] == $currentUser) { $ownedByCurrentUser = true; $blockingInfo['owned_by_current_user'] = true; } break; }
                }
            } else {
                $overlaps = 0; $lastBlocking = null;
                foreach ($occupied as $occ) { if ($slotStart < $occ[1] && $slotEnd > $occ[0]) { $overlaps++; $lastBlocking = $occ; } }
                if ($overlaps >= $dentistsCount) { $isAvailable = false; if ($lastBlocking) { $blockingInfo = ['type'=>'appointment','start'=>date('g:i A',$lastBlocking[0]),'end'=>date('g:i A',$lastBlocking[1]),'appointment_id'=>$lastBlocking[2]??null]; if (!empty($lastBlocking[3]) && $currentUser && $lastBlocking[3] == $currentUser) { $ownedByCurrentUser = true; $blockingInfo['owned_by_current_user'] = true; } } }
            }
            if ($isAvailable && !empty($availabilityBlocks)) {
                foreach ($availabilityBlocks as $occ) { if ($slotStart < $occ[1] && $slotEnd > $occ[0]) { $isAvailable = false; $blockingInfo = ['type'=>'availability_block','start'=>date('g:i A',$occ[0]),'end'=>date('g:i A',$occ[1])]; break; } }
            }

            $slotInfo = ['time'=>date('g:i A',$slotStart),'timestamp'=>$slotStart,'datetime'=>date('Y-m-d H:i:s',$slotStart),'available'=>$isAvailable,'duration_minutes'=>$duration,'grace_minutes'=>$grace,'ends_at'=>date('g:i A',$slotEnd)];
            if ($dentistId) $slotInfo['dentist_id'] = (int)$dentistId;
            if (!$isAvailable && $blockingInfo) { $slotInfo['blocking_info'] = $blockingInfo; $slotInfo['owned_by_current_user'] = $ownedByCurrentUser; }
            $slots[] = $slotInfo;
            if (count($slots) >= 100) break;
        }

        // enrich occupied_map with patient names
        try {
            $db = \Config\Database::connect();
            $ids = []; foreach ($occupied as $occ) { if (!empty($occ[2])) $ids[] = $occ[2]; }
            if (!empty($ids)) {
                $rows = $db->table('appointments')->select('appointments.id, appointments.user_id, user.name as patient_name')->join('user','user.id = appointments.user_id','left')->whereIn('appointments.id',$ids)->get()->getResultArray();
                $meta = []; foreach ($rows as $r) $meta[$r['id']] = $r;
                foreach ($occupied_map as &$m) { $aid = $m['appointment_id'] ?? null; if ($aid && isset($meta[$aid])) { $m['patient_name'] = $meta[$aid]['patient_name'] ?? null; } } unset($m);
            }
        } catch (\Exception $e) { /* ignore */ }

        $available_slots = array_values(array_filter($slots, function($slot){ return $slot['available']; }));
        $unavailable_slots = array_values(array_filter($slots, function($slot){ return !$slot['available']; }));
        usort($available_slots, function($a,$b){ return $a['timestamp'] <=> $b['timestamp']; });
        $first_available = !empty($available_slots) ? $available_slots[0] : null;

        // build suggestions (prefer hourly alignment for duration=45)
        $suggestions = [];
        try {
            if ($date === date('Y-m-d')) { $refTs = time(); if ($refTs < $dayStart) $refTs = $dayStart; } else { $refTs = $dayStart; }
            $candidatesAfterRef = array_values(array_filter($available_slots, function($s) use ($refTs) { return ($s['timestamp'] >= $refTs); }));
            if (empty($candidatesAfterRef)) $candidatesAfterRef = $available_slots;
            $maxSuggestions = 6; $count = 0;
            if ($duration === 45) {
                $availIndex = []; foreach ($available_slots as $a) { $availIndex[date('H:i',$a['timestamp'])] = $a; }
                for ($h = (int)date('H',$dayStart); $h <= (int)date('H',$dayEnd); $h++) {
                    $hourTs = strtotime(date('Y-m-d',$dayStart) . ' ' . str_pad($h,2,'0',STR_PAD_LEFT) . ':00:00');
                    if ($hourTs < $refTs) continue;
                    $trueEnd = $hourTs + ($duration * 60);
                    if ($trueEnd > $dayEnd) continue;
                    $key = date('H:i', $hourTs);
                    if (isset($availIndex[$key])) {
                        $cand = $availIndex[$key];
                        $suggestions[] = ['time'=>$cand['time'],'datetime'=>$cand['datetime'],'timestamp'=>$cand['timestamp'],'ends_at'=>date('Y-m-d H:i:s',$trueEnd),'duration_minutes'=>$duration,'dentist_id'=>$cand['dentist_id'] ?? null,'available'=>true,'aligned'=>'hourly'];
                        $count++; if ($count >= $maxSuggestions) break;
                    }
                }
            }
            foreach ($candidatesAfterRef as $cand) {
                if ($count >= $maxSuggestions) break;
                $start = (int)$cand['timestamp']; $trueEnd = $start + ($duration * 60);
                if ($trueEnd > $dayEnd) continue;
                $already = false; foreach ($suggestions as $s) { if ((int)$s['timestamp'] === $start) { $already = true; break; } }
                if ($already) continue;
                $suggestions[] = ['time'=>$cand['time'],'datetime'=>$cand['datetime'],'timestamp'=>$cand['timestamp'],'ends_at'=>date('Y-m-d H:i:s',$trueEnd),'duration_minutes'=>$duration,'dentist_id'=>$cand['dentist_id'] ?? null,'available'=>true];
                $count++;
            }
        } catch (\Exception $e) { $suggestions = []; }

        $response = ['success'=>true,'slots'=>$available_slots,'all_slots'=>$slots,'available_slots'=>$available_slots,'unavailable_slots'=>$unavailable_slots,'occupied_map'=>$occupied_map,'suggestions'=>$suggestions,'metadata'=>['total_slots_checked'=>count($slots),'available_count'=>count($available_slots),'unavailable_count'=>count($unavailable_slots),'duration_minutes'=>$duration,'grace_minutes'=>$grace,'day_start'=>date('g:i A',$dayStart),'day_end'=>date('g:i A',$dayEnd),'first_available'=>$first_available]];
        if (empty($available_slots)) $response['note'] = 'No available slots within lookahead window. Please choose another date or time.';
        return $response;
    }
}
