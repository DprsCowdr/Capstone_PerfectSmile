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
                <span class="mr-4 hidden lg:inline text-gray-600 font-semibold"><?= $user['name'] ?? 'Patient' ?></span>
                <div class="relative">
                    <button class="focus:outline-none">
                        <img class="w-10 h-10 rounded-full border-2 border-gray-200" src="<?= base_url('img/undraw_profile.svg') ?>" alt="Profile">
                    </button>
                    <!-- Dropdown -->
                    <div class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50" id="userDropdownMenu">
                        <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100"><i class="fas fa-user mr-2 text-gray-400"></i>Profile</a>
                        <div class="border-t my-1"></div>
                        <a href="<?= base_url('auth/logout') ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100"><i class="fas fa-sign-out-alt mr-2 text-gray-400"></i>Logout</a>
                    </div>ted
                </div>
            </div>
        </nav>
        <!-- End of Topbar -->
        <main class="flex-1 px-6 pb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">👤 Patient Dashboards</h1>
            
            <!-- Welcome Message -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-100 border border-blue-200 rounded-lg p-6 mb-8">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-smile text-blue-600 text-3xl"></i>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-lg font-semibold text-blue-900">Welcome back, <?= esc($user['name'] ?? 'Patient') ?>!</h2>
                        <p class="text-blue-700">Manage your appointments, view your dental records, and stay on top of your oral health.</p>
                    </div>
                </div>
            </div>
            
            <!-- Rejected Appointments Alert -->
            <?php if (($rejectedAppointments ?? 0) > 0): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">
                            You have <?= $rejectedAppointments ?> rejected appointment<?= $rejectedAppointments > 1 ? 's' : '' ?>
                        </h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p>Some of your appointment requests were declined. Please review them and book a new appointment if needed.</p>
                        </div>
                        <div class="mt-3">
                            <a href="<?= base_url('patient/appointments') ?>" class="text-sm bg-red-100 text-red-800 hover:bg-red-200 px-3 py-1 rounded-md font-medium transition">
                                View All Appointments
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Pending Appointments Alert -->
            <?php if (($pendingAppointments ?? 0) > 0): ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-hourglass-half text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">
                            You have <?= $pendingAppointments ?> appointment<?= $pendingAppointments > 1 ? 's' : '' ?> awaiting approval
                        </h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>Your appointment requests are being reviewed by our staff. You'll be notified once they're approved.</p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Cards Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
                <!-- My Appointments Card -->
                <div class="bg-white border-l-4 border-blue-400 shadow rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-blue-600 uppercase mb-1">My Appointments</div>
                        <div class="text-2xl font-bold text-gray-800"><?= $totalAppointments ?? 0 ?></div>
                    </div>
                    <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                </div>
                <!-- Pending Appointments Card -->
                <div class="bg-white border-l-4 border-yellow-400 shadow rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-yellow-600 uppercase mb-1">Pending</div>
                        <div class="text-2xl font-bold text-gray-800"><?= $pendingAppointments ?? 0 ?></div>
                    </div>
                    <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                </div>
                <!-- Completed Treatments Card -->
                <div class="bg-white border-l-4 border-purple-400 shadow rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-purple-600 uppercase mb-1">Completed</div>
                        <div class="text-2xl font-bold text-gray-800"><?= $completedTreatments ?? 0 ?></div>
                    </div>
                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                </div>
                <!-- Rejected Appointments Card -->
                <div class="bg-white border-l-4 border-red-400 shadow rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-red-600 uppercase mb-1">Rejected</div>
                        <div class="text-2xl font-bold text-gray-800"><?= $rejectedAppointments ?? 0 ?></div>
                    </div>
                    <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                </div>
            </div>

            <!-- Quick Actions & Information -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Quick Actions -->
                <div class="bg-white shadow rounded-lg mb-6">
                    <div class="border-b px-6 py-3">
                        <h2 class="text-lg font-bold text-slate-700">Quick Actions</h2>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="<?= base_url('patient/calendar') ?>" class="flex items-center justify-between p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition group">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-alt text-blue-600 mr-3 text-lg"></i>
                                <span class="font-semibold text-gray-700 group-hover:text-blue-700">Calendar View</span>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 group-hover:text-blue-600"></i>
                        </a>
                        <a href="<?= base_url('patient/book-appointment') ?>" class="flex items-center justify-between p-4 bg-green-50 hover:bg-green-100 rounded-lg transition group">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-plus text-green-600 mr-3 text-lg"></i>
                                <span class="font-semibold text-gray-700 group-hover:text-green-700">Book New Appointment</span>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 group-hover:text-green-600"></i>
                        </a>
                        <a href="<?= base_url('patient/appointments') ?>" class="flex items-center justify-between p-4 bg-green-50 hover:bg-green-100 rounded-lg transition group">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-check text-green-600 mr-3 text-lg"></i>
                                <span class="font-semibold text-gray-700 group-hover:text-green-700">View My Appointments</span>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 group-hover:text-green-600"></i>
                        </a>
                        <a href="<?= base_url('patient/records') ?>" class="flex items-center justify-between p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition group">
                            <div class="flex items-center">
                                <i class="fas fa-file-medical-alt text-purple-600 mr-3 text-lg"></i>
                                <span class="font-semibold text-gray-700 group-hover:text-purple-700">View Medical Records</span>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 group-hover:text-purple-600"></i>
                        </a>
                        <a href="<?= base_url('patient/profile') ?>" class="flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition group">
                            <div class="flex items-center">
                                <i class="fas fa-user-cog text-gray-600 mr-3 text-lg"></i>
                                <span class="font-semibold text-gray-700 group-hover:text-gray-700">Update Profile</span>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 group-hover:text-gray-600"></i>
                        </a>
                    </div>
                </div>

                <!-- Upcoming Appointments -->
                <div class="bg-white shadow rounded-lg mb-6">
                    <div class="border-b px-6 py-3">
                        <h2 class="text-lg font-bold text-slate-700">Upcoming Appointments</h2>
                    </div>
                    <div class="p-6">
                        <?php if (!empty($upcomingAppointments)): ?>
                        <div class="space-y-4">
                            <?php foreach ($upcomingAppointments as $appointment): ?>
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-semibold text-gray-800">Dr. <?= $appointment['dentist_name'] ?? 'TBD' ?></h3>
                                        <p class="text-sm text-gray-600"><?= $appointment['service_name'] ?? 'General Checkup' ?></p>
                                        <p class="text-sm text-gray-500">
                                            <i class="fas fa-building mr-1"></i>
                                            <?= $appointment['branch_name'] ?? 'Main Branch' ?>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm text-gray-500"><?= date('M j, Y', strtotime($appointment['appointment_datetime'])) ?></div>
                                        <div class="font-semibold text-gray-800"><?= date('g:i A', strtotime($appointment['appointment_datetime'])) ?></div>
                                        <span class="px-2 py-1 text-xs rounded-full <?= $appointment['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' ?>">
                                            <?= ucfirst($appointment['status']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-8">
                            <i class="fas fa-calendar-times text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-500">No upcoming appointments scheduled.</p>
                            <a href="<?= base_url('patient/book-appointment') ?>" class="text-blue-600 hover:text-blue-700 font-semibold mt-2 inline-block">
                                Book Your Next Appointment →
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Health Tips & Information -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="border-b px-6 py-3">
                    <h2 class="text-lg font-bold text-slate-700">Oral Health Tips</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="bg-blue-100 rounded-full p-4 w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                                <i class="fas fa-teeth text-blue-600 text-xl"></i>
                            </div>
                            <h3 class="font-semibold text-gray-800 mb-2">Brush Twice Daily</h3>
                            <p class="text-sm text-gray-600">Brush your teeth for 2 minutes, twice a day with fluoride toothpaste.</p>
                        </div>
                        <div class="text-center">
                            <div class="bg-green-100 rounded-full p-4 w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                                <i class="fas fa-smile text-green-600 text-xl"></i>
                            </div>
                            <h3 class="font-semibold text-gray-800 mb-2">Floss Daily</h3>
                            <p class="text-sm text-gray-600">Remove plaque and food particles between teeth with daily flossing.</p>
                        </div>
                        <div class="text-center">
                            <div class="bg-purple-100 rounded-full p-4 w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                                <i class="fas fa-calendar-check text-purple-600 text-xl"></i>
                            </div>
                            <h3 class="font-semibold text-gray-800 mb-2">Regular Checkups</h3>
                            <p class="text-sm text-gray-600">Visit your dentist every 6 months for professional cleaning and checkups.</p>
                        </div>
                    </div>
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