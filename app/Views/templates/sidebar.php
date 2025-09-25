<?php
// Normalize $user to an array. Some environments may return non-array values from session('user')
// (for example, a RedirectResponse accidentally passed through). Protect against that.
$user = isset($user) ? $user : (session('user') ?? []);
if (!is_array($user)) {
    // If object provides toArray(), use it. Otherwise fall back to empty array.
    if (is_object($user) && method_exists($user, 'toArray')) {
        try { $user = $user->toArray(); } catch (\Throwable $e) { $user = []; }
    } else {
        $user = [];
    }
}

$userType = $user['user_type'] ?? null;
$currentUrl = current_url();

// Force admin context if we're on an admin route (even if user_type is missing)
$isAdminRoute = strpos($currentUrl, '/admin/') !== false || strpos($currentUrl, '/admin') !== false;
if ($isAdminRoute && !$userType) {
    $userType = 'admin';
}
?>

<!-- Enhanced Sidebar Styles -->
<style>
/* Sidebar scroll enhancements */
#sidebar {
    scroll-behavior: smooth;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f7fafc;
}

#sidebar::-webkit-scrollbar {
    width: 6px;
}

#sidebar::-webkit-scrollbar-track {
    background: #f7fafc;
    border-radius: 3px;
}

#sidebar::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 3px;
    transition: background 0.2s ease;
}

#sidebar::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}

/* Navigation link enhancements */
.nav-link {
    position: relative;
    transition: all 0.2s ease-in-out;
}

.nav-link:hover {
    transform: translateX(2px);
}

.nav-link.active {
    background: linear-gradient(135deg, #ebf8ff 0%, #bee3f8 100%);
    border-right: 3px solid #3182ce;
    font-weight: 600;
}

.nav-link.active i {
    color: #3182ce;
}

/* Loading state for navigation */
.nav-link.loading {
    opacity: 0.6;
    pointer-events: none;
}

.nav-link.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    right: 12px;
    width: 12px;
    height: 12px;
    border: 2px solid #e2e8f0;
    border-top: 2px solid #3182ce;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    transform: translateY(-50%);
}

@keyframes spin {
    0% { transform: translateY(-50%) rotate(0deg); }
    100% { transform: translateY(-50%) rotate(360deg); }
}

/* Smooth transitions */
.nav-section {
    transition: opacity 0.3s ease;
}

/* Mobile optimization */
@media (max-width: 1023px) {
    #sidebar {
        backdrop-filter: blur(10px);
    }
}

/* Ensure sidebar remains fixed/sticky on large screens to avoid shifting when page content scrolls */
@media (min-width: 1024px) {
    /* Fixed sidebar is opt-in: only apply when #sidebar has class .sidebar-fixed */
    #sidebar.sidebar-fixed {
        position: fixed !important;
        top: 0;
        left: 0;
        width: 16rem; /* 256px */
        height: 100vh;
        overflow-y: auto;
        z-index: 50;
    }

    /* Utility: add this class to main content wrappers that should reserve space for the fixed sidebar.
       Use padding instead of margin to avoid increasing the outer width (which can cause horizontal scroll).
       Also make the main content scrollable inside the page so the sidebar stays fixed. */
    .with-sidebar-offset-active {
        padding-left: 16rem !important; /* reserve sidebar width inside the page container */
        box-sizing: border-box !important;
        margin-left: 0 !important;
        overflow-x: hidden !important;
    }

    /* When the page container has the offset class, make its <main> area scroll internally so long
       content doesn't push the sidebar. This is a safe global behavior for dashboards/layouts.
       Use min-height:0 so flex children can shrink and overflow properly. */
    .with-sidebar-offset-active > main {
        min-height: 0;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }
}

/* Chart responsiveness helpers (global) */
.chart-responsive {
    width: 100%;
    height: 100%;
    position: relative;
}
.doughnut-wrapper { display:flex; flex-direction:column; align-items:center; justify-content:center; }

/* Cap doughnut size on small screens */
@media (max-width: 640px) {
    .doughnut-wrapper canvas { width: 200px !important; height: 200px !important; }
}
</style>

