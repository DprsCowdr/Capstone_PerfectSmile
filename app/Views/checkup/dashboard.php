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

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Add Record</h1>
                    <p class="mt-1 text-sm text-gray-500">Create or update patient records after treatment</p>
                </div>
           
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i>
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php 
                $error = session()->getFlashdata('error');
                if (is_array($error)) {
                    foreach ($error as $err) {
                        echo $err . '<br>';
                    }
                } else {
                    echo $error;
                }
                ?>
            </div>
        <?php endif; ?>

        <!-- Today's Date -->
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-2">
                <i class="fas fa-calendar-day text-blue-500 mr-2"></i>
                Today's Appointments - <?= date('l, F j, Y') ?>
            </h2>
        </div>

        <!-- Resume Ongoing Checkup -->
        <?php if (!empty($ongoingCheckup)): ?>
            <div class="mb-8">
                <div class="bg-yellow-50 border-l-4 border-yellow-400 rounded-xl shadow p-6 flex items-center justify-between">
                    <div>
                        <div class="text-lg font-bold text-yellow-800 mb-1 flex items-center">
                            <i class="fas fa-stethoscope mr-3"></i>Ongoing Treatment In Progress
                        </div>
                        <div class="text-gray-700 mb-1">
                            <span class="font-semibold">Patient:</span> <?= esc($ongoingCheckup['patient_name'] ?? 'Unknown') ?>
                        </div>
                        <div class="text-gray-600 text-sm">
                            <span class="font-semibold">Started at:</span> <?= isset($ongoingCheckup['appointment_datetime']) ? date('g:i A', strtotime($ongoingCheckup['appointment_datetime'])) : 'N/A' ?>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <a href="/checkup/patient/<?= $ongoingCheckup['id'] ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-3 rounded-lg font-semibold text-lg transition-colors flex items-center">
                            <i class="fas fa-play mr-2"></i>Resume Add Record
                        </a>
                        <a href="/admin/dental-charts/<?= $ongoingCheckup['id'] ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold text-lg transition-colors flex items-center">
                            <i class="fas fa-tooth mr-2"></i>View Chart
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Appointments Grid -->
        <?php if (!empty($appointments)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($appointments as $appointment): ?>
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <!-- Appointment Header -->
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="text-lg font-bold text-white"><?= $appointment['patient_name'] ?></h3>
                                    <p class="text-blue-100 text-sm"><?= $appointment['patient_email'] ?></p>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-white">
                                        <?= date('g:i A', strtotime($appointment['appointment_time'])) ?>
                                    </div>
                                    <div class="text-blue-100 text-sm">
                                        <?= $appointment['branch_name'] ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Patient Info -->
                        <div class="p-6">
                            <div class="space-y-3">
                                <div class="flex items-center">
                                    <i class="fas fa-phone text-gray-400 w-5"></i>
                                    <span class="text-sm text-gray-600 ml-2"><?= $appointment['patient_phone'] ?></span>
                                </div>
                                
                                <div class="flex items-center">
                                    <i class="fas fa-clock text-gray-400 w-5"></i>
                                    <span class="text-sm text-gray-600 ml-2">
                                        <?= ucfirst($appointment['appointment_type']) ?> Appointment
                                    </span>
                                </div>

                                <?php if ($appointment['remarks']): ?>
                                    <div class="flex items-start">
                                        <i class="fas fa-comment text-gray-400 w-5 mt-1"></i>
                                        <span class="text-sm text-gray-600 ml-2 italic"><?= $appointment['remarks'] ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Status Badge -->
                            <div class="mt-4">
                                <?php
                                $statusClass = '';
                                $statusText = ucfirst($appointment['status']);
                                switch($appointment['status']) {
                                    case 'confirmed': 
                                        $statusClass = 'bg-green-100 text-green-800'; 
                                        break;
                                    case 'ongoing': 
                                        $statusClass = 'bg-blue-100 text-blue-800'; 
                                        $statusText = 'In Progress';
                                        break;
                                    case 'completed': 
                                        $statusClass = 'bg-slate-100 text-slate-800'; 
                                        break;
                                    case 'no_show': 
                                        $statusClass = 'bg-red-100 text-red-800'; 
                                        $statusText = 'No Show';
                                        break;
                                    default: 
                                        $statusClass = 'bg-gray-100 text-gray-800';
                                }
                                ?>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full <?= $statusClass ?>">
                                    <?= $statusText ?>
                                </span>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-6 space-y-2">
                                <?php if ($appointment['status'] === 'confirmed'): ?>
                                    <button onclick="startCheckup(<?= $appointment['id'] ?>)" 
                                            class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                                            <i class="fas fa-play mr-2"></i>Start Treatment
                                    </button>
                                    <div class="flex space-x-2">
                                        <button onclick="markNoShow(<?= $appointment['id'] ?>)" 
                                                class="flex-1 bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg text-sm font-semibold transition-colors">
                                            <i class="fas fa-times mr-1"></i>No Show
                                        </button>
                                        <button onclick="cancelAppointment(<?= $appointment['id'] ?>)" 
                                                class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-3 py-2 rounded-lg text-sm font-semibold transition-colors">
                                            <i class="fas fa-ban mr-1"></i>Cancel
                                        </button>
                                    </div>
                                <?php elseif ($appointment['status'] === 'ongoing'): ?>
                                    <a href="/checkup/patient/<?= $appointment['id'] ?>" 
                                       class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors text-center block">
                                        <i class="fas fa-stethoscope mr-2"></i>Continue Checkup
                                    </a>
                                <?php elseif ($appointment['status'] === 'completed'): ?>
                                    <div class="text-center text-green-600 font-semibold">
                                        <i class="fas fa-check-circle mr-2"></i>Checkup Completed
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-gray-500">
                                        <i class="fas fa-info-circle mr-2"></i>No Action Available
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No Appointments Today</h3>
                    <p class="text-gray-500">There are no confirmed appointments scheduled for today.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Cancel Appointment Modal -->
<div id="cancelModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Cancel Appointment</h3>
                <form id="cancelForm" method="POST">
                    <div class="mb-4">
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Reason for Cancellation</label>
                        <textarea id="reason" name="reason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeCancelModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors">
                            Confirm Cancellation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function startCheckup(appointmentId) {
    if (confirm('Start checkup for this patient?')) {
        window.location.href = `/checkup/start/${appointmentId}`;
    }
}

function markNoShow(appointmentId) {
    if (confirm('Mark this patient as no-show?')) {
        window.location.href = `/checkup/no-show/${appointmentId}`;
    }
}

function cancelAppointment(appointmentId) {
    document.getElementById('cancelForm').action = `/checkup/cancel/${appointmentId}`;
    document.getElementById('cancelModal').classList.remove('hidden');
}

function closeCancelModal() {
    document.getElementById('cancelModal').classList.add('hidden');
    document.getElementById('reason').value = '';
}

// Auto-refresh every 5 minutes
setInterval(function() {
    window.location.reload();
}, 300000);
</script>

        </main>
    </div>
</div>

<?= view('templates/footer') ?> 