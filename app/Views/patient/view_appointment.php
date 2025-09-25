<?php $user = $user ?? session('user') ?? []; ?>

<?= view('templates/header') ?>

<?= view('templates/sidebar', ['user' => $user ?? null]) ?>

<div class="min-h-screen bg-white flex">
    <div class="flex-1 flex flex-col min-h-screen bg-white">
        <?= view('templates/patient_topbar', ['user' => $user ?? null]) ?>

        <!-- Main Content -->
        <main class="flex-1 px-6 pb-6" data-sidebar-offset>
            <div class="max-w-4xl mx-auto">
                <!-- Header with Back Button -->
                <div class="flex items-center mb-6">
                    <a href="<?= base_url('patient/appointments') ?>" 
                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to My Appointments
                    </a>
                    <h1 class="text-2xl font-semibold text-gray-800 ml-4">Appointment Details</h1>
                </div>

                <!-- Appointment Details Card -->
                <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
                    <!-- Header Section -->
                    <div class="bg-gradient-to-r from-indigo-500 to-blue-600 px-6 py-4 text-white">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-xl font-semibold">
                                    <?= date('F j, Y', strtotime($appointment['appointment_datetime'])) ?>
                                </h2>
                                <p class="text-indigo-100 mt-1">
                                    <i class="fas fa-clock mr-2"></i>
                                    <?= date('g:i A', strtotime($appointment['appointment_datetime'])) ?>
                                </p>
                            </div>
                            <div class="mt-3 sm:mt-0 flex flex-col sm:items-end">
                                <?php 
                                $statusClass = match($appointment['approval_status'] ?? 'pending') {
                                    'approved' => 'bg-green-500 text-white',
                                    'pending' => 'bg-yellow-500 text-white',
                                    'declined' => 'bg-red-500 text-white',
                                    default => 'bg-gray-500 text-white'
                                };
                                ?>
                                <span class="inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full <?= $statusClass ?>">
                                    <i class="fas fa-check-circle mr-1" style="font-size: 12px;"></i>
                                    <?= ucfirst($appointment['approval_status'] ?? 'Pending') ?>
                                </span>
                                
                                <?php 
                                $appointmentStatusClass = match($appointment['status'] ?? 'pending') {
                                    'confirmed', 'scheduled' => 'bg-blue-500 text-white',
                                    'completed' => 'bg-green-500 text-white',
                                    'cancelled' => 'bg-red-500 text-white',
                                    'no_show' => 'bg-gray-500 text-white',
                                    default => 'bg-yellow-500 text-white'
                                };
                                ?>
                                <span class="inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full <?= $appointmentStatusClass ?> mt-2">
                                    <?php
                                    $statusIcon = match($appointment['status'] ?? 'pending') {
                                        'confirmed', 'scheduled' => 'fas fa-calendar-check',
                                        'completed' => 'fas fa-check-double',
                                        'cancelled' => 'fas fa-times-circle',
                                        'no_show' => 'fas fa-user-times',
                                        default => 'fas fa-clock'
                                    };
                                    ?>
                                    <i class="<?= $statusIcon ?> mr-1" style="font-size: 12px;"></i>
                                    <?= ucfirst(str_replace('_', ' ', $appointment['status'] ?? 'Pending')) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Content Section -->
                    <div class="p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Left Column: Basic Info -->
                            <div class="space-y-6">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                        <i class="fas fa-info-circle text-indigo-500 mr-2"></i>
                                        Basic Information
                                    </h3>
                                    <div class="space-y-3">
                                        <div class="flex items-center">
                                            <i class="fas fa-building text-gray-400 mr-3 w-5"></i>
                                            <div>
                                                <span class="text-sm font-medium text-gray-600">Branch:</span>
                                                <span class="ml-2 text-gray-800"><?= esc($appointment['branch_name'] ?? 'Not specified') ?></span>
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <i class="fas fa-user-md text-gray-400 mr-3 w-5"></i>
                                            <div>
                                                <span class="text-sm font-medium text-gray-600">Dentist:</span>
                                                <span class="ml-2 text-gray-800">
                                                    <?= (isset($appointment['dentist_name']) && $appointment['dentist_name']) ? ('Dr. ' . esc($appointment['dentist_name'])) : 'Not assigned yet' ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <i class="fas fa-tag text-gray-400 mr-3 w-5"></i>
                                            <div>
                                                <span class="text-sm font-medium text-gray-600">Type:</span>
                                                <span class="ml-2 text-gray-800"><?= ucfirst($appointment['appointment_type'] ?? 'Scheduled') ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php if (!empty($appointment['remarks'])): ?>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                        <i class="fas fa-comment text-indigo-500 mr-2"></i>
                                        Notes
                                    </h3>
                                    <div class="bg-gray-50 p-4 rounded-lg border-l-4 border-indigo-300">
                                        <p class="text-gray-700"><?= esc($appointment['remarks']) ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Right Column: Services -->
                            <div class="space-y-6">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                        <i class="fas fa-dental text-indigo-500 mr-2"></i>
                                        Services
                                    </h3>
                                    <?php if (!empty($services)): ?>
                                        <div class="space-y-3">
                                            <?php 
                                            $totalDuration = 0;
                                            $totalPrice = 0;
                                            ?>
                                            <?php foreach ($services as $service): ?>
                                                <?php 
                                                $totalDuration += $service['duration_minutes'] ?? 0;
                                                // Prices intentionally ignored for patient-facing view
                                                ?>
                                                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-sm transition-shadow duration-200">
                                                    <div class="flex items-start">
                                                        <div class="flex-1">
                                                            <h4 class="font-medium text-gray-800"><?= esc($service['name']) ?></h4>
                                                            <!-- Service duration hidden to avoid redundant durations; show only in summary -->
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                            
                                            <!-- Summary -->
                                            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 mt-4">
                                                <div class="flex justify-between items-center">
                                                    <div>
                                                        <span class="font-medium text-indigo-800">Total Duration:</span>
                                                        <span class="ml-2 text-indigo-700"><?= $totalDuration ?> minutes</span>
                                                    </div>
                                                    <!-- Pricing removed from patient-facing view per request -->
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="bg-gray-50 p-4 rounded-lg text-center">
                                            <i class="fas fa-dental text-gray-300 text-3xl mb-3"></i>
                                            <p class="text-gray-500">No services specified for this appointment</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Special Notices -->
                        <?php if (($appointment['pending_change'] ?? 0) == 1): ?>
                        <div class="mt-6 bg-orange-50 border border-orange-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <i class="fas fa-clock text-orange-500 mr-3"></i>
                                <div>
                                    <h4 class="font-medium text-orange-800">Change Pending Review</h4>
                                    <p class="text-orange-700 text-sm mt-1">A change request for this appointment is currently being reviewed by clinic staff.</p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Cancellation Notice -->
                        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle text-blue-500 mr-3"></i>
                                <div>
                                    <h4 class="font-medium text-blue-800">Need to Cancel?</h4>
                                    <p class="text-blue-700 text-sm mt-1">To cancel or reschedule this appointment, please contact the clinic directly. Online cancellations are not available through the patient portal.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?= view('templates/footer') ?>

<script>
// Add smooth interactions
(() => {
    // Add hover effects to service cards
    const serviceCards = document.querySelectorAll('.bg-white.border.border-gray-200');
    serviceCards.forEach(card => {
        card.style.transition = 'all 0.2s ease';
    });

    // Enhanced back button
    const backButton = document.querySelector('a[href*="patient/appointments"]');
    if (backButton) {
        backButton.addEventListener('click', (e) => {
            // Add loading state
            const originalContent = backButton.innerHTML;
            backButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
            backButton.style.pointerEvents = 'none';
            
            // Reset after delay in case navigation is slow
            setTimeout(() => {
                backButton.innerHTML = originalContent;
                backButton.style.pointerEvents = 'auto';
            }, 3000);
        });
    }
})();
</script>