<?php
// Check services table structure
echo "Services Table Structure Check\n";
echo "==============================\n\n";

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "perfectsmile_db-v1";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check services table structure
    echo "Services table columns:\n";
    $query = "DESCRIBE services";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "  - " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    echo "\nSample services data:\n";
    $sampleQuery = "SELECT * FROM services LIMIT 3";
    $sampleStmt = $pdo->prepare($sampleQuery);
    $sampleStmt->execute();
    $samples = $sampleStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($samples as $service) {
        echo "  ID: " . $service['id'] . " | Name: " . $service['name'] . " | Duration: " . (isset($service['duration_minutes']) ? $service['duration_minutes'] : 'N/A') . " minutes\n";
    }
    
} catch(PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
}
?>