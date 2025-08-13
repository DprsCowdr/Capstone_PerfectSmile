<?php

try {
    $pdo = new PDO('mysql:host=localhost;dbname=perfectsmile_db', 'root', '');
    
    echo "Checking dental chart data...\n";
    
    // Check if we have any dental charts
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM dental_chart');
    $totalCharts = $stmt->fetch()['total'];
    echo "Total dental chart entries: $totalCharts\n";
    
    // Check most recent chart entries
    $stmt = $pdo->query('SELECT dc.*, dr.record_date, u.name as patient_name 
                          FROM dental_chart dc 
                          JOIN dental_record dr ON dr.id = dc.dental_record_id 
                          JOIN user u ON u.id = dr.user_id 
                          ORDER BY dr.record_date DESC 
                          LIMIT 5');
    $recentCharts = $stmt->fetchAll();
    
    if ($recentCharts) {
        echo "\nRecent dental chart entries:\n";
        foreach ($recentCharts as $chart) {
            echo sprintf("- Patient: %s, Tooth: %s, Condition: %s, Status: %s, Date: %s\n",
                $chart['patient_name'], 
                $chart['tooth_number'], 
                $chart['condition'] ?: 'None', 
                $chart['status'] ?: 'None',
                $chart['record_date']
            );
        }
    } else {
        echo "No dental chart data found.\n";
    }
    
    // Check patients to test with
    $stmt = $pdo->query('SELECT id, name FROM user WHERE user_type = "patient" LIMIT 3');
    $patients = $stmt->fetchAll();
    
    echo "\nAvailable patients for testing:\n";
    foreach ($patients as $patient) {
        echo "- ID: {$patient['id']}, Name: {$patient['name']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
