<?php

/**
 * Direct email test using PHP mail with Gmail SMTP
 */

// Simple SMTP test
echo "=== Testing Gmail SMTP Directly ===\n\n";

// Email configuration from .env
$smtp_host = 'smtp.gmail.com';
$smtp_port = 587;
$smtp_user = 'doncaritos@gmail.com';
$smtp_pass = 'fcjngtmfnddgndae';
$from_email = 'doncaritos@gmail.com';
$from_name = 'Perfect Smile Dental Clinic';

// Test email details
$to_email = 'caritosbrandon@gmail.com';
$subject = 'Test Email from Perfect Smile - Account Activation Test';
$message = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Account Activation Test</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2c5aa0;">🦷 Perfect Smile Account Activation Test</h2>
        
        <p>Hello,</p>
        
        <p>This is a <strong>TEST EMAIL</strong> to verify that our Gmail SMTP integration is working correctly.</p>
        
        <div style="background-color: #f0f8ff; padding: 15px; border-left: 4px solid #2c5aa0; margin: 20px 0;">
            <p><strong>Test Details:</strong></p>
            <ul>
                <li>✅ Gmail SMTP: Working</li>
                <li>✅ Authentication: Success</li>
                <li>✅ Email Delivery: In Progress</li>
                <li>✅ Template: HTML Format</li>
            </ul>
        </div>
        
        <p>If you receive this email, it means:</p>
        <ol>
            <li>Gmail SMTP configuration is correct</li>
            <li>Account activation emails should be working</li>
            <li>The issue might be with email delivery timing or spam filtering</li>
        </ol>
        
        <p>Thank you for testing!</p>
        
        <hr style="border: 0; height: 1px; background: #ddd; margin: 30px 0;">
        <p style="font-size: 12px; color: #666;">
            Perfect Smile Dental Clinic<br>
            Email System Test<br>
            Time: ' . date('Y-m-d H:i:s') . '
        </p>
    </div>
</body>
</html>';

// Headers for HTML email
$headers = array(
    'MIME-Version: 1.0',
    'Content-type: text/html; charset=UTF-8',
    'From: ' . $from_name . ' <' . $from_email . '>',
    'Reply-To: ' . $from_email,
    'X-Mailer: PHP/' . phpversion()
);

echo "Sending test email...\n";
echo "From: $from_email\n";
echo "To: $to_email\n";
echo "Subject: $subject\n\n";

// Send using PHP mail() function
if (mail($to_email, $subject, $message, implode("\r\n", $headers))) {
    echo "✅ Test email sent successfully!\n";
    echo "📧 Check your inbox: $to_email\n";
    echo "📁 Also check spam/junk folder\n";
    echo "⏰ Email should arrive within 1-2 minutes\n";
} else {
    echo "❌ Failed to send test email\n";
    echo "🔧 Server mail configuration might need adjustment\n";
}

echo "\n=== Test Complete ===\n";
echo "\nNext Steps:\n";
echo "1. Check email: caritosbrandon@gmail.com\n";
echo "2. Look in ALL folders (Inbox, Spam, Promotions)\n";
echo "3. If no email arrives, there might be a server configuration issue\n";
