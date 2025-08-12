<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=perfectsmile_db', 'root', '');
    
    echo "Checking dental_chart table structure:\n";
    $stmt = $pdo->query('DESCRIBE dental_chart');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']}: {$row['Type']}\n";
    }
    
    echo "\nChecking dental_record table structure:\n";
    $stmt = $pdo->query('DESCRIBE dental_record');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']}: {$row['Type']}\n";
    }
    
    echo "\nChecking dental_chart data:\n";
    $stmt = $pdo->query('SELECT * FROM dental_chart LIMIT 5');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    
    echo "\nChecking dental_record data:\n";
    $stmt = $pdo->query('SELECT * FROM dental_record LIMIT 5');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
?>
