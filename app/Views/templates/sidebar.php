<?php
$user = isset($user) ? $user : (session('user') ?? []);
$userType = $user['user_type'] ?? null;
$currentUrl = current_url();
?>

<!-- Mobile menu button -->
<div class="lg:hidden fixed top-4 left-4 z-50">
    <button id="mobileSidebarToggle" class="p-2 rounded-md bg-white shadow-lg border border-gray-200 text-gray-600 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <i class="fas fa-bars text-lg"></i>
    </button>
</div>

<!-- Mobile overlay -->
<div id="sidebarOverlay" class="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden hidden"></div>

<!-- Sidebar -->
<aside id="sidebar" class="fixed lg:relative inset-y-0 left-0 z-50 flex flex-col w-64 h-screen px-4 sm:px-5 py-6 sm:py-8 overflow-y-auto bg-white border-r shadow-lg lg:shadow-none transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
    <!-- Close button for mobile -->
    <div class="flex items-center justify-between lg:hidden mb-4">
        <span class="text-lg font-bold text-gray-800">Menu</span>
        <button id="closeSidebar" class="p-2 rounded-md text-gray-600 hover:bg-gray-100 focus:outline-none">
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>

    <!-- Logo/Brand -->
    <a href="<?= base_url($userType ? $userType . '/dashboard' : '/') ?>" class="mb-6">
        <div class="flex items-center">
            <div class="text-xl sm:text-2xl mr-2 sm:mr-3 text-blue-600">
                <?php if ($userType === 'admin'): ?>
                    <i class="fas fa-laugh-wink"></i>
                <?php elseif ($userType === 'doctor'): ?>
                    <i class="fas fa-user-md"></i>
                <?php elseif ($userType === 'staff'): ?>
                    <i class="fas fa-user-tie"></i>
                <?php elseif ($userType === 'patient'): ?>
                    <i class="fas fa-user"></i>
                <?php else: ?>
                    <i class="fas fa-home"></i>
                <?php endif; ?>
            </div>
            <span class="text-base sm:text-lg font-bold text-gray-800">
                Perfect Smile
            </span>
        </div>
    </a>

    <?php if ($userType === 'admin'): ?>
    <!-- Branch Selector -->
    <div class="mb-6">
        <label class="block text-xs text-gray-500 uppercase font-semibold mb-2">Select Branch</label>
        <div class="relative">
            <select id="branchSelector" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                <option value="">-- All Branches --</option>
                <?php
                // Load branches from database
                $branchModel = new \App\Models\BranchModel();
                $branches = $branchModel->getActiveBranches();
                $selectedBranch = session('selected_branch_id') ?? '';
                
                foreach ($branches as $branch):
                    $isSelected = ($selectedBranch == $branch['id']) ? 'selected' : '';
                ?>
                <option value="<?= $branch['id'] ?>" <?= $isSelected ?>>
                    <?= esc($branch['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
            </div>
        </div>
        <?php if ($selectedBranch): ?>
        <div class="mt-2 text-xs text-blue-600 font-medium">
            <i class="fas fa-building mr-1"></i>
            Branch: <?= esc($branches[array_search($selectedBranch, array_column($branches, 'id'))]['name'] ?? 'Unknown') ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="flex flex-col justify-between flex-1">
        <nav class="-mx-2 sm:-mx-3 space-y-4 sm:space-y-6">
            <?php
            function nav_link($href, $icon, $label, $currentUrl) {
                $isActive = strpos($currentUrl, $href) !== false;
                $baseClass = 'flex items-center px-2 sm:px-3 py-2 sm:py-2 text-gray-600 transition-colors duration-300 transform rounded-lg hover:bg-gray-100 hover:text-gray-700 text-sm sm:text-base';
                $activeClass = $isActive ? 'bg-gray-100 text-gray-700' : '';
                return "<a class=\"{$baseClass} {$activeClass}\" href=\"{$href}\">
                    <i class=\"{$icon} w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0\"></i>
                    <span class=\"mx-2 text-xs sm:text-sm font-medium\">{$label}</span>
                </a>";
            }
            ?>
            
            <!-- Dashboard Section -->
            <div class="space-y-2 sm:space-y-3">
                <label class="px-2 sm:px-3 text-xs text-gray-500 uppercase font-semibold">Dashboard</label>
                <?= nav_link(base_url($userType ? $userType . '/dashboard' : '/'), 'fas fa-tachometer-alt', 'Dashboard', $currentUrl) ?>
            </div>

            <?php if ($userType === 'admin'): ?>
            <!-- Management Section -->
            <div class="space-y-2 sm:space-y-3">
                <label class="px-2 sm:px-3 text-xs text-gray-500 uppercase font-semibold">Management</label>
                <?= nav_link(base_url('admin/users'), 'fas fa-user-cog', 'Users', $currentUrl) ?>
                <?= nav_link(base_url('admin/patients'), 'fas fa-users', 'Patients', $currentUrl) ?>
                <?= nav_link(base_url('admin/appointments'), 'fas fa-calendar-alt', 'Appointments', $currentUrl) ?>
                <?= nav_link(base_url('admin/services'), 'fas fa-stethoscope', 'Services', $currentUrl) ?>
                <?= nav_link(base_url('admin/waitlist'), 'fas fa-clipboard-list', 'Waitlist', $currentUrl) ?>
                <?= nav_link(base_url('admin/procedures'), 'fas fa-file-medical', 'Procedures', $currentUrl) ?>
                <?= nav_link(base_url('admin/records'), 'fas fa-folder-open', 'Records', $currentUrl) ?>
                <?= nav_link(base_url('admin/invoices'), 'fas fa-file-invoice-dollar', 'Invoices', $currentUrl) ?>
            </div>

            <!-- Patient Flow Section -->
            <div class="space-y-2 sm:space-y-3">
                <label class="px-2 sm:px-3 text-xs text-gray-500 uppercase font-semibold">Patient Flow</label>
                <?= nav_link(base_url('checkin'), 'fas fa-sign-in-alt', 'Patient Check-In', $currentUrl) ?>
                <?= nav_link(base_url('queue'), 'fas fa-users', 'Treatment Queue', $currentUrl) ?>
                <?= nav_link(base_url('checkup'), 'fas fa-stethoscope', 'Checkup Module', $currentUrl) ?>
            </div>

            <!-- Clinical Records Section -->
            <div class="space-y-2 sm:space-y-3">
                <label class="px-2 sm:px-3 text-xs text-gray-500 uppercase font-semibold">Clinical Records</label>
                <?= nav_link(base_url('admin/patient-checkups'), 'fas fa-clipboard-check', 'Patient Checkups', $currentUrl) ?>
                <?= nav_link(base_url('admin/dental-records'), 'fas fa-file-medical-alt', 'Dental Records', $currentUrl) ?>
                <?= nav_link(base_url('admin/dental-charts'), 'fas fa-tooth', 'Dental Charts', $currentUrl) ?>
            </div>

            <!-- Administration Section -->
            <div class="space-y-2 sm:space-y-3">
                <label class="px-2 sm:px-3 text-xs text-gray-500 uppercase font-semibold">Administration</label>
                <?= nav_link(base_url('admin/role-permission'), 'fas fa-user-shield', 'Role Permission', $currentUrl) ?>
                <?= nav_link(base_url('admin/branches'), 'fas fa-code-branch', 'Branches', $currentUrl) ?>
                <?= nav_link(base_url('admin/settings'), 'fas fa-cog', 'Settings', $currentUrl) ?>
            </div>

            <?php elseif ($userType === 'doctor'): ?>
            <!-- Management Section -->
            <div class="space-y-2 sm:space-y-3">
                <label class="px-2 sm:px-3 text-xs text-gray-500 uppercase font-semibold">Management</label>
                <?= nav_link(base_url('dentist/appointments'), 'fas fa-calendar-check', 'Appointments', $currentUrl) ?>
                <?= nav_link(base_url('dentist/patients'), 'fas fa-user-injured', 'Patients', $currentUrl) ?>
                <?= nav_link(base_url('dentist/procedures'), 'fas fa-notes-medical', 'Procedures', $currentUrl) ?>
            </div>

            <!-- Patient Care Section -->
            <div class="space-y-2 sm:space-y-3">
                <label class="px-2 sm:px-3 text-xs text-gray-500 uppercase font-semibold">Patient Care</label>
                <?= nav_link(base_url('queue'), 'fas fa-users', 'Treatment Queue', $currentUrl) ?>
                <?= nav_link(base_url('checkup'), 'fas fa-stethoscope', 'Patient Checkups', $currentUrl) ?>
                <?= nav_link(base_url('dentist/dashboard'), 'fas fa-tooth', 'Dental Charts', $currentUrl) ?>
                <?= nav_link(base_url('dentist/dashboard'), 'fas fa-file-medical-alt', 'Medical Records', $currentUrl) ?>
            </div>

            <?php elseif ($userType === 'staff'): ?>
            <!-- Management Section -->
            <div class="space-y-2 sm:space-y-3">
                <label class="px-2 sm:px-3 text-xs text-gray-500 uppercase font-semibold">Management</label>
                <?= nav_link(base_url('staff/appointments'), 'fas fa-calendar-check', 'Appointments', $currentUrl) ?>
                <?= nav_link(base_url('staff/patients'), 'fas fa-users', 'Patients', $currentUrl) ?>
                <?= nav_link('#', 'fas fa-file-invoice-dollar', 'Invoices', $currentUrl) ?>
            </div>

            <!-- Patient Flow Section -->
            <div class="space-y-2 sm:space-y-3">
                <label class="px-2 sm:px-3 text-xs text-gray-500 uppercase font-semibold">Patient Flow</label>
                <?= nav_link(base_url('checkin'), 'fas fa-sign-in-alt', 'Patient Check-In', $currentUrl) ?>
            </div>

            <?php elseif ($userType === 'patient'): ?>
            <!-- My Account Section -->
            <div class="space-y-2 sm:space-y-3">
                <label class="px-2 sm:px-3 text-xs text-gray-500 uppercase font-semibold">My Account</label>
                <?= nav_link('#', 'fas fa-calendar-check', 'My Appointments', $currentUrl) ?>
                <?= nav_link('#', 'fas fa-file-medical-alt', 'My Records', $currentUrl) ?>
                <?= nav_link('#', 'fas fa-user-cog', 'Profile', $currentUrl) ?>
            </div>
            <?php endif; ?>

            <!-- Account Section -->
            <div class="space-y-2 sm:space-y-3 border-t border-gray-200 pt-4">
                <label class="px-2 sm:px-3 text-xs text-gray-500 uppercase font-semibold">Account</label>
                <?= nav_link(base_url('auth/logout'), 'fas fa-sign-out-alt', 'Sign Out', $currentUrl) ?>
            </div>
        </nav>
    </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
    const closeSidebar = document.getElementById('closeSidebar');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }
    
    function closeSidebarFn() {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
    
    if (mobileSidebarToggle) {
        mobileSidebarToggle.addEventListener('click', openSidebar);
    }
    
    if (closeSidebar) {
        closeSidebar.addEventListener('click', closeSidebarFn);
    }
    
    if (overlay) {
        overlay.addEventListener('click', closeSidebarFn);
    }
    
    // Close sidebar when clicking on nav links on mobile
    const navLinks = sidebar.querySelectorAll('a[href]');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 1024) { // lg breakpoint
                setTimeout(closeSidebarFn, 150);
            }
        });
    });
    
    // Handle window resize
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) { // lg breakpoint
            closeSidebarFn();
        }
    });

    // Branch Selector Functionality
    const branchSelector = document.getElementById('branchSelector');
    if (branchSelector) {
        branchSelector.addEventListener('change', function() {
            const selectedBranchId = this.value;
            
            // Show loading state
            this.disabled = true;
            this.style.opacity = '0.6';
            
            // Send AJAX request to update branch selection
            fetch('<?= base_url('admin/switch-branch') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                },
                body: JSON.stringify({
                    branch_id: selectedBranchId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload the page to reflect the new branch context
                    window.location.reload();
                } else {
                    alert('Error switching branch: ' + (data.message || 'Unknown error'));
                    // Reset to previous selection
                    this.value = '<?= session('selected_branch_id') ?? '' ?>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error switching branch. Please try again.');
                // Reset to previous selection
                this.value = '<?= session('selected_branch_id') ?? '' ?>';
            })
            .finally(() => {
                // Re-enable selector
                this.disabled = false;
                this.style.opacity = '1';
            });
        });
    }
});
</script> 