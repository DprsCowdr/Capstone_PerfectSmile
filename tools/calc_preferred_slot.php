<?php
// Calculate preferred slot end time: preferred_time + total_service_duration + grace
// Usage:
// php tools/calc_preferred_slot.php '{"preferred_time":"2025-09-19 08:00:00","duration_minutes":120,"grace_minutes":20}'
// or pipe JSON via STDIN

$raw = null;
if (isset($argv[1]) && strlen($argv[1]) > 0) {
    $raw = $argv[1];
} else {
    $stdin = fopen('php://stdin', 'r');
    $contents = stream_get_contents($stdin);
    fclose($stdin);
    if ($contents) $raw = trim($contents);
}

if (!$raw) {
    fwrite(STDERR, "No input JSON provided. See usage in file header.\n");
    exit(2);
}

$data = json_decode($raw, true);
if ($data === null) {
    fwrite(STDERR, "Invalid JSON input.\n");
    exit(3);
}

// Expected input shape:
// { preferred_time: 'YYYY-MM-DD HH:MM:SS' or 'YYYY-MM-DDTHH:MM:SS',
//   duration_minutes: int (optional),
//   services: [{duration_minutes:int}, ...] (optional),
//   grace_minutes: int (optional, default 20),
//   expected_end: 'YYYY-MM-DD HH:MM:SS' (optional) }

if (empty($data['preferred_time'])) {
    fwrite(STDERR, "preferred_time is required in input JSON\n");
    exit(4);
}

$preferred = $data['preferred_time'];
$preferredTs = strtotime($preferred);
if ($preferredTs === false) {
    fwrite(STDERR, "preferred_time could not be parsed as a valid datetime. Use 'YYYY-MM-DD HH:MM:SS'\n");
    exit(5);
}

$durationTotal = 0;
if (!empty($data['duration_minutes'])) {
    $durationTotal += (int)$data['duration_minutes'];
}
if (!empty($data['services']) && is_array($data['services'])) {
    foreach ($data['services'] as $s) {
        if (isset($s['duration_minutes'])) $durationTotal += (int)$s['duration_minutes'];
    }
}

$grace = isset($data['grace_minutes']) ? (int)$data['grace_minutes'] : 20;

$computedEndTs = $preferredTs + (($durationTotal + $grace) * 60);
$computedEnd = date('Y-m-d H:i:s', $computedEndTs);

$out = [
    'success' => true,
    'preferred_start' => date('Y-m-d H:i:s', $preferredTs),
    'duration_minutes' => $durationTotal,
    'grace_minutes' => $grace,
    'computed_end' => $computedEnd,
    'computed_end_unix' => $computedEndTs
];

if (!empty($data['expected_end'])) {
    $expectedTs = strtotime($data['expected_end']);
    $out['expected_end'] = ($expectedTs !== false) ? date('Y-m-d H:i:s', $expectedTs) : $data['expected_end'];
    $out['calculation_correct'] = ($expectedTs !== false) ? ($expectedTs === $computedEndTs) : false;
}

// Pretty print JSON
echo json_encode($out, JSON_PRETTY_PRINT) . "\n";

exit(0);
