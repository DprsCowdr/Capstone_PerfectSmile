<?= view('templates/header') ?>

<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <!-- Topbar -->
        <nav class="flex items-center justify-between bg-white shadow px-6 py-4 mb-6">
            <button id="sidebarToggleTop" class="block lg:hidden text-gray-600 mr-3 text-2xl focus:outline-none">
                <i class="fa fa-bars"></i>
            </button>
            <div class="flex items-center ml-auto">
                <span class="mr-4 hidden lg:inline text-gray-600 font-semibold"><?= $user['name'] ?? 'Staff' ?></span>
                <div class="relative">
                    <button class="focus:outline-none">
                        <img class="w-10 h-10 rounded-full border-2 border-gray-200" src="<?= base_url('img/undraw_profile.svg') ?>" alt="Profile">
                    </button>
                </div>
            </div>
        </nav>
        
        <main class="flex-1 p-8">
                <!-- Header -->
                <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-xl shadow-lg mb-8">
                    <div class="p-6 text-white">
                        <h1 class="text-3xl font-bold flex items-center">
                            <i class="fas fa-sign-in-alt mr-4"></i>
                            Patient Check-in
                        </h1>
                        <p class="mt-2 opacity-90">Manage patient arrivals and check-ins for today</p>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 mb-8">
                    <?php 
                    $confirmedCount = 0;
                    $checkedInCount = 0;
                    $ongoingCount = 0;
                    $completedCount = 0;
                    
                    foreach ($appointments as $appointment) {
                        switch ($appointment['status']) {
                            case 'confirmed': $confirmedCount++; break;
                            case 'checked_in': $checkedInCount++; break;
                            case 'ongoing': $ongoingCount++; break;
                            case 'completed': $completedCount++; break;
                        }
                    }
                    ?>
                    <div class="bg-white rounded-xl shadow p-6 flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-calendar-check text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Scheduled</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $confirmedCount ?></p>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow p-6 flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-user-check text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Checked In</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $checkedInCount ?></p>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow p-6 flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <i class="fas fa-user-md text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">In Treatment</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $ongoingCount ?></p>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow p-6 flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-check-circle text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Completed</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $completedCount ?></p>
                        </div>
                    </div>
                </div>

                <!-- Appointments List -->
                <div class="bg-white rounded-xl shadow-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-list-alt mr-3"></i>
                            Today's Appointments
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">Check in patients as they arrive</p>
                    </div>
                    <div class="p-6">
                        <?php if (empty($appointments)): ?>
                            <div class="text-center py-12">
                                <i class="fas fa-calendar-times text-4xl text-gray-400 mb-4"></i>
                                <p class="text-gray-500 text-lg">No appointments scheduled for today</p>
                            </div>
                        <?php else: ?>
                            <!-- Desktop Table -->
                            <div class="hidden md:block overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dentist</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($appointments as $appointment): ?>
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?= date('g:i A', strtotime($appointment['appointment_datetime'])) ?>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900"><?= esc($appointment['patient_name']) ?></div>
                                                    <div class="text-sm text-gray-500"><?= esc($appointment['patient_phone']) ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900"><?= esc($appointment['dentist_name']) ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php
                                                    $statusColors = [
                                                        'confirmed' => 'bg-blue-100 text-blue-800',
                                                        'checked_in' => 'bg-green-100 text-green-800',
                                                        'ongoing' => 'bg-yellow-100 text-yellow-800',
                                                        'completed' => 'bg-slate-100 text-slate-800'
                                                    ];
                                                    $statusClass = $statusColors[$appointment['status']] ?? 'bg-gray-100 text-gray-800';
                                                    ?>
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= $statusClass ?>">
                                                        <?= ucfirst(str_replace('_', ' ', $appointment['status'])) ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <?php if ($appointment['status'] === 'confirmed'): ?>
                                                        <form method="POST" action="<?= base_url('checkin/process/' . $appointment['id']) ?>" class="inline checkin-form" data-patient-name="<?= esc($appointment['patient_name']) ?>">
                                                            <?= csrf_field() ?>
                                                            <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                                <i class="fas fa-sign-in-alt mr-2"></i>
                                                                Check In
                                                            </button>
                                                        </form>
                                                    <?php elseif ($appointment['status'] === 'checked_in'): ?>
                                                        <span class="text-green-600 text-sm">
                                                            <i class="fas fa-check-circle mr-1"></i>
                                                            Checked In
                                                            <?php if (!empty($appointment['checked_in_at'])): ?>
                                                                <br><small class="text-gray-500">at <?= date('g:i A', strtotime($appointment['checked_in_at'])) ?></small>
                                                            <?php endif; ?>
                                                        </span>
                                                        <br>
                                                        <form method="POST" action="<?= base_url('queue/call/' . $appointment['id']) ?>" class="inline mt-2 send-to-treatment">
                                                            <?= csrf_field() ?>
                                                            <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                                <i class="fas fa-user-md mr-2"></i>
                                                                Send to Treatment
                                                            </button>
                                                        </form>
                                                    <?php elseif ($appointment['status'] === 'ongoing'): ?>
                                                        <span class="text-yellow-600 text-sm">
                                                            <i class="fas fa-user-md mr-1"></i>
                                                            In Treatment
                                                        </span>
                                                    <?php elseif ($appointment['status'] === 'completed'): ?>
                                                        <span class="text-blue-600 text-sm">
                                                            <i class="fas fa-check-circle mr-1"></i>
                                                            Completed
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <!-- Mobile Card View -->
                            <div class="md:hidden space-y-4">
                                <?php foreach ($appointments as $appointment): ?>
                                    <div class="rounded-xl shadow border border-gray-100 p-4 bg-white flex flex-col gap-2">
                                        <div class="flex items-center justify-between">
                                            <div class="text-lg font-bold text-purple-700">
                                                <?= date('g:i A', strtotime($appointment['appointment_datetime'])) ?>
                                            </div>
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php
                                                $statusColors = [
                                                    'confirmed' => 'bg-blue-100 text-blue-800',
                                                    'checked_in' => 'bg-green-100 text-green-800',
                                                    'ongoing' => 'bg-yellow-100 text-yellow-800',
                                                    'completed' => 'bg-slate-100 text-slate-800'
                                                ];
                                                echo $statusColors[$appointment['status']] ?? 'bg-gray-100 text-gray-800';
                                            ?>">
                                                <?= ucfirst(str_replace('_', ' ', $appointment['status'])) ?>
                                            </span>
                                        </div>
                                        <div class="text-base font-semibold text-gray-900">
                                            <?= esc($appointment['patient_name']) ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?= esc($appointment['patient_phone']) ?>
                                        </div>
                                        <div class="text-sm text-gray-700">
                                            <i class="fas fa-user-md mr-1"></i><?= esc($appointment['dentist_name']) ?>
                                        </div>
                                        <div>
                                            <?php if ($appointment['status'] === 'confirmed'): ?>
                                                <form method="POST" action="<?= base_url('checkin/process/' . $appointment['id']) ?>" class="inline checkin-form" data-patient-name="<?= esc($appointment['patient_name']) ?>">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="appointment_id" value="<?= $appointment['id'] ?>">
                                                    <button type="submit" class="w-full mt-2 inline-flex items-center justify-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                        <i class="fas fa-sign-in-alt mr-2"></i>
                                                        Check In
                                                    </button>
                                                </form>
                                            <?php elseif ($appointment['status'] === 'checked_in'): ?>
                                                <span class="text-green-600 text-sm">
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    Checked In
                                                    <?php if (!empty($appointment['checked_in_at'])): ?>
                                                        <br><small class="text-gray-500">at <?= date('g:i A', strtotime($appointment['checked_in_at'])) ?></small>
                                                    <?php endif; ?>
                                                </span>
                                                <form method="POST" action="<?= base_url('queue/call/' . $appointment['id']) ?>" class="inline mt-2 send-to-treatment">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="w-full mt-2 inline-flex items-center justify-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                        <i class="fas fa-user-md mr-2"></i>
                                                        Send to Treatment
                                                    </button>
                                                </form>
                                            <?php elseif ($appointment['status'] === 'ongoing'): ?>
                                                <span class="text-yellow-600 text-sm">
                                                    <i class="fas fa-user-md mr-1"></i>
                                                    In Treatment
                                                </span>
                                            <?php elseif ($appointment['status'] === 'completed'): ?>
                                                <span class="text-blue-600 text-sm">
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    Completed
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<script>
// Simple toast helper to show AJAX feedback without opening devtools
function showToast(msg, isError) {
    var container = document.getElementById('ajax-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'ajax-toast-container';
        container.style.position = 'fixed';
        container.style.right = '20px';
        container.style.bottom = '20px';
        container.style.zIndex = 9999;
        document.body.appendChild(container);
    }
    var el = document.createElement('div');
    el.textContent = msg;
    el.style.background = isError ? 'rgba(220,38,38,0.95)' : 'rgba(37,99,235,0.95)';
    el.style.color = '#fff';
    el.style.padding = '10px 14px';
    el.style.marginTop = '8px';
    el.style.borderRadius = '6px';
    el.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
    container.appendChild(el);
    setTimeout(function(){ el.style.transition='opacity 300ms'; el.style.opacity=0; setTimeout(()=>el.remove(),300); }, 4000);
}

