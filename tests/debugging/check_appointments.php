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

    echo "=== APPOINTMENTS TABLE STRUCTURE ===\n";
    $stmt = $pdo->query("DESCRIBE appointments");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "{$row['Field']} - {$row['Type']} ({$row['Null']}, {$row['Key']})\n";
    }

    echo "\n=== SAMPLE APPOINTMENTS DATA ===\n";
    $stmt = $pdo->query("SELECT * FROM appointments LIMIT 3");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID {$row['id']}: " . json_encode($row) . "\n";
    }

    echo "\n=== CHECK FOR MISSING COLUMNS ===\n";
    $requiredColumns = ['checked_in_at', 'checked_in_by', 'started_at'];
    $stmt = $pdo->query("DESCRIBE appointments");
    $existingColumns = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $existingColumns[] = $row['Field'];
    }
    
    foreach ($requiredColumns as $col) {
        if (in_array($col, $existingColumns)) {
            echo "✅ Column '$col' exists\n";
        } else {
            echo "❌ Column '$col' MISSING\n";
        }
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
