<?php
require_once 'vendor/autoload.php';

// Initialize CodeIgniter
$paths = new Config\Paths();
$bootstrap = rtrim(realpath(FCPATH . '../'), DIRECTORY_SEPARATOR);
$paths->systemDirectory = $bootstrap . DIRECTORY_SEPARATOR . 'vendor/codeigniter4/framework/system';
$paths->appDirectory = $bootstrap . DIRECTORY_SEPARATOR . 'app';
$paths->writableDirectory = $bootstrap . DIRECTORY_SEPARATOR . 'writable';

$app = Config\Services::codeigniter();
$app->initialize();

// Get database connection
$db = \Config\Database::connect();

// Check for doctors/dentists
echo "Current doctors/dentists in database:\n";
echo "=====================================\n";

$users = $db->table('user')->whereIn('user_type', ['doctor', 'dentist'])->get()->getResultArray();

foreach($users as $user) {
    echo "ID: {$user['id']} | Name: {$user['name']} | Email: {$user['email']} | Type: {$user['user_type']}\n";
}

echo "\nTotal found: " . count($users) . "\n";
