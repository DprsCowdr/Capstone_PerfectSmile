 <?= view('templates/header') ?>
<!-- Dentist dashboard scoped assets (isolated) -->
<link rel="stylesheet" href="<?= base_url('css/dentist-dashboard.css') ?>">
<script src="<?= base_url('js/dentist-dashboard.js') ?>" defer></script>

<!-- Inline fallback (ensures behavior even if external assets 404) -->
<style>
/* Inline fallback: prevent page stretch when sidebar is fixed */
.dentist-dashboard-root.with-sidebar-offset-active { padding-left: 16rem !important; box-sizing: border-box !important; overflow-x: hidden !important; }
#sidebar.sidebar-fixed { width: 16rem !important; }
</style>
<script>
(function(){ window.DENTIST_STATS_URL = '<?= base_url('dentist/stats') ?>'; })();
(function(){
    try {
        function applyInlineOffset(){
            var root = document.querySelector('.dentist-dashboard-root');
            var sidebar = document.getElementById('sidebar');
            if (!root || !sidebar) return;
            var should = window.innerWidth >= 1024 && root.hasAttribute('data-sidebar-offset');
            if (should) { sidebar.classList.add('sidebar-fixed'); root.classList.add('with-sidebar-offset-active'); }
            else { sidebar.classList.remove('sidebar-fixed'); root.classList.remove('with-sidebar-offset-active'); }
        }
        window.addEventListener('load', applyInlineOffset);
        window.addEventListener('resize', applyInlineOffset);
    } catch(e){ console && console.warn && console.warn('inline dentist fallback error', e); }
})();
</script>
<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    <div class="flex-1 flex flex-col min-h-screen bg-white dentist-dashboard-root" data-sidebar-offset>
        <!-- Topbar -->
        <nav class="flex items-center justify-between bg-white shadow px-6 py-4 mb-6">
            <button id="sidebarToggleTop" class="block lg:hidden text-gray-600 mr-3 text-2xl focus:outline-none">
                <i class="fa fa-bars"></i>
            </button>
            <div class="flex items-center ml-auto">
                <span class="mr-4 hidden lg:inline text-gray-600 font-semibold">Dr. <?= $user['name'] ?? 'Dentist' ?></span>
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
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-800">🩺 Dentist Dashboard</h1>
                <div>
                    <button id="openManageAvailability" class="bg-indigo-600 text-white px-3 py-1 rounded text-sm">Manage Availability</button>
                </div>
            </div>

            <!-- Cards Row (clinic-wide stats from admin DashboardService) -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
                <div class="bg-white border-l-4 border-indigo-400 shadow rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-indigo-600 uppercase mb-1">Total Patients</div>
                        <div class="text-2xl font-bold text-gray-800"><span id="dentist-total-patients"><?= isset($statistics['totalPatients']) ? $statistics['totalPatients'] : ($totalPatients ?? 0) ?></span></div>
                    </div>
                    <i class="fas fa-users fa-2x text-gray-300"></i>
                </div>
                <div class="bg-white border-l-4 border-green-400 shadow rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-green-600 uppercase mb-1">Today's Appointments</div>
                        <div class="text-2xl font-bold text-gray-800"><span id="dentist-total-today-appointments"><?= count($todayAppointments ?? []) ?></span></div>
                    </div>
                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                </div>
                <div class="bg-white border-l-4 border-orange-400 shadow rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-orange-600 uppercase mb-1">Pending Approvals</div>
                        <div class="text-2xl font-bold text-gray-800"><span id="dentist-total-pending-approvals"><?= count($pendingAppointments ?? []) ?></span></div>
                    </div>
                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                </div>
                <div class="bg-white border-l-4 border-purple-400 shadow rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-purple-600 uppercase mb-1">Available Dentists</div>
                        <div class="text-2xl font-bold text-gray-800"><span id="dentist-total-dentists"><?= isset($statistics['totalDentists']) ? $statistics['totalDentists'] : 0 ?></span></div>
                    </div>
                    <i class="fas fa-user-md fa-2x text-gray-300"></i>
                </div>
            </div>
            
            <!-- (Removed duplicate Live Stats block - single dashboard section retained later) -->
            
            <!-- Live Chart (replaces Today's Schedule) -->
            <div class="bg-white shadow rounded-lg mb-6 p-6">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-lg font-bold text-slate-700">Live Dashboard</h2>
                    <div>
                        <select id="statsScope" class="text-sm border rounded px-2 py-1">
                            <option value="mine">My stats</option>
                            <option value="clinic">Clinic-wide</option>
                        </select>
                    </div>
                </div>
                <div class="w-full">
                    <div class="w-full bg-white rounded-lg p-4">
                        <div class="flex items-start justify-between mb-2 gap-4">
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="text-sm text-gray-600">Live Chart</div>
                                    <div>
                                        <select id="chartSelectorTop" class="text-sm border rounded px-2 py-1">
                                            <option value="appointments">Appointments Live Chart</option>
                                            <option value="patients">Patients Live Chart</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="chart-responsive" style="position:relative; overflow:hidden; height:clamp(320px,45vh,560px);">
                                    <canvas id="appointmentsChartTop" style="width:100%; display:block; height:100%;"></canvas>
                                </div>
                                <div class="mt-3 text-sm text-gray-700">
                                    <div>Avg per day: <span id="avgPerDayTop" class="font-semibold">—</span></div>
                                    <div class="mt-1">Peak day: <span id="peakDayTop" class="font-semibold">—</span></div>
                                </div>
                            </div>

                            <div class="w-1/3 flex-shrink-0 bg-white">
                                <div class="p-3 rounded-md text-center h-full flex flex-col items-center justify-center">
                                    <div class="doughnut-wrapper" style="height:420px; width:420px; max-width:100%;">
                                        <canvas id="statusChartTop" width="420" height="420" style="max-width:100%; height:auto;"></canvas>
                                    </div>
                                    <div class="mt-3">
                                        <div class="text-sm text-gray-600">Patients (7d)</div>
                                        <div class="text-2xl font-bold" id="patientTotal">—</div>
                                    </div>
                                    <div class="mt-3 text-sm text-gray-500" id="nextAppointment">
                                        <span id="nextAppointmentBadge" class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full mr-2 hidden">Upcoming appointment</span>
                                        <span id="nextAppointmentText">No upcoming appointments</span>
                                    </div>
                                    <!-- status legend removed per design request -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- include Chart.js CDN (required by dentist-dashboard.js) -->
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<?= view('templates/footer') ?>

<!-- Manage Availability Modal -->
<div id="manageAvailabilityModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
    <div class="bg-white rounded-lg shadow-lg w-11/12 max-w-3xl p-6">
        <button id="closeManageAvailability" class="float-right text-2xl">&times;</button>
        <h3 class="text-lg font-bold mb-3">Manage Availability</h3>
        <div class="glass rounded-2xl p-6">
            <div class="flex items-center mb-4">
                <div class="h-10 w-10 rounded-xl bg-red-100 flex items-center justify-center mr-3">
                    <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.864-.833-2.634 0L3.18 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                </div>
                <div>
                    <h4 class="text-lg font-bold">Add Day Off / Emergency</h4>
                    <p class="text-sm text-gray-600">Block specific dates and times</p>
                </div>
            </div>

            <form id="adHocForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Type</label>
                    <input name="type" list="adHocTypeOptions" class="w-full border-2 border-gray-200 rounded-xl p-3 form-transition focus:border-red-500 focus:outline-none bg-white" placeholder="e.g. day_off or Custom label" required>
                    <datalist id="adHocTypeOptions">
                        <option value="day_off">🏖️ Day Off</option>
                        <option value="sick_leave">🤒 Sick Leave</option>
                        <option value="emergency">🚨 Emergency</option>
                        <option value="urgent">⚡ Urgent</option>
                    </datalist>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Start Date & Time</label>
                        <input name="start" type="datetime-local" class="w-full border-2 border-gray-200 rounded-xl p-3 form-transition focus:border-red-500 focus:outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">End Date & Time</label>
                        <input name="end" type="datetime-local" class="w-full border-2 border-gray-200 rounded-xl p-3 form-transition focus:border-red-500 focus:outline-none" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" class="w-full border-2 border-gray-200 rounded-xl p-3 form-transition focus:border-red-500 focus:outline-none resize-none" rows="3" placeholder="Optional notes about this time block..."></textarea>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Create Time Block
                </button>
            </form>
        </div>
    </div>
</div>

<script>
// Manage Availability JS (dentist dashboard)
document.addEventListener('DOMContentLoaded', function(){
    // Small notification helper so dashboard UX matches availability page
    function showNotification(message, type = 'success') {
        let container = document.getElementById('notificationContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notificationContainer';
            container.className = 'fixed top-4 right-4 z-60 space-y-2';
            document.body.appendChild(container);
        }
        const notification = document.createElement('div');
        const colors = {
            success: 'bg-green-500 border-green-600',
            error: 'bg-red-500 border-red-600',
            info: 'bg-blue-500 border-blue-600',
            warning: 'bg-yellow-500 border-yellow-600'
        };
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            info: 'fa-info-circle',
            warning: 'fa-exclamation-triangle'
        };
        notification.className = `${colors[type] || colors.success} text-white px-6 py-3 rounded-xl shadow-lg border-l-4 transform translate-x-full transition-transform duration-500 max-w-sm`;
        notification.innerHTML = `<div class="flex items-center space-x-3"><i class="fas ${icons[type] || icons.success} text-lg"></i><span class="font-medium">${message}</span><button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-white/80 hover:text-white"><i class="fas fa-times"></i></button></div>`;
        container.appendChild(notification);
        setTimeout(() => notification.classList.remove('translate-x-full'), 100);
        setTimeout(() => { notification.classList.add('translate-x-full'); setTimeout(()=>notification.remove(),500); }, 4500);
    }

    const openBtn = document.getElementById('openManageAvailability');
    const modal = document.getElementById('manageAvailabilityModal');
    const closeBtn = document.getElementById('closeManageAvailability');
    const recurringForm = document.getElementById('recurringForm');
    const adHocForm = document.getElementById('adHocForm');
    const userId = <?= $user['id'] ?? 'null' ?>;
    const base = (typeof window !== 'undefined' && window.baseUrl) ? window.baseUrl : '';

    function showModal(){ modal.classList.remove('hidden'); }
    function hideModal(){ modal.classList.add('hidden'); }
    openBtn && openBtn.addEventListener('click', showModal);
    closeBtn && closeBtn.addEventListener('click', hideModal);


    // Minimal modal behavior: open/close and keep ad-hoc form submit handler below

    // loadList removed — modal only contains the ad-hoc form now

    // Recurring working-hours support was removed server-side (410). No client handler is attached.

    adHocForm && adHocForm.addEventListener('submit', async function(e){
        e.preventDefault();
        const fd = new FormData(adHocForm);
        // append CSRF token for POST requests
        fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        fd.append('user_id', userId);

        // Normalize datetime-local inputs to 'YYYY-MM-DD HH:MM:SS' which the server expects
        try {
            const startRaw = fd.get('start');
            const endRaw = fd.get('end');
            if (startRaw) fd.set('start', startRaw.replace('T', ' ') + ':00');
            if (endRaw) fd.set('end', endRaw.replace('T', ' ') + ':00');
        } catch (e) { /* ignore normalization errors */ }

        const body = new URLSearchParams(); fd.forEach((v,k)=> body.append(k,v));
        try{
            const res = await fetch(base + '/dentist/availability/create', {method:'POST', body, credentials:'same-origin'});
            let j = null;
            try { j = await res.json(); } catch(parseErr){
                const text = await res.text().catch(()=> '');
                console.error('Non-JSON response from availability.create', res.status, text.substring(0,200));
                showNotification('Server error: ' + (text || res.statusText || res.status), 'error');
                return;
            }
            if (!res.ok) {
                showNotification(j && j.message ? j.message : ('Request failed: ' + res.status), 'error');
                return;
            }
            if (j && j.success){
                // Dispatch a global event so other calendars on the page refresh availability
                try { window.dispatchEvent(new CustomEvent('availability:changed', { detail: { user_id: userId, id: j.id || null } })); } catch(e){}
                // If there are conflicts, show them and offer to cancel
                const conflicts = (j.conflicts && j.conflicts.length) ? j.conflicts : [];
                if (conflicts.length){
                    let html = '<div class="p-2">The new block overlaps with the following confirmed appointments:<ul class="mt-2">';
                    conflicts.forEach(c=>{
                        html += `<li class="mb-1">${c.patient_name} — ${c.appointment_datetime} <button data-id="${c.id}" class="cancel-conflict ml-2 bg-red-500 text-white px-2 py-1 rounded text-xs">Cancel</button></li>`;
                    });
                    html += '</ul><div class="mt-3 text-sm text-gray-600">You can cancel these appointments now to free the slot; patients will be notified by the clinic.</div></div>';
                    // show a small modal-like confirm using window.confirm fallback
                    const wrapper = document.createElement('div');
                    wrapper.className = 'fixed inset-0 z-60 flex items-center justify-center bg-black bg-opacity-30';
                    // add a 'Flag all for reschedule' button
                    wrapper.innerHTML = `<div class="bg-white rounded p-4 max-w-xl w-11/12">${html}<div class="mt-3 text-right"><button id="flagAllConflicts" class="bg-yellow-500 text-white px-3 py-1 rounded mr-2">Flag all for reschedule</button><button id="closeConflicts" class="bg-gray-200 px-3 py-1 rounded">Close</button></div></div>`;
                    document.body.appendChild(wrapper);
                    // wire cancel buttons
                            Array.from(wrapper.querySelectorAll('.cancel-conflict')).forEach(b=> b.addEventListener('click', async function(){
                        const apptId = this.getAttribute('data-id');
                        if (!confirm('Cancel this appointment? This will remove it from the schedule.')) return;
                        try{
                            const r = await fetch(base + '/dentist/appointments/delete/' + encodeURIComponent(apptId), {method:'POST', credentials:'same-origin'});
                            const jr = await r.json();
                            if (jr && jr.success){ showNotification('Appointment cancelled', 'success'); if (typeof loadList === 'function') loadList(); wrapper.remove(); } else { showNotification(jr.message || 'Failed to cancel', 'error'); }
                        }catch(err){ console.error(err); showNotification('Error cancelling', 'error'); }
                    }));
                    wrapper.querySelector('#closeConflicts').addEventListener('click', ()=> wrapper.remove());
                    // Flag all conflicts handler: collect ids and POST to markConflicts
                    wrapper.querySelector('#flagAllConflicts').addEventListener('click', async function(){
                        if (!confirm('Flag all listed appointments for reschedule? Patients will be notified.')) return;
                        const ids = Array.from(wrapper.querySelectorAll('.cancel-conflict')).map(b=> b.getAttribute('data-id'));
                        if (!ids.length) return showNotification('No appointments to flag', 'info');
                        const fd = new URLSearchParams(); ids.forEach(i=> fd.append('appointment_ids[]', i));
                        try{
                            const r = await fetch(base + '/availability/markConflicts', {method:'POST', body: fd, credentials:'same-origin'});
                            const jr = await r.json();
                            if (jr && jr.success){ showNotification('Appointments flagged for reschedule', 'success'); if (typeof loadList === 'function') loadList(); wrapper.remove(); try{ window.dispatchEvent(new CustomEvent('availability:changed', { detail: { user_id: userId } })); }catch(e){} } else { showNotification(jr.message || 'Failed to flag', 'error'); }
                        }catch(err){ console.error(err); showNotification('Error flagging appointments', 'error'); }
                    });
                }

                // Refresh list
                if (typeof loadList === 'function') loadList();

                // Auto-close the Manage Availability modal when there are no conflicts
                if (!conflicts.length) {
                    try { hideModal(); if (adHocForm && typeof adHocForm.reset === 'function') adHocForm.reset(); } catch(e){}
                    showNotification(j.message || 'Block created', 'success');
                } else {
                    // leave modal open so user can act on conflicts; still show a notice
                    showNotification(j.message || 'Block created with conflicts', 'warning');
                }
        } else showNotification(j.message || 'Failed', 'error');
    }catch(e){ console.error(e); showNotification('Error', 'error'); }
    });
});
</script>

 