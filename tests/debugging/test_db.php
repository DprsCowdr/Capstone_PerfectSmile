<?php

// Simple test to check database tables
require_once 'vendor/autoload.php';

use Config\Database;

$db = Database::connect();

echo "Checking database tables...\n";

// List all tables
$tables = $db->listTables();
echo "Available tables:\n";
foreach ($tables as $table) {
    echo "- $table\n";
}

// Check dental_record structure
echo "\nDental Record table structure:\n";
$fields = $db->getFieldData('dental_record');
foreach ($fields as $field) {
    echo "- {$field->name} ({$field->type})\n";
}

// Check if dental_chart table exists
if (in_array('dental_chart', $tables)) {
    echo "\nDental Chart table structure:\n";
    $fields = $db->getFieldData('dental_chart');
    foreach ($fields as $field) {
        echo "- {$field->name} ({$field->type})\n";
    }
} else {
    echo "\nDental Chart table does not exist!\n";
}

echo "\nDone.\n";
