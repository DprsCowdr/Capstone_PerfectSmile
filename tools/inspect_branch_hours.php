<?php
require_once __DIR__ . '/../vendor/autoload.php';
// Initialize framework services (similar to smoke script) so Config\Database is available
if (is_file(__DIR__ . '/../tests/_bootstrap.php')) require_once __DIR__ . '/../tests/_bootstrap.php';
try { $db = \Config\Database::connect(); } catch (Exception $e) { echo "DB connect failed: " . $e->getMessage() . "\n"; exit(1); }
$rows = $db->table('branches')->select('id,name,operating_hours')->get()->getResultArray();
if (empty($rows)) { echo "No branches found\n"; exit(0); }
$date = date('Y-m-d', strtotime('+1 day'));
$weekday = strtolower(date('l', strtotime($date)));
foreach ($rows as $r) {
    echo "Branch #{$r['id']} - {$r['name']}\n";
    $oh = $r['operating_hours'] ?? null;
    if (!$oh) { echo "  operating_hours: null\n\n"; continue; }
    $decoded = json_decode($oh, true);
    if (!is_array($decoded)) { echo "  operating_hours: invalid JSON\n\n"; continue; }
    echo "  operating_hours (raw): " . json_encode($decoded) . "\n";
    if (!isset($decoded[$weekday])) { echo "  no data for weekday {$weekday}\n\n"; continue; }
    $d = $decoded[$weekday];
    $enabled = $d['enabled'] ?? false; $open = $d['open'] ?? '08:00'; $close = $d['close'] ?? '20:00';
    echo "  {$weekday}: enabled=" . ($enabled ? 'true' : 'false') . ", open={$open}, close={$close}\n";
    // Validate format
    $validOpen = preg_match('/^([01]?\\d|2[0-3]):[0-5]\\d$/', $open);
    $validClose = preg_match('/^([01]?\\d|2[0-3]):[0-5]\\d$/', $close);
    echo "    validOpen={$validOpen}, validClose={$validClose}\n";
    if ($enabled && $validOpen && $validClose) {
        $dayStart = strtotime($date . ' ' . $open . ':00');
        $dayEnd = strtotime($date . ' ' . $close . ':00');
        $minDayStart = strtotime($date . ' 08:00:00');
        $minDayEnd = strtotime($date . ' 20:00:00');
        $clampedStart = $dayStart < $minDayStart ? $minDayStart : $dayStart;
        $clampedEnd = $dayEnd < $minDayEnd ? $minDayEnd : $dayEnd;
        echo "    computed dayStart=" . date('Y-m-d H:i:s', $dayStart) . ", dayEnd=" . date('Y-m-d H:i:s', $dayEnd) . "\n";
        echo "    clampedStart=" . date('Y-m-d H:i:s', $clampedStart) . ", clampedEnd=" . date('Y-m-d H:i:s', $clampedEnd) . "\n";
    }
    echo "\n";
}
