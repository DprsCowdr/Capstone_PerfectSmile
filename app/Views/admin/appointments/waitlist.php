<?= view('templates/header') ?>

<div class="min-h-screen bg-gray-50 flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    <div class="flex-1 flex flex-col min-h-screen min-w-0 overflow-hidden">
        <!-- Topbar -->
        <nav class="flex items-center justify-between bg-white shadow px-6 py-4 mb-6 flex-shrink-0">
            <button id="sidebarToggleTop" class="block lg:hidden text-gray-600 mr-3 text-2xl focus:outline-none">
                <i class="fa fa-bars"></i>
            </button>
            <div class="flex items-center ml-auto">
                <span class="mr-4 hidden lg:inline text-gray-600 font-semibold"><?= $user['name'] ?? 'Admin' ?></span>
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
        <main class="flex-1 px-6 pb-6 overflow-auto min-w-0">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Waitlist Management</h1>
            <p class="text-gray-600 mb-6">Review and approve pending appointment requests. These requests are NOT yet booked - they will only become appointments after approval.</p>

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
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <i class="fas fa-clock text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Pending Appointments</p>
                            <p class="text-2xl font-bold text-gray-900"><?= count($pendingAppointments) ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <i class="fas fa-calendar-check text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Today's Appointments</p>
                            <p class="text-2xl font-bold text-gray-900"><?= count(array_filter($pendingAppointments, function($apt) { return date('Y-m-d') === $apt['appointment_date']; })) ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <i class="fas fa-users text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Unique Patients</p>
                            <p class="text-2xl font-bold text-gray-900"><?= count(array_unique(array_column($pendingAppointments, 'user_id'))) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Controls -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <div class="flex flex-col lg:flex-row gap-4 items-center">
                    <!-- Search Bar -->
                    <div class="flex-1 w-full lg:w-auto">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" id="searchInput" placeholder="Search by patient name, email, or phone..." 
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        </div>
                    </div>
                    
                    <!-- Filter Dropdown -->
                    <div class="w-full lg:w-auto">
                        <select id="filterDropdown" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="all" data-origin="all">All Appointments</option>
                            <option value="patient" data-origin="patient">Patient Appointments</option>
                            <option value="guest" data-origin="guest">Guest Bookings</option>
                        </select>
                    </div>
                    
                    <!-- Refresh Button -->
                    <button id="refreshBtn" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                        <i class="fas fa-sync-alt mr-2"></i>
                        Refresh
                    </button>
                </div>
            </div>

            <!-- Pending Appointments -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-clock text-orange-500 mr-3"></i>
                        Pending Appointment Requests
                        <span id="resultCount" class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded-full"></span>
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">Review and approve appointment requests before they become bookings</p>
                </div>
                
                <?php if (!empty($pendingAppointments)): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned Dentist</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-48">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="appointmentsTableBody">
                                <?php foreach ($pendingAppointments as $appointment): ?>
                                    <tr class="hover:bg-gray-50 appointment-row" 
                                        data-origin="<?= !empty($appointment['user_id']) ? 'patient' : 'guest' ?>"
                                        data-patient-name="<?= strtolower(esc($appointment['patient_name'])) ?>"
                                        data-patient-email="<?= strtolower(esc($appointment['patient_email'])) ?>"
                                        data-patient-phone="<?= esc($appointment['patient_phone'] ?? '') ?>"
                                        <?= !empty($appointment['dentist_id']) ? 'data-dentist-id="' . esc($appointment['dentist_id']) . '"' : '' ?>>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8">
                                                    <div class="h-8 w-8 rounded-full <?= !empty($appointment['user_id']) ? 'bg-blue-100' : 'bg-green-100' ?> flex items-center justify-center">
                                                        <i class="fas fa-user <?= !empty($appointment['user_id']) ? 'text-blue-600' : 'text-green-600' ?> text-xs"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-gray-900 truncate max-w-32">
                                                        <?= esc($appointment['patient_name']) ?>
                                                        <?php if (empty($appointment['user_id'])): ?>
                                                            <span class="ml-2 px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Guest</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="text-xs text-gray-500 truncate max-w-32"><?= esc($appointment['patient_email']) ?></div>
                                                    <?php if (!empty($appointment['patient_phone'])): ?>
                                                        <div class="text-xs text-gray-400 truncate max-w-32"><?= esc($appointment['patient_phone']) ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><?= date('M j, Y', strtotime($appointment['appointment_date'])) ?></div>
                                            <div class="text-xs text-gray-500">
                                                <?= date('g:i A', strtotime($appointment['appointment_time'])) ?>
                                                <span id="conflict-indicator-<?= $appointment['id'] ?>" class="ml-2 hidden">
                                                    <i class="fas fa-exclamation-triangle text-orange-500" title="Potential scheduling conflict"></i>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 max-w-24 truncate">
                                            <?= $appointment['branch_name'] ?>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 max-w-24 truncate">
                                            <?php if ($appointment['dentist_name']): ?>
                                                <span class="text-green-600 font-medium"><?= $appointment['dentist_name'] ?></span>
                                            <?php else: ?>
                                                <span class="text-red-500 italic text-xs">Not assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                                <?= $appointment['appointment_type'] === 'scheduled' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' ?>">
                                                <?= ucfirst($appointment['appointment_type']) ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Pending Request
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex flex-col space-y-1 w-44">
                                                <button onclick="approveAppointment(<?= $appointment['id'] ?>, '<?= $appointment['dentist_name'] ? 'assigned' : 'unassigned' ?>')" 
                                                        class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-xs transition-colors truncate"
                                                        data-appointment-id="<?= $appointment['id'] ?>"
                                                        data-appointment-date="<?= $appointment['appointment_date'] ?>"
                                                        data-appointment-time="<?= date('H:i', strtotime($appointment['appointment_time'])) ?>"
                                                        data-branch-id="<?= $appointment['branch_id'] ?>"
                                                        data-dentist-id="<?= $appointment['dentist_id'] ?? '' ?>"
                                                        data-duration="<?= $appointment['procedure_duration'] ?? '30' ?>"
                                                        data-service-id="<?= $appointment['service_id'] ?? '' ?>">
                                                    <i class="fas fa-check mr-1"></i><?= $appointment['dentist_name'] ? 'Approve' : 'Assign & Approve' ?>
                                                </button>
                                                <button onclick="declineAppointment(<?= $appointment['id'] ?>)" 
                                                        class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs transition-colors">
                                                    <i class="fas fa-times mr-1"></i>Decline
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12">
                        <i class="fas fa-check-circle text-6xl text-green-300 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">No Pending Requests</h3>
                        <p class="text-gray-500">All appointment requests have been reviewed and processed.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
        <footer class="bg-white py-4 shadow-inner flex-shrink-0">
            <div class="text-center text-gray-500 text-sm">
                &copy; Perfect Smile <?= date('Y') ?>
            </div>
        </footer>
    </div>
