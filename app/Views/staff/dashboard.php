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
 
            <?= view('_partials/branch_dashboard', [ 'selectedBranchId' => isset($selectedBranchId) ? $selectedBranchId : null ]) ?>
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
    window.STAFF_NEXT_APPT_GRACE = <?= (int)(config('App\Config\Dashboard')->nextAppointmentGraceMinutes ?? 15) ?>;
    // Enable client-side debug dump when ?staff_debug=1 is present
    window.STAFF_DEBUG = <?= isset($_GET['staff_debug']) && $_GET['staff_debug'] ? 'true' : 'false' ?>;
    // If admin selected a branch and the staff user is assigned to it, expose it for the client to prefer
    window.STAFF_SELECTED_BRANCH = <?= isset($selectedBranchId) && $selectedBranchId ? (int)$selectedBranchId : 'null' ?>;
    // Expose current logged-in user type for client-side decisions
    window.CURRENT_USER_TYPE = '<?= htmlentities($user['user_type'] ?? '', ENT_QUOTES) ?>';
    // Admin preview endpoint for branch stats (server-side proxy)
    window.ADMIN_PREVIEW_STATS = '<?= base_url('admin/preview-branch-stats') ?>';
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