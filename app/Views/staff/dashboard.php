<?= view('templates/header') ?>

<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <!-- Topbar -->
        <nav class="flex items-center justify-between bg-white shadow px-6 py-4 mb-6">
            <button id="sidebarToggleTop" class="block lg:hidden text-gray-600 mr-3 text-2xl focus:outline-none" aria-label="Open sidebar on mobile">
                <i class="fas fa-bars" aria-hidden="true"></i>
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
                        <div class="text-2xl font-bold text-gray-800"><span id="staff-total-patients"><?= $totalPatients ?? 0 ?></span></div>
                    </div>
                    <i class="fas fa-users fa-2x text-gray-300"></i>
                </div>
                <!-- Today's Appointments Card -->
                <div class="bg-white border-l-4 border-green-400 shadow rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-green-600 uppercase mb-1">Today's Appointments</div>
                        <div class="text-2xl font-bold text-gray-800"><span id="staff-total-today-appointments"><?= count($todayAppointments ?? []) ?></span></div>
                    </div>
                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                </div>
                <!-- Pending Approvals Card -->
                <div class="bg-white border-l-4 border-orange-400 shadow rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-orange-600 uppercase mb-1">Pending Approvals</div>
                        <div class="text-2xl font-bold text-gray-800"><span id="staff-total-pending-approvals"><?= count($pendingAppointments ?? []) ?></span></div>
                    </div>
                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                </div>
                <!-- Total Dentists Card -->
                <div class="bg-white border-l-4 border-purple-400 shadow rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-purple-600 uppercase mb-1">Available Dentists</div>
                        <div class="text-2xl font-bold text-gray-800"><span id="staff-total-dentists"><?= $totalDentists ?? 0 ?></span></div>
                    </div>
                    <i class="fas fa-user-md fa-2x text-gray-300"></i>
                </div>
            </div>
            <!-- Live charts (line chart only) - copied from dentist dashboard layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Chart column (left, spans 2 cols) -->
                <div class="lg:col-span-2 bg-white shadow rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <label for="chartSelectorTop" class="text-sm text-gray-600">Metric</label>
                            <select id="chartSelectorTop" class="border rounded px-2 py-1 text-sm text-gray-700">
                                <option value="patients">Patients</option>
                                <option value="appointments">Appointments</option>
                                <option value="treatments">Treatments</option>
                            </select>
                            <?php if (!empty($assignedBranches) && count($assignedBranches) === 1): ?>
                                <!-- Single assigned branch: show label and provide hidden input so JS still reads statsScope -->
                                <span class="inline-block px-3 py-1 border rounded text-sm text-gray-700"><?= htmlentities($assignedBranches[0]['name'] ?? 'Branch', ENT_QUOTES) ?></span>
                                <input type="hidden" id="statsScope" value="branch:<?= $assignedBranches[0]['id'] ?>">
                            <?php else: ?>
                                <select id="statsScope" class="border rounded px-2 py-1 text-sm text-gray-700">
                                    <option value="all" <?= (empty($assignedBranches)) ? 'selected' : '' ?>>All Branches</option>
                                    <?php if (!empty($assignedBranches)): ?>
                                        <?php foreach ($assignedBranches as $b): ?>
                                            <option value="branch:<?= $b['id'] ?>" <?= (isset($assignedBranches) && isset($assignedBranches[0]) && $assignedBranches[0]['id'] == $b['id']) ? 'selected' : '' ?>><?= htmlentities($b['name'], ENT_QUOTES) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            <?php endif; ?>
                            <button id="showTotalsHistoryBtn" class="ml-2 px-3 py-1 bg-gray-100 text-sm rounded hover:bg-gray-200">View totals history</button>
                        </div>
                        <div class="text-sm text-gray-500"></div>
                    </div>
                    <div class="w-full h-64">
                        <canvas id="staffTotalsChart" height="160"></canvas>
                    </div>
                </div>

                <!-- Totals history modal -->
                <div id="totalsHistoryModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-40">
                    <div class="bg-white rounded-lg shadow-lg w-11/12 max-w-3xl p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-lg font-semibold">Totals History</h3>
                            <button id="closeTotalsHistoryBtn" class="px-2 py-1 text-gray-600 hover:text-gray-800">&times;</button>
                        </div>
                        <div id="totalsHistoryContent" class="max-h-80 overflow-y-auto text-sm text-gray-800">
                            <!-- populated by JS -->
                        </div>
                    </div>
                </div>

                <!-- Summary column (right) -->
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="mb-4">
                        <div class="text-xs font-semibold text-gray-500 uppercase">Average / day</div>
                        <div id="avgPerDayTop" class="text-2xl font-bold text-gray-800">—</div>
                    </div>
                    <div class="mb-4">
                        <div class="text-xs font-semibold text-gray-500 uppercase">Peak day</div>
                        <div id="peakDayTop" class="text-lg font-semibold text-gray-800">—</div>
                    </div>
                    <div class="mb-4">
                        <div class="text-xs font-semibold text-gray-500 uppercase">Total (latest)</div>
                        <div id="patientTotal" class="text-2xl font-bold text-gray-800">—</div>
                    </div>
                    <div class="mb-4">
                        <div class="text-xs font-semibold text-gray-500 uppercase">Next appointment</div>
                        <div class="text-sm text-gray-700" id="nextAppointment">
                            <span id="nextAppointmentBadge" class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full mr-2 hidden">Upcoming appointment</span>
                            <span id="nextAppointmentText">—</span>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="text-xs font-semibold text-gray-500 uppercase">Statuses</div>
                        <div id="statusLegend" class="mt-2 text-sm text-gray-700"></div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase">Recent samples</div>
                        <div id="recentValues" class="mt-2 text-sm text-gray-700 max-h-40 overflow-y-auto"></div>
                    </div>
                </div>
            </div>
            <!-- Quick Actions & Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Quick Actions -->
 
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
                                <div class="mt-3 flex space-x-2">
                                    <button onclick="staffApproveAppointment(<?= $appointment['id'] ?>)" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm"><i class="fas fa-check mr-1"></i>Approve</button>
                                    <button onclick="staffDeclineAppointment(<?= $appointment['id'] ?>)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm"><i class="fas fa-times mr-1"></i>Decline</button>
                                </div>
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
                
                 
            
            <!-- Quick Actions removed (now consolidated in the left column Quick Actions card) -->
        </main>
        <footer class="bg-white py-4 mt-auto shadow-inner">
            <div class="text-center text-gray-500 text-sm">
                &copy; Perfect Smile <?= date('Y') ?>
        </div>
        </footer>
    </div>
