<?php
// Simple test script to check appointment data and endpoint responses
$appointmentId = 1; // Change this to test different appointments

echo "<h2>Testing Appointment Info Modal</h2>\n";

// Test 1: Check if appointment endpoint exists
echo "<h3>Test 1: Checking appointment details endpoint</h3>\n";
$detailsUrl = "http://localhost:8000/admin/appointments/details/{$appointmentId}";
echo "URL: {$detailsUrl}\n";

// Test 2: Check service endpoint
echo "<h3>Test 2: Checking service endpoint</h3>\n";
$servicesUrl = "http://localhost:8000/admin/services/1";
echo "URL: {$servicesUrl}\n";

// Test 3: Check if the functions exist in the scripts
echo "<h3>Test 3: Checking JavaScript function</h3>\n";
$scriptsFile = 'app/Views/templates/calendar/scripts.php';
if (file_exists($scriptsFile)) {
    $content = file_get_contents($scriptsFile);
    if (strpos($content, 'showAppointmentInfoById') !== false) {
        echo "✓ showAppointmentInfoById function found in scripts.php\n";
    } else {
        echo "✗ showAppointmentInfoById function NOT found in scripts.php\n";
    }
} else {
    echo "✗ scripts.php file not found\n";
}

// Test 4: Check database for appointment data
echo "<h3>Test 4: Sample appointment data structure</h3>\n";
echo "Expected fields in appointment object:\n";
echo "- id, patient_name, dentist_name, branch_name\n";
echo "- appointment_date, appointment_time, appointment_datetime\n";
echo "- service_id, service_name, duration_minutes\n";
echo "- status, approval_status, appointment_type\n";
echo "- remarks\n";

echo "\n<h3>Instructions for testing:</h3>\n";
echo "1. Open your browser's Developer Tools (F12)\n";
echo "2. Go to the Console tab\n";
echo "3. Navigate to the admin calendar page\n";
echo "4. Click on an appointment\n";
echo "5. Watch the console for debug messages\n";
echo "6. Check if the modal shows appointment length and service details\n";
?>