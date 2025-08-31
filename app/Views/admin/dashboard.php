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
        <main class="flex-1 px-6 pb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Admin Dashboard</h1>
            
            <?php if (isset($selectedBranchId) && $selectedBranchId): ?>
            <!-- Branch Context Indicator -->
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-building text-blue-600 mr-3 text-lg"></i>
                    <div>
                        <h3 class="text-sm font-semibold text-blue-800">Current Branch Context</h3>
                        <p class="text-sm text-blue-700">
                            <?php
                            $branchModel = new \App\Models\BranchModel();
                            $branch = $branchModel->find($selectedBranchId);
                            echo esc($branch['name'] ?? 'Unknown Branch');
                            ?>
                        </p>
                    </div>
                    <div class="ml-auto">
                        <a href="<?= base_url('admin/switch-branch') ?>" 
                           onclick="document.getElementById('branchSelector').value = ''; document.getElementById('branchSelector').dispatchEvent(new Event('change')); return false;"
                           class="text-xs text-blue-600 hover:text-blue-800 underline">
                            Switch to All Branches
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Branch-specific dashboard moved to a dedicated view: admin/branch_dashboard.php -->
            
            <!-- Cards Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
                <!-- Total Users Card -->
                <div class="bg-white border-l-4 border-indigo-400 shadow rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-indigo-600 uppercase mb-1">Total Users</div>
                        <div class="text-2xl font-bold text-gray-800"><?= $totalUsers ?? 0 ?></div>
                    </div>
                    <i class="fas fa-users fa-2x text-gray-300"></i>
                </div>
                <!-- Today's Appointments Card -->
                <div class="bg-white border-l-4 border-green-400 shadow rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-green-600 uppercase mb-1">Today's Appointments</div>
                        <div class="text-2xl font-bold text-gray-800"><?= count($todayAppointments ?? []) ?></div>
                    </div>
                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                </div>
                <!-- Pending Approvals Card -->
                <div class="bg-white border-l-4 border-orange-400 shadow rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-orange-600 uppercase mb-1">Pending Approvals</div>
                        <div class="text-2xl font-bold text-gray-800"><?= count($pendingAppointments ?? []) ?></div>
                    </div>
                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                </div>
                <!-- Total Branches Card -->
                <div class="bg-white border-l-4 border-blue-400 shadow rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-blue-600 uppercase mb-1">Total Branches</div>
                        <div class="text-2xl font-bold text-gray-800"><?= $totalBranches ?? 0 ?></div>
                    </div>
                    <i class="fas fa-building fa-2x text-gray-300"></i>
                </div>
            </div>
            <!-- Quick Actions & Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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
                                    <div class="flex gap-2">
                                        <button onclick="approveAppointment(<?= $appointment['id'] ?>)" 
                                                class="bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1 rounded text-sm font-semibold transition-colors">
                                            <i class="fas fa-check mr-1"></i> Approve
                                        </button>
                                        <button onclick="declineAppointment(<?= $appointment['id'] ?>)" 
                                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm font-semibold transition-colors">
                                            <i class="fas fa-times mr-1"></i> Decline
                                        </button>
                                    </div>
                                </div>
                                <?php if ($appointment['remarks']): ?>
                                <div class="mt-3 text-sm text-gray-600 italic">
                                    <i class="fas fa-comment mr-1"></i> <?= $appointment['remarks'] ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-4 text-center">
                            <a href="<?= base_url('admin/appointments') ?>" class="text-orange-600 hover:text-orange-700 font-semibold">
                                View All Appointments →
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Quick Actions -->
                <div class="bg-white shadow rounded-lg mb-6">
                    <div class="border-b px-6 py-3">
                        <h2 class="text-lg font-bold text-slate-700">Quick Actions</h2>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <a href="<?= base_url('admin/patients') ?>" class="flex items-center justify-center gap-2 bg-slate-600 hover:bg-slate-700 text-white font-semibold py-3 rounded-lg transition"><i class="fas fa-user-plus"></i> Manage Patients</a>
                        <a href="<?= base_url('admin/appointments') ?>" class="flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition"><i class="fas fa-calendar-plus"></i> Manage Appointments</a>
                        <a href="<?= base_url('admin/services') ?>" class="flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 rounded-lg transition"><i class="fas fa-stethoscope"></i> Manage Services</a>
                        <a href="<?= base_url('admin/branches') ?>" class="flex items-center justify-center gap-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 rounded-lg transition"><i class="fas fa-building"></i> Manage Branches</a>
                    </div>
                </div>
                <!-- Recent Activity -->
                <div class="bg-white shadow rounded-lg mb-6">
                    <div class="border-b px-6 py-3">
                        <h2 class="text-lg font-bold text-slate-700">Recent Activity</h2>
                    </div>
                    <div class="p-6">
                        <div class="mb-4">
                            <div class="text-sm font-semibold text-gray-700">New patient registration</div>
                            <div class="text-xs text-gray-400">2 minutes ago</div>
                        </div>
                        <div class="border-t my-2"></div>
                        <div class="mb-4">
                            <div class="text-sm font-semibold text-gray-700">Appointment scheduled</div>
                            <div class="text-xs text-gray-400">15 minutes ago</div>
                        </div>
                        <div class="border-t my-2"></div>
                        <div class="mb-4">
                            <div class="text-sm font-semibold text-gray-700">Payment received</div>
                            <div class="text-xs text-gray-400">1 hour ago</div>
                        </div>
                        <div class="border-t my-2"></div>
                        <div class="mb-4">
                            <div class="text-sm font-semibold text-gray-700">New doctor added</div>
                            <div class="text-xs text-gray-400">2 hours ago</div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Pending Cancellation Requests -->
            <?php if (!empty($pendingCancellationRequests)): ?>
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="border-b px-6 py-3">
                    <h2 class="text-lg font-bold text-red-700 flex items-center">
                        <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                        Cancellation Requests (<?= count($pendingCancellationRequests) ?>)
                    </h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4 max-h-96 overflow-y-auto">
                        <?php foreach ($pendingCancellationRequests as $item):
                            $apt = $item['appointment'];
                            $note = $item['notification'];
                        ?>
                        <div class="border border-red-200 rounded-lg p-4 bg-red-50">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h3 class="font-semibold text-gray-800"><?= esc($apt['user_id'] ? 'Patient ID: ' . $apt['user_id'] : 'Unknown') ?></h3>
                                    <div class="text-sm text-gray-600"><?= esc($apt['remarks'] ?? '') ?></div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-500"><?= date('M j, Y', strtotime($apt['appointment_datetime'])) ?></div>
                                    <div class="font-semibold text-gray-800"><?= date('g:i A', strtotime($apt['appointment_datetime'])) ?></div>
                                </div>
                            </div>
                            <?php if (!empty($item['reason'])): ?>
                            <div class="text-sm italic text-gray-700 mb-2">Reason: <?= esc($item['reason']) ?></div>
                            <?php endif; ?>
                            <div class="flex space-x-2">
                                <button onclick="approveCancellation(<?= (int)$apt['id'] ?>)" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm"><i class="fas fa-check mr-1"></i>Approve Cancel</button>
                                <button onclick="rejectCancellation(<?= (int)$apt['id'] ?>)" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm"><i class="fas fa-times mr-1"></i>Reject Cancel</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </main>
        <footer class="bg-white py-4 mt-auto shadow-inner">
            <div class="text-center text-gray-500 text-sm">
                &copy; Perfect Smile <?= date('Y') ?>
            </div>
        </footer>
    </div>
