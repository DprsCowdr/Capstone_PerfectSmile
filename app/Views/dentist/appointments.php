<?= view('templates/header') ?>
<div class="min-h-screen bg-[#F5ECFE] flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <main class="flex-1 px-6 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-extrabold text-purple-700 tracking-tight mb-2">
                    📅 My Appointments
                </h1>
                <p class="text-gray-600">Manage your appointments and patient schedules</p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <?php
                $totalAppointments = count($appointments);
                $pendingAppointments = array_filter($appointments, fn($apt) => $apt['approval_status'] === 'pending');
                $confirmedAppointments = array_filter($appointments, fn($apt) => $apt['status'] === 'confirmed');
                $completedAppointments = array_filter($appointments, fn($apt) => $apt['status'] === 'completed');
                ?>
                
                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <i class="fas fa-calendar text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500 text-sm">Total</p>
                            <p class="text-2xl font-bold text-gray-800"><?= $totalAppointments ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center">
                        <div class="p-3 bg-orange-100 rounded-lg">
                            <i class="fas fa-clock text-orange-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500 text-sm">Pending</p>
                            <p class="text-2xl font-bold text-gray-800"><?= count($pendingAppointments) ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <i class="fas fa-check text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500 text-sm">Confirmed</p>
                            <p class="text-2xl font-bold text-gray-800"><?= count($confirmedAppointments) ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <i class="fas fa-check-double text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500 text-sm">Completed</p>
                            <p class="text-2xl font-bold text-gray-800"><?= count($completedAppointments) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Appointments Table -->
            <div class="bg-white rounded-xl shadow-lg">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800">All Appointments</h2>
                </div>
                <div class="p-6">
                    <?php if (!empty($appointments)): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Patient</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Date & Time</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Branch</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Type</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Approval</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $appointment): ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-4 px-4">
                                        <div>
                                            <div class="font-semibold text-gray-800"><?= $appointment['patient_name'] ?></div>
                                            <div class="text-sm text-gray-600"><?= $appointment['patient_email'] ?></div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div>
                                            <div class="font-semibold text-gray-800"><?= date('M j, Y', strtotime($appointment['appointment_date'])) ?></div>
                                            <div class="text-sm text-gray-600"><?= date('g:i A', strtotime($appointment['appointment_time'])) ?></div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="text-gray-700"><?= $appointment['branch_name'] ?></div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <?php
                                        $typeClass = $appointment['appointment_type'] === 'walkin' ? 'bg-indigo-100 text-indigo-800' : 'bg-purple-100 text-purple-800';
                                        $typeText = ucfirst($appointment['appointment_type'] ?? 'scheduled');
                                        ?>
                                        <span class="px-2 py-1 text-xs rounded-full <?= $typeClass ?>">
                                            <?= $typeText ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <?php
                                        $statusClass = '';
                                        switch($appointment['status']) {
                                            case 'pending': $statusClass = 'bg-yellow-100 text-yellow-800'; break;
                                            case 'confirmed': $statusClass = 'bg-green-100 text-green-800'; break;
                                            case 'completed': $statusClass = 'bg-blue-100 text-blue-800'; break;
                                            case 'cancelled': $statusClass = 'bg-red-100 text-red-800'; break;
                                            default: $statusClass = 'bg-gray-100 text-gray-800';
                                        }
                                        ?>
                                        <span class="px-2 py-1 text-xs rounded-full <?= $statusClass ?>">
                                            <?= ucfirst($appointment['status']) ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <?php
                                        $approvalClass = '';
                                        switch($appointment['approval_status'] ?? 'pending') {
                                            case 'pending': $approvalClass = 'bg-orange-100 text-orange-800'; break;
                                            case 'approved': $approvalClass = 'bg-green-100 text-green-800'; break;
                                            case 'declined': $approvalClass = 'bg-red-100 text-red-800'; break;
                                            case 'auto_approved': $approvalClass = 'bg-blue-100 text-blue-800'; break;
                                            default: $approvalClass = 'bg-gray-100 text-gray-800';
                                        }
                                        $approvalText = str_replace('_', ' ', $appointment['approval_status'] ?? 'pending');
                                        ?>
                                        <span class="px-2 py-1 text-xs rounded-full <?= $approvalClass ?>">
                                            <?= ucfirst($approvalText) ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex gap-2">
                                            <?php if (($appointment['approval_status'] ?? 'pending') === 'pending'): ?>
                                            <button onclick="approveAppointment(<?= $appointment['id'] ?>)" 
                                                    class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition-colors">
                                                <i class="fas fa-check mr-1"></i> Approve
                                            </button>
                                            <button onclick="declineAppointment(<?= $appointment['id'] ?>)" 
                                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition-colors">
                                                <i class="fas fa-times mr-1"></i> Decline
                                            </button>
                                            <?php else: ?>
                                            <span class="text-gray-500 text-sm">No action needed</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-12 text-gray-500">
                        <i class="fas fa-calendar-times text-6xl mb-4"></i>
                        <p class="text-xl">No appointments found</p>
                        <p class="text-sm mt-2">You don't have any appointments assigned yet.</p>
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