// calendar-patient.js - migrated from patient-calendar.js
// Provide a safe stub so inline onclick handlers won't throw if the script
// hasn't initialized yet (avoids "openAddAppointmentPanelWithTime is not defined").
window.openAddAppointmentPanelWithTime = window.openAddAppointmentPanelWithTime || function(date, time){
  console.warn('openAddAppointmentPanelWithTime called before calendar initialization', date, time);
};

(function(){
  // Keep compatibility with existing code that expects these globals
  const baseUrl = window.baseUrl || '';
  const qs = (s) => document.querySelector(s);

  function postJson(url, data){
    const headers = {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'};
    const csrf = document.querySelector('meta[name="csrf-token"]');
    if(csrf) headers['X-CSRF-TOKEN'] = csrf.getAttribute('content');
    return fetch(baseUrl + url, {method:'POST', headers, body: JSON.stringify(data), credentials:'same-origin'})
      .then(r => r.text().then(t => {
        let parsed;
        try { parsed = JSON.parse(t); } catch(e) { parsed = t; }
        if (!r.ok) return Promise.reject({ status: r.status, body: parsed });
        return parsed;
      }));
  }

  // Helper to POST as form-encoded (for legacy controllers expecting $this->request->getPost())
  function postForm(url, data){
    const headers = {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-Requested-With': 'XMLHttpRequest'};
    const csrf = document.querySelector('meta[name="csrf-token"]');
    if(csrf) headers['X-CSRF-TOKEN'] = csrf.getAttribute('content');
  // no debug header in production
    const body = new URLSearchParams();
    Object.keys(data || {}).forEach(k => { if (data[k] !== undefined && data[k] !== null) body.append(k, data[k]); });
    return fetch(baseUrl + url, {method:'POST', headers, body: body.toString(), credentials:'same-origin'})
      .then(r => r.text().then(t => {
        let parsed;
        try { parsed = JSON.parse(t); } catch(e) { parsed = t; }
        if (!r.ok) return Promise.reject({ status: r.status, body: parsed });
        return parsed;
      }));
  }

  function initPatientHandlers(){
    // Run handlers on pages that use the appointment panel (patients, admin, staff, guests).
    // Previously this returned early and blocked admin/staff from getting available slots.
    if (typeof window !== 'undefined' && window.userType && !['patient','admin','staff','guest'].includes(window.userType)) return;

  // Accept either name="appointment_date" (patient) or name="date" (admin), fallback to #appointmentDate
  const dateEl = qs('input[name="appointment_date"]') || qs('input[name="date"]') || qs('#appointmentDate');
    if(!dateEl) return;

  const branchEl = qs('select[name="branch_id"]') || qs('#branchSelect');
  const durationEl = qs('select[name="procedure_duration"]') || qs('input[name="procedure_duration"]');
  const dentistEl = qs('select[name="dentist_id"]') || qs('#dentistSelect');
  // helper to locate service select on pages that use either name="service_id" or name="service"
  const getServiceEl = () => document.querySelector('select[name="service_id"]') || document.querySelector('select[name="service"]') || document.getElementById('service_id');
    const timeSelect = qs('select[name="appointment_time"]') || qs('#timeSelect') || qs('input[name="appointment_time"]');

    // helper: fetch available slots from server and populate timeSelect.
    async function fetchAndPopulateSlots(selectedDate){
      if(!timeSelect) return;
      const branch = branchEl ? branchEl.value : '';
      const dentist = dentistEl ? dentistEl.value : '';
      const serviceSel = getServiceEl();
      const serviceId = serviceSel ? serviceSel.value : '';

      // Server requires a duration (via service_id) otherwise it will return no slots.
      if (!serviceId) {
        timeSelect.innerHTML = '<option value="">Select service first</option>';
        return;
      }

      if (!selectedDate) {
        // try to derive from selectedDateDisplay or today
        const sel = document.getElementById('selectedDateDisplay');
        selectedDate = selectedDate || (sel && sel.value) || new Date().toISOString().slice(0,10);
      }

      try {
        timeSelect.innerHTML = '<option value="">Loading...</option>';
        const payload = Object.assign({ branch_id: branch, date: selectedDate, dentist_id: dentist, granularity: 3 }, serviceId ? { service_id: serviceId } : {});
        const res = await postForm('/appointments/available-slots', payload);

        if (res && res.success) {
          // Prefer complete slot list (all_slots includes unavailable metadata) for caching
          let slots = res.all_slots || res.slots || res.available_slots || [];
          // For populating the select show only available slots
          let displaySlots = res.slots || res.available_slots || [];
          if (!Array.isArray(slots)) slots = [];
          if (!Array.isArray(displaySlots)) displaySlots = [];

          // Cache the complete slot data (includes available and unavailable) so calendar-core can disable unavailable options
          try{ window.__available_slots_cache = window.__available_slots_cache || {}; if(selectedDate) window.__available_slots_cache[selectedDate] = slots; }catch(e){}

          timeSelect.innerHTML = '<option value="">Select Time</option>';
          // Populate only available slots; show unavailable separately via message if needed
          displaySlots.forEach(slot => {
            const timeStrRaw = (typeof slot === 'string') ? slot : (slot.time || slot.datetime || slot);
            let timeVal = timeStrRaw;
            try { if(typeof timeStrRaw === 'string' && timeStrRaw.indexOf(' ')>=0) timeVal = timeStrRaw.split(' ')[1].slice(0,5); else if(typeof timeStrRaw === 'string') timeVal = timeStrRaw.slice(0,5); }catch(e){}
            const opt = document.createElement('option');
            opt.value = timeVal;
            let label = window.calendarCore?.formatTime(timeVal) || timeVal;
            try{ if (slot && typeof slot === 'object'){
                if (slot.ends_at) label += ' — ends ' + (slot.ends_at.slice(0,5));
                else if (slot.end) label += ' — ends ' + (slot.end.slice(0,5));
                else if (slot.duration) {
                  const parts = timeVal.split(':'); const hh = parseInt(parts[0],10); const mm = parseInt(parts[1],10); const dur = parseInt(slot.duration,10);
                  if(!isNaN(hh) && !isNaN(mm) && !isNaN(dur)){
                    const dt = new Date(2000,0,1,hh,mm + dur);
                    const end = ('0'+dt.getHours()).slice(-2) + ':' + ('0'+dt.getMinutes()).slice(-2);
                    label += ' — ends ' + end;
                  }
                }
              }}catch(e){}
            opt.textContent = label;
            timeSelect.appendChild(opt);
          });

          // Prefill with first_available if provided
          try{
            if(res && res.metadata && res.metadata.first_available && !timeSelect.value){
              const fa = res.metadata.first_available;
              const pref = fa.time || (fa.datetime ? fa.datetime.split(' ')[1].slice(0,5) : null) || (fa.timestamp ? new Date(fa.timestamp*1000).toTimeString().slice(0,5) : null);
              if(pref) timeSelect.value = pref;
              if(fa.dentist_id){ const dentistSelect = document.querySelector('select[name="dentist_id"]') || document.getElementById('dentistSelect'); if(dentistSelect) dentistSelect.value = fa.dentist_id; }
            }
          }catch(e){ }
          return;
        }
      } catch (e) {
        console.warn('[calendar-patient] Server slot fetch failed, using fallback:', e);
      }

      // Fallback
      if (typeof window.calendarCore?.populateAvailableTimeSlots === 'function') {
        window.calendarCore.populateAvailableTimeSlots(selectedDate, timeSelect);
      }
    }

    // populate slots on date change using server API (works for admin too)
    dateEl.addEventListener('change', () => { fetchAndPopulateSlots(dateEl.value); });

    // Also refresh slots when branch or dentist changes
    if (branchEl) {
      branchEl.addEventListener('change', () => {
        // Clear operating hours cache when branch changes
        if (window.calendarCore && typeof window.calendarCore.clearOperatingHoursCache === 'function') {
          window.calendarCore.clearOperatingHoursCache();
        }
        
        const selected = dateEl ? dateEl.value : '';
        if (selected) fetchAndPopulateSlots(selected);
        else fetchAndPopulateSlots(null);
        
        // Also refresh week view if visible
        if (typeof updateWeekView === 'function') {
          updateWeekView();
        }
      });
    }

    if (dentistEl) {
      dentistEl.addEventListener('change', () => {
        const selected = dateEl.value;
        if (selected && timeSelect) {
          // Trigger the same slot loading logic
          fetchAndPopulateSlots(selected);
        }
      });
    }

    // also update when the service selection changes (service defines duration)
    try{
      const svcEl = getServiceEl();
      if(svcEl){
        svcEl.addEventListener('change', () => {
          const selected = dateEl ? dateEl.value : '';
          fetchAndPopulateSlots(selected);
        });
      }
    }catch(e){ console.error('service change hook failed', e); }

    // quick header buttons handled in patient-calendar.js previously — keep basic behavior
  const availableBtn = qs('#availableSlotsBtn');
  const availableMenuContent = qs('#availableSlotsMenuContent');
  if(availableBtn && availableMenuContent){
      availableBtn.addEventListener('click', async (e) => {
        e.stopPropagation();
        try {
          const branch = branchEl ? branchEl.value : '';
          // fallback to selectedDateDisplay or today when appointment_date is empty
          let date = dateEl ? dateEl.value : '';
          if (!date) {
            const sel = document.getElementById('selectedDateDisplay');
            if (sel && sel.value) date = sel.value;
            else date = new Date().toISOString().slice(0,10);
          }
          const dentist = dentistEl ? dentistEl.value : '';
          console.log('[calendar-patient] availableBtn clicked', { branch, date, duration, dentist });
          availableMenuContent.textContent = 'Loading...';
          // include service_id when present for fallback as well
          const svcSel_f = getServiceEl();
          const svcId_f = svcSel_f ? svcSel_f.value : '';
          const res = await postForm('/appointments/available-slots', Object.assign({branch_id:branch, date, dentist_id: dentist, granularity: 3}, svcId_f ? {service_id: svcId_f} : {}));
          if(res && res.success){
            // Enhanced API compatibility: try different slot sources
            // For UI to work correctly, prefer all_slots (available + unavailable) over just available_slots
            let slots = res.all_slots || res.slots || res.available_slots || [];
            // Cache the complete slot data (includes available and unavailable) so calendar-core can disable unavailable options
            try{ window.__available_slots_cache = window.__available_slots_cache || {}; if(date) window.__available_slots_cache[date] = slots; }catch(e){}
            
            // For the dropdown display, show only available slots
            let displaySlots = res.slots || res.available_slots || [];
            if (!Array.isArray(displaySlots)) displaySlots = [];
            
            // Ensure slots is an array
            if (!Array.isArray(slots)) {
              console.warn('[calendar-patient] availableBtn - slots is not an array:', typeof slots, slots);
              slots = [];
            }
            
            if(displaySlots.length === 0){
              availableMenuContent.textContent = 'No slots';
            } else {
              availableMenuContent.innerHTML = '';
              const ul = document.createElement('ul');
              ul.style.listStyle = 'none'; ul.style.margin='0'; ul.style.padding='0'; ul.style.maxHeight='220px'; ul.style.overflowY='auto';
                  displaySlots.slice(0,50).forEach(s=>{
                const li = document.createElement('li');
                const b = document.createElement('button'); b.type='button'; b.className='w-full text-left px-2 py-1 hover:bg-gray-100';
                const raw = (typeof s === 'string') ? s : (s.time || s.datetime || s.slot || '');
                let timeStr = raw;
                try { if(typeof raw === 'string' && raw.indexOf(' ')>=0) timeStr = raw.split(' ')[1].slice(0,5); else if(typeof raw === 'string') timeStr = raw.slice(0,5); }catch(e){}
                b.textContent = window.calendarCore?.formatTime(timeStr) || timeStr;
                if (s && typeof s === 'object' && s.dentist_id) b.dataset.dentist = s.dentist_id;
                b.onclick = ()=>{
                  if(timeSelect) timeSelect.value = timeStr;
                  // if slot carries dentist info, set dentist select
                  const dt = b.dataset && b.dataset.dentist ? b.dataset.dentist : null;
                  const dentistSelect = document.querySelector('select[name="dentist_id"]') || document.getElementById('dentistSelect');
                  if(dt && dentistSelect) dentistSelect.value = dt;
                  // ensure preferred time is synced and conflicts are checked
                  if(typeof window.calendarCore.populateAvailableTimeSlots === 'function') window.calendarCore.populateAvailableTimeSlots(date, timeSelect);
                  if(typeof window.checkConflictFor === 'function') window.checkConflictFor(timeStr);
                };
                li.appendChild(b);
                ul.appendChild(li);
              });
              availableMenuContent.appendChild(ul);

              // Prefill timeSelect with server first_available if present
              try{
                if(res && res.metadata && res.metadata.first_available && timeSelect && !timeSelect.value){
                  const fa = res.metadata.first_available;
                  const pref = fa.time || (fa.datetime ? fa.datetime.split(' ')[1].slice(0,5) : null);
                  if(pref) timeSelect.value = pref;
                  if(fa.dentist_id){ const dentistSelect = document.querySelector('select[name="dentist_id"]') || document.getElementById('dentistSelect'); if(dentistSelect) dentistSelect.value = fa.dentist_id; }
                }
              } catch(e){console.error('prefill from metadata failed', e)}
            }
          } else availableMenuContent.textContent='No slots';
        } catch(err) {
          console.error('[calendar-patient] available slots handler error', err);
          // Try fallback to API endpoint for guests (some installs use non-/api path)
          if (err && err.status === 401) {
            try {
              const branch = branchEl ? branchEl.value : '';
              let date = dateEl ? dateEl.value : '';
              if (!date) {
                const sel = document.getElementById('selectedDateDisplay');
                if (sel && sel.value) date = sel.value;
                else date = new Date().toISOString().slice(0,10);
              }
              const dentist = dentistEl ? dentistEl.value : '';
              const svcSel_f2 = getServiceEl();
              const svcId_f2 = svcSel_f2 ? svcSel_f2.value : '';
              const res2 = await postForm('/appointments/available-slots', Object.assign({branch_id:branch, date, dentist_id: dentist}, svcId_f2 ? {service_id: svcId_f2} : {}));
              if (res2 && res2.success) {
                  // Enhanced API compatibility: try different slot sources
                  let slots = res2.slots || res2.available_slots || [];
                  
                  // Ensure slots is an array
                  if (!Array.isArray(slots)) {
                    console.warn('[calendar-patient] quickPickBtn - slots is not an array:', typeof slots, slots);
                    slots = [];
                  }
                  
                  availableMenuContent.innerHTML = '';
                  const ul = document.createElement('ul'); ul.style.listStyle='none'; ul.style.margin='0'; ul.style.padding='0'; ul.style.maxHeight='220px'; ul.style.overflowY='auto';
                  (slots || []).slice(0,50).forEach(s=>{
                    const li = document.createElement('li');
                    const b = document.createElement('button'); b.type='button'; b.className='w-full text-left px-2 py-1 hover:bg-gray-100'; b.textContent=s;
                    b.onclick = ()=>{ if(timeSelect) timeSelect.value = s; if(typeof window.calendarCore.populateAvailableTimeSlots === 'function') window.calendarCore.populateAvailableTimeSlots(date, timeSelect); };
                    li.appendChild(b); ul.appendChild(li);
                  });
                  availableMenuContent.appendChild(ul);
                return;
              }
            } catch(e2) { console.error('[calendar-patient] fallback available slots error', e2); }
          }
          if (availableMenuContent) {
            if (err && err.status) {
              let msg = 'Error loading slots (HTTP ' + err.status + ')';
              if (err.body) {
                if (typeof err.body === 'string') msg += ': ' + err.body;
                else if (err.body.message) msg += ': ' + err.body.message;
                else msg += ': ' + JSON.stringify(err.body);
              }
              availableMenuContent.textContent = msg;
            } else {
              availableMenuContent.textContent='Error';
            }
          }
        }
      });
    }

  // show conflict messages returned from server under the time field
    const timeField = qs('select[name="appointment_time"]') || qs('#appointment_time') || qs('input[name="appointment_time"]');
    const conflictContainerId = 'appointment-time-conflicts';
    function ensureConflictContainer(){
      let c = document.getElementById(conflictContainerId);
      if(!c){
        c = document.createElement('div');
        c.id = conflictContainerId;
        c.className = 'mt-2 text-sm text-red-700';
        if(timeField && timeField.parentNode) timeField.parentNode.appendChild(c);
      }
      return c;
    }

  async function checkConflicts(){
      const branch = branchEl ? branchEl.value : '';
      const date = dateEl ? dateEl.value : '';
      const time = timeField ? (timeField.value || '') : '';
      if(!date || !time) return;
      try{
  // Do not send a client-side duration. Include service_id when present so server can compute authoritative conflicts.
  const svcSel = getServiceEl();
  const svcId = svcSel ? svcSel.value : '';
        const res = await postJson('/api/patient/check-conflicts', Object.assign({branch_id:branch, date, time}, svcId ? { service_id: svcId } : {}));
        const c = ensureConflictContainer();
        if(res && res.success && res.hasConflicts && Array.isArray(res.messages) && res.messages.length){
          c.innerHTML = '';
          res.messages.forEach(m=>{ const p = document.createElement('div'); p.textContent = m; c.appendChild(p); });
        } else {
          c.innerHTML = '';
        }
      }catch(e){
        console.error('checkConflicts error', e);
        // If unauthorized (no patient session), try the generic appointments endpoint as a fallback
        if (e && e.status === 401) {
            try {
            // include service_id when present so server can compute duration/conflicts
            const svcSel_cf = getServiceEl();
            const svcId_cf = svcSel_cf ? svcSel_cf.value : '';
            const res2 = await postForm('/appointments/check-conflicts', Object.assign({branch_id:branch, date, time}, svcId_cf ? {service_id: svcId_cf} : {}));
            const c = ensureConflictContainer();
            if (res2 && res2.success && res2.hasConflicts && Array.isArray(res2.conflicts) && res2.conflicts.length) {
              // build friendly messages from conflicts
              c.innerHTML = '';
              (res2.conflicts || []).forEach(cf => {
                const m = (cf.patient_name ? cf.patient_name : 'Appointment') + ' at ' + (cf.start || '') + '–' + (cf.end || '');
                const p = document.createElement('div'); p.textContent = m; c.appendChild(p);
              });
            } else {
              const c = ensureConflictContainer(); c.innerHTML = '';
            }
            return;
          } catch(e2) {
            console.error('fallback checkConflicts error', e2);
            const c = ensureConflictContainer();
            if (e2 && e2.status) {
              let msg = 'Error checking availability (HTTP ' + e2.status + ')';
                           // prefill earliest slot if time input is empty
                           try{
                             const timeInput = document.querySelector('input[name="appointment_time"]') || document.querySelector('select[name="appointment_time"]') || document.getElementById('timeSelect');
                             if(slots.length && timeInput && !timeInput.value){
                               timeInput.value = slots[0];
                               // transient hint
                               let hint = timeInput.parentNode && timeInput.parentNode.querySelector('.prefill-hint');
                               if(!hint){
                                 hint = document.createElement('div'); hint.className='prefill-hint text-sm text-gray-600 mt-1'; hint.textContent='Prefilled with earliest available slot — click to change'; if(timeInput.parentNode) timeInput.parentNode.appendChild(hint);
                                 setTimeout(()=>{ try{ hint.remove(); }catch(e){} }, 6000);
                               }
                             }
                           }catch(e){ console.error('prefill earliest slot error', e); }
              if (e2.body) {
                if (typeof e2.body === 'string') msg += ': ' + e2.body;
                else if (e2.body.message) msg += ': ' + e2.body.message;
                else msg += ': ' + JSON.stringify(e2.body);
              }
              c.textContent = msg;
            } else {
              c.textContent = 'Error checking availability';
            }
            return;
          }
        }
        const c = ensureConflictContainer();
        if (e && e.status) {
          let msg = 'Error checking availability (HTTP ' + e.status + ')';
          if (e.body) {
            if (typeof e.body === 'string') msg += ': ' + e.body;
            else if (e.body.message) msg += ': ' + e.body.message;
            else msg += ': ' + JSON.stringify(e.body);
          }
          c.textContent = msg;
        } else {
          c.textContent = 'Error checking availability';
        }
      }
    }

  // check conflicts when time or duration or branch or date change
    if(timeField) timeField.addEventListener('change', checkConflicts);
    if(durationEl) durationEl.addEventListener('change', checkConflicts);
    if(branchEl) branchEl.addEventListener('change', checkConflicts);
    if(dateEl) dateEl.addEventListener('change', checkConflicts);

    // Open add-appointment panel when clicking calendar cells (month/day views use onclick="openAddAppointmentPanelWithTime(date,time)")
    window.openAddAppointmentPanelWithTime = function(date, time){
      const panel = document.getElementById('addAppointmentPanel');
      if(!panel) return;
      // fill date and time
      const dateInput = document.getElementById('appointmentDate');
      const selectedDateDisplay = document.getElementById('selectedDateDisplay');
      const timeSelect = document.getElementById('timeSelect') || document.querySelector('select[name="appointment_time"]');
      if(dateInput){ dateInput.value = date; }
      if(selectedDateDisplay){ selectedDateDisplay.value = date; }
      if(time && timeSelect){ timeSelect.innerHTML = '<option value="'+time+'">'+(window.calendarCore ? window.calendarCore.formatTime(time) : time)+'</option>'; timeSelect.value = time; }

      // show panel
      panel.style.display = 'block';
      // ensure branch default exists
      const branchSelect = document.getElementById('branchSelect');
      if(branchSelect && branchSelect.options.length && !branchSelect.value && window.currentBranchId) branchSelect.value = window.currentBranchId;
    };

    // Hook appointment form submit to send via AJAX and avoid full redirect
    const appointmentForm = document.getElementById('appointmentForm');
    if(appointmentForm){
      appointmentForm.addEventListener('submit', async function(e){
        e.preventDefault();
        const form = e.target;
        const fd = new FormData(form);
        // include origin marker because server expects 'origin' sometimes
        if(!fd.has('origin')) fd.append('origin','patient');
        // build JSON payload
        const payload = {};
        fd.forEach((v,k)=>{ payload[k]=v; });
          // Normalize service field: some forms use name="service" instead of "service_id" - ensure server gets service_id
          if (!payload.service_id && payload.service) payload.service_id = payload.service;
          // Do NOT allow patient page to set procedure_duration - remove any client-side duration entirely.
          if (payload.procedure_duration) delete payload.procedure_duration;
        try{
          const isPatientPage = (window.userType === 'patient') || (window.CURRENT_USER_TYPE === 'patient') || window.location.pathname.startsWith('/patient');
          // Use admin/staff endpoint when on admin dashboard or staff pages
          const bookingEndpoint = (window.userType === 'admin' || window.userType === 'staff' || window.location.pathname.startsWith('/admin')) ? '/admin/appointments/create' : (isPatientPage ? '/patient/book-appointment' : '/guest/book-appointment');
          // ensure branch id when present on the page
          if(!payload.branch_id && branchEl) payload.branch_id = branchEl.value || '';
          // If the user is admin and set procedure_duration, ensure it's included; patient pages must not send it (handled earlier)
          if (window.userType === 'admin') {
            const pd = document.querySelector('input[name="procedure_duration"]') || document.getElementById('procedureDuration');
            if (pd && pd.value) payload.procedure_duration = pd.value;
          }
          const res = await postForm(bookingEndpoint, payload);
          if(res && res.success){
            // update client-side appointments array
            if(res.appointment){
              window.appointments = window.appointments || [];
              window.appointments.push(res.appointment);
            }
            // show success message in panel
            const msgEl = document.getElementById('appointmentSuccessMessage');
            const main = document.getElementById('appointmentSuccessMain');
            if(main) main.textContent = res.message || 'Appointment booked successfully!';
            if(msgEl) msgEl.style.display = 'block';
            // dispatch event so calendar can refresh
            window.dispatchEvent(new CustomEvent('appointmentCreated', { detail: res.appointment || null }));
            // close/hide form inputs
            form.reset();
          } else {
            alert((res && res.message) ? res.message : 'Failed to create appointment');
          }
        }catch(err){
          console.error(err);
          if(err && err.status === 422 && err.body){
            // display validation errors inside form
            let b = err.body;
            const errors = b && b.errors ? b.errors : (b && b.message ? b.message : b);
            try{
              let container = form.querySelector('.form-errors');
              if(!container){ container = document.createElement('div'); container.className='form-errors text-sm text-red-700 mb-2'; form.insertBefore(container, form.firstChild); }
              container.innerHTML='';
              if(Array.isArray(errors)) errors.forEach(m=>{ const p = document.createElement('div'); p.textContent = m; container.appendChild(p); });
              else if(typeof errors === 'object') Object.keys(errors).forEach(k=>{ const p = document.createElement('div'); p.textContent = errors[k]; container.appendChild(p); });
              else { const p = document.createElement('div'); p.textContent = String(errors); container.appendChild(p); }
              container.scrollIntoView({behavior:'smooth', block:'center'});
            }catch(e){ console.error('show validation errors failed', e); }
            return;
          }
          if(err && err.status === 401){ alert('Session expired or unauthorized. Please log in again.'); return; }
          alert('Error submitting appointment');
        }
      });
    }
  }

  // Listen for appointmentCreated globally to update UI without navigation
  window.addEventListener('appointmentCreated', function(e){
    try{
      // Rebuild calendar grid/counts if function is available
      if(typeof window.updateCalendarDisplay === 'function'){
        window.updateCalendarDisplay();
      } else if (typeof window.rebuildCalendarGrid === 'function'){
        window.rebuildCalendarGrid();
      }

      // If we're on the My Appointments page and there's a container, append the new appointment summary
      const container = document.getElementById('myAppointmentsList');
      const apt = e && e.detail ? e.detail : null;
      if(container && apt){
        const card = document.createElement('div');
        card.className = 'bg-white rounded-lg shadow-lg p-4 mb-4';
        const date = apt.appointment_datetime ? new Date(apt.appointment_datetime) : (apt.appointment_date ? new Date(apt.appointment_date + ' ' + (apt.appointment_time || '00:00')) : null);
        const dateText = date ? date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) : (apt.appointment_date || '');
        // Prefer appointment_time (HH:MM raw) but display in 12-hour friendly form when possible
        let timeText = 'TBD';
        if (apt.appointment_time) {
          if (window.calendarCore && typeof window.calendarCore.formatTime === 'function') timeText = window.calendarCore.formatTime(apt.appointment_time);
          else timeText = apt.appointment_time;
        } else if (apt.appointment_datetime) {
          timeText = new Date(apt.appointment_datetime).toLocaleTimeString('en-US',{hour: 'numeric', minute: '2-digit'});
        }
        card.innerHTML = `
          <div class="flex justify-between items-start">
            <div class="flex-1">
              <div class="flex items-center mb-2">
                <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>
                <h3 class="text-lg font-semibold text-gray-800">${dateText}</h3>
              </div>
              <div class="text-sm text-gray-600">
                <div><i class="fas fa-clock mr-2"></i><strong>Time:</strong> ${timeText}</div>
                <div><i class="fas fa-building mr-2"></i><strong>Branch:</strong> ${apt.branch_name || 'Not specified'}</div>
              </div>
            </div>
            <div class="text-right ml-4">
              <div class="mb-2"><span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">${apt.status || (apt.approval_status || 'Pending')}</span></div>
            </div>
          </div>
        `;
        // prepend so newest appear on top
        container.insertBefore(card, container.firstChild);
      }
    }catch(err){ console.error('Error handling appointmentCreated listener', err); }
  });

  // Remove taken slot when an appointment is created
  window.addEventListener('appointmentCreated', function(e){
    try{
      const apt = e && e.detail ? e.detail : null;
      if(apt){
  const time = apt.appointment_time || (apt.appointment_datetime ? (new Date(apt.appointment_datetime).toTimeString().substr(0,5)) : null) || apt.start || null;
        if(time){
          // try to disable matching options and remove menu entries
          const sel = document.querySelector('select[name="appointment_time"]') || document.getElementById('timeSelect');
          if(sel){ const opt = sel.querySelector('option[value="'+time+'"]'); if(opt) opt.disabled = true; }
          const menu = document.getElementById('availableSlotsMenuContent');
          if(menu){ Array.from(menu.querySelectorAll('button')).forEach(b => { if(b.textContent && b.textContent.trim().startsWith(time)) b.remove(); }); }
        }
      }
    }catch(err){ console.error('[calendar-patient] appointmentCreated removeTakenSlot error', err); }
  });

  if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initPatientHandlers);
  else initPatientHandlers();
})();
