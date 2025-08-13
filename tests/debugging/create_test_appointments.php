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

    echo "=== CREATING TEST APPOINTMENTS ===\n\n";

    // Create test appointments for today with different statuses
    $testAppointments = [
        [
            'branch_id' => 1,
            'dentist_id' => 2, // Dr. John Smith
            'user_id' => 3,    // Patient Jane
            'appointment_datetime' => date('Y-m-d 14:00:00'), // 2 PM today
            'status' => 'scheduled',
            'appointment_type' => 'scheduled',
            'approval_status' => 'approved'
        ],
        [
            'branch_id' => 1,
            'dentist_id' => 2, // Dr. John Smith
            'user_id' => 5,    // Brandon
            'appointment_datetime' => date('Y-m-d 14:30:00'), // 2:30 PM today
            'status' => 'confirmed',
            'appointment_type' => 'scheduled',
            'approval_status' => 'approved'
        ],
        [
            'branch_id' => 1,
            'dentist_id' => 9, // Dr. Sarah Johnson
            'user_id' => 10,   // Marc Aron Gamban
            'appointment_datetime' => date('Y-m-d 15:00:00'), // 3 PM today
            'status' => 'scheduled',
            'appointment_type' => 'scheduled',
            'approval_status' => 'approved'
        ]
    ];

    foreach ($testAppointments as $index => $appointment) {
        $placeholders = ':' . implode(', :', array_keys($appointment));
        $columns = implode(', ', array_keys($appointment));
        
        $sql = "INSERT INTO appointments ({$columns}, created_at, updated_at) 
                VALUES ({$placeholders}, NOW(), NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($appointment);
        
        $appointmentId = $pdo->lastInsertId();
        
        echo "✅ Created test appointment #{$appointmentId}: ";
        echo "{$appointment['status']} appointment for user {$appointment['user_id']} ";
        echo "with dentist {$appointment['dentist_id']} at {$appointment['appointment_datetime']}\n";
    }

    echo "\n=== TEST APPOINTMENTS CREATED SUCCESSFULLY ===\n";
    echo "Now you can test:\n";
    echo "1. Check-in patients (status: scheduled/confirmed → checked_in)\n";
    echo "2. Call patients for treatment (status: checked_in → ongoing)\n";
    echo "3. Complete treatments (status: ongoing → completed)\n\n";

    // Show current appointments for today
    echo "=== TODAY'S APPOINTMENTS (Updated) ===\n";
    $stmt = $pdo->query("
        SELECT 
            a.id, 
            a.status, 
            a.appointment_datetime,
            u.name as patient_name,
            d.name as dentist_name
        FROM appointments a
        JOIN user u ON u.id = a.user_id
        LEFT JOIN user d ON d.id = a.dentist_id
        WHERE DATE(a.appointment_datetime) = CURDATE()
        ORDER BY a.appointment_datetime
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "#{$row['id']}: {$row['patient_name']} → {$row['dentist_name']} | ";
        echo "{$row['appointment_datetime']} | Status: {$row['status']}\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
