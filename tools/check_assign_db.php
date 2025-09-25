<?php
// tools/check_assign_db.php
// Lightweight script to simulate assignment POST and show debug_assign.log
// Usage: php tools/check_assign_db.php <role_id> <comma-separated-user-ids>

chdir(__DIR__ . '/..');
require 'vendor/autoload.php';

// Attempt to bootstrap CodeIgniter's framework minimally
// If full CI bootstrap isn't configured, fall back to direct DB check

$roleId = $argv[1] ?? null;
$userIds = $argv[2] ?? null;
if (!$roleId) {
    echo "Usage: php tools/check_assign_db.php <role_id> <user_ids_comma_separated>\n";
    exit(1);
}

// Try direct DB connection using Config\Database
try {
    $db = \Config\Database::connect();
    echo "DB connect OK\n";
} catch (Exception $e) {
    echo "DB connect failed: " . $e->getMessage() . "\n";
}

// Append a manual log entry via the same path used by RoleController
$logsDir = defined('WRITEPATH') ? rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'logs' : __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'writable' . DIRECTORY_SEPARATOR . 'logs';
if (!is_dir($logsDir)) @mkdir($logsDir, 0755, true);
$logPath = $logsDir . DIRECTORY_SEPARATOR . 'debug_assign.log';
$entry = date('Y-m-d H:i:s') . " check_assign_db invoked with role={$roleId} user_ids={$userIds}\n";
file_put_contents($logPath, $entry, FILE_APPEND | LOCK_EX);

// show last 40 lines of the log
if (is_file($logPath)) {
    echo "\nLast lines of {$logPath}:\n";
    $lines = preg_split("/\r\n|\n|\r/", file_get_contents($logPath));
    $lines = array_filter($lines, function($l){return trim($l) !== '';});
    $lines = array_slice($lines, -40);
    foreach ($lines as $l) echo $l . "\n";
} else {
    echo "Log file not found: {$logPath}\n";
}
