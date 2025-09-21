<?php
// Test calendar rendering for admin with the fixed appointment query
require_once 'vendor/autoload.php';

// Initialize CodeIgniter
$app = \Config\Services::codeigniter();
$app->initialize();

// Mock admin user
$user = [
    'id' => 1,
    'user_type' => 'admin',
    'name' => 'Test Admin'
];

// Load appointment data using the updated model
$appointmentModel = new \App\Models\AppointmentModel();
$appointments = $appointmentModel->getAppointmentsWithDetails();

// Load branch data
$branchModel = new \App\Models\BranchModel();
$branches = $branchModel->findAll();

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Calendar Test</title>\n";
echo "<script src='https://cdn.tailwindcss.com'></script>\n";
echo "</head><body class='bg-gray-100'>\n";
echo "<div class='container mx-auto p-4'>\n";
echo "<h1 class='text-2xl font-bold mb-4'>Admin Calendar Test</h1>\n";

echo "<div class='mb-4 p-4 bg-white rounded shadow'>\n";
echo "<h2 class='text-lg font-semibold mb-2'>Appointment Data</h2>\n";
echo "<p>Total appointments loaded: <strong>" . count($appointments) . "</strong></p>\n";

if (count($appointments) > 0) {
    echo "<h3 class='mt-4 font-medium'>Recent Appointments:</h3>\n";
    echo "<div class='grid gap-2 mt-2'>\n";
    
    // Show first 5 appointments
    foreach (array_slice($appointments, 0, 5) as $apt) {
        $statusColor = match($apt['status'] ?? '') {
            'confirmed' => 'bg-green-100 text-green-800',
            'pending_approval' => 'bg-yellow-100 text-yellow-800',
            'scheduled' => 'bg-blue-100 text-blue-800',
            'ongoing' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800'
        };
        
        echo "<div class='p-2 border rounded $statusColor'>\n";
        echo "<strong>" . htmlspecialchars($apt['patient_name'] ?? 'Unknown') . "</strong><br>\n";
        echo "Date: " . htmlspecialchars($apt['appointment_date'] ?? 'N/A') . " " . htmlspecialchars($apt['appointment_time'] ?? 'N/A') . "<br>\n";
        echo "Status: " . htmlspecialchars($apt['status'] ?? 'N/A') . " / " . htmlspecialchars($apt['approval_status'] ?? 'N/A') . "<br>\n";
        echo "Branch: " . htmlspecialchars($apt['branch_name'] ?? 'N/A') . "\n";
        echo "</div>\n";
    }
    
    echo "</div>\n";
}

echo "</div>\n";

// Include calendar structure (simplified)
echo "<div class='bg-white p-4 rounded shadow'>\n";
echo "<h2 class='text-lg font-semibold mb-4'>Calendar Views</h2>\n";

// Day view placeholder
echo "<div id='dayView' class='mb-4'>\n";
echo "<h3 class='font-medium'>Day View</h3>\n";
echo "<table class='w-full border'>\n";
echo "<tbody><tr><td>All-day</td><td id='dayViewContent'>Loading...</td></tr></tbody>\n";
echo "</table>\n";
echo "</div>\n";

// Week view placeholder
echo "<div id='weekView'>\n";
echo "<h3 class='font-medium'>Week View</h3>\n";
echo "<div id='weekViewBody'>Loading...</div>\n";
echo "</div>\n";

echo "</div>\n";

// Include simplified calendar scripts
echo "<script>\n";
echo "// Set up window variables like the real calendar\n";
echo "window.userType = 'admin';\n";
echo "window.appointments = " . json_encode($appointments) . ";\n";
echo "window.branches = " . json_encode($branches) . ";\n";
echo "window.currentBranchFilter = 'all';\n";
echo "window.baseUrl = '" . base_url() . "';\n";
echo "\n";
echo "console.log('Test Data Loaded:');\n";
echo "console.log('Appointments count:', window.appointments.length);\n";
echo "console.log('Sample appointment:', window.appointments[0]);\n";
echo "console.log('Branches:', window.branches);\n";
echo "\n";
echo "// Simple test of getFilteredAppointments\n";
echo "function getFilteredAppointments() {\n";
echo "  const raw = window.appointments || [];\n";
echo "  const filter = window.currentBranchFilter || 'all';\n";
echo "  if (filter === 'all') return raw;\n";
echo "  return raw.filter(a => (a.branch_name || '').toLowerCase().includes(filter.toLowerCase()));\n";
echo "}\n";
echo "\n";
echo "// Test rendering\n";
echo "document.addEventListener('DOMContentLoaded', function() {\n";
echo "  const filtered = getFilteredAppointments();\n";
echo "  console.log('Filtered appointments:', filtered.length);\n";
echo "  \n";
echo "  // Simple day view test\n";
echo "  const dayContent = document.getElementById('dayViewContent');\n";
echo "  if (filtered.length > 0) {\n";
echo "    dayContent.innerHTML = filtered.length + ' appointment(s) available';\n";
echo "  } else {\n";
echo "    dayContent.innerHTML = 'No appointments found';\n";
echo "  }\n";
echo "  \n";
echo "  // Simple week view test\n";
echo "  const weekContent = document.getElementById('weekViewBody');\n";
echo "  weekContent.innerHTML = 'Week view would show ' + filtered.length + ' appointments';\n";
echo "});\n";
echo "</script>\n";

echo "</div></body></html>\n";
?>