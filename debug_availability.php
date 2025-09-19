<?php

// Simple diagnostic script to check availability table
require_once __DIR__ . '/vendor/autoload.php';

// Load CodeIgniter config
defined('APPPATH') || define('APPPATH', realpath(__DIR__ . '/app/') . DIRECTORY_SEPARATOR);
defined('ROOTPATH') || define('ROOTPATH', realpath(__DIR__ . '/') . DIRECTORY_SEPARATOR);

// Load database config
$config = include __DIR__ . '/app/Config/Database.php';

try {
    // Connect to database
    $db = \Config\Database::connect();
    
    echo "=== Availability Table Diagnostic ===\n";
    
    // Check if table exists
    if (!$db->tableExists('availability')) {
        echo "❌ Availability table does not exist!\n";
        
        // Show available tables
        $tables = $db->listTables();
        echo "Available tables: " . implode(', ', $tables) . "\n";
        
        exit(1);
    }
    
    echo "✓ Availability table exists\n";
    
    // Show table structure
    $fields = $db->getFieldData('availability');
    echo "\nTable structure:\n";
    foreach ($fields as $field) {
        echo "  - {$field->name} ({$field->type})\n";
    }
    
    // Show recent records
    $query = $db->table('availability')
               ->orderBy('created_at', 'DESC')
               ->limit(20)
               ->get();
    
    $rows = $query->getResultArray();
    
    echo "\nRecent availability records (" . count($rows) . " found):\n";
    
    if (empty($rows)) {
        echo "No records found in availability table.\n";
    } else {
        foreach ($rows as $i => $row) {
            echo sprintf(
                "%d. ID:%s User:%s Type:%s Recurring:%s Start:%s End:%s Day:%s Time:%s-%s Notes:%s Created:%s\n",
                $i + 1,
                $row['id'],
                $row['user_id'],
                $row['type'] ?? 'NULL',
                $row['is_recurring'] ? 'YES' : 'NO',
                $row['start_datetime'] ?? 'NULL',
                $row['end_datetime'] ?? 'NULL',
                $row['day_of_week'] ?? 'NULL',
                $row['start_time'] ?? 'NULL',
                $row['end_time'] ?? 'NULL',
                substr($row['notes'] ?? '', 0, 30),
                $row['created_at'] ?? 'NULL'
            );
        }
    }
    
    // Test insert to verify table is writable
    echo "\nTesting insert capability...\n";
    
    $testId = $db->table('availability')->insert([
        'user_id' => 999,
        'type' => 'test',
        'start_datetime' => '2025-09-16 10:00:00',
        'end_datetime' => '2025-09-16 11:00:00',
        'is_recurring' => 0,
        'notes' => 'Diagnostic test insert',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    if ($testId) {
        echo "✓ Test insert successful (ID: $testId)\n";
        
        // Clean up test record
        $db->table('availability')->delete(['id' => $testId]);
        echo "✓ Test record cleaned up\n";
    } else {
        echo "❌ Test insert failed\n";
        echo "Last error: " . $db->error()['message'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\n=== Diagnostic Complete ===\n";