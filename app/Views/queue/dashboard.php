<?= view('templates/header') ?>

<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <!-- Topbar -->
        <nav class="flex items-center justify-between bg-white shadow px-6 py-4 mb-6">
            <button id="sidebarToggleTop" class="block lg:hidden text-gray-600 mr-3 text-2xl focus:outline-none">
                <i class="fa fa-bars"></i>
            </button>
            <div class="flex items-center ml-auto">
                <span class="mr-4 hidden lg:inline text-gray-600 font-semibold"><?= $user['name'] ?? 'Dentist' ?></span>
                <div class="relative">
                    <button class="focus:outline-none">
                        <img class="w-10 h-10 rounded-full border-2 border-gray-200" src="<?= base_url('img/undraw_profile.svg') ?>" alt="Profile">
                    </button>
                </div>
            </div>
        </nav>
        
        <main class="flex-1 p-8">
                <!-- Header -->
                <div class="bg-gradient-to-r from-green-600 to-blue-600 rounded-xl shadow-lg mb-8">
                    <div class="p-6 text-white">
                        <h1 class="text-3xl font-bold flex items-center">
                            <i class="fas fa-users mr-4"></i>
                            Treatment Queue
                        </h1>
                        <p class="mt-2 opacity-90">Manage your patient queue and call patients for treatment</p>
                    </div>
                </div>

                <!-- Queue Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                <i class="fas fa-clock text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Patients Waiting</p>
                                <p class="text-2xl font-bold text-gray-900"><?= count($waitingPatients) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i class="fas fa-user-md text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">In Treatment</p>
                                <p class="text-2xl font-bold text-gray-900"><?= count($ongoingTreatments) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <i class="fas fa-stopwatch text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Avg Wait Time</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    <?php
                                    $avgWaitTime = 0;
                                    if (!empty($waitingPatients)) {
                                        $totalWaitTime = array_sum(array_column($waitingPatients, 'waiting_time'));
                                        $avgWaitTime = round($totalWaitTime / count($waitingPatients));
                                    }
                                    echo $avgWaitTime . 'm';
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Patients Waiting -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <div class="bg-white rounded-xl shadow-lg">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-hourglass-half mr-3 text-yellow-500"></i>
                                Patients Waiting
                            </h2>
                            <p class="text-sm text-gray-600 mt-1">Checked-in patients ready for treatment</p>
                        </div>
                        
                        <div class="p-6">
                            <?php if (empty($waitingPatients)): ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-user-clock text-4xl text-gray-400 mb-4"></i>
                                    <p class="text-gray-500">No patients waiting</p>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($waitingPatients as $patient): ?>
                                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                                            <div class="flex items-center justify-between">
                                                <div class="flex-1">
                                                    <h3 class="font-medium text-gray-900"><?= esc($patient['patient_name']) ?></h3>
                                                    <p class="text-sm text-gray-600">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        Appointment: <?= date('g:i A', strtotime($patient['appointment_datetime'])) ?>
                                                    </p>
                                                    <p class="text-sm text-gray-600">
                                                        <i class="fas fa-phone mr-1"></i>
                                                        <?= esc($patient['patient_phone']) ?>
                                                    </p>
                                                    <p class="text-xs text-yellow-600 font-medium mt-1">
                                                        <i class="fas fa-hourglass-half mr-1"></i>
                                                        Waiting: <?= $patient['waiting_time'] ?? 0 ?> minutes
                                                    </p>
                                                </div>
                                                <div class="ml-4 flex items-center space-x-2">
                                                    <form method="POST" action="<?= base_url('queue/call/' . $patient['id']) ?>" class="inline treatment-call-form">
                                                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                            <i class="fas fa-hand-paper mr-2"></i>
                                                            Call Patient
                                                        </button>
                                                    </form>

                                                    <button type="button" onclick="reschedulePatient(<?= $patient['id'] ?>)" class="inline-flex items-center px-3 py-2 border border-purple-600 text-sm font-medium rounded-md text-purple-700 bg-white hover:bg-purple-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                                        <i class="fas fa-calendar-plus mr-2"></i>
                                                        Reschedule
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
                    <div class="bg-white rounded-xl shadow-lg">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-user-md mr-3 text-green-500"></i>
                                Ongoing Treatments
                            </h2>
                            <p class="text-sm text-gray-600 mt-1">Patients currently being treated</p>
                        </div>
                        
                        <div class="p-6">
                            <?php if (empty($ongoingTreatments)): ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-stethoscope text-4xl text-gray-400 mb-4"></i>
                                    <p class="text-gray-500">No ongoing treatments</p>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($ongoingTreatments as $treatment): ?>
                                        <div class="border border-gray-200 rounded-lg p-4 bg-green-50">
                                            <div class="flex items-center justify-between">
                                                <div class="flex-1">
                                                    <h3 class="font-medium text-gray-900"><?= esc($treatment['patient_name']) ?></h3>
                                                    <p class="text-sm text-gray-600">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        Started: <?= date('g:i A', strtotime($treatment['appointment_datetime'])) ?>
                                                    </p>
                                                    <p class="text-xs text-green-600 font-medium mt-1">
                                                        <i class="fas fa-play-circle mr-1"></i>
                                                        Duration: <?= $treatment['treatment_duration'] ?? 0 ?> minutes
                                                    </p>
                                                </div>
                                                <div class="ml-4">
                                                    <a href="<?= base_url('checkup/patient/' . $treatment['id']) ?>" 
                                                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                        <i class="fas fa-eye mr-2"></i>
                                                        Continue
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mt-8 bg-white rounded-xl shadow-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-lightning-bolt mr-3"></i>
                            Quick Actions
                        </h2>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <a href="<?= base_url('checkin') ?>" 
                               class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                <i class="fas fa-sign-in-alt text-blue-500 text-xl mr-3"></i>
                                <div>
                                    <p class="font-medium text-gray-900">Check-In Dashboard</p>
                                    <p class="text-sm text-gray-600">Manage patient arrivals</p>
                                </div>
                            </a>
                            
                            <a href="<?= base_url('checkup') ?>" 
                               class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                <i class="fas fa-stethoscope text-green-500 text-xl mr-3"></i>
                                <div>
                                    <p class="font-medium text-gray-900">Checkup Module</p>
                                    <p class="text-sm text-gray-600">Start patient examinations</p>
                                </div>
                            </a>
                            
                            <a href="<?= base_url('admin/dental-records') ?>" 
                               class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                <i class="fas fa-file-medical text-blue-600 text-xl mr-3"></i>
                                <div>
                                    <p class="font-medium text-gray-900">Dental Records</p>
                                    <p class="text-sm text-gray-600">View patient records</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<script>
// Auto-refresh every 15 seconds for real-time updates
let autoRefreshTimer = setTimeout(function() {
    window.location.reload();
}, 15000);

// Clear timer if user interacts with the page
document.addEventListener('click', function() {
    clearTimeout(autoRefreshTimer);
    // Restart the timer
    autoRefreshTimer = setTimeout(function() {
        window.location.reload();
    }, 15000);
});

// Confirmation for calling patients (only for call forms)
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.treatment-call-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!confirm('Call this patient for treatment?')) {
                e.preventDefault();
            }
        });
    });
    
    // Sidebar toggle functionality
    const sidebarToggle = document.getElementById('sidebarToggleTop');
    const sidebar = document.getElementById('sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('sidebar-collapsed');
        });
    }
});
</script>

<?= view('templates/footer') ?>
