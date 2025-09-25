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
  // Safe toString: avoid calling toString on undefined/null
  const safeToString = (v) => { try { if (v === null || v === undefined) return ''; if (typeof v.toString === 'function') return v.toString(); return String(v); } catch(e) { return ''; } };
  return fetch(baseUrl + url, {method: 'POST', headers, body: safeToString(body), credentials: 'same-origin'})
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

  // Normalize various time string formats to 24-hour 'HH:MM'
  function to24Hour(timeStr){
    if(!timeStr || typeof timeStr !== 'string') return '';
    const s = timeStr.trim();
    // Already in HH:MM or HH:MM:SS (24-hour) -> take first 5 chars
    if(/^\d{1,2}:\d{2}(:\d{2})?$/.test(s) && !/[aApP][mM]/.test(s)){
      const parts = s.split(':');
      const hh = parts[0].padStart(2,'0');
      const mm = parts[1];
      return hh + ':' + mm;
    }
    // HHMM e.g. 0830
    if(/^\d{3,4}$/.test(s)){
      const hh = s.length === 3 ? '0' + s.slice(0,1) : s.slice(0,2);
      const mm = s.slice(-2);
      return hh + ':' + mm;
    }
    // 12-hour with AM/PM
    const m = s.match(/^(\d{1,2}):(\d{2})(?::\d{2})?\s*([AaPp][Mm])$/);
    if(m){
      let h = parseInt(m[1],10);
      const min = m[2];
      const ampm = m[3].toUpperCase();
      if(ampm === 'PM' && h < 12) h += 12;
      if(ampm === 'AM' && h === 12) h = 0;
      return String(h).padStart(2,'0') + ':' + min;
    }
    const m2 = s.match(/^(\d{1,2}):(\d{2})([AaPp][Mm])$/);
    if(m2){
      let h = parseInt(m2[1],10);
      const min = m2[2];
      const ampm = m2[3].toUpperCase();
      if(ampm === 'PM' && h < 12) h += 12;
      if(ampm === 'AM' && h === 12) h = 0;
      return String(h).padStart(2,'0') + ':' + min;
    }
    // As a last resort, attempt Date parsing for today and extract time
    try{
      const d = new Date('1970-01-01 ' + s);
      if(!isNaN(d.getTime())){
        const hh = String(d.getHours()).padStart(2,'0');
        const mm = String(d.getMinutes()).padStart(2,'0');
        return hh + ':' + mm;
      }
    }catch(e){}
    return '';
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
      opt.textContent = `${a.patient_name || '—'} — ${formatTimeHM(a.start)} to ${formatTimeHM(a.end)} (${a.procedure_duration || '30'}m)`;
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
      const rawTime = (typeof s === 'string') ? s : (s.time || s.time || '');
      const timeStr = to24Hour(rawTime) || rawTime;
      btn.textContent = timeStr;
      if (s && typeof s === 'object' && s.dentist_id) btn.dataset.dentist = s.dentist_id;
      btn.addEventListener('click', () => {
        const timeInput = qs('input[name="appointment_time"]') || qs('select[name="appointment_time"]');
        if(timeInput) timeInput.value = to24Hour(timeStr) || timeStr;
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
    if(!containerEl){
      containerEl = qs('#timeConflicts') || qs('.time-conflicts') || null;
    }
    if(!containerEl){
      // no conflicts container present in this layout; silently skip rendering
      return;
    }
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

  function init(){
    const branchEl = qs('select[name="branch_id"]');
    const dateEl = qs('input[name="appointment_date"]');
    const durationEl = qs('select[name="procedure_duration"]') || qs('input[name="procedure_duration"]');
    const dentistEl = qs('select[name="dentist_id"]');
    const timeTakenSelect = qs('#timeTakenSelect');
    const slotsContainer = qs('#availableSlots');
    const conflictsContainer = qs('#timeConflicts');

    if(!dateEl) return; // not on calendar/create page

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
      // Prefer explicit procedure_duration field when present
      if (durationEl) {
        const v = (durationEl.value !== undefined) ? durationEl.value : (durationEl.selectedIndex ? durationEl.options[durationEl.selectedIndex].value : null);
        const n = Number(v);
        duration = Number.isFinite(n) && n > 0 ? n : 30;
      }

      // If no explicit duration provided, try to derive from selected service option data attributes
      const svcSel = document.querySelector('select[name="service"]') || document.querySelector('select[name="service_id"]') || document.querySelector('input[name="service_id"]') || document.querySelector('input[name="service"]');
      let svcIdForRequest = '';
      try{
        if(svcSel){
          svcIdForRequest = svcSel.value || svcSel.getAttribute('value') || '';
          // If duration wasn't explicitly set via a duration element, read data-duration/data-duration-max on the selected option
          if((!durationEl || !durationEl.value) && svcSel.options && svcSel.selectedIndex >= 0){
            const opt = svcSel.options[svcSel.selectedIndex];
            const ddMax = opt ? opt.getAttribute('data-duration-max') : null;
            const dd = opt ? opt.getAttribute('data-duration') : null;
            const candidate = ddMax ? Number(ddMax) : (dd ? Number(dd) : null);
            if (Number.isFinite(candidate) && candidate > 0) duration = candidate;
          }
        }
      }catch(e){ console.warn('[patient-calendar] service duration read failed', e); }

      const dentist = dentistEl ? dentistEl.value : '';
      if(!date) return;
        // Always include branch_id so server can apply branch operating hours
        // Include service_id when available so server can authoritative determine duration if needed
  const payload = { branch_id: branch || (branchEl ? branchEl.value : ''), date, duration: Number(duration), dentist_id: dentist, granularity: 5 };
        if (svcIdForRequest) payload.service_id = svcIdForRequest;
        postForm('/appointments/available-slots', payload)
        .then(res => {
          if(res && res.success){
            const slots = res.slots || [];
            showSlots(slotsContainer, slots);
            // prefill earliest slot if time field is empty
            try{
              const timeInput = qs('input[name="appointment_time"]') || qs('select[name="appointment_time"]') || qs('#timeSelect');
              if(slots.length && timeInput && !timeInput.value){
                const first = slots[0];
                const firstTimeRaw = (typeof first === 'string') ? first : (first.time || first);
                const firstTime = to24Hour(firstTimeRaw) || firstTimeRaw;
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
        
          // Show enhanced availability message using returned metadata
          const metadata = (res && res.metadata) ? res.metadata : {};
          const serviceName = metadata.service ? metadata.service.name : 'selected service';
          let message = `${(res && res.slots ? res.slots.length : 0)} suggestions for ${serviceName}`;
          if (metadata.day_start && metadata.day_end) {
            message += ` (Branch hours: ${metadata.day_start} - ${metadata.day_end})`;
          }
          showAvailabilityMessage(true, message);

          // Update a dedicated branch hours display element if present
          try{
            const bh = document.getElementById('branchHoursDisplay');
            if(bh){
              if(metadata.day_start && metadata.day_end) bh.textContent = `Branch hours: ${metadata.day_start} - ${metadata.day_end}`;
              else bh.textContent = '';
            }
          }catch(e){/* ignore */}
        }).catch(console.error);
    }

    function checkConflictFor(timeValue){
      const branch = branchEl ? branchEl.value : '';
      const date = dateEl.value;
      const duration = durationEl ? (Number(durationEl.value) || 30) : 30;
      const dentist = dentistEl ? dentistEl.value : '';
      if(!date || !timeValue) return;
      const nv = to24Hour(timeValue) || timeValue;
  postForm('/appointments/check-conflicts', {branch_id: branch, date, time: nv, duration, dentist_id: dentist})
        .then(res => {
          if(res && res.success){
            showConflicts(conflictsContainer, res.conflicts || []);
          }
        }).catch(console.error);
    }

    // initial load
    loadDayAppointments();
    loadSlots();

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
          const nv = to24Hour(val) || val;
          if(timeInputLocal) timeInputLocal.value = nv;
          checkConflictFor(nv);
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
          // Determine booking endpoint strictly: patient pages must use patient endpoint; other pages use guest endpoint
          const isPatientPage = (window.userType === 'patient') || (window.CURRENT_USER_TYPE === 'patient') || window.location.pathname.startsWith('/patient');
          const bookingEndpoint = isPatientPage ? '/patient/book-appointment' : '/guest/book-appointment';

          // Ensure branch_id present
          if(!payload.branch_id && branchEl) payload.branch_id = branchEl.value || '';

          // normalize appointment_time to HH:MM before posting
          if(payload.appointment_time){
            payload.appointment_time = to24Hour(payload.appointment_time) || payload.appointment_time;
          }

          // Debug: log the exact payload being sent
          console.log('[patient-calendar] Sending booking payload:', JSON.stringify(payload, null, 2));

          // Make the request to the appropriate endpoint
          const res = await postForm(bookingEndpoint, payload);
          if(res && res.success){
            if(res.appointment){
              try {
                // Only append to the global appointments array if the appointment belongs to the current user (defensive)
                const owner = res.appointment.user_id || res.appointment.patient_id || res.appointment.patient || null;
                if (window.userType === 'patient' && window.currentUserId) {
                  if (owner && Number(owner) === Number(window.currentUserId)) {
                    window.appointments = window.appointments || []; window.appointments.push(res.appointment);
                  } else {
                    // Don't pollute the patient's calendar with other users' appointments
                    console.debug('[patient-calendar] Skipped pushing appointment not owned by current patient', res.appointment);
                  }
                } else {
                  // For non-patient contexts, preserve existing behavior
                  window.appointments = window.appointments || []; window.appointments.push(res.appointment);
                }
              } catch (e) {
                console.error('[patient-calendar] Error while handling appointment push', e);
                window.appointments = window.appointments || []; window.appointments.push(res.appointment);
              }
            }
            const msgEl = document.getElementById('appointmentSuccessMessage');
            const main = document.getElementById('appointmentSuccessMain');
            if(main) main.textContent = res.message || 'Appointment booked successfully!';
            if(msgEl) msgEl.style.display = 'block';
            window.dispatchEvent(new CustomEvent('appointmentCreated', { detail: res.appointment || null }));
            form.reset();
          } else {
            if (typeof showInvoiceAlert === 'function') showInvoiceAlert((res && res.message) ? res.message : 'Failed to create appointment', 'error', 5000); else alert((res && res.message) ? res.message : 'Failed to create appointment');
          }
        }catch(err){
          console.error('[patient-calendar] appointment submit error', err);
          // Surface validation errors returned as HTTP 422
          if(err && err.status === 422 && err.body){
            // err.body may be parsed JSON or a plain object
            const b = err.body;
            const errors = b && b.errors ? b.errors : (b && b.message ? b.message : b);
            
            // If this is a date/time format error, show it to the user and instruct how to collect debug info.
            if(typeof errors === 'string' && (errors.includes('Invalid appointment date/time format') || errors.includes('date/time format'))) {
              console.warn('[patient-calendar] Date/time format error returned by server:', errors);
              console.info('To collect server parsing details, re-submit the form using the X-Debug-Booking header or use the guest debug endpoint.');
            }
            
            showFormValidationErrors(appointmentForm, errors);
            return;
          }
          if(err && err.status === 401){
            if (typeof showInvoiceAlert === 'function') showInvoiceAlert('Session expired or unauthorized. Please log in again.', 'warning', 6000); else alert('Session expired or unauthorized. Please log in again.');
            return;
          }
          
          // No automatic guest fallback. If patient endpoint fails due to auth, surface a clear message so user can re-authenticate.
          if(err && err.status === 422 && typeof errors === 'string' && errors.toLowerCase().includes('unauthorized')){
            if (typeof showInvoiceAlert === 'function') showInvoiceAlert('Booking failed due to authentication. Please log in and try again.', 'warning', 7000);
            else alert('Booking failed due to authentication. Please log in and try again.');
            return;
          }
          
          if (typeof showInvoiceAlert === 'function') showInvoiceAlert('Error submitting appointment', 'error', 5000); else alert('Error submitting appointment');
        }
      });
    }

    // Buttons in the header to jump to side-panel
    const availableBtn = qs('#availableSlotsBtn');
    const timeTakenBtn = qs('#timeTakenBtn');
    const sidePanel = qs('aside');
  const availableMenu = qs('#availableSlotsMenu');
  const availableMenuContent = qs('#availableSlotsMenuContent');
  const timeTakenMenu = qs('#timeTakenMenu');
  const timeTakenMenuContent = qs('#timeTakenMenuContent');
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

    // Simplified preload for patient available slots: delegate to TimeTable modal when possible
    async function preloadAvailableSlots(){
      if (!availableMenuContent) return;
      availableMenuContent.textContent = 'Opening Time Table...';
      try {
        if (typeof initTimeTableModal === 'function') {
          const modalInstance = initTimeTableModal();
          if (modalInstance) {
            await modalInstance.openModal();
            // hide small menu if modal opened
            if (availableMenu) availableMenu.classList.add('hidden');
            return;
          }
        }
      } catch (err) {
        console.warn('[patient-calendar] preloadAvailableSlots: TimeTable modal open failed, falling back', err);
      }

      // Fallback: show a brief list by calling the existing API but keep it minimal
      try {
        availableMenuContent.textContent = 'Loading...';
        const branch = branchEl ? branchEl.value : '';
        const date = dateEl ? dateEl.value : '';
        let duration = durationEl ? (Number(durationEl.value) || 30) : 30;
        const payload = { branch_id: branch, date, duration };
        const res = await postForm('/appointments/available-slots', payload);
        if (res && res.success) {
          const slots = res.slots || [];
          showSlots(availableMenuContent, slots);
        } else {
          availableMenuContent.textContent = 'No available slots';
        }
      } catch (err) {
        availableMenuContent.textContent = 'Error loading slots';
        console.error('[patient-calendar] preloadAvailableSlots fallback error', err);
      }
    }

    async function preloadTimeTaken(){
      try {
        if(!timeTakenMenuContent) return;
        console.log('[patient-calendar] preloadTimeTaken start');
        timeTakenMenuContent.textContent = 'Loading...';
        const branch = branchEl ? branchEl.value : '';
        const date = dateEl ? dateEl.value : '';
        const res = await postForm('/appointments/day-appointments', {branch_id: branch, date});
        if(res && res.success){
          const appts = res.appointments || [];
          if(appts.length === 0) timeTakenMenuContent.textContent = 'No appointments for selected day';
          else{
            timeTakenMenuContent.innerHTML = '';
            const ul = document.createElement('ul');
            ul.style.listStyle = 'none';
            ul.style.margin = '0';
            ul.style.padding = '0';
            ul.style.maxHeight = '220px';
            ul.style.overflowY = 'auto';
            appts.forEach(a => {
              const li = document.createElement('li');
              const btn = document.createElement('button');
              btn.type = 'button';
              btn.className = 'w-full text-left px-2 py-1 hover:bg-gray-100';
              // Hide patient name: only show time, date, duration, branch
              const datePart = a.date || '';
              const branch = a.branch_name || '';
              const duration = a.procedure_duration || '30';
              const start24 = to24Hour(a.start) || a.start;
              const end24 = to24Hour(a.end) || a.end;
              btn.textContent = `${start24} - ${end24} (${duration}m) ${branch ? '• ' + branch : ''}`;
              btn.addEventListener('click', () => {
                const timeInput = qs('input[name="appointment_time"]') || qs('#timeSelect') || qs('select[name="appointment_time"]');
                if(timeInput) timeInput.value = start24;
                hideAllMenus();
                checkConflictFor(start24);
              });
              li.appendChild(btn);
              ul.appendChild(li);
            });
            timeTakenMenuContent.appendChild(ul);
          }
        } else timeTakenMenuContent.textContent = 'No appointments';
        console.log('[patient-calendar] preloadTimeTaken done');
      } catch(err) {
        timeTakenMenuContent.textContent = 'Error loading appointments';
        console.error('[patient-calendar] preloadTimeTaken error', err);
      }
    }

    if(availableBtn){
      availableBtn.addEventListener('click', async (e) => {
        e.stopPropagation();
        // Try to open the full Time Table modal if available
        try {
          if (typeof initTimeTableModal === 'function') {
            const modalInstance = initTimeTableModal();
            if (modalInstance) {
              await modalInstance.openModal();
              return;
            }
          }
        } catch (err) {
          console.warn('[patient-calendar] Failed to open TimeTableModal, falling back to small menu', err);
        }

        // Fallback: toggle small menu as before
        if(availableMenu) availableMenu.classList.toggle('hidden');
        if(timeTakenMenu) timeTakenMenu.classList.add('hidden');
        if(!availableMenu.classList.contains('hidden')) await preloadAvailableSlots();
      });
    }
    if(timeTakenBtn){
      timeTakenBtn.addEventListener('click', async (e) => {
        e.stopPropagation();
        if(timeTakenMenu) timeTakenMenu.classList.toggle('hidden');
        if(availableMenu) availableMenu.classList.add('hidden');
        if(!timeTakenMenu.classList.contains('hidden')) await preloadTimeTaken();
      });
    }

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
