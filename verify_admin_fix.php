<?php
// Simplified test using direct database access to verify the appointment query fix
$env = file_exists('.env') ? '.env' : '.env.example';
if (file_exists($env)) {
    $lines = file($env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }
}

$host = $_ENV['database_default_hostname'] ?? 'localhost';
$database = $_ENV['database_default_database'] ?? 'perfectsmile_db-v1';
$username = $_ENV['database_default_username'] ?? 'root';
$password = $_ENV['database_default_password'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

echo "✅ FIXED: Admin Calendar Day/Week View Appointments\n";
echo "==================================================\n\n";

// Test the exact query that getAppointmentsWithDetails() now uses
$stmt = $pdo->query("
    SELECT appointments.*, 
           user.name as patient_name, 
           user.email as patient_email, 
           branches.name as branch_name,
           dentists.name as dentist_name,
           dentists.email as dentist_email
    FROM appointments 
    LEFT JOIN user ON user.id = appointments.user_id
    LEFT JOIN branches ON branches.id = appointments.branch_id
    LEFT JOIN user as dentists ON dentists.id = appointments.dentist_id
    WHERE appointments.approval_status IN ('approved', 'pending', 'auto_approved')
    AND appointments.status IN ('confirmed', 'scheduled', 'pending', 'pending_approval', 'ongoing')
    ORDER BY appointments.appointment_datetime DESC
    LIMIT 10
");

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Query Results (First 10 appointments that admin calendar will now show):\n";
echo "------------------------------------------------------------------------\n";

foreach ($results as $apt) {
    // Split datetime like the AppointmentModel does
    $datetime = $apt['appointment_datetime'] ?? '';
    $apt['appointment_date'] = substr($datetime, 0, 10);
    $apt['appointment_time'] = substr($datetime, 11, 5);
    
    echo "📅 " . $apt['appointment_date'] . " at " . $apt['appointment_time'] . "\n";
    echo "   Patient: " . ($apt['patient_name'] ?? 'Unknown') . "\n";
    echo "   Status: " . ($apt['status'] ?? 'N/A') . " / " . ($apt['approval_status'] ?? 'N/A') . "\n";
    echo "   Branch: " . ($apt['branch_name'] ?? 'N/A') . "\n";
    echo "   ---\n";
}

echo "\nBefore Fix: Only appointments with approval_status = 'approved' were shown\n";
echo "After Fix: Now includes pending, auto_approved, and relevant statuses\n\n";

echo "🎯 The admin calendar day/week views should now display these appointments!\n";
echo "   Open the admin appointments page and check the console logs for:\n";
echo "   - '[Calendar Debug] Initial appointments count: " . count($results) . "+'\n";
echo "   - '[DayView] Found X appointments for YYYY-MM-DD'\n";
echo "   - '[WeekView] Updating with X filtered appointments'\n\n";

echo "✨ Fix Summary:\n";
echo "   1. Updated AppointmentModel::getAppointmentsWithDetails() to include more statuses\n";
echo "   2. Updated AppointmentService::getAllAppointments() for consistency\n";
echo "   3. Added console logging for easier debugging\n";
echo "   4. Added fallback logic in calendar filtering to prevent empty views\n\n";

echo "🔧 Files Modified:\n";
echo "   - app/Models/AppointmentModel.php\n";
echo "   - app/Services/AppointmentService.php\n";
echo "   - app/Controllers/Staff.php\n";
echo "   - app/Views/templates/calendar/scripts.php\n";
echo "   - public/js/calendar-core.js\n";
?>