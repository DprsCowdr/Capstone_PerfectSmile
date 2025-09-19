// calendar-core.js (non-module friendly)
// Minimal shared helpers for calendar views. Kept intentionally small to avoid
// duplicating large legacy logic. Attach helpers to window.calendarCore.
(function(){
  window.calendarCore = window.calendarCore || {};

  // Cache for operating hours to avoid repeated API calls
  let operatingHoursCache = {};

  function formatTime(timeStr) {
    if(!timeStr) return '';
    const [hours, minutes] = timeStr.split(':');
    const hour = parseInt(hours,10);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour;
    return `${displayHour}:${minutes} ${ampm}`;
  }

  function getFilteredAppointments() {
    const raw = window.appointments || [];
    let filter = window.currentBranchFilter || 'all';
    if (filter === 'all') return raw;
    // If the filter is an object (branch row), try to extract a usable string
    if (typeof filter === 'object' && filter !== null) {
      filter = (filter.slug || filter.name || filter.id || '').toString();
    } else {
      filter = filter.toString();
    }
    const f = filter.toLowerCase();
    const filtered = raw.filter(a => {
      // Check several possible branch fields on the appointment record
      const branchName = (a.branch_name || a.branch || a.branch_id || '').toString().toLowerCase();
      const branchSlug = (a.branch_slug || a.branch_code || '').toString().toLowerCase();
      return branchName.includes(f) || branchSlug.includes(f);
    });
    // If filter produced no results but appointments exist, allow admin/staff to see all appointments
    try {
      if ((filtered.length === 0) && Array.isArray(raw) && raw.length > 0 && window.userType && window.userType !== 'patient') {
        console.debug('[calendar-core] Branch filter returned 0 results — falling back to full appointment list for userType:', window.userType);
        return raw;
      }
    } catch (e) {
      // swallow
    }
    return filtered;
  }

  /**
   * Fetch operating hours for a branch from the server
   */
  async function fetchOperatingHours(branchId) {
    if (!branchId) {
      console.warn('[calendar-core] No branch ID provided for operating hours');
      return null;
    }

    // Check cache first
    if (operatingHoursCache[branchId]) {
      console.debug('[calendar-core] Using cached operating hours for branch', branchId);
      return operatingHoursCache[branchId];
    }

    try {
      const baseUrl = window.location.origin + '/';
      const response = await fetch(`${baseUrl}appointments/getOperatingHours`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include',
        body: `branch_id=${encodeURIComponent(branchId)}`
      });

      if (!response.ok) {
        console.warn('[calendar-core] Failed to fetch operating hours:', response.status);
        return null;
      }

      const data = await response.json();
      if (data.success && data.operating_hours) {
        // Cache the result
        operatingHoursCache[branchId] = data.operating_hours;
        console.debug('[calendar-core] Fetched operating hours for branch', branchId, data.operating_hours);
        return data.operating_hours;
      } else {
        console.warn('[calendar-core] Invalid operating hours response:', data);
        return null;
      }
    } catch (error) {
      console.error('[calendar-core] Error fetching operating hours:', error);
      return null;
    }
  }

  /**
   * Get operating hours for a specific date
   */
  function getOperatingHoursForDate(operatingHours, date) {
    // Default fallback: 08:00 - 21:00 (8 AM - 9 PM) for better evening coverage
    if (!operatingHours || !date) return { startHour: 8, endHour: 21 };

    const dateObj = new Date(date);
    const dayName = dateObj.toLocaleLowerCase().toLocaleLowerCase('en-US', { weekday: 'long' });
    
    const dayHours = operatingHours[dayName];
    if (!dayHours || !dayHours.enabled) {
      // Branch is closed on this day
      return { startHour: null, endHour: null, closed: true };
    }

    // Parse open/close times (format: "HH:MM")
  const openTime = dayHours.open || '08:00';
  const closeTime = dayHours.close || '20:00';
    
    const startHour = parseInt(openTime.split(':')[0], 10);
    const endHour = parseInt(closeTime.split(':')[0], 10);

    return { startHour, endHour, openTime, closeTime };
  }

  async function populateAvailableTimeSlots(selectedDate, timeSelect){
    // Prefer server-provided slots cached by the patient/calendar scripts
    timeSelect.innerHTML = '<option value="">Select Time</option>';
    try{
      if(window.__available_slots_cache && window.__available_slots_cache[selectedDate] && Array.isArray(window.__available_slots_cache[selectedDate])){
        const slots = window.__available_slots_cache[selectedDate];
        slots.forEach(slot => {
          // normalize time value to HH:MM
          let timeStrRaw = (typeof slot === 'string') ? slot : (slot.time || slot.datetime || slot);
          let timeVal = timeStrRaw;
          try { if(typeof timeStrRaw === 'string' && timeStrRaw.indexOf(' ')>=0) timeVal = timeStrRaw.split(' ')[1].slice(0,5); else if(typeof timeStrRaw === 'string') timeVal = timeStrRaw.slice(0,5); }catch(e){}
          const opt = document.createElement('option');
          opt.value = timeVal;
          opt.textContent = formatTime(timeVal);
          // disable if slot object indicates unavailable
          if(slot && typeof slot === 'object' && slot.available === false) opt.disabled = true;
          timeSelect.appendChild(opt);
        });
        return;
      }
    }catch(e){ console.warn('populateAvailableTimeSlots cache read failed', e); }

    // Fallback implementation - now uses dynamic operating hours
    const dateAppointments = (window.appointments || []).filter(apt => {
      const aptDate = apt.appointment_date || (apt.appointment_datetime ? (apt.appointment_datetime.substring(0,10)) : null);
      return aptDate === selectedDate;
    });

    // Try to get branch ID from the form or global variables
    let branchId = null;
    try {
      const branchSelect = document.querySelector('select[name="branch"], select[name="branch_id"]');
      branchId = branchSelect ? branchSelect.value : null;
      
      // Fallback to global variables
      if (!branchId) {
        branchId = window.currentBranchId || window.selectedBranchId || '1';
      }
    } catch (e) {
      console.warn('[calendar-core] Could not determine branch ID, using default');
      branchId = '1';
    }

    // Get operating hours for the branch
  let startHour = 8, endHour = 21; // Default fallback (08:00 - 21:00) extended for evening coverage
    
    try {
      const operatingHours = await fetchOperatingHours(branchId);
      if (operatingHours) {
        const dayHours = getOperatingHoursForDate(operatingHours, selectedDate);
        
        if (dayHours.closed) {
          // Branch is closed on this day
          const opt = document.createElement('option');
          opt.value = '';
          opt.textContent = 'Branch closed on this day';
          opt.disabled = true;
          timeSelect.appendChild(opt);
          return;
        }
        
        if (dayHours.startHour !== null && dayHours.endHour !== null) {
          startHour = dayHours.startHour;
          endHour = dayHours.endHour;
          console.debug('[calendar-core] Using operating hours:', { startHour, endHour, date: selectedDate });
        }
      }
    } catch (e) {
      console.warn('[calendar-core] Failed to get operating hours, using defaults:', e);
    }

    // Generate time slots based on operating hours
    for(let hour = startHour; hour < endHour; hour++){
      for(let minute=0; minute<60; minute+=30){
        const timeStr = String(hour).padStart(2,'0') + ':' + String(minute).padStart(2,'0');
        const opt = document.createElement('option');
        opt.value = timeStr;
        // Keep raw comparison for booking logic, but display using human-friendly format
        const isBooked = dateAppointments.some(apt => (apt.appointment_time || (apt.appointment_datetime ? apt.appointment_datetime.substring(11,16) : null)) === timeStr);
        opt.textContent = isBooked ? `${formatTime(timeStr)} (Unavailable)` : formatTime(timeStr);
        if(isBooked) opt.disabled = true;
        timeSelect.appendChild(opt);
      }
    }
  }

  /**
   * Refresh available time slots for all time selects on the page
   * This is useful when operating hours change or branch changes
   */
  async function refreshAllTimeSlots() {
    const timeSelects = document.querySelectorAll('select[name="appointment_time"], select[name="time"], .time-select');
    const dateInput = document.querySelector('input[name="appointment_date"], input[name="date"], .date-input');
    
    if (!dateInput || !dateInput.value) {
      console.debug('[calendar-core] No date selected, skipping time slot refresh');
      return;
    }
    
    const selectedDate = dateInput.value;
    console.debug('[calendar-core] Refreshing time slots for date:', selectedDate);
    
    for (const timeSelect of timeSelects) {
      try {
        await populateAvailableTimeSlots(selectedDate, timeSelect);
      } catch (e) {
        console.warn('[calendar-core] Failed to refresh time slots for select:', timeSelect, e);
      }
    }
  }

  /**
   * Clear operating hours cache for a branch (useful when hours are updated)
   */
  function clearOperatingHoursCache(branchId = null) {
    if (branchId) {
      delete operatingHoursCache[branchId];
      console.debug('[calendar-core] Cleared operating hours cache for branch:', branchId);
    } else {
      operatingHoursCache = {};
      console.debug('[calendar-core] Cleared all operating hours cache');
    }
  }

  // Safe no-op stubs for larger calendar functions the legacy scripts provide
  function noop(){ }

  window.calendarCore.formatTime = formatTime;
  window.calendarCore.getFilteredAppointments = getFilteredAppointments;
  window.calendarCore.populateAvailableTimeSlots = populateAvailableTimeSlots;
  window.calendarCore.fetchOperatingHours = fetchOperatingHours;
  window.calendarCore.getOperatingHoursForDate = getOperatingHoursForDate;
  window.calendarCore.refreshAllTimeSlots = refreshAllTimeSlots;
  window.calendarCore.clearOperatingHoursCache = clearOperatingHoursCache;
  window.calendarCore.rebuildCalendarGrid = window.rebuildCalendarGrid || noop;
  window.calendarCore.updateDayViewForDate = window.updateDayViewForDate || noop;
  window.calendarCore.updateWeekView = window.updateWeekView || noop;
  window.calendarCore.handleCalendarNav = window.handleCalendarNav || noop;
  // Provide global wrapper functions so inline onclick handlers (in templates) won't throw
  // They delegate to the implementations attached to window.calendarCore when available.
  window.handleCalendarNav = window.handleCalendarNav || function(direction){
    if (window.calendarCore && typeof window.calendarCore.handleCalendarNav === 'function') {
      return window.calendarCore.handleCalendarNav(direction);
    }
    return noop();
  };
  window.rebuildCalendarGrid = window.rebuildCalendarGrid || function(){
    if (window.calendarCore && typeof window.calendarCore.rebuildCalendarGrid === 'function') {
      return window.calendarCore.rebuildCalendarGrid();
    }
  };
  window.updateDayViewForDate = window.updateDayViewForDate || function(date){
    if (window.calendarCore && typeof window.calendarCore.updateDayViewForDate === 'function') {
      return window.calendarCore.updateDayViewForDate(date);
    }
  };
  window.updateWeekView = window.updateWeekView || function(){
    if (window.calendarCore && typeof window.calendarCore.updateWeekView === 'function') {
      return window.calendarCore.updateWeekView();
    }
  };
  window.updateCalendarDisplay = window.updateCalendarDisplay || function(){
    if (window.calendarCore && typeof window.calendarCore.handleCalendarNav === 'function') {
      // best-effort: call handleCalendarNav(0) to trigger a refresh if available
      return window.calendarCore.handleCalendarNav(0);
    }
  };
})();
