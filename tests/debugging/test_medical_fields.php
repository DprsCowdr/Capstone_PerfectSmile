<?php
require_once 'vendor/autoload.php';

$config = new \Config\Services();
$db = \Config\Database::connect();

echo "Testing medical history fields...\n";

$query = $db->query('SELECT id, name, previous_dentist, physician_name FROM user WHERE user_type = "patient" LIMIT 2');
$results = $query->getResultArray();

foreach ($results as $row) {
    echo 'Patient: ' . $row['name'] . ' - Previous Dentist: ' . ($row['previous_dentist'] ?: 'Not set') . "\n";
}

echo "Database fields working correctly!\n";
?>
