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

  // Normalize many human time formats into 24-hour HH:MM (e.g. "9:25 AM" -> "09:25", "14:00" -> "14:00")
  // Returns null on obviously empty input.
  function to24Hour(timeStr){
    if(!timeStr && timeStr !== '0') return null;
    let s = String(timeStr).trim();
    if(!s) return null;
    // If already in HH:MM 24-hour form
    const hhmm24 = s.match(/^([01]?\d|2[0-3]):([0-5]\d)$/);
    if(hhmm24) {
      const h = hhmm24[1].padStart(2,'0');
      return `${h}:${hhmm24[2]}`;
    }
    // Match 12-hour like 9:25 AM or 9:25AM
    const m12 = s.match(/^(1[0-2]|0?[1-9]):([0-5]\d)\s*([AaPp][Mm])$/);
    if(m12){
      let h = parseInt(m12[1],10);
      const min = m12[2];
      const ampm = m12[3].toLowerCase();
      if(ampm === 'pm' && h !== 12) h += 12;
      if(ampm === 'am' && h === 12) h = 0;
      return `${String(h).padStart(2,'0')}:${min}`;
    }
    // Match hour only with am/pm e.g. "9am"
    const h12 = s.match(/^(1[0-2]|0?[1-9])\s*([AaPp][Mm])$/);
    if(h12){
      let h = parseInt(h12[1],10);
      const ampm = h12[2].toLowerCase();
      if(ampm === 'pm' && h !== 12) h += 12;
      if(ampm === 'am' && h === 12) h = 0;
      return `${String(h).padStart(2,'0')}:00`;
    }
    // Fallback: try Date parsing (best-effort) by appending to arbitrary date
    try{
      const dt = new Date('1970-01-01 ' + s);
      if(!isNaN(dt.getTime())){
        const hh = String(dt.getHours()).padStart(2,'0');
        const mm = String(dt.getMinutes()).padStart(2,'0');
        return `${hh}:${mm}`;
      }
    }catch(e){}
    return null;
  }

  // Make available to inline scripts and other modules as a safe global helper
  try{ if(typeof window !== 'undefined' && !window.to24Hour) window.to24Hour = to24Hour; }catch(e){}

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
  const safeToString = (v) => { try { if (v === null || v === undefined) return ''; if (typeof v.toString === 'function') return v.toString(); return String(v); } catch(e) { return ''; } };
  return fetch(baseUrl + url, {method:'POST', headers, body: safeToString(body), credentials:'same-origin'})
      .then(r => r.text().then(t => {
        let parsed;
        try { parsed = JSON.parse(t); } catch(e) { parsed = t; }
        if (!r.ok) return Promise.reject({ status: r.status, body: parsed });
        return parsed;
      }));
  }

  function initPatientHandlers(){
    // guard: only run patient handlers on patient pages
    if (typeof window !== 'undefined' && window.userType && window.userType !== 'patient') return;

  const dateEl = qs('input[name="appointment_date"]') || qs('#appointmentDate') || qs('#appointmentDate');
    if(!dateEl) return;

    const branchEl = qs('select[name="branch_id"]') || qs('#branchSelect');
    const durationEl = qs('select[name="procedure_duration"]') || qs('input[name="procedure_duration"]');
    const dentistEl = qs('select[name="dentist_id"]') || qs('#dentistSelect');
    const timeSelect = qs('select[name="appointment_time"]') || qs('#timeSelect') || qs('input[name="appointment_time"]');

    // populate slots on date change using core helper
    dateEl.addEventListener('change', () => {
      const selected = dateEl.value;
      if(timeSelect && typeof window.calendarCore.populateAvailableTimeSlots === 'function'){
        window.calendarCore.populateAvailableTimeSlots(selected, timeSelect);
      }
    });

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
          // duration may be an input or select; coerce to Number safely
          let duration = 30;
          if (durationEl) {
            const v = (durationEl.value !== undefined) ? durationEl.value : durationEl.getAttribute && durationEl.getAttribute('value');
            const n = Number(v);
            duration = Number.isFinite(n) && n > 0 ? n : 30;
          }
          const dentist = dentistEl ? dentistEl.value : '';
          console.log('[calendar-patient] availableBtn clicked', { branch, date, duration, dentist });
          availableMenuContent.textContent = 'Loading...';
          const res = await postForm('/appointments/available-slots', {branch_id:branch, date, duration, dentist_id: dentist});
          if(res && res.success){
            const slots = res.slots || [];
            if(slots.length === 0){
              availableMenuContent.textContent = 'No slots';
            } else {
              availableMenuContent.innerHTML = '';
              const ul = document.createElement('ul');
              ul.style.listStyle = 'none'; ul.style.margin='0'; ul.style.padding='0'; ul.style.maxHeight='220px'; ul.style.overflowY='auto';
              slots.slice(0,50).forEach(s=>{
                const li = document.createElement('li');
                const b = document.createElement('button'); b.type='button'; b.className='w-full text-left px-2 py-1 hover:bg-gray-100';
                const rawTimeStr = (typeof s === 'string') ? s : (s.time || s.slot || '');
                const timeStr = to24Hour(rawTimeStr) || rawTimeStr;
                b.textContent = timeStr;
                if (s && typeof s === 'object' && s.dentist_id) b.dataset.dentist = s.dentist_id;
                b.onclick = ()=>{
                  if(timeSelect) timeSelect.value = timeStr;
                  // if slot carries dentist info, set dentist select
                  const dt = b.dataset && b.dataset.dentist ? b.dataset.dentist : null;
                  const dentistSelect = document.querySelector('select[name="dentist_id"]') || document.getElementById('dentistSelect');
                  if(dt && dentistSelect) dentistSelect.value = dt;
                  if(typeof window.calendarCore.populateAvailableTimeSlots === 'function') window.calendarCore.populateAvailableTimeSlots(date, timeSelect);
                };
                li.appendChild(b);
                ul.appendChild(li);
              });
              availableMenuContent.appendChild(ul);
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
              let duration = 30;
              if (durationEl) {
                const v = (durationEl.value !== undefined) ? durationEl.value : durationEl.getAttribute && durationEl.getAttribute('value');
                const n = Number(v);
                duration = Number.isFinite(n) && n > 0 ? n : 30;
              }
              const dentist = dentistEl ? dentistEl.value : '';
              const res2 = await postForm('/appointments/available-slots', {branch_id:branch, date, duration, dentist_id: dentist});
              if (res2 && res2.success) {
                  const slots = res2.slots || [];
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
  const time = timeField ? (to24Hour(timeField.value) || (timeField.value || '')) : '';
      const duration = durationEl ? (Number(durationEl.value) || 30) : 30;
      if(!date || !time) return;
      try{
  const res = await postJson('/api/patient/check-conflicts', {branch_id:branch, date, time, duration});
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
            const res2 = await postForm('/appointments/check-conflicts', {branch_id:branch, date, time, duration});
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
  if(time && timeSelect){ const normalized = to24Hour(time) || time; timeSelect.innerHTML = '<option value="'+normalized+'">'+(window.calendarCore ? window.calendarCore.formatTime(normalized) : normalized)+'</option>'; timeSelect.value = normalized; }

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
        // Ensure procedure_duration is present and numeric in the payload
        if(!payload.procedure_duration){
          const pdEl = document.querySelector('select[name="procedure_duration"]') || document.querySelector('input[name="procedure_duration"]');
          if(pdEl){
            const pdv = pdEl.value || pdEl.getAttribute('value') || '';
            const pn = Number(pdv);
            payload.procedure_duration = (Number.isFinite(pn) && pn > 0) ? pn : 30;
          } else {
            payload.procedure_duration = 30;
          }
        } else {
          const pn = Number(payload.procedure_duration);
          payload.procedure_duration = (Number.isFinite(pn) && pn > 0) ? pn : 30;
        }
        try{
            const isPatientPage = (window.userType === 'patient') || (window.CURRENT_USER_TYPE === 'patient') || window.location.pathname.startsWith('/patient');
          const bookingEndpoint = isPatientPage ? '/patient/book-appointment' : '/guest/book-appointment';
          // ensure branch id when present on the page
          if(!payload.branch_id && branchEl) payload.branch_id = branchEl.value || '';
            // Normalize appointment_time to HH:MM before posting
            if(payload.appointment_time){
              const normalized = to24Hour(payload.appointment_time);
              if(normalized) payload.appointment_time = normalized;
            }
          const res = await postForm(bookingEndpoint, payload);
          if(res && res.success){
            // update client-side appointments array
            if(res.appointment){
              try {
                // Only append to the global appointments array if the appointment belongs to the current user (defensive)
                const owner = res.appointment.user_id || res.appointment.patient_id || res.appointment.patient || null;
                if (window.userType === 'patient' && window.currentUserId) {
                  if (owner && Number(owner) === Number(window.currentUserId)) {
                    window.appointments = window.appointments || [];
                    window.appointments.push(res.appointment);
                  } else {
                    // Don't pollute the patient's calendar with other users' appointments
                    console.debug('[calendar-patient] Skipped pushing appointment not owned by current patient', res.appointment);
                  }
                } else {
                  // For non-patient contexts, preserve existing behavior
                  window.appointments = window.appointments || [];
                  window.appointments.push(res.appointment);
                }
              } catch (e) {
                console.error('[calendar-patient] Error while handling appointment push', e);
                window.appointments = window.appointments || [];
                window.appointments.push(res.appointment);
              }
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
        const timeText = (apt.appointment_time) ? apt.appointment_time : (apt.appointment_datetime ? new Date(apt.appointment_datetime).toLocaleTimeString('en-US',{hour: 'numeric', minute: '2-digit'}) : 'TBD');
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
        const time = apt.appointment_time || (apt.appointment_datetime ? apt.appointment_datetime.substring(11,16) : null) || apt.start || null;
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
