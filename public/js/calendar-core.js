// calendar-core.js (non-module friendly)
// Minimal shared helpers for calendar views. Kept intentionally small to avoid
// duplicating large legacy logic. Attach helpers to window.calendarCore.
(function(){
  window.calendarCore = window.calendarCore || {};

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
    const filter = window.currentBranchFilter || 'all';
    if (filter === 'all') return raw;
  const safe = v => { try { if (v === null || v === undefined) return ''; if (typeof v.toString === 'function') return v.toString(); return String(v); } catch(e) { return ''; } };
  return raw.filter(a => safe(a.branch_name || a.branch_id || '').toLowerCase().includes(safe(filter).toLowerCase()));
  }

  function populateAvailableTimeSlots(selectedDate, timeSelect){
    // fallback implementation used by patient JS when legacy renderer isn't loaded
    // Prefer server-provided availability if available via window.latestAvailability
    timeSelect.innerHTML = '<option value="">Select Time</option>';
    try{
      const latest = window.latestAvailability || null;
      if(latest && Array.isArray(latest.all_slots) && latest.all_slots.length){
        // Use server canonical slots (each may be object {time, available, ...} or string)
        latest.all_slots.forEach(s => {
          const raw = (typeof s === 'string') ? s : (s.time || s);
          const timeStr = raw && raw.substring ? raw.substring(0,5) : (raw || '');
          const opt = document.createElement('option');
          opt.value = timeStr;
          opt.textContent = formatTime(timeStr);
          if (s && typeof s === 'object' && Object.prototype.hasOwnProperty.call(s,'available') && s.available === false) {
            opt.disabled = true;
            opt.textContent = opt.textContent + ' (Unavailable)';
          }
          timeSelect.appendChild(opt);
        });
        return;
      }
    }catch(e){ console.warn('populateAvailableTimeSlots: error reading window.latestAvailability', e); }

    const dateAppointments = (window.appointments || []).filter(apt => {
      const aptDate = apt.appointment_date || (apt.appointment_datetime ? apt.appointment_datetime.substring(0,10) : null);
      return aptDate === selectedDate;
    });

    // default to 8:00 - 20:00 so fallback aligns better with common branch hours
    const startHour = 8, endHour = 20;
    for(let hour = startHour; hour < endHour; hour++){
      for(let minute=0; minute<60; minute+=30){
        const timeStr = String(hour).padStart(2,'0') + ':' + String(minute).padStart(2,'0');
        const opt = document.createElement('option');
        opt.value = timeStr;
        const isBooked = dateAppointments.some(apt => (apt.appointment_time || (apt.appointment_datetime?apt.appointment_datetime.substring(11,16):null)) === timeStr);
        opt.textContent = isBooked ? `${formatTime(timeStr)} (Unavailable)` : formatTime(timeStr);
        if(isBooked) opt.disabled = true;
        timeSelect.appendChild(opt);
      }
    }
  }

  // Safe no-op stubs for larger calendar functions the legacy scripts provide
  function noop(){ }

  window.calendarCore.formatTime = formatTime;
  window.calendarCore.getFilteredAppointments = getFilteredAppointments;
  window.calendarCore.populateAvailableTimeSlots = populateAvailableTimeSlots;
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
