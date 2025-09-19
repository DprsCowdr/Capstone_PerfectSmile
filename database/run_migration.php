<?php
// Simple migration runner for local dev when mysql CLI is not available
$sqlFile = __DIR__ . '/migrations/20250916_update_availability_table.sql';
if (!file_exists($sqlFile)) {
    echo "SQL file not found: $sqlFile\n";
    exit(2);
}

$sql = file_get_contents($sqlFile);
if ($sql === false) { echo "Failed to read SQL file\n"; exit(3); }

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db   = 'perfectsmile_db-v1';
$port = 3306;

$mysqli = new mysqli($host, $user, $pass, $db, $port);
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "\n";
    exit(4);
}

// Split statements by semicolon while respecting delimiter lines is complex; use multi_query
if ($mysqli->multi_query($sql)) {
    do {
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());
    echo "Migration executed successfully.\n";
    exit(0);
} else {
    echo "Migration failed: (" . $mysqli->errno . ") " . $mysqli->error . "\n";
    exit(5);
}
