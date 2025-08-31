<?= view('templates/header') ?>

<?= view('templates/sidebar', ['user' => $user ?? null]) ?>

<div class="min-h-screen bg-gray-50 flex">
    <div class="flex-1 flex flex-col min-h-screen min-w-0 overflow-hidden" data-sidebar-offset>
        <main class="flex-1 px-6 py-8 bg-white">
            <div class="max-w-3xl mx-auto">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Book Appointment</h1>
                        <p class="text-gray-600">Choose a date, time and preferred dentist.</p>
                    </div>
                    <div>
                        <a href="<?= base_url('patient/appointments') ?>" class="text-sm text-gray-600 hover:underline">Back to My Appointments</a>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <?php $errors = session()->getFlashdata('errors') ?? []; ?>
                    <?php if (!empty($errors)): ?>
                        <div class="mb-4 p-3 bg-red-50 border border-red-100 text-red-700 rounded">
                            <ul class="list-disc pl-5">
                                <?php foreach ($errors as $field => $msg): ?>
                                    <li><?= is_array($msg) ? esc(implode(' ', $msg)) : esc($msg) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form id="appointmentForm" method="POST" action="<?= base_url('patient/book-appointment') ?>" novalidate>
                        <input type="hidden" name="csrf_test_name" value="<?= csrf_hash() ?>">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Branch <span class="text-red-500">*</span></label>
                                <select id="branchSelect" name="branch_id" class="w-full px-3 py-2 border rounded" required>
                                    <option value="">Select branch</option>
                                    <?php if (!empty($branches)): foreach ($branches as $b): ?>
                                        <option value="<?= esc($b['id']) ?>" <?= (old('branch_id') == $b['id']) ? 'selected' : '' ?>><?= esc($b['name']) ?></option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>

                            <div>
                                <?php if (!empty($dentists) && count($dentists) > 1): ?>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Preferred Dentist (optional)</label>
                                    <select id="dentistSelect" name="dentist_id" class="w-full px-3 py-2 border rounded">
                                        <option value="">Any available</option>
                                        <?php foreach ($dentists as $d): ?>
                                            <option value="<?= esc($d['id']) ?>" <?= (old('dentist_id') == $d['id']) ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php elseif (!empty($dentists) && count($dentists) === 1):
                                    // single dentist: include hidden input so backend receives it
                                    $onlyDentist = reset($dentists);
                                ?>
                                    <input type="hidden" name="dentist_id" value="<?= esc($onlyDentist['id']) ?>">
                                <?php else: ?>
                                    <select id="dentistSelect" name="dentist_id" class="w-full px-3 py-2 border rounded hidden"></select>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date <span class="text-red-500">*</span></label>
                                <input id="dateInput" type="date" name="appointment_date" min="<?= date('Y-m-d') ?>" value="<?= old('appointment_date') ?>" class="w-full px-3 py-2 border rounded" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Time <span class="text-red-500">*</span></label>
                                <select id="timeSelect" name="appointment_time" class="w-full px-3 py-2 border rounded" required>
                                    <option value="">Select time</option>
                                    <!-- Options populated by patient-calendar.js via /appointments/available-slots -->
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Service / Procedure <span class="text-red-500">*</span></label>
                                <select id="serviceSelect" name="service_id" class="w-full px-3 py-2 border rounded" required>
                                    <option value="">Select service</option>
                                    <?php if (!empty($services)): foreach ($services as $s): 
                                        $dur = $s['duration'] ?? $s['procedure_duration'] ?? 30;
                                    ?>
                                        <option value="<?= esc($s['id']) ?>" data-duration="<?= esc($dur) ?>" <?= (old('service_id') == $s['id']) ? 'selected' : '' ?>><?= esc($s['name']) ?> (<?= esc($dur) ?>m)</option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Duration (minutes)</label>
                                <input id="durationDisplay" type="text" class="w-full px-3 py-2 border rounded bg-gray-100" value="<?= old('procedure_duration') ?: '30' ?>" readonly>
                                <input id="procedureDuration" type="hidden" name="procedure_duration" value="<?= old('procedure_duration') ?: '30' ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Special Notes (optional)</label>
                            <textarea name="remarks" rows="3" class="w-full px-3 py-2 border rounded"><?= old('remarks') ?></textarea>
                        </div>

                        <div class="flex items-center justify-between">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Request Booking</button>
                            <a href="<?= base_url('patient/appointments') ?>" class="text-sm text-gray-600 hover:underline">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?= view('templates/footer') ?>

