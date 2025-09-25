<?php
// Simple test to check patient authentication and CSRF
require 'vendor/autoload.php';

// Start CodeIgniter session to check authentication
$session = \Config\Services::session();
$session->start();

echo "=== Patient Authentication Test ===\n";
echo "Session ID: " . session_id() . "\n";
echo "Session data: " . json_encode($_SESSION ?? [], JSON_PRETTY_PRINT) . "\n";

// Check if user is logged in
$userType = $session->get('user_type');
$userId = $session->get('user_id');

echo "User Type: " . ($userType ?? 'null') . "\n";
echo "User ID: " . ($userId ?? 'null') . "\n";

// Check CSRF
$security = \Config\Services::security();
echo "CSRF Token Name: " . $security->getCSRFTokenName() . "\n";
echo "CSRF Token: " . $security->getCSRFHash() . "\n";

if ($userType === 'patient') {
    echo "\n✅ AUTHENTICATED as patient\n";
} else {
    echo "\n❌ NOT AUTHENTICATED as patient\n";
    echo "Current user type: " . ($userType ?? 'none') . "\n";
}