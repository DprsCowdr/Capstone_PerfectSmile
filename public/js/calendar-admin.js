// calendar-admin.js - placeholder for admin/staff calendar logic
(function(){
  window.calendarAdmin = window.calendarAdmin || {};
  // Admin-specific handlers moved here from inline templates
  const baseUrl = window.baseUrl || '';

  function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : null;
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
    init: function(){ initEditFormHandler(); }
  };

  if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', window.calendarAdmin.init);
  else window.calendarAdmin.init();
})();