<script>
// Auto-fill duration from selected service and fetch available slots
(function(){
    const serviceSelect = document.getElementById('serviceSelect');
    const durationDisplay = document.getElementById('durationDisplay');
    const procedureDuration = document.getElementById('procedureDuration');
    const branchSelect = document.getElementById('branchSelect');
    const dateInput = document.getElementById('dateInput');
    const dentistSelect = document.getElementById('dentistSelect');
    const timeSelect = document.getElementById('timeSelect');

    function postForm(url, data){
        const headers = {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-Requested-With': 'XMLHttpRequest'};
        const body = new URLSearchParams();
        Object.keys(data || {}).forEach(k => { if (data[k] !== undefined && data[k] !== null) body.append(k, data[k]); });
        return fetch(url, {method: 'POST', headers, body: body.toString(), credentials: 'same-origin'}).then(r => r.json());
    }

    function setDurationFromService(){
        if(!serviceSelect) return;
        const opt = serviceSelect.options[serviceSelect.selectedIndex];
        const dur = opt ? opt.dataset.duration || opt.getAttribute('data-duration') : null;
        const val = dur ? String(dur) : '30';
        if(durationDisplay) durationDisplay.value = val;
        if(procedureDuration) procedureDuration.value = val;
    }

    async function loadSlots(){
        if(!timeSelect) return;
        timeSelect.innerHTML = '<option>Loading...</option>';
        const branch = branchSelect ? branchSelect.value : '';
        const date = dateInput ? dateInput.value : '';
        const dentist = dentistSelect ? dentistSelect.value : '';
        const duration = procedureDuration ? Number(procedureDuration.value || 30) : 30;
        if(!date) { timeSelect.innerHTML = '<option value="">Select date first</option>'; return; }
        try{
            const res = await postForm('/appointments/available-slots', {branch_id: branch, date, duration, dentist_id: dentist});
            if(res && res.success){
                const slots = res.slots || [];
                timeSelect.innerHTML = '<option value="">Select time</option>';
                slots.forEach(s => {
                    const time = (typeof s === 'string') ? s : (s.time || '');
                    const opt = document.createElement('option');
                    opt.value = time;
                    opt.textContent = time + (s.dentist_id ? (' • Dr. ' + s.dentist_id) : '');
                    timeSelect.appendChild(opt);
                });
                if(slots.length === 0) timeSelect.innerHTML = '<option value="">No available slots</option>';
            } else {
                timeSelect.innerHTML = '<option value="">No available slots</option>';
            }
        }catch(e){ console.error('loadSlots error', e); timeSelect.innerHTML = '<option value="">Error loading slots</option>'; }
    }

    // Events
    if(serviceSelect) serviceSelect.addEventListener('change', function(){ setDurationFromService(); loadSlots(); });
    if(branchSelect) branchSelect.addEventListener('change', loadSlots);
    if(dateInput) dateInput.addEventListener('change', loadSlots);
    if(dentistSelect) dentistSelect.addEventListener('change', loadSlots);

    // Initialize
    setDurationFromService();
    // Preload slots if date present
    if(dateInput && dateInput.value) loadSlots();
})();
</script>

<script>
// AJAX submit for appointmentForm with in-page popup feedback (no redirect)
(function(){
    const form = document.getElementById('appointmentForm');
    if(!form) return;

    // Create or reuse an errors container above the form
    function ensureErrorContainer(){
        let el = document.getElementById('ajaxErrors');
        if(el) return el;
        el = document.createElement('div');
        el.id = 'ajaxErrors';
        el.className = 'mb-4 p-3 bg-red-50 border border-red-100 text-red-700 rounded hidden';
        form.parentNode.insertBefore(el, form);
        return el;
    }

    function showErrors(errors){
        const el = ensureErrorContainer();
        el.innerHTML = '';
        if(!errors) { el.classList.add('hidden'); return; }
        const ul = document.createElement('ul'); ul.className = 'list-disc pl-5';
        Object.keys(errors).forEach(k => {
            const msg = Array.isArray(errors[k]) ? errors[k].join(' ') : errors[k];
            const li = document.createElement('li'); li.textContent = msg; ul.appendChild(li);
        });
        el.appendChild(ul);
        el.classList.remove('hidden');
    }

    // Inline success panel (above form)
    function ensureSuccessContainer(){
        let el = document.getElementById('ajaxSuccess');
        if(el) return el;
        el = document.createElement('div');
        el.id = 'ajaxSuccess';
        el.className = 'mb-4 p-3 bg-green-50 border border-green-100 text-green-800 rounded hidden';
        form.parentNode.insertBefore(el, form);
        return el;
    }

    function showSuccess(message, autoClose = 6000){
        const el = ensureSuccessContainer();
        el.textContent = message || 'Appointment requested successfully';
        el.classList.remove('hidden');
        if(autoClose) setTimeout(() => { el.classList.add('hidden'); }, autoClose);
    }

    async function submitAjax(e){
        e.preventDefault();
        showErrors(null);
        const submitBtn = form.querySelector('button[type="submit"]');
        if(submitBtn) submitBtn.disabled = true;

        // Collect form data
        const fd = new FormData(form);
        // inform server this came from patient page (keeps legacy behavior) but we still expect JSON
        fd.set('origin', 'patient');

        // Convert to URLSearchParams for urlencoded body
        const params = new URLSearchParams();
        for(const pair of fd.entries()){ params.append(pair[0], pair[1]); }

        try{
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: params.toString(),
                credentials: 'same-origin'
            });

            const data = await res.json().catch(() => null);
            if(res.ok && data && data.success){
                // Redirect patient to their appointments list after successful request
                window.location.href = '<?= base_url('patient/appointments') ?>';
                return;
            } else if(res.status === 422 && data && data.errors){
                // Validation errors
                showErrors(data.errors);
                if(submitBtn) submitBtn.disabled = false;
            } else {
                // Generic failure: show message from server or generic
                const msg = data && data.message ? data.message : 'Failed to request booking. Please try again.';
                // show as inline error
                showErrors({general: msg});
                if(submitBtn) submitBtn.disabled = false;
            }
        } catch(err){
            console.error('Booking AJAX error', err);
            showErrors({general: 'Network error while requesting booking. Please try again.'});
            const submitBtn = form.querySelector('button[type="submit"]');
            if(submitBtn) submitBtn.disabled = false;
        }
    }

    form.addEventListener('submit', submitAjax);
})();
</script>
