# Gmail SMTP Integration Setup Guide

This guide will help you configure Gmail SMTP for sending account activation emails in the Perfect Smile application.

## Prerequisites

1. A Gmail account
2. 2-Step Verification enabled on your Google Account
3. Access to your Google Account settings

## Step 1: Enable 2-Step Verification

1. Go to [Google Account Security](https://myaccount.google.com/security)
2. Enable "2-Step Verification" if not already enabled
3. Follow the setup process

## Step 2: Generate App Password

1. Go to [Google Account Security](https://myaccount.google.com/security)
2. Click on "2-Step Verification"
3. Scroll down to "App passwords"
4. Click "Generate app password"
5. Select "Mail" as the app
6. Copy the 16-character password (remove spaces)

## Step 3: Update .env Configuration

Open your `.env` file and update these settings:

```env
# Gmail SMTP Configuration
email.protocol = smtp
email.SMTPHost = smtp.gmail.com
email.SMTPUser = your-email@gmail.com
email.SMTPPass = your-16-character-app-password
email.SMTPPort = 587
email.SMTPCrypto = tls
email.fromEmail = your-email@gmail.com
email.fromName = Perfect Smile Dental Clinic
```

**Important:**

- Replace `your-email@gmail.com` with your actual Gmail address
- Replace `your-16-character-app-password` with the app password from Step 2
- Do not use your regular Gmail password

## Step 4: Test Configuration

Run the test script to verify your configuration:

```bash
php test_email_config.php
```

## Step 5: Activate Patient Accounts

1. Login as admin
2. Go to Admin → Patients → Account Activation
3. Click "Activate" for any inactive patient
4. The system will:
   - Generate a temporary password
   - Send email to patient's email address
   - Show success message with email status

## Email Features

When a patient account is activated:

1. **Automatic Email:** Patient receives a professional HTML email
2. **Temporary Password:** Included securely in the email
3. **Login Instructions:** Step-by-step guide for first login
4. **Security Notice:** Reminder to change password after first login
5. **Fallback:** If email fails, admin sees the password for manual sharing

## Troubleshooting

### Common Issues:

1. **"Authentication failed"**

   - Check if you're using App Password, not regular password
   - Verify 2-Step Verification is enabled

2. **"Connection timeout"**

   - Check internet connection
   - Verify SMTP settings (host, port, encryption)

3. **"Email not received"**
   - Check patient's spam/junk folder
   - Verify patient's email address is correct
   - Check Gmail sending limits

### Gmail Sending Limits:

- Free Gmail: 500 emails per day
- Google Workspace: 2000 emails per day

### Security Best Practices:

1. Never share your app password
2. Regularly rotate app passwords
3. Monitor Gmail security notifications
4. Use a dedicated clinic Gmail account if possible

## Support

If you encounter issues:

1. Check the application logs in `writable/logs/`
2. Run the test script for configuration validation
3. Verify all environment variables are set correctly

## Sample Email Preview

The patient will receive an email with:

- Professional clinic branding
- Clear temporary password display
- Login instructions
- Security recommendations
- Contact information
