<?php $user = $user ?? session('user') ?? []; ?>

<?= view('templates/header') ?>

<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user ?? null]) ?>
    <div class="flex-1 flex flex-col min-h-screen bg-white">
        <?= view('templates/patient_topbar', ['user' => $user ?? null]) ?>

        <!-- Main Content -->
        <main class="flex-1 px-6 pb-6" data-sidebar-offset>
            <h1 class="text-2xl font-semibold text-gray-800 mb-6">My Appointments</h1>
            
            <!-- Search and Filter Controls -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
                <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                    <!-- Search Bar -->
                    <div class="flex-1 max-w-md">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" id="appointmentSearch" 
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500" 
                                   placeholder="Search appointments...">
                        </div>
                    </div>
                    
                    <!-- Filter Dropdown -->
                    <div class="flex items-center">
                        <label for="appointmentFilter" class="sr-only">Filter appointments</label>
                        <select id="appointmentFilter" class="block pl-3 pr-8 py-2 border border-gray-300 rounded-md bg-white text-sm text-gray-700 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="all">All</option>
                            <option value="upcoming">Upcoming</option>
                            <option value="current">Current</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                
                <!-- Results Summary -->
                <div class="mt-3 text-sm text-gray-600" id="resultsInfo">
                    Showing all appointments
                </div>
            </div>
            
                <?php if (!empty($appointments)): ?>
                    <div class="overflow-x-auto bg-white rounded-lg shadow-sm border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approval</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="appointmentsTableBody">
                                <?php foreach ($appointments as $appointment): ?>
                                    <?php
                                        // Compute time range: prefer explicit procedure_duration, otherwise sum linked services durations, fallback to 15 minutes
                                        $startTs = strtotime($appointment['appointment_datetime']);
                                        $durationMinutes = null;
                                        if (!empty($appointment['procedure_duration'])) {
                                            $durationMinutes = (int)$appointment['procedure_duration'];
                                        } else {
                                            // attempt to compute from appointment_service -> services (duration_max_minutes or duration_minutes)
                                            try {
                                                $db = \Config\Database::connect();
                                                $row = $db->table('appointment_service')
                                                          ->select('SUM(COALESCE(services.duration_max_minutes, services.duration_minutes, 0)) as total')
                                                          ->join('services', 'services.id = appointment_service.service_id', 'left')
                                                          ->where('appointment_service.appointment_id', $appointment['id'])
                                                          ->get()->getRowArray();
                                                if (!empty($row) && !empty($row['total'])) $durationMinutes = (int)$row['total'];
                                            } catch (\Throwable $t) {
                                                // ignore DB errors and fallback later
                                                $durationMinutes = null;
                                            }
                                        }
                                        if (empty($durationMinutes) || $durationMinutes <= 0) $durationMinutes = 15; // sensible default
                                        $endTs = $startTs + ($durationMinutes * 60);
                                        $timeLabel = date('g:i A', $startTs) . ' - ' . date('g:i A', $endTs);
                                        $dateLabel = date('F j, Y', $startTs);
                                        $patientName = $appointment['patient_name'] ?? $appointment['patient'] ?? '';
                                        $searchText = strtolower($patientName . ' ' . $dateLabel . ' ' . $timeLabel . ' ' . ($appointment['status'] ?? '') . ' ' . ($appointment['approval_status'] ?? '') . ' ' . ($appointment['remarks'] ?? ''));
                                    ?>
                                    <tr class="hover:bg-gray-50 appointment-row" 
                                        data-date="<?= date('Y-m-d', $startTs) ?>"
                                        data-status="<?= strtolower($appointment['status'] ?? 'pending') ?>"
                                        data-search="<?= esc($searchText) ?>">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= esc($patientName ?: '-') ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $dateLabel ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= $timeLabel ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <?php $appointmentStatusClass = match($appointment['status'] ?? 'pending') {
                                                'confirmed', 'scheduled' => 'text-blue-600',
                                                'completed' => 'text-green-600',
                                                'cancelled' => 'text-red-600',
                                                'no_show' => 'text-gray-600',
                                                default => 'text-yellow-600'
                                            }; ?>
                                            <span class="<?= $appointmentStatusClass ?>"><?= ucfirst(str_replace('_', ' ', $appointment['status'] ?? 'Pending')) ?></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <?php $statusClass = match($appointment['approval_status'] ?? 'pending') {
                                                'approved' => 'text-green-600',
                                                'pending' => 'text-yellow-600',
                                                'declined' => 'text-red-600',
                                                default => 'text-gray-600'
                                            }; ?>
                                            <span class="<?= $statusClass ?>"><?= ucfirst($appointment['approval_status'] ?? 'Pending') ?></span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            <?= !empty($appointment['remarks']) ? esc($appointment['remarks']) : '-' ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="<?= base_url('patient/appointments/view/' . ($appointment['id'] ?? '')) ?>" class="text-indigo-600 hover:text-indigo-900 inline-flex items-center px-3 py-1 rounded-md mr-2 view-link">
                                                <i class="fas fa-eye mr-2"></i> View
                                            </a>
                                            <!-- Delete button: shown client-side only for past appointments -->
                                            <button data-appointment-id="<?= $appointment['id'] ?? '' ?>" class="delete-btn text-red-600 hover:text-red-900 inline-flex items-center px-3 py-1 rounded-md" style="display:none;">
                                                <i class="fas fa-trash mr-2"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                        <div class="max-w-md mx-auto">
                            <div class="bg-gray-100 rounded-full p-6 w-24 h-24 mx-auto mb-6 flex items-center justify-center">
                                <i class="fas fa-calendar-times text-3xl text-gray-400"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-600 mb-3">No Appointments Yet</h3>
                            <p class="text-gray-500 mb-6">You haven't booked any appointments yet.</p>
                            <div class="text-sm text-gray-500 bg-blue-50 border border-blue-200 p-4 rounded-lg">
                                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                To book an appointment, please contact the clinic directly or visit during business hours.
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<!-- Removed cancel modal since patient cancellations are disabled -->

