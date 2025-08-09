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
                <span class="mr-4 hidden lg:inline text-gray-600 font-semibold"><?= $user['name'] ?? 'Staff' ?></span>
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
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Staff Dashboard</h1>
            <!-- Cards Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
                <!-- Total Patients Card -->
                <div class="bg-white border-l-4 border-indigo-400 shadow rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-indigo-600 uppercase mb-1">Total Patients</div>
                        <div class="text-2xl font-bold text-gray-800"><?= $totalPatients ?? 0 ?></div>
                    </div>
                    <i class="fas fa-users fa-2x text-gray-300"></i>
                </div>
                <!-- Today's Appointments Card -->
                <div class="bg-white border-l-4 border-green-400 shadow rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-green-600 uppercase mb-1">Today's Appointments</div>
                        <div class="text-2xl font-bold text-gray-800"><?= count($todayAppointments ?? []) ?></div>
                    </div>
                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                </div>
                <!-- Pending Approvals Card -->
                <div class="bg-white border-l-4 border-orange-400 shadow rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-orange-600 uppercase mb-1">Pending Approvals</div>
                        <div class="text-2xl font-bold text-gray-800"><?= count($pendingAppointments ?? []) ?></div>
                    </div>
                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                </div>
                <!-- Total Dentists Card -->
                <div class="bg-white border-l-4 border-purple-400 shadow rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-purple-600 uppercase mb-1">Available Dentists</div>
                        <div class="text-2xl font-bold text-gray-800"><?= $totalDentists ?? 0 ?></div>
                    </div>
                    <i class="fas fa-user-md fa-2x text-gray-300"></i>
                </div>
            </div>
            <!-- Quick Actions & Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Quick Actions -->
                <div class="bg-white shadow rounded-lg mb-6">
                    <div class="border-b px-6 py-3">
                        <h2 class="text-lg font-bold text-slate-700">Quick Actions</h2>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <a href="<?= base_url('staff/patients') ?>" class="flex items-center justify-center gap-2 bg-slate-600 hover:bg-slate-700 text-white font-semibold py-3 rounded-lg transition"><i class="fas fa-user-plus"></i> Manage Patients</a>
                        <a href="<?= base_url('staff/appointments') ?>" class="flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition"><i class="fas fa-calendar-plus"></i> Manage Appointments</a>
                        <a href="<?= base_url('checkin') ?>" class="flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 rounded-lg transition"><i class="fas fa-sign-in-alt"></i> Patient Check-In</a>
                        <a href="<?= base_url('queue') ?>" class="flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 rounded-lg transition"><i class="fas fa-users"></i> Treatment Queue</a>
                    </div>
                </div>
                <!-- Pending Approvals -->
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
                                        <div class="text-sm text-gray-500"><?= date('M j, Y', strtotime($appointment['appointment_date'])) ?></div>
                                        <div class="font-semibold text-gray-800"><?= date('g:i A', strtotime($appointment['appointment_time'])) ?></div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-600">
                                        <i class="fas fa-building mr-1"></i> <?= $appointment['branch_name'] ?>
                                    </div>
                                    <div class="text-sm text-orange-600 font-semibold">
                                        <i class="fas fa-info-circle mr-1"></i> Waiting for admin/dentist approval
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
                            <a href="<?= base_url('staff/appointments') ?>" class="text-orange-600 hover:text-orange-700 font-semibold">
                                View All Appointments →
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Recent Patients -->
                <div class="bg-white shadow rounded-lg mb-6">
                    <div class="border-b px-6 py-3">
                        <h2 class="text-lg font-bold text-indigo-700 flex items-center">
                            <i class="fas fa-users text-indigo-500 mr-2"></i>
                            Recent Patients (<?= count($recentPatients ?? []) ?>)
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4 max-h-96 overflow-y-auto">
                            <?php if (!empty($recentPatients)): ?>
                                <?php foreach ($recentPatients as $patient): ?>
                                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h3 class="font-semibold text-gray-800"><?= $patient['name'] ?></h3>
                                            <p class="text-sm text-gray-600"><?= $patient['email'] ?></p>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm text-gray-500"><?= $patient['phone'] ?></div>
                                            <div class="text-xs text-gray-400"><?= date('M j, Y', strtotime($patient['created_at'])) ?></div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm text-gray-600">
                                            <i class="fas fa-map-marker-alt mr-1"></i> <?= $patient['address'] ?>
                                        </div>
                                        <span class="px-2 py-1 text-xs rounded-full <?= $patient['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                            <?= ucfirst($patient['status']) ?>
                                        </span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-8 text-gray-500">
                                    <i class="fas fa-users text-4xl mb-4"></i>
                                    <p>No patients found</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-4 text-center">
                            <a href="<?= base_url('staff/patients') ?>" class="text-indigo-600 hover:text-indigo-700 font-semibold">
                                View All Patients →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="border-b px-6 py-3">
                    <h2 class="text-lg font-bold text-indigo-700">Quick Actions</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="<?= base_url('staff/patients') ?>" class="flex items-center justify-center gap-2 bg-indigo-500 hover:bg-indigo-600 text-white font-semibold py-3 rounded-lg transition"><i class="fas fa-users"></i> Manage Patients</a>
                    <a href="<?= base_url('staff/appointments') ?>" class="flex items-center justify-center gap-2 bg-green-500 hover:bg-green-600 text-white font-semibold py-3 rounded-lg transition"><i class="fas fa-calendar-plus"></i> Create Appointment</a>
                    <a href="<?= base_url('staff/patients/add') ?>" class="flex items-center justify-center gap-2 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 rounded-lg transition"><i class="fas fa-user-plus"></i> Add Patient</a>
                    <a href="<?= base_url('staff/appointments') ?>" class="flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition"><i class="fas fa-calendar-check"></i> View Appointments</a>
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

<?= view('templates/footer') ?> 