</div>

<script>
// Helpers for cancellation approval/rejection (send CSRF as form data)
function postForm(url, data){
    data = data || {};
    data['<?= csrf_token() ?>'] = '<?= csrf_hash() ?>';
    const params = new URLSearchParams();
    Object.keys(data).forEach(k => { if (data[k] !== undefined && data[k] !== null) params.append(k, data[k]); });
    return fetch(url, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json'
        },
        body: params.toString()
    }).then(r => r.json().catch(() => ({ success: false, message: 'Invalid JSON response', status: r.status })));
}

function approveCancellation(id){
    if(!confirm('Approve cancellation for appointment #' + id + '?')) return;
    postForm('<?= base_url('staff/appointments/approve-cancel') ?>/' + id, {}).then(res => {
        console.log('approveCancellation response', res);
        if(res && res.success){
            alert('Cancellation approved. The timeslot is now freed.');
            location.reload();
        } else {
            alert('Failed to approve: ' + (res?.message || 'Unknown error'));
        }
    }).catch(e => { console.error(e); alert('Network error'); });
}

function rejectCancellation(id){
    var reason = prompt('Enter a reason for rejecting this cancellation request (optional):');
    postForm('<?= base_url('staff/appointments/reject-cancel') ?>/' + id, { reason: reason }).then(res => {
        console.log('rejectCancellation response', res);
        if(res && res.success){
            alert('Cancellation request rejected.');
            location.reload();
        } else {
            alert('Failed to reject: ' + (res?.message || 'Unknown error'));
        }
    }).catch(e => { console.error(e); alert('Network error'); });
}
function approveAppointment(appointmentId) {
    if (confirm('Are you sure you want to approve this appointment?')) {
        // Show dentist selection dialog
        const dentistId = prompt('Enter dentist ID to assign to this appointment:');
        if (!dentistId) {
            alert('Dentist ID is required');
            return;
        }
        
        const formData = new FormData();
        formData.append('dentist_id', dentistId);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        
        fetch(`<?= base_url() ?>admin/appointments/approve/${appointmentId}`, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                location.reload();
            } else {
                alert('Failed to approve appointment');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to approve appointment');
        });
    }
}

function declineAppointment(appointmentId) {
    const reason = prompt('Please provide a reason for declining this appointment:');
    if (reason) {
        const formData = new FormData();
        formData.append('reason', reason);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        
        fetch(`<?= base_url() ?>admin/appointments/decline/${appointmentId}`, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                location.reload();
            } else {
                alert('Failed to decline appointment');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to decline appointment');
        });
    }
}
</script>

<?= view('templates/footer') ?> 