<?php
/**
 * Enhanced Patient Check-in View
 * Improved with better UI, real-time updates, and comprehensive status display
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Check-in Dashboard - Perfect Smile</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .status-badge {
            @apply px-2 py-1 rounded-full text-xs font-medium;
        }
        .status-confirmed { @apply bg-blue-100 text-blue-800; }
        .status-checked-in { @apply bg-green-100 text-green-800; }
        .status-ongoing { @apply bg-yellow-100 text-yellow-800; }
        .status-completed { @apply bg-gray-100 text-gray-800; }
        
        .appointment-card {
            transition: all 0.3s ease;
        }
        .appointment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .pulse-animation {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <i class="fas fa-user-check text-blue-600 text-2xl mr-3"></i>
                    <h1 class="text-2xl font-bold text-gray-900">Patient Check-in Dashboard</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-calendar-day mr-1"></i>
                        Today: <?= date('M d, Y') ?>
                    </div>
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-clock mr-1"></i>
                        <span id="current-time"><?= date('h:i A') ?></span>
                    </div>
                    <div class="text-sm text-gray-600">
                        Welcome, <?= esc($user['name']) ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-calendar-check text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Appointments</p>
                        <p class="text-2xl font-semibold text-gray-900" id="total-appointments">
                            <?= count($appointments) ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Pending Check-in</p>
                        <p class="text-2xl font-semibold text-gray-900" id="pending-checkin">
                            <?= count(array_filter($appointments, fn($a) => in_array($a['status'], ['confirmed', 'scheduled']))) ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-user-check text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Checked In</p>
                        <p class="text-2xl font-semibold text-gray-900" id="checked-in">
                            <?= count(array_filter($appointments, fn($a) => $a['status'] === 'checked_in')) ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-procedures text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">In Treatment</p>
                        <p class="text-2xl font-semibold text-gray-900" id="in-treatment">
                            <?= count(array_filter($appointments, fn($a) => $a['status'] === 'ongoing')) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <div id="alert-container" class="mb-6"></div>

        <!-- Appointments List -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Today's Appointments</h2>
                    <button onclick="refreshAppointments()" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-sync-alt mr-2"></i>
                        Refresh
                    </button>
                </div>
            </div>
            
            <div class="overflow-hidden">
                <?php if (empty($appointments)): ?>
                    <div class="p-8 text-center">
                        <i class="fas fa-calendar-times text-gray-400 text-4xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No appointments for today</h3>
                        <p class="text-gray-600">There are no appointments scheduled for today that require check-in.</p>
                    </div>
                <?php else: ?>
                    <div class="divide-y divide-gray-200" id="appointments-list">
                        <?php foreach ($appointments as $appointment): ?>
                            <div class="appointment-card p-6 hover:bg-gray-50" data-appointment-id="<?= $appointment['id'] ?>">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <!-- Patient Avatar -->
                                        <div class="flex-shrink-0">
                                            <div class="h-12 w-12 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold text-lg">
                                                <?= strtoupper(substr($appointment['patient_name'], 0, 1)) ?>
                                            </div>
                                        </div>
                                        
                                        <!-- Appointment Details -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center space-x-3">
                                                <h3 class="text-lg font-semibold text-gray-900">
                                                    <?= esc($appointment['patient_name']) ?>
                                                </h3>
                                                <span class="status-badge status-<?= $appointment['status'] ?>">
                                                    <?= ucfirst($appointment['status']) ?>
                                                </span>
                                            </div>
                                            
                                            <div class="mt-1 flex items-center space-x-4 text-sm text-gray-600">
                                                <div class="flex items-center">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    <?= date('h:i A', strtotime($appointment['appointment_datetime'])) ?>
                                                </div>
                                                <div class="flex items-center">
                                                    <i class="fas fa-user-md mr-1"></i>
                                                    <?= esc($appointment['dentist_name'] ?? 'Unassigned') ?>
                                                </div>
                                                <?php if (!empty($appointment['patient_phone'])): ?>
                                                <div class="flex items-center">
                                                    <i class="fas fa-phone mr-1"></i>
                                                    <?= esc($appointment['patient_phone']) ?>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <div class="flex items-center space-x-3">
                                        <?php if (in_array($appointment['status'], ['confirmed', 'scheduled'])): ?>
                                            <button onclick="checkinPatient(<?= $appointment['id'] ?>)" 
                                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                                <i class="fas fa-user-check mr-2"></i>
                                                Check In
                                            </button>
                                        <?php elseif ($appointment['status'] === 'checked_in'): ?>
                                            <div class="flex items-center text-green-600">
                                                <i class="fas fa-check-circle mr-2"></i>
                                                <span class="text-sm font-medium">Checked In</span>
                                            </div>
                                        <?php elseif ($appointment['status'] === 'ongoing'): ?>
                                            <div class="flex items-center text-yellow-600">
                                                <i class="fas fa-procedures mr-2 pulse-animation"></i>
                                                <span class="text-sm font-medium">In Treatment</span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Appointment Menu -->
                                        <div class="relative">
                                            <button type="button" class="text-gray-400 hover:text-gray-600 p-2" onclick="toggleMenu(<?= $appointment['id'] ?>)">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div id="menu-<?= $appointment['id'] ?>" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                                                <div class="py-1">
                                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        <i class="fas fa-eye mr-2"></i>View Details
                                                    </a>
                                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        <i class="fas fa-edit mr-2"></i>Edit Appointment
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script>
        // Update time every second
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit',
                hour12: true 
            });
            document.getElementById('current-time').textContent = timeString;
        }
        setInterval(updateTime, 1000);

        // Show alert message
        function showAlert(message, type = 'success') {
            const alertContainer = document.getElementById('alert-container');
            const alertClass = type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800';
            const iconClass = type === 'success' ? 'fas fa-check-circle text-green-400' : 'fas fa-exclamation-circle text-red-400';
            
            alertContainer.innerHTML = `
                <div class="rounded-md border p-4 ${alertClass}">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="${iconClass}"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium">${message}</p>
                        </div>
                        <div class="ml-auto pl-3">
                            <button type="button" class="inline-flex rounded-md p-1.5 hover:bg-opacity-20" onclick="this.parentElement.parentElement.parentElement.remove()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            // Auto hide after 5 seconds
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 5000);
        }

        // Check in patient
        async function checkinPatient(appointmentId) {
            try {
                const button = document.querySelector(`[data-appointment-id="${appointmentId}"] button`);
                const originalText = button.innerHTML;
                
                // Show loading state
                button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Checking In...';
                button.disabled = true;
                
                const response = await fetch(`/checkin/checkinPatient/${appointmentId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert(result.message || 'Patient checked in successfully!', 'success');
                    
                    // Update the appointment card
                    const card = document.querySelector(`[data-appointment-id="${appointmentId}"]`);
                    const statusBadge = card.querySelector('.status-badge');
                    statusBadge.className = 'status-badge status-checked-in';
                    statusBadge.textContent = 'Checked In';
                    
                    // Replace button with status
                    button.parentElement.innerHTML = `
                        <div class="flex items-center text-green-600">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span class="text-sm font-medium">Checked In</span>
                        </div>
                    `;
                    
                    // Update stats
                    updateStats();
                } else {
                    showAlert(result.message || 'Failed to check in patient', 'error');
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            } catch (error) {
                console.error('Check-in error:', error);
                showAlert('An error occurred during check-in', 'error');
                
                // Restore button
                const button = document.querySelector(`[data-appointment-id="${appointmentId}"] button`);
                if (button) {
                    button.innerHTML = '<i class="fas fa-user-check mr-2"></i>Check In';
                    button.disabled = false;
                }
            }
        }

        // Update statistics
        function updateStats() {
            const appointments = document.querySelectorAll('.appointment-card');
            let pending = 0, checkedIn = 0, inTreatment = 0;
            
            appointments.forEach(card => {
                const status = card.querySelector('.status-badge').textContent.toLowerCase();
                if (status === 'confirmed' || status === 'scheduled') pending++;
                else if (status === 'checked in') checkedIn++;
                else if (status === 'ongoing') inTreatment++;
            });
            
            document.getElementById('pending-checkin').textContent = pending;
            document.getElementById('checked-in').textContent = checkedIn;
            document.getElementById('in-treatment').textContent = inTreatment;
        }

        // Refresh appointments
        function refreshAppointments() {
            window.location.reload();
        }

        // Toggle menu
        function toggleMenu(appointmentId) {
            const menu = document.getElementById(`menu-${appointmentId}`);
            menu.classList.toggle('hidden');
            
            // Close other menus
            document.querySelectorAll('[id^="menu-"]').forEach(m => {
                if (m.id !== `menu-${appointmentId}`) {
                    m.classList.add('hidden');
                }
            });
        }

        // Close menus when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('[onclick^="toggleMenu"]')) {
                document.querySelectorAll('[id^="menu-"]').forEach(m => {
                    m.classList.add('hidden');
                });
            }
        });

        // Auto refresh every 30 seconds
        setInterval(refreshAppointments, 30000);
        
        // Check for flash messages
        <?php if (session()->getFlashdata('success')): ?>
            showAlert('<?= session()->getFlashdata('success') ?>', 'success');
        <?php endif; ?>
        
        <?php if (session()->getFlashdata('error')): ?>
            showAlert('<?= session()->getFlashdata('error') ?>', 'error');
        <?php endif; ?>
    </script>
</body>
</html>
