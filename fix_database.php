<?php

// Simple script to add missing next_appointment_id column using direct MySQL connection

$hostname = '127.0.0.1';
$username = 'root';
$password = 'root';
$database = 'perfectsmile_db';
$port = 3306;

try {
    // Create connection
    $connection = new mysqli($hostname, $username, $password, $database, $port);
    
    // Check connection
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }
    
    echo "Connected to database successfully.\n";
    
    // Check if column already exists
    $checkQuery = "SHOW COLUMNS FROM dental_record LIKE 'next_appointment_id'";
    $result = $connection->query($checkQuery);
    
    if ($result && $result->num_rows > 0) {
        echo "Column 'next_appointment_id' already exists in dental_record table.\n";
        $connection->close();
        exit(0);
    }
    
    // Add the column
    $sql = "ALTER TABLE dental_record ADD COLUMN next_appointment_id INT(11) NULL AFTER next_appointment_date";
    
    if ($connection->query($sql) === TRUE) {
        echo "Successfully added 'next_appointment_id' column to dental_record table.\n";
        
        // Try to add foreign key constraint
        $fkSql = "ALTER TABLE dental_record ADD CONSTRAINT fk_dental_record_next_appointment FOREIGN KEY (next_appointment_id) REFERENCES appointments(id) ON DELETE SET NULL ON UPDATE CASCADE";
        
        if ($connection->query($fkSql) === TRUE) {
            echo "Successfully added foreign key constraint.\n";
        } else {
            echo "Note: Could not add foreign key constraint (this is often okay): " . $connection->error . "\n";
        }
        
    } else {
        echo "Error adding column: " . $connection->error . "\n";
        $connection->close();
        exit(1);
    }
    
    $connection->close();
    echo "Database update completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
