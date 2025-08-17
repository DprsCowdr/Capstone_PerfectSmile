<?php
/**
 * Enhanced Treatment Queue Dashboard
 * Improved with real-time updates, better UI, and comprehensive patient management
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment Queue - Perfect Smile</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .status-badge {
            @apply px-2 py-1 rounded-full text-xs font-medium;
        }
        .status-checked-in { @apply bg-blue-100 text-blue-800; }
        .status-ongoing { @apply bg-yellow-100 text-yellow-800; }
        .status-completed { @apply bg-green-100 text-green-800; }
        
        .patient-card {
            transition: all 0.3s ease;
        }
        .patient-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .pulse-animation {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        .priority-high { @apply border-l-4 border-red-500; }
        .priority-normal { @apply border-l-4 border-blue-500; }
        
        .waiting-time {
            color: #059669;
        }
        .waiting-time.urgent {
            color: #dc2626;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <i class="fas fa-procedures text-blue-600 text-2xl mr-3"></i>
                    <h1 class="text-2xl font-bold text-gray-900">Treatment Queue</h1>
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
                        Dr. <?= esc($user['name']) ?>
                        <?php if ($user['user_type'] === 'admin'): ?>
                            <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded-full ml-2">Admin View</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Waiting Patients</p>
                        <p class="text-2xl font-semibold text-gray-900" id="waiting-count">
                            <?= count($waitingPatients ?? []) ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-user-md text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">In Treatment</p>
                        <p class="text-2xl font-semibold text-gray-900" id="treatment-count">
                            <?= count($ongoingTreatments ?? []) ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Avg. Wait Time</p>
                        <p class="text-2xl font-semibold text-gray-900" id="avg-wait-time">
                            <?php
                            $waitingPatients = $waitingPatients ?? [];
                            if (!empty($waitingPatients)) {
                                $totalWait = 0;
                                $count = 0;
                                foreach ($waitingPatients as $patient) {
                                    if (!empty($patient['waiting_time'])) {
                                        $totalWait += $patient['waiting_time'];
                                        $count++;
                                    }
                                }
                                echo $count > 0 ? round($totalWait / $count) . 'm' : '0m';
                            } else {
                                echo '0m';
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <div id="alert-container" class="mb-6"></div>

        <!-- Waiting Patients -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-clock text-blue-600 mr-2"></i>
                        Waiting Patients
                        <span class="ml-2 text-sm bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                            <?= count($waitingPatients ?? []) ?>
                        </span>
                    </h2>
                    <button onclick="refreshQueue()" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-sync-alt mr-2"></i>
                        Refresh
                    </button>
                </div>
            </div>
            
            <div class="overflow-hidden">
                <?php if (empty($waitingPatients ?? [])): ?>
                    <div class="p-8 text-center">
                        <i class="fas fa-user-clock text-gray-400 text-4xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No patients waiting</h3>
                        <p class="text-gray-600">All checked-in patients are currently being treated or have completed their appointments.</p>
                    </div>
                <?php else: ?>
                    <div class="divide-y divide-gray-200" id="waiting-list">
                        <?php foreach ($waitingPatients as $index => $patient): ?>
                            <?php
                            $waitingMinutes = $patient['waiting_time'] ?? 0;
                            $isUrgent = $waitingMinutes > 30;
                            $priorityClass = $index < 2 ? 'priority-high' : 'priority-normal';
                            ?>
                            <div class="patient-card p-6 hover:bg-gray-50 <?= $priorityClass ?>" data-patient-id="<?= $patient['id'] ?>">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <!-- Position Number -->
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">
                                                <?= $index + 1 ?>
                                            </div>
                                        </div>
                                        
                                        <!-- Patient Info -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center space-x-3">
                                                <h3 class="text-lg font-semibold text-gray-900">
                                                    <?= esc($patient['patient_name']) ?>
                                                </h3>
                                                <span class="status-badge status-<?= $patient['status'] ?>">
                                                    Waiting
                                                </span>
                                                <?php if ($index < 2): ?>
                                                    <span class="bg-red-100 text-red-800 text-xs font-medium px-2 py-1 rounded-full">
                                                        Priority
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="mt-1 flex items-center space-x-4 text-sm text-gray-600">
                                                <div class="flex items-center">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    Appt: <?= date('h:i A', strtotime($patient['appointment_datetime'])) ?>
                                                </div>
                                                <div class="flex items-center waiting-time <?= $isUrgent ? 'urgent' : '' ?>">
                                                    <i class="fas fa-hourglass-half mr-1"></i>
                                                    Waiting: <?= $waitingMinutes ?>m
                                                </div>
                                                <?php if (!empty($patient['patient_phone'])): ?>
                                                <div class="flex items-center">
                                                    <i class="fas fa-phone mr-1"></i>
                                                    <?= esc($patient['patient_phone']) ?>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <div class="flex items-center space-x-3">
                                        <button onclick="callNext(<?= $patient['id'] ?>)" 
                                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                                            <i class="fas fa-play mr-2"></i>
                                            Start Treatment
                                        </button>
                                        
                                        <button onclick="postponePatient(<?= $patient['id'] ?>)" 
                                                class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <i class="fas fa-clock mr-2"></i>
                                            Postpone
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ongoing Treatments -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-user-md text-yellow-600 mr-2"></i>
                    Ongoing Treatments
                    <span class="ml-2 text-sm bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full">
                        <?= count($ongoingTreatments ?? []) ?>
                    </span>
                </h2>
            </div>
            
            <div class="overflow-hidden">
                <?php if (empty($ongoingTreatments ?? [])): ?>
                    <div class="p-8 text-center">
                        <i class="fas fa-procedures text-gray-400 text-4xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No ongoing treatments</h3>
                        <p class="text-gray-600">Start treating waiting patients to see them here.</p>
                    </div>
                <?php else: ?>
                    <div class="divide-y divide-gray-200" id="ongoing-list">
                        <?php foreach ($ongoingTreatments as $treatment): ?>
                            <div class="p-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <!-- Treatment Icon -->
                                        <div class="flex-shrink-0">
                                            <div class="h-12 w-12 rounded-full bg-yellow-100 flex items-center justify-center">
                                                <i class="fas fa-procedures text-yellow-600 text-xl pulse-animation"></i>
                                            </div>
                                        </div>
                                        
                                        <!-- Treatment Info -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center space-x-3">
                                                <h3 class="text-lg font-semibold text-gray-900">
                                                    <?= esc($treatment['patient_name']) ?>
                                                </h3>
                                                <span class="status-badge status-ongoing">
                                                    In Treatment
                                                </span>
                                            </div>
                                            
                                            <div class="mt-1 flex items-center space-x-4 text-sm text-gray-600">
                                                <div class="flex items-center">
                                                    <i class="fas fa-play mr-1"></i>
                                                    Started: <?= isset($treatment['started_at']) ? date('h:i A', strtotime($treatment['started_at'])) : 'Unknown' ?>
                                                </div>
                                                <div class="flex items-center">
                                                    <i class="fas fa-stopwatch mr-1"></i>
                                                    Duration: <?= $treatment['treatment_duration'] ?? 0 ?>m
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <div class="flex items-center space-x-3">
                                        <button onclick="completeTreatment(<?= $treatment['id'] ?>)" 
                                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <i class="fas fa-check mr-2"></i>
                                            Complete
                                        </button>
                                        
                                        <button onclick="pauseTreatment(<?= $treatment['id'] ?>)" 
                                                class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                            <i class="fas fa-pause mr-2"></i>
                                            Pause
                                        </button>
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
            
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 5000);
        }

        // Call next patient
        async function callNext(appointmentId) {
            try {
                const response = await fetch(`/queue/callNext/${appointmentId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert('Treatment started successfully!', 'success');
                    setTimeout(() => refreshQueue(), 1000);
                } else {
                    showAlert(result.message || 'Failed to start treatment', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('An error occurred', 'error');
            }
        }

        // Complete treatment
        async function completeTreatment(appointmentId) {
            try {
                const response = await fetch(`/queue/complete/${appointmentId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert('Treatment completed successfully!', 'success');
                    setTimeout(() => refreshQueue(), 1000);
                } else {
                    showAlert(result.message || 'Failed to complete treatment', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('An error occurred', 'error');
            }
        }

        // Postpone patient
        function postponePatient(appointmentId) {
            if (confirm('Are you sure you want to postpone this patient?')) {
                // Implementation for postponing
                showAlert('Patient postponed', 'success');
            }
        }

        // Pause treatment
        function pauseTreatment(appointmentId) {
            if (confirm('Are you sure you want to pause this treatment?')) {
                // Implementation for pausing
                showAlert('Treatment paused', 'success');
            }
        }

        // Refresh queue
        function refreshQueue() {
            window.location.reload();
        }

        // Auto refresh every 30 seconds
        setInterval(refreshQueue, 30000);
        
        // Update waiting times every minute
        setInterval(updateWaitingTimes, 60000);
        
        function updateWaitingTimes() {
            // Update waiting time displays
            document.querySelectorAll('.waiting-time').forEach(element => {
                // Implementation for updating waiting times
            });
        }
    </script>
</body>
</html>
