<?php
// tools/test_duration_examples.php
// Quick CLI test to validate blockSeconds, candidate fit, and overlap rules
// Usage: php tools/test_duration_examples.php

date_default_timezone_set('Asia/Manila');

function fmt($ts) { return date('Y-m-d H:i:s', $ts); }

$tests = [];

// Example 1 — single service with duration_max_minutes = 90, grace = 20
$tests[] = [
    'name' => 'Single service 90 + grace 20',
    'duration' => 90,
    'grace' => 20,
    'candidates' => [ '2025-09-24 08:00:00' ],
    // occupied appointments: none overlapping
    'occupied' => []
];

// Example 2 — multiple services: max 45 + max 30 = 75, grace = 15
$tests[] = [
    'name' => 'Multi-service 45+30 + grace 15',
    'duration' => 75,
    'grace' => 15,
    'candidates' => [ '2025-09-24 08:00:00' ],
    // occupied appointment that would block until 09:00 (i.e., an appointment from 07:00 for 60 + grace 0)
    'occupied' => [ ['start' => '2025-09-24 07:00:00', 'end' => '2025-09-24 09:00:00'] ]
];

// Example 3 — missing duration
$tests[] = [
    'name' => 'Missing duration (0)',
    'duration' => 0,
    'grace' => 20,
    'candidates' => [ '2025-09-24 08:00:00' ],
    'occupied' => []
];

$dayStart = strtotime('2025-09-24 08:00:00');
$dayEnd = strtotime('2025-09-24 20:00:00');
$granularity = 5;

foreach ($tests as $t) {
    echo "=== {$t['name']} ===\n";
    $duration = (int)$t['duration'];
    $grace = (int)$t['grace'];
    $blockSeconds = ($duration + $grace) * 60;
    echo "duration={$duration} min, grace={$grace} min => blockSeconds={$blockSeconds} (" . (($blockSeconds)/60) . " minutes)\n";

    if ($blockSeconds <= 0) {
        echo "  -> No slots computed because duration+grace <= 0\n\n";
        continue;
    }

    foreach ($t['candidates'] as $cand) {
        $ts = strtotime($cand);
        $slotStart = $ts;
        $slotEnd = $slotStart + $blockSeconds;
        echo "Candidate start: " . date('Y-m-d H:i', $slotStart) . "  ends at " . date('Y-m-d H:i', $slotEnd) . "\n";

        $fitsDay = ($slotStart >= $dayStart) && ($slotEnd <= $dayEnd);
        echo " - fits within day window (8:00-20:00)? "; echo ($fitsDay ? "YES" : "NO"); echo "\n";

        // check overlaps
        $overlap = false;
        foreach ($t['occupied'] as $occ) {
            $os = strtotime($occ['start']);
            $oe = strtotime($occ['end']);
            if ($slotStart < $oe && $slotEnd > $os) { $overlap = true; echo " - overlaps occupied (" . date('H:i',$os) . "-" . date('H:i',$oe) . ")\n"; break; }
        }
        if (!$overlap) echo " - no overlaps detected => AVAILABLE\n";
        else echo " - overlap detected => NOT AVAILABLE\n";
    }

    echo "\n";
}

echo "Done.\n";
