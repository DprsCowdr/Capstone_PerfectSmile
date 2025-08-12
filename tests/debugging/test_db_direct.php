<?php
// Test direct database access for dental chart data
try {
    $pdo = new PDO('mysql:host=localhost;dbname=perfectsmile_db', 'root', '');
    
    $patientId = 10; // Marc Aron Gamban
    echo "Testing dental chart data for patient ID: $patientId\n\n";
    
    // Query that matches the AdminController getPatientDentalChart method
    $sql = "SELECT dental_chart.*,
                   dental_record.record_date,
                   dental_record.diagnosis,
                   dentist.name as dentist_name,
                   services.name as recommended_service,
                   services.price as service_price
            FROM dental_chart 
            LEFT JOIN dental_record ON dental_record.id = dental_chart.dental_record_id 
            LEFT JOIN user as dentist ON dentist.id = dental_record.dentist_id
            LEFT JOIN services ON services.id = dental_chart.recommended_service_id
            WHERE dental_record.user_id = ? 
            ORDER BY dental_chart.tooth_number ASC, dental_record.record_date DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$patientId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Raw database results:\n";
    if (empty($results)) {
        echo "No dental chart data found for patient ID $patientId\n";
    } else {
        foreach ($results as $row) {
            echo "Tooth: {$row['tooth_number']}, Condition: {$row['condition']}, Date: {$row['record_date']}\n";
        }
        
        // Structure data like the API would
        $teeth_data = [];
        foreach ($results as $row) {
            $teeth_data[$row['tooth_number']][] = $row;
        }
        
        echo "\nStructured teeth_data:\n";
        print_r($teeth_data);
        
        // Test the API response format
        $response = [
            'status' => 'success',
            'chart' => $results,
            'teeth_data' => $teeth_data
        ];
        
        echo "\nExpected API response:\n";
        echo json_encode($response, JSON_PRETTY_PRINT);
    }
    
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
?>
