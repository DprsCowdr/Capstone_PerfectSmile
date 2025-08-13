<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug - Current User Info</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .debug-box { background: #f8f9fa; border: 1px solid #dee2e6; padding: 20px; margin: 10px 0; border-radius: 8px; }
        .success { background: #d4edda; border-color: #c3e6cb; }
        .error { background: #f8d7da; border-color: #f5c6cb; }
        .info { background: #d1ecf1; border-color: #bee5eb; }
    </style>
</head>
<body>
    <h1>🔍 Debug Information</h1>
    
    <div class="debug-box info">
        <h3>Current URL</h3>
        <p><strong>Current URL:</strong> <?= current_url() ?></p>
        <p><strong>Base URL:</strong> <?= base_url() ?></p>
    </div>

    <div class="debug-box">
        <h3>Session Information</h3>
        <?php if (session()->get('isLoggedIn')): ?>
            <div class="debug-box success">
                <p>✅ <strong>User is logged in</strong></p>
                <p><strong>User ID:</strong> <?= session()->get('user_id') ?></p>
                <p><strong>Name:</strong> <?= session()->get('user_name') ?></p>
                <p><strong>Email:</strong> <?= session()->get('user_email') ?></p>
                <p><strong>User Type:</strong> <?= session()->get('user_type') ?></p>
            </div>
        <?php else: ?>
            <div class="debug-box error">
                <p>❌ <strong>User is NOT logged in</strong></p>
            </div>
        <?php endif; ?>
    </div>

    <div class="debug-box">
        <h3>Available Routes for Testing</h3>
        <?php if (session()->get('user_type') === 'doctor'): ?>
            <ul>
                <li><a href="<?= base_url('dentist/dashboard') ?>">🏠 Dentist Dashboard</a></li>
                <li><a href="<?= base_url('dentist/dental-chart/1') ?>">🦷 Dental Chart (Appointment 1)</a></li>
                <li><a href="<?= base_url('dentist/dental-chart/2') ?>">🦷 Dental Chart (Appointment 2)</a></li>
                <li><a href="<?= base_url('dentist/patient-records/3') ?>">📋 Patient Records (Patient 3)</a></li>
                <li><a href="<?= base_url('dentist/procedures') ?>">🔧 Procedures</a></li>
            </ul>
        <?php else: ?>
            <p>⚠️ You are not logged in as a dentist. Please <a href="<?= base_url('login') ?>">login</a> first.</p>
        <?php endif; ?>
    </div>

    <div class="debug-box">
        <h3>Sample Login Credentials</h3>
        <p><strong>Dentist Login:</strong></p>
        <ul>
            <li>Email: <code>doctor@perfectsmile.com</code></li>
            <li>Password: <code>password</code></li>
        </ul>
        
        <p><strong>Dr. Sarah Johnson:</strong></p>
        <ul>
            <li>Email: <code>sarah.johnson@perfectsmile.com</code></li>
            <li>Password: <code>password</code></li>
        </ul>
    </div>

    <div class="debug-box">
        <h3>Database Check</h3>
        <?php
        $appointmentModel = new \App\Models\AppointmentModel();
        $appointments = $appointmentModel->select('id, user_id, dentist_id, appointment_datetime, status')
                                       ->limit(5)
                                       ->findAll();
        ?>
        <h4>Available Appointments:</h4>
        <table border="1" style="border-collapse: collapse; width: 100%;">
            <tr>
                <th>ID</th>
                <th>Patient ID</th>
                <th>Dentist ID</th>
                <th>Date & Time</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($appointments as $apt): ?>
            <tr>
                <td><?= $apt['id'] ?></td>
                <td><?= $apt['user_id'] ?></td>
                <td><?= $apt['dentist_id'] ?></td>
                <td><?= $apt['appointment_datetime'] ?></td>
                <td><?= $apt['status'] ?></td>
                <td>
                    <a href="<?= base_url('dentist/dental-chart/' . $apt['id']) ?>" style="background: #007bff; color: white; padding: 2px 8px; text-decoration: none; border-radius: 4px; font-size: 12px;">Chart</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="debug-box">
        <h3>Actions</h3>
        <p>
            <a href="<?= base_url('login') ?>" style="background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;">🔐 Login</a>
            <a href="<?= base_url('auth/logout') ?>" style="background: #dc3545; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;">🚪 Logout</a>
        </p>
    </div>
</body>
</html>
