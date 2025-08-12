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

    echo "=== DENTAL RECORD TABLE STRUCTURE ===\n";
    $stmt = $pdo->query("DESCRIBE dental_record");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "{$row['Field']} - {$row['Type']} ({$row['Null']}, {$row['Key']})\n";
    }

    echo "\n=== DENTAL RECORDS BY USER ===\n";
    $stmt = $pdo->query("SELECT user_id, COUNT(*) as record_count FROM dental_record GROUP BY user_id");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "User ID {$row['user_id']}: {$row['record_count']} dental records\n";
    }

    echo "\n=== DENTAL CHART DATA BY RECORD ===\n";
    $stmt = $pdo->query("
        SELECT dr.user_id, dc.tooth_number, dc.condition, dc.created_at
        FROM dental_chart dc
        JOIN dental_record dr ON dr.id = dc.dental_record_id
        ORDER BY dr.user_id, dc.tooth_number
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "User {$row['user_id']} - Tooth {$row['tooth_number']}: {$row['condition']} ({$row['created_at']})\n";
    }

    echo "\n=== CHECKING USER 10 SPECIFICALLY ===\n";
    $stmt = $pdo->prepare("
        SELECT dr.id as record_id, dr.user_id, dc.tooth_number, dc.condition
        FROM dental_record dr
        JOIN dental_chart dc ON dr.id = dc.dental_record_id
        WHERE dr.user_id = 10
    ");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($results)) {
        echo "❌ No dental records found for user 10\n";
    } else {
        foreach ($results as $row) {
            echo "✅ User 10 - Record {$row['record_id']} - Tooth {$row['tooth_number']}: {$row['condition']}\n";
        }
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
