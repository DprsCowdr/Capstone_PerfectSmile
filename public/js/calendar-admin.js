// calendar-admin.js - Dedicated admin calendar logic 
// Separate from patient calendar to avoid function conflicts
(function(){
  'use strict';
  
  const baseUrl = window.baseUrl || '';
  
  // Admin-specific form field selectors
  const ADMIN_SELECTORS = {
    dateInput: 'input[name="date"]',
    branchSelect: 'select[name="branch"]',
    dentistSelect: 'select[name="dentist"]', 
    serviceSelect: 'select[name="service_id"]',
    timeSelect: 'select[name="appointment_time"]',
    patientSelect: 'select[name="patient"]',
    procedureDuration: 'input[name="procedure_duration"]'
  };

  function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : null;
  }

  // Normalize various time string formats to 'HH:MM' 24-hour
  function normalizeTime(timeStr) {
    if (!timeStr) return '';
    timeStr = String(timeStr).trim();
    // If already in HH:MM 24-hour format
    if (/^\d{1,2}:\d{2}$/.test(timeStr)) {
      const parts = timeStr.split(':');
      return parts[0].padStart(2, '0') + ':' + parts[1];
    }
    // Match 12-hour formats like '9:00 AM' or '09:00PM'
    const m = timeStr.match(/^(\d{1,2}):(\d{2})\s*([APap][Mm])$/);
    if (m) {
      let h = parseInt(m[1], 10);
      const min = m[2];
      const ampm = m[3].toUpperCase();
      if (ampm === 'PM' && h < 12) h += 12;
      if (ampm === 'AM' && h === 12) h = 0;
      return String(h).padStart(2, '0') + ':' + min;
    }
    // Try to parse ISO date-time 'YYYY-MM-DD HH:MM:SS'
    const isoMatch = timeStr.match(/\d{4}-\d{2}-\d{2}\s+(\d{2}):(\d{2}):\d{2}/);
    if (isoMatch) return isoMatch[1] + ':' + isoMatch[2];
    // Fallback: take first HH:MM found
    const anyMatch = timeStr.match(/(\d{1,2}:\d{2})/);
    if (anyMatch) {
      const parts = anyMatch[1].split(':');
      return parts[0].padStart(2, '0') + ':' + parts[1];
    }
    return '';
  }

  // Convert 24-hour time to 12-hour format for display
  function format12Hour(time24) {
    if (!time24) return '';
    const [hours, minutes] = time24.split(':').map(Number);
    if (isNaN(hours) || isNaN(minutes)) return time24;
    
    const period = hours >= 12 ? 'PM' : 'AM';
    const displayHours = hours === 0 ? 12 : (hours > 12 ? hours - 12 : hours);
    return `${displayHours}:${minutes.toString().padStart(2, '0')} ${period}`;
  }

  // Calculate appointment end time
  function calculateAppointmentEndTime(startTime, durationMinutes, graceMinutes) {
    const totalMinutes = (durationMinutes || 180) + (graceMinutes || 20);
    const [hours, minutes] = startTime.split(':').map(Number);
    const startMinutes = hours * 60 + minutes;
    const endMinutes = startMinutes + totalMinutes;
    const endHours = Math.floor(endMinutes / 60);
    const endMins = endMinutes % 60;
    return `${endHours.toString().padStart(2, '0')}:${endMins.toString().padStart(2, '0')}`;
  }

  // Admin-specific POST helper with proper CSRF handling
  function postAdminForm(url, data) {
  if (window.__psm_debug) console.debug('[admin-calendar] postAdminForm called with:', { url, data });
    
    const headers = {
      'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
      'X-Requested-With': 'XMLHttpRequest'
    };
    
    const csrf = getCsrfToken();
    if (csrf) headers['X-CSRF-TOKEN'] = csrf;
    
    const body = new URLSearchParams();
    Object.keys(data || {}).forEach(k => {
      if (data[k] !== undefined && data[k] !== null && data[k] !== '') {
        body.append(k, data[k]);
        if (window.__psm_debug) console.debug(`[admin-calendar] Adding to body: ${k} = ${data[k]}`);
      }
    });
    
  if (window.__psm_debug) console.debug('[admin-calendar] Final body string:', body.toString());
  if (window.__psm_debug) console.debug('[admin-calendar] Headers:', headers);
    
    // Build a safe absolute URL to avoid admin routing conflicts and double origins
    let fullUrl = '';
    const currentOrigin = window.location.origin;

    // If baseUrl already contains an origin, strip it for safe concatenation
    let normalizedBase = baseUrl || '';
    try {
      const parsed = new URL(normalizedBase, currentOrigin);
      // If normalizedBase included origin, use only its pathname
      if (parsed.origin && normalizedBase.indexOf(parsed.origin) === 0) {
        normalizedBase = parsed.pathname.replace(/\/$/, '');
      }
    } catch (e) {
      // baseUrl might be a relative path; leave as-is
    }

    // Prefer absolute path for known API endpoint to avoid admin prefixing
    if (url && url.indexOf('/') === 0) {
      // Ensure we don't end up with duplicated slashes
      fullUrl = currentOrigin.replace(/\/$/, '') + (normalizedBase ? (normalizedBase.startsWith('/') ? normalizedBase : '/' + normalizedBase) : '') + url;
    } else {
      // Relative URL case
      fullUrl = currentOrigin.replace(/\/$/, '') + (normalizedBase ? (normalizedBase.startsWith('/') ? normalizedBase : '/' + normalizedBase) : '') + '/' + url;
    }

    // Normalize double slashes (except after protocol)
    fullUrl = fullUrl.replace(/([^:\/]\/+)\/+/g, '$1');

  if (window.__psm_debug) console.debug('[admin-calendar] Complete URL:', fullUrl);

    return fetch(fullUrl, {
      method: 'POST',
      headers,
      body: body.toString(),
      credentials: 'same-origin'
    }).then(r => {
  if (window.__psm_debug) console.debug('[admin-calendar] Response status:', r.status);
  if (window.__psm_debug) console.debug('[admin-calendar] Response headers:', Object.fromEntries(r.headers.entries()));
      return r.text().then(t => {
          if (window.__psm_debug) console.debug('[admin-calendar] Raw response text (debug):', t);
        let parsed;
        try { 
          parsed = JSON.parse(t); 
        } catch(e) { 
          console.warn('Non-JSON response:', t);
          parsed = { success: false, message: 'Invalid JSON response', raw: t };
        }
        if (!r.ok) {
          console.error('[admin-calendar] HTTP error:', r.status, r.statusText);
          return Promise.reject({ status: r.status, body: parsed });
        }
        return parsed;
      });
    });
  }

  // Fetch available slots for admin dashboard
  async function fetchAdminAvailableSlots() {
    if (window.__psm_debug) console.log('[admin-calendar] fetchAdminAvailableSlots called');
    
    const dateEl = document.querySelector(ADMIN_SELECTORS.dateInput);
    const branchEl = document.querySelector(ADMIN_SELECTORS.branchSelect);
    const dentistEl = document.querySelector(ADMIN_SELECTORS.dentistSelect);
    let serviceEl = document.querySelector(ADMIN_SELECTORS.serviceSelect);
    
    // Fallback selectors if primary ones don't work
    if (!serviceEl) {
      serviceEl = document.getElementById('service_id') || document.querySelector('#service_id');
      if (window.__psm_debug) console.log('[admin-calendar] Using fallback service selector:', serviceEl ? 'found' : 'not found');
    }
    
    const timeEl = document.querySelector(ADMIN_SELECTORS.timeSelect);
    
    if (!timeEl) {
      if (window.__psm_debug) console.warn('[admin-calendar] Time select element not found');
      return;
    }
    
    // Get current values with fallback for service selection
    const date = dateEl ? dateEl.value : '';
    const branchId = branchEl ? branchEl.value : '';
    const dentistId = dentistEl ? dentistEl.value : '';
    let serviceId = serviceEl ? serviceEl.value : '';
    
    // Fallback: if serviceId is empty but there's a selected option, try to get it
    if (!serviceId && serviceEl && serviceEl.selectedIndex > 0) {
      serviceId = serviceEl.options[serviceEl.selectedIndex]?.value || '';
      console.log('[admin-calendar] Fallback service ID from selectedIndex:', serviceId);
    }
    
    console.log('[admin-calendar] Element values debug:', {
      dateEl: dateEl ? { name: dateEl.name, value: dateEl.value } : null,
      branchEl: branchEl ? { name: branchEl.name, value: branchEl.value } : null,
      dentistEl: dentistEl ? { name: dentistEl.name, value: dentistEl.value } : null,
      serviceEl: serviceEl ? { 
        name: serviceEl.name, 
        value: serviceEl.value,
        selectedIndex: serviceEl.selectedIndex,
        optionsCount: serviceEl.options.length,
        selectedOption: serviceEl.selectedIndex >= 0 ? {
          value: serviceEl.options[serviceEl.selectedIndex]?.value,
          text: serviceEl.options[serviceEl.selectedIndex]?.text
        } : null
      } : null
    });
    
    console.log('[admin-calendar] Current form values:', {
      date, branchId, dentistId, serviceId
    });
    
    // Clear time options while loading
    timeEl.innerHTML = '<option value="">Loading...</option>';
    
    // Validate required fields
    if (!date) {
      timeEl.innerHTML = '<option value="">Select date first</option>';
      if (window.__psm_debug) console.warn('[admin-calendar] Date is empty:', { date, dateEl: dateEl ? dateEl.outerHTML : null });
      return;
    }
    
    if (!branchId) {
      timeEl.innerHTML = '<option value="">Select branch first</option>';
      if (window.__psm_debug) console.warn('[admin-calendar] Branch ID is empty:', { branchId, branchEl: branchEl ? branchEl.outerHTML : null });
      return;
    }
    
    if (!serviceId) {
      timeEl.innerHTML = '<option value="">Select service first</option>';
      if (window.__psm_debug) console.warn('[admin-calendar] Service ID is empty:', { serviceId, serviceEl: serviceEl ? serviceEl.outerHTML : null });
      return;
    }
    
    // Extra validation: ensure values are not just whitespace
    if (!date.trim() || !branchId.trim() || !serviceId.trim()) {
      timeEl.innerHTML = '<option value="">Please check your selections</option>';
      if (window.__psm_debug) console.error('[admin-calendar] Form values contain only whitespace:', { 
        date: `"${date}"`, 
        branchId: `"${branchId}"`, 
        serviceId: `"${serviceId}"` 
      });
      return;
    }
    
    try {
      // Build payload with all required fields
      const payload = {
        branch_id: branchId,
        date: date,
        service_id: serviceId,
        granularity: 3
      };
      
      // Add dentist if selected
      if (dentistId) {
        payload.dentist_id = dentistId;
      }
      
      if (window.__psm_debug) console.log('[admin-calendar] Posting to /appointments/available-slots with payload:', payload);
      
      // Use the general endpoint with proper admin session authentication
      const response = await fetch(`${baseUrl}appointments/available-slots`, {
        method: 'POST',
        body: new URLSearchParams(payload),
        headers: { 
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        credentials: 'include'
      }).then(r => r.json());
      if (window.__psm_debug) console.log('[admin-calendar] Available slots response:', response);
      
      if (response && (response.success !== false) && (response.available_slots || response.slots)) {
        populateAdminTimeSlots(response, timeEl);
      } else {
        if (window.__psm_debug) console.error('[admin-calendar] API returned error:', response);
        timeEl.innerHTML = '<option value="">No slots available</option>';
        
        // Show error message if available
        if (response && response.message) {
          showAdminMessage(response.message, 'error');
        }
      }
    } catch (error) {
      if (window.__psm_debug) console.error('[admin-calendar] Error fetching slots:', error);
      timeEl.innerHTML = '<option value="">Error loading slots</option>';
      showAdminMessage('Error loading available times. Please try again.', 'error');
    }
  }
  
  // Populate time select with available slots
  function populateAdminTimeSlots(response, timeElement) {
    // Prefer all_slots so admin can see unavailable/blocked slots too
    const slots = response.all_slots || response.available_slots || response.slots || [];
    
    if (window.__psm_debug) console.log('[admin-calendar] Populating slots:', slots);
    
    // Clear existing options
    timeElement.innerHTML = '<option value="">Select Time</option>';
    
    if (!Array.isArray(slots) || slots.length === 0) {
      timeElement.innerHTML = '<option value="">No available slots</option>';
      return;
    }
    
    // Add slot options with end time information
    slots.forEach(slot => {
      const option = document.createElement('option');
      
      // Extract time value - handle both 12-hour (9:00 AM) and 24-hour (09:00) formats
      let timeValue;
      let displayTime;
      
      if (typeof slot === 'string') {
        // slot string - try to normalize AM/PM to 24-hour
        timeValue = normalizeTime(slot);
        displayTime = slot;
      } else if (slot.datetime) {
        // Prefer authoritative datetime when available (format: "YYYY-MM-DD HH:MM:SS")
        const timePart = slot.datetime.split(' ')[1];
        timeValue = timePart ? timePart.slice(0, 5) : (slot.time ? normalizeTime(slot.time) : ''); // "09:00"
        displayTime = slot.time || timeValue;
      } else if (slot.time) {
        // Fallback to slot.time but normalize AM/PM strings into 24-hour
        timeValue = normalizeTime(slot.time);
        displayTime = slot.time;
      } else {
        timeValue = String(slot);
        displayTime = String(slot);
      }
      
      // Use the time as-is for value (the API expects this format)
      option.value = timeValue;
      
      // Create label with end time if available
      let label = displayTime;
      if (slot && typeof slot === 'object') {
        if (slot.ends_at) {
          label += ' — ends ' + slot.ends_at;
        } else if (slot.end) {
          label += ' — ends ' + slot.end;
        } else if (slot.duration_minutes) {
          // Calculate end time from duration (fallback if ends_at not provided)
          try {
            const timeMatch = timeValue.match(/(\d+):(\d+)/);
            if (timeMatch) {
              const hours = parseInt(timeMatch[1], 10);
              const minutes = parseInt(timeMatch[2], 10);
              const endDate = new Date(2000, 0, 1, hours, minutes + slot.duration_minutes);
              const endTime = String(endDate.getHours()).padStart(2, '0') + ':' + 
                             String(endDate.getMinutes()).padStart(2, '0');
              label += ' — ends ' + endTime;
            }
          } catch (e) {
            // If calculation fails, just use the base time
          }
        }
      }
      
      // If slot object explicitly marked unavailable, disable and annotate
      if (slot && typeof slot === 'object' && slot.available === false) {
        option.disabled = true;
        option.textContent = label + ' (blocked)';
        if (slot.blocking_info) {
          const bi = slot.blocking_info;
          option.title = (bi.type ? bi.type + ': ' : '') + (bi.start || '') + (bi.end ? ' - ' + bi.end : '');
        }
      } else {
        option.textContent = label;
      }
      timeElement.appendChild(option);
    });
    
    // Pre-select first available if specified
    if (response.metadata && response.metadata.first_available) {
      const firstAvailable = response.metadata.first_available;
      let firstTime = null;
      
      if (firstAvailable.time) {
        firstTime = firstAvailable.time; // Use full time format
      } else if (firstAvailable.datetime) {
        firstTime = firstAvailable.datetime.split(' ')[1].slice(0, 5);
      }
      
      if (firstTime) {
        // Normalize firstAvailable time to 24-hour if it's in 12-hour format
        const norm = normalizeTime(firstTime);
        timeElement.value = norm;
        if (window.__psm_debug) console.log('[admin-calendar] Pre-selected first available time:', norm);
      }
    }
    
    // If metadata exists, prefer its counts for clarity
    const meta = response.metadata || {};
    const avail = meta.available_count !== undefined ? meta.available_count : slots.filter(s => s && s.available !== false).length;
    showAdminMessage(`${avail} available / ${slots.length} total slots loaded`, 'success');
  }
  
  // Show admin message
  function showAdminMessage(message, type = 'info') {
    if (window.__psm_debug) console.log(`[admin-calendar] ${type.toUpperCase()}: ${message}`);
    
    // Try to show in UI if message elements exist
    const successEl = document.getElementById('availabilityMessage');
    const errorEl = document.getElementById('unavailableMessage');
    
    if (type === 'success' && successEl) {
      const textEl = document.getElementById('availabilityText');
      if (textEl) textEl.textContent = message;
      successEl.style.display = 'block';
      if (errorEl) errorEl.style.display = 'none';
    } else if (type === 'error' && errorEl) {
      const textEl = document.getElementById('unavailableText');
      if (textEl) textEl.textContent = message;
      errorEl.style.display = 'block';  
      if (successEl) successEl.style.display = 'none';
    }
  }
  
  // Setup admin calendar event listeners
  function initAdminCalendar() {
    if (window.__psm_debug) {
      console.log('[admin-calendar] Initializing admin calendar');
      console.log('[admin-calendar] window.userType:', window.userType);
      console.log('[admin-calendar] URL:', window.location.href);
    }
    
    // Check if we're on admin/staff page by URL or user type
    const isAdminPage = window.location.href.includes('/admin/') || 
                       window.location.href.includes('/staff/') ||
                       (window.userType && ['admin', 'staff'].includes(window.userType));
    
    if (!isAdminPage) {
      if (window.__psm_debug) console.log('[admin-calendar] Not admin/staff page, but continuing anyway for testing');
      // Don't return - let's try to initialize anyway for testing
    }
    
    // Get form elements with detailed logging
    const elements = {};
    Object.keys(ADMIN_SELECTORS).forEach(key => {
      elements[key] = document.querySelector(ADMIN_SELECTORS[key]);
      if (window.__psm_debug) {
        if (elements[key]) {
          console.log(`[admin-calendar] Found ${key}:`, elements[key].name, elements[key].value);
        } else {
          console.warn(`[admin-calendar] Missing ${key} element with selector:`, ADMIN_SELECTORS[key]);
        }
      }
    });
    
    console.log('[admin-calendar] Element summary:', Object.keys(elements).map(k => ({
      name: k,
      found: !!elements[k],
      selector: ADMIN_SELECTORS[k]
    })));
    
    // Setup change listeners to refresh slots
    const triggersRefresh = [elements.dateInput, elements.branchSelect, elements.dentistSelect, elements.serviceSelect];
    
    triggersRefresh.forEach((element, index) => {
      if (element) {
        console.log(`[admin-calendar] Setting up listener for:`, element.name || element.id);
        element.addEventListener('change', (e) => {
          console.log(`[admin-calendar] ${e.target.name || e.target.id} changed to:`, e.target.value);
          
          // Prevent any form submission that might be triggered by this change
          e.preventDefault();
          e.stopPropagation();
          
          // Call our AJAX function
          fetchAdminAvailableSlots();
        });
      } else {
        console.warn(`[admin-calendar] Cannot set up listener for trigger ${index} - element not found`);
      }
    });
    
    // Setup form submission handler
    const form = document.getElementById('appointmentForm');
    if (form) {
      console.log('[admin-calendar] Setting up form submission handler');
      form.addEventListener('submit', handleAdminFormSubmit);
    } else {
      console.warn('[admin-calendar] appointmentForm not found');
    }
    
    console.log('[admin-calendar] Admin calendar initialization complete');
  }
  
  // Handle admin form submission
  function handleAdminFormSubmit(e) {
    console.log('[admin-calendar] Form submission started');
    
    // Validate required fields
    const requiredFields = [
      { selector: ADMIN_SELECTORS.dateInput, name: 'Date' },
      { selector: ADMIN_SELECTORS.branchSelect, name: 'Branch' },
      { selector: ADMIN_SELECTORS.serviceSelect, name: 'Service' },
      { selector: ADMIN_SELECTORS.timeSelect, name: 'Time' },
      { selector: ADMIN_SELECTORS.patientSelect, name: 'Patient' }
    ];
    
    const missingFields = [];
    requiredFields.forEach(field => {
      const element = document.querySelector(field.selector);
      if (!element || !element.value) {
        missingFields.push(field.name);
      }
    });
    
    if (missingFields.length > 0) {
      e.preventDefault();
      showAdminMessage(`Please fill in: ${missingFields.join(', ')}`, 'error');
      return false;
    }

    // Before submission, ensure server-expected field names exist on the form
    try {
      const form = e.target || document.getElementById('appointmentForm');
      if (form) {
        // appointment_date (server expects this)
        let ad = form.querySelector('input[name="appointment_date"]');
        if (!ad) {
          ad = document.createElement('input');
          ad.type = 'hidden';
          ad.name = 'appointment_date';
          form.appendChild(ad);
        }
        const dateEl = document.querySelector(ADMIN_SELECTORS.dateInput) || document.querySelector('input[name="date"]');
        ad.value = dateEl ? dateEl.value : '';

        // appointment_time
        let at = form.querySelector('input[name="appointment_time"]') || form.querySelector('input[name="time"]');
        if (!at) {
          at = document.createElement('input');
          at.type = 'hidden';
          at.name = 'appointment_time';
          form.appendChild(at);
        }
        const timeEl = document.querySelector(ADMIN_SELECTORS.timeSelect) || document.querySelector('select[name="appointment_time"]') || document.querySelector('select[name="time"]');
        at.value = timeEl ? timeEl.value : '';

        // branch_id (server resolves branch via branch_id or session)
        let bid = form.querySelector('input[name="branch_id"]');
        if (!bid) {
          bid = document.createElement('input');
          bid.type = 'hidden';
          bid.name = 'branch_id';
          form.appendChild(bid);
        }
        const branchEl = document.querySelector(ADMIN_SELECTORS.branchSelect) || document.querySelector('select[name="branch_id"]') || document.querySelector('select[name="branch"]');
        bid.value = branchEl ? branchEl.value : '';

        // service_id & procedure_duration
        let svc = form.querySelector('input[name="service_id"]') || form.querySelector('select[name="service_id"]');
        if (!svc) {
          // ensure a hidden input exists for service_id when select is not submitted for some reason
          svc = document.createElement('input');
          svc.type = 'hidden';
          svc.name = 'service_id';
          form.appendChild(svc);
        }
        const serviceEl = document.querySelector(ADMIN_SELECTORS.serviceSelect) || document.getElementById('service_id');
        svc.value = serviceEl ? serviceEl.value : '';

        let pd = form.querySelector('input[name="procedure_duration"]');
        if (!pd) {
          pd = document.createElement('input');
          pd.type = 'hidden';
          pd.name = 'procedure_duration';
          form.appendChild(pd);
        }
        const pdEl = document.querySelector(ADMIN_SELECTORS.procedureDuration) || document.getElementById('procedureDuration');
        pd.value = pdEl ? pdEl.value : '';

        console.log('[admin-calendar] Injected hidden form values', {
          appointment_date: ad.value,
          appointment_time: at.value,
          branch_id: bid.value,
          service_id: svc.value,
          procedure_duration: pd.value
        });
      }
    } catch (ex) {
      console.warn('[admin-calendar] Failed to inject hidden inputs before submit', ex);
    }

    console.log('[admin-calendar] Form validation passed, submitting');
    return true;
  }

  // Legacy functions for compatibility with existing calendar scripts
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
    if (confirm('Are you sure you want to delete this appointment?')) {
      const formData = new FormData();
      formData.append('_method', 'DELETE');
      const csrf = getCsrfToken();
      if (csrf) formData.append('_token', csrf);
      
      fetch(`${baseUrl}admin/appointments/delete/${appointmentId}`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Appointment deleted successfully');
          location.reload();
        } else {
          alert('Failed to delete appointment: ' + (data.message || 'Unknown error'));
        }
      })
      .catch(() => {
        alert('Failed to delete appointment');
      });
    }
  }

  function approveAppointment(appointmentId) {
    if (confirm('Are you sure you want to approve this appointment?')) {
      const dentistId = prompt('Enter dentist ID to assign to this appointment:');
      if (!dentistId) {
        alert('Dentist ID is required');
        return;
      }
      
      const formData = new FormData();
      formData.append('dentist_id', dentistId);
      const csrf = getCsrfToken();
      if (csrf) formData.append('_token', csrf);
      
      fetch(`${baseUrl}admin/appointments/approve/${appointmentId}`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Appointment approved successfully');
          // Refresh appointments and availability so UI updates immediately
          let selDate = null;
          try {
            if (typeof loadAppointmentsForDate === 'function') {
              selDate = document.querySelector('input[name="date"]') ? document.querySelector('input[name="date"]').value : null;
              loadAppointmentsForDate(selDate || new Date().toISOString().slice(0,10));
              try { if (typeof refreshAppointmentsForDate === 'function') refreshAppointmentsForDate(selDate || new Date().toISOString().slice(0,10)); } catch(e) { console.warn('refreshAppointmentsForDate call failed', e); }
            }
          } catch (e) { console.warn('Failed to refresh appointments after approve', e); }
          try { window.dispatchEvent(new Event('availability:changed')); } catch(e) {}
          // Clear any available slots cache so patient/admin selects re-query the server
          try {
            if (window.__available_slots_cache && selDate) {
              delete window.__available_slots_cache[selDate];
            }
            if (window.calendarCore && typeof window.calendarCore.refreshAllTimeSlots === 'function') {
              window.calendarCore.refreshAllTimeSlots();
            }
          } catch (e) { console.warn('Failed to clear available slots cache or refresh selects', e); }
          // Broadcast to other tabs via BroadcastChannel and localStorage fallback
          try { if (window._availabilityBroadcast) window._availabilityBroadcast.postMessage({ detail: { appointment_id: appointmentId, action: 'approved' } }); } catch(e){}
          try { localStorage.setItem('_availability_update', String(Date.now())); } catch(e){}
          // NOTE: removed location.reload() so dynamic refresh (events, cache clearing, broadcast)
          // can update other tabs and the current admin UI without a full reload.
         } else {
           alert('Failed to approve appointment: ' + (data.message || 'Unknown error'));
         }
       })
      .catch(() => {
        alert('Failed to approve appointment');
      });
    }
  }

  function declineAppointment(appointmentId) {
    const reason = prompt('Please provide a reason for declining this appointment:');
    if (reason) {
      const formData = new FormData();
      formData.append('reason', reason);
      const csrf = getCsrfToken();
      if (csrf) formData.append('_token', csrf);
      
      fetch(`${baseUrl}admin/appointments/decline/${appointmentId}`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Appointment declined successfully');
          // Refresh appointments and availability so UI updates immediately (mirror approve flow)
          let selDateDecline = null;
          try {
            if (typeof loadAppointmentsForDate === 'function') {
              selDateDecline = document.querySelector('input[name="date"]') ? document.querySelector('input[name="date"]').value : null;
              loadAppointmentsForDate(selDateDecline || new Date().toISOString().slice(0,10));
              try { if (typeof refreshAppointmentsForDate === 'function') refreshAppointmentsForDate(selDateDecline || new Date().toISOString().slice(0,10)); } catch(e) { console.warn('refreshAppointmentsForDate call failed', e); }
            }
          } catch (e) { console.warn('Failed to refresh appointments after decline', e); }
          try { window.dispatchEvent(new Event('availability:changed')); } catch(e) {}
          try {
            if (window.__available_slots_cache && selDateDecline) delete window.__available_slots_cache[selDateDecline];
            if (window.calendarCore && typeof window.calendarCore.refreshAllTimeSlots === 'function') window.calendarCore.refreshAllTimeSlots();
          } catch(e) { console.warn('Failed to clear available slots cache or refresh selects after decline', e); }
          try { if (window._availabilityBroadcast) window._availabilityBroadcast.postMessage({ detail: { appointment_id: appointmentId, action: 'declined' } }); } catch(e){}
          try { localStorage.setItem('_availability_update', String(Date.now())); } catch(e){}
          // NOTE: removed location.reload() so dynamic refresh (events, cache clearing, broadcast)
          // can update other tabs and the current admin UI without a full reload.
        } else {
          alert('Failed to decline appointment: ' + (data.message || 'Unknown error'));
        }
      })
      .catch(() => {
        alert('Failed to decline appointment');
      });
    }
  }

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminCalendar);
  } else {
    initAdminCalendar();
  }
  
  // Expose legacy admin calendar interface for compatibility
  window.calendarAdmin = {
    editAppointment,
    deleteAppointment,
    approveAppointment,
    declineAppointment,
    fetchSlots: fetchAdminAvailableSlots,
    init: initAdminCalendar
  };
  
  // Export for debugging
  window.adminCalendar = {
    fetchSlots: fetchAdminAvailableSlots,
    init: initAdminCalendar,
    test: function() {
      console.log('=== Admin Calendar Test ===');
      console.log('Elements check:');
      Object.keys(ADMIN_SELECTORS).forEach(key => {
        const el = document.querySelector(ADMIN_SELECTORS[key]);
        console.log(`${key}:`, el ? { name: el.name, value: el.value, id: el.id } : 'NOT FOUND');
      });
      console.log('=== End Test ===');
    },
    populateTestData: function() {
      console.log('Populating test data...');
      const dateEl = document.querySelector(ADMIN_SELECTORS.dateInput);
      const branchEl = document.querySelector(ADMIN_SELECTORS.branchSelect);
      const serviceEl = document.querySelector(ADMIN_SELECTORS.serviceSelect) || document.getElementById('service_id');
      
      if (dateEl) {
        dateEl.value = '2025-09-20';
        console.log('Set date to:', dateEl.value);
      }
      if (branchEl && branchEl.options.length > 1) {
        branchEl.selectedIndex = 1;
        console.log('Set branch to:', branchEl.value, branchEl.options[branchEl.selectedIndex].text);
      }
      if (serviceEl && serviceEl.options.length > 1) {
        serviceEl.selectedIndex = 1;
        console.log('Set service to:', serviceEl.value, serviceEl.options[serviceEl.selectedIndex].text);
        
        // Trigger change event to make sure the value is registered
        serviceEl.dispatchEvent(new Event('change', { bubbles: true }));
      }
      
      console.log('Test data populated, calling fetchSlots...');
      fetchAdminAvailableSlots();
    },
    testServiceSelection: function() {
      console.log('=== Service Selection Test ===');
      const serviceEl = document.querySelector(ADMIN_SELECTORS.serviceSelect) || document.getElementById('service_id');
      if (serviceEl) {
        console.log('Service element found:', {
          value: serviceEl.value,
          selectedIndex: serviceEl.selectedIndex,
          optionsCount: serviceEl.options.length,
          allOptions: Array.from(serviceEl.options).map((opt, i) => ({
            index: i,
            value: opt.value,
            text: opt.text
          }))
        });
      } else {
        console.log('Service element NOT found');
      }
      console.log('=== End Test ===');
    }
  };
  
  // Admin Available Slots Menu functionality (similar to patient calendar)
  function initAdminAvailableSlotsMenu() {
    const availableBtn = document.querySelector('#availableSlotsBtn');
    const availableMenu = document.querySelector('#availableSlotsMenu');
    const availableMenuContent = document.querySelector('#availableSlotsMenuContent');
    
    if (!availableBtn || !availableMenu || !availableMenuContent) {
      console.log('[admin-calendar] Available slots menu elements not found');
      return;
    }
    
    // Hide all menus function
    function hideAllMenus() {
      if (availableMenu) availableMenu.classList.add('hidden');
    }
    
    // Available slots preload function
    async function preloadAdminAvailableSlots(requestedGranularity) {
      // Ensure these variables exist in the function scope to avoid ReferenceError in any path
      let allSlots = [];
      let availableSlots = [];

      try {
        if (!availableMenuContent) return;
        console.log('[admin-calendar] preloadAdminAvailableSlots start');
        availableMenuContent.textContent = 'Loading...';
        
        // Get form values from admin form
        const branchEl = document.querySelector(ADMIN_SELECTORS.branchSelect);
        const dateEl = document.querySelector(ADMIN_SELECTORS.dateInput);
        const serviceEl = document.querySelector(ADMIN_SELECTORS.serviceSelect) || document.getElementById('service_id');
        const dentistEl = document.querySelector(ADMIN_SELECTORS.dentistSelect);
        
        console.log('[admin-calendar] Form elements found:', {
          branchEl: !!branchEl,
          dateEl: !!dateEl, 
          serviceEl: !!serviceEl,
          dentistEl: !!dentistEl
        });
        
        let branch = branchEl ? branchEl.value : '';
        let date = dateEl ? dateEl.value : '';
        let serviceId = serviceEl ? serviceEl.value : '';
        let dentist = dentistEl ? dentistEl.value : '';
        
        console.log('[admin-calendar] Form values:', {
          branch,
          date,
          serviceId,
          dentist
        });
        
        if (!date || !branch || !serviceId) {
          // Try to fall back to sensible defaults so admins/staff can still inspect slots
          console.log('[admin-calendar] Missing required fields, attempting defaults', { date, branch, serviceId });
          if (!date) {
            // try to use today's date or date input default
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth()+1).padStart(2,'0');
            const dd = String(today.getDate()).padStart(2,'0');
            date = `${yyyy}-${mm}-${dd}`;
          }
          if (!branch) {
            // Try to get from branch select element
            const branchEl = document.querySelector(ADMIN_SELECTORS.branchSelect) || document.querySelector('select[name="branch_id"]');
            if (branchEl && branchEl.options.length > 0) {
              // Skip the first option if it's a placeholder (empty value or "Select...")
              for (let i = 0; i < branchEl.options.length; i++) {
                const option = branchEl.options[i];
                if (option.value && option.value !== '' && !option.text.toLowerCase().includes('select')) {
                  branch = option.value;
                  break;
                }
              }
            }
            // If still no branch, try a default value
            if (!branch) {
              branch = '1'; // fallback to branch ID 1
            }
          }
          if (!serviceId) {
            // Try to get from service select element
            const serviceEl = document.querySelector(ADMIN_SELECTORS.serviceSelect) || document.getElementById('service_id');
            if (serviceEl && serviceEl.options.length > 0) {
              // Skip the first option if it's a placeholder (empty value or "Select...")
              for (let i = 0; i < serviceEl.options.length; i++) {
                const option = serviceEl.options[i];
                if (option.value && option.value !== '' && !option.text.toLowerCase().includes('select')) {
                  serviceId = option.value;
                  break;
                }
              }
            }
            // If still no service, try a default value
            if (!serviceId) {
              serviceId = '1'; // fallback to service ID 1
            }
          }
        }
        // If still missing service/branch, warn but proceed and let server decide
        if (!date || !branch || !serviceId) {
          console.warn('[admin-calendar] Still missing fields after defaults, proceeding with available values', { date, branch, serviceId });
          availableMenuContent.innerHTML = `
            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded">
              <p class="text-yellow-800 text-sm font-medium">Using Available Defaults:</p>
              <ul class="text-yellow-700 text-xs mt-1">
                <li>Date: ${date || 'Not set'}</li>
                <li>Branch: ${branch || 'Not set'}</li>
                <li>Service: ${serviceId || 'Not set'}</li>
              </ul>
              <p class="text-yellow-600 text-xs mt-2">If no slots appear, please select branch and service in the calendar form above.</p>
            </div>
          `;
        } else {
          availableMenuContent.innerHTML = `
            <div class="p-4 bg-blue-50 border border-blue-200 rounded">
              <p class="text-blue-800 text-sm font-medium">Loading slots for:</p>
              <ul class="text-blue-700 text-xs mt-1">
                <li>Date: ${date}</li>
                <li>Branch: ${branch}</li>
                <li>Service: ${serviceId}</li>
              </ul>
              <p class="text-blue-600 text-xs mt-2">Fetching available appointment slots...</p>
            </div>
          `;
        }
        
        const payload = {
          branch_id: branch,
          date: date,
          service_id: serviceId,
          granularity: requestedGranularity || 30  // allow override
        };
        
        if (dentist) {
          payload.dentist_id = dentist;
        }
        
        const url = `${baseUrl}appointments/available-slots`;
        console.log('[admin-calendar] Making request to:', url);
        console.log('[admin-calendar] Payload:', payload);
        
        const res = await fetch(url, {
          method: 'POST',
          body: new URLSearchParams(payload),
          headers: { 
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          credentials: 'include'
        });
        
        console.log('[admin-calendar] Response status:', res.status);
        console.log('[admin-calendar] Response ok:', res.ok);
        
        // Handle HTTP error status codes
        if (!res.ok) {
          if (res.status === 401) {
            availableMenuContent.innerHTML = `
              <div style="padding: 12px; text-align: center; color: #dc2626;">
                <p>⚠️ Authentication Required</p>
                <small>Please log in as admin to view available slots</small>
              </div>
            `;
            console.error('[admin-calendar] 401 Unauthorized - user not logged in as admin');
            return;
          } else if (res.status === 403) {
            availableMenuContent.innerHTML = `
              <div style="padding: 12px; text-align: center; color: #dc2626;">
                <p>⚠️ Access Denied</p>
                <small>Admin privileges required</small>
              </div>
            `;
            return;
          } else {
            availableMenuContent.innerHTML = `
              <div style="padding: 12px; text-align: center; color: #dc2626;">
                <p>⚠️ Server Error (${res.status})</p>
                <small>Please try again later</small>
              </div>
            `;
            return;
          }
        }
        
        const responseText = await res.text();
        console.log('[admin-calendar] Raw response:', responseText);
        
        let responseData;
        try {
          responseData = JSON.parse(responseText);
          console.log('[admin-calendar] Parsed response:', responseData);
        } catch (parseError) {
          console.error('[admin-calendar] JSON parse error:', parseError);
          availableMenuContent.textContent = 'Error: Invalid response format';
          return;
        }
        
        if (responseData && responseData.success) {
          // populate function-scoped arrays (do not redeclare)
          allSlots = responseData.all_slots || responseData.slots || responseData.available_slots || [];
          
          console.log('[admin-calendar] All slots found:', allSlots);
          
          if (!Array.isArray(allSlots)) {
            allSlots = [];
          }
          
          // Filter to show only available slots with 30-minute intervals
          availableSlots = []; // Reset the function-scoped variable
          if (allSlots.length > 0) {
            // Check if slots have availability property
            if (typeof allSlots[0] === 'object' && allSlots[0].hasOwnProperty('available')) {
              // Filter available slots and space them out every 30 minutes
              const filteredSlots = allSlots.filter(slot => slot.available === true);
              
              // Group by 30-minute intervals for better UX
              const slotMap = new Map();
              filteredSlots.forEach(slot => {
                const timeStr = slot.time || slot.start_time || String(slot);
                const time24 = normalizeTime(timeStr);
                if (time24) {
                  const [hours, minutes] = time24.split(':').map(Number);
                  // Round to nearest 30-minute interval
                  const roundedMinutes = Math.floor(minutes / 30) * 30;
                  const roundedTime = `${hours.toString().padStart(2, '0')}:${roundedMinutes.toString().padStart(2, '0')}`;
                  
                  if (!slotMap.has(roundedTime)) {
                    // Calculate end time for display
                    const endTime = calculateAppointmentEndTime(time24, slot.duration_minutes || 180, slot.grace_minutes || 20);
                    slotMap.set(roundedTime, {
                      startTime: roundedTime,
                      endTime: endTime,
                      displayText: `${format12Hour(roundedTime)} - ${format12Hour(endTime)}`
                    });
                  }
                }
              });
              
              availableSlots = Array.from(slotMap.values());
            } else {
              // Fallback for simple string array
              availableSlots = allSlots.slice(0, 10); // Limit to first 10 for better UX
            }
          }
          
          console.log('[admin-calendar] Available slots after filtering:', availableSlots);
          
          // If no available slots, continue and show unavailable slots as well so admin/staff can inspect
          if (availableSlots.length === 0) {
            console.log('[admin-calendar] No available slots found; will show unavailable slots for admin inspection');
          }
          
          // Clear and rebuild menu content with richer admin UI
          availableMenuContent.innerHTML = '';

          // Controls: search, interval selector, show all toggle, refresh
          const controls = document.createElement('div');
          controls.style.display = 'flex';
          controls.style.gap = '8px';
          controls.style.alignItems = 'center';
          controls.style.marginBottom = '8px';

          const search = document.createElement('input');
          search.type = 'search';
          search.placeholder = 'Search time (e.g. 2:00)';
          search.style.flex = '1';
          search.className = 'px-2 py-1 border rounded';

          const intervalSelect = document.createElement('select');
          intervalSelect.className = 'px-2 py-1 border rounded';
          [15,30,60].forEach(i => {
            const o = document.createElement('option'); o.value = String(i); o.textContent = i + 'm';
            if (i === (responseData.metadata?.requested_granularity || 30)) o.selected = true;
            intervalSelect.appendChild(o);
          });

          const filterSelect = document.createElement('select');
          filterSelect.className = 'px-2 py-1 border rounded';
          ['available','unavailable','all'].forEach(k => {
            const o = document.createElement('option'); o.value = k; o.textContent = k.charAt(0).toUpperCase() + k.slice(1);
            if (k === 'available') o.selected = true;
            filterSelect.appendChild(o);
          });

          const refreshBtn = document.createElement('button');
          refreshBtn.type = 'button';
          refreshBtn.textContent = 'Refresh';
          refreshBtn.className = 'px-2 py-1 border rounded bg-gray-100';

          controls.appendChild(search);
          controls.appendChild(intervalSelect);
          controls.appendChild(filterSelect);
          controls.appendChild(refreshBtn);

          // Legend
          const legend = document.createElement('div');
          legend.style.display = 'flex';
          legend.style.gap = '8px';
          legend.style.alignItems = 'center';
          legend.style.marginBottom = '6px';

          function legendItem(color, text) {
            const it = document.createElement('div');
            it.style.display = 'inline-flex';
            it.style.alignItems = 'center';
            it.style.gap = '6px';
            const sw = document.createElement('span');
            sw.style.width = '14px';
            sw.style.height = '14px';
            sw.style.borderRadius = '3px';
            sw.style.background = color;
            it.appendChild(sw);
            const lbl = document.createElement('small'); lbl.textContent = text; it.appendChild(lbl);
            return it;
          }

          legend.appendChild(legendItem('#bbf7d0','Available'));
          // removed patient-facing "Your appointment" label for admin dashboard
          legend.appendChild(legendItem('#fff7f7','Blocked / Unavailable'));

          availableMenuContent.appendChild(legend);
          availableMenuContent.appendChild(controls);

          // Slots grid container (will contain grouped sections)
          const slotsContainer = document.createElement('div');
          slotsContainer.style.display = 'block';
          slotsContainer.style.gap = '8px';
          slotsContainer.style.maxHeight = '360px';
          slotsContainer.style.overflowY = 'auto';
          slotsContainer.style.paddingBottom = '8px';

          // Timeline container for occupied blocks
          const timelineContainer = document.createElement('div');
          timelineContainer.style.marginTop = '8px';
          timelineContainer.style.padding = '8px';
          timelineContainer.style.borderTop = '1px solid #eee';
          timelineContainer.style.fontSize = '12px';
          timelineContainer.style.color = '#444';

          const timelineTitle = document.createElement('div');
          timelineTitle.textContent = 'Timeline (occupied blocks)';
          timelineTitle.style.fontWeight = '600';
          timelineTitle.style.marginBottom = '6px';
          timelineContainer.appendChild(timelineTitle);

          const timelineBar = document.createElement('div');
          timelineBar.style.position = 'relative';
          timelineBar.style.height = '36px';
          timelineBar.style.background = '#f8fafc';
          timelineBar.style.border = '1px solid #e5e7eb';
          timelineBar.style.borderRadius = '6px';
          timelineBar.style.overflow = 'hidden';
          timelineBar.style.marginBottom = '6px';
          timelineContainer.appendChild(timelineBar);

          // Helper: create group section
          function createGroupSection(titleText) {
            const section = document.createElement('div');
            section.style.marginBottom = '8px';
            const header = document.createElement('div');
            header.style.display = 'flex';
            header.style.justifyContent = 'space-between';
            header.style.alignItems = 'center';
            header.style.cursor = 'pointer';
            header.style.padding = '6px 0';
            const h = document.createElement('div'); h.textContent = titleText; h.style.fontWeight = '600';
            const toggle = document.createElement('button'); toggle.type = 'button'; toggle.textContent = 'Collapse'; toggle.className = 'px-2 py-1 border rounded text-sm';
            const body = document.createElement('div'); body.style.display = 'block'; body.style.marginTop = '6px';
            header.appendChild(h); header.appendChild(toggle);
            section.appendChild(header); section.appendChild(body);
            toggle.addEventListener('click', () => {
              if (body.style.display === 'none') { body.style.display = 'block'; toggle.textContent = 'Collapse'; }
              else { body.style.display = 'none'; toggle.textContent = 'Expand'; }
            });
            return { section, body };
          }

          // Helper to render slots list (accepts array of slot objects)
          function renderSlots(list) {
            slotsContainer.innerHTML = '';
            timelineBar.innerHTML = '';
            if (!list || list.length === 0) {
              const empty = document.createElement('div');
              empty.style.padding = '12px';
              empty.style.color = '#666';
              empty.style.textAlign = 'center';
              empty.textContent = 'No slots to display';
              slotsContainer.appendChild(empty);
              return;
            }

            // Group into Morning (00:00-11:59), Afternoon (12:00-16:59), Evening (17:00-23:59)
            const groups = { morning: [], afternoon: [], evening: [] };
            list.forEach(slot => {
              const raw = (typeof slot === 'string') ? slot : (slot.startTime || slot.time || '');
              const t24 = normalizeTime(raw);
              if (!t24) { groups.morning.push(slot); return; }
              const h = parseInt(t24.split(':')[0], 10);
              if (h < 12) groups.morning.push(slot);
              else if (h < 17) groups.afternoon.push(slot);
              else groups.evening.push(slot);
            });

            const morningSection = createGroupSection('Morning');
            const afternoonSection = createGroupSection('Afternoon');
            const eveningSection = createGroupSection('Evening');

            // helper to render cards into a container
            function appendCards(container, arr) {
              const grid = document.createElement('div');
              grid.style.display = 'grid';
              grid.style.gridTemplateColumns = 'repeat(2,1fr)';
              grid.style.gap = '8px';
              arr.forEach(slot => {
                const start = (typeof slot === 'string') ? slot : (slot.startTime || slot.time || '');
                const end = (typeof slot === 'object') ? (slot.endTime || slot.end || '') : '';
                const display = (typeof slot === 'object' && slot.displayText) ? slot.displayText : (format12Hour(start) + (end ? (' - ' + format12Hour(end)) : ''));

                const card = document.createElement('div');
                card.className = 'p-2 border rounded bg-white';
                card.style.display = 'flex';
                card.style.flexDirection = 'column';

                if (slot && typeof slot === 'object' && slot.available === false) {
                  card.style.opacity = '0.55';
                  card.style.backgroundColor = '#fff7f7';
                  card.style.borderColor = '#fca5a5';
                } else if (slot && typeof slot === 'object' && slot.owned_by_current_user) {
                  card.style.backgroundColor = '#fff7e6';
                  card.style.borderColor = '#ffd580';
                } else {
                  card.style.backgroundColor = '#f0fdf4';
                  card.style.borderColor = '#86efac';
                }

                const title = document.createElement('div'); title.style.fontWeight = '600'; title.style.marginBottom = '6px'; title.textContent = display;
                const meta = document.createElement('div'); meta.style.fontSize = '12px'; meta.style.color = '#666'; meta.style.marginBottom = '6px'; meta.textContent = `Start: ${format12Hour(start)}` + (end ? ` • End: ${format12Hour(end)}` : '');
                if (slot && typeof slot === 'object' && slot.available === false) {
                  const reason = document.createElement('div'); reason.style.fontSize = '12px'; reason.style.color = '#a00000'; reason.textContent = (slot.blocking_info && slot.blocking_info.type === 'appointment') ? `Blocked by appointment #${slot.blocking_info.appointment_id || 'N/A'}` : 'Blocked/unavailable'; meta.appendChild(document.createElement('br')); meta.appendChild(reason);
                }

                const actions = document.createElement('div'); actions.style.display = 'flex'; actions.style.gap = '6px';
                const selectBtn = document.createElement('button'); selectBtn.type='button'; selectBtn.className='px-2 py-1 bg-purple-600 text-white rounded text-sm'; selectBtn.textContent='Select';
                selectBtn.addEventListener('click', ()=>{ const timeInput = document.querySelector(ADMIN_SELECTORS.timeSelect); if (timeInput) { if (timeInput.tagName && timeInput.tagName.toLowerCase() === 'select') { let opt = timeInput.querySelector(`option[value="${start}"]`); if (!opt) { opt = document.createElement('option'); opt.value = start; opt.textContent = start; timeInput.appendChild(opt); } timeInput.value = start; } else { timeInput.value = start; } timeInput.dispatchEvent(new Event('change',{bubbles:true})); } hideAllMenus(); });
                const copyBtn = document.createElement('button'); copyBtn.type='button'; copyBtn.className='px-2 py-1 border rounded text-sm'; copyBtn.textContent='Copy'; copyBtn.addEventListener('click', async ()=>{ try { await navigator.clipboard.writeText(start); copyBtn.textContent='Copied'; setTimeout(()=>copyBtn.textContent='Copy',1200); } catch(e){ console.error('Clipboard failed',e); } });
                actions.appendChild(selectBtn); actions.appendChild(copyBtn);

                card.appendChild(title); card.appendChild(meta); card.appendChild(actions);
                grid.appendChild(card);
              });
              container.appendChild(grid);
            }

            appendCards(morningSection.body, groups.morning);
            appendCards(afternoonSection.body, groups.afternoon);
            appendCards(eveningSection.body, groups.evening);

            slotsContainer.appendChild(morningSection.section);
            slotsContainer.appendChild(afternoonSection.section);
            slotsContainer.appendChild(eveningSection.section);

            // Build timeline occupied blocks from slots that are unavailable or have blocking_info
            // Timeline scale based on metadata day_start/day_end if provided
            const md = responseData.metadata || {};
            const dayStart = md.day_start ? normalizeTime(md.day_start) : '08:00';
            const dayEnd = md.day_end ? normalizeTime(md.day_end) : '20:00';
            const startMinutes = dayStart.split(':').map(Number); const endMinutes = dayEnd.split(':').map(Number);
            const startTotal = startMinutes[0]*60 + startMinutes[1]; const endTotal = endMinutes[0]*60 + endMinutes[1];
            const totalSpan = Math.max(1, endTotal - startTotal);

            // For each blocked slot, draw a band
            (list.filter(s => s && typeof s === 'object' && s.available === false)).forEach(bs => {
              // blocking_info: try to get start/end
              let bStart = bs.blocking_info?.start || bs.time || bs.startTime;
              let bEnd = bs.blocking_info?.end || bs.ends_at || bs.endTime;
              const t1 = normalizeTime(bStart) || normalizeTime(bs.time) || null;
              const t2 = normalizeTime(bEnd) || null;
              if (!t1) return;
              const t1Parts = t1.split(':').map(Number); const t1Min = t1Parts[0]*60 + t1Parts[1];
              const t2Min = t2 ? (t2.split(':').map(Number)[0]*60 + t2.split(':').map(Number)[1]) : (t1Min + (bs.duration_minutes || 60));
              const leftPercent = Math.max(0, ((t1Min - startTotal) / totalSpan) * 100);
              const widthPercent = Math.max(1, ((t2Min - t1Min) / totalSpan) * 100);
              const band = document.createElement('div');
              band.style.position = 'absolute';
              band.style.left = leftPercent + '%';
              band.style.width = widthPercent + '%';
              band.style.top = '4px';
              band.style.bottom = '4px';
              band.style.background = 'rgba(248, 113, 113, 0.7)';
              band.style.borderRadius = '4px';
              band.title = `Blocked ${bs.time || ''} ${bs.blocking_info?.type ? '('+bs.blocking_info.type+')' : ''}`;
              timelineBar.appendChild(band);
            });

            // Add time labels
            const labels = document.createElement('div'); labels.style.display='flex'; labels.style.justifyContent='space-between'; labels.style.fontSize='11px'; labels.style.color='#666'; labels.style.marginTop='4px'; labels.innerHTML = `<div>${format12Hour(dayStart)}</div><div>${format12Hour(dayEnd)}</div>`;
            timelineContainer.appendChild(labels);

            availableMenuContent.appendChild(timelineContainer);
          }

          availableMenuContent.appendChild(slotsContainer);

          // Initial render: by default show filtered available slots, fallback to availableSlots
          renderSlots(availableSlots);

          // Wiring controls
          search.addEventListener('input', () => {
            const q = search.value.trim().toLowerCase();
            const filter = filterSelect.value; // 'available' | 'unavailable' | 'all'
            let source = [];
            if (filter === 'all') source = allSlots;
            else if (filter === 'available') source = availableSlots;
            else source = allSlots.filter(s => s.available === false);

            if (!q) {
              renderSlots(source);
              return;
            }
            const filtered = source.filter(s => {
              const times = (typeof s === 'string') ? s : (s.startTime || s.time || '');
              return String(times).toLowerCase().includes(q) || ((s.displayText||'').toLowerCase().includes(q));
            });
            renderSlots(filtered);
          });

          intervalSelect.addEventListener('change', async () => {
            const g = parseInt(intervalSelect.value, 10) || 30;
            // Re-fetch with new granularity
            await preloadAdminAvailableSlots(g);
          });

          refreshBtn.addEventListener('click', async () => {
            const g = parseInt(intervalSelect.value, 10) || 30;
            await preloadAdminAvailableSlots(g);
          });

          filterSelect.addEventListener('change', () => {
            // Re-render based on selected filter
            const filter = filterSelect.value;
            if (filter === 'all') renderSlots(allSlots);
            else if (filter === 'available') renderSlots(availableSlots);
            else renderSlots(allSlots.filter(s => s.available === false));
          });

          // Show metadata if available (below controls)
          if (responseData.metadata) {
            const metaDiv = document.createElement('div');
            metaDiv.style.fontSize = '12px';
            metaDiv.style.color = '#666';
            metaDiv.style.padding = '8px 0 0 0';
            metaDiv.style.borderTop = '1px solid #eee';

            const duration = responseData.metadata.duration_minutes || 180;
            const grace = responseData.metadata.grace_minutes || 20;
            const total = duration + grace;

            metaDiv.innerHTML = `
              <div>Showing ${availableSlots.length} available slots</div>
              <div>Service: ${duration} min + Grace: ${grace} min = ${total} min total</div>
              <div>Checked ${responseData.metadata.total_slots_checked || 0} possible times</div>
            `;
            availableMenuContent.appendChild(metaDiv);
          }
        } else {
          console.error('[admin-calendar] API error:', responseData);
          availableMenuContent.textContent = responseData?.message || 'Error loading slots';
        }
        console.log('[admin-calendar] preloadAdminAvailableSlots done');
      } catch (err) {
        console.error('[admin-calendar] preloadAdminAvailableSlots error', err);
        
        // More descriptive error handling
        if (err.name === 'TypeError' && err.message.includes('fetch')) {
          availableMenuContent.innerHTML = `
            <div style="padding: 12px; text-align: center; color: #dc2626;">
              <p>⚠️ Network Error</p>
              <small>Cannot connect to server. Please check your connection.</small>
            </div>
          `;
        } else if (err.name === 'AbortError') {
          availableMenuContent.innerHTML = `
            <div style="padding: 12px; text-align: center; color: #dc2626;">
              <p>⚠️ Request Timeout</p>
              <small>Server took too long to respond. Please try again.</small>
            </div>
          `;
        } else {
          availableMenuContent.innerHTML = `
            <div style="padding: 12px; text-align: center; color: #dc2626;">
              <p>⚠️ Unexpected Error</p>
              <small>${err.message || 'Please try again or contact support.'}</small>
            </div>
          `;
        }
      }
    }
    
    // Button click handler
    availableBtn.addEventListener('click', async () => {
      hideAllMenus();
      
      // If menu is currently hidden, we're about to show it, so load the slots
      if (availableMenu.classList.contains('hidden')) {
        await preloadAdminAvailableSlots();
      }
      
      availableMenu.classList.toggle('hidden');
    });
    
    // Click outside to close
    document.addEventListener('click', (e) => {
      if (!availableBtn.contains(e.target) && !availableMenu.contains(e.target)) {
        hideAllMenus();
      }
    });
    
    console.log('[admin-calendar] Available slots menu initialized');
  }
  
  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminAvailableSlotsMenu);
  } else {
    initAdminAvailableSlotsMenu();
  }
  
  // Debug: Log initialization
  console.log('[admin-calendar] Module loaded, baseUrl:', baseUrl);
  
})();