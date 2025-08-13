<?php
try {
    // Create connection using direct database configuration
    $mysqli = new mysqli('127.0.0.1', 'root', '', 'perfectsmile_db', 3306);
    
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }
    
    echo "Connected to database successfully.\n";
    
    // Add the missing next_appointment_id column
    $sql = "ALTER TABLE `dental_record` ADD COLUMN `next_appointment_id` INT(11) NULL AFTER `next_appointment_date`";
    
    if ($mysqli->query($sql)) {
        echo "Column 'next_appointment_id' added successfully to dental_record table!\n";
    } else {
        echo "Error adding column: " . $mysqli->error . "\n";
    }
    
    // Verify the column was added
    $result = $mysqli->query("DESCRIBE dental_record");
    echo "\nUpdated dental_record table structure:\n";
    echo "Field\t\t\tType\n";
    echo "-----\t\t\t----\n";
    
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . "\t\t" . $row['Type'] . "\n";
    }
    
    $mysqli->close();
    echo "\nDatabase update completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
