// calendar-admin.js - Dedicated admin calendar logic 
// Separate from patient calendar to avoid function conflicts
(function(){
  'use strict';
  
  const baseUrl = window.baseUrl || '';

  // If server templates provide showAdminNotification, reuse it. Otherwise provide a fallback.
  function notifyAdmin(message, type='info', duration=6000) {
    if (typeof window.showAdminNotification === 'function') return window.showAdminNotification(message, type, duration);
    try { console.log('ADMIN_NOTIFY', type, message); } catch(e){}
    if (type === 'error') { /* non-blocking */ }
    return null;
  }
  
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

  // Safe toString wrapper to avoid calling toString on undefined/null
  function safeToString(v) {
    try {
      if (v === null || v === undefined) return '';
      if (typeof v.toString === 'function') return v.toString();
      return String(v);
    } catch (e) {
      return '';
    }
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
    
  if (window.__psm_debug) console.debug('[admin-calendar] Final body string:', safeToString(body));
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
      body: safeToString(body),
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

    // Defensive safeguard: if the page date is missing or older than today,
    // override it with today's date so admins see current availability.
    try {
      const todayStr = new Date().toISOString().substring(0,10);
      if (!date || (typeof date === 'string' && date < todayStr)) {
        if (window.__psm_debug) console.info('[admin-calendar] Overriding stale/missing date in fetchAdminAvailableSlots with today:', todayStr, 'previous:', date);
        if (dateEl) dateEl.value = todayStr;
      }
    } catch (e) { if (window.__psm_debug) console.warn('[admin-calendar] Date override failed in fetchAdminAvailableSlots', e); }
    
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
        granularity: 5
      };
      // If procedure duration is exposed on the select option, prefer sending it so server can consider it
      try {
        const sel = document.querySelector(ADMIN_SELECTORS.serviceSelect) || document.getElementById('service_id');
        if (sel && sel.options && sel.selectedIndex >= 0) {
          const opt = sel.options[sel.selectedIndex];
          const ddMax = opt ? opt.getAttribute('data-duration-max') : null;
          const dd = opt ? opt.getAttribute('data-duration') : null;
          const candidate = ddMax ? Number(ddMax) : (dd ? Number(dd) : null);
          if (Number.isFinite(candidate) && candidate > 0) payload.duration = candidate;
        }
      } catch (e) { if (window.__psm_debug) console.warn('[admin-calendar] service duration read failed', e); }
      
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
  notifyAdmin('Error loading available times. Please try again.', 'error');
    }
  }
  
  // Populate time select with available + unavailable slots; mark availability and ensure prefill uses an actually available slot
  function populateAdminTimeSlots(response, timeElement) {
    // Use the complete slots list when available so we can mark unavailable times
    const allSlots = response.all_slots || response.slots || [];
    // Fallback to available_slots if all_slots missing
    const availableSlots = response.available_slots || [];

    if (window.__psm_debug) console.log('[admin-calendar] Populating slots (allSlots, availableSlots):', allSlots, availableSlots);

    // Clear existing options
    timeElement.innerHTML = '<option value="">Select Time</option>';

    if (!Array.isArray(allSlots) || allSlots.length === 0) {
      // If no complete list provided, fall back to available only
      if (!Array.isArray(availableSlots) || availableSlots.length === 0) {
        timeElement.innerHTML = '<option value="">No available slots</option>';
        return;
      }
    }

    // Helper to extract normalized time and timestamp from slot object/string
    function slotInfo(slot) {
      let timeValue = '';
      let displayTime = '';
      let timestamp = null;
      let available = false;

      if (typeof slot === 'string') {
        displayTime = slot;
        timeValue = normalizeTime(slot);
      } else {
        // slot object
        if (slot.datetime) {
          const parts = slot.datetime.split(' ');
          if (parts.length >= 2) timeValue = parts[1].slice(0,5);
          displayTime = slot.time || timeValue;
          if (slot.timestamp) timestamp = Number(slot.timestamp);
          else timestamp = Date.parse((slot.datetime || '').replace(' ', 'T'))/1000 || null;
        } else if (slot.time) {
          displayTime = slot.time;
          timeValue = normalizeTime(slot.time);
        } else {
          displayTime = String(slot);
          timeValue = normalizeTime(displayTime) || displayTime;
        }
        available = !!slot.available;
      }

      // If timestamp missing but we can derive from time and current date in metadata, attempt a best-effort
      if (timestamp === null && response.metadata && response.metadata.day_start && response.metadata.day_end && response.metadata.duration_minutes) {
        // best-effort: use the response day (not perfect, but gives comparable values)
        try {
          const day = (response.metadata && response.metadata.first_available && response.metadata.first_available.datetime) ? response.metadata.first_available.datetime.split(' ')[0] : null;
          if (day && timeValue) timestamp = Date.parse(day + 'T' + timeValue + ':00')/1000;
        } catch (e) { timestamp = null; }
      }

      return { timeValue, displayTime, timestamp, available };
    }

    // Build options from allSlots when possible so users can see blocked times. Otherwise fall back to availableSlots
    const renderSlots = (Array.isArray(allSlots) && allSlots.length) ? allSlots : availableSlots;

    // Keep a map of timestamps -> option values for nearest-available lookup
    const availableIndex = [];

    renderSlots.forEach(slot => {
      const info = slotInfo(slot);
      const option = document.createElement('option');
      option.value = info.timeValue || '';
      option.dataset.available = info.available ? '1' : '0';
      // Annotate timestamp for nearest searches
      if (info.timestamp) option.dataset.timestamp = String(info.timestamp);

      // Show only the slot start time. Do not append end times because service durations
      // are dynamic and can vary; showing end times may mislead users.
      let label = info.displayTime || info.timeValue || '';
      if (slot && typeof slot === 'object' && info.displayTime && info.displayTime !== info.timeValue) {
        // prefer a human-friendly displayTime when provided, but still avoid adding end times
        label = info.displayTime;
      }

      // Mark unavailable visually and disable selection
      if (!info.available) {
        option.disabled = true;
        label += ' (Unavailable)';
      } else {
        // record available entries for nearest lookup
        availableIndex.push({ timestamp: info.timestamp || null, value: info.timeValue });
      }

      option.textContent = label;
      timeElement.appendChild(option);
    });

    // Now determine preselection: use metadata.first_available if it's truly available; otherwise pick nearest available
    let preselectTimestamp = null;
    if (response.metadata && response.metadata.first_available) {
      const fa = response.metadata.first_available;
      if (fa.timestamp) preselectTimestamp = Number(fa.timestamp);
      else if (fa.datetime) preselectTimestamp = Date.parse(fa.datetime.replace(' ', 'T'))/1000;
      else if (fa.time && response.metadata && response.metadata.first_available && response.metadata.first_available.datetime) {
        // combine date from datetime and time
        try { preselectTimestamp = Date.parse(response.metadata.first_available.datetime.replace(' ', 'T'))/1000; } catch(e) { preselectTimestamp = null; }
      }
    }

    // Helper to find nearest available by timestamp
    function findNearestAvailable(ts) {
      if (!ts) return null;
      let best = null;
      let bestDiff = Infinity;
      availableIndex.forEach(item => {
        if (!item.timestamp) return; // skip if we couldn't derive timestamp
        const diff = Math.abs(item.timestamp - ts);
        if (diff < bestDiff) { bestDiff = diff; best = item; }
      });
      return best;
    }

    // Attempt preselection
    if (preselectTimestamp) {
      // Try to find an exact available option with matching timestamp
      let chosen = null;
      // Check options for one with data-timestamp == preselectTimestamp and not disabled
      for (let i=0;i<timeElement.options.length;i++) {
        const opt = timeElement.options[i];
        if (opt.dataset && opt.dataset.timestamp && Number(opt.dataset.timestamp) === preselectTimestamp && opt.disabled === false) {
          chosen = opt.value; break;
        }
      }

      if (!chosen) {
        // No exact available match, find nearest available
        const nearest = findNearestAvailable(preselectTimestamp);
        if (nearest && nearest.value) chosen = nearest.value;
      }

      if (chosen) {
        timeElement.value = chosen;
        if (window.__psm_debug) console.log('[admin-calendar] Pre-selected nearest available time:', chosen);
      }
    } else {
      // If no metadata provided, still prefer the first non-disabled option
      for (let i=0;i<timeElement.options.length;i++) {
        const opt = timeElement.options[i];
        if (!opt.disabled && opt.value) { timeElement.value = opt.value; break; }
      }
    }

    showAdminMessage(`${availableSlots.length} available time slots loaded`, 'success');
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
    
    // Add listener for availability changes to auto-refresh TimeTable and slots
    window.addEventListener('availability:changed', function(e) {
      if (window.__psm_debug) console.log('[admin-calendar] Availability changed event received', e && e.detail ? e.detail : null);

      // Clear the entire available slots cache to avoid stale data for any branch/date
      try {
        if (window.__available_slots_cache && typeof window.__available_slots_cache === 'object') {
          Object.keys(window.__available_slots_cache).forEach(k => delete window.__available_slots_cache[k]);
        }
      } catch (ex) {
        if (window.__psm_debug) console.warn('[admin-calendar] Failed to clear available slots cache', ex);
      }

      // If the event contains a target date/branch, try to pre-select them for the UI refresh
      try {
        if (e && e.detail) {
          const d = e.detail.date || e.detail.appointment_date || e.detail.appointmentDate || null;
          const b = e.detail.branch_id || e.detail.branchId || null;
          if (d) {
            const dateEl = document.querySelector(ADMIN_SELECTORS.dateInput) || document.querySelector('input[name="date"]');
            if (dateEl) dateEl.value = d;
          }
          if (b) {
            const branchEl = document.querySelector(ADMIN_SELECTORS.branchSelect) || document.querySelector('select[name="branch"]');
            if (branchEl) branchEl.value = b;
          }
        }
      } catch (ex) { if (window.__psm_debug) console.warn('[admin-calendar] Failed to apply event-supplied date/branch', ex); }

      // Refresh admin time slots (reads the form inputs so it will reflect the branch/date now)
      try {
        fetchAdminAvailableSlots();
      } catch (ex) {
        console.warn('[admin-calendar] Failed to refresh admin slots after availability change', ex);
      }

      // Refresh TimeTable modal if it's open
      try {
        if (timeTableModalInstance && timeTableModalInstance.isOpen) {
          timeTableModalInstance.refreshData();
        }
      } catch (ex) {
        console.warn('[admin-calendar] Failed to refresh TimeTable after availability change', ex);
      }
    });
    
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
    
    // Setup form submission handler (use capture phase so we run before other listeners)
    const form = document.getElementById('appointmentForm');
    if (form) {
      console.log('[admin-calendar] Setting up form submission handler (capture phase)');
      // Ensure hidden inputs are populated early even if other scripts intercept submit
      form.addEventListener('submit', function(e){
        try { populateAppointmentHiddenInputs(form); } catch(ex) { console.warn('[admin-calendar] populate hidden inputs failed', ex); }
      }, true);

      // Also populate hidden inputs on submit button clicks (covers some AJAX flows)
      Array.from(form.querySelectorAll('button[type="submit"], input[type="submit"]')).forEach(btn => {
        btn.addEventListener('click', function(evt){
          try { populateAppointmentHiddenInputs(form); } catch(ex) { console.warn('[admin-calendar] populate hidden inputs on click failed', ex); }
        });
      });

      // Attach the legacy submit handling as well (non-capture) for validation/behavior
      form.addEventListener('submit', handleAdminFormSubmit);

      // Add success detection for appointment creation
      setupAppointmentCreationListener(form);
    } else {
      console.warn('[admin-calendar] appointmentForm not found');
    }
    
    console.log('[admin-calendar] Admin calendar initialization complete');
  }
  
  // Setup listener to detect successful appointment creation and refresh UI
  function setupAppointmentCreationListener(form) {
    // Monitor for successful appointment creation responses. Wrap fetch and also support Request objects.
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
      const promise = originalFetch.apply(this, args);

      // derive URL string from args[0]
      try {
        let urlStr = null;
        if (!args || !args[0]) urlStr = '';
        else if (typeof args[0] === 'string') urlStr = args[0];
        else if (args[0] instanceof Request && args[0].url) urlStr = args[0].url;
        else if (args[0] && typeof args[0].toString === 'function') urlStr = String(args[0]);

        if (urlStr && urlStr.indexOf('/appointments/create') !== -1) {
          // Attempt to snapshot the outgoing request body for debugging purposes
          try {
            const reqInit = args[1] || {};
            const reqBody = reqInit.body;
            if (reqBody instanceof URLSearchParams) {
              console.debug('[admin-calendar][DEBUG] Outgoing /appointments/create URLSearchParams body:', reqBody.toString());
            } else if (typeof FormData !== 'undefined' && reqBody instanceof FormData) {
              const obj = {};
              for (const pair of reqBody.entries()) obj[pair[0]] = pair[1];
              console.debug('[admin-calendar][DEBUG] Outgoing /appointments/create FormData body:', obj);
            } else if (typeof reqBody === 'string') {
              console.debug('[admin-calendar][DEBUG] Outgoing /appointments/create raw body string:', reqBody);
            } else if (reqBody && typeof reqBody === 'object') {
              console.debug('[admin-calendar][DEBUG] Outgoing /appointments/create body (object):', reqBody);
            } else {
              console.debug('[admin-calendar][DEBUG] Outgoing /appointments/create body not captured (possibly Request object or stream)');
            }
          } catch (dbgErr) { console.warn('[admin-calendar][DEBUG] Failed to snapshot outgoing create payload', dbgErr); }

          promise.then(response => {
            if (response && response.ok) {
              // Try parse JSON, if available
              response.clone().text().then(txt => {
                try {
                  const data = JSON.parse(txt);
                  if (data && data.success) {
                    console.log('[admin-calendar] Appointment created successfully, refreshing availability');
                    // Try to include branch and date context so the UI refreshes the correct TimeTable
                    const form = document.getElementById('appointmentForm');
                    const dateVal = (data.appointment_date || data.appointment_datetime) ? (data.appointment_date || (data.appointment_datetime ? data.appointment_datetime.split(' ')[0] : null)) : (form ? (form.querySelector('input[name="appointment_date"]') ? form.querySelector('input[name="appointment_date"]').value : (form.querySelector('input[name="date"]') ? form.querySelector('input[name="date"]').value : null)) : null);
                    const branchVal = (data.branch_id || data.branch) ? (data.branch_id || data.branch) : (form ? (form.querySelector('input[name="branch_id"]') ? form.querySelector('input[name="branch_id"]').value : (form.querySelector('select[name="branch"]') ? form.querySelector('select[name="branch"]').value : null)) : null);
                    const timeVal = (data.appointment_time || data.appointment_datetime) ? (data.appointment_time || (data.appointment_datetime ? data.appointment_datetime.split(' ')[1] && data.appointment_datetime.split(' ')[1].slice(0,5) : null)) : null;
                    window.dispatchEvent(new CustomEvent('availability:changed', { detail: { action: 'created', appointment_id: data.appointment_id || null, source: 'admin-form', appointment_date: dateVal, branch_id: branchVal, appointment_time: timeVal } }));
                    notifyAdmin('Appointment created successfully! Time slots updated.', 'success', 4000);
                    if (timeTableModalInstance) try { timeTableModalInstance.clearSelection(); } catch(e){}
                  } else {
                    // Not success JSON but still trigger a refresh as a fallback
                    window.dispatchEvent(new CustomEvent('availability:changed', { detail: { action: 'created', source: 'admin-form' } }));
                  }
                } catch (ex) {
                  // Non-JSON response: still trigger a refresh
                  window.dispatchEvent(new CustomEvent('availability:changed', { detail: { action: 'created', source: 'admin-form' } }));
                }
              }).catch(() => {
                // couldn't read text; still trigger a refresh
                window.dispatchEvent(new CustomEvent('availability:changed', { detail: { action: 'created', source: 'admin-form' } }));
              });
            }
          }).catch(() => {});
        }
      } catch (ex) {
        // Defensive: ignore wrapper errors and return original promise
      }

      return promise;
    };
    
    // Also listen for form success via page reload detection
    let formSubmitted = false;
    form.addEventListener('submit', () => {
      formSubmitted = true;
    });
    
    // If page reloads after form submission, trigger refresh on next load
    if (formSubmitted && window.performance && window.performance.navigation.type === 1) {
      // Page was reloaded, likely due to form submission
      setTimeout(() => {
        window.dispatchEvent(new CustomEvent('availability:changed', {
          detail: { action: 'created', source: 'page-reload' }
        }));
      }, 100);
    }
  }

  // Populate hidden inputs required by the server prior to submission
  function populateAppointmentHiddenInputs(form) {
    if (!form) form = document.getElementById('appointmentForm');
    if (!form) return;

    // appointment_date
    let ad = form.querySelector('input[name="appointment_date"]') || form.querySelector('input[name="date"]');
    if (!ad) {
      ad = document.createElement('input'); ad.type = 'hidden'; ad.name = 'appointment_date'; form.appendChild(ad);
    }
    const dateEl = document.querySelector(ADMIN_SELECTORS.dateInput) || document.querySelector('input[name="date"]') || document.getElementById('appointmentDate');
    ad.value = dateEl ? (dateEl.value || dateEl.getAttribute('value') || '') : '';

    // appointment_time
    let at = form.querySelector('input[name="appointment_time"]') || form.querySelector('input[name="time"]');
    if (!at) { at = document.createElement('input'); at.type = 'hidden'; at.name = 'appointment_time'; form.appendChild(at); }
    const timeEl = document.querySelector(ADMIN_SELECTORS.timeSelect) || document.getElementById('timeSelect') || document.querySelector('select[name="appointment_time"]');
    let timeValue = timeEl ? timeEl.value : '';
    if (!timeValue && typeof timeTableModalInstance !== 'undefined' && timeTableModalInstance && timeTableModalInstance.selectedSlot) {
      timeValue = timeTableModalInstance.selectedSlot.time || timeTableModalInstance.selectedSlot.display || '';
    }
    at.value = timeValue ? normalizeTime(timeValue) : '';

    // branch_id
    let bid = form.querySelector('input[name="branch_id"]');
    if (!bid) { bid = document.createElement('input'); bid.type = 'hidden'; bid.name = 'branch_id'; form.appendChild(bid); }
    const branchEl = document.querySelector(ADMIN_SELECTORS.branchSelect) || document.querySelector('select[name="branch_id"]') || document.querySelector('select[name="branch"]');
    bid.value = branchEl ? branchEl.value : '';

    // service_id
    let svc = form.querySelector('input[name="service_id"]') || form.querySelector('select[name="service_id"]');
    if (!svc) { svc = document.createElement('input'); svc.type = 'hidden'; svc.name = 'service_id'; form.appendChild(svc); }
    const serviceEl = document.querySelector(ADMIN_SELECTORS.serviceSelect) || document.getElementById('service_id');
    svc.value = serviceEl ? serviceEl.value : '';

    // procedure_duration
    let pd = form.querySelector('input[name="procedure_duration"]');
    if (!pd) { pd = document.createElement('input'); pd.type = 'hidden'; pd.name = 'procedure_duration'; form.appendChild(pd); }
    const pdEl = document.querySelector(ADMIN_SELECTORS.procedureDuration) || document.getElementById('procedureDuration');
    pd.value = pdEl ? pdEl.value : '';

    // patient field: if a visible patientSelect exists, ensure hidden select/input is populated
    const patientSelect = form.querySelector('select[name="patient"]') || document.getElementById('patientSelect');
    if (patientSelect) {
      try { patientSelect.value = patientSelect.value || '';} catch(e){}
    }

    if (window.__psm_debug) console.log('[admin-calendar] populateAppointmentHiddenInputs applied', { appointment_date: ad.value, appointment_time: at.value, branch_id: bid.value, service_id: svc.value });

    // Extra debug: print FormData-like object for easier debugging of missing fields
    try {
      const snapshot = {
        appointment_date: ad.value,
        appointment_time: at.value,
        branch_id: bid.value,
        service_id: svc.value,
        procedure_duration: pd.value
      };
      console.debug('[admin-calendar][DEBUG] Hidden inputs snapshot before submit:', snapshot);
    } catch (e) {
      console.warn('[admin-calendar][DEBUG] Failed to snapshot hidden inputs', e);
    }
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

        // appointment_time - ensure this field is always present and populated
        let at = form.querySelector('input[name="appointment_time"]') || form.querySelector('input[name="time"]');
        if (!at) {
          at = document.createElement('input');
          at.type = 'hidden';
          at.name = 'appointment_time';
          form.appendChild(at);
        }
        // Look for time in multiple places: time select, appointment_time select, or TimeTable selection
        const timeEl = document.querySelector(ADMIN_SELECTORS.timeSelect) || 
                      document.querySelector('select[name="appointment_time"]') || 
                      document.querySelector('select[name="time"]') ||
                      document.getElementById('timeSelect');
        
        let timeValue = timeEl ? timeEl.value : '';
        
        // If no time found in selects, check if TimeTable modal has a selection
        if (!timeValue && timeTableModalInstance && timeTableModalInstance.selectedSlot) {
          timeValue = timeTableModalInstance.selectedSlot.time || '';
          console.log('[admin-calendar] Using TimeTable selected time:', timeValue);
        }
        
        // Ensure time is in HH:MM format
        if (timeValue) {
          timeValue = normalizeTime(timeValue);
        }
        
        at.value = timeValue;
        
        // Validate that appointment_time is present
        if (!at.value) {
          e.preventDefault();
          showAdminMessage('Please select an appointment time', 'error');
          return false;
        }

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
          try {
            // include possible context so UI refreshes correct branch/date
            const form = document.getElementById('appointmentForm');
            const dateVal = form ? (form.querySelector('input[name="appointment_date"]') ? form.querySelector('input[name="appointment_date"]').value : (form.querySelector('input[name="date"]') ? form.querySelector('input[name="date"]').value : null)) : null;
            const branchVal = form ? (form.querySelector('input[name="branch_id"]') ? form.querySelector('input[name="branch_id"]').value : (form.querySelector('select[name="branch"]') ? form.querySelector('select[name="branch"]').value : null)) : null;
            window.dispatchEvent(new CustomEvent('availability:changed', { detail: { action: 'approved', appointment_id: appointmentId, branch_id: branchVal, appointment_date: dateVal } }));
          } catch(e) {}
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
          try {
            const form = document.getElementById('appointmentForm');
            const dateVal = form ? (form.querySelector('input[name="appointment_date"]') ? form.querySelector('input[name="appointment_date"]').value : (form.querySelector('input[name="date"]') ? form.querySelector('input[name="date"]').value : null)) : null;
            const branchVal = form ? (form.querySelector('input[name="branch_id"]') ? form.querySelector('input[name="branch_id"]').value : (form.querySelector('select[name="branch"]') ? form.querySelector('select[name="branch"]').value : null)) : null;
            window.dispatchEvent(new CustomEvent('availability:changed', { detail: { action: 'declined', appointment_id: appointmentId, branch_id: branchVal, appointment_date: dateVal } }));
          } catch(e) {}
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
  
  // Admin Available Slots: simplified initializer
  // The legacy dropdown/menu implementation was large and duplicated functionality
  // of the TimeTable modal. Replace with a compact initializer that opens the
  // TimeTable modal and falls back to a minimal message if the modal is not
  // available. This keeps behavior but removes the bulky legacy UI code.
  function initAdminAvailableSlotsMenu() {
    const availableBtn = document.querySelector('#availableSlotsBtn');
    if (!availableBtn) {
      console.log('[admin-calendar] Available slots button not found, skipping initializer');
      return;
    }

    availableBtn.addEventListener('click', async (e) => {
      e.preventDefault();
      try {
        const modalInstance = initTimeTableModal();
        if (modalInstance) {
          await modalInstance.openModal();
          return;
        }

        // As a final fallback, create a temporary TimeTableModal and open it
        if (typeof TimeTableModal === 'function') {
          const tmp = new TimeTableModal();
          await tmp.openModal();
          return;
        }

        // If nothing is available, show a concise message so the user isn't left wondering
        alert('Time Table is not available in this context. Please select branch/service/date in the form above.');
      } catch (err) {
        console.error('[admin-calendar] Error opening time table modal:', err);
        alert('Failed to open Time Table. See console for details.');
      }
    });

    console.log('[admin-calendar] Available slots shortcut initialized (modal-only)');
  }
  
  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminAvailableSlotsMenu);
  } else {
    initAdminAvailableSlotsMenu();
  }
  
  // Debug: Log initialization
  console.log('[admin-calendar] Module loaded, baseUrl:', baseUrl);

  // ===== TIME TABLE MODAL FUNCTIONALITY =====
  
  let timeTableModalInstance = null;
  
  class TimeTableModal {
    constructor() {
      this.isOpen = false;
      this.currentData = null;
      this.selectedSlot = null;
      
      // Create modal HTML if it doesn't exist
      this.modal = document.getElementById('timeTableModal');
      if (!this.modal) {
        console.log('[TimeTableModal] Creating modal HTML structure...');
        // insert HTML markup into body
        document.body.insertAdjacentHTML('beforeend', this._defaultModalHTML());
        // createModalHTML kept for backward compatibility
        if (typeof this.createModalHTML === 'function') {
          try { this.createModalHTML(); } catch(e){}
        }
        this.modal = document.getElementById('timeTableModal');
      }
      
      this.initializeElements();
      this.bindEvents();
    }

    // Returns the default modal HTML string used to inject into the page
    _defaultModalHTML() {
      return `
      <div id="timeTableModal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
        <div id="timeTableModalBackdrop" class="absolute inset-0 bg-black opacity-40"></div>
        <div class="relative max-w-4xl mx-auto mt-12 bg-white rounded shadow-lg overflow-hidden" style="max-height:85vh;">
          <div class="p-4 border-b flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <h3 class="text-lg font-semibold">Time Table</h3>
              <div id="timeTableButtonText" class="text-sm text-gray-600"></div>
            </div>
            <div>
              <button id="closeTimeTableModal" class="px-3 py-1 rounded bg-gray-200">Close</button>
            </div>
          </div>
          <div id="timeTableLoading" class="p-6 text-center">Loading...</div>
          <div id="timeTableContainer" class="hidden">
            <div class="p-4">
              <div class="mb-3"><strong>Branch:</strong> <select id="timeTableBranchSelect"></select></div>
              <div class="mb-3"><strong>Date:</strong> <span id="selectedDateDisplay"></span></div>
              <div class="mb-3"><strong>Time:</strong> <span id="selectedTimeDisplay">Not selected</span></div>
            </div>
            <div class="p-4 overflow-auto h-72">
              <div id="timeGrid" class="grid gap-2" style="grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));"></div>
            </div>
          </div>
          <div id="timeTableError" class="hidden p-6 text-center">Error loading time table</div>
          <div class="border-t p-3 flex items-center justify-between">
            <div class="text-sm text-gray-600"><span id="availableSlotsCount">0</span> available</div>
            <div class="flex items-center space-x-2">
              <button id="refreshTimeTable" class="px-3 py-1 text-blue-600">Refresh</button>
              <button id="cancelTimeSelection" class="px-4 py-2 bg-gray-500 text-white">Cancel</button>
            </div>
          </div>
        </div>
      </div>
      `;
    }
    
    initializeElements() {
      // Modal elements
      this.openButton = document.getElementById('openTimeTableModal');
      this.closeButton = document.getElementById('closeTimeTableModal');
      this.modalBackdrop = document.getElementById('timeTableModalBackdrop');
      this.cancelButton = document.getElementById('cancelTimeSelection');
      
      // Content elements
      this.loadingState = document.getElementById('timeTableLoading');
      this.containerState = document.getElementById('timeTableContainer');
      this.errorState = document.getElementById('timeTableError');
      
      // Info elements
      this.branchSelect = document.getElementById('timeTableBranchSelect');
      this.selectedDateDisplay = document.getElementById('selectedDateDisplay');
      this.timeGrid = document.getElementById('timeGrid');
  // occupied list removed from modal markup; keep references null to avoid DOM errors
  this.occupiedList = null;
  this.availableSlotsCount = document.getElementById('availableSlotsCount');
  this.occupiedSlotsCount = null;
      
      // Form elements
      this.timeSelect = document.getElementById('timeSelect');
      this.selectedTimeDisplay = document.getElementById('selectedTimeDisplay');
      this.selectedTimeText = document.getElementById('selectedTimeText');
      this.selectedTimeDuration = document.getElementById('selectedTimeDuration');
      this.clearTimeButton = document.getElementById('clearTimeSelection');
      this.timeTableButtonText = document.getElementById('timeTableButtonText');
      
      // Refresh and retry buttons
      this.refreshButton = document.getElementById('refreshTimeTable');
      this.retryButton = document.getElementById('retryTimeTable');
    }
    
    bindEvents() {
      // Modal controls
      if (this.openButton) {
        this.openButton.addEventListener('click', () => this.openModal());
      }
      
      if (this.closeButton) {
        this.closeButton.addEventListener('click', () => this.closeModal());
      }
      
      if (this.modalBackdrop) {
        this.modalBackdrop.addEventListener('click', () => this.closeModal());
      }
      
      if (this.cancelButton) {
        this.cancelButton.addEventListener('click', () => this.closeModal());
      }
      
      if (this.clearTimeButton) {
        this.clearTimeButton.addEventListener('click', () => this.clearSelection());
      }
      
      // Branch selector
      if (this.branchSelect) {
        this.branchSelect.addEventListener('change', () => this.onBranchChange());
      }
      
      // Refresh and retry
      if (this.refreshButton) {
        this.refreshButton.addEventListener('click', () => this.refreshData());
      }
      
      if (this.retryButton) {
        this.retryButton.addEventListener('click', () => this.refreshData());
      }
      
      // ESC key to close
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && this.isOpen) {
          this.closeModal();
        }
      });
    }
    
    async openModal() {
      if (!this.modal) return;
      
      this.isOpen = true;
      this.modal.classList.remove('hidden');
      this.modal.setAttribute('aria-hidden', 'false');
      
      // Lock body scroll
      document.body.style.overflow = 'hidden';
      
      // Load initial data
      // Ensure page date input exists and defaults to today when missing so modal is always based on current date
      try {
        const pageDateEl = document.querySelector('input[name="date"]') || document.querySelector('input[type="date"]');
        const today = new Date().toISOString().substring(0,10);
        // If page date is missing, set it to today. If page date exists but is in the past and no explicit selection is expected,
        // prefer today's date for the modal so admins see the current availability by default.
        if (pageDateEl && (!pageDateEl.value || pageDateEl.value !== today)) {
          pageDateEl.value = today;
        }
        // If there's a branch select on the page, prefer that as the modal's default branch
        const pageBranch = document.querySelector(ADMIN_SELECTORS.branchSelect) || document.querySelector('select[name="branch_id"]') || document.querySelector('select[name="branch"]');
        if (pageBranch && this.branchSelect) {
          try { this.branchSelect.value = pageBranch.value; } catch(e) {}
        }
        // Set the Today display in the modal header
        const todayDisplay = document.getElementById('todayDateDisplay');
        if (todayDisplay) {
          try {
            const d = new Date();
            const formatted = d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
            todayDisplay.textContent = formatted + ' (Today)';
          } catch(e) { todayDisplay.textContent = today; }
        }
      } catch (err) { if (window.__psm_debug) console.warn('[TimeTableModal] set default date/branch failed', err); }

      await this.loadBranches();
      await this.loadTimeSlots();
    }
    
    closeModal() {
      if (!this.modal) return;
      
      this.isOpen = false;
      this.modal.classList.add('hidden');
      this.modal.setAttribute('aria-hidden', 'true');
      
      // Unlock body scroll
      document.body.style.overflow = '';
    }
    
    async loadBranches() {
      if (!this.branchSelect) return;
      
      try {
        // Get current branch from form or session
        const currentBranchSelect = document.querySelector(ADMIN_SELECTORS.branchSelect);
        const currentBranchId = currentBranchSelect ? currentBranchSelect.value : '';
        
        // For now, populate with basic branches - this could be enhanced to fetch from API
        this.branchSelect.innerHTML = `
          <option value="1" ${currentBranchId === '1' ? 'selected' : ''}>Branch 1 (Nabua)</option>
          <option value="2" ${currentBranchId === '2' ? 'selected' : ''}>Branch 2 (Iriga)</option>
        `;
        
        // Set default if no current selection
        if (!currentBranchId) {
          this.branchSelect.value = '1';
        }
      } catch (error) {
        console.error('Error loading branches:', error);
      }
    }
    
    async loadTimeSlots() {
      if (!this.containerState || !this.loadingState) return;
      
      // Show loading state
      this.showLoading();
      
      try {
        // Get form data
        const formData = this.getFormData();
        
        if (!formData.date) {
          this.showError('Please select a date first');
          return;
        }
        
        // Update displays
        this.updateDisplays(formData);
        
        // Fetch available slots
        const response = await this.fetchAvailableSlots(formData);
        
        if (response && response.available_slots) {
          this.currentData = response;
          this.renderTimeGrid(response);
          // Occupied list removed — do not render it anymore
          this.updateCounts(response);
          this.showContainer();
        } else {
          this.showError('No time slots available');
        }
        
      } catch (error) {
        console.error('Error loading time slots:', error);
        this.showError('Failed to load time slots: ' + error.message);
      }
    }
    
    getFormData() {
      // Look for form elements in the current page
      const dateEl = document.querySelector('input[name="date"]') || 
                    document.querySelector('input[type="date"]') || 
                    document.querySelector(ADMIN_SELECTORS.dateInput);
      
      const serviceEl = document.querySelector('select[name="service_id"]') || 
                       document.getElementById('service_id') || 
                       document.querySelector(ADMIN_SELECTORS.serviceSelect);
      
      const dentistEl = document.querySelector('select[name="dentist"]') || 
                       document.querySelector('select[name="dentist_id"]') || 
                       document.querySelector(ADMIN_SELECTORS.dentistSelect);
      
      // Get date from form element or use today as fallback
      let date = '';
      const todayStr = new Date().toISOString().substring(0, 10);
      
      if (dateEl && dateEl.value) {
        date = dateEl.value;
      } else {
        date = todayStr;
        if (window.__psm_debug) console.log('[TimeTableModal] Using current date:', date);
      }

      // If the collected date is missing or appears to be in the past relative to today,
      // prefer showing/using today's date for the TimeTable modal so admins immediately
      // see the current availability. This also updates the page input so subsequent
      // calls read the same value.
      if (!date || (typeof date === 'string' && date < todayStr)) {
        if (window.__psm_debug) console.info('[TimeTableModal] Overriding stale/missing date with today:', todayStr, 'previous:', date);
        date = todayStr;
        try { if (dateEl) dateEl.value = todayStr; } catch (e) { /* ignore */ }
        // Also try to set common alternate date input names used elsewhere
        try { const alt = document.querySelector('input[name="appointment_date"]') || document.getElementById('appointmentDate'); if (alt) alt.value = todayStr; } catch(e){}
      }      // Get service_id from form element
      let serviceId = '';
      if (serviceEl && serviceEl.value) {
        serviceId = serviceEl.value;
      } else {
        serviceId = '1'; // Default service ID
        if (window.__psm_debug) console.log('[TimeTableModal] Using default service_id:', serviceId);
      }
      
      // Determine duration logic:
      // - If the selected <option> contains data-duration or data-duration-max use it
      // - Else if there's a procedure duration input use it
      // - Else set duration = 0 so backend will resolve authoritative service duration when service_id is provided
      let duration = 0;
      try {
        if (serviceEl) {
          const selectedOpt = serviceEl.options && serviceEl.selectedIndex >= 0 ? serviceEl.options[serviceEl.selectedIndex] : null;
          if (selectedOpt) {
            const ddMax = selectedOpt.getAttribute('data-duration-max');
            const dd = selectedOpt.getAttribute('data-duration');
            const cand = ddMax ? Number(ddMax) : (dd ? Number(dd) : NaN);
            if (Number.isFinite(cand) && cand > 0) duration = cand;
          }
        }
      } catch (e) { /* ignore */ }

      // fallback to explicit procedure duration input if present
      const procDurEl = document.querySelector(ADMIN_SELECTORS.procedureDuration);
      if ((!duration || duration <= 0) && procDurEl && procDurEl.value) {
        const parsed = Number(procDurEl.value);
        if (Number.isFinite(parsed) && parsed > 0) duration = parsed;
      }

      // If still no duration, prefer sending 0 so the backend (authoritative) resolves
      // the service duration based on service_id. Avoid client-side hard-defaults.
      if (!duration || duration <= 0) {
        duration = 0; // let server pick correct service duration
        if (window.__psm_debug) console.info('[TimeTableModal] No duration specified on client; sending duration=0 so server will resolve service duration');
      }

      const formData = {
        date: date,
        branch_id: this.branchSelect ? this.branchSelect.value : '1',
        service_id: serviceId,
        dentist_id: dentistEl ? dentistEl.value : '',
        duration: duration
      };
      
      console.log('[TimeTableModal] Form data collected:', formData);
      console.log('[TimeTableModal] Date debug - dateEl value:', dateEl ? dateEl.value : 'no dateEl', 'final date:', date, 'today:', todayStr);
      return formData;
    }
    
    updateDisplays(formData) {
      if (this.selectedDateDisplay) {
        // Format date nicely
        try {
          const dateObj = new Date(formData.date);
          const formatted = dateObj.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
          });
          this.selectedDateDisplay.textContent = formatted;
        } catch (e) {
          this.selectedDateDisplay.textContent = formData.date || 'Not selected';
        }
      }
      
      // Update time display if we have a selected slot
      const timeDisplay = document.getElementById('selectedTimeDisplay');
      if (timeDisplay && this.selectedSlot) {
        timeDisplay.textContent = this.selectedSlot.display || this.selectedSlot.time || 'Not selected';
      } else if (timeDisplay) {
        timeDisplay.textContent = 'Not selected';
      }
    }
    
    async fetchAvailableSlots(formData) {
      const payload = {
        date: formData.date,
        branch_id: formData.branch_id,
        service_id: formData.service_id,
        dentist_id: formData.dentist_id,
        duration: formData.duration
      };
      
      // Send as form-encoded to match server-side POST parsing (getPost/getRawInput)
      const formBody = new URLSearchParams();
      Object.keys(payload).forEach(k => { if (payload[k] !== undefined && payload[k] !== null) formBody.append(k, payload[k]); });
      const response = await fetch(`${baseUrl}appointments/available-slots`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': getCsrfToken()
        },
        credentials: 'include',
    body: safeToString(formBody)
      });
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }
      const json = await response.json();
      console.log('[TimeTableModal] available-slots response:', json);
      return json;
    }
    
    renderTimeGrid(data) {
      if (!this.timeGrid) return;
      
      const availableSlots = data.available_slots || [];
      const occupiedMap = data.occupied_map || {};
      const allSlots = data.all_slots || [];
      const meta = (data.metadata || {});

      // Build normalized lookup structures to avoid mismatches caused by
      // differing time formats ("8:00 AM" vs "08:00").
      // this._availableSet contains normalized 'HH:MM' strings
      // this._occupiedMapNormalized maps normalized 'HH:MM' -> appointment object
      try {
        this._availableSet = new Set((availableSlots || []).map(s => {
          if (!s) return '';
          if (typeof s === 'string') return normalizeTime(s);
          if (s.time) return normalizeTime(s.time);
          if (s.datetime) {
            const parts = String(s.datetime).split(' ');
            return parts[1] ? parts[1].slice(0,5) : '';
          }
          return '';
        }).filter(Boolean));

        this._occupiedMapNormalized = {};
        if (Array.isArray(occupiedMap)) {
          occupiedMap.forEach(entry => {
            try {
              const key = entry && entry.time ? normalizeTime(entry.time) : (entry && entry.datetime ? (String(entry.datetime).split(' ')[1] || '') : '');
              if (key) this._occupiedMapNormalized[key] = entry;
            } catch (e) {}
          });
        } else if (occupiedMap && typeof occupiedMap === 'object') {
          // If server returned an object keyed by time already, normalize keys
          Object.keys(occupiedMap).forEach(k => {
            try {
              const norm = normalizeTime(k) || normalizeTime(occupiedMap[k] && occupiedMap[k].time);
              if (norm) this._occupiedMapNormalized[norm] = occupiedMap[k];
            } catch (e) {}
          });
        }
      } catch (e) {
        this._availableSet = new Set();
        this._occupiedMapNormalized = {};
      }

      // Expose metadata visibly for debugging
      try {
        if (this.timeTableButtonText) {
          const ds = meta.day_start ? meta.day_start + ' - ' + (meta.day_end || '') : '';
          const counts = 'avail: ' + (meta.available_count || availableSlots.length) + ' | total: ' + (meta.total_slots_checked || allSlots.length);
          this.timeTableButtonText.textContent = ds ? ds + ' | ' + counts : counts;
        }
      } catch (e) { console.warn('[TimeTableModal] failed to set metadata display', e); }

      console.log('[TimeTableModal] renderTimeGrid: allSlots.length=', allSlots.length, 'availableSlots=', availableSlots.length, 'metadata=', meta);
      
      // Use server-provided slots if available, otherwise generate local ones
      let slots = [];
      if (Array.isArray(allSlots) && allSlots.length > 0) {
        // Use server slots and ensure they have display property
        slots = allSlots.map(slot => {
          if (typeof slot === 'string') {
            return { time: slot, display: slot };
          } else if (slot && typeof slot === 'object') {
            return {
              time: slot.time || slot.slot || '',
              display: slot.time || slot.display || slot.slot || '',
              ...slot // preserve other properties like timestamp, available, etc.
            };
          }
          return { time: '', display: '' };
        });
        // If server provided metadata.day_end but the last server slot ends earlier
        // than metadata.day_end, append generated slots to cover the remaining window.
        try {
          if (meta && meta.day_end) {
            // Helper to convert normalized HH:MM to minutes
            const toMinutes = (hhmm) => {
              if (!hhmm) return null;
              const parts = String(hhmm).split(':').map(Number);
              if (parts.length < 2 || Number.isNaN(parts[0]) || Number.isNaN(parts[1])) return null;
              return parts[0] * 60 + parts[1];
            };

            // Get last slot minute if possible
            const lastSlot = slots.length ? slots[slots.length - 1] : null;
            let lastNorm = '';
            if (lastSlot) {
              if (lastSlot.time) lastNorm = normalizeTime(lastSlot.time);
              else if (lastSlot.display) lastNorm = normalizeTime(lastSlot.display);
              else if (lastSlot.datetime) {
                const p = String(lastSlot.datetime).split(' ');
                lastNorm = p[1] ? p[1].slice(0,5) : '';
              }
            }

            const lastMinute = toMinutes(lastNorm);
            const endNorm = normalizeTime(meta.day_end);
            const endMinute = toMinutes(endNorm);

            const interval = 5;
            // Only append if we can determine both minutes and lastMinute is before endMinute
            if (Number.isFinite(lastMinute) && Number.isFinite(endMinute) && lastMinute + interval <= endMinute) {
              // Generate missing slots starting after lastMinute up to endMinute
              const extra = this.generateTimeSlots(meta, lastMinute + interval, endMinute, interval);
              // Avoid duplicates by filtering any times that already exist
              const existingSet = new Set(slots.map(s => normalizeTime(s.time || s.display || '')));
              extra.forEach(s => {
                const n = normalizeTime(s.time || s.display || '');
                if (n && !existingSet.has(n)) {
                  slots.push(s);
                }
              });
            }
          }
        } catch (e) {
          // non-fatal; fall back to server slots as-is
          if (window.__psm_debug) console.warn('[TimeTableModal] extending slots failed', e);
        }
      } else {
        // Fallback to generating time slots using server metadata if available (day_start/day_end),
        // otherwise default to 8:00 AM - 8:00 PM in 5-minute intervals
        slots = this.generateTimeSlots(meta);
      }
      
      this.timeGrid.innerHTML = '';
      
      slots.forEach(slot => {
        const slotElement = this.createSlotElement(slot, availableSlots, occupiedMap, allSlots, meta);
        this.timeGrid.appendChild(slotElement);
      });
    }
    
    // generateTimeSlots(meta, startMinuteOverride, endMinuteOverride, intervalOverride)
    // When server-provided all_slots is truncated we call this with explicit start/end minutes
    generateTimeSlots(meta, startMinuteOverride, endMinuteOverride, intervalOverride) {
      const slots = [];
      const interval = Number.isFinite(intervalOverride) ? intervalOverride : 5; // 5 minutes

      // Default start/end minutes
      let startMinute = 8 * 60; // 8:00
      let endMinute = 20 * 60; // 20:00

      try {
        if (meta && meta.day_start) {
          const s = normalizeTime(meta.day_start); // returns HH:MM
          if (s) {
            const parts = s.split(':').map(Number);
            if (parts.length >= 2 && Number.isFinite(parts[0]) && Number.isFinite(parts[1])) {
              startMinute = parts[0] * 60 + parts[1];
            }
          }
        }
        if (meta && meta.day_end) {
          const e = normalizeTime(meta.day_end);
          if (e) {
            const parts = e.split(':').map(Number);
            if (parts.length >= 2 && Number.isFinite(parts[0]) && Number.isFinite(parts[1])) {
              endMinute = parts[0] * 60 + parts[1];
            }
          }
        }

        // If explicit overrides are provided, use them
        if (Number.isFinite(startMinuteOverride) && startMinuteOverride > 0) startMinute = startMinuteOverride;
        if (Number.isFinite(endMinuteOverride) && endMinuteOverride > 0) endMinute = endMinuteOverride;

        // Safety: ensure numeric and reasonable bounds
        if (!Number.isFinite(startMinute) || startMinute < 0) startMinute = 8 * 60;
        if (!Number.isFinite(endMinute) || endMinute <= startMinute) endMinute = Math.max(startMinute + 60, 20 * 60);
      } catch (err) {
        // fallback to defaults on any parse error
        startMinute = 8 * 60; endMinute = 20 * 60;
      }

      for (let minuteCursor = startMinute; minuteCursor < endMinute; minuteCursor += interval) {
        const hour = Math.floor(minuteCursor / 60);
        const minute = minuteCursor % 60;
        const hh = String(hour).padStart(2, '0');
        const mm = String(minute).padStart(2, '0');
        const timeStr = `${hh}:${mm}`;
        const displayTime = this.formatDisplayTime(timeStr);
        slots.push({ time: timeStr, display: displayTime });
      }

      return slots;
    }
    
    formatDisplayTime(timeStr) {
      try {
        if (timeStr === null || timeStr === undefined) return 'N/A';

        // If it's a number, treat as unix timestamp (seconds)
        if (typeof timeStr === 'number' && Number.isFinite(timeStr)) {
          const d = new Date(timeStr * 1000);
          const hh = d.getHours();
          const mm = d.getMinutes();
          const period = hh >= 12 ? 'PM' : 'AM';
          const displayHour = hh > 12 ? hh - 12 : (hh === 0 ? 12 : hh);
          return `${displayHour}:${String(mm).padStart(2, '0')} ${period}`;
        }

        // Try to normalize common string formats (HH:MM, HH:MM:SS, 'YYYY-MM-DD HH:MM:SS', '9:00 AM')
        const normalized = normalizeTime(String(timeStr));
        let hhmm = normalized;

        // If normalizeTime couldn't parse, try extracting HH:MM from datetime-like strings
        if (!hhmm) {
          const m = String(timeStr).match(/(\d{1,2}:\d{2})(:\d{2})?/);
          if (m) hhmm = m[1];
        }

        // If still nothing, try Date.parse
        if (!hhmm) {
          const parsed = Date.parse(String(timeStr));
          if (!Number.isNaN(parsed)) {
            const d = new Date(parsed);
            hhmm = `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
          }
        }

        if (!hhmm) return String(timeStr);

        const parts = hhmm.split(':').map(Number);
        if (parts.length < 2 || !Number.isFinite(parts[0]) || !Number.isFinite(parts[1])) return String(timeStr);

        const hour = parts[0];
        const minute = parts[1];
        const period = hour >= 12 ? 'PM' : 'AM';
        const displayHour = hour > 12 ? hour - 12 : (hour === 0 ? 12 : hour);
        return `${displayHour}:${String(minute).padStart(2, '0')} ${period}`;
      } catch (e) {
        // Fallback to raw string if anything unexpected happens
        try { return String(timeStr); } catch (ex) { return 'N/A'; }
      }
    }
    
    createSlotElement(slot, availableSlots, occupiedMap, allSlots, meta) {
      const slotDiv = document.createElement('div');
      slotDiv.className = 'time-slot';
      
      // Check availability (defensive: guard against missing slot.time)
      const slotTime = slot && (slot.time || slot.display) ? normalizeTime(slot.time || slot.display) : '';

      // Prefer server slot's own 'available' property if present
      let isAvailable = false;
      if (slot && typeof slot.available === 'boolean') {
        isAvailable = slot.available;
      } else {
        // Fallback to normalized lookup if available
        isAvailable = (this._availableSet && slotTime) ? this._availableSet.has(slotTime) : (availableSlots || []).some(availSlot => {
          const availTime = typeof availSlot === 'string' ? availSlot : (availSlot.time || availSlot.slot || '');
          return slotTime && normalizeTime(availTime) === slotTime;
        });
      }

      const isOccupied = slotTime ? (this._occupiedMapNormalized && this._occupiedMapNormalized[slotTime] ? true : false) : false;
      

      // Determine status and styling
      // Prefer server-provided metadata.day_start/day_end to determine operating window.
      let status = 'outside-hours';
      let bgColor = 'bg-gray-200 text-gray-500';
      let hoverColor = '';
      let cursor = 'cursor-not-allowed';

      // Determine if this slot falls within operating hours. Use metadata if available,
      // otherwise fall back to server allSlots content (if provided) or assume generated range.
      let isInOperatingWindow = false;
      try {
        if (meta && meta.day_start && meta.day_end) {
          const startNorm = normalizeTime(meta.day_start);
          const endNorm = normalizeTime(meta.day_end || meta.day_start);
          const toMinutes = (t) => {
            if (!t) return null;
            const parts = t.split(':').map(Number);
            if (parts.length < 2 || Number.isNaN(parts[0]) || Number.isNaN(parts[1])) return null;
            return parts[0] * 60 + parts[1];
          };
          // Normalize slot time first (handles object slots, display differences, datetimes)
          const normalizedSlot = normalizeTime(slot.time || slot.display || '');
          const slotMin = toMinutes(normalizedSlot);
          const startMin = toMinutes(startNorm);
          const endMin = toMinutes(endNorm);
          if (slotMin !== null && startMin !== null && endMin !== null) {
            if (endMin >= startMin) {
              isInOperatingWindow = slotMin >= startMin && slotMin <= endMin;
            } else {
              // range wraps past midnight
              isInOperatingWindow = slotMin >= startMin || slotMin <= endMin;
            }
          }
        } else if (Array.isArray(allSlots) && allSlots.length > 0) {
          // normalize allSlots entries for comparison
          const normalizedSlotTime = normalizeTime(slot.time || slot.display || '');
          isInOperatingWindow = allSlots.some(s => {
            const candidate = (typeof s === 'string') ? s : (s.time || s.slot || '');
            const normalizedCandidate = normalizeTime(candidate);
            return normalizedCandidate && normalizedSlotTime && normalizedCandidate === normalizedSlotTime;
          });
        } else {
          // If server did not provide helpful info, assume generateTimeSlots range is operating hours
          isInOperatingWindow = true;
        }
      } catch (e) {
        isInOperatingWindow = true;
      }

      if (isAvailable) {
        status = 'available';
        bgColor = 'bg-green-100 text-green-800 border-green-300';
        hoverColor = 'hover:bg-green-200';
        cursor = 'cursor-pointer';
      } else if (isOccupied) {
        status = 'occupied';
        bgColor = 'bg-red-100 text-red-800 border-red-300';
        hoverColor = 'hover:bg-red-200';
        cursor = 'cursor-help';
      } else if (isInOperatingWindow) {
        // In operating hours but not available — could be occupied or unavailable
        // If we have slot.available === false, treat as occupied/unavailable
        if (slot && slot.available === false) {
          status = 'occupied';
          bgColor = 'bg-red-100 text-red-800 border-red-300';
          hoverColor = 'hover:bg-red-200';
          cursor = 'cursor-help';
        } else {
          status = 'unavailable';
          bgColor = 'bg-gray-100 text-gray-600 border-gray-200';
          hoverColor = '';
          cursor = 'cursor-not-allowed';
        }
      } else {
        // Truly outside operating hours
        status = 'outside-hours';
        bgColor = 'bg-gray-200 text-gray-500';
        hoverColor = '';
        cursor = 'cursor-not-allowed';
      }
      
      slotDiv.className = `time-slot p-2 border rounded text-center text-sm font-medium transition-colors ${bgColor} ${hoverColor} ${cursor}`;
      
      // Safely get display text with fallbacks
      const displayText = slot && (slot.display || slot.time || slot.slot) ? (slot.display || slot.time || slot.slot) : 'N/A';
      
      slotDiv.innerHTML = `
        <div class="font-medium">${displayText}</div>
        <div class="text-xs mt-1 capitalize">${status.replace('-', ' ')}</div>
      `;
      
      // Add click handler for available slots
      if (isAvailable) {
        slotDiv.addEventListener('click', () => this.selectSlot(slot));
      } else if (isOccupied) {
        // Show tooltip or info for occupied slots
        const occ = (this._occupiedMapNormalized && slotTime) ? this._occupiedMapNormalized[slotTime] : (occupiedMap && occupiedMap[slot.time]) || null;
        slotDiv.title = `Occupied - ${occ && (occ.patient_name || occ.patient) ? (occ.patient_name || occ.patient) : 'Unknown patient'}`;
      }
      
      return slotDiv;
    }
    
    selectSlot(slot) {
      this.selectedSlot = slot;
      
      // Update form
      if (this.timeSelect) {
        this.timeSelect.value = slot.time;
        
        // Trigger change event for form validation
        const changeEvent = new Event('change', { bubbles: true });
        this.timeSelect.dispatchEvent(changeEvent);
      }
      
      // Update the time display in the modal header
      const timeDisplay = document.getElementById('selectedTimeDisplay');
      if (timeDisplay) {
        timeDisplay.textContent = slot.display || slot.time || 'Selected';
      }
      
      if (this.selectedTimeText) {
        this.selectedTimeText.textContent = slot.display;
      }
      
      if (this.timeTableButtonText) {
        this.timeTableButtonText.textContent = `Selected: ${slot.display}`;
      }
      
      // Close modal
      this.closeModal();
      
      // Show success message
      notifyAdmin(`Time slot ${slot.display} selected`, 'success', 3000);
    }
    
    clearSelection() {
      this.selectedSlot = null;
      
      if (this.timeSelect) {
        this.timeSelect.value = '';
      }
      
      // Reset time display in modal
      const timeDisplay = document.getElementById('selectedTimeDisplay');
      if (timeDisplay) {
        timeDisplay.textContent = 'Not selected';
      }
      
      if (this.timeTableButtonText) {
        this.timeTableButtonText.textContent = 'Open Time Table';
      }
    }
    
    renderOccupiedInfo(data) {
      if (!this.occupiedList) return;

      // Prefer the normalized occupied map we already built for consistent keys
      const occupiedMap = (this._occupiedMapNormalized && Object.keys(this._occupiedMapNormalized).length) ? this._occupiedMapNormalized : (data.occupied_map || {});
      const occupiedSlots = Object.keys(occupiedMap || {});

      if (occupiedSlots.length === 0) {
        this.occupiedList.innerHTML = '<div class="text-sm text-gray-500 italic">No occupied time slots</div>';
        return;
      }

      // Helper to resolve a service name from various possible server fields
      // Return empty string when unknown so the UI can omit the service column
      const resolveServiceName = (appt) => {
        if (!appt) return '';
        if (appt.service_name) return appt.service_name;
        if (appt.service) return appt.service;
        if (appt.services) {
          if (Array.isArray(appt.services)) return appt.services.map(s => (s.name || s)).join(', ');
          if (typeof appt.services === 'string') return appt.services;
        }
        if (appt.service_id) {
          // Try to find matching option text in the service select
          try {
            const opt = document.querySelector(`select[name="service_id"] option[value="${appt.service_id}"]`) || (document.getElementById('service_id') && document.getElementById('service_id').querySelector(`option[value="${appt.service_id}"]`));
            if (opt && opt.textContent) return opt.textContent.trim();
          } catch (e) {}
        }
        return '';
      };

      this.occupiedList.innerHTML = '';

      occupiedSlots.forEach(timeSlotKey => {
        const appointment = occupiedMap[timeSlotKey] || {};

        // Derive a display time from the key or from appointment fields
        let displayTime = 'Unknown time';
        try {
          // Prefer appointment explicit datetime/time when available
          let candidate = null;
          if (appointment && (appointment.datetime || appointment.start_datetime)) {
            candidate = appointment.datetime || appointment.start_datetime;
          } else if (appointment && (appointment.time || appointment.start_time)) {
            candidate = appointment.time || appointment.start_time;
          } else if (timeSlotKey) {
            candidate = timeSlotKey;
          }

          const norm = normalizeTime(candidate);
          // Treat missing or midnight (00:00) as unknown in this UI context
          if (!norm || norm === '00:00') {
            displayTime = 'Unknown time';
          } else {
            displayTime = this.formatDisplayTime(norm);
          }
        } catch (e) {
          displayTime = String(timeSlotKey || 'Unknown time');
        }

  const patientName = appointment.patient_name || appointment.patient || appointment.patient_display || 'Unknown Patient';
  const serviceName = resolveServiceName(appointment);

        const appointmentDiv = document.createElement('div');
        appointmentDiv.className = 'flex items-center justify-between p-2 bg-red-50 border border-red-200 rounded text-sm';
        // Build inner HTML; omit service column if serviceName is empty
        appointmentDiv.innerHTML = `
          <div class="flex items-center space-x-3">
            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
            <div>
              <div class="font-medium text-red-900">${displayTime || ''}</div>
              <div class="text-red-700">${patientName}</div>
            </div>
          </div>
          ${serviceName ? `<div class="text-red-600 text-xs">${serviceName}</div>` : ''}
        `;

        // Optionally append remarks or end time if available
        if (appointment.end_time || appointment.end_datetime) {
          const endLabel = document.createElement('div');
          endLabel.className = 'text-xs text-red-500 mt-1';
          const endDisplay = this.formatDisplayTime(appointment.end_time || appointment.end_datetime);
          endLabel.textContent = `Ends: ${endDisplay}`;
          appointmentDiv.querySelector('.flex > div > div:last-child').appendChild(endLabel);
        }

        this.occupiedList.appendChild(appointmentDiv);
      });
    }
    
    updateCounts(data) {
      const availableCount = (data.available_slots || []).length;
      // occupied_map may be an array of occupied intervals; fall back to unavailable_slots length
      const occupiedCount = (data.occupied_map && Array.isArray(data.occupied_map)) ? data.occupied_map.length : ((data.unavailable_slots && Array.isArray(data.unavailable_slots)) ? data.unavailable_slots.length : 0);

      if (this.availableSlotsCount) {
        try {
          // Show combined info so admins can immediately see server-side counts
          this.availableSlotsCount.textContent = `${availableCount} available | ${occupiedCount} occupied`;
        } catch (e) {
          this.availableSlotsCount.textContent = availableCount;
        }
      }
      // occupied count intentionally not shown since occupied panel is removed
    }
    
    showLoading() {
      if (this.loadingState) this.loadingState.classList.remove('hidden');
      if (this.containerState) this.containerState.classList.add('hidden');
      if (this.errorState) this.errorState.classList.add('hidden');
    }
    
    showContainer() {
      if (this.loadingState) this.loadingState.classList.add('hidden');
      if (this.containerState) this.containerState.classList.remove('hidden');
      if (this.errorState) this.errorState.classList.add('hidden');
    }
    
    showError(message) {
      if (this.loadingState) this.loadingState.classList.add('hidden');
      if (this.containerState) this.containerState.classList.add('hidden');
      if (this.errorState) this.errorState.classList.remove('hidden');
      
      const errorMessageEl = document.getElementById('timeTableErrorMessage');
      if (errorMessageEl) {
        errorMessageEl.textContent = message;
      }
    }
    
    async onBranchChange() {
      await this.loadTimeSlots();
    }
    
    async refreshData() {
      await this.loadTimeSlots();
    }
  }
  
  // Initialize time table modal
  function initTimeTableModal() {
    if (timeTableModalInstance) return timeTableModalInstance;
    
    // Check if the open button exists (indicates we're on a page that needs the modal)
    const openButton = document.getElementById('openTimeTableModal');
    if (!openButton) {
      console.log('[admin-calendar] Time table modal button not found, skipping initialization');
      return null;
    }
    
    console.log('[admin-calendar] Initializing time table modal...');
    timeTableModalInstance = new TimeTableModal();
    console.log('[admin-calendar] Time table modal initialized');
    
    return timeTableModalInstance;
  }
  
  // Initialize time table modal when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTimeTableModal);
  } else {
    initTimeTableModal();
  }
  
})();