</div>

<?= view('templates/partials/loading_overlay') ?>

<!-- Decline Appointment Modal -->
<div id="declineModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Decline Appointment</h3>
                <form id="declineForm" method="POST">
                    <div class="mb-4">
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Reason for Declining</label>
                        <textarea id="reason" name="reason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500" required></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeDeclineModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors">
                            Confirm Decline
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Search and Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const filterDropdown = document.getElementById('filterDropdown');
    const refreshBtn = document.getElementById('refreshBtn');
    const appointmentRows = document.querySelectorAll('.appointment-row');
    const resultCount = document.getElementById('resultCount');
    
    // Initialize result count
    updateResultCount();
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        filterAppointments();
    });
    
    // Filter dropdown functionality
    filterDropdown.addEventListener('change', function() {
        filterAppointments();
    });
    
    // Refresh button functionality
    refreshBtn.addEventListener('click', function() {
        const icon = refreshBtn.querySelector('i');
        icon.classList.add('fa-spin');
        
        // Simulate loading and reload the page
        setTimeout(() => {
            location.reload();
        }, 500);
    });
    
    function filterAppointments() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const selectedFilter = filterDropdown.value;
        let visibleCount = 0;
        
        appointmentRows.forEach(row => {
            let showRow = true;
            
            // Apply origin filter
            if (selectedFilter !== 'all') {
                const rowOrigin = row.getAttribute('data-origin');
                if (rowOrigin !== selectedFilter) {
                    showRow = false;
                }
            }
            
            // Apply search filter
            if (showRow && searchTerm) {
                const patientName = row.getAttribute('data-patient-name') || '';
                const patientEmail = row.getAttribute('data-patient-email') || '';
                const patientPhone = row.getAttribute('data-patient-phone') || '';
                
                const searchableText = `${patientName} ${patientEmail} ${patientPhone}`.toLowerCase();
                
                if (!searchableText.includes(searchTerm)) {
                    showRow = false;
                }
            }
            
            // Show/hide row
            if (showRow) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        updateResultCount(visibleCount);
        
        // Show no results message if needed
        showNoResultsMessage(visibleCount === 0 && (searchTerm || selectedFilter !== 'all'));
    }
    
    function updateResultCount(count = null) {
        if (count === null) {
            count = appointmentRows.length;
        }
        resultCount.textContent = `${count} result${count !== 1 ? 's' : ''}`;
    }
    
    function showNoResultsMessage(show) {
        let noResultsRow = document.getElementById('noResultsRow');
        
        if (show && !noResultsRow) {
            // Create no results row
            noResultsRow = document.createElement('tr');
            noResultsRow.id = 'noResultsRow';
            noResultsRow.innerHTML = `
                <td colspan="7" class="px-4 py-12 text-center">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-semibold text-gray-600 mb-2">No appointments found</h3>
                        <p class="text-gray-500">Try adjusting your search terms or filters.</p>
                    </div>
                </td>
            `;
            document.getElementById('appointmentsTableBody').appendChild(noResultsRow);
        } else if (!show && noResultsRow) {
            noResultsRow.remove();
        }
    }
});