<!-- Mobile menu button -->
<div class="lg:hidden fixed top-4 left-4 z-50">
    <button id="mobileSidebarToggle" class="p-2 rounded-md bg-white shadow-lg border border-gray-200 text-gray-600 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <i class="fas fa-bars text-lg"></i>
    </button>
</div>

<!-- Mobile overlay -->
<div id="sidebarOverlay" class="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden hidden"></div>

<!-- Sidebar -->
<aside id="sidebar" class="fixed lg:relative inset-y-0 left-0 z-50 flex flex-col w-64 h-screen px-4 sm:px-5 py-6 sm:py-8 overflow-y-auto bg-white border-r shadow-lg lg:shadow-none transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out" data-scroll-preserve="true">
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
                $isActive = strpos($currentUrl, $href) !== false && $href !== '#';
                $baseClass = 'nav-link flex items-center px-2 sm:px-3 py-2 sm:py-2 text-gray-600 transition-all duration-200 transform rounded-lg hover:bg-gray-100 hover:text-gray-700 text-sm sm:text-base';
                $activeClass = $isActive ? 'active bg-blue-50 text-blue-700' : '';
                return "<a class=\"{$baseClass} {$activeClass}\" href=\"{$href}\" data-nav-link>
                    <i class=\"{$icon} w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0\"></i>
                    <span class=\"mx-2 text-xs sm:text-sm font-medium\">{$label}</span>
                </a>";
            }
            ?>
            
            <!-- Dashboard Section -->
            <div class="nav-section space-y-2 sm:space-y-3">
                <label class="px-2 sm:px-3 text-xs text-gray-500 uppercase font-semibold">Dashboard</label>
                <?= nav_link(base_url($userType ? $userType . '/dashboard' : '/'), 'fas fa-tachometer-alt', 'Dashboard', $currentUrl) ?>
            </div>

            <?php if ($userType === 'admin'): ?>
            <!-- Management Section -->
            <div class="nav-section space-y-2 sm:space-y-3">
                <label class="px-2 sm:px-3 text-xs text-gray-500 uppercase font-semibold">Management</label>
                <?= nav_link(base_url('admin/users'), 'fas fa-user-cog', 'Users', $currentUrl) ?>
                <?= nav_link(base_url('admin/patients'), 'fas fa-users', 'Patients', $currentUrl) ?>
                <?= nav_link(base_url('admin/appointments'), 'fas fa-calendar-alt', 'Appointments', $currentUrl) ?>
                <?= nav_link(base_url('admin/services'), 'fas fa-stethoscope', 'Services', $currentUrl) ?>
                <?= nav_link(base_url('admin/procedures'), 'fas fa-procedures', 'Procedures', $currentUrl) ?>
                <?= nav_link(base_url('admin/prescriptions'), 'fas fa-file-prescription', 'Prescriptions', $currentUrl) ?>
                <?= nav_link(base_url('admin/waitlist'), 'fas fa-clipboard-list', 'Waitlist', $currentUrl) ?>
                <?= nav_link(base_url('admin/records'), 'fas fa-folder-open', 'Records', $currentUrl) ?>
                <?= nav_link(base_url('admin/invoices'), 'fas fa-file-invoice-dollar', 'Invoices', $currentUrl) ?>
            </div>

            <!-- Patient Flow Section -->
            <div class="nav-section space-y-2 sm:space-y-3">
                <label class="px-2 sm:px-3 text-xs text-gray-500 uppercase font-semibold">Patient Flow</label>
                <?= nav_link(base_url('checkin'), 'fas fa-sign-in-alt', 'Patient Check-In', $currentUrl) ?>
                <?= nav_link(base_url('queue'), 'fas fa-users', 'Treatment Queue', $currentUrl) ?>
                <?= nav_link(base_url('checkup'), 'fas fa-stethoscope', 'Checkup Module', $currentUrl) ?>
            </div>

            <!-- Administration Section -->
            <div class="space-y-2 sm:space-y-3">
                <label class="px-2 sm:px-3 text-xs text-gray-500 uppercase font-semibold">Administration</label>
                <?= nav_link(base_url('admin/roles'), 'fas fa-user-shield', 'Role Permission', $currentUrl) ?>
                <?= nav_link(base_url('admin/branches'), 'fas fa-code-branch', 'Branches', $currentUrl) ?>
                <?= nav_link(base_url('admin/settings'), 'fas fa-cog', 'Settings', $currentUrl) ?>
            </div>

            <?php elseif ($userType === 'dentist'): ?>
            <!-- Management Section -->
            <div class="space-y-2 sm:space-y-3">
                <label class="px-2 sm:px-3 text-xs text-gray-500 uppercase font-semibold">Management</label>
                <?= nav_link(base_url('dentist/appointments'), 'fas fa-calendar-check', 'Appointments', $currentUrl) ?>
                <?= nav_link(base_url('dentist/patients'), 'fas fa-user-injured', 'Patients', $currentUrl) ?>
            </div>

            <!-- Patient Care Section -->
            <div class="space-y-2 sm:space-y-3">
                <label class="px-2 sm:px-3 text-xs text-gray-500 uppercase font-semibold">Patient Care</label>
                <?= nav_link(base_url('queue'), 'fas fa-users', 'Treatment Queue', $currentUrl) ?>
                <?= nav_link(base_url('dentist/dashboard'), 'fas fa-file-medical-alt', 'Medical Records', $currentUrl) ?>
                <?= nav_link(base_url('dentist/availability'), 'fas fa-calendar-times', 'Availability', $currentUrl) ?>
            </div>

            <?php elseif ($userType === 'staff'): ?>
            <!-- Management Section -->
            <div class="space-y-2 sm:space-y-3">
                <label class="px-2 sm:px-3 text-xs text-gray-500 uppercase font-semibold">Management</label>
                <?= nav_link(base_url('staff/appointments'), 'fas fa-calendar-check', 'Appointments', $currentUrl) ?>
                <?= nav_link(base_url('staff/patients'), 'fas fa-users', 'Patients', $currentUrl) ?>
                <?= nav_link(base_url('staff/records'), 'fas fa-folder-open', 'Patient Records', $currentUrl) ?>
                <?= nav_link(base_url('staff/waitlist'), 'fas fa-clipboard-list', 'Waitlist', $currentUrl) ?>
                <?= nav_link('#', 'fas fa-file-invoice-dollar', 'Invoices', $currentUrl) ?>
            </div>

            <!-- Patient Flow Section -->
            <div class="space-y-2 sm:space-y-3">
                <label class="px-2 sm:px-3 text-xs text-gray-500 uppercase font-semibold">Patient Flow</label>
                <?= nav_link(base_url('checkin'), 'fas fa-sign-in-alt', 'Patient Check-In', $currentUrl) ?>
                <?= nav_link(base_url('queue'), 'fas fa-users', 'Treatment Queue', $currentUrl) ?>
                <?= nav_link(base_url('checkup'), 'fas fa-stethoscope', 'Checkup Module', $currentUrl) ?>
            </div>

            <?php elseif ($userType === 'patient'): ?>
            <!-- Patient Sidebar (reordered & concise) -->
            <div class="space-y-2 sm:space-y-3">
                <label class="px-2 sm:px-3 text-xs text-gray-500 uppercase font-semibold">Patient Menu</label>
                <!-- Appointments parent linking to My Appointments -->
                <?= nav_link(base_url('patient/appointments'), 'fas fa-calendar-check', 'My Appointments', $currentUrl) ?>
                <?= nav_link(base_url('patient/calendar'), 'fas fa-calendar-alt', 'My Calendar', $currentUrl) ?>
                <?= nav_link(base_url('patient/records'), 'fas fa-file-medical-alt', 'My Records', $currentUrl) ?>
                <?= nav_link(base_url('patient/notifications'), 'fas fa-bell', 'Notifications', $currentUrl) ?>
                <?php /* Removed: My Invoice and My Prescriptions are now available in My Records to avoid redundancy */ ?>
            </div>
            <?php endif; ?>

            <!-- Account Section -->
            <div class="space-y-2 sm:space-y-3 border-t border-gray-200 pt-4">
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
    
    // Scroll Position Preservation
    const SCROLL_STORAGE_KEY = 'perfectsmile_sidebar_scroll';
    
    function saveSidebarScrollPosition() {
        if (sidebar) {
            const scrollPosition = sidebar.scrollTop;
            localStorage.setItem(SCROLL_STORAGE_KEY, scrollPosition.toString());
        }
    }
    
    function restoreSidebarScrollPosition() {
        if (sidebar) {
            const savedScrollPosition = localStorage.getItem(SCROLL_STORAGE_KEY);
            if (savedScrollPosition) {
                // Use setTimeout to ensure DOM is fully rendered
                setTimeout(() => {
                    sidebar.scrollTop = parseInt(savedScrollPosition, 10);
                }, 100);
            }
        }
    }
    
    // Restore scroll position on page load
    restoreSidebarScrollPosition();
    
    // Save scroll position periodically and on scroll
    if (sidebar) {
        sidebar.addEventListener('scroll', () => {
            // Debounce scroll saving to avoid excessive localStorage writes
            clearTimeout(sidebar.scrollSaveTimeout);
            sidebar.scrollSaveTimeout = setTimeout(saveSidebarScrollPosition, 200);
        });
    }
    
    // Save scroll position before navigation
    window.addEventListener('beforeunload', saveSidebarScrollPosition);
    
    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        // Restore scroll position when opening sidebar on mobile
        restoreSidebarScrollPosition();
    }
    
    function closeSidebarFn() {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        // Save scroll position when closing sidebar on mobile
        saveSidebarScrollPosition();
    }
    
    if (mobileSidebarToggle) {
        mobileSidebarToggle.addEventListener('click', openSidebar);
    }
    // Also allow the topbar toggle button to open the mobile sidebar on small screens
    const topbarSidebarToggle = document.getElementById('sidebarToggleTop');
    if (topbarSidebarToggle) {
        topbarSidebarToggle.addEventListener('click', openSidebar);
    }
    
    if (closeSidebar) {
        closeSidebar.addEventListener('click', closeSidebarFn);
    }
    
    if (overlay) {
        overlay.addEventListener('click', closeSidebarFn);
    }
    
    // Enhanced navigation handling with scroll preservation
    const navLinks = sidebar.querySelectorAll('a[data-nav-link]');
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            const rawHref = link.getAttribute('href');
            // Handle placeholder/hash links so they DON'T jump to top
            if (!rawHref || rawHref === '#') {
                e.preventDefault(); // stop default scroll-to-top
                return; // keep scroll position intact
            }
            // Ignore if already loading
            if (link.classList.contains('loading')) return;

            // Save scroll position before full navigation
            saveSidebarScrollPosition();

            // Add loading state feedback
            link.classList.add('loading');

            // Close sidebar on mobile after brief delay
            if (window.innerWidth < 1024) {
                setTimeout(closeSidebarFn, 120);
            }

            // Safety: remove loading class if navigation is blocked (e.g., prevented elsewhere)
            setTimeout(() => {
                link.classList.remove('loading');
            }, 1800);
        });
        
        // Add visual feedback on hover
        link.addEventListener('mouseenter', () => {
            if (!link.classList.contains('active') && !link.classList.contains('loading')) {
                link.style.transform = 'translateX(2px)';
            }
        });
        
        link.addEventListener('mouseleave', () => {
            if (!link.classList.contains('active')) {
                link.style.transform = 'translateX(0)';
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
            
            // Save scroll position before branch change
            saveSidebarScrollPosition();
            
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
                    if (typeof showInvoiceAlert === 'function') showInvoiceAlert('Error switching branch: ' + (data.message || 'Unknown error'), 'error', 5000); else alert('Error switching branch: ' + (data.message || 'Unknown error'));
                    // Reset to previous selection
                    this.value = '<?= session('selected_branch_id') ?? '' ?>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof showInvoiceAlert === 'function') showInvoiceAlert('Error switching branch. Please try again.', 'error', 5000); else alert('Error switching branch. Please try again.');
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
    
    // Visual feedback for active navigation states
    function highlightActiveNavItem() {
        const currentPath = window.location.pathname;
        navLinks.forEach(link => {
            const linkPath = new URL(link.href).pathname;
            // More precise active state detection
            const isActive = (currentPath === linkPath) || 
                           (currentPath.startsWith(linkPath) && linkPath !== '/' && linkPath.length > 1);
            
            if (isActive && !link.href.includes('#')) {
                link.classList.add('active', 'bg-blue-50', 'text-blue-700');
                const icon = link.querySelector('i');
                if (icon) {
                    icon.classList.add('text-blue-600');
                }
            } else {
                link.classList.remove('active', 'bg-blue-50', 'text-blue-700');
                const icon = link.querySelector('i');
                if (icon) {
                    icon.classList.remove('text-blue-600');
                }
            }
        });
    }
    
    // Apply active state highlighting
    highlightActiveNavItem();
    
    // Smooth scroll behavior for sidebar
    if (sidebar) {
        sidebar.style.scrollBehavior = 'smooth';
    }
    
    // Add scroll position indicator
    function updateScrollIndicator() {
        const scrollPercentage = (sidebar.scrollTop / (sidebar.scrollHeight - sidebar.clientHeight)) * 100;
        // You can add a scroll indicator here if needed
    }
    
    if (sidebar) {
        sidebar.addEventListener('scroll', updateScrollIndicator);
    }
    
    // Keyboard navigation support
    document.addEventListener('keydown', (e) => {
        // Alt + S to focus sidebar
        if (e.altKey && e.key === 's') {
            e.preventDefault();
            sidebar.focus();
        }
    });
    
    // Debug information (remove in production)
    console.log('Perfect Smile Sidebar: Enhanced navigation initialized');
    console.log('Saved scroll position:', localStorage.getItem(SCROLL_STORAGE_KEY));
    console.log('Active nav links:', navLinks.length);
});
</script> 

<script>
// Apply content offset to elements that opt-in via data-sidebar-offset attribute
function applyContentOffset() {
    try {
        const shouldOffset = window.innerWidth >= 1024; // only on large screens
        const targets = document.querySelectorAll('[data-sidebar-offset]');
        const sidebar = document.getElementById('sidebar');
        // If there are explicit opt-in targets, use them. Otherwise fall back to the page <main> element
        // so most dashboards automatically get the fixed-sidebar + scrollable content UX.
        let applied = false;
        if (targets.length && shouldOffset) {
            if (sidebar) sidebar.classList.add('sidebar-fixed');
            targets.forEach(el => el.classList.add('with-sidebar-offset-active'));
            applied = true;
        } else if (shouldOffset) {
            const main = document.querySelector('main');
            if (main) {
                // Check if the page already uses proper flex layout
                const isFlexLayout = main.closest('.flex') && main.closest('.flex-1');
                
                if (!isFlexLayout) {
                    // Only apply offset for non-flex layouts
                    if (sidebar) sidebar.classList.add('sidebar-fixed');
                    main.classList.add('with-sidebar-offset-active');
                    applied = true;
                }
            }
        }

        // If not applied (small screens or no main), remove fixed behavior gracefully
        if (!applied) {
            if (sidebar) sidebar.classList.remove('sidebar-fixed');
            targets.forEach(el => el.classList.remove('with-sidebar-offset-active'));
            const main = document.querySelector('main');
            if (main) main.classList.remove('with-sidebar-offset-active');
        }
        // Dev log
        console.log('applyContentOffset: targets=', targets.length, 'sidebarFixed=', sidebar ? sidebar.classList.contains('sidebar-fixed') : false);
    } catch (e) {
        console.warn('applyContentOffset error', e);
    }
}

window.addEventListener('load', applyContentOffset);
window.addEventListener('resize', applyContentOffset);
</script>