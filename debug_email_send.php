<?php

/**
 * Debug Email Test - Test actual email sending through the application
 */

// Set the working directory
chdir(__DIR__);

// Bootstrap the application
define('FCPATH', __DIR__ . '/public/');

require_once 'vendor/autoload.php';

// Boot the framework
$paths = new Config\Paths();
require_once $paths->systemDirectory . '/bootstrap.php';

$app = Config\Services::codeigniter();
$app->initialize();

echo "=== Testing Email Service in CodeIgniter Context ===\n\n";

try {
    // Load the email service
    $emailService = new \App\Services\EmailService();
    
    echo "1. Testing email service creation... ✅\n";
    
    // Test sending to the email that should have received the activation
    $testEmail = 'caritosbrandon@gmail.com'; // The email that should receive activation
    $result = $emailService->sendAccountActivationEmail(
        $testEmail,
        'Test Patient',
        'TEMP123456'
    );
    
    echo "2. Email sending result:\n";
    if ($result === true) {
        echo "   ✅ SUCCESS: Email sent successfully!\n";
        echo "   📧 To: $testEmail\n";
        echo "   🔑 Temp Password: TEMP123456\n";
        echo "   📨 Check your inbox and spam folder\n";
    } else {
        echo "   ❌ FAILED: Email could not be sent\n";
        echo "   🔍 Check the application logs for detailed error information\n";
    }
    
} catch (Exception $e) {
    echo "❌ Exception occurred: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