// Auto-refresh functionality with improved UX
let autoRefreshInterval;
function startAutoRefresh() {
    autoRefreshInterval = setInterval(function() {
        // Only refresh if there are no active modals or user interactions
        if (!document.querySelector('.fixed.inset-0.bg-gray-600') && !document.activeElement.matches('input, select, textarea')) {
            location.reload();
        }
    }, 30000);
}

// Start auto-refresh
startAutoRefresh();

// Pause auto-refresh when user is interacting with search/filters
document.getElementById('searchInput').addEventListener('focus', () => clearInterval(autoRefreshInterval));
document.getElementById('searchInput').addEventListener('blur', () => setTimeout(startAutoRefresh, 5000));
document.getElementById('filterDropdown').addEventListener('focus', () => clearInterval(autoRefreshInterval));
document.getElementById('filterDropdown').addEventListener('blur', () => setTimeout(startAutoRefresh, 5000));

function showLoading(){
    const el = document.getElementById('globalLoadingOverlay');
    if (el) el.classList.remove('hidden');
}

function hideLoading(){
    const el = document.getElementById('globalLoadingOverlay');
    if (el) el.classList.add('hidden');
}
</script>

<script>
function approveAppointment(appointmentId, isAssigned) {
    const confirmMessage = isAssigned === 'assigned'
        ? 'Are you sure you want to approve this appointment? (Dentist is already assigned)'
        : 'Are you sure you want to approve this appointment? You will need to assign a dentist.';

    if (confirm(confirmMessage)) {
        // Get appointment data from the button that was clicked
        const approveButton = document.querySelector(`button[data-appointment-id="${appointmentId}"]`);
        if (!approveButton) {
            alert('Could not find appointment data');
            return;
        }

        const appointmentData = {
            appointment_id: appointmentId,
            date: approveButton.dataset.appointmentDate,
            time: approveButton.dataset.appointmentTime,
            branch_id: approveButton.dataset.branchId,
            dentist_id: approveButton.dataset.dentistId,
            duration: approveButton.dataset.duration || '30',
            service_id: approveButton.dataset.serviceId
        };

        // Before final approval, call server to check conflicts and get suggestions
        const checkData = new FormData();
        checkData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        checkData.append('appointment_id', appointmentData.appointment_id);
        checkData.append('date', appointmentData.date);
        checkData.append('time', appointmentData.time);
        checkData.append('branch_id', appointmentData.branch_id);
        checkData.append('dentist_id', appointmentData.dentist_id);
        checkData.append('duration', appointmentData.duration);
        if (appointmentData.service_id) {
            checkData.append('service_id', appointmentData.service_id);
        }

        showLoading();

        fetch(`<?= base_url() ?><?= isset($isStaff) && $isStaff ? 'staff' : 'admin' ?>/appointments/check-conflicts`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: checkData
        })
        .then(r => r.text())
        .then(text => {
            let resp;
            try { resp = JSON.parse(text); } catch (e) { resp = null; }
            // If check endpoint returned a conflict payload, show suggestions modal
            if (resp && resp.success === false && (resp.conflict || resp.hasConflicts)) {
                hideLoading();
                // Show conflict modal with suggestions if available
                showConflictModal(appointmentId, resp.suggestions || [], resp);
                return;
            }

            // Otherwise, proceed to approve (server side will still validate strict conflicts)
            const formData = new FormData();
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

            return fetch(`<?= base_url() ?><?= isset($isStaff) && $isStaff ? 'staff' : 'admin' ?>/appointments/approve/${appointmentId}`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
        })
        .then(response => {
            // If previous branch returned early (conflict), response may be undefined
            if (!response) return;
            return response.text();
        })
        .then(text => {
            if (!text) return;
            let data;
            try { data = JSON.parse(text); } catch (e) { data = null; }
            hideLoading();
            if (data && data.success) {
                location.reload();
            } else if (data && Array.isArray(data.suggestions) && data.suggestions.length > 0) {
                showConflictModal(appointmentId, data.suggestions);
            } else {
                if (data && data.message && data.message.includes('No dentists available')) {
                    alert('No dentists available for auto-assignment. Please manually select a dentist.');
                    showDentistSelectionModal(appointmentId);
                } else {
                    alert('Error: ' + (data && data.message ? data.message : 'Unknown error'));
                }
            }
        })
        .catch(error => { 
            hideLoading(); 
            console.error('Error:', error); 
            alert('Failed to check/approve: ' + error.message); 
        });
    }
}

