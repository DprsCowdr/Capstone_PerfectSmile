<?= view('templates/header') ?>
<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    <div class="flex-1 flex flex-col min-h-screen bg-white">
        <!-- Topbar -->
        <nav class="flex items-center justify-between bg-white shadow px-6 py-4 mb-6">
            <button id="sidebarToggleTop" class="block lg:hidden text-gray-600 mr-3 text-2xl focus:outline-none">
                <i class="fa fa-bars"></i>
            </button>
            <div class="flex items-center ml-auto">
                <span class="mr-4 hidden lg:inline text-gray-600 font-semibold">Dr. <?= $user['name'] ?? 'Dentist' ?></span>
                <div class="relative">
                    <button class="focus:outline-none">
                        <img class="w-10 h-10 rounded-full border-2 border-gray-200" src="<?= base_url('img/undraw_profile.svg') ?>" alt="Profile">
                    </button>
                    <!-- Dropdown -->
                    <div class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50" id="userDropdownMenu">
                        <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100"><i class="fas fa-user mr-2 text-gray-400"></i>Profile</a>
                        <div class="border-t my-1"></div>
                        <a href="<?= base_url('auth/logout') ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100"><i class="fas fa-sign-out-alt mr-2 text-gray-400"></i>Logout</a>
                    </div>
                </div>
            </div>
        </nav>
        <!-- End of Topbar -->
        <main class="flex-1 px-6 pb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">👨‍⚕️ Dentist Dashboard</h1>
            
            <!-- Cards Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
                <!-- Today's Appointments Card -->
                <div class="bg-white border-l-4 border-blue-400 shadow rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-blue-600 uppercase mb-1">Today's Appointments</div>
                        <div class="text-2xl font-bold text-gray-800"><?= count($todayAppointments ?? []) ?></div>
                    </div>
                    <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                </div>
                <!-- Pending Approvals Card -->
                <div class="bg-white border-l-4 border-orange-400 shadow rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-orange-600 uppercase mb-1">Pending Approvals</div>
                        <div class="text-2xl font-bold text-gray-800"><?= count($pendingAppointments ?? []) ?></div>
                    </div>
                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                </div>
                <!-- This Week Card -->
                <div class="bg-white border-l-4 border-green-400 shadow rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-green-600 uppercase mb-1">This Week</div>
                        <div class="text-2xl font-bold text-gray-800"><?= count($upcomingAppointments ?? []) ?></div>
                    </div>
                    <i class="fas fa-calendar-week fa-2x text-gray-300"></i>
                </div>
                <!-- Total Patients Card -->
                <div class="bg-white border-l-4 border-indigo-400 shadow rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-indigo-600 uppercase mb-1">My Patients</div>
                        <div class="text-2xl font-bold text-gray-800"><?= $totalPatients ?? 0 ?></div>
                    </div>
                    <i class="fas fa-users fa-2x text-gray-300"></i>
                </div>
            </div>

            <!-- Quick Actions & Management Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Quick Actions -->
                <div class="bg-white shadow rounded-lg mb-6">
                    <div class="border-b px-6 py-3">
                        <h2 class="text-lg font-bold text-slate-700">Quick Actions</h2>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <a href="<?= base_url('doctor/appointments') ?>" class="flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition"><i class="fas fa-calendar-plus"></i> View Appointments</a>
                        <a href="<?= base_url('doctor/patients') ?>" class="flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-lg transition"><i class="fas fa-user-injured"></i> Manage Patients</a>
                        <a href="<?= base_url('queue/') ?>" class="flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 rounded-lg transition"><i class="fas fa-users"></i> Treatment Queue</a>
                        <a href="<?= base_url('checkup/') ?>" class="flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 rounded-lg transition"><i class="fas fa-stethoscope"></i> Patient Checkups</a>
                    </div>
                </div>

                <!-- Pending Approvals Section -->
                <?php if (!empty($pendingAppointments)): ?>
                <div class="bg-white shadow rounded-lg mb-6">
                    <div class="border-b px-6 py-3">
                        <h2 class="text-lg font-bold text-orange-700 flex items-center">
                            <i class="fas fa-clock text-orange-500 mr-2"></i>
                            Pending Approvals (<?= count($pendingAppointments) ?>)
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4 max-h-96 overflow-y-auto">
                            <?php foreach ($pendingAppointments as $appointment): ?>
                            <div class="border border-orange-200 rounded-lg p-4 bg-orange-50">
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
                                                class="bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1 rounded text-sm font-semibold transition-colors">
                                            <i class="fas fa-check mr-1"></i> Approve
                                        </button>
                                        <button onclick="declineAppointment(<?= $appointment['id'] ?>)" 
                                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm font-semibold transition-colors">
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
                        <div class="mt-4 text-center">
                            <a href="<?= base_url('doctor/appointments') ?>" class="text-orange-600 hover:text-orange-700 font-semibold">
                                View All Appointments →
                            </a>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <!-- Recent Activity when no pending appointments -->
                <div class="bg-white shadow rounded-lg mb-6">
                    <div class="border-b px-6 py-3">
                        <h2 class="text-lg font-bold text-slate-700">Recent Activity</h2>
                    </div>
                    <div class="p-6">
                        <div class="mb-4">
                            <div class="text-sm font-semibold text-gray-700">Patient appointment completed</div>
                            <div class="text-xs text-gray-400">2 hours ago</div>
                        </div>
                        <div class="border-t my-2"></div>
                        <div class="mb-4">
                            <div class="text-sm font-semibold text-gray-700">Dental chart updated</div>
                            <div class="text-xs text-gray-400">1 day ago</div>
                        </div>
                        <div class="border-t my-2"></div>
                        <div class="mb-4">
                            <div class="text-sm font-semibold text-gray-700">New appointment scheduled</div>
                            <div class="text-xs text-gray-400">2 days ago</div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Today's Schedule -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="border-b px-6 py-3">
                    <h2 class="text-lg font-bold text-slate-700">Today's Schedule</h2>
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
                                        <a href="<?= base_url('doctor/dental-chart/' . $appointment['id']) ?>" class="bg-blue-500 text-white px-3 py-1 rounded text-xs hover:bg-blue-600">
                                            🦷 Chart
                                        </a>
                                        <a href="<?= base_url('doctor/patient-records/' . $appointment['user_id']) ?>" class="bg-green-500 text-white px-3 py-1 rounded text-xs hover:bg-green-600">
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
                    <div class="text-center py-8">
                        <i class="fas fa-calendar-times text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-500">No appointments scheduled for today.</p>
                        <a href="<?= base_url('doctor/appointments') ?>" class="text-blue-600 hover:text-blue-700 font-semibold mt-2 inline-block">
                            View All Appointments →
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
        
        <footer class="bg-white py-4 mt-auto shadow-inner">
            <div class="text-center text-gray-500 text-sm">
                &copy; Perfect Smile <?= date('Y') ?>
            </div>
        </footer>
    </div>
</div>

<script>
function approveAppointment(appointmentId) {
    if (confirm('Are you sure you want to approve this appointment?')) {
        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        
        fetch(`<?= base_url() ?>doctor/appointments/approve/${appointmentId}`, {
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
        
        fetch(`<?= base_url() ?>doctor/appointments/decline/${appointmentId}`, {
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