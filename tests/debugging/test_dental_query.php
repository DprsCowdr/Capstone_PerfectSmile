<?php
// Test the exact query used by AdminController
try {
    $pdo = new PDO('mysql:host=localhost;dbname=perfectsmile_db', 'root', '');
    
    $patientId = 10; // Marc Aron Gamban
    echo "Testing dental chart query for patient ID: $patientId\n\n";
    
    // This is the exact query from AdminController
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
    $chartData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Raw query results:\n";
    if (empty($chartData)) {
        echo "No dental chart data found for patient ID $patientId\n";
    } else {
        foreach ($chartData as $chart) {
            echo "- Tooth {$chart['tooth_number']}: {$chart['condition']} (status: {$chart['status']}, date: {$chart['record_date']})\n";
        }
        
        // Group by tooth number like the API does
        $teethData = [];
        foreach ($chartData as $chart) {
            $toothNumber = $chart['tooth_number'];
            if (!isset($teethData[$toothNumber])) {
                $teethData[$toothNumber] = [];
            }
            $teethData[$toothNumber][] = $chart;
        }
        
        echo "\nGrouped teeth data:\n";
        foreach ($teethData as $toothNum => $toothData) {
            echo "Tooth $toothNum: " . count($toothData) . " record(s)\n";
            foreach ($toothData as $record) {
                echo "  - Condition: '{$record['condition']}', Status: '{$record['status']}', Date: {$record['record_date']}\n";
            }
        }
        
        // Simulate the API response
        $response = [
            'success' => true,
            'chart' => $chartData,
            'teeth_data' => $teethData
        ];
        
        echo "\nSimulated API response structure:\n";
        echo "Success: true\n";
        echo "Chart entries: " . count($chartData) . "\n";
        echo "Teeth data keys: " . implode(', ', array_keys($teethData)) . "\n";
        
        // Test the color mapping that would happen in JavaScript
        echo "\nColor mapping test:\n";
        foreach ($teethData as $toothNum => $toothData) {
            if (!empty($toothData)) {
                $latestRecord = $toothData[0];
                $condition = $latestRecord['condition'];
                echo "Tooth $toothNum: condition='$condition' -> ";
                
                // This matches the getToothColor function in JavaScript
                switch($condition) {
                    case 'healthy': echo "0x4ade80 (green)"; break;
                    case 'cavity': echo "0xf59e0b (yellow)"; break;
                    case 'missing': echo "0x6b7280 (gray)"; break;
                    case 'filled': echo "0x3b82f6 (blue)"; break;
                    case 'crown': echo "0xfbbf24 (gold)"; break;
                    case 'root_canal': echo "0xef4444 (red)"; break;
                    case 'extraction_needed': echo "0x991b1b (dark red)"; break;
                    default: echo "0xd1d5db (default gray)"; break;
                }
                echo "\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
?>