function showConflictModal(appointmentId, suggestions, conflictData) {
    // Build modal content with enhanced admin options
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 z-50';
    
    // Build conflict details
    const conflictDetails = conflictData ? `
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
            <h4 class="text-sm font-semibold text-red-800 mb-2">Conflict Details:</h4>
            <p class="text-sm text-red-700">${conflictData.message || 'Scheduling conflict detected'}</p>
            ${conflictData.metadata ? `
                <div class="mt-2 text-xs text-red-600">
                    Requested: ${conflictData.metadata.requested_date} at ${conflictData.metadata.requested_time} 
                    (${conflictData.metadata.duration_minutes}min)
                </div>
            ` : ''}
        </div>
    ` : '';
    
    // Render suggestions as radio list so user can explicitly pick one
    const suggestionsHtml = suggestions.length ? suggestions.map((s, idx) => {
        // Use server-provided timestamp for robust client-side parsing
        const ts = s.timestamp || (s.datetime ? Math.floor(new Date(s.datetime).getTime()/1000) : null);
        const timeDisplay = s.time || s;
        const endTime = s.ends_at ? ` (ends ${s.ends_at})` : '';
        const aligned = s.aligned ? ` <span class="text-blue-600 text-xs">[${s.aligned}]</span>` : '';
        return `
            <li class="py-2 flex items-center border-b border-gray-100 last:border-b-0">
                <label class="flex items-center space-x-3 w-full cursor-pointer hover:bg-gray-50 p-2 rounded">
                    <input type="radio" name="suggestion" value="${ts}" data-datetime="${s.datetime || ''}" ${idx===0? 'checked':''} class="suggestion-radio" />
                    <span class="flex-1">
                        <strong>${timeDisplay}</strong>${endTime}${aligned}
                        ${s.available === false ? ' <span class="text-red-500 text-xs">(unavailable)</span>' : ''}
                    </span>
                </label>
            </li>
        `;
    }).join('') : '<li class="py-2 text-gray-500 text-center">No alternative suggestions available</li>';
    
    const hasValidSuggestions = suggestions.length > 0 && suggestions.some(s => s.available !== false);
    
    modal.innerHTML = `
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-lg w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-exclamation-triangle text-orange-500 mr-2"></i>
                        Scheduling Conflict Detected
                    </h3>
                    
                    ${conflictDetails}
                    
                    <div class="mb-6">
                        <p class="text-sm text-gray-600 mb-4">
                            This appointment conflicts with existing bookings. Choose an option below:
                        </p>
                        
                        ${hasValidSuggestions ? `
                            <div class="mb-4">
                                <h4 class="text-sm font-semibold text-gray-800 mb-2">Suggested Alternative Times:</h4>
                                <ul id="suggestionList" class="border border-gray-200 rounded-lg max-h-48 overflow-y-auto">
                                    ${suggestionsHtml}
                                </ul>
                            </div>
                        ` : ''}
                    </div>
                    
                    <div class="flex flex-col space-y-3">
                        ${hasValidSuggestions ? `
                            <button type="button" onclick="autoRescheduleApprove(${appointmentId})" 
                                    class="w-full px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors">
                                <i class="fas fa-calendar-check mr-2"></i>Reschedule & Approve
                            </button>
                        ` : ''}
                        
                        <button type="button" onclick="approveAnyway(${appointmentId})" 
                                class="w-full px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-colors">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Approve Anyway (Override Conflict)
                        </button>
                        
                        <button type="button" onclick="closeConflictModal()" 
                                class="w-full px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg transition-colors">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

function closeConflictModal() {
    const modal = document.querySelector('.fixed.inset-0.bg-gray-600');
    if (modal) modal.remove();
}

function autoRescheduleApprove(appointmentId) {
    // Pick selected suggestion radio value
    const radio = document.querySelector('.suggestion-radio:checked');
    const chosenTs = radio ? parseInt(radio.value, 10) : null;
    if (!chosenTs) {
        alert('No suggested time selected to auto-reschedule');
        return;
    }

    // Prefer server-provided datetime (YYYY-MM-DD HH:MM:SS) to avoid timezone/formatting issues
    const radioEl = radio;
    let chosenDate = null;
    let chosenTime = null;
    const serverDatetime = radioEl.getAttribute('data-datetime');
    if (serverDatetime) {
        // serverDatetime expected as 'YYYY-MM-DD HH:MM:SS'
        const parts = serverDatetime.split(' ');
        if (parts.length >= 2) {
            chosenDate = parts[0];
            chosenTime = parts[1].slice(0,5);
        }
    }

    if (!chosenDate || !chosenTime) {
        // fallback to timestamp-derived values
        const dt = new Date(chosenTs * 1000);
        chosenDate = dt.toISOString().slice(0,10);
        chosenTime = dt.toTimeString().slice(0,5); // HH:MM
    }

    const formData = new FormData();
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    formData.append('auto_reschedule', '1');
    // Send the server-provided datetime (preferred) and timestamp (fallback)
    const serverDatetimeSend = serverDatetime || '';
    if (serverDatetimeSend) {
        formData.append('chosen_datetime', serverDatetimeSend);
    }
    formData.append('chosen_timestamp', String(chosenTs));

    showLoading();
    closeConflictModal();

    fetch(`<?= base_url() ?>admin/appointments/approve/${appointmentId}`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.text())
    .then(text => {
        let data;
        try { data = JSON.parse(text); } catch (e) { data = null; }
        hideLoading();
        if (data && data.success) {
            alert('Appointment rescheduled and approved successfully!');
            location.reload();
        } else {
            alert('Failed to reschedule appointment: ' + (data ? data.message : 'Unknown error'));
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        alert('Failed to reschedule appointment: ' + error.message);
    });
}

function approveAnyway(appointmentId) {
    if (!confirm('Are you sure you want to approve this appointment despite the scheduling conflict? This may cause overlapping appointments.')) {
        return;
    }

    const formData = new FormData();
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    formData.append('force_approve', '1'); // Flag to override conflicts

    showLoading();
    closeConflictModal();

    fetch(`<?= base_url() ?>admin/appointments/approve/${appointmentId}`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.text())
    .then(text => {
        let data;
        try { data = JSON.parse(text); } catch (e) { data = null; }
        hideLoading();
        if (data && data.success) {
            alert('Appointment approved successfully (conflict overridden)!');
            location.reload();
        } else {
            alert('Failed to approve appointment: ' + (data ? data.message : 'Unknown error'));
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        alert('Failed to approve appointment: ' + error.message);
    });
    }

function showDentistSelectionModal(appointmentId) {
    // Create modal for dentist selection
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 z-50';
    modal.innerHTML = `
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Select Dentist</h3>
                    <div class="mb-4">
                        <label for="dentistSelect" class="block text-sm font-medium text-gray-700 mb-2">Choose a dentist:</label>
                        <select id="dentistSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Select Dentist --</option>
                            <?php 
                            $userModel = new \App\Models\UserModel();
                            $dentists = $userModel->where('user_type', 'dentist')->where('status', 'active')->findAll();
                            foreach ($dentists as $dentist): 
                            ?>
                            <option value="<?= $dentist['id'] ?>"><?= $dentist['name'] ?> (ID: <?= $dentist['id'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeDentistModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                            Cancel
                        </button>
                        <button type="button" onclick="confirmDentistAssignment(${appointmentId})" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors">
                            Assign & Approve
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

function closeDentistModal() {
    const modal = document.querySelector('.fixed.inset-0.bg-gray-600');
    if (modal) {
        modal.remove();
    }
}

function confirmDentistAssignment(appointmentId) {
    const dentistSelect = document.getElementById('dentistSelect');
    const dentistId = dentistSelect.value;
    
    if (!dentistId) {
        alert('Please select a dentist');
        return;
    }
    
    const formData = new FormData();
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    formData.append('dentist_id', dentistId);

    // Improve UX: show global loading overlay while request is in-flight
    closeDentistModal();
    showLoading();

    fetch(`<?= base_url() ?>admin/appointments/approve/${appointmentId}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('Non-JSON response:', text);
            hideLoading();
            alert('Server returned an unexpected response. Please check the logs.');
            return;
        }
        hideLoading();
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        alert('An error occurred while approving the appointment. Please check the console for details.');
    });
}

function declineAppointment(appointmentId) {
    // Store the appointment ID in a data attribute on the form
    document.getElementById('declineForm').setAttribute('data-appointment-id', appointmentId);
    document.getElementById('declineModal').classList.remove('hidden');
}

function closeDeclineModal() {
    document.getElementById('declineModal').classList.add('hidden');
    document.getElementById('reason').value = '';
}

// Handle decline form submission
document.addEventListener('DOMContentLoaded', function() {
    const declineForm = document.getElementById('declineForm');
    if (declineForm) {
        declineForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const reason = document.getElementById('reason').value;
            if (!reason.trim()) {
                alert('Please provide a reason for declining');
                return;
            }
            
            const appointmentId = this.getAttribute('data-appointment-id');
            if (!appointmentId) {
                alert('Appointment ID not found');
                return;
            }
            
            const formData = new FormData();
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            formData.append('reason', reason);
            
            fetch(`<?= base_url() ?><?= isset($isStaff) && $isStaff ? 'staff' : 'admin' ?>/appointments/decline/${appointmentId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.text())
            .then(text => {
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Non-JSON response:', text);
                    alert('Server returned an unexpected response. Please check the logs.');
                    return;
                }
                if (data.success) {
                    closeDeclineModal();
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while declining the appointment. Please check the console for details.');
            });
        });
    }
});
</script>

<?= view('templates/footer') ?> 