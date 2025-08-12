<?php
// Check what databases and tables exist

$host = 'localhost';
$username = 'root';
$password = '';

try {
    // First, list all databases
    $pdo = new PDO("mysql:host={$host}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== AVAILABLE DATABASES ===\n";
    $stmt = $pdo->query("SHOW DATABASES");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Database']}\n";
    }

    // Try perfectsmile_db
    echo "\n=== CHECKING perfectsmile_db ===\n";
    try {
        $pdo->exec("USE perfectsmile_db");
        echo "✅ perfectsmile_db exists\n";
        
        echo "\n=== TABLES IN perfectsmile_db ===\n";
        $stmt = $pdo->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            echo "- {$row[0]}\n";
        }
    } catch (Exception $e) {
        echo "❌ perfectsmile_db error: " . $e->getMessage() . "\n";
    }

    // Try perfect_smile
    echo "\n=== CHECKING perfect_smile ===\n";
    try {
        $pdo->exec("USE perfect_smile");
        echo "✅ perfect_smile exists\n";
        
        echo "\n=== TABLES IN perfect_smile ===\n";
        $stmt = $pdo->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            echo "- {$row[0]}\n";
        }
    } catch (Exception $e) {
        echo "❌ perfect_smile error: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