<?= view('templates/footer') ?>

<script>
// Search and Filter Functionality
(() => {
    const searchInput = document.getElementById('appointmentSearch');
    const filterSelect = document.getElementById('appointmentFilter');
    const appointmentRows = document.querySelectorAll('.appointment-row');
    const resultsInfo = document.getElementById('resultsInfo');
    const tableBody = document.getElementById('appointmentsTableBody');
    
    let currentFilter = 'all';
    let currentSearch = '';
    
    // Get current date for filtering
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    function getAppointmentCategory(row) {
        const appointmentDate = new Date(row.dataset.date);
        const status = row.dataset.status;
        
        if (status === 'completed') return 'completed';
        if (status === 'cancelled') return 'cancelled';
        
        const daysDiff = Math.ceil((appointmentDate - today) / (1000 * 60 * 60 * 24));
        
        if (daysDiff < 0) return 'past';
        if (daysDiff === 0) return 'current';
        if (daysDiff <= 7) return 'upcoming';
        return 'future';
    }
    
    function filterAppointments() {
        let visibleCount = 0;
        
        appointmentRows.forEach(row => {
            const category = getAppointmentCategory(row);
            const searchText = row.dataset.search;
            
            // Apply filter
            let matchesFilter = false;
            switch (currentFilter) {
                case 'all':
                    matchesFilter = true;
                    break;
                case 'upcoming':
                    matchesFilter = category === 'upcoming' || category === 'future';
                    break;
                case 'current':
                    matchesFilter = category === 'current';
                    break;
                case 'completed':
                    matchesFilter = category === 'completed';
                    break;
                case 'cancelled':
                    matchesFilter = category === 'cancelled';
                    break;
            }
            
            // Apply search
            const matchesSearch = currentSearch === '' || searchText.includes(currentSearch.toLowerCase());
            
            const shouldShow = matchesFilter && matchesSearch;
            row.style.display = shouldShow ? '' : 'none';
            
            if (shouldShow) visibleCount++;
        });
        
        // Update results info
        const totalCount = appointmentRows.length;
        let filterText = currentFilter === 'all' ? 'all' : currentFilter;
        
        if (currentSearch) {
            resultsInfo.textContent = `Showing ${visibleCount} of ${totalCount} appointments matching "${currentSearch}" in ${filterText}`;
        } else {
            resultsInfo.textContent = `Showing ${visibleCount} of ${totalCount} ${filterText} appointments`;
        }
        
        // Show/hide no results message
        const existingNoResults = document.querySelector('.no-results-message');
        if (existingNoResults) existingNoResults.remove();
        
        if (visibleCount === 0 && totalCount > 0) {
            const noResultsRow = document.createElement('tr');
            noResultsRow.className = 'no-results-message';
            noResultsRow.innerHTML = `
                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-search text-gray-300 text-3xl mb-3"></i>
                        <p class="text-lg font-medium">No appointments found</p>
                        <p class="text-sm">Try adjusting your search or filter criteria</p>
                    </div>
                </td>
            `;
            tableBody.appendChild(noResultsRow);
        }
    }
    
    // Search input handler
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            currentSearch = e.target.value.trim();
            filterAppointments();
        });
    }
    
    // Dropdown filter handler
    if (filterSelect) {
        filterSelect.addEventListener('change', (e) => {
            currentFilter = e.target.value;
            filterAppointments();
        });
    }
    
    // Initialize filters
    filterAppointments();
})();