let formSubmissionInProgress = false;

// Auto-refresh page every 30 seconds to update real-time status
// But only if no form submission is in progress
function scheduleRefresh() {
    setTimeout(function() {
        if (!formSubmissionInProgress) {
            console.log('Auto-refreshing page...');
            window.location.reload();
        } else {
            console.log('Form submission in progress, delaying refresh...');
            scheduleRefresh(); // Try again later
        }
    }, 30000);
}

// Start the refresh timer
scheduleRefresh();

// Scoped handlers for check-in and send-to-treatment to avoid intercepting unrelated forms
document.querySelectorAll('.checkin-form').forEach(function(form){
    form.addEventListener('submit', function(e){
        e.preventDefault();
        var name = form.dataset.patientName || 'this patient';
        if (!confirm('Check in ' + name + '?')) return;
        var action = form.getAttribute('action');
        var body = new FormData(form);
        formSubmissionInProgress = true;
        console.debug && console.debug('Sending check-in via', action);
        fetch(action, { method: 'POST', body: body, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(res){ return res.json ? res.json().catch(()=>null) : null; })
        .then(function(data){
            console.debug('check-in response', data);
            if (data && data.success) {
                showToast('Check-in successful', false);
                setTimeout(function(){ window.location.reload(); }, 900);
            } else if (data && (data.error || data.message)) {
                showToast('Error: ' + (data.error || data.message), true);
                formSubmissionInProgress = false;
            } else {
                // fallback to normal submit if server returned HTML/redirect
                formSubmissionInProgress = false;
                form.submit();
            }
        })
        .catch(function(err){
            console.error('Failed to check in', err);
            formSubmissionInProgress = false;
            form.submit();
        });
    });
});

// Intercept send-to-treatment forms to perform a fetch and show immediate feedback (prevents full-page hangs)
document.querySelectorAll('.send-to-treatment').forEach(function(form){
    form.addEventListener('submit', function(e){
        e.preventDefault();
        var name = form.closest('tr')?.querySelector('td:nth-child(2) .text-sm.font-medium')?.innerText || form.dataset.patientName || 'this patient';
        if (!confirm('Send to treatment queue for ' + name + '?')) return;
        var action = form.getAttribute('action');
        var body = new FormData(form);
        formSubmissionInProgress = true;
        console.debug && console.debug('Sending to treatment via', action);
        fetch(action, { method: 'POST', body: body, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(res){
            // Try to parse JSON, otherwise return null
            return res.json ? res.json().catch(()=>null) : null;
        })
        .then(function(data){
            console.debug('send-to-treatment response', data);
            // on structured success, reload to show updated queue; on error, show message and re-enable
            if (data && data.success) {
                showToast('Patient sent to treatment', false);
                setTimeout(function(){ window.location.reload(); }, 900);
            } else if (data && data.error) {
                showToast('Error: ' + (data.error || data.message || 'Unknown'), true);
                formSubmissionInProgress = false;
            } else {
                // If server returned HTML/redirect or unexpected payload, fallback to full submit
                formSubmissionInProgress = false;
                form.submit();
            }
        })
        .catch(function(err){
            console.error('Failed to send to treatment', err);
            formSubmissionInProgress = false;
            // fallback to normal submit to preserve behavior
            form.submit();
        });
    });
});

// Debug: Log when page finishes loading
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded, found', document.querySelectorAll('.checkin-form').length, 'check-in forms');
});
</script>

<?= view('templates/footer') ?>
