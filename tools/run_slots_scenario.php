<?php
// Lightweight standalone scenario runner for available slots suggestion logic
// This does not bootstrap CodeIgniter; it simulates occupied intervals and runs the core candidate/suggestion algorithm.

date_default_timezone_set('Asia/Manila');

function generate_candidates_and_suggestions($date, $dayStart, $dayEnd, $occupied, $duration, $grace = 15, $granularity = 5, $maxSuggestions = 6) {
    $blockSeconds = ($duration + $grace) * 60;
    if ($blockSeconds <= 0) return ['available_slots' => [], 'suggestions' => []];

    // round helper
    $roundToGranularity = function($ts, $gran, $mode = 'nearest') use ($date) {
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
        if ($endTs >= $dayStart && ($endTs + $blockSeconds) <= $dayEnd) {
            $candidates[] = $endTs; $endDerivedCandidates[] = $endTs;
        }
        $snapped = $roundToGranularity($endTs, $granularity, 'nearest');
        if ($snapped >= $dayStart && ($snapped + $blockSeconds) <= $dayEnd) {
            $candidates[] = $snapped; if ($snapped !== $endTs) $endDerivedCandidates[] = $snapped;
        }
    }

    $candidates = array_values(array_unique($candidates)); sort($candidates);

    // Evaluate availability
    $slots = [];
    foreach ($candidates as $slotStart) {
        $slotEnd = $slotStart + $blockSeconds;
        if ($slotEnd > $dayEnd) continue;
        $isAvailable = true; $blocking = null;
        foreach ($occupied as $occ) {
            if ($slotStart < $occ[1] && $slotEnd > $occ[0]) { $isAvailable = false; $blocking = $occ; break; }
        }
        $slots[] = ['timestamp' => $slotStart, 'time' => date('g:i A', $slotStart), 'available' => $isAvailable, 'ends_at' => date('g:i A', $slotEnd)];
    }

    $available_slots = array_values(array_filter($slots, function($s){ return $s['available']; }));
    usort($available_slots, function($a,$b){ return $a['timestamp'] <=> $b['timestamp']; });

    // suggestions based on reference
    if ($date === date('Y-m-d')) $refTs = time(); else $refTs = $dayStart;
    if ($refTs < $dayStart) $refTs = $dayStart;
    $candidatesAfterRef = array_values(array_filter($available_slots, function($s) use ($refTs){ return $s['timestamp'] >= $refTs; }));
    if (empty($candidatesAfterRef)) $candidatesAfterRef = $available_slots;

    $suggestions = [];$count=0;
    foreach ($candidatesAfterRef as $cand) {
        if ($count >= $maxSuggestions) break;
        $start = $cand['timestamp']; $trueEnd = $start + ($duration * 60);
        if ($trueEnd > $dayEnd) continue;
        $suggestions[] = ['time' => $cand['time'], 'datetime' => date('Y-m-d H:i:s', $start), 'ends_at' => date('Y-m-d H:i:s', $trueEnd)];
        $count++;
    }

    return ['available_slots' => $available_slots, 'suggestions' => $suggestions];
}

// Scenario 1: simple day with two occupied appointments
$date = date('Y-m-d', strtotime('+0 day'));
$dayStart = strtotime($date . ' 08:00:00');
$dayEnd = strtotime($date . ' 17:00:00');
$occupied = [
    [strtotime($date . ' 09:10:00'), strtotime($date . ' 09:40:00'), 101, 2],
    [strtotime($date . ' 11:00:00'), strtotime($date . ' 12:00:00'), 102, 3]
];

$duration = 45; $grace=15; $granularity=5;

$res = generate_candidates_and_suggestions($date, $dayStart, $dayEnd, $occupied, $duration, $grace, $granularity);

echo "Scenario 1 (duration {$duration}m) - Suggestions (45min, hourly-preferred):\n";
foreach ($res['suggestions'] as $s) echo "  {$s['time']} -> ends {$s['ends_at']}" . (isset($s['aligned']) ? " (aligned)" : "") . "\n";

echo "\nAvailable slot count: " . count($res['available_slots']) . "\n";

// Scenario 2: long duration that needs large contiguous free block
$duration = 180; // 3 hours
$res2 = generate_candidates_and_suggestions($date, $dayStart, $dayEnd, $occupied, $duration, $grace, $granularity);

echo "\nScenario 2 (duration {$duration}m) - Suggestions:\n";
foreach ($res2['suggestions'] as $s) echo "  {$s['time']} -> ends {$s['ends_at']}\n";

echo "\nAvailable slot count: " . count($res2['available_slots']) . "\n";

// Scenario 3: date in future (tomorrow) should start suggestions from dayStart, not now
$date = date('Y-m-d', strtotime('+1 day'));
$dayStart = strtotime($date . ' 08:00:00'); $dayEnd = strtotime($date . ' 17:00:00');
$occupied = [];
$duration = 45;
$res3 = generate_candidates_and_suggestions($date, $dayStart, $dayEnd, $occupied, $duration, $grace, $granularity);

echo "\nScenario 3 (tomorrow, duration {$duration}m) - Suggestions:\n";
foreach ($res3['suggestions'] as $s) echo "  {$s['time']} -> ends {$s['ends_at']}\n";

echo "\nAvailable slot count: " . count($res3['available_slots']) . "\n";

echo "\nScenario runner completed.\n";
