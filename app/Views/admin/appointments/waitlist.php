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

            <!-- Pending Appointments -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-clock text-orange-500 mr-3"></i>
                        Pending Appointment Requests
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
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($pendingAppointments as $appointment): ?>
                                    <tr class="hover:bg-gray-50" <?= !empty($appointment['dentist_id']) ? 'data-dentist-id="' . esc($appointment['dentist_id']) . '"' : '' ?> >
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8">
                                                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                        <i class="fas fa-user text-blue-600 text-xs"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-gray-900 truncate max-w-32"><?= $appointment['patient_name'] ?></div>
                                                    <div class="text-xs text-gray-500 truncate max-w-32"><?= $appointment['patient_email'] ?></div>
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
                                                        class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-xs transition-colors truncate">
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
function approveAppointment(appointmentId, isAssigned) {
    const confirmMessage = isAssigned === 'assigned'
        ? 'Are you sure you want to approve this appointment? (Dentist is already assigned)'
        : 'Are you sure you want to approve this appointment? You will need to assign a dentist.';

    if (confirm(confirmMessage)) {
        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        fetch(`<?= base_url() ?><?= isset($isStaff) && $isStaff ? 'staff' : 'admin' ?>/appointments/approve/${appointmentId}`, {
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
                location.reload();
            } else {
                if (data.message && data.message.includes('No dentists available')) {
                    alert('No dentists available for auto-assignment. Please manually select a dentist.');
                    showDentistSelectionModal(appointmentId);
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while approving the appointment. Please check the console for details.');
        });
    }
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
            alert('Server returned an unexpected response. Please check the logs.');
            return;
        }
        if (data.success) {
            closeDentistModal();
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
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

// Auto-refresh every 30 seconds to check for new pending appointments
setInterval(function() {
    location.reload();
}, 30000);
</script>

<?= view('templates/footer') ?> 