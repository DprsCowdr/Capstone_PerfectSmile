<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Perfect Smile' ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .welcome-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 100%;
        }
        .welcome-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 1rem;
        }
        .welcome-subtitle {
            font-size: 1.2rem;
            color: #718096;
            margin-bottom: 2rem;
        }
        .feature-list {
            text-align: left;
            margin: 2rem 0;
        }
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.5rem 0;
        }
        .feature-icon {
            color: #667eea;
            margin-right: 1rem;
            font-size: 1.2rem;
            width: 30px;
        }
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 2rem;
        }
        .btn-primary {
            background: #667eea;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-block;
            text-decoration: none;
            color: white;
        }
        .btn-primary:hover {
            background: #5a67d8;
            transform: translateY(-2px);
            text-decoration: none;
            color: white;
        }
        .btn-outline-primary {
            border: 2px solid #667eea;
            color: #667eea;
            background: transparent;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-block;
            text-decoration: none;
        }
        .btn-outline-primary:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            text-decoration: none;
        }
        .guest-booking-section {
            background: #f7fafc;
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
            border-left: 5px solid #667eea;
        }
        .guest-booking-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 1rem;
        }
        .guest-booking-text {
            color: #718096;
            margin-bottom: 1.5rem;
        }
        @media (max-width: 768px) {
            .welcome-card {
                padding: 2rem;
                margin: 1rem;
            }
            .welcome-title {
                font-size: 2rem;
            }
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <div class="welcome-card">
            <div class="welcome-title">
                <i class="fas fa-tooth text-primary me-2"></i>
                Perfect Smile
            </div>
            <div class="welcome-subtitle">
                Professional Dental Clinic Management System
            </div>

            <!-- Guest Booking Section -->
            <div class="guest-booking-section">
                <div class="guest-booking-title">
                    <i class="fas fa-calendar-plus me-2"></i>
                    Book Your Appointment
                </div>
                <div class="guest-booking-text">
                    Quick and easy appointment booking - no account required! Get your perfect smile today.
                </div>
                <a href="<?= $guestBookingUrl ?>" class="btn-primary">
                    <i class="fas fa-calendar-check me-2"></i>
                    Book Appointment Now
                </a>
            </div>

            <div class="feature-list">
                <div class="feature-item">
                    <i class="fas fa-user-md feature-icon"></i>
                    <span>Professional dental care management</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-calendar-alt feature-icon"></i>
                    <span>Easy appointment scheduling</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-users feature-icon"></i>
                    <span>Multi-role user management</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-chart-line feature-icon"></i>
                    <span>Comprehensive reporting system</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-shield-alt feature-icon"></i>
                    <span>Secure patient data management</span>
                </div>
            </div>

            <div class="action-buttons">
                <a href="<?= base_url('login') ?>" class="btn-outline-primary">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Staff Login
                </a>
                <a href="<?= base_url('auth/register') ?>" class="btn-outline-primary">
                    <i class="fas fa-user-plus me-2"></i>
                    Create Account
                </a>
            </div>

            <div class="mt-4 text-muted">
                <small>
                    <i class="fas fa-info-circle me-1"></i>
                    For staff members and registered patients
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
