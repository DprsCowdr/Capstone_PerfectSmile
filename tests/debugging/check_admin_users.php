<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=perfectsmile_db', 'root', '');
    echo "Admin/Doctor users:\n";
    $stmt = $pdo->query('SELECT id, email, name, user_type FROM user WHERE user_type IN ("admin", "doctor") LIMIT 5');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']}, Email: {$row['email']}, Name: {$row['name']}, Type: {$row['user_type']}\n";
    }
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>