// Delete button handling: show only for past appointments and perform AJAX delete with confirmation
(function() {
    const appointmentRows = document.querySelectorAll('.appointment-row');
    const deleteButtons = document.querySelectorAll('.delete-btn');

    function getAppointmentCategory(row) {
        const today = new Date();
        today.setHours(0,0,0,0);
        const appointmentDate = new Date(row.dataset.date);
        const status = row.dataset.status;
        if (status === 'completed') return 'completed';
        if (status === 'cancelled') return 'cancelled';
        const daysDiff = Math.ceil((appointmentDate - today) / (1000 * 60 * 60 * 24));
        if (daysDiff < 0) return 'past';
        if (daysDiff === 0) return 'current';
        if (daysDiff <= 7) return 'upcoming';
        return 'future';
    }

    // Show delete buttons for past, completed, and cancelled appointments
    appointmentRows.forEach(row => {
        const category = getAppointmentCategory(row);
        if (['past', 'completed', 'cancelled'].includes(category)) {
            const btn = row.querySelector('.delete-btn');
            if (btn) btn.style.display = '';
        }
    });

    // Obtain CSRF token if provided as meta tag (CodeIgniter often uses meta name="csrf-token")
    let csrfName = null, csrfHash = null;
    const metaCsrf = document.querySelector('meta[name="csrf-token"]');
    if (metaCsrf) {
        // expecting content in format: name=token or token only; try to parse
        csrfHash = metaCsrf.getAttribute('content');
        // fallback: look for global JS variable 'csrfToken'
    } else if (window.csrfToken) {
        csrfHash = window.csrfToken;
    }

    // Click handler for delete
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.delete-btn');
        if (!btn) return;
        e.preventDefault();

        const apptId = btn.dataset.appointmentId;
        if (!apptId) return alert('Invalid appointment ID');

        if (!confirm('Are you sure you want to delete this past appointment? This action cannot be undone.')) return;

        // Disable button while request is in progress
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Deleting...';
        btn.disabled = true;

        // Build form data
        const formData = new FormData();
        if (csrfName && csrfHash) {
            formData.append(csrfName, csrfHash);
        }

        fetch('<?= base_url('patient/appointments/delete/') ?>' + apptId, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(r => r.json()).then(json => {
            if (json && json.success) {
                // remove row from DOM
                const row = btn.closest('tr');
                if (row) row.remove();
                // update results info if present
                const resultsInfo = document.getElementById('resultsInfo');
                if (resultsInfo) resultsInfo.textContent = 'Appointment deleted';
            } else {
                alert((json && json.message) ? json.message : 'Failed to delete appointment');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }).catch(err => {
            console.error('Delete request failed', err);
            alert('Failed to delete appointment. Please try again later.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });
})();

// Enhanced patient appointments functionality
(() => {
    // Add smooth transitions to table rows
    const appointmentRows = document.querySelectorAll('table tbody tr');
    appointmentRows.forEach(row => {
        row.style.transition = 'background-color 0.15s ease, transform 0.15s ease';
        row.addEventListener('mouseenter', () => row.style.transform = 'translateY(-1px)');
        row.addEventListener('mouseleave', () => row.style.transform = 'translateY(0)');
    });

    // Enhanced View button functionality with better loading state
    document.addEventListener('click', (e) => {
        const viewButton = e.target.closest('a[href*="/patient/appointments/view/"]');
        if (viewButton) {
            e.preventDefault();
            
            // Store original content
            const originalContent = viewButton.innerHTML;
            const originalClasses = viewButton.className;
            
            // Add loading state
            viewButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
            viewButton.className = originalClasses.replace('bg-indigo-600 hover:bg-indigo-700', 'bg-gray-400 cursor-not-allowed');
            viewButton.style.pointerEvents = 'none';
            
            // Navigate after short delay for better UX
            setTimeout(() => {
                window.location.href = viewButton.href;
            }, 300);
            
            // Fallback reset (in case navigation fails)
            setTimeout(() => {
                viewButton.innerHTML = originalContent;
                viewButton.className = originalClasses;
                viewButton.style.pointerEvents = 'auto';
            }, 5000);
        }
    });

    // Add fade-in animation to table rows
    appointmentRows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateY(8px)';
        setTimeout(() => {
            row.style.transition = 'opacity 0.35s ease, transform 0.35s ease';
            row.style.opacity = '1';
            row.style.transform = 'translateY(0)';
        }, index * 60);
    });

    // Add ripple effect to view links
    document.querySelectorAll('a[href*="/patient/appointments/view/"]').forEach(link => {
        link.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(0, 0, 0, 0.06);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.45s linear;
                pointer-events: none;
                z-index: 10;
            `;

            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);

            setTimeout(() => ripple.remove(), 500);
        });
    });
})();

// Add CSS for ripple animation
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
</script>
