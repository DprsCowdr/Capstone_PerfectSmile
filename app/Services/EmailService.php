<?php

namespace App\Services;

use Config\Email as EmailConfig;
use CodeIgniter\Email\Email;

class EmailService
{
    protected $emailConfig;
    
    public function __construct()
    {
        $this->emailConfig = new EmailConfig();
    }
    
    /**
     * Send account activation email with temporary password
     *
     * @param string $recipientEmail
     * @param string $recipientName
     * @param string $temporaryPassword
     * @return bool
     */
    public function sendAccountActivationEmail($recipientEmail, $recipientName, $temporaryPassword)
    {
        try {
            $email = \Config\Services::email($this->emailConfig);
            
            $email->setFrom($this->emailConfig->fromEmail, $this->emailConfig->fromName);
            $email->setTo($recipientEmail);
            $email->setSubject('Your Perfect Smile Account Has Been Activated');
            
            // Create HTML email content
            $message = $this->getActivationEmailTemplate($recipientName, $temporaryPassword, $recipientEmail);
            $email->setMessage($message);
            
            $result = $email->send();
            
            if (!$result) {
                log_message('error', 'Failed to send activation email to: ' . $recipientEmail . '. Error: ' . $email->printDebugger());
                return false;
            }
            
            log_message('info', 'Account activation email sent successfully to: ' . $recipientEmail);
            return true;
            
        } catch (\Exception $e) {
            log_message('error', 'Exception while sending activation email: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get HTML template for activation email
     *
     * @param string $recipientName
     * @param string $temporaryPassword
     * @param string $recipientEmail
     * @return string
     */
    private function getActivationEmailTemplate($recipientName, $temporaryPassword, $recipientEmail)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Account Activated - Perfect Smile</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 30px;
                    text-align: center;
                    border-radius: 10px 10px 0 0;
                }
                .content {
                    background: #f8f9fa;
                    padding: 30px;
                    border-radius: 0 0 10px 10px;
                }
                .password-box {
                    background: #fff;
                    border: 2px dashed #007bff;
                    padding: 20px;
                    margin: 20px 0;
                    text-align: center;
                    border-radius: 8px;
                }
                .password {
                    font-size: 24px;
                    font-weight: bold;
                    color: #007bff;
                    letter-spacing: 2px;
                }
                .warning {
                    background: #fff3cd;
                    border: 1px solid #ffeaa7;
                    color: #856404;
                    padding: 15px;
                    border-radius: 5px;
                    margin: 20px 0;
                }
                .footer {
                    text-align: center;
                    margin-top: 30px;
                    padding-top: 20px;
                    border-top: 1px solid #dee2e6;
                    color: #6c757d;
                    font-size: 14px;
                }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>🦷 Perfect Smile Dental Clinic</h1>
                <p>Your account has been activated!</p>
            </div>
            
            <div class='content'>
                <h2>Hello " . htmlspecialchars($recipientName) . ",</h2>
                
                <p>Great news! Your Perfect Smile account has been successfully activated by our administrator.</p>
                
                <p>You can now log in to our patient portal using your email address and the temporary password below:</p>
                
                <div class='password-box'>
                    <p><strong>Your Temporary Password:</strong></p>
                    <div class='password'>" . htmlspecialchars($temporaryPassword) . "</div>
                </div>
                
                <div class='warning'>
                    <strong>⚠️ Important Security Notice:</strong><br>
                    For your security, please change this temporary password immediately after your first login.
                    Go to your account settings to update your password.
                </div>
                
                <h3>How to access your account:</h3>
                <ol>
                    <li>Visit our patient portal login page</li>
                    <li>Enter your email: <strong>" . htmlspecialchars($recipientEmail) . "</strong></li>
                    <li>Enter the temporary password shown above</li>
                    <li>Change your password in account settings</li>
                </ol>
                
                <h3>What you can do with your account:</h3>
                <ul>
                    <li>📅 Schedule and manage appointments</li>
                    <li>📋 View your dental records and treatment history</li>
                    <li>💊 Access your prescriptions</li>
                    <li>🔔 Receive appointment reminders</li>
                    <li>👤 Update your personal information</li>
                </ul>
                
                <p>If you have any questions or need assistance, please don't hesitate to contact our clinic.</p>
                
                <p>Welcome to Perfect Smile!</p>
            </div>
            
            <div class='footer'>
                <p>Perfect Smile Dental Clinic<br>
                This is an automated message. Please do not reply to this email.<br>
                If you didn't request this account activation, please contact us immediately.</p>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Test email configuration
     *
     * @return array
     */
    public function testEmailConfiguration()
    {
        try {
            $email = \Config\Services::email($this->emailConfig);
            
            // Test connection by creating email instance
            $testResult = [
                'success' => true,
                'message' => 'Email configuration appears to be valid',
                'config' => [
                    'protocol' => $this->emailConfig->protocol,
                    'host' => $this->emailConfig->SMTPHost,
                    'port' => $this->emailConfig->SMTPPort,
                    'user' => $this->emailConfig->SMTPUser ? 'Configured' : 'Not set',
                    'from_email' => $this->emailConfig->fromEmail,
                    'from_name' => $this->emailConfig->fromName
                ]
            ];
            
            return $testResult;
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Email configuration error: ' . $e->getMessage(),
                'config' => []
            ];
        }
    }
    
    /**
     * Send welcome email to new patients
     *
     * @param string $recipientEmail
     * @param string $recipientName
     * @param string $temporaryPassword
     * @param string $subject
     * @param string $source
     * @return bool
     */
    public function sendWelcomeEmail($recipientEmail, $recipientName, $temporaryPassword, $subject = null, $source = 'appointment')
    {
        try {
            $email = \Config\Services::email($this->emailConfig);
            
            $email->setFrom($this->emailConfig->fromEmail, $this->emailConfig->fromName);
            $email->setTo($recipientEmail);
            $email->setSubject($subject ?? 'Welcome to Perfect Smile - Your Account Details');
            
            // Create HTML email content
            $message = $this->getWelcomeEmailTemplate($recipientName, $temporaryPassword, $recipientEmail, $source);
            $email->setMessage($message);
            
            $result = $email->send();
            
            if (!$result) {
                log_message('error', 'Failed to send welcome email to: ' . $recipientEmail . '. Error: ' . $email->printDebugger());
                return false;
            }
            
            log_message('info', 'Welcome email sent successfully to: ' . $recipientEmail);
            return true;
            
        } catch (\Exception $e) {
            log_message('error', 'Exception while sending welcome email: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate welcome email template for new patients
     */
    private function getWelcomeEmailTemplate($recipientName, $temporaryPassword, $recipientEmail, $source)
    {
        $loginUrl = base_url('login');
        $sourceText = $source === 'walkin' ? 'during your visit' : 'when you booked your appointment';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Welcome to Perfect Smile</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .credentials-box { background: white; border: 2px solid #667eea; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .cta-button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>🦷 Welcome to Perfect Smile!</h1>
                <p>Your patient account has been created</p>
            </div>
            
            <div class='content'>
                <h2>Hello " . htmlspecialchars($recipientName) . "!</h2>
                
                <p>Great news! We've created your patient account {$sourceText}. You can now:</p>
                
                <ul>
                    <li>📅 View and manage your appointments online</li>
                    <li>📋 Access your dental records and treatment history</li>
                    <li>💬 Receive important updates and reminders</li>
                    <li>📞 Request appointment changes or ask questions</li>
                </ul>
                
                <div class='credentials-box'>
                    <h3>🔐 Your Login Credentials</h3>
                    <p><strong>Email:</strong> " . htmlspecialchars($recipientEmail) . "</p>
                    <p><strong>Temporary Password:</strong> <code style='background: #e9ecef; padding: 4px 8px; border-radius: 4px;'>" . htmlspecialchars($temporaryPassword) . "</code></p>
                    <p style='color: #dc3545; font-size: 14px;'><strong>⚠️ Please change this password after your first login</strong></p>
                </div>
                
                <div style='text-align: center;'>
                    <a href='" . htmlspecialchars($loginUrl) . "' class='cta-button'>Login to Your Account</a>
                </div>
                
                <h3>📍 What's Next?</h3>
                <ol>
                    <li>Click the login button above</li>
                    <li>Use your email and temporary password</li>
                    <li>Update your password and complete your profile</li>
                    <li>Review your appointment details</li>
                </ol>
                
                <p style='margin-top: 30px;'>If you have any questions or need assistance, please don't hesitate to contact our office.</p>
                
                <p><strong>Perfect Smile Dental Clinic</strong><br>
                Your oral health is our priority! 🌟</p>
            </div>
            
            <div class='footer'>
                <p>This email was sent automatically. Please do not reply to this email.</p>
                <p>If you didn't expect this email, please contact our office immediately.</p>
            </div>
        </body>
        </html>
        ";
    }
}
