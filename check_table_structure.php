<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=perfectsmile_db-v1', 'root', '');
$result = $pdo->query('DESCRIBE appointments');
$columns = $result->fetchAll(PDO::FETCH_ASSOC);
echo "Appointments table structure:\n";
foreach($columns as $col) {
    echo "  {$col['Field']} - {$col['Type']}\n";
}