</div>

<?= view('templates/footer') ?> 
<script>
function staffApproveAppointment(appointmentId) {
    if (!confirm('Approve this appointment?')) return;
    const formData = new FormData();
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    fetch(`<?= base_url() ?>staff/appointments/approve/${appointmentId}`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.json())
    .then(data => { if (data.success) location.reload(); else alert('Error: ' + (data.message || 'Unknown')); })
    .catch(e => { console.error(e); alert('An error occurred'); });
}

function staffDeclineAppointment(appointmentId) {
    const reason = prompt('Reason for declining the appointment (required):');
    if (!reason || !reason.trim()) { alert('Decline reason is required'); return; }
    const formData = new FormData();
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    formData.append('reason', reason);
    fetch(`<?= base_url() ?>staff/appointments/decline/${appointmentId}`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.json())
    .then(data => { if (data.success) location.reload(); else alert('Error: ' + (data.message || 'Unknown')); })
    .catch(e => { console.error(e); alert('An error occurred'); });
}
</script>
<!-- staff live charts removed; no chart scripts loaded here -->
<!-- Staff live chart scripts -->
<script>
    // endpoint used by public/js/staff-dashboard-totals.js
    // assigned branch name provided by controller (falls back to 'All Branches')
    window.STAFF_ASSIGNED_BRANCH = '<?= htmlentities($assignedBranchName ?? 'All Branches', ENT_QUOTES) ?>';
    window.STAFF_BRANCHES = <?= json_encode(array_values($assignedBranches ?? [])) ?>; // array of assigned branch objects
    // Provide a clean base stats endpoint; client will append branch_id or scope as needed
    window.STAFF_STATS_BASE = '<?= base_url('staff/stats') ?>';
    window.STAFF_STATS_URL = window.STAFF_STATS_BASE; // client will build final URL per selection
</script>
<!-- Use CDN Chart.js (matches dentist view) and a lightweight staff chart script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="<?= base_url('js/staff-simple-chart.js') ?>"></script>
<script>
// Welcome carousel behavior
(function() {
    const slides = Array.from(document.querySelectorAll('#welcomeCarousel .carousel-slide'));
    if (!slides.length) return;
    let idx = 0;
    const total = slides.length;
    const show = (n) => {
        slides.forEach((s, i) => {
            s.style.opacity = (i === n) ? '1' : '0';
            s.style.zIndex = (i === n) ? '5' : '1';
        });
    };
    show(idx);
    let auto = setInterval(() => {
        idx = (idx + 1) % total; show(idx);
    }, 4500);

    const prev = document.getElementById('carouselPrev');
    const next = document.getElementById('carouselNext');
    if (prev) prev.addEventListener('click', () => { clearInterval(auto); idx = (idx - 1 + total) % total; show(idx); auto = setInterval(() => { idx = (idx + 1) % total; show(idx); }, 4500); });
    if (next) next.addEventListener('click', () => { clearInterval(auto); idx = (idx + 1) % total; show(idx); auto = setInterval(() => { idx = (idx + 1) % total; show(idx); }, 4500); });
})();
</script>