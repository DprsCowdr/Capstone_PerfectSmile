// calendar-admin-separate.js - Dedicated admin calendar logic 
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

  // Admin-specific POST helper with proper CSRF handling
  function postAdminForm(url, data) {
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
      }
    });
    const safeToString = (v) => { try { if (v === null || v === undefined) return ''; if (typeof v.toString === 'function') return v.toString(); return String(v); } catch(e) { return ''; } };
    
    return fetch(baseUrl + url, {
      method: 'POST',
      headers,
      body: safeToString(body),
      credentials: 'same-origin'
    }).then(r => r.text().then(t => {
      let parsed;
      try { 
        parsed = JSON.parse(t); 
      } catch(e) { 
        console.warn('Non-JSON response:', t);
        parsed = t; 
      }
      if (!r.ok) return Promise.reject({ status: r.status, body: parsed });
      return parsed;
    }));
  }

  // Fetch available slots for admin dashboard
  async function fetchAdminAvailableSlots() {
    console.log('[admin-calendar] fetchAdminAvailableSlots called');
    
    const dateEl = document.querySelector(ADMIN_SELECTORS.dateInput);
    const branchEl = document.querySelector(ADMIN_SELECTORS.branchSelect);
    const dentistEl = document.querySelector(ADMIN_SELECTORS.dentistSelect);
    const serviceEl = document.querySelector(ADMIN_SELECTORS.serviceSelect);
    const timeEl = document.querySelector(ADMIN_SELECTORS.timeSelect);
    
    if (!timeEl) {
      console.warn('[admin-calendar] Time select element not found');
      return;
    }
    
    // Get current values
    const date = dateEl ? dateEl.value : '';
    const branchId = branchEl ? branchEl.value : '';
    const dentistId = dentistEl ? dentistEl.value : '';
    const serviceId = serviceEl ? serviceEl.value : '';
    
    console.log('[admin-calendar] Current form values:', {
      date, branchId, dentistId, serviceId
    });
    
    // Clear time options while loading
    timeEl.innerHTML = '<option value="">Loading...</option>';
    
    // Validate required fields
    if (!date) {
      timeEl.innerHTML = '<option value="">Select date first</option>';
      return;
    }
    
    if (!branchId) {
      timeEl.innerHTML = '<option value="">Select branch first</option>';
      return;
    }
    
    if (!serviceId) {
      timeEl.innerHTML = '<option value="">Select service first</option>';
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
      // Derive duration from option data attributes if present
      try {
        const sel = document.querySelector(ADMIN_SELECTORS.serviceSelect) || document.getElementById('service_id');
        if (sel && sel.options && sel.selectedIndex >= 0) {
          const opt = sel.options[sel.selectedIndex];
          const ddMax = opt ? opt.getAttribute('data-duration-max') : null;
          const dd = opt ? opt.getAttribute('data-duration') : null;
          const candidate = ddMax ? Number(ddMax) : (dd ? Number(dd) : null);
          if (Number.isFinite(candidate) && candidate > 0) payload.duration = candidate;
        }
      } catch (e) { console.warn('[admin-calendar] duration read failed', e); }
      
      // Add dentist if selected
      if (dentistId) {
        payload.dentist_id = dentistId;
      }
      
      console.log('[admin-calendar] Posting to /appointments/available-slots with payload:', payload);
      
      const response = await postAdminForm('/appointments/available-slots', payload);
      console.log('[admin-calendar] Available slots response:', response);
      
      if (response && response.success) {
        populateAdminTimeSlots(response, timeEl);
      } else {
        console.error('[admin-calendar] API returned error:', response);
        timeEl.innerHTML = '<option value="">No slots available</option>';
        
        // Show error message if available
        if (response && response.message) {
          showAdminMessage(response.message, 'error');
        }
      }
    } catch (error) {
      console.error('[admin-calendar] Error fetching slots:', error);
      timeEl.innerHTML = '<option value="">Error loading slots</option>';
      showAdminMessage('Error loading available times. Please try again.', 'error');
    }
  }
  
  // Populate time select with available slots
  function populateAdminTimeSlots(response, timeElement) {
    const slots = response.available_slots || response.slots || [];
    
    console.log('[admin-calendar] Populating slots:', slots);
    
    // Clear existing options
    timeElement.innerHTML = '<option value="">Select Time</option>';
    
    if (!Array.isArray(slots) || slots.length === 0) {
      timeElement.innerHTML = '<option value="">No available slots</option>';
      return;
    }
    
    // Add slot options with end time information
    slots.forEach(slot => {
      const option = document.createElement('option');
      
      // Extract time value
      let timeValue;
      if (typeof slot === 'string') {
        timeValue = slot.includes(' ') ? slot.split(' ')[1].slice(0, 5) : slot.slice(0, 5);
      } else if (slot.time) {
        timeValue = slot.time.slice(0, 5);
      } else if (slot.datetime) {
        timeValue = slot.datetime.split(' ')[1].slice(0, 5);
      } else {
        timeValue = String(slot).slice(0, 5);
      }
      
      option.value = timeValue;
      
      // Keep label focused on start time only. End times are not shown because service
      // durations can vary — calculating an end time here is misleading. Use the
      // provided human-friendly slot.time if available.
      let label = (slot && typeof slot === 'object' && slot.time) ? slot.time : timeValue;
      
      option.textContent = label;
      timeElement.appendChild(option);
    });
    
    // Pre-select first available if specified
    if (response.metadata && response.metadata.first_available) {
      const firstAvailable = response.metadata.first_available;
      let firstTime = null;
      
      if (firstAvailable.time) {
        firstTime = firstAvailable.time.slice(0, 5);
      } else if (firstAvailable.datetime) {
        firstTime = firstAvailable.datetime.split(' ')[1].slice(0, 5);
      }
      
      if (firstTime) {
        timeElement.value = firstTime;
        console.log('[admin-calendar] Pre-selected first available time:', firstTime);
      }
    }
    
    showAdminMessage(`${slots.length} available time slots loaded`, 'success');
  }
  
  // Show admin message
  function showAdminMessage(message, type = 'info') {
    console.log(`[admin-calendar] ${type.toUpperCase()}: ${message}`);
    
    // Try to show in UI if message elements exist
    const successEl = document.getElementById('availabilityMessage');
    const errorEl = document.getElementById('unavailableMessage');
    
    if (type === 'success' && successEl) {
      document.getElementById('availabilityText').textContent = message;
      successEl.style.display = 'block';
      if (errorEl) errorEl.style.display = 'none';
    } else if (type === 'error' && errorEl) {
      document.getElementById('unavailableText').textContent = message;
      errorEl.style.display = 'block';  
      if (successEl) successEl.style.display = 'none';
    }
  }
  
  // Setup admin calendar event listeners
  function initAdminCalendar() {
    console.log('[admin-calendar] Initializing admin calendar');
    
    // Check if we're on admin/staff page
    if (!window.userType || !['admin', 'staff'].includes(window.userType)) {
      console.log('[admin-calendar] Not admin/staff page, skipping initialization');
      return;
    }
    
    // Get form elements
    const elements = {};
    Object.keys(ADMIN_SELECTORS).forEach(key => {
      elements[key] = document.querySelector(ADMIN_SELECTORS[key]);
    });
    
    console.log('[admin-calendar] Found elements:', Object.keys(elements).filter(k => elements[k]));
    
    // Setup change listeners to refresh slots
    const triggersRefresh = [elements.dateInput, elements.branchSelect, elements.dentistSelect, elements.serviceSelect];
    
    triggersRefresh.forEach(element => {
      if (element) {
        element.addEventListener('change', (e) => {
          console.log(`[admin-calendar] ${e.target.name || e.target.id} changed to:`, e.target.value);
          fetchAdminAvailableSlots();
        });
      }
    });
    
    // Setup form submission handler
    const form = document.getElementById('appointmentForm');
    if (form) {
      form.addEventListener('submit', handleAdminFormSubmit);
    }
    
    console.log('[admin-calendar] Admin calendar initialized successfully');
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
    
    console.log('[admin-calendar] Form validation passed, submitting');
    return true;
  }
  
  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminCalendar);
  } else {
    initAdminCalendar();
  }
  
  // Export for debugging
  window.adminCalendar = {
    fetchSlots: fetchAdminAvailableSlots,
    init: initAdminCalendar
  };
  
})();