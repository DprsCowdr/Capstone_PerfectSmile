// calendar-admin.js - placeholder for admin/staff calendar logic
(function(){
  window.calendarAdmin = window.calendarAdmin || {};
  // Admin-specific handlers moved here from inline templates
  const baseUrl = window.baseUrl || '';

  function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : null;
  }

  // Lightweight form POST helper used by admin UI to fetch available slots
  async function postForm(url, data){
    const headers = {'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8', 'X-Requested-With':'XMLHttpRequest'};
    const csrf = getCsrfToken(); if(csrf) headers['X-CSRF-TOKEN'] = csrf;
    const body = new URLSearchParams();
    Object.keys(data || {}).forEach(k => { if (data[k] !== undefined && data[k] !== null) body.append(k, data[k]); });
    const res = await fetch((window.baseUrl || '') + url, { method: 'POST', headers, body: body.toString(), credentials: 'same-origin' });
    const text = await res.text();
    try { return JSON.parse(text); } catch(e) { return text; }
  }

  function editAppointment(appointmentId) {
    const appointment = (window.appointments || []).find(apt => apt.id == appointmentId);
    if (!appointment) {
      alert('Appointment not found');
      return;
    }
    const panel = document.getElementById('editAppointmentPanel');
    if (!panel) {
      alert('Edit panel not found.');
      return;
    }
    const form = panel.querySelector('form');
    if (form) {
      form.action = `${baseUrl}admin/appointments/update/${appointmentId}`;
      if (form.elements['patient']) form.elements['patient'].value = appointment.patient_id || '';
      if (form.elements['branch']) form.elements['branch'].value = appointment.branch_id || '';
      if (form.elements['date']) form.elements['date'].value = appointment.appointment_date || (appointment.appointment_datetime ? appointment.appointment_datetime.substring(0,10) : '');
      if (form.elements['time']) form.elements['time'].value = appointment.appointment_time || (appointment.appointment_datetime ? appointment.appointment_datetime.substring(11,16) : '');
      if (form.elements['remarks']) form.elements['remarks'].value = appointment.remarks || '';
    }
    panel.classList.add('active');
    const modal = document.getElementById('dayAppointmentsModal');
    if (modal) modal.classList.add('hidden');
  }

  function deleteAppointment(appointmentId) {
    if (!confirm('Are you sure you want to delete this appointment?')) return;
    const token = getCsrfToken();
    fetch(`${baseUrl}admin/appointments/delete/${appointmentId}`, {
      method: 'DELETE',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(token ? { csrf_token: token } : {})
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('Appointment deleted successfully');
        window.appointments = (window.appointments || []).filter(apt => apt.id != appointmentId);
        if (typeof updateCalendarDisplay === 'function') updateCalendarDisplay();
        const editPanel = document.getElementById('editAppointmentPanel');
        if (editPanel) editPanel.classList.remove('active');
        const dayModal = document.getElementById('dayAppointmentsModal');
        if (dayModal) dayModal.classList.add('hidden');
      } else {
        alert('Failed to delete appointment: ' + (data.message || 'Unknown error'));
      }
    })
    .catch(() => {
      alert('Failed to delete appointment');
    });
  }

  function approveAppointment(appointmentId) {
    const appointment = window.appointments.find(apt => apt.id == appointmentId);
    if (!appointment) { alert('Appointment not found'); return; }
    const dentistId = prompt('Enter dentist ID to assign to this appointment:');
    if (!dentistId) { alert('Dentist ID is required'); return; }
    const formData = new FormData();
    formData.append('dentist_id', dentistId);
    const token = getCsrfToken(); if (token) formData.append('csrf_token', token);
    fetch(`${baseUrl}admin/appointments/approve/${appointmentId}`, { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => { if (data.success) { alert('Appointment approved successfully'); location.reload(); } else { alert('Failed to approve appointment: ' + (data.message || '')); } })
    .catch(()=> alert('Failed to approve appointment'));
  }

  function declineAppointment(appointmentId) {
    const reason = prompt('Please provide a reason for declining this appointment:');
    if (!reason) { alert('Decline reason is required'); return; }
    const formData = new FormData();
    formData.append('reason', reason);
    const token = getCsrfToken(); if (token) formData.append('csrf_token', token);
    fetch(`${baseUrl}admin/appointments/decline/${appointmentId}`, { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => { if (data.success) { alert('Appointment declined successfully'); location.reload(); } else { alert('Failed to decline appointment: ' + (data.message || '')); } })
    .catch(()=> alert('Failed to decline appointment'));
  }

  // Intercept edit appointment form submit via AJAX
  function initEditFormHandler(){
    const editPanel = document.getElementById('editAppointmentPanel');
    if (!editPanel) return;
    const form = editPanel.querySelector('form');
    if (!form) return;
    form.onsubmit = function(e){
      e.preventDefault();
      const formData = new FormData(form);
      fetch(form.action, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Appointment updated successfully');
          location.reload();
        } else {
          alert('Failed to update appointment: ' + (data.message || 'Unknown error'));
        }
      })
      .catch(() => { alert('Failed to update appointment'); });
    };
  }

  window.calendarAdmin = {
    editAppointment,
    deleteAppointment,
    approveAppointment,
    declineAppointment,
  init: function(){ initEditFormHandler(); initAdminPrefillHandlers(); }
  };

  if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', window.calendarAdmin.init);
  else window.calendarAdmin.init();

  // --- Admin prefill handlers: populate suggested times on branch/date change ---
  function initAdminPrefillHandlers(){
    try{
      const adminTimeSelect = document.getElementById('adminTimeSelect');
      if(!adminTimeSelect) return; // nothing to do on pages without the control

      // Support multiple possible field names/ids used across templates
      const branchEl = document.querySelector('select[name="branch"]')
                        || document.querySelector('select[name="branch_id"]')
                        || document.getElementById('branchSelect')
                        || document.getElementById('branch_id');

      const dateEl = document.getElementById('appointmentDate')
                     || document.getElementById('selectedDateDisplay')
                     || document.querySelector('input[name="date"]')
                     || document.querySelector('input[name="appointment_date"]');

      const durationEl = document.querySelector('select[name="procedure_duration"]')
                         || document.querySelector('select[name="duration"]')
                         || document.getElementById('procedureDuration')
                         || document.getElementById('duration');

      const timeInput = document.getElementById('appointmentTime')
                        || document.querySelector('input[name="time"]')
                        || (function(){
                          // fallback: first time input inside the add/edit panel or any form
                          const panel = document.getElementById('addAppointmentPanel') || document.getElementById('editAppointmentPanel') || document.querySelector('form');
                          return panel ? panel.querySelector('input[type="time"]') : null;
                        })();

      async function populateAdminSuggestedTimes(){
        try{
          const branch = branchEl ? branchEl.value : '';
          let date = dateEl ? (dateEl.value || dateEl.getAttribute('value') || '') : '';
          // If selectedDateDisplay is a text field showing YYYY-MM-DD, use that
          if(!date && document.getElementById('selectedDateDisplay')) date = document.getElementById('selectedDateDisplay').value || '';
          console.debug('[calendar-admin] populateAdminSuggestedTimes called', { branch, date });
          if(!branch || !date){ adminTimeSelect.innerHTML = '<option value="">Select Suggested Time</option>'; return; }
          const duration = durationEl ? (durationEl.value || 30) : 30;
          // Use role-scoped admin endpoint for availability to enforce routing-based auth
          const res = await postForm('/admin/calendar/available-slots', { branch_id: branch, date: date, duration: Number(duration) });
          console.debug('[calendar-admin] available-slots response', res);
          if(res && (res.success || res.slots)){
            const slots = res.slots || (res.body && res.body.slots) || [];
            console.debug('[calendar-admin] parsed slots', slots);
            // populate both the select and the clickable list
            adminTimeSelect.innerHTML = '<option value="">Select Suggested Time</option>';
            const timeList = document.getElementById('adminTimeList');
            if(timeList) timeList.innerHTML = '';
            if(Array.isArray(slots) && slots.length){
              slots.slice(0,50).forEach(s => {
                const timeStr = (typeof s === 'string') ? s : (s.time || s.slot || '');
                if(!timeStr) return;
                const opt = document.createElement('option'); opt.value = timeStr; opt.textContent = timeStr;
                adminTimeSelect.appendChild(opt);
                if(timeList){
                  const li = document.createElement('li');
                  li.textContent = timeStr;
                  li.setAttribute('data-time', timeStr);
                  li.style.padding = '6px 12px'; li.style.cursor = 'pointer';
                  li.addEventListener('mouseenter', ()=> li.style.background = '#f3f4f6');
                  li.addEventListener('mouseleave', ()=> li.style.background = '');
                  li.addEventListener('click', function(){
                    try{ let t = this.getAttribute('data-time') || ''; if(t.length === 8) t = t.slice(0,5); if(timeInput) timeInput.value = t; }
                    catch(e){ console.error('adminTimeList click error', e); }
                    // hide list after click
                    if(timeList) timeList.style.display = 'none';
                  });
                  timeList.appendChild(li);
                }
              });
              if(timeList && timeList.children.length) timeList.style.display = 'none';
            } else {
              adminTimeSelect.innerHTML = '<option value="">No suggested times</option>';
              if(timeList) timeList.innerHTML = '<li style="padding:8px 12px;color:#666">No suggested times</li>';
            }
          } else {
            adminTimeSelect.innerHTML = '<option value="">No suggested times</option>';
          }
        }catch(err){ console.error('populateAdminSuggestedTimes error', err); if(adminTimeSelect) adminTimeSelect.innerHTML = '<option value="">Error loading times</option>'; }
      }

  // wire events: branch, date, and duration changes should refresh suggestions
  if(branchEl) branchEl.addEventListener('change', populateAdminSuggestedTimes);
  if(dateEl) dateEl.addEventListener('change', populateAdminSuggestedTimes);
  if(durationEl) durationEl.addEventListener('change', populateAdminSuggestedTimes);

      // Selecting from the select also fills the time input
      adminTimeSelect.addEventListener('change', function(){
        const v = adminTimeSelect.value;
        if(!v) return;
        try{ let t = v; if(typeof t === 'string' && t.length === 8 && t.indexOf(':')>=0) t = t.slice(0,5); if(timeInput) timeInput.value = t; }
        catch(e){ console.error('adminTimeSelect fill error', e); }
      });

      // Wire up the clickable suggestion list UI: show on focus, hide on blur with small delay to allow click
      const timeListEl = document.getElementById('adminTimeList');
      if(timeInput && timeListEl){
        timeInput.addEventListener('focus', function(){ if(timeListEl.children.length) timeListEl.style.display = 'block'; });
        timeInput.addEventListener('input', function(){
          // If user types, we can optionally filter suggestions
          const q = (timeInput.value || '').trim();
          Array.from(timeListEl.children).forEach(li => {
            const t = li.getAttribute('data-time') || li.textContent || '';
            li.style.display = (!q || t.indexOf(q) === 0) ? '' : 'none';
          });
          const anyVisible = Array.from(timeListEl.children).some(li => li.style.display !== 'none');
          timeListEl.style.display = anyVisible ? 'block' : 'none';
        });
        // hide on blur after short delay
        timeInput.addEventListener('blur', function(){ setTimeout(()=> { if(timeListEl) timeListEl.style.display = 'none'; }, 150); });
      }

      // initial populate if values present
      populateAdminSuggestedTimes();
    }catch(e){ console.error('initAdminPrefillHandlers error', e); }
  }
})();
