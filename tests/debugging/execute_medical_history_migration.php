<?php
try {
    // Create connection using direct database configuration
    $mysqli = new mysqli('127.0.0.1', 'root', '', 'perfectsmile_db', 3306);
    
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }
    
    echo "Connected to database successfully.\n";
    
    // Read and execute the SQL script
    $sql = file_get_contents('add_medical_history.sql');
    
    if ($mysqli->multi_query($sql)) {
        echo "Medical history fields added successfully!\n";
        
        // Process all results
        do {
            if ($result = $mysqli->store_result()) {
                $result->free();
            }
        } while ($mysqli->more_results() && $mysqli->next_result());
        
    } else {
        echo "Error: " . $mysqli->error . "\n";
    }
    
    // Verify the fields were added
    $result = $mysqli->query("DESCRIBE user");
    echo "\nUpdated user table structure:\n";
    echo "Field\t\t\tType\n";
    echo "-----\t\t\t----\n";
    
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . "\t\t" . $row['Type'] . "\n";
    }
    
    $mysqli->close();
    echo "\nDatabase migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
