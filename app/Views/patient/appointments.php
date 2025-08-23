<?= view('templates/header') ?>

<div class="min-h-screen bg-gray-50 flex">
    <div class="flex-1 flex flex-col min-h-screen min-w-0 overflow-hidden">
        <!-- Topbar -->
        <nav class="flex items-center justify-between bg-white shadow px-6 py-4 mb-6 flex-shrink-0">
            <div class="flex items-center">
                <a href="<?= base_url('patient/dashboard') ?>" class="text-gray-600 hover:text-gray-800 mr-4">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <h1 class="text-xl font-semibold text-gray-800">My Appointments</h1>
            </div>
            <div class="flex items-center">
                <a href="<?= base_url('patient/book-appointment') ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-plus mr-2"></i>Book New Appointment
                </a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1 px-6 pb-6 overflow-auto min-w-0">
            <?php if (!empty($appointments)): ?>
                <div class="grid grid-cols-1 gap-6">
                    <?php foreach ($appointments as $appointment): ?>
                        <div class="bg-white rounded-lg shadow-lg p-6">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>
                                        <h3 class="text-lg font-semibold text-gray-800">
                                            <?= date('F j, Y', strtotime($appointment['appointment_datetime'])) ?>
                                        </h3>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                                        <div>
                                            <i class="fas fa-clock mr-2"></i>
                                            <strong>Time:</strong> <?= date('g:i A', strtotime($appointment['appointment_datetime'])) ?>
                                        </div>
                                        <div>
                                            <i class="fas fa-building mr-2"></i>
                                            <strong>Branch:</strong> <?= esc($appointment['branch_name'] ?? 'Not specified') ?>
                                        </div>
                                        <div>
                                            <i class="fas fa-user-md mr-2"></i>
                                            <strong>Dentist:</strong> <?= $appointment['dentist_name'] ? 'Dr. ' . esc($appointment['dentist_name']) : 'Not assigned yet' ?>
                                        </div>
                                        <div>
                                            <i class="fas fa-tag mr-2"></i>
                                            <strong>Type:</strong> <?= ucfirst($appointment['appointment_type'] ?? 'Scheduled') ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($appointment['remarks'])): ?>
                                        <div class="mt-3 text-sm text-gray-600">
                                            <i class="fas fa-comment mr-2"></i>
                                            <strong>Notes:</strong> <?= esc($appointment['remarks']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="text-right ml-4">
                                    <div class="mb-2">
                                        <?php 
                                        $statusClass = match($appointment['approval_status'] ?? 'pending') {
                                            'approved' => 'bg-green-100 text-green-800',
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'declined' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                        ?>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full <?= $statusClass ?>">
                                            <?= ucfirst($appointment['approval_status'] ?? 'Pending') ?>
                                        </span>
                                    </div>
                                    <div>
                                        <?php 
                                        $appointmentStatusClass = match($appointment['status'] ?? 'pending') {
                                            'confirmed', 'scheduled' => 'bg-blue-100 text-blue-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            'no_show' => 'bg-gray-100 text-gray-800',
                                            default => 'bg-yellow-100 text-yellow-800'
                                        };
                                        ?>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full <?= $appointmentStatusClass ?>">
                                            <?= ucfirst(str_replace('_', ' ', $appointment['status'] ?? 'Pending')) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No Appointments Yet</h3>
                    <p class="text-gray-500 mb-6">You haven't booked any appointments yet.</p>
                    <a href="<?= base_url('patient/book-appointment') ?>" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition inline-flex items-center">
                        <i class="fas fa-calendar-plus mr-2"></i>
                        Book Your First Appointment
                    </a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?= view('templates/footer') ?>
