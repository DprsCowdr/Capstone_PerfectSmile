<?php

// Simple availability diagnostic script
require_once __DIR__ . '/public/index.php';

try {
    $db = \Config\Database::connect();
    
    echo "=== Availability Table Status ===\n";
    
    // Check if table exists
    if (!$db->tableExists('availability')) {
        echo "❌ Availability table does not exist!\n";
        exit(1);
    }
    
    echo "✓ Availability table exists\n";
    
    // Get recent records
    $query = $db->table('availability')
               ->orderBy('id', 'DESC')
               ->limit(10)
               ->get();
    
    $rows = $query->getResultArray();
    
    echo "\nRecent records (" . count($rows) . " found):\n";
    
    if (empty($rows)) {
        echo "No records found.\n";
    } else {
        foreach ($rows as $row) {
            echo sprintf(
                "ID:%s User:%s Type:%s Recurring:%s Start:%s End:%s Day:%s Notes:%s\n",
                $row['id'],
                $row['user_id'],
                $row['type'] ?? 'NULL',
                $row['is_recurring'] ? 'YES' : 'NO',
                $row['start_datetime'] ?? $row['start_time'] ?? 'NULL',
                $row['end_datetime'] ?? $row['end_time'] ?? 'NULL',
                $row['day_of_week'] ?? 'NULL',
                substr($row['notes'] ?? '', 0, 20)
            );
        }
    }
    
    // Test write capability
    echo "\nTesting insert...\n";
    $result = $db->table('availability')->insert([
        'user_id' => 999,
        'type' => 'test_diagnostic',
        'start_datetime' => '2025-09-16 12:00:00',
        'end_datetime' => '2025-09-16 13:00:00',
        'is_recurring' => 0,
        'notes' => 'Test insert from diagnostic',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    if ($result) {
        $insertId = $db->insertID();
        echo "✓ Test insert successful (ID: $insertId)\n";
        
        // Cleanup
        $db->table('availability')->delete(['id' => $insertId]);
        echo "✓ Test record cleaned up\n";
    } else {
        echo "❌ Insert failed\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Done ===\n";