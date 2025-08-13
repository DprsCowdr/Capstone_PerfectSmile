<?php
$host = 'localhost';
$database = 'perfectsmile_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$database};charset=utf8mb4",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== USER TYPES IN SYSTEM ===\n";
    $stmt = $pdo->query("SELECT DISTINCT user_type FROM user ORDER BY user_type");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['user_type']}\n";
    }

    echo "\n=== USERS BY TYPE ===\n";
    $stmt = $pdo->query("SELECT id, name, user_type, email FROM user ORDER BY user_type, id");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID {$row['id']}: {$row['name']} ({$row['user_type']}) - {$row['email']}\n";
    }

    echo "\n=== TODAY'S APPOINTMENTS STATUS ===\n";
    $stmt = $pdo->query("
        SELECT 
            a.id, 
            a.status, 
            a.checked_in_at, 
            a.started_at,
            u.name as patient_name,
            d.name as dentist_name
        FROM appointments a
        JOIN user u ON u.id = a.user_id
        LEFT JOIN user d ON d.id = a.dentist_id
        WHERE DATE(a.appointment_datetime) = CURDATE()
        ORDER BY a.appointment_datetime
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Appointment {$row['id']}: {$row['patient_name']} -> {$row['dentist_name']} | Status: {$row['status']}\n";
        if ($row['checked_in_at']) echo "  Checked in: {$row['checked_in_at']}\n";
        if ($row['started_at']) echo "  Started: {$row['started_at']}\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
