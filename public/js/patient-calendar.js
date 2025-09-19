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
      opt.textContent = `${a.patient_name || '—'} — ${formatTimeHM(a.start)} to ${formatTimeHM(a.end)} (${a.procedure_duration || '30'}m)`;
      selectEl.appendChild(opt);
    });
  }

  function parseAmPmTo24(t){
    // t like '8:35 AM' or '12:05 PM' -> '08:35' or '12:05'
    if(!t || typeof t !== 'string') return t;
    const m = t.match(/^(\d{1,2}:\d{2})\s*([AP]M)$/i);
    if(!m) return t.slice(0,5);
    let [_, hm, ampm] = m;
    let [h, mm] = hm.split(':').map(Number);
    if(ampm.toUpperCase() === 'PM' && h < 12) h += 12;
    if(ampm.toUpperCase() === 'AM' && h === 12) h = 0;
    return (h<10? '0'+h : String(h)) + ':' + (mm<10? '0'+mm : String(mm));
  }

  function slotValue(slot){
    // prefer explicit datetime (Y-m-d H:i:s) -> return HH:MM
    if(slot && typeof slot === 'object' && slot.datetime){
      try{ return slot.datetime.split(' ')[1].slice(0,5); }catch(e){}
    }
    if(slot && typeof slot === 'object' && slot.time){
      // convert AM/PM time string to 24h HH:MM
      return parseAmPmTo24(slot.time);
    }
    if(typeof slot === 'string'){
      // assume 'HH:MM' or 'HH:MM:SS'
      return slot.slice(0,5);
    }
    return '';
  }

  function slotLabel(slot){
    // friendly label: HH:MM (ends HH:MM) — 30m
    const val = slotValue(slot);
    if(slot && typeof slot === 'object'){
      const ends = slot.ends_at ? (slot.ends_at.length>0 ? slot.ends_at : '') : '';
      const dur = slot.duration_minutes || slot.duration || '';
      // ends_at may be 'g:i A' format — convert if needed
      let endsVal = '';
      if(slot.datetime && slot.ends_at === undefined){
        // compute ends from datetime + duration if available
        try{
          const dt = slot.datetime.split(' ');
          if(dt && dt.length>1 && dur){
            const startTs = new Date(slot.datetime.replace(' ', 'T')).getTime();
            const endTs = new Date(startTs + (Number(dur) * 60 * 1000));
            const hh = ('0' + endTsToString(endTs).slice(0,2)).slice(-2);
          }
        }catch(e){ }
      }
      if(slot.ends_at && typeof slot.ends_at === 'string'){
        // ends_at likely like '9:05 AM' -> convert to 24h
        endsVal = parseAmPmTo24(slot.ends_at);
      } else if(slot.datetime && dur){
        try{
          const start = new Date(slot.datetime.replace(' ', 'T'));
          const end = new Date(start.getTime() + (Number(dur) * 60 * 1000));
          endsVal = ('0'+end.getHours()).slice(-2) + ':' + ('0'+end.getMinutes()).slice(-2);
        }catch(e){ endsVal = ''; }
      }
      const parts = [val];
      if(endsVal) parts.push('(ends ' + endsVal + ')');
      if(dur) parts.push('— ' + dur + 'm');
      return parts.join(' ');
    }
    return val || '';
  }

  function endTsToString(ms){
    const d = new Date(ms);
    return ('0'+d.getHours()).slice(-2) + ':' + ('0'+d.getMinutes()).slice(-2);
  }

  function showSlots(containerEl, slots, metadata){
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
    ul.className = 'slot-list';
    // find first available hint from metadata
    let firstAvailableValue = null;
    if(metadata && metadata.first_available){
      firstAvailableValue = slotValue(metadata.first_available);
    }
    slots.forEach(s => {
      const li = document.createElement('li');
      li.className = 'slot-item';
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'slot-btn';
      const val = slotValue(s);
      btn.textContent = slotLabel(s) || val;
      btn.dataset.value = val;
      if (s && typeof s === 'object' && s.dentist_id) btn.dataset.dentist = s.dentist_id;
      // visually mark unavailable
      if(s && s.available === false){ btn.disabled = true; btn.classList.add('slot-unavailable'); }
      // highlight first available
      if(firstAvailableValue && val === firstAvailableValue){ btn.classList.add('slot-first-available'); }
      btn.addEventListener('click', () => {
        const timeInput = qs('input[name="appointment_time"]') || qs('select[name="appointment_time"]');
        if(timeInput){
          try{
            if(timeInput.tagName && timeInput.tagName.toLowerCase() === 'select'){
              let opt = timeInput.querySelector('option[value="'+val+'"]');
              if(!opt){
                opt = document.createElement('option');
                opt.value = val;
                opt.textContent = btn.textContent || val;
                timeInput.insertBefore(opt, timeInput.firstChild);
              }
              timeInput.value = val;
            } else {
              timeInput.value = val;
            }
          }catch(e){ try{ timeInput.value = val; }catch(err){} }
        }
        // if slot includes dentist, preselect dentist
        const dt = btn.dataset && btn.dataset.dentist ? btn.dataset.dentist : null;
        const dentistSelect = qs('select[name="dentist_id"]') || qs('#dentistSelect');
        if(dt && dentistSelect) dentistSelect.value = dt;
        // trigger conflict check for the selected time
        checkConflictFor(val);
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
    // Resolve fallback containers if the passed one is null
    if(!containerEl){
      containerEl = qs('#timeConflicts') || qs('.time-conflicts') || document.getElementById('timeConflicts');
    }
    if(!containerEl){
      // Nothing to render into; avoid throwing and log for diagnostics
      console.warn('[patient-calendar] showConflicts: no container element found to render conflicts');
      return;
    }

    // Safely render conflicts
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
    const svcSel = document.querySelector('select[name="service_id"]') || document.querySelector('select[name="service"]') || document.getElementById('service_id');
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
      if (durationEl) {
        const v = (durationEl.value !== undefined) ? durationEl.value : (durationEl.selectedIndex ? durationEl.options[durationEl.selectedIndex].value : null);
        const n = Number(v);
        duration = Number.isFinite(n) && n > 0 ? n : 30;
      }
      const dentist = dentistEl ? dentistEl.value : '';
      if(!date) return;
      
      // Include service_id when present so server can compute authoritative duration
  // Support both select[name="service_id"] and select[name="service"] (some pages use `service`)
  const svcSel = document.querySelector('select[name="service_id"]') || document.querySelector('select[name="service"]') || document.getElementById('service_id');
      const svcId = svcSel ? svcSel.value : '';
      
  const payload = {branch_id: branch, date, dentist_id: dentist, granularity: 3};
      // Only send duration if no service_id (let server be authoritative)
      if (svcId) {
        payload.service_id = svcId;
      } else {
        payload.duration = Number(duration);
      }
      
      postForm('/appointments/available-slots', payload)
        .then(res => {
          if(res && res.success){
            // Enhanced API compatibility: try different slot sources
            // For UI to work correctly, prefer all_slots (available + unavailable) over just available_slots
            let slots = res.all_slots || res.slots || res.available_slots || [];
            
            // For display purposes, show only available slots
            let displaySlots = res.slots || res.available_slots || [];
            
            // Ensure slots is an array
            if (!Array.isArray(slots)) {
              console.warn('[patient-calendar] fetchAvailableSlots - slots is not an array:', typeof slots, slots);
              slots = [];
            }
            if (!Array.isArray(displaySlots)) {
              console.warn('[patient-calendar] fetchAvailableSlots - displaySlots is not an array:', typeof displaySlots, displaySlots);
              displaySlots = [];
            }
            
            // Cache the complete slot data (includes available and unavailable) so calendar-core can disable unavailable options
            try{ window.__available_slots_cache = window.__available_slots_cache || {}; if(date) window.__available_slots_cache[date] = slots; }catch(e){}
            
            showSlots(slotsContainer, displaySlots);
            // prefill earliest slot: prefer server-provided first_available, otherwise use first available slot
            try{
              const timeInput = qs('input[name="appointment_time"]') || qs('select[name="appointment_time"]') || qs('#timeSelect');
              if(timeInput && !timeInput.value){
                let firstTime = null;
                let dentistForFirst = null;
                if(res && res.metadata && res.metadata.first_available){
                  const fa = res.metadata.first_available;
                  firstTime = fa.time || (fa.datetime ? fa.datetime.split(' ')[1].slice(0,5) : null);
                  dentistForFirst = fa.dentist_id || null;
                }
                if(!firstTime && slots.length){
                  const first = slots[0];
                  firstTime = (typeof first === 'string') ? first : (first.time || first);
                  dentistForFirst = first && first.dentist_id ? first.dentist_id : dentistForFirst;
                }
                if(firstTime){
                  try{
                    if(timeInput.tagName && timeInput.tagName.toLowerCase() === 'select'){
                      let opt = timeInput.querySelector('option[value="'+firstTime+'"]');
                      if(!opt){
                        opt = document.createElement('option');
                        opt.value = firstTime;
                        opt.textContent = firstTime;
                        timeInput.insertBefore(opt, timeInput.firstChild);
                      }
                      timeInput.value = firstTime;
                    } else {
                      timeInput.value = firstTime;
                    }
                  }catch(e){ try{ timeInput.value = firstTime; }catch(err){} }
                  if(dentistForFirst){ const dentistSelect = qs('select[name="dentist_id"]') || qs('#dentistSelect'); if(dentistSelect) dentistSelect.value = dentistForFirst; }
                  showPrefillHint(timeInput);
                  // use metadata duration when available for conflict check
                  const mdDuration = res && res.metadata && res.metadata.duration_minutes ? Number(res.metadata.duration_minutes) : null;
                  checkConflictFor(firstTime, mdDuration);
                }
              }
            }catch(e){ console.error('prefill earliest slot error', e); }
          }
        }).catch(console.error);
    }

    // UX hint: show a helpful note near the Service select explaining service affects available times
    try{
      if(svcSel){
        let hint = svcSel.parentNode && svcSel.parentNode.querySelector('.service-duration-hint');
        if(!hint){
          hint = document.createElement('div');
          hint.className = 'service-duration-hint text-xs text-gray-500 mt-1';
          hint.textContent = 'Service determines duration — pick a service to see available times';
          if(svcSel.parentNode) svcSel.parentNode.appendChild(hint);
        }
        // when service changes, reload slots (server needs service to compute duration)
        svcSel.addEventListener('change', () => { loadSlots(); });
      }
    }catch(e){ console.error('service hint injection failed', e); }

    function checkConflictFor(timeValue, overrideDuration){
      const branch = branchEl ? branchEl.value : '';
      const date = dateEl.value;
      const localDuration = durationEl ? (Number(durationEl.value) || 30) : 30;
      const dentist = dentistEl ? dentistEl.value : '';
      const duration = (typeof overrideDuration === 'number' && overrideDuration > 0) ? overrideDuration : localDuration;
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

        // Ensure service/service_id present: prefer select[name="service_id"] then select[name="service"].
        if(!payload.service && !payload.service_id){
          const svcSelForm = document.querySelector('select[name="service_id"]') || document.querySelector('select[name="service"]') || document.querySelector('input[name="service_id"]') || document.querySelector('input[name="service"]');
          const svcText = document.getElementById('service_text');
          let sv = '';
          if(svcSelForm) sv = svcSelForm.value || svcSelForm.getAttribute('value') || '';
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
            if (typeof showInvoiceAlert === 'function') showInvoiceAlert((res && res.message) ? res.message : 'Failed to create appointment', 'error', 5000); else alert((res && res.message) ? res.message : 'Failed to create appointment');
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
            if (typeof showInvoiceAlert === 'function') showInvoiceAlert('Session expired or unauthorized. Please log in again.', 'warning', 6000); else alert('Session expired or unauthorized. Please log in again.');
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

    async function preloadAvailableSlots(){
      try {
        if(!availableMenuContent) return;
        console.log('[patient-calendar] preloadAvailableSlots start');
        availableMenuContent.textContent = 'Loading...';
        const branch = branchEl ? branchEl.value : '';
        const date = dateEl ? dateEl.value : '';
        const duration = durationEl ? (Number(durationEl.value) || 30) : 30;
        const dentist = dentistEl ? dentistEl.value : '';
        
        // Include service_id when present so server can compute authoritative duration for staff/admin
        const svcSel = document.querySelector('select[name="service_id"]') || document.getElementById('service_id');
        const svcId = svcSel ? svcSel.value : '';
        
  const payload = {branch_id: branch, date, dentist_id: dentist, granularity: 3};
        // Only send duration if no service_id (let server be authoritative)
        if (svcId) {
          payload.service_id = svcId;
        } else {
          payload.duration = duration;
        }
        
        const res = await postForm('/appointments/available-slots', payload);
        if(res && res.success){
          // Enhanced API compatibility: try different slot sources
          let slots = res.slots || res.available_slots || [];
          
          // Debug logging for troubleshooting
          console.log('[patient-calendar] API response:', res);
          console.log('[patient-calendar] Extracted slots:', slots);
          
          // Ensure slots is an array
          if (!Array.isArray(slots)) {
            console.warn('[patient-calendar] slots is not an array:', typeof slots, slots);
            slots = [];
          }
          
          if(slots.length === 0){
            availableMenuContent.textContent = 'No available slots';
          } else {
            // render full list in a small scrollable panel
            availableMenuContent.innerHTML = '';
            const ul = document.createElement('ul');
            ul.style.listStyle = 'none';
            ul.style.margin = '0';
            ul.style.padding = '0';
            ul.style.maxHeight = '220px';
            ul.style.overflowY = 'auto';
            slots.forEach(s => {
              const li = document.createElement('li');
              const btn = document.createElement('button');
              btn.type = 'button';
              btn.className = 'w-full text-left px-2 py-1 hover:bg-gray-100';
              const timeStr = (typeof s === 'string') ? s : (s.time || s);
              btn.textContent = timeStr;
              if(s && typeof s === 'object' && s.dentist_id) btn.dataset.dentist = s.dentist_id;
              btn.addEventListener('click', () => {
                const timeInput = qs('input[name="appointment_time"]') || qs('#timeSelect') || qs('select[name="appointment_time"]');
                if(timeInput){
                  try{
                    if(timeInput.tagName && timeInput.tagName.toLowerCase() === 'select'){
                      let opt = timeInput.querySelector('option[value="'+timeStr+'"]');
                      if(!opt){
                        opt = document.createElement('option');
                        opt.value = timeStr;
                        opt.textContent = timeStr;
                        timeInput.insertBefore(opt, timeInput.firstChild);
                      }
                      timeInput.value = timeStr;
                    } else {
                      timeInput.value = timeStr;
                    }
                  }catch(e){ try{ timeInput.value = timeStr; }catch(err){} }
                }
                // if slot contains dentist, select it
                if(btn.dataset && btn.dataset.dentist){ const dentistSelect = qs('select[name="dentist_id"]') || qs('#dentistSelect'); if(dentistSelect) dentistSelect.value = btn.dataset.dentist; }
                hideAllMenus();
                checkConflictFor(timeStr);
              });
              li.appendChild(btn);
              ul.appendChild(li);
            });
            availableMenuContent.appendChild(ul);
            
            // Show enhanced metadata if available
            if (res.metadata) {
              const metaDiv = document.createElement('div');
              metaDiv.style.fontSize = '12px';
              metaDiv.style.color = '#666';
              metaDiv.style.padding = '8px';
              metaDiv.style.borderTop = '1px solid #eee';
              metaDiv.textContent = `${res.metadata.available_count} available of ${res.metadata.total_slots_checked} total slots`;
              availableMenuContent.appendChild(metaDiv);
            }
            
            // Show user's existing appointments for rescheduling
            if (res.unavailable_slots && res.unavailable_slots.length > 0) {
              const userAppointments = res.unavailable_slots.filter(slot => slot.owned_by_current_user);
              if (userAppointments.length > 0) {
                const rescheduleDiv = document.createElement('div');
                rescheduleDiv.style.marginTop = '10px';
                rescheduleDiv.style.padding = '8px';
                rescheduleDiv.style.borderTop = '1px solid #eee';
                rescheduleDiv.style.backgroundColor = '#fff3cd';
                
                const rescheduleTitle = document.createElement('div');
                rescheduleTitle.textContent = 'Your existing appointments (click to reschedule):';
                rescheduleTitle.style.fontSize = '12px';
                rescheduleTitle.style.fontWeight = 'bold';
                rescheduleTitle.style.marginBottom = '4px';
                rescheduleDiv.appendChild(rescheduleTitle);
                
                userAppointments.forEach(slot => {
                  const rescheduleBtn = document.createElement('button');
                  rescheduleBtn.type = 'button';
                  rescheduleBtn.className = 'w-full text-left px-2 py-1 hover:bg-yellow-100 text-orange-700';
                  rescheduleBtn.style.fontSize = '11px';
                  rescheduleBtn.textContent = `${slot.time} - ${slot.ends_at || 'existing appointment'}`;
                  rescheduleBtn.title = 'Click to reschedule this appointment';
                  rescheduleBtn.addEventListener('click', () => {
                    alert(`Reschedule appointment at ${slot.time}? (Feature coming soon)`);
                  });
                  rescheduleDiv.appendChild(rescheduleBtn);
                });
                
                availableMenuContent.appendChild(rescheduleDiv);
              }
            }
          }
        } else {
          availableMenuContent.textContent = 'No available slots';
        }
        console.log('[patient-calendar] preloadAvailableSlots done');
      } catch(err) {
        availableMenuContent.textContent = 'Error loading slots';
        console.error('[patient-calendar] preloadAvailableSlots error', err);
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
              btn.textContent = `${a.start} - ${a.end} (${duration}m) ${branch ? '• ' + branch : ''}`;
              btn.addEventListener('click', () => {
                const timeInput = qs('input[name="appointment_time"]') || qs('#timeSelect') || qs('select[name="appointment_time"]');
                if(timeInput){
                  try{
                    if(timeInput.tagName && timeInput.tagName.toLowerCase() === 'select'){
                      let opt = timeInput.querySelector('option[value="'+a.start+'"]');
                      if(!opt){
                        opt = document.createElement('option');
                        opt.value = a.start;
                        opt.textContent = a.start;
                        timeInput.insertBefore(opt, timeInput.firstChild);
                      }
                      timeInput.value = a.start;
                    } else {
                      timeInput.value = a.start;
                    }
                  }catch(e){ try{ timeInput.value = a.start; }catch(err){} }
                }
                hideAllMenus();
                checkConflictFor(a.start);
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
