// calendar-staff.js - staff-specific calendar handlers (lightweight shell)
(function(){
  window.calendarStaff = window.calendarStaff || {};
  const baseUrl = window.baseUrl || '';

  function getCsrfToken(){
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : null;
  }

  // Simple POST helper reused by staff handlers
  async function postForm(url, data){
    const headers = {'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8', 'X-Requested-With':'XMLHttpRequest'};
    const csrf = getCsrfToken(); if(csrf) headers['X-CSRF-TOKEN'] = csrf;
    const body = new URLSearchParams();
    Object.keys(data || {}).forEach(k => { if (data[k] !== undefined && data[k] !== null) body.append(k, data[k]); });
    const res = await fetch((window.baseUrl || '') + url, { method: 'POST', headers, body: body.toString(), credentials: 'same-origin' });
    const text = await res.text(); try { return JSON.parse(text); } catch(e) { return text; }
  }

  function initStaffHandlers(){
    // Only run staff dashboard logic when the current user is staff. This isolates behavior.
    if (typeof window === 'undefined' || window.userType !== 'staff') return;
    console.debug('[calendar-staff] init (staff-only)');
    try{
      initStaffPrefillHandlers();
    }catch(e){ console.error('[calendar-staff] init error', e); }
  }

  // --- Staff-specific: populate suggested start times for selected branch/date and compute end time ---
  function initStaffPrefillHandlers(){
    const staffTimeSelect = document.getElementById('adminTimeSelect');
    if(!staffTimeSelect) return; // no staff add panel on page

    const branchEl = document.getElementById('branchSelect') || document.querySelector('select[name="branch"]') || document.querySelector('select[name="branch_id"]');
    const dateEl = document.getElementById('appointmentDate') || document.getElementById('selectedDateDisplay') || document.querySelector('input[name="date"]') || document.querySelector('input[name="appointment_date"]');
    const durationEl = document.getElementById('procedureDuration') || document.querySelector('select[name="procedure_duration"]') || document.querySelector('select[name="duration"]');
    const timeInput = document.getElementById('appointmentTime') || document.querySelector('input[name="time"]');
    const endInput = document.getElementById('appointmentEnd');
    const timeList = document.getElementById('adminTimeList');

    async function populateStaffSuggestedTimes(){
      try{
        const branch = branchEl ? branchEl.value : '';
        let date = dateEl ? (dateEl.value || dateEl.getAttribute('value') || '') : '';
        if(!branch || !date){ staffTimeSelect.innerHTML = '<option value="">Select Suggested Time</option>'; return; }
        // Duration might be stored as value='30' or option text '30 minutes'. Parse safely.
        let duration = 30;
        if (durationEl) {
          const raw = durationEl.value || (durationEl.selectedOptions && durationEl.selectedOptions[0] && durationEl.selectedOptions[0].text) || '';
          duration = parseInt(String(raw).replace(/[^0-9]/g, ''), 10) || 30;
        }
        const res = await postForm('/staff/calendar/available-slots', { branch_id: branch, date: date, duration: duration });
        const slots = (res && (res.slots || res.body && res.body.slots)) || [];
        staffTimeSelect.innerHTML = '<option value="">Select Suggested Time</option>';
        if(timeList) timeList.innerHTML = '';
        if(Array.isArray(slots) && slots.length){
          slots.slice(0,50).forEach(s => {
            const timeStr = (typeof s === 'string') ? s : (s.time || s.slot || '');
            if(!timeStr) return;
            const opt = document.createElement('option'); opt.value = timeStr; opt.textContent = timeStr; staffTimeSelect.appendChild(opt);
            if(timeList){
              const li = document.createElement('li'); li.textContent = timeStr; li.setAttribute('data-time', timeStr);
              li.style.padding = '6px 12px'; li.style.cursor = 'pointer';
              li.addEventListener('click', function(){
                try{
                  let t = this.getAttribute('data-time') || '';
                  if(t.length === 8 && t.indexOf(':') >= 0) t = t.slice(0,5);
                  if(timeInput) timeInput.value = t;
                  computeEndTime();
                }catch(e){ console.error('timeList click error', e); }
                if(timeList) timeList.style.display = 'none';
              });
              timeList.appendChild(li);
            }
          });
        } else {
          staffTimeSelect.innerHTML = '<option value="">No suggested times</option>';
          if(timeList) timeList.innerHTML = '<li style="padding:8px 12px;color:#666">No suggested times</li>';
        }
      }catch(err){ console.error('populateStaffSuggestedTimes error', err); staffTimeSelect.innerHTML = '<option value="">Error loading times</option>'; }
    }

    // When selecting a suggested time, fill the time input and compute end time
    staffTimeSelect.addEventListener('change', function(){
      const v = staffTimeSelect.value; if(!v) return;
      let t = v;
      if(typeof t === 'string' && t.indexOf(':')>=0) {
        if (t.length === 8) t = t.slice(0,5);
      }
      if(timeInput) timeInput.value = t;
      computeEndTime();
    });

    // compute end time from start + duration and set appointmentEnd if present
    function computeEndTime(){
      if(!timeInput || !durationEl || !endInput) return;
      const start = (timeInput.value || '').trim();
      if(!start) { endInput.value = ''; return; }
      const parts = start.split(':');
      if(parts.length < 2) { endInput.value = ''; return; }
      const startH = parseInt(parts[0], 10);
      const startM = parseInt(parts[1], 10);
      if (Number.isNaN(startH) || Number.isNaN(startM)) { endInput.value = ''; return; }

      // Parse duration robustly (value or option text)
      let dur = 30;
      const raw = durationEl.value || (durationEl.selectedOptions && durationEl.selectedOptions[0] && durationEl.selectedOptions[0].text) || '';
      dur = parseInt(String(raw).replace(/[^0-9]/g, ''), 10);
      if (!Number.isFinite(dur) || isNaN(dur)) dur = 30;

      const totalMinutes = startH * 60 + startM + dur;
      const wrapped = ((totalMinutes % (24*60)) + (24*60)) % (24*60);
      const endH = Math.floor(wrapped / 60);
      const endM = wrapped % 60;
      const pad = n => (n<10? '0'+n : ''+n);
      endInput.value = pad(endH) + ':' + pad(endM);
    }

    if(branchEl) branchEl.addEventListener('change', populateStaffSuggestedTimes);
    if(dateEl) dateEl.addEventListener('change', populateStaffSuggestedTimes);
    if(durationEl) durationEl.addEventListener('change', function(){ populateStaffSuggestedTimes(); computeEndTime(); });
    if(timeInput) timeInput.addEventListener('change', computeEndTime);

    // initial populate
    populateStaffSuggestedTimes();
  }

  window.calendarStaff = { init: initStaffHandlers };
  if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', window.calendarStaff.init);
  else window.calendarStaff.init();
})();
