<?= view('templates/header') ?>
<div class="min-h-screen bg-[#F5ECFE] flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <main class="flex-1 px-6 py-8">
            <!-- Welcome Section -->
            <div class="mb-8">
                <h1 class="text-4xl font-extrabold text-purple-700 tracking-tight mb-2">
                    👨‍⚕️ Dentist Dashboard
                </h1>
                <p class="text-gray-600">Welcome back, Dr. <?= $user['name'] ?>! Here's your schedule and pending approvals.</p>
            </div>

            <!-- Notification Badge -->
            <?php if (count($pendingAppointments) > 0): ?>
            <div class="bg-orange-100 border border-orange-400 text-orange-800 px-4 py-3 rounded-lg mb-6">
                <div class="flex items-center">
                    <i class="fas fa-bell text-orange-600 mr-3"></i>
                    <div>
                        <strong>Pending Approvals:</strong> You have <?= count($pendingAppointments) ?> appointment(s) waiting for your approval.
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500 text-sm">Today's Appointments</p>
                            <p class="text-2xl font-bold text-gray-800"><?= count($todayAppointments) ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center">
                        <div class="p-3 bg-orange-100 rounded-lg">
                            <i class="fas fa-clock text-orange-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500 text-sm">Pending Approvals</p>
                            <p class="text-2xl font-bold text-gray-800"><?= count($pendingAppointments) ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <i class="fas fa-calendar-week text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500 text-sm">This Week</p>
                            <p class="text-2xl font-bold text-gray-800"><?= count($upcomingAppointments) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Approvals Section -->
            <?php if (!empty($pendingAppointments)): ?>
            <div class="bg-white rounded-xl shadow-lg mb-8">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-clock text-orange-500 mr-3"></i>
                        Pending Approvals
                    </h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <?php foreach ($pendingAppointments as $appointment): ?>
                        <div class="border border-gray-200 rounded-lg p-4 bg-orange-50">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h3 class="font-semibold text-gray-800"><?= $appointment['patient_name'] ?></h3>
                                    <p class="text-sm text-gray-600"><?= $appointment['patient_email'] ?></p>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-500"><?= date('M j, Y', strtotime($appointment['appointment_datetime'])) ?></div>
                                    <div class="font-semibold text-gray-800"><?= date('g:i A', strtotime($appointment['appointment_datetime'])) ?></div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-600">
                                    <i class="fas fa-building mr-1"></i> <?= $appointment['branch_name'] ?>
                                </div>
                                <div class="flex gap-2">
                                    <button onclick="approveAppointment(<?= $appointment['id'] ?>)" 
                                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                                        <i class="fas fa-check mr-1"></i> Approve
                                    </button>
                                    <button onclick="declineAppointment(<?= $appointment['id'] ?>)" 
                                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                                        <i class="fas fa-times mr-1"></i> Decline
                                    </button>
                                </div>
                            </div>
                            <?php if ($appointment['remarks']): ?>
                            <div class="mt-3 text-sm text-gray-600 italic">
                                <i class="fas fa-comment mr-1"></i> <?= $appointment['remarks'] ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Today's Schedule -->
            <div class="bg-white rounded-xl shadow-lg mb-8">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-calendar-day text-blue-500 mr-3"></i>
                        Today's Schedule
                    </h2>
                </div>
                <div class="p-6">
                    <?php if (!empty($todayAppointments)): ?>
                    <div class="space-y-4">
                        <?php foreach ($todayAppointments as $appointment): ?>
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="font-semibold text-gray-800"><?= $appointment['patient_name'] ?></h3>
                                    <p class="text-sm text-gray-600"><?= $appointment['patient_email'] ?></p>
                                    <p class="text-sm text-gray-500"><?= $appointment['branch_name'] ?></p>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-gray-800"><?= date('g:i A', strtotime($appointment['appointment_datetime'])) ?></div>
                                    <span class="px-2 py-1 text-xs rounded-full <?= $appointment['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' ?>">
                                        <?= ucfirst($appointment['status']) ?>
                                    </span>
                                    <div class="mt-2 space-x-2">
                                        <a href="<?= base_url('dentist/dental-chart/' . $appointment['id']) ?>" class="bg-blue-500 text-white px-3 py-1 rounded text-xs hover:bg-blue-600">
                                            🦷 Chart
                                        </a>
                                        <a href="<?= base_url('dentist/patient-records/' . $appointment['user_id']) ?>" class="bg-green-500 text-white px-3 py-1 rounded text-xs hover:bg-green-600">
                                            📋 Records
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php if ($appointment['remarks']): ?>
                            <div class="mt-3 text-sm text-gray-600 italic">
                                <i class="fas fa-comment mr-1"></i> <?= $appointment['remarks'] ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-calendar-times text-4xl mb-4"></i>
                        <p>No appointments scheduled for today</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Upcoming Appointments -->
            <div class="bg-white rounded-xl shadow-lg">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-calendar-week text-green-500 mr-3"></i>
                        Upcoming Appointments (Next 7 Days)
                    </h2>
                </div>
                <div class="p-6">
                    <?php if (!empty($upcomingAppointments)): ?>
                    <div class="space-y-4">
                        <?php foreach ($upcomingAppointments as $appointment): ?>
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="font-semibold text-gray-800"><?= $appointment['patient_name'] ?></h3>
                                    <p class="text-sm text-gray-600"><?= $appointment['patient_email'] ?></p>
                                    <p class="text-sm text-gray-500"><?= $appointment['branch_name'] ?></p>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-500"><?= date('M j, Y', strtotime($appointment['appointment_datetime'])) ?></div>
                                    <div class="text-lg font-bold text-gray-800"><?= date('g:i A', strtotime($appointment['appointment_datetime'])) ?></div>
                                    <span class="px-2 py-1 text-xs rounded-full <?= $appointment['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' ?>">
                                        <?= ucfirst($appointment['status']) ?>
                                    </span>
                                    <div class="mt-2 space-x-2">
                                        <a href="<?= base_url('dentist/dental-chart/' . $appointment['id']) ?>" class="bg-blue-500 text-white px-3 py-1 rounded text-xs hover:bg-blue-600">
                                            🦷 Chart
                                        </a>
                                        <a href="<?= base_url('dentist/patient-records/' . $appointment['user_id']) ?>" class="bg-green-500 text-white px-3 py-1 rounded text-xs hover:bg-green-600">
                                            📋 Records
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php if ($appointment['remarks']): ?>
                            <div class="mt-3 text-sm text-gray-600 italic">
                                <i class="fas fa-comment mr-1"></i> <?= $appointment['remarks'] ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-calendar-times text-4xl mb-4"></i>
                        <p>No upcoming appointments</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function approveAppointment(appointmentId) {
    if (confirm('Are you sure you want to approve this appointment?')) {
        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        
        fetch(`<?= base_url() ?>dentist/appointments/approve/${appointmentId}`, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                location.reload();
            } else {
                alert('Failed to approve appointment');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to approve appointment');
        });
    }
}

function declineAppointment(appointmentId) {
    const reason = prompt('Please provide a reason for declining this appointment:');
    if (reason) {
        const formData = new FormData();
        formData.append('reason', reason);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        
        fetch(`<?= base_url() ?>dentist/appointments/decline/${appointmentId}`, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                location.reload();
            } else {
                alert('Failed to decline appointment');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to decline appointment');
        });
    }
}
</script>

<?= view('templates/footer') ?> 