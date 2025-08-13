<?php
$host = '127.0.0.1';
$dbname = 'perfectsmile_db';
$username = 'root';
$password = '';
$port = 3306;

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if special_notes column exists
    $stmt = $pdo->prepare('DESCRIBE user');
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('special_notes', $columns)) {
        $sql = 'ALTER TABLE user ADD COLUMN special_notes TEXT DEFAULT NULL';
        $pdo->exec($sql);
        echo "Added special_notes column to user table successfully.\n";
    } else {
        echo "special_notes column already exists in user table.\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>
