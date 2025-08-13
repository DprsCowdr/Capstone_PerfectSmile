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

    echo "=== USER TABLE STRUCTURE ===\n";
    $stmt = $pdo->query("DESCRIBE user");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "{$row['Field']} - {$row['Type']} ({$row['Null']}, {$row['Key']})\n";
    }

    echo "\n=== DENTAL_CHART TABLE STRUCTURE ===\n";
    $stmt = $pdo->query("DESCRIBE dental_chart");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "{$row['Field']} - {$row['Type']} ({$row['Null']}, {$row['Key']})\n";
    }

    echo "\n=== SAMPLE USER DATA ===\n";
    $stmt = $pdo->query("SELECT * FROM user LIMIT 3");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID {$row['id']}: " . json_encode($row) . "\n";
    }

    echo "\n=== SAMPLE DENTAL CHART DATA ===\n";
    $stmt = $pdo->query("SELECT * FROM dental_chart LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($row) . "\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
