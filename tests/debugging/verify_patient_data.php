<?php
// Direct database connection without CodeIgniter

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

    echo "=== USER 10 VERIFICATION ===\n\n";

    // Check patient exists (in user table)
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, role FROM user WHERE id = 10");
    $stmt->execute();
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($patient) {
        echo "✅ User found: {$patient['first_name']} {$patient['last_name']} (ID: {$patient['id']}, Role: {$patient['role']})\n\n";
    } else {
        echo "❌ User 10 not found!\n";
        
        // Let's see what users exist
        echo "\n📋 AVAILABLE USERS:\n";
        $stmt = $pdo->query("SELECT id, first_name, last_name, role FROM user LIMIT 10");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "ID {$row['id']}: {$row['first_name']} {$row['last_name']} ({$row['role']})\n";
        }
        echo "\n";
    }

    // Check dental chart data
    echo "🦷 DENTAL CHART DATA:\n";
    $stmt = $pdo->prepare("
        SELECT tooth_number, condition, notes, created_at 
        FROM dental_chart 
        WHERE patient_id = 10 
        ORDER BY tooth_number, created_at DESC
    ");
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($records)) {
        echo "❌ No dental chart records found for user 10\n";
        
        // Check if there are any dental records at all
        echo "\n🔍 CHECKING ALL DENTAL RECORDS:\n";
        $stmt = $pdo->query("SELECT DISTINCT patient_id, COUNT(*) as record_count FROM dental_chart GROUP BY patient_id LIMIT 5");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "Patient ID {$row['patient_id']}: {$row['record_count']} records\n";
        }
    } else {
        foreach ($records as $record) {
            echo "Tooth {$record['tooth_number']}: {$record['condition']} ({$record['created_at']})\n";
            if ($record['notes']) {
                echo "  Notes: {$record['notes']}\n";
            }
        }
    }

    echo "\n🔍 EXPECTED COLORS:\n";
    echo "Tooth 8 (cavity): Should be BRIGHT RED (#FF0000)\n";
    echo "Tooth 26 (missing): Should be DIM GRAY (#696969)\n\n";

    // Check if these specific teeth have data
    $stmt = $pdo->prepare("
        SELECT tooth_number, condition 
        FROM dental_chart 
        WHERE patient_id = 10 AND tooth_number IN (8, 26)
        ORDER BY tooth_number, created_at DESC
    ");
    $stmt->execute();
    $specificTeeth = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "🎯 TARGET TEETH STATUS:\n";
    foreach ($specificTeeth as $tooth) {
        echo "Tooth {$tooth['tooth_number']}: {$tooth['condition']}\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
