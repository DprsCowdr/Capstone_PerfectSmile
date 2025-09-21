<?= view('templates/header') ?>
<!-- Dentist dashboard scoped assets (isolated) -->
<link rel="stylesheet" href="<?= base_url('css/dentist-dashboard.css') ?>">
<script src="<?= base_url('js/dentist-dashboard.js') ?>" defer></script>

<!-- Inline fallback (ensures behavior even if external assets 404) -->
<style>
/* Inline fallback: prevent page stretch when sidebar is fixed */
.dentist-dashboard-root.with-sidebar-offset-active { padding-left: 16rem !important; box-sizing: border-box !important; overflow-x: hidden !important; }
#sidebar.sidebar-fixed { width: 16rem !important; }
</style>
<script>
(function(){ window.DENTIST_STATS_URL = '<?= base_url('dentist/stats') ?>'; })();
(function(){
    try {
        function applyInlineOffset(){
            var root = document.querySelector('.dentist-dashboard-root');
            var sidebar = document.getElementById('sidebar');
            if (!root || !sidebar) return;
            var should = window.innerWidth >= 1024 && root.hasAttribute('data-sidebar-offset');
            if (should) { sidebar.classList.add('sidebar-fixed'); root.classList.add('with-sidebar-offset-active'); }
            else { sidebar.classList.remove('sidebar-fixed'); root.classList.remove('with-sidebar-offset-active'); }
        }
        window.addEventListener('load', applyInlineOffset);
        window.addEventListener('resize', applyInlineOffset);
    } catch(e){ console && console.warn && console.warn('inline dentist fallback error', e); }
})();
</script>
<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    <div class="flex-1 flex flex-col min-h-screen bg-white dentist-dashboard-root" data-sidebar-offset>
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
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-800">🩺 Dentist Dashboard</h1>
            </div>

            <!-- Cards Row (clinic-wide stats from admin DashboardService) -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
                <div class="bg-white border-l-4 border-indigo-400 shadow rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-indigo-600 uppercase mb-1">Total Patients</div>
                        <div class="text-2xl font-bold text-gray-800"><span id="dentist-total-patients"><?= isset($statistics['totalPatients']) ? $statistics['totalPatients'] : ($totalPatients ?? 0) ?></span></div>
                    </div>
                    <i class="fas fa-users fa-2x text-gray-300"></i>
                </div>
                <div class="bg-white border-l-4 border-green-400 shadow rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-green-600 uppercase mb-1">Today's Appointments</div>
                        <div class="text-2xl font-bold text-gray-800"><span id="dentist-total-today-appointments"><?= count($todayAppointments ?? []) ?></span></div>
                    </div>
                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                </div>
                <div class="bg-white border-l-4 border-orange-400 shadow rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-orange-600 uppercase mb-1">Pending Approvals</div>
                        <div class="text-2xl font-bold text-gray-800"><span id="dentist-total-pending-approvals"><?= count($pendingAppointments ?? []) ?></span></div>
                    </div>
                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                </div>
                <div class="bg-white border-l-4 border-purple-400 shadow rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-purple-600 uppercase mb-1">Available Dentists</div>
                        <div class="text-2xl font-bold text-gray-800"><span id="dentist-total-dentists"><?= isset($statistics['totalDentists']) ? $statistics['totalDentists'] : 0 ?></span></div>
                    </div>
                    <i class="fas fa-user-md fa-2x text-gray-300"></i>
                </div>
            </div>
            
            <!-- (Removed duplicate Live Stats block - single dashboard section retained later) -->
            
            <!-- Live Chart (replaces Today's Schedule) -->
            <div class="bg-white shadow rounded-lg mb-6 p-6">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-lg font-bold text-slate-700">Live Dashboard</h2>
                    <div>
                        <select id="statsScope" class="text-sm border rounded px-2 py-1">
                            <option value="mine">My stats</option>
                            <option value="clinic">Clinic-wide</option>
                        </select>
                    </div>
                </div>
                <div class="w-full">
                    <div class="w-full bg-white rounded-lg p-4">
                        <div class="flex items-start justify-between mb-2 gap-4">
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="text-sm text-gray-600">Live Chart</div>
                                    <div>
                                        <select id="chartSelectorTop" class="text-sm border rounded px-2 py-1">
                                            <option value="appointments">Appointments Live Chart</option>
                                            <option value="patients">Patients Live Chart</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="chart-responsive" style="position:relative; overflow:hidden; height:clamp(320px,45vh,560px);">
                                    <canvas id="appointmentsChartTop" style="width:100%; display:block; height:100%;"></canvas>
                                </div>
                                <div class="mt-3 text-sm text-gray-700">
                                    <div>Avg per day: <span id="avgPerDayTop" class="font-semibold">—</span></div>
                                    <div class="mt-1">Peak day: <span id="peakDayTop" class="font-semibold">—</span></div>
                                </div>
                            </div>

                            <div class="w-1/3 flex-shrink-0 bg-white">
                                <div class="p-3 rounded-md text-center h-full flex flex-col items-center justify-center">
                                    <div class="doughnut-wrapper" style="height:420px; width:420px; max-width:100%;">
                                        <canvas id="statusChartTop" width="420" height="420" style="max-width:100%; height:auto;"></canvas>
                                    </div>
                                    <div class="mt-3">
                                        <div class="text-sm text-gray-600">Patients (7d)</div>
                                        <div class="text-2xl font-bold" id="patientTotal">—</div>
                                    </div>
                                    <div class="mt-3 text-sm text-gray-500" id="nextAppointment">
                                        <span id="nextAppointmentBadge" class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full mr-2 hidden">Upcoming appointment</span>
                                        <span id="nextAppointmentText">No upcoming appointments</span>
                                    </div>
                                    <div id="statusLegend" class="mt-3 w-full text-left text-sm"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- include Chart.js CDN (required by dentist-dashboard.js) -->
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<?= view('templates/footer') ?>

 