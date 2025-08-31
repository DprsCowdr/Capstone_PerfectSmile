(function(){
  // patient-calendar.js
  // Fetch day appointments, populate "Time Taken" dropdown, fetch available slots,
  // and check conflicts when selecting a time.

  const baseUrl = window.baseUrl || '';
  const qs = (s) => document.querySelector(s);
  const qsa = (s) => Array.from(document.querySelectorAll(s));

  function postJson(url, data){
    const headers = {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'};
    const csrf = document.querySelector('meta[name="csrf-token"]');
    if(csrf) headers['X-CSRF-TOKEN'] = csrf.getAttribute('content');

    return fetch(baseUrl + url, {
      method: 'POST',
      headers,
      body: JSON.stringify(data),
      credentials: 'same-origin'
    }).then(r => r.text().then(t => {
      let parsed;
      try { parsed = JSON.parse(t); } catch(e) { parsed = t; }
      if (!r.ok) return Promise.reject({ status: r.status, body: parsed });
      // If parsed is an object, merge status for backward compatibility
      if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
        parsed.status = r.status;
        return parsed;
      }
      return { status: r.status, body: parsed };
    }));
  }

  function postForm(url, data){
    const headers = {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-Requested-With': 'XMLHttpRequest'};
    const csrf = document.querySelector('meta[name="csrf-token"]');
    if(csrf) headers['X-CSRF-TOKEN'] = csrf.getAttribute('content');
  // no debug header in production
    const body = new URLSearchParams();
    Object.keys(data || {}).forEach(k => { if (data[k] !== undefined && data[k] !== null) body.append(k, data[k]); });
    return fetch(baseUrl + url, {method: 'POST', headers, body: body.toString(), credentials: 'same-origin'})
      .then(r => r.text().then(t => {
        let parsed;
        try { parsed = JSON.parse(t); } catch(e) { parsed = t; }
        if (!r.ok) return Promise.reject({ status: r.status, body: parsed });
        if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
          parsed.status = r.status;
          return parsed;
        }
        return { status: r.status, body: parsed };
      }));
  }

  function formatTimeHM(t){
    // t = "HH:MM:SS" or "HH:MM"
    return t.slice(0,5);
  }

  function populateTimeTaken(selectEl, appointments){
    selectEl.innerHTML = '';
    const empty = document.createElement('option');
    empty.value = '';
    empty.textContent = '-- select existing appointment (time taken) --';
    selectEl.appendChild(empty);

    appointments.forEach(a => {
      const opt = document.createElement('option');
      opt.value = a.start; // e.g. "08:00"
  // If appointment belongs to current patient, show full details; otherwise show generic 'Booked'
  const patientLabel = (a.is_owner || a.patient_name) ? (a.patient_name || 'You') : 'Booked';
  const procLabel = a.procedure || a.service_name || a.procedure_name || a.procedure_label || '';
  const procText = procLabel ? ` | ${procLabel}` : '';
  opt.textContent = `${patientLabel} — ${formatTimeHM(a.start)} to ${formatTimeHM(a.end)} (${a.procedure_duration || '30'}m)${procText}`;
      selectEl.appendChild(opt);
    });
  }

  function showSlots(containerEl, slots){
    // containerEl may be null if aside was removed; try menu content fallbacks
    if(!containerEl){
      containerEl = qs('#availableSlots') || qs('#availableSlotsMenuContent') || document.getElementById('availableSlots');
    }
    if(!containerEl){ console.warn('[patient-calendar] showSlots: no container found'); return; }
    containerEl.innerHTML = '';
    if(!slots || slots.length === 0){
      containerEl.textContent = 'No available slots for the selected duration.';
      return;
    }
    const ul = document.createElement('ul');
    slots.forEach(s => {
      const li = document.createElement('li');
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'slot-btn';
      // support string slots or object {time, dentist_id}
      const timeStr = (typeof s === 'string') ? s : (s.time || s.time || '');
      btn.textContent = timeStr;
      if (s && typeof s === 'object' && s.dentist_id) btn.dataset.dentist = s.dentist_id;
      btn.addEventListener('click', () => {
        const timeInput = qs('input[name="appointment_time"]') || qs('select[name="appointment_time"]');
        if(timeInput) timeInput.value = timeStr;
        // if slot includes dentist, preselect dentist
        const dt = btn.dataset && btn.dataset.dentist ? btn.dataset.dentist : null;
        const dentistSelect = qs('select[name="dentist_id"]') || qs('#dentistSelect');
        if(dt && dentistSelect) dentistSelect.value = dt;
      });
      li.appendChild(btn);
      ul.appendChild(li);
    });
    containerEl.appendChild(ul);
  }

  // Remove a taken time from available lists and selects
  function removeTakenSlot(timeStr){
    try{
      // remove from select(s)
      const selects = Array.from(document.querySelectorAll('select[name="appointment_time"], #timeSelect'));
      selects.forEach(sel => {
        const opt = sel.querySelector('option[value="'+timeStr+'"]');
        if(opt) opt.disabled = true; // mark unavailable
      });
      // remove from header menus
      const menuBtns = document.querySelectorAll('#availableSlotsMenuContent button, #availableSlotsMenuContent li');
      // simply rebuild menu on next open; also try to remove exact text buttons
      const menuContent = qs('#availableSlotsMenuContent');
      if(menuContent){
        const buttons = Array.from(menuContent.querySelectorAll('button'));
        buttons.forEach(b => { if(b.textContent && b.textContent.trim().startsWith(timeStr)) b.remove(); });
      }
    }catch(e){ console.error('[patient-calendar] removeTakenSlot error', e); }
  }

  function showConflicts(containerEl, conflicts){
    containerEl.innerHTML = '';
    if(!conflicts || conflicts.length === 0){
      containerEl.textContent = 'No conflicts detected.';
      containerEl.classList.remove('conflict');
      return;
    }
    containerEl.classList.add('conflict');
    const ul = document.createElement('ul');
    conflicts.forEach(c => {
      const li = document.createElement('li');
      li.textContent = `${c.patient_name || '—'}: ${formatTimeHM(c.start)} - ${formatTimeHM(c.end)} (${c.procedure_duration || '30'}m)`;
      ul.appendChild(li);
    });
    containerEl.appendChild(ul);
  }

  // Slot granularity in minutes (configurable). Use global override if provided.
  const SLOT_GRANULARITY = (window.SLOT_GRANULARITY && Number(window.SLOT_GRANULARITY)) ? Number(window.SLOT_GRANULARITY) : 30;

  // Simple cache for available-slots responses by date+duration to avoid repeated calls while rendering month
  const slotsCache = {};
  function cacheKey(date, duration, dentist, branch){
    return `${date}::${duration || ''}::${dentist || ''}::${branch || ''}`;
  }
  async function getAvailableSlotsForDate(date, duration, dentist){
    const branch = (document.querySelector('select[name="branch_id"]') || {}).value || '';
    const key = cacheKey(date, duration || SLOT_GRANULARITY, dentist || '', branch || '');
    if(slotsCache[key]) return slotsCache[key];
    try{
  const res = await postForm('/appointments/available-slots', { branch_id: branch, date, duration: Number(duration || SLOT_GRANULARITY), dentist_id: dentist || '', granularity: Number(SLOT_GRANULARITY) });
  const slots = (res && res.success) ? (res.slots || []) : [];
  // Cache entire response object so callers can use totals if present
  slotsCache[key] = res || { slots };
  return slotsCache[key];
    }catch(e){ console.error('[patient-calendar] getAvailableSlotsForDate error', e); slotsCache[key] = []; return []; }
  }

  function computeOccupiedSlotsForDate(date, granularity){
    const appointments = (window.appointments || []).filter(a => {
      const aptDate = a.appointment_date || (a.appointment_datetime ? a.appointment_datetime.substring(0,10) : null);
      return aptDate === date;
    });
    const g = Number(granularity) || SLOT_GRANULARITY;
    let occupied = 0;
    appointments.forEach(a => {
      const dur = Number(a.procedure_duration || a.duration_minutes || a.duration || 30) || 30;
      occupied += Math.ceil(dur / g);
    });
    return { occupied, appointmentsCount: appointments.length };
  }

  // Update month view badges to show occupied/total slots dynamically
  async function updateMonthSlotCounts(){
    try{
      const monthView = document.getElementById('monthView');
      if(!monthView) return;
      const tds = monthView.querySelectorAll('td[data-date]');
      if(!tds || tds.length === 0) return;
      // For each date cell that contains an appointment badge, compute totals and update
      for(const td of Array.from(tds)){
        const date = td.getAttribute('data-date');
        if(!date) continue;
        // Find the appointment badge span created by calendar scripts
        const badge = td.querySelector('.relative.z-10 .bg-blue-100') || td.querySelector('.relative .bg-blue-100');
        // Only update cells that previously had an appointment indicator
        if(!badge) continue;
        // Compute occupied units (sum of ceil(duration/granularity))
        const { occupied, appointmentsCount } = computeOccupiedSlotsForDate(date, SLOT_GRANULARITY);
        // Fetch available starting slots and totals (server returns total_possible_starting_slots and remaining_starting_slots)
        const availResp = await getAvailableSlotsForDate(date, SLOT_GRANULARITY, '');
        const serverTotal = (availResp && availResp.total_possible_starting_slots) ? Number(availResp.total_possible_starting_slots) : null;
        const serverRemaining = (availResp && (typeof availResp.remaining_starting_slots !== 'undefined')) ? Number(availResp.remaining_starting_slots) : null;
        if (serverTotal !== null && serverRemaining !== null) {
          // serverRemaining is how many starting slots fit the requested duration; we display occupied units vs possible starting slots
          badge.innerHTML = `<i class=\"fas fa-calendar-check mr-1 text-blue-600\"></i>${occupied}/${serverTotal} slots`;
        } else {
          // fallback to previous computation: available.length + occupied
          const available = (availResp && availResp.slots) ? availResp.slots : (availResp || []);
          const total = (available ? available.length : 0) + occupied;
          if(total > 0){
            badge.innerHTML = `<i class=\"fas fa-calendar-check mr-1 text-blue-600\"></i>${occupied}/${total} slots`;
          } else {
            badge.innerHTML = `<i class=\"fas fa-calendar-check mr-1 text-blue-600\"></i>${appointmentsCount} apt${appointmentsCount>1?'s':''}`;
          }
        }
      }
    }catch(e){ console.error('[patient-calendar] updateMonthSlotCounts error', e); }
  }

  function init(){
    const branchEl = qs('select[name="branch_id"]');
    const dateEl = qs('input[name="appointment_date"]');
    const durationEl = qs('select[name="procedure_duration"]') || qs('input[name="procedure_duration"]');
    const dentistEl = qs('select[name="dentist_id"]');
    const timeTakenSelect = qs('#timeTakenSelect');
    const slotsContainer = qs('#availableSlots');
    const conflictsContainer = qs('#timeConflicts');

  // If there's no appointment form date input, don't bail out completely because
  // we still want header-only features (Available slots / Time Taken) to work
  // on staff calendar pages. Only return early if nothing relevant exists.
  const headerAvailableBtn = qs('#availableSlotsBtn');
  const headerTimeTakenBtn = qs('#timeTakenBtn');
  if(!dateEl && !headerAvailableBtn && !headerTimeTakenBtn) return;

    // Show server-side validation errors on the form
    function showFormValidationErrors(form, errors){
      try{
        let container = form.querySelector('.form-errors');
        if(!container){
          container = document.createElement('div');
          container.className = 'form-errors text-sm text-red-700 mb-2';
          form.insertBefore(container, form.firstChild);
        }
        container.innerHTML = '';
        if(!errors) return;
        // errors may be an object mapping field->message or array
        if(Array.isArray(errors)){
          errors.forEach(m => { const p = document.createElement('div'); p.textContent = m; container.appendChild(p); });
        } else if (typeof errors === 'object'){
          Object.keys(errors).forEach(k => { const p = document.createElement('div'); p.textContent = errors[k]; container.appendChild(p); });
        } else {
          const p = document.createElement('div'); p.textContent = String(errors); container.appendChild(p);
        }
        container.scrollIntoView({behavior:'smooth', block:'center'});
      }catch(e){ console.error('showFormValidationErrors error', e); }
    }

    function loadDayAppointments(){
      const branch = branchEl ? branchEl.value : '';
      let date = dateEl.value;
      if (!date) {
        const sel = document.getElementById('selectedDateDisplay');
        if (sel && sel.value) date = sel.value;
        else date = new Date().toISOString().slice(0,10);
      }
      if(!date) return;
  postForm('/appointments/day-appointments', {branch_id: branch, date})
        .then(res => {
          if(res && res.success){
            const appts = res.appointments || [];
            if(timeTakenSelect) populateTimeTaken(timeTakenSelect, appts);
          }
        }).catch(console.error);
    }

    function showPrefillHint(timeInput){
      try{
        if(!timeInput) return;
        // avoid duplicate hints
        let hint = timeInput.parentNode && timeInput.parentNode.querySelector('.prefill-hint');
        if(!hint){
          hint = document.createElement('div');
          hint.className = 'prefill-hint text-sm text-gray-600 mt-1';
          hint.textContent = 'Prefilled with earliest available slot — click to change';
          if(timeInput.parentNode) timeInput.parentNode.appendChild(hint);
            // dismiss immediately on click
            hint.style.cursor = 'pointer';
            hint.addEventListener('click', function(){ try{ hint.remove(); }catch(e){} });
        }
        // fade out after 6s
        setTimeout(()=>{ try{ hint.remove(); }catch(e){} }, 6000);
      }catch(e){ console.error('showPrefillHint error', e); }
    }

    function loadSlots(){
      const branch = branchEl ? branchEl.value : '';
      let date = dateEl.value;
      if (!date) {
        const sel = document.getElementById('selectedDateDisplay');
        if (sel && sel.value) date = sel.value;
        else date = new Date().toISOString().slice(0,10);
      }
      let duration = 30;
      if (durationEl) {
        const v = (durationEl.value !== undefined) ? durationEl.value : (durationEl.selectedIndex ? durationEl.options[durationEl.selectedIndex].value : null);
        const n = Number(v);
        duration = Number.isFinite(n) && n > 0 ? n : 30;
      }
      const dentist = dentistEl ? dentistEl.value : '';
      if(!date) return;
      postForm('/appointments/available-slots', {branch_id: branch, date, duration: Number(duration), dentist_id: dentist})
        .then(res => {
          if(res && res.success){
            const slots = res.slots || [];
            showSlots(slotsContainer, slots);
            // prefill earliest slot if time field is empty
            try{
              const timeInput = qs('input[name="appointment_time"]') || qs('select[name="appointment_time"]') || qs('#timeSelect');
              if(slots.length && timeInput && !timeInput.value){
                const first = slots[0];
                const firstTime = (typeof first === 'string') ? first : (first.time || first);
                timeInput.value = firstTime;
                // if slot includes dentist, preselect dentist
                if(first && typeof first === 'object' && first.dentist_id){
                  const dentistSelect = qs('select[name="dentist_id"]') || qs('#dentistSelect');
                  if(dentistSelect) dentistSelect.value = first.dentist_id;
                }
                showPrefillHint(timeInput);
                // also perform a conflict check for the newly selected time
                checkConflictFor(firstTime);
              }
            }catch(e){ console.error('prefill earliest slot error', e); }
          }
        }).catch(console.error);
    }

    function checkConflictFor(timeValue){
      const branch = branchEl ? branchEl.value : '';
      const date = dateEl.value;
      const duration = durationEl ? (Number(durationEl.value) || 30) : 30;
      const dentist = dentistEl ? dentistEl.value : '';
      if(!date || !timeValue) return;
  postForm('/appointments/check-conflicts', {branch_id: branch, date, time: timeValue, duration, dentist_id: dentist})
        .then(res => {
          if(res && res.success){
            showConflicts(conflictsContainer, res.conflicts || []);
          }
        }).catch(console.error);
    }

  // initial load
  loadDayAppointments();
  loadSlots();
  // Update month badges to show dynamic occupied/total slots
  setTimeout(() => { try{ if(typeof updateMonthSlotCounts === 'function') updateMonthSlotCounts(); }catch(e){console.error(e);} }, 600);

    // listeners
    if(dateEl) dateEl.addEventListener('change', () => { loadDayAppointments(); loadSlots(); });
    if(branchEl) branchEl.addEventListener('change', () => { loadDayAppointments(); loadSlots(); });
    if(durationEl) durationEl.addEventListener('change', () => { loadSlots(); });
    if(dentistEl) dentistEl.addEventListener('change', () => { loadSlots(); });

    // when the user types/selects a time, check conflicts
    const timeInput = qs('input[name="appointment_time"]');
    if(timeInput){
      timeInput.addEventListener('change', (e) => { checkConflictFor(e.target.value); });
      timeInput.addEventListener('blur', (e) => { checkConflictFor(e.target.value); });
    }

    if(timeTakenSelect){
      timeTakenSelect.addEventListener('change', (e) => {
        const val = e.target.value;
        if(val){
          const timeInputLocal = qs('input[name="appointment_time"]');
          if(timeInputLocal) timeInputLocal.value = val;
          checkConflictFor(val);
        }
      });
    }

    // Hook appointment form submit to send via AJAX and avoid full redirect
    const appointmentForm = document.getElementById('appointmentForm');
    if(appointmentForm){
      appointmentForm.addEventListener('submit', async function(e){
        e.preventDefault();
        const form = e.target;
        const fd = new FormData(form);
        if(!fd.has('origin')) fd.append('origin','patient');
        const payload = {};
        fd.forEach((v,k)=>{ payload[k]=v; });
        // Ensure procedure_duration present and numeric
        if(!payload.procedure_duration){
          const pdEl = document.querySelector('select[name="procedure_duration"]') || document.querySelector('input[name="procedure_duration"]');
          if(pdEl){ const pdv = pdEl.value || pdEl.getAttribute('value') || ''; const pn = Number(pdv); payload.procedure_duration = (Number.isFinite(pn) && pn>0)? pn:30; }
          else payload.procedure_duration = 30;
        } else { const pn = Number(payload.procedure_duration); payload.procedure_duration = (Number.isFinite(pn) && pn>0)? pn:30; }

        // Ensure service/service_id present: prefer select[name="service"], fallback to service_id
        if(!payload.service && !payload.service_id){
          const svcSel = document.querySelector('select[name="service"]') || document.querySelector('select[name="service_id"]') || document.querySelector('input[name="service_id"]') || document.querySelector('input[name="service"]');
          const svcText = document.getElementById('service_text');
          let sv = '';
          if(svcSel) sv = svcSel.value || svcSel.getAttribute('value') || '';
          if(!sv && svcText) sv = svcText.value || '';
          // final fallback to any global default
          if(!sv && window.defaultService) sv = window.defaultService;
          if(sv){ payload.service = sv; payload.service_id = sv; }
        }

        // Debug: log service being sent so missing-service errors are diagnosable
        try{ console.debug('[patient-calendar] booking payload service:', payload.service, 'service_id:', payload.service_id); }catch(e){}

        // Ensure dentist_id is only sent when it has a non-empty value.
        try{
          const dentistEl = document.querySelector('select[name="dentist_id"]') || document.querySelector('#dentistSelect');
          if(dentistEl){
            const dv = dentistEl.value;
            if(dv !== undefined && dv !== null && String(dv).trim() !== ''){
              payload.dentist_id = dv;
            } else {
              // remove empty dentist_id from payload to avoid server converting '' -> null
              if(Object.prototype.hasOwnProperty.call(payload, 'dentist_id')) delete payload.dentist_id;
            }
          }
        }catch(e){ console.warn('[patient-calendar] dentist payload normalization failed', e); }

        try{
          // Always target patient booking endpoint when on a patient page (robust detection)
          const isPatientPage = (window.userType === 'patient') || (window.CURRENT_USER_TYPE === 'patient') || window.location.pathname.startsWith('/patient');
          const bookingEndpoint = isPatientPage ? '/patient/book-appointment' : '/guest/book-appointment';
          // Ensure branch_id present
          if(!payload.branch_id && branchEl) payload.branch_id = branchEl.value || '';
          // Make the request
          const res = await postForm(bookingEndpoint, payload);
            if(res && res.success){
            if(res.appointment){ window.appointments = window.appointments || []; window.appointments.push(res.appointment); }
            const msgEl = document.getElementById('appointmentSuccessMessage');
            const main = document.getElementById('appointmentSuccessMain');
            if(main) main.textContent = res.message || 'Appointment booked successfully!';
            if(msgEl) msgEl.style.display = 'block';
            window.dispatchEvent(new CustomEvent('appointmentCreated', { detail: res.appointment || null }));
            form.reset();
          } else {
            alert((res && res.message) ? res.message : 'Failed to create appointment');
          }
        }catch(err){
          console.error('[patient-calendar] appointment submit error', err);
          // Surface validation errors returned as HTTP 422
          if(err && err.status === 422 && err.body){
            // err.body may be parsed JSON or a plain object
            const b = err.body;
            const errors = b && b.errors ? b.errors : (b && b.message ? b.message : b);
            showFormValidationErrors(appointmentForm, errors);
            return;
          }
          if(err && err.status === 401){
            alert('Session expired or unauthorized. Please log in again.');
            return;
          }
          alert('Error submitting appointment');
        }
      });
    }

    // When appointments are created elsewhere in the app, refresh month slot counts
    window.addEventListener('appointmentCreated', function(){ if(typeof updateMonthSlotCounts === 'function') updateMonthSlotCounts(); });

  // Header buttons removed: Available Slots & Time Taken UI are deprecated
  const sidePanel = qs('aside');
  const availableMenuContent = qs('#availableSlotsMenuContent');
    function showSidePanel(){
      if(!sidePanel) return;
      sidePanel.style.display = 'block';
      sidePanel.scrollIntoView({behavior: 'smooth', block: 'center'});
    }
    // Toggle menus and preload content
    function hideAllMenus(){
      if(availableMenu) availableMenu.classList.add('hidden');
      if(timeTakenMenu) timeTakenMenu.classList.add('hidden');
    }

  // Previously there were header buttons for Available Slots / Time Taken. Those UI elements
  // were removed. We now show dynamic slot counts inside the Month view and inline hints.

    // close menus when clicking outside
    document.addEventListener('click', (e) => {
      if(availableMenu && !availableMenu.classList.contains('hidden')) availableMenu.classList.add('hidden');
      if(timeTakenMenu && !timeTakenMenu.classList.contains('hidden')) timeTakenMenu.classList.add('hidden');
    });
  }

  // init on DOMReady
  if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
