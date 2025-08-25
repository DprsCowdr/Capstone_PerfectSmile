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
                <span class="mr-4 hidden lg:inline text-gray-600 font-semibold"><?= $user['name'] ?? 'Admin' ?></span>
                <div class="relative">
                    <button class="focus:outline-none">
                        <img class="w-10 h-10 rounded-full border-2 border-gray-200" src="<?= base_url('img/undraw_profile.svg') ?>" alt="Profile">
                    </button>
                </div>
            </div>
        </nav>
        <!-- End of Topbar -->
        <main class="flex-1 px-6 pb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Branch Dashboard</h1>

            <?= view('_partials/branch_dashboard', [ 'selectedBranchId' => isset($selectedBranchId) ? $selectedBranchId : null ]) ?>

            <!-- Quick Actions & Recent Activity (reuse admin quick actions if needed) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- (existing admin quick actions will be shown below by controller/view composition) -->
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

<script>
    // Expose globals for staff client
    window.STAFF_STATS_BASE = '<?= base_url('staff/stats') ?>';
    window.STAFF_SELECTED_BRANCH = <?= (int)$selectedBranchId ?>;
    window.STAFF_ASSIGNED_BRANCH = '<?= htmlentities($branch['name'] ?? 'Branch', ENT_QUOTES) ?>';
    window.STAFF_BRANCHES = <?= json_encode([$branch]) ?>;
    window.STAFF_NEXT_APPT_GRACE = <?= (int)(config('App\\Config\\Dashboard')->nextAppointmentGraceMinutes ?? 15) ?>;
    window.STAFF_DEBUG = <?= isset($_GET['staff_debug']) && $_GET['staff_debug'] ? 'true' : 'false' ?>;
    // Indicate current user is admin so the client uses the admin preview proxy
    window.CURRENT_USER_TYPE = 'admin';
    window.ADMIN_PREVIEW_STATS = '<?= base_url('admin/preview-branch-stats') ?>';
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="<?= base_url('js/staff-simple-chart.js') ?>"></script>

<script>
(function(){
    // ensure the staff client uses this page's statsScope element
    setTimeout(function(){
        try{
            const s = document.getElementById('statsScope');
            if (!s) {
                const h = document.createElement('input'); h.type='hidden'; h.id='statsScope'; h.value='branch:<?= (int)$selectedBranchId ?>'; document.body.appendChild(h);
            }
        }catch(e){}
    },50);
})();
</script>
