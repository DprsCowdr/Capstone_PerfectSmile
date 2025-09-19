<script>
// Marker: global calendar scripts loaded
window.globalCalendarLoaded = window.globalCalendarLoaded || true;
// Helper to format HH:MM or full datetime -> h:MM AM/PM for display across calendar scripts
function prettyTimeForDisplay(hm) {
  if (!hm) return hm;
  if (typeof hm !== 'string') return hm;
  let timePart = hm;
  if (hm.indexOf(' ') !== -1) timePart = hm.split(' ')[1];
  const parts = timePart.split(':');
  if (parts.length < 2) return hm;
  let hh = parseInt(parts[0], 10);
  const mm = parts[1];
  const ampm = hh >= 12 ? 'PM' : 'AM';
  hh = hh % 12; if (hh === 0) hh = 12;
  return hh + ':' + mm + ' ' + ampm;
}
// Ensure the booking panel opener exists as a safe fallback so inline onclick handlers never throw
window.openAddAppointmentPanelWithTime = window.openAddAppointmentPanelWithTime || function(date, time){
  console.warn('Fallback openAddAppointmentPanelWithTime called before calendar initialization', date, time);
};
// Make showWeekAppointmentDetails globally available for week view appointment clicks
window.showWeekAppointmentDetails = function(appointmentId) {
  const appointment = (window.appointments || []).find(apt => apt.id == appointmentId);
  if (!appointment) {
    if (typeof showInvoiceAlert === 'function') showInvoiceAlert('Appointment not found', 'warning', 4000); else alert('Appointment not found');
    return;
  }
  let modal = document.getElementById('dayAppointmentsModal');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'dayAppointmentsModal';
    modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40';
    modal.innerHTML = `
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 relative animate-fade-in">
        <button id="closeDayAppointmentsModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl font-bold">&times;</button>
        <h2 class="text-xl font-bold mb-4 text-blue-700">Appointment Details</h2>
        <div id="dayAppointmentsList"></div>
      </div>
    `;
    document.body.appendChild(modal);
  }
  // Populate details
  const list = modal.querySelector('#dayAppointmentsList');
  // Respect patient privacy: only show patient name to non-patient users
  let patientLine = '';
  if (window.userType !== 'patient') {
    patientLine = `<div class="mb-2"><span class="font-semibold">Patient:</span> ${appointment.patient_name || ''}</div>`;
  }
  function formatTimeWithGrace(apt) {
    const rawTime = apt.appointment_time || (apt.appointment_datetime ? apt.appointment_datetime.substring(11,16) : 'TBD');
    const time = prettyTimeForDisplay(rawTime);
    const grace = apt.grace_minutes ? ` <span class="text-xs text-gray-500">(+${apt.grace_minutes}m buffer)</span>` : (apt.adjusted_time && apt.adjusted_time !== (apt.appointment_time||'') ? ` <span class="text-xs text-gray-500">(adjusted ${prettyTimeForDisplay(apt.adjusted_time)})</span>` : '');
    return time + grace;
  }

  list.innerHTML = patientLine + `
    <div class="mb-2"><span class="font-semibold">Date:</span> ${appointment.appointment_date || (appointment.appointment_datetime ? appointment.appointment_datetime.substring(0,10) : '')}</div>
    <div class="mb-2"><span class="font-semibold">Time:</span> ${formatTimeWithGrace(appointment)}</div>
    <div class="mb-2"><span class="font-semibold">Status:</span> ${appointment.status || ''}</div>
    <div class="mb-2"><span class="font-semibold">Remarks:</span> ${appointment.remarks || ''}</div>
    <button class="bg-slate-600 hover:bg-slate-700 text-white px-3 py-1 rounded text-sm mt-2" onclick="editAppointment(${appointment.id})">Edit</button>
  `;
}
// Reusable formatter for appointment time that includes grace/adjusted labels
function formatAppointmentTime(apt) {
  if (!apt) return '';
  const time = apt.appointment_time || (apt.appointment_datetime ? apt.appointment_datetime.substring(11,16) : 'TBD');
  if (apt.grace_minutes) {
    return `${time} <span class="text-xs text-gray-500">(+${apt.grace_minutes}m buffer)</span>`;
  }
  if (apt.adjusted_time && apt.adjusted_time !== (apt.appointment_time||'')) {
    return `${apt.adjusted_time} <span class="text-xs text-gray-500">(adjusted)</span>`;
  }
  return time;
}
// Compute queue positions for appointments that share the same date+time.
function computeQueuePositions(appts) {
  if (!Array.isArray(appts)) return appts;
  const groups = {};
  appts.forEach(apt => {
    const date = apt.appointment_date || (apt.appointment_datetime ? apt.appointment_datetime.substring(0,10) : '');
  const rawTime = apt.appointment_time || (apt.appointment_datetime ? apt.appointment_datetime.substring(11,16) : '');
  const time = prettyTimeForDisplay(rawTime);
    const key = `${date} ${time}`;
    groups[key] = groups[key] || [];
    groups[key].push(apt);
  });
  Object.keys(groups).forEach(k => {
    groups[k].sort((a,b) => {
      if (a.created_at && b.created_at) return new Date(a.created_at) - new Date(b.created_at);
      return (a.id||0) - (b.id||0);
    });
    groups[k].forEach((apt, idx) => apt._queuePosition = idx + 1);
  });
  return appts;
}
  // Handles the All Appointments modal logic for the calendar
function showAllAppointments(limitToPatient = false) {
  // Build or reuse modal
  let modal = document.getElementById('allAppointmentsModal');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'allAppointmentsModal';
    modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40';
    modal.innerHTML = `
      <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full p-6 relative animate-fade-in">
        <button id="closeAllAppointmentsModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl font-bold">&times;</button>
        <h2 class="text-xl font-bold mb-4 text-blue-700">All Appointments <span id="allAppointmentsCount" class='ml-2 text-base text-gray-500 font-normal'></span></h2>
        <div id="allAppointmentsList" class="max-h-[60vh] overflow-y-auto"></div>
      </div>
    `;
    document.body.appendChild(modal);
  }

  const list = modal.querySelector('#allAppointmentsList');
  const countEl = modal.querySelector('#allAppointmentsCount');
  let appointments = getFilteredAppointments();
  if (limitToPatient && window.currentUserId) {
    appointments = appointments.filter(a => Number(a.user_id) === Number(window.currentUserId));
  }

  countEl.textContent = `(${appointments.length})`;

  if (!appointments || appointments.length === 0) {
    list.innerHTML = '<div class="text-gray-500">No appointments found.</div>';
  } else {
    // Build a simple table listing
    // Compute queue positions by grouping by datetime and sorting by created_at if available
    const grouped = {};
    appointments.forEach(apt => {
      const key = (apt.appointment_date || (apt.appointment_datetime ? apt.appointment_datetime.substring(0,10) : '')) + ' ' + (apt.appointment_time || (apt.appointment_datetime ? apt.appointment_datetime.substring(11,16) : ''));
      grouped[key] = grouped[key] || [];
      grouped[key].push(apt);
    });
    // assign queue positions
    Object.keys(grouped).forEach(k => {
      grouped[k].sort((a,b) => {
        if (a.created_at && b.created_at) return new Date(a.created_at) - new Date(b.created_at);
        return (a.id||0) - (b.id||0);
      });
      grouped[k].forEach((apt, idx) => apt._queuePosition = idx + 1);
    });

    function timeWithGrace(apt) {
    const rawTime = apt.appointment_time || (apt.appointment_datetime ? apt.appointment_datetime.substring(11,16) : '');
    const time = prettyTimeForDisplay(rawTime);
    if (apt.grace_minutes) return `${time} (+${apt.grace_minutes}m)`;
    if (apt.adjusted_time && apt.adjusted_time !== apt.appointment_time) return `${prettyTimeForDisplay(apt.adjusted_time)} (adjusted)`;
    return time;
    }

    const rows = appointments.map((apt, i) => {
      const date = apt.appointment_date || (apt.appointment_datetime ? apt.appointment_datetime.substring(0,10) : '');
      const time = timeWithGrace(apt);
      const patient = (window.userType === 'patient') ? 'Appointment' : (apt.patient_name || 'Unknown');
      const statusClass = getStatusBadgeClass(apt.status);
      const queueLabel = apt._queuePosition && apt._queuePosition > 1 ? `<span class="text-xs text-gray-500">#${apt._queuePosition}</span>` : '';
      return `
        <tr class="${i % 2 === 0 ? 'bg-white' : 'bg-blue-50'} hover:bg-blue-100 transition">
          <td class="px-4 py-2 font-semibold text-blue-800">${patient}</td>
          <td class="px-4 py-2 text-gray-600">${date}</td>
          <td class="px-4 py-2 text-gray-600">${time} ${queueLabel}</td>
          <td class="px-4 py-2 text-xs"><span class="inline-block rounded-full px-2 py-1 ${statusClass}">${apt.status || ''}</span></td>
          <td class="px-4 py-2 text-gray-500">${apt.dentist_name || ''}</td>
        </tr>
      `;
    }).join('');

    list.innerHTML = `
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Patient</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Date</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Time</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Status</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Dentist</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            ${rows}
          </tbody>
        </table>
      </div>
    `;
  }

  // Helper for status badge color (kept local to avoid global overwrite)
  function getStatusBadgeClass(status) {
    switch ((status||'').toLowerCase()) {
      case 'pending': return 'bg-yellow-100 text-yellow-800';
      case 'scheduled': return 'bg-blue-100 text-blue-800';
      case 'confirmed': return 'bg-green-100 text-green-800';
      case 'completed': return 'bg-green-200 text-green-900';
      case 'cancelled': return 'bg-red-100 text-red-800';
      case 'no_show': return 'bg-gray-100 text-gray-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  }

  // Show modal and wire close
  modal.classList.remove('hidden');
  const closeBtn = modal.querySelector('#closeAllAppointmentsModal');
  if (closeBtn) closeBtn.onclick = () => { modal.classList.add('hidden'); };
}

document.addEventListener('DOMContentLoaded', function () {
  const allBtn = document.getElementById('allAppointmentsBtn');
  if (allBtn) {
    allBtn.addEventListener('click', function(){ try { showAllAppointments(); } catch(e) { console.error(e); if (typeof showInvoiceAlert === 'function') showInvoiceAlert('All Appointments function not loaded.', 'error', 4000); else alert('All Appointments function not loaded.'); } });
  }
});
</script>
 
<script>
try {
// Pass data to JavaScript
window.userType = '<?= $user['user_type'] ?>';
let _apts = <?= json_encode($appointments ?? []) ?>;
if (!Array.isArray(_apts)) {
  // If it's an object (from array_filter with numeric keys missing), convert to array
  window.appointments = Object.values(_apts);
} else {
  window.appointments = _apts;
}
window.baseUrl = '<?= base_url() ?>';
window.branches = <?= json_encode($branches ?? []) ?>;
window.currentUserId = <?= isset($user['id']) ? $user['id'] : 'null' ?>;

// Diagnostic logs to help debug missing appointments in Day/Week views
console.info('[Calendar Debug] Initial appointments count:', Array.isArray(window.appointments) ? window.appointments.length : typeof window.appointments);
console.debug('[Calendar Debug] appointments sample:', (window.appointments || []).slice(0,5));
console.debug('[Calendar Debug] currentBranchFilter initial value:', window.currentBranchFilter);

// Initialize branch filter dropdown
document.addEventListener('DOMContentLoaded', function() {
    // Initialize branch dropdown functionality
    const branchDropdownBtn = document.getElementById('branchDropdownBtn');
    const branchDropdownMenu = document.getElementById('branchDropdownMenu');
    const branchDropdownLabel = document.getElementById('branchDropdownLabel');
    
    if (branchDropdownBtn && branchDropdownMenu) {
        // Toggle dropdown visibility
        branchDropdownBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            branchDropdownMenu.classList.toggle('hidden');
        });
        
        // Handle branch filter option clicks
        const branchOptions = branchDropdownMenu.querySelectorAll('.branch-filter-option');
        branchOptions.forEach(option => {
            option.addEventListener('click', function() {
                const branchValue = this.getAttribute('data-branch');
                const branchLabel = this.textContent.trim();
                
                // Update current branch filter
                window.currentBranchFilter = branchValue;
                console.log('[Branch Filter] Changed to:', branchValue);
                console.log('[Branch Filter] Total appointments:', window.appointments?.length || 0);
                console.log('[Branch Filter] Filtered appointments:', getFilteredAppointments()?.length || 0);
                
                // Update dropdown label
                if (branchDropdownLabel) {
                    branchDropdownLabel.textContent = branchLabel;
                    // Update label color based on branch
                    branchDropdownLabel.className = branchValue === 'nabua' ? 'text-green-700' : 
                                                   branchValue === 'iriga' ? 'text-blue-700' : 
                                                   'text-gray-900';
                }
                
                // Update active state visual
                branchOptions.forEach(opt => opt.classList.remove('bg-gray-100', 'bg-green-100', 'bg-blue-100'));
                this.classList.add(branchValue === 'nabua' ? 'bg-green-100' : 
                                  branchValue === 'iriga' ? 'bg-blue-100' : 
                                  'bg-gray-100');
                
                // Hide dropdown
                branchDropdownMenu.classList.add('hidden');
                
                // Refresh current view
                if (typeof rebuildCalendarGrid === 'function') {
                    rebuildCalendarGrid();
                }
                if (typeof updateDayViewForDate === 'function' && typeof currentSelectedDay !== 'undefined') {
                    const selectedDate = `${currentDisplayedYear}-${String(currentDisplayedMonth + 1).padStart(2, '0')}-${String(currentSelectedDay).padStart(2, '0')}`;
                    updateDayViewForDate(selectedDate);
                }
                if (typeof updateWeekView === 'function') {
                    updateWeekView();
                }
            });
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!branchDropdownBtn.contains(e.target) && !branchDropdownMenu.contains(e.target)) {
                branchDropdownMenu.classList.add('hidden');
            }
        });
    }
    
    // Initialize current branch filter to 'all'
  // If only one branch is available, hide the 'All' option and set filter to that branch
  if (Array.isArray(window.branches) && window.branches.length <= 1) {
    window.currentBranchFilter = window.branches.length === 1 ? window.branches[0] : 'all';
    // hide the 'All' option in the dropdown if present
    const allOpt = branchDropdownMenu ? branchDropdownMenu.querySelector('[data-branch="all"]') : null;
    if (allOpt) allOpt.classList.add('hidden');
  } else {
    window.currentBranchFilter = 'all';
  }
  // After setting up branch filter, do a quick guarded refresh of calendar views so day/week update with current data
  setTimeout(function(){
    try {
      console.info('[Calendar Debug] Triggering initial calendar refresh - appointments:', (window.appointments||[]).length, 'filter:', window.currentBranchFilter);
      if (typeof rebuildCalendarGrid === 'function') rebuildCalendarGrid();
      // compute a reasonable selectedDate for day view
      if (typeof updateDayViewForDate === 'function') {
        const today = new Date();
        const sd = `${today.getFullYear()}-${String(today.getMonth()+1).padStart(2,'0')}-${String(today.getDate()).padStart(2,'0')}`;
        try { updateDayViewForDate(sd); } catch(e) { console.warn('updateDayViewForDate failed', e); }
      }
      if (typeof updateWeekView === 'function') updateWeekView();
    } catch(e) { console.error('[Calendar Debug] initial refresh error', e); }
  }, 120);
});

// Add some test appointments for debugging if none exist
if (!window.appointments || window.appointments.length === 0) {
    console.log('No appointments found');
    window.appointments = [];
}

console.log('Loaded appointments for conflict detection:', window.appointments);

// Function to populate available time slots for patients
function populateAvailableTimeSlots(selectedDate, timeSelect) {
  // Clear existing options
  timeSelect.innerHTML = '<option value="">Select Time</option>';
  
  // Get all appointments for the selected date
  const dateAppointments = (window.appointments || []).filter(apt => {
    const aptDate = apt.appointment_date || (apt.appointment_datetime ? apt.appointment_datetime.substring(0, 10) : null);
    return aptDate === selectedDate;
  });
  // Decide working hours using recurring availability events (is_recurring == 1) for this dentist/date
  const events = (window.availabilityEvents || []);
  // Which dentist is selected on booking form (if any)
  let dentistId = null;
  const dentistEl = document.querySelector('select[name="dentist_id"]') || document.getElementById('dentistSelect');
  if (dentistEl && dentistEl.value) dentistId = dentistEl.value;

  // Find recurring occurrences that intersect selectedDate and belong to the dentist (if dentistId provided)
  const dayStart = getPHDate(selectedDate + 'T00:00:00');
  const dayEnd = getPHDate(selectedDate + 'T23:59:59');

  // Consider either explicit recurring expansions OR generated working_hours blocks
  const recurring = events.filter(ev => (
      Number(ev.is_recurring) === 1 || String(ev.type) === 'working_hours'
    ) && (!dentistId || String(ev.user_id) === String(dentistId))).filter(ev => {
    const evStart = getPHDate(ev.start);
    const evEnd = getPHDate(ev.end);
    return evStart <= dayEnd && evEnd >= dayStart;
  });

  // If no recurring schedule found, fall back to default clinic hours 08:00-20:00
  let workingStart = getPHDate(selectedDate + 'T08:00:00');
  let workingEnd = getPHDate(selectedDate + 'T20:00:00');
  if (recurring.length) {
    // Choose earliest start and latest end among recurring occurrences for that day
    let earliest = recurring[0] && getPHDate(recurring[0].start);
    let latest = recurring[0] && getPHDate(recurring[0].end);
    recurring.forEach(r => {
      const rs = getPHDate(r.start);
      const re = getPHDate(r.end);
      if (rs < earliest) earliest = rs;
      if (re > latest) latest = re;
    });
    workingStart = earliest;
    workingEnd = latest;
  }

  // Build 30-min slots between workingStart and workingEnd, exclude booked and explicit blocks
  let availableSlots = 0;
  let bookedSlots = 0;

  // gather explicit blocks (non-recurring) affecting this date
  const explicitBlocks = events.filter(ev => Number(ev.is_recurring) === 0 && (!dentistId || String(ev.user_id) === String(dentistId))).map(ev => ({
    start: getPHDate(ev.start),
    end: getPHDate(ev.end),
    type: ev.type
  }));

  // iterate from workingStart to workingEnd in 30-min steps
  let cursor = new Date(workingStart.getTime());
  while (cursor < workingEnd) {
    const slotStart = new Date(cursor.getTime());
    const slotEnd = new Date(cursor.getTime() + 30 * 60 * 1000);
    // format time for option value (HH:MM)
    const hh = String(slotStart.getHours()).padStart(2, '0');
    const mm = String(slotStart.getMinutes()).padStart(2, '0');
    const timeStr = `${hh}:${mm}`;
    const displayTime = formatTime(timeStr);

    // Check if slot overlaps any booked appointment
    const isBooked = dateAppointments.some(apt => {
      const aptTime = apt.appointment_time || (apt.appointment_datetime ? apt.appointment_datetime.substring(11, 16) : null);
      return aptTime === timeStr;
    });

    // Check if slot intersects explicit block
    const blocked = explicitBlocks.some(b => !(b.end <= slotStart || b.start >= slotEnd));

    const option = document.createElement('option');
    option.value = timeStr;
    if (isBooked || blocked) {
      // Friendlier text for non-technical users
      option.textContent = `${displayTime} — Not available`;
      option.disabled = true;
      // Many browsers ignore option styling; keep inline color as best-effort
      option.style.color = '#b91c1c';
      bookedSlots++;
    } else {
      option.textContent = displayTime;
      availableSlots++;
    }
    timeSelect.appendChild(option);

    cursor = new Date(cursor.getTime() + 30 * 60 * 1000);
  }
  
  // Show availability message
  const availabilityMessage = document.getElementById('availabilityMessage');
  const unavailableMessage = document.getElementById('unavailableMessage');

  // Global fallback helper. Only install if nothing else provides showMessageModal.
  // If the server-side partial is present it will expose `pushModal`/`showMessageModal`.
  if (!window.showMessageModal) {
    window.showMessageModal = function(message, title = 'Message', type = 'info') {
      // If the server partial was included and installed modal helpers, use them directly.
      if (window._modalRoot && typeof window.pushModal === 'function') {
        try { window.pushModal({ message, title, type }); return; } catch(e) { /* fall through to alert */ }
      }
      // fallback to simple alert
      alert(title + '\n\n' + message);
    };
  }
  const availabilityText = document.getElementById('availabilityText');
  const unavailableText = document.getElementById('unavailableText');
  
  // Provide friendly, accessible messages and set ARIA live region
  if (availabilityMessage) {
    availabilityMessage.setAttribute('role', 'status');
    availabilityMessage.setAttribute('aria-live', 'polite');
  }
  if (unavailableMessage) {
    unavailableMessage.setAttribute('role', 'status');
    unavailableMessage.setAttribute('aria-live', 'polite');
  }

  if (availableSlots > 0) {
    if (availabilityMessage && availabilityText) {
      availabilityText.textContent = `${availableSlots} open times — choose one to book`; // clearer action-oriented text
      availabilityMessage.style.display = 'block';
    }
    if (unavailableMessage) {
      unavailableMessage.style.display = 'none';
    }
  } else {
    if (unavailableMessage && unavailableText) {
      unavailableText.textContent = 'No open times on this date — try another day or contact the clinic';
      unavailableMessage.style.display = 'block';
    }
    if (availabilityMessage) {
      availabilityMessage.style.display = 'none';
    }
  }
}

// Helper function to format time for display
function formatTime(timeStr) {
  const [hours, minutes] = timeStr.split(':');
  const hour = parseInt(hours);
  const ampm = hour >= 12 ? 'PM' : 'AM';
  const displayHour = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour;
  return `${displayHour}:${minutes} ${ampm}`;
}

// Calendar state management - Initialize these first
let currentCalendarDate = getPHDate();
let currentDisplayedMonth = currentCalendarDate.getMonth();
let currentDisplayedYear = currentCalendarDate.getFullYear();
let currentSelectedDay = currentCalendarDate.getDate(); // Track selected day for day view
let currentWeekStart = null; // Start of the current week

// Dropdown toggle logic
const dropdownBtn = document.getElementById('viewDropdownBtn');
const dropdownMenu = document.getElementById('viewDropdownMenu');
const dropdownLabel = document.getElementById('viewDropdownLabel');
const viewOptions = dropdownMenu ? dropdownMenu.querySelectorAll('.dropdown-option') : [];
const views = {
  Day: document.getElementById('dayView'),
  Week: document.getElementById('weekView'),
  Month: document.getElementById('monthView')
};

// If user is a patient, hide the 'All' view option
if (window.userType === 'patient' && dropdownMenu) {
  const allOpt = dropdownMenu.querySelector('[data-view="All"]');
  if (allOpt) allOpt.classList.add('hidden');
}

// Show/hide dropdown
if (dropdownBtn && dropdownMenu) {
  dropdownBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    dropdownMenu.classList.toggle('hidden');
  });
  document.addEventListener('click', function() {
    dropdownMenu.classList.add('hidden');
  });
}

// Switch view logic
function switchView(view) {
  // Safety check - ensure variables are initialized
  if (typeof currentSelectedDay === 'undefined') {
    const today = getPHDate();
    currentSelectedDay = today.getDate();
    currentDisplayedMonth = today.getMonth();
    currentDisplayedYear = today.getFullYear();
  }

  if (dropdownLabel) dropdownLabel.textContent = view;
  viewOptions.forEach(opt => {
    if (opt.getAttribute('data-view') === view) {
      opt.classList.add('bg-gray-100');
    } else {
      opt.classList.remove('bg-gray-100');
    }
  });
  Object.keys(views).forEach(v => {
    if (v === view) {
      views[v].classList.remove('hidden');
    } else {
      views[v].classList.add('hidden');
    }
  });

  // Reset selected day to today when switching to day view
  if (view === 'Day') {
    const today = getPHDate();
    currentSelectedDay = today.getDate();
    currentDisplayedMonth = today.getMonth();
    currentDisplayedYear = today.getFullYear();
  }
  // Reset week state when switching to week view
  if (view === 'Week') {
    const today = getPHDate();
    // Always start week on Monday (ISO 8601)
    let dayOfWeek = today.getDay();
    // JS: Sunday=0, Monday=1, ..., Saturday=6
    let diffToMonday = (dayOfWeek === 0 ? -6 : 1) - dayOfWeek;
    currentWeekStart = new Date(today);
    currentWeekStart.setDate(today.getDate() + diffToMonday);
    currentDisplayedYear = currentWeekStart.getFullYear();
    currentDisplayedMonth = currentWeekStart.getMonth();
    currentSelectedDay = currentWeekStart.getDate();
  }

  updateCalendarDisplay();
  dropdownMenu.classList.add('hidden');
}


viewOptions.forEach(opt => {
  opt.addEventListener('click', function(e) {
    const view = opt.getAttribute('data-view');
    if (view === 'All') {
      if (window.userType === 'patient') {
        // For patients, show only their own appointments
        if (typeof showAllAppointments === 'function') {
          showAllAppointments(true); // pass flag to limit to patient
        } else {
          alert('All Appointments function not loaded.');
        }
      } else {
        if (typeof showAllAppointments === 'function') {
          showAllAppointments(false);
        } else {
          alert('All Appointments function not loaded.');
        }
      }
      dropdownMenu.classList.add('hidden');
      return;
    }
    switchView(view);
  });
});

// Initialize with Month view
switchView('Month');

// Helper function to get filtered appointments based on current branch filter
function getFilteredAppointments() {
  console.log('[getFilteredAppointments] Current filter:', window.currentBranchFilter);
  console.log('[getFilteredAppointments] Raw appointments:', window.appointments?.length || 0);
  
  if (window.currentBranchFilter === 'all') {
    return window.appointments || [];
  }
  
  const filtered = (window.appointments || []).filter(apt => {
    if (!apt.branch_name) {
      console.log('[getFilteredAppointments] Appointment missing branch_name:', apt.id);
      return false;
    }
    
    const branchName = apt.branch_name.toLowerCase();
    
    if (window.currentBranchFilter === 'nabua') {
      return branchName.includes('nabua');
    } else if (window.currentBranchFilter === 'iriga') {
      return branchName.includes('iriga');
    }
    
    return true;
  });
  
  console.log('[getFilteredAppointments] Filtered result:', filtered.length);
  return filtered;
}

// Initialize calendar display
updateCalendarDisplay();

function navigateMonth(direction) {
  // Safety check - ensure variables are initialized
  if (typeof currentDisplayedMonth === 'undefined' || typeof currentDisplayedYear === 'undefined') {
    const today = getPHDate();
    currentDisplayedMonth = today.getMonth();
    currentDisplayedYear = today.getFullYear();
  }
  
  currentDisplayedMonth += direction;
  
  if (currentDisplayedMonth > 11) {
    currentDisplayedMonth = 0;
    currentDisplayedYear++;
  } else if (currentDisplayedMonth < 0) {
    currentDisplayedMonth = 11;
    currentDisplayedYear--;
  }
  
  updateCalendarDisplay();
}

// New function for day navigation
function navigateDay(direction) {
  // Safety check - ensure variables are initialized
  if (typeof currentSelectedDay === 'undefined' || typeof currentDisplayedMonth === 'undefined' || typeof currentDisplayedYear === 'undefined') {
    const today = getPHDate();
    currentSelectedDay = today.getDate();
    currentDisplayedMonth = today.getMonth();
    currentDisplayedYear = today.getFullYear();
  }
  
  const currentView = dropdownLabel ? dropdownLabel.textContent : 'Month';
  console.log('navigateDay called with direction:', direction, 'currentView:', currentView);
  
  if (currentView === 'Day') {
    // Navigate by day
    const currentDate = getPHDate(`${currentDisplayedYear}-${String(currentDisplayedMonth + 1).padStart(2, '0')}-${String(currentSelectedDay).padStart(2, '0')}`);
    currentDate.setDate(currentDate.getDate() + direction);
    currentDisplayedYear = currentDate.getFullYear();
    currentDisplayedMonth = currentDate.getMonth();
    currentSelectedDay = currentDate.getDate();
    console.log('Day navigation - new date:', currentSelectedDay, 'month:', currentDisplayedMonth, 'year:', currentDisplayedYear);
    updateCalendarDisplay();
  } else {
    // For Week and Month views, use month navigation
    console.log('Month navigation - direction:', direction);
    navigateMonth(direction);
  }
}

function handleCalendarNav(direction) {
  const currentView = dropdownLabel ? dropdownLabel.textContent : 'Month';
  if (currentView === 'Day') {
    navigateDay(direction);
  } else if (currentView === 'Week') {
    navigateWeek(direction);
  } else {
    navigateMonth(direction);
  }
}

function navigateWeek(direction) {
  // If not set, initialize to the current week
  if (!currentWeekStart) {
    const today = getPHDate(`${currentDisplayedYear}-${String(currentDisplayedMonth + 1).padStart(2, '0')}-${String(currentSelectedDay).padStart(2, '0')}`);
    currentWeekStart = getPHDate(today);
    currentWeekStart.setDate(today.getDate() - today.getDay()); // Sunday as first day
  }
  // Move by 7 days
  currentWeekStart.setDate(currentWeekStart.getDate() + direction * 7);

  // Update displayed month/year/day to match the new week start
  currentDisplayedYear = currentWeekStart.getFullYear();
  currentDisplayedMonth = currentWeekStart.getMonth();
  currentSelectedDay = currentWeekStart.getDate();

  updateCalendarDisplay();
}

function goToToday() {
  const today = getPHDate();
  currentDisplayedMonth = today.getMonth();
  currentDisplayedYear = today.getFullYear();
  currentSelectedDay = today.getDate();
  updateCalendarDisplay();
}

function updateCalendarDisplay() {
  // Safety check - ensure variables are initialized
  if (typeof currentDisplayedMonth === 'undefined' || typeof currentDisplayedYear === 'undefined' || typeof currentSelectedDay === 'undefined') {
    const today = new Date();
    currentDisplayedMonth = today.getMonth();
    currentDisplayedYear = today.getFullYear();
    currentSelectedDay = today.getDate();
  }
  
  // Update calendar title based on current view
  const currentView = dropdownLabel ? dropdownLabel.textContent : 'Month';
  const titleElement = document.getElementById('calendarTitle');
  
  console.log('updateCalendarDisplay - currentView:', currentView, 'titleElement:', titleElement);
  
  if (titleElement) {
    if (currentView === 'Day') {
      // For day view, show the specific date
      const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                         'July', 'August', 'September', 'October', 'November', 'December'];
      const selectedDate = new Date(currentDisplayedYear, currentDisplayedMonth, currentSelectedDay);
      const dayName = selectedDate.toLocaleDateString('en-US', { weekday: 'long' });
      const monthName = monthNames[currentDisplayedMonth];
      const day = currentSelectedDay;
      const year = currentDisplayedYear;
      
      const newTitle = `${dayName}, ${monthName} ${day}, ${year}`;
      titleElement.textContent = newTitle;
      console.log('Day view title updated to:', newTitle);
    } else {
      // For month and week views, show month and year
      const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                         'July', 'August', 'September', 'October', 'November', 'December'];
      const newTitle = `${monthNames[currentDisplayedMonth]} ${currentDisplayedYear}`;
      titleElement.textContent = newTitle;
      console.log('Month/Week view title updated to:', newTitle);
    }
  }
  
  // Load availability for the visible month range then rebuild views so availability is shown
  try {
    const month = currentDisplayedMonth;
    const year = currentDisplayedYear;
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const start = `${year}-${String(month + 1).padStart(2, '0')}-01`;
    const end = `${year}-${String(month + 1).padStart(2, '0')}-${String(daysInMonth).padStart(2, '0')}`;
    loadAvailabilityRange(start, end).then(() => {
      rebuildCalendarGrid();
      // Update day view if it's currently active
      updateDayView();
      if (dropdownLabel && dropdownLabel.textContent === 'Week') updateWeekView();
    }).catch(err => {
      console.error('Failed to load availability:', err);
      rebuildCalendarGrid();
      updateDayView();
      if (dropdownLabel && dropdownLabel.textContent === 'Week') updateWeekView();
    });
  } catch (e) {
    console.error('updateCalendarDisplay availability error', e);
    rebuildCalendarGrid();
    updateDayView();
    if (dropdownLabel && dropdownLabel.textContent === 'Week') updateWeekView();
  }
}

// Listen for availability changes and reload the availability for the current displayed range
try {
  window.addEventListener('availability:changed', function(e){
    try{
      // Debug: indicate that the availability change event fired and show detail
      try { console.log('[availability:event] availability:changed fired', e && e.detail ? e.detail : null); } catch(ex){}
      // compute start/end from current displayed month/week/day
      if (typeof currentDisplayedYear === 'undefined' || typeof currentDisplayedMonth === 'undefined') return;
      const daysInMonth = new Date(currentDisplayedYear, currentDisplayedMonth + 1, 0).getDate();
      const start = `${currentDisplayedYear}-${String(currentDisplayedMonth + 1).padStart(2,'0')}-01`;
      const end = `${currentDisplayedYear}-${String(currentDisplayedMonth + 1).padStart(2,'0')}-${String(daysInMonth).padStart(2,'0')}`;
      loadAvailabilityRange(start, end).then(()=>{
        // Refresh appointments for currently selected day so timeline immediately shows approved/declined appointments
        try {
          const sel = typeof currentSelectedDay !== 'undefined' ? `${currentDisplayedYear}-${String(currentDisplayedMonth + 1).padStart(2,'0')}-${String(currentSelectedDay).padStart(2,'0')}` : null;
          if (sel && typeof refreshAppointmentsForDate === 'function') {
            refreshAppointmentsForDate(sel).catch(()=>{});
          }
        } catch(err) { console.warn('refreshAppointmentsForDate failed', err); }
        rebuildCalendarGrid(); updateDayView(); if (document.getElementById('weekView')) updateWeekView();
      }).catch(()=>{});
    }catch(err){ console.error('availability:changed handler', err); }
  });
} catch(e) { console && console.warn && console.warn('availability event hookup failed', e); }

// Cross-tab sync: listen for BroadcastChannel messages and storage events so availability changes
// propagate to other open tabs/windows immediately (fallbacks included).
try {
  if (typeof window !== 'undefined' && 'BroadcastChannel' in window) {
    try {
      window._availabilityBroadcast = new BroadcastChannel('availability');
      window._availabilityBroadcast.onmessage = function(evt) {
        try {
          // evt.data should contain { detail: {...} }
          window.dispatchEvent(new CustomEvent('availability:changed', { detail: evt.data && evt.data.detail ? evt.data.detail : {} }));
        } catch(e) { /* swallow */ }
      };
    } catch(e) { /* ignore BroadcastChannel init errors */ }
  }
  // storage event fallback (fires in other tabs when localStorage key changes)
  window.addEventListener('storage', function(evt){
    try {
      if (!evt) return;
      if (evt.key === '_availability_update') {
        try { window.dispatchEvent(new Event('availability:changed')); } catch(e){}
      }
    } catch(e) { /* noop */ }
  });
} catch(e) { /* ignore */ }

// Fetch availability events between start and end dates (YYYY-MM-DD). Sets window.availabilityEvents
function loadAvailabilityRange(start, end) {
  const debug = !!window.__AVAILABILITY_DEBUG;
  // Return a promise
  // If a dentist is selected on the page, include dentist_id so server returns scoped availability
  let dentistId = null;
  const dentistEl = document.querySelector('select[name="dentist_id"]') || document.getElementById('dentistSelect');
  if (dentistEl && dentistEl.value) dentistId = dentistEl.value;

  // Guard against pages that don't set window.baseUrl to avoid creating 'undefined' in URLs
  const base = (typeof window !== 'undefined' && window.baseUrl) ? window.baseUrl : '';
  let url = base + '/calendar/availability-events?start=' + encodeURIComponent(start) + '&end=' + encodeURIComponent(end);
  if (dentistId) url += '&dentist_id=' + encodeURIComponent(dentistId);

  if (debug) console.debug('[availability] loadAvailabilityRange url=', url, 'start=', start, 'end=', end, 'dentistId=', dentistId);

  return fetch(url, {
    credentials: 'same-origin',
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  }).then(r => {
    if (debug) console.debug('[availability] response status', r.status, r.statusText);
    return r.json();
  }).then(j => {
    if (debug) console.debug('[availability] raw json', j);
    if (j && (j.success || Array.isArray(j.events)) && Array.isArray(j.events)) {
      // Normalize datetimes to ISO 8601 so `new Date(...)` behaves consistently across browsers.
      window.availabilityEvents = j.events.map(ev => {
        const normalize = s => {
          if (!s) return s;
          // If already looks like ISO with timezone (contains 'T' and offset), return as-is
          if (s.indexOf('T') !== -1 && /[+-]\d\d:\d\d$/.test(s)) return s;
          // If contains 'T' but no timezone, assume server intends local ISO and append Manila offset
          if (s.indexOf('T') !== -1 && !/[+-]\d\d:\d\d$/.test(s)) return s + '+08:00';
          // Fallback: replace first space with 'T' and append Manila offset
          let out = s.indexOf('T') !== -1 ? s : s.replace(' ', 'T');
          if (!/[+-]\d\d:\d\d$/.test(out)) out = out + '+08:00';
          return out;
        };
        const before = { start: ev.start, end: ev.end };
        const normalized = { start: normalize(ev.start), end: normalize(ev.end) };
        if (debug) console.debug('[availability] event normalize', ev.id || ev, before, '->', normalized);
        return Object.assign({}, ev, normalized);
      });
    } else {
      window.availabilityEvents = [];
    }
    if (debug) console.debug('[availability] set window.availabilityEvents count=', window.availabilityEvents.length);
    return window.availabilityEvents;
  }).catch(err => {
    console.error('loadAvailabilityRange failed', err);
    window.availabilityEvents = window.availabilityEvents || [];
    throw err;
  });
}

// Start a lightweight poller to refresh availability for the currently displayed range.
// This makes availability changes created by any user appear for other open clients within ~30s.
let _availabilityPollHandle = null;
function startAvailabilityPolling() {
  if (_availabilityPollHandle) return;
  _availabilityPollHandle = setInterval(() => {
    try {
      const month = currentDisplayedMonth;
      const year = currentDisplayedYear;
      const daysInMonth = new Date(year, month + 1, 0).getDate();
      const start = `${year}-${String(month + 1).padStart(2, '0')}-01`;
      const end = `${year}-${String(month + 1).padStart(2, '0')}-${String(daysInMonth).padStart(2, '0')}`;
      loadAvailabilityRange(start, end).then(() => {
        try { window.dispatchEvent(new Event('availability:changed')); } catch(e){}
      }).catch(()=>{});
    } catch(e) { /* swallow */ }
  }, 30000); // poll every 30s
}

// Start polling once the page is loaded
document.addEventListener('DOMContentLoaded', function(){ startAvailabilityPolling(); });

function updateDayView() {
  const dayView = document.getElementById('dayView');
  if (!dayView) return;
  
  // Get the current selected date using the tracked selected day
  const selectedDate = `${currentDisplayedYear}-${String(currentDisplayedMonth + 1).padStart(2, '0')}-${String(currentSelectedDay).padStart(2, '0')}`;
  
  // Update day view for the selected day
  updateDayViewForDate(selectedDate);
}

function updateDayViewForDate(selectedDate) {
  const dayView = document.getElementById('dayView');
  if (!dayView) { console.error('[DayView] #dayView not found'); return; }
  const tbody = dayView.querySelector('tbody');
  if (!tbody) { console.error('[DayView] tbody not found in #dayView'); return; }
  const appointments = getFilteredAppointments(); // Use branch-filtered appointments
  
  console.info(`[DayView] Updating for date: ${selectedDate}, total filtered appointments: ${appointments.length}`);
  
  // Filter all appointments for the selected date
  const dayAppointments = appointments.filter(apt => {
    // Normalize both selectedDate and appointment date to YYYY-MM-DD
    let apt_date = null;
    if (apt.appointment_date) {
      apt_date = apt.appointment_date.length > 10 ? apt.appointment_date.substring(0, 10) : apt.appointment_date;
    } else if (apt.appointment_datetime) {
      apt_date = apt.appointment_datetime.substring(0, 10);
    }
    // Remove any whitespace and compare
    return apt_date && apt_date.trim() === selectedDate.trim();
  });
  
  console.info(`[DayView] Found ${dayAppointments.length} appointments for ${selectedDate}`);
  if (dayAppointments.length > 0) {
    console.debug('[DayView] Sample appointments:', dayAppointments.slice(0, 3));
  }
  
  if (!Array.isArray(appointments)) console.error('[DayView] getFilteredAppointments() is not an array:', appointments);
  if (appointments.length === 0) console.warn('[DayView] No appointments found in getFilteredAppointments()');
  if (dayAppointments.length === 0) console.warn(`[DayView] No appointments found for selectedDate: ${selectedDate}`);

  // --- Update all-day row ---
  const allDayRow = tbody.querySelector('tr:first-child td:last-child');
  if (!allDayRow) { console.error('[DayView] All-day row cell not found'); }
  if (allDayRow) {
    // Find all-day appointments (no time or time is 00:00)
    const allDayAppointments = dayAppointments.filter(apt => {
      const apt_time = apt.appointment_time || (apt.appointment_datetime ? apt.appointment_datetime.substring(11, 16) : null);
      return !apt_time || apt_time === '00:00';
    });
    allDayRow.innerHTML = '';
    allDayRow.onclick = () => openAddAppointmentPanelWithTime(selectedDate, '');
    if (allDayAppointments.length === 0) console.info(`[DayView] No all-day appointments for ${selectedDate}`);
    allDayAppointments.forEach(apt => {
      // Color logic matches PHP
      let bgColor, textColor, statusText;
      switch ((apt.status || '').toLowerCase()) {
        case 'confirmed': bgColor = 'bg-green-100'; textColor = 'text-green-800'; statusText = 'Confirmed'; break;
        case 'cancelled': bgColor = 'bg-red-100'; textColor = 'text-red-800'; statusText = 'Cancelled'; break;
        case 'completed': bgColor = 'bg-blue-100'; textColor = 'text-blue-800'; statusText = 'Completed'; break;
        case 'pending_approval': bgColor = 'bg-yellow-100'; textColor = 'text-yellow-800'; statusText = 'Pending'; break;
        default: bgColor = 'bg-gray-100'; textColor = 'text-gray-800'; statusText = (apt.status || 'Scheduled').charAt(0).toUpperCase() + (apt.status || 'Scheduled').slice(1); break;
      }
      const div = document.createElement('div');
      div.className = `${bgColor} rounded p-1 sm:p-2 text-xs ${textColor} mb-1 hover:bg-opacity-80 transition-colors cursor-pointer`;
      div.innerHTML = `
        <div class=\"flex flex-col sm:flex-row sm:items-center sm:justify-between\">
          <div class=\"flex flex-col sm:flex-row sm:items-center\">
            <span class=\"font-bold text-gray-700 text-xs sm:text-sm\">${apt.patient_name || 'Appointment'}</span>
            <span class="text-gray-500 text-xs sm:ml-2">${formatAppointmentTime(apt)}</span>
          </div>
          <span class=\"text-xs ${textColor} font-semibold mt-1 sm:mt-0\">${statusText}</span>
        </div>
        ${apt.remarks ? `<div class='text-gray-600 italic text-xs mt-1'>${apt.remarks}</div>` : ''}
      `;
      div.onclick = function(e) { e.stopPropagation(); window.showDayAppointmentDetails(apt.id); };
      allDayRow.appendChild(div);
    });
  }

  // --- Update hourly slots (use dynamic start/end from server-side hidden div) ---
  const hourlyRows = tbody.querySelectorAll('tr:not(:first-child)');
  // Determine start hour from hidden div
  let rowStartHour = 6;
  try {
    const dayOps = document.getElementById('dayViewOperatingHours');
    if (dayOps) {
      const s = parseInt(dayOps.getAttribute('data-start-hour'));
      if (!isNaN(s)) rowStartHour = s;
    }
  } catch (e) { /* ignore */ }

  hourlyRows.forEach((row, index) => {
    const hour = rowStartHour + index;
    const appointmentCell = row.querySelector('td:last-child');
    if (!appointmentCell) { console.error(`[DayView] Hourly cell not found for hour ${hour}`); return; }
    appointmentCell.innerHTML = '';
    const time = String(hour).padStart(2, '0') + ':00';
    appointmentCell.onclick = () => openAddAppointmentPanelWithTime(selectedDate, time);
    // Find appointments for this hour
    const hourAppointments = dayAppointments.filter(apt => {
      const apt_time = apt.appointment_time || (apt.appointment_datetime ? apt.appointment_datetime.substring(11, 16) : null);
      if (!apt_time) return false;
      const apt_hour = parseInt(apt_time.split(':')[0]);
      return apt_hour === hour;
    });
    if (hourAppointments.length === 0) console.info(`[DayView] No appointments for hour ${hour} on ${selectedDate}`);
    hourAppointments.forEach(apt => {
      let bgColor, textColor, statusText;
      switch ((apt.status || '').toLowerCase()) {
        case 'confirmed': bgColor = 'bg-green-100'; textColor = 'text-green-800'; statusText = 'Confirmed'; break;
        case 'cancelled': bgColor = 'bg-red-100'; textColor = 'text-red-800'; statusText = 'Cancelled'; break;
        case 'completed': bgColor = 'bg-blue-100'; textColor = 'text-blue-800'; statusText = 'Completed'; break;
        case 'pending_approval': bgColor = 'bg-yellow-100'; textColor = 'text-yellow-800'; statusText = 'Pending'; break;
        default: bgColor = 'bg-gray-100'; textColor = 'text-gray-800'; statusText = (apt.status || 'Scheduled').charAt(0).toUpperCase() + (apt.status || 'Scheduled').slice(1); break;
      }
      const div = document.createElement('div');
      div.className = `${bgColor} rounded p-1 sm:p-2 text-xs ${textColor} mb-1 hover:bg-opacity-80 transition-colors cursor-pointer`;
      div.innerHTML = `
        <div class=\"flex flex-col sm:flex-row sm:items-center sm:justify-between\">
          <div class=\"flex flex-col sm:flex-row sm:items-center\">
            <span class=\"font-bold text-gray-700 text-xs sm:text-sm\">${apt.patient_name || 'Appointment'}</span>
            <span class=\"text-gray-500 text-xs sm:ml-2\">(${apt.appointment_time})</span>
          </div>
          <span class=\"text-xs ${textColor} font-semibold mt-1 sm:mt-0\">${statusText}</span>
        </div>
        ${apt.remarks ? `<div class='text-gray-600 italic text-xs mt-1'>${apt.remarks}</div>` : ''}
      `;
      div.onclick = function(e) { e.stopPropagation(); window.showDayAppointmentDetails(apt.id); };
      appointmentCell.appendChild(div);
    });
    // Check availability blocks for this specific hour
    try {
      const cellDate = selectedDate; // YYYY-MM-DD
      const hourStart = cellDate + ' ' + String(hour).padStart(2,'0') + ':00:00';
      const hourEnd = new Date(new Date(hourStart).getTime() + 60*60*1000).toISOString().substring(0,19).replace('T',' ');
      const hs = new Date(hourStart);
      const he = new Date(hourEnd);
      // Compute intersections manually so we can debug per-event
      const events = (window.availabilityEvents || []);
      let blocked = false;
      for (let i = 0; i < events.length; i++) {
        const ev = events[i];
        const evStart = getPHDate(ev.start);
        const evEnd = getPHDate(ev.end);
        const intersects = evStart < he && evEnd > hs;
        if (window.__AVAILABILITY_DEBUG) console.debug('[availability][hour-check]', { cellDate, hour, evId: ev.id, evStartRaw: ev.start, evEndRaw: ev.end, evStart, evEnd, hs, he, intersects });
        if (intersects) { blocked = true; break; }
      }
      if (blocked) {
        const blk = document.createElement('div');
        blk.className = 'bg-red-100 text-red-700 text-xs rounded p-1 mt-1';
        blk.textContent = 'Blocked — unavailable';
        appointmentCell.appendChild(blk);
      }
    } catch(e) { console.error('hour availability check error', e); }
// Show day view appointment details panel (global, reusing modal logic)
window.showDayAppointmentDetails = function(appointmentId) {
  const appointment = (window.appointments || []).find(apt => apt.id == appointmentId);
  if (!appointment) {
    alert('Appointment not found');
    return;
  }
  let modal = document.getElementById('dayAppointmentsModal');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'dayAppointmentsModal';
    modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40';
    modal.innerHTML = `
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 relative animate-fade-in">
        <button id="closeDayAppointmentsModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl font-bold">&times;</button>
        <h2 class="text-xl font-bold mb-4 text-blue-700">Appointment Details</h2>
        <div id="dayAppointmentsList"></div>
      </div>
    `;
    document.body.appendChild(modal);
  }
  // Populate details
  const list = modal.querySelector('#dayAppointmentsList');
  // Respect patient privacy: do not show patient name to patient users
  let patientLine = '';
  if (window.userType !== 'patient') {
    patientLine = `<div class="mb-2"><span class="font-semibold">Patient:</span> ${appointment.patient_name || ''}</div>`;
  }
  list.innerHTML = patientLine + `
    <div class="mb-2"><span class="font-semibold">Date:</span> ${appointment.appointment_date || (appointment.appointment_datetime ? appointment.appointment_datetime.substring(0,10) : '')}</div>
    <div class="mb-2"><span class="font-semibold">Time:</span> ${appointment.appointment_time || (appointment.appointment_datetime ? appointment.appointment_datetime.substring(11,16) : '')}</div>
    <div class="mb-2"><span class="font-semibold">Status:</span> ${appointment.status || ''}</div>
    <div class="mb-2"><span class="font-semibold">Remarks:</span> ${appointment.remarks || ''}</div>
    <button class="bg-slate-600 hover:bg-slate-700 text-white px-3 py-1 rounded text-sm mt-2" onclick="editAppointment(${appointment.id})">Edit</button>
  `;
  modal.classList.remove('hidden');
  modal.querySelector('#closeDayAppointmentsModal').onclick = () => {
    modal.classList.add('hidden');
  };
}
  });
}

function rebuildCalendarGrid() {
  const monthView = document.getElementById('monthView');
  if (!monthView) return;
  // Calculate first day of month and days in month
  const firstDay = new Date(currentDisplayedYear, currentDisplayedMonth, 1).getDay();
  const daysInMonth = new Date(currentDisplayedYear, currentDisplayedMonth + 1, 0).getDate();
  // Find the table body
  const tbody = document.getElementById('monthViewBody');
  if (!tbody) return;
  // Clear existing rows
  tbody.innerHTML = '';
  // Build calendar grid
  let day = 1;
  let weeks = [];
  // Calculate weeks
  while (day <= daysInMonth) {
    let week = [];
    for (let i = 0; i < 7; i++) {
      if ((weeks.length === 0 && i < firstDay) || day > daysInMonth) {
        week.push({ day: '', date: '', inactive: true });
      } else {
        const dateStr = `${currentDisplayedYear}-${String(currentDisplayedMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        week.push({ day: day, date: dateStr, inactive: false });
        day++;
      }
    }
    weeks.push(week);
  }
  // Build HTML
  weeks.forEach(week => {
    const row = document.createElement('tr');
    week.forEach(cell => {
      const td = document.createElement('td');
      td.className = 'h-28 align-top text-xs transition';
      if (cell.inactive) {
        td.className += ' text-gray-300 bg-white';
        td.textContent = cell.day;
      } else {
        const todayStr = new Date();
        const cellDate = new Date(cell.date);
        todayStr.setHours(0,0,0,0);
        cellDate.setHours(0,0,0,0);
        const isPast = cell.date && cellDate < todayStr;
        if (isPast) {
          // Past day: gray, not clickable, no pointer, no hover, no tooltip
          td.className += ' text-gray-300 bg-gray-50 cursor-not-allowed';
          td.textContent = cell.day;
        } else {
          // Today or future: interactive
          td.className += ' text-gray-700 bg-white hover:bg-gray-50 cursor-pointer';
          td.setAttribute('data-date', cell.date);
          td.onclick = () => openAddAppointmentPanelWithTime(cell.date, '');
          td.textContent = cell.day;
        }
        // Appointments indicator (show for all days if toggle ON, else only for today/future)
        const appointments = getFilteredAppointments(); // Use branch-filtered appointments
        // Use consistent date formatting (YYYY-MM-DD) and timezone for comparison
        const cellDateStr = (() => {
          const d = getPHDate(cell.date);
          return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        })();
        const dayAppointments = appointments.filter(apt => {
          let apt_date = null;
          if (apt.appointment_date) {
            apt_date = apt.appointment_date.length > 10 ? apt.appointment_date.substring(0, 10) : apt.appointment_date;
          } else if (apt.appointment_datetime) {
            apt_date = apt.appointment_datetime.substring(0, 10);
          }
          if (!apt_date) return false;
          // Normalize to PH timezone for comparison
          const aptDateObj = getPHDate(apt_date);
          const aptDateStr = aptDateObj.getFullYear() + '-' + String(aptDateObj.getMonth() + 1).padStart(2, '0') + '-' + String(aptDateObj.getDate()).padStart(2, '0');
          return aptDateStr === cellDateStr;
        });
        let showCount = true;
        // Patients should be able to see their past and current appointments (read-only)
        if (window.userType === 'patient') {
          showCount = true;
        } else {
          if (typeof window.showPastAppointments === 'function') {
            if (isPast && !window.showPastAppointments()) showCount = false;
          }
        }
        if (dayAppointments.length > 0 && showCount) {
          td.classList.add('relative');
          const bgOverlay = document.createElement('div');
          bgOverlay.className = 'absolute inset-0 bg-blue-50 border-2 border-blue-100 rounded-lg opacity-80 pointer-events-none';
          td.appendChild(bgOverlay);
          const appointmentDiv = document.createElement('div');
          appointmentDiv.className = 'relative z-10 mt-1 text-xs text-blue-700 font-medium flex items-center justify-center cursor-pointer';
          const span = document.createElement('span');
          span.className = 'bg-blue-100 px-2 py-0.5 rounded-full border border-blue-200';
          span.innerHTML = `<i class=\"fas fa-calendar-check mr-1 text-blue-600\"></i>${dayAppointments.length} apt${dayAppointments.length > 1 ? 's' : ''}`;
          appointmentDiv.appendChild(span);
          // On click, show a list of appointments for that day
          appointmentDiv.onclick = function(e) {
            e.stopPropagation();
            showDayAppointmentsModal(dayAppointments);
          };
          td.appendChild(appointmentDiv);
        }
        // Show availability information for the date
        try {
          const events = (window.availabilityEvents || []);
          const cellDateObj = getPHDate(cell.date);
          cellDateObj.setHours(0,0,0,0);
          const startOfDay = new Date(cellDateObj);
          const endOfDay = new Date(cellDateObj);
          endOfDay.setHours(23,59,59,999);

          // Separate explicit blocks (ad-hoc / emergency / day_off with is_recurring==0)
          const explicitBlocks = events.filter(ev => {
            // consider only explicit (non-recurring) blocks for overlays
            const isRecurring = Number(ev.is_recurring) === 1;
            if (isRecurring) return false;
            const evStart = getPHDate(ev.start);
            const evEnd = getPHDate(ev.end);
            return evStart <= endOfDay && evEnd >= startOfDay;
          });

          // For recurring schedules (is_recurring==1), derive the working hours for the day
          const recurringForDay = events.filter(ev => Number(ev.is_recurring) === 1).map(ev => {
            // Expect recurring events to have start/end times on that date; parse times only
            return {
              id: ev.id,
              type: ev.type,
              start: getPHDate(ev.start),
              end: getPHDate(ev.end),
              notes: ev.notes || ''
            };
          }).filter(ev => {
            // Ensure the recurring occurrence actually intersects the day (some recurrences may be outside bounds)
            return ev.start <= endOfDay && ev.end >= startOfDay;
          });

          // Render explicit blocks as overlays/badges (these are true 'blocked' times)
          if (explicitBlocks && explicitBlocks.length) {
            td.classList.add('relative');
            const overlay = document.createElement('div');
            overlay.className = 'absolute inset-0 rounded-lg pointer-events-none';
            // choose color by first explicit block type (day_off > emergency > other)
            const t0 = (explicitBlocks[0].type || '').toLowerCase();
            if (t0 === 'day_off' || t0 === 'day-off') overlay.style.background = 'rgba(239, 68, 68, 0.06)';
            else if (t0 === 'emergency') overlay.style.background = 'rgba(249, 115, 22, 0.06)';
            else overlay.style.background = 'rgba(59, 130, 246, 0.04)';
            td.appendChild(overlay);
            const badge = document.createElement('div');
            badge.className = 'relative z-10 mt-1 text-xs text-red-700 font-semibold flex items-center justify-center';
            badge.innerHTML = `<span class='bg-white/80 px-2 py-0.5 rounded-full border text-xs text-red-700'>${explicitBlocks.length} block${explicitBlocks.length>1?'s':''}</span>`;
            badge.onclick = function(e){ e.stopPropagation(); showAvailabilityListModal(explicitBlocks, cell.date); };
            td.appendChild(badge);
          }

          // Render friendly recurring-hours indicator (available window) if present
          if (recurringForDay && recurringForDay.length) {
            // Prefer the earliest start and latest end among recurring rules for the day
            let earliest = recurringForDay.reduce((acc, cur) => acc && acc.start < cur.start ? acc : cur.start, null);
            let latest = recurringForDay.reduce((acc, cur) => acc && acc.end > cur.end ? acc : cur.end, null);
            // If reduce initial nulls, fallback compute properly
            if (!earliest) earliest = recurringForDay[0].start;
            if (!latest) latest = recurringForDay[0].end;

            const formatTimeLocal = dt => {
              const d = new Date(dt.getTime());
              // Show in hh:mm AM/PM in Manila local time
              // Convert UTC instant to Manila components
              const manilaMs = dt.getTime() + (8 * 60 * 60 * 1000);
              const md = new Date(manilaMs);
              let hh = md.getUTCHours();
              const mm = String(md.getUTCMinutes()).padStart(2, '0');
              const ampm = hh >= 12 ? 'PM' : 'AM';
              hh = hh % 12; if (hh === 0) hh = 12;
              return `${hh}:${mm} ${ampm}`;
            };

            const info = document.createElement('div');
            info.className = 'relative z-10 mt-1 text-xs text-green-700 font-medium flex items-center justify-center';
            info.innerHTML = `<span class='bg-white/90 px-2 py-0.5 rounded-full border text-xs text-green-700'>Available ${formatTimeLocal(earliest)} — ${formatTimeLocal(latest)}</span>`;
            // Attach click to show recurring hours details
            info.onclick = function(e){ e.stopPropagation(); showAvailabilityListModal(recurringForDay, cell.date); };
            td.appendChild(info);
          }
        } catch(e) { console.error('availability render error', e); }
// Show modal with list of appointments for a day, each clickable for editing
function showDayAppointmentsModal(dayAppointments) {
  let modal = document.getElementById('dayAppointmentsModal');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'dayAppointmentsModal';
    modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40';
    modal.innerHTML = `
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 relative animate-fade-in">
        <button id="closeDayAppointmentsModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl font-bold">&times;</button>
        <h2 class="text-xl font-bold mb-4 text-blue-700">Appointments</h2>
        <div id="dayAppointmentsList" class="max-h-[60vh] overflow-y-auto"></div>
      </div>
    `;
    document.body.appendChild(modal);
  }
  // Populate the list
  const list = modal.querySelector('#dayAppointmentsList');
  if (dayAppointments.length === 0) {
    list.innerHTML = '<div class="text-gray-500">No appointments found.</div>';
  } else {
    computeQueuePositions(dayAppointments);
    list.innerHTML = dayAppointments.map(apt => `
      <div class="border rounded p-2 mb-2 bg-blue-50 hover:bg-blue-100 cursor-pointer" onclick="editAppointment(${apt.id})">
        <div class="font-semibold text-blue-800">${(window.userType === 'patient') ? 'Appointment' : (apt.patient_name || 'Unknown')}</div>
        <div class="text-xs text-gray-500">${formatAppointmentTime(apt)} ${apt._queuePosition && apt._queuePosition > 1 ? `<span class="text-xs text-gray-500">#${apt._queuePosition}</span>` : ''}</div>
        <div class="text-xs text-gray-400">${apt.remarks ? apt.remarks : ''}</div>
      </div>
    `).join('');
  }
  modal.classList.remove('hidden');
  modal.querySelector('#closeDayAppointmentsModal').onclick = () => {
    modal.classList.add('hidden');
  };
}
// Show modal listing availability blocks for a date
function showAvailabilityListModal(availList, date) {
  let modal = document.getElementById('availabilityListModal');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'availabilityListModal';
    modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40';
    modal.innerHTML = `
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 relative animate-fade-in">
        <button id="closeAvailabilityListModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl font-bold">&times;</button>
        <h2 class="text-xl font-bold mb-4 text-blue-700">Availability for ${date}</h2>
        <div id="availabilityListContent" class="max-h-[60vh] overflow-y-auto"></div>
      </div>
    `;
    document.body.appendChild(modal);
  }
  const content = modal.querySelector('#availabilityListContent');
  if (!availList || availList.length === 0) {
    content.innerHTML = '<div class="text-gray-500">No availability blocks</div>';
  } else {
    content.innerHTML = availList.map(a => `
      <div class="border rounded p-2 mb-2">
        <div class="font-semibold">${a.title || a.type}</div>
        <div class="text-xs text-gray-500">${(new Date(a.start)).toLocaleString()} — ${(new Date(a.end)).toLocaleString()}</div>
        <div class="text-sm text-gray-600 mt-1">${a.notes || ''}</div>
      </div>
    `).join('');
  }
  modal.classList.remove('hidden');
  modal.querySelector('#closeAvailabilityListModal').onclick = () => { modal.classList.add('hidden'); };
}
      }
      row.appendChild(td);
    });
    tbody.appendChild(row);
  });
}

function updateWeekView() {
  const weekView = document.getElementById('weekView');
  if (!weekView) return;
  const weekDaysHeaderRow = document.getElementById('weekDaysHeaderRow');
  const weekViewBody = document.getElementById('weekViewBody');
  if (!weekDaysHeaderRow || !weekViewBody) return;

  const appointments = getFilteredAppointments();
  console.info(`[WeekView] Updating with ${appointments.length} filtered appointments`);

  // Checkbox: show past appointments? (shared toggle)
  const showPastCheckbox = document.getElementById('showPastAppointmentsToggle');
  let showPast = showPastCheckbox && showPastCheckbox.checked;
  // Patients always see past/current appointments in week view (read-only)
  if (window.userType === 'patient') {
    showPast = true;
  }

  // Calculate week days based on currentWeekStart
  let weekDays = [];
  let start = currentWeekStart ? new Date(currentWeekStart) : new Date(currentDisplayedYear, currentDisplayedMonth, currentSelectedDay);
  start.setDate(start.getDate() - start.getDay()); // Sunday as first day
  for (let i = 0; i < 7; i++) {
    let d = new Date(start);
    d.setDate(start.getDate() + i);
    weekDays.push({
      label: d.toLocaleDateString('en-US', { weekday: 'short', month: 'numeric', day: 'numeric' }),
      date: d.toISOString().slice(0, 10)
    });
  }

  // Render header (replace all except first cell)
  weekDaysHeaderRow.innerHTML = '<th class="w-24 text-xs text-left text-gray-400 font-normal"></th>' +
    weekDays.map(wd => {
      // Highlight today
      const today = new Date();
      const wdDate = new Date(wd.date);
      const isToday = wdDate.toDateString() === today.toDateString();
      return `<th class="text-center text-xs font-medium ${isToday ? 'bg-blue-100 text-blue-800' : 'text-gray-500'}">${wd.label}<br><span class="font-normal">${isToday ? 'Today' : 'Others'}</span></th>`;
    }).join('');

  // Render body
  let html = '';
  // All-day row
  html += '<tr><td class="bg-gray-100 text-xs text-gray-400 py-2 px-2 align-top">all-day</td>' +
    weekDays.map(wd => {
      const today = new Date();
      const wdDate = new Date(wd.date);
      wdDate.setHours(0,0,0,0); today.setHours(0,0,0,0);
      const isToday = wdDate.getTime() === today.getTime();
      const isPast = wdDate < today;
      if (!showPast && !isToday) {
        // Only today is enabled
        return `<td class="bg-gray-50 text-gray-300 cursor-not-allowed"></td>`;
      }
      if (!showPast && isToday) {
        return `<td class="bg-gray-100 cursor-pointer hover:bg-blue-100" onclick="openAddAppointmentPanelWithTime('${wd.date}', '')"></td>`;
      }
      if (showPast && (isPast || isToday)) {
        return `<td class="bg-gray-100 cursor-pointer hover:bg-blue-100" onclick="openAddAppointmentPanelWithTime('${wd.date}', '')"></td>`;
      }
      // Future days always disabled
      return `<td class="bg-gray-50 text-gray-300 cursor-not-allowed"></td>`;
    }).join('') + '</tr>';
  // Hourly rows
  // Get per-day operating hours for the week
  let weekOperatingHours = {};
  let globalStartHour = 8, globalEndHour = 20; // Default fallback
  
  try {
    // Try to get branch ID
    let branchId = null;
    const branchSelect = document.querySelector('select[name="branch"], select[name="branch_id"]');
    branchId = branchSelect ? branchSelect.value : null;
    if (!branchId) {
      branchId = window.currentBranchId || window.selectedBranchId || '1';
    }
    
    // Fetch operating hours for each day
    if (window.calendarCore && typeof window.calendarCore.fetchOperatingHours === 'function') {
      window.calendarCore.fetchOperatingHours(branchId).then(operatingHours => {
        if (operatingHours) {
          weekDays.forEach(wd => {
            const dayHours = window.calendarCore.getOperatingHoursForDate(operatingHours, wd.date);
            weekOperatingHours[wd.date] = dayHours;
          });
          
          // Calculate global start/end hours for the grid
          const validHours = Object.values(weekOperatingHours).filter(h => !h.closed && h.startHour !== null);
          if (validHours.length > 0) {
            globalStartHour = Math.min(...validHours.map(h => h.startHour));
            globalEndHour = Math.max(...validHours.map(h => h.endHour));
          }
          
          // Re-render the week view with the updated hours
          renderWeekGrid();
        }
      }).catch(e => {
        console.warn('[WeekView] Failed to fetch operating hours, using defaults:', e);
        renderWeekGrid();
      });
    } else {
      // Fallback to day view data if available
      const dayOps = document.getElementById('dayViewOperatingHours');
      if (dayOps) {
        const s = parseInt(dayOps.getAttribute('data-start-hour'));
        const e = parseInt(dayOps.getAttribute('data-end-hour'));
        if (!isNaN(s) && !isNaN(e)) {
          globalStartHour = s;
          globalEndHour = e;
        }
      }
      renderWeekGrid();
    }
  } catch (e) {
    console.warn('[WeekView] Error setting up operating hours:', e);
    renderWeekGrid();
  }
  
  function renderWeekGrid() {

  for (let h = globalStartHour; h <= globalEndHour; h++) {
    const time = String(h).padStart(2, '0') + ':00';
    html += `<tr><td class="text-xs text-gray-400 py-2 px-2 align-top border-t">${h <= 12 ? h : h-12}${h < 12 ? 'am' : 'pm'}</td>`;
    weekDays.forEach(wd => {
      const today = new Date();
      const wdDate = new Date(wd.date);
      wdDate.setHours(0,0,0,0); today.setHours(0,0,0,0);
      const isToday = wdDate.getTime() === today.getTime();
      const isPast = wdDate < today;
      
      // Check if this hour is within the day's operating hours
      const dayHours = weekOperatingHours[wd.date];
      const isWithinOperatingHours = dayHours && !dayHours.closed && 
        h >= dayHours.startHour && h < dayHours.endHour;
      
      if (!showPast && !isToday) {
        html += `<td class="border-t bg-gray-50 text-gray-300 cursor-not-allowed"></td>`;
        return;
      }
      
      // If day is closed or hour is outside operating hours, show as unavailable
      if (!isWithinOperatingHours) {
        html += `<td class="border-t bg-gray-100 text-gray-400" title="Outside operating hours">
          <div class="text-xs opacity-50">${dayHours?.closed ? 'Closed' : 'N/A'}</div>
        </td>`;
        return;
      }
        
        if (!showPast && isToday) {
          // Only today
          // Find appointments for this day/hour
          const appointments = getFilteredAppointments().filter(apt => {
            const apt_date = apt.appointment_date || (apt.appointment_datetime ? apt.appointment_datetime.substring(0, 10) : null);
            const apt_time = apt.appointment_time || (apt.appointment_datetime ? apt.appointment_datetime.substring(11, 16) : null);
            if (!apt_date || !apt_time) return false;
            const apt_hour = parseInt(apt_time.split(':')[0]);
            return apt_date === wd.date && apt_hour === h;
          });
          html += `<td class="border-t cursor-pointer hover:bg-blue-50 min-h-12 p-1 sm:p-2" onclick="openAddAppointmentPanelWithTime('${wd.date}', '${time}')">`;
          appointments.forEach(apt => {
            html += `
              <div class="bg-blue-100 rounded p-1 sm:p-2 text-xs text-blue-800 mb-1 hover:bg-opacity-80 transition-colors cursor-pointer" onclick="event.stopPropagation();showWeekAppointmentDetails(${apt.id})">
                <span class="font-bold text-blue-900 text-xs sm:text-sm">${(window.userType === 'patient') ? 'Appointment' : (apt.patient_name || 'Appointment')}</span>
              </div>
            `;
          });
          html += `</td>`;
          return;
        }
      if (showPast && (isPast || isToday)) {
        // Past and today
        const appointments = getFilteredAppointments().filter(apt => {
          const apt_date = apt.appointment_date || (apt.appointment_datetime ? apt.appointment_datetime.substring(0, 10) : null);
          const apt_time = apt.appointment_time || (apt.appointment_datetime ? apt.appointment_datetime.substring(11, 16) : null);
          if (!apt_date || !apt_time) return false;
          const apt_hour = parseInt(apt_time.split(':')[0]);
          return apt_date === wd.date && apt_hour === h;
        });
        html += `<td class="border-t cursor-pointer hover:bg-blue-50 min-h-12 p-1 sm:p-2" onclick="openAddAppointmentPanelWithTime('${wd.date}', '${time}')">`;
        appointments.forEach(apt => {
          html += `
            <div class="bg-blue-100 rounded p-1 sm:p-2 text-xs text-blue-800 mb-1 hover:bg-opacity-80 transition-colors cursor-pointer" onclick="event.stopPropagation();showWeekAppointmentDetails(${apt.id})">
              <span class="font-bold text-blue-900 text-xs sm:text-sm">${(window.userType === 'patient') ? 'Appointment' : (apt.patient_name || 'Appointment')}</span>
            </div>
          `;
        });
        html += `</td>`;
        return;
      }

// Make showWeekAppointmentDetails globally available
window.showWeekAppointmentDetails = function(appointmentId) {
  const appointment = (window.appointments || []).find(apt => apt.id == appointmentId);
  if (!appointment) {
    alert('Appointment not found');
    return;
  }
  let modal = document.getElementById('dayAppointmentsModal');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'dayAppointmentsModal';
    modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40';
    modal.innerHTML = `
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 relative animate-fade-in">
        <button id="closeDayAppointmentsModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl font-bold">&times;</button>
        <h2 class="text-xl font-bold mb-4 text-blue-700">Appointment Details</h2>
        <div id="dayAppointmentsList"></div>
      </div>
    `;
    document.body.appendChild(modal);
  }
  // Populate details
  const list = modal.querySelector('#dayAppointmentsList');
  list.innerHTML = `
    <div class="mb-2"><span class="font-semibold">Patient:</span> ${appointment.patient_name || ''}</div>
    <div class="mb-2"><span class="font-semibold">Date:</span> ${appointment.appointment_date || (appointment.appointment_datetime ? appointment.appointment_datetime.substring(0,10) : '')}</div>
    <div class="mb-2"><span class="font-semibold">Time:</span> ${appointment.appointment_time || (appointment.appointment_datetime ? appointment.appointment_datetime.substring(11,16) : '')}</div>
    <div class="mb-2"><span class="font-semibold">Remarks:</span> ${appointment.remarks || ''}</div>
    <button class="bg-slate-600 hover:bg-slate-700 text-white px-3 py-1 rounded text-sm mt-2" onclick="editAppointment(${appointment.id})">Edit</button>
  `;
  modal.classList.remove('hidden');
  modal.querySelector('#closeDayAppointmentsModal').onclick = () => {
    modal.classList.add('hidden');
  };
}
      // Future days always disabled
      html += `<td class="border-t bg-gray-50 text-gray-300 cursor-not-allowed"></td>`;
    });
    html += '</tr>';
  }
  weekViewBody.innerHTML = html;
  } // End of renderWeekGrid function
}
// No need to add a separate event listener for the week view checkbox, handled by shared toggle event

// Keep the old navigateDay function for backward compatibility but make it not change URL
function navigateToDate(date) {
  // Instead of changing URL, just update the calendar display
  const dateObj = new Date(date);
  currentDisplayedMonth = dateObj.getMonth();
  currentDisplayedYear = dateObj.getFullYear();
  updateCalendarDisplay();
}

// Function to open appointment panel with specific date and time
function openAddAppointmentPanelWithTime(date, time) {
  const userType = window.userType || 'admin';
  
  // Update the day view to show appointments for the clicked date
  updateDayViewForDate(date);
  
  if (userType === 'patient') {
    const panel = document.getElementById('addAppointmentPanel');
    if (panel) {
      const dateInput = document.getElementById('appointmentDate');
      const dateDisplay = document.getElementById('selectedDateDisplay');
      const timeSelect = document.getElementById('timeSelect');
      
      if (dateInput) dateInput.value = date;
      if (dateDisplay) {
        const formattedDate = new Date(date).toLocaleDateString('en-US', {
          weekday: 'long',
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        });
        dateDisplay.value = formattedDate;
      }
      
      // Populate available time slots for patients
      if (timeSelect) {
        populateAvailableTimeSlots(date, timeSelect);
      }
      
      panel.classList.add('active');
    }
  } else if (userType === 'admin' || userType === 'staff') {
    const panel = document.getElementById('addAppointmentPanel');
    if (panel) {
      const dateInput = document.getElementById('appointmentDate');
      const dateDisplay = document.getElementById('selectedDateDisplay');
      const timeInput = document.querySelector('input[name="time"]');
      
      if (dateInput) dateInput.value = date;
      if (dateDisplay) {
        const formattedDate = new Date(date).toLocaleDateString('en-US', {
          weekday: 'long',
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        });
        dateDisplay.value = formattedDate;
      }
      if (timeInput && time) {
        timeInput.value = time;
      }
      
      panel.classList.add('active');
      
  // (Conflict detection removed)
    }
  } else if (userType === 'doctor') {
    const availabilityPanel = document.getElementById('doctorAvailabilityPanel');
    if (availabilityPanel) {
      const dateInput = document.getElementById('availabilityDate');
      const dateDisplay = document.getElementById('selectedAvailabilityDateDisplay');
      
      if (dateInput) dateInput.value = date;
      if (dateDisplay) {
        const formattedDate = new Date(date).toLocaleDateString('en-US', {
          weekday: 'long',
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        });
        dateDisplay.value = formattedDate;
      }
      
      availabilityPanel.classList.add('active');
    }
  }
}

document.addEventListener('DOMContentLoaded', function() {
    // Function to load appointments for a specific date
  function loadAppointmentsForDate(date) {
    const contentDiv = document.getElementById('appointmentInfoContent');
    if (!contentDiv) return;
    const appointments = getFilteredAppointments();
    // Normalize both selected date and appointment date to YYYY-MM-DD
    const dayAppointments = appointments.filter(apt => {
      let apt_date = null;
      if (apt.appointment_date) {
        apt_date = apt.appointment_date.length > 10 ? apt.appointment_date.substring(0, 10) : apt.appointment_date;
      } else if (apt.appointment_datetime) {
        apt_date = apt.appointment_datetime.substring(0, 10);
      }
      return apt_date && apt_date.trim() === date.trim();
    });
    if (dayAppointments.length === 0) {
      contentDiv.innerHTML = `
        <div class="text-center py-8">
          <div class="text-gray-500 text-lg">No appointments for ${new Date(date).toLocaleDateString()}</div>
        </div>
      `;
      return;
    }
    let html = `<h3 class="text-gray-800 font-semibold mb-4">Appointments for ${new Date(date).toLocaleDateString()}</h3>`;
    dayAppointments.forEach(appointment => {
      const statusClass = getStatusClass(appointment.status);
      const statusText = appointment.status.charAt(0).toUpperCase() + appointment.status.slice(1);
      const approvalStatusClass = getApprovalStatusClass(appointment.approval_status || 'pending');
      const approvalStatusText = (appointment.approval_status || 'pending').replace('_', ' ').charAt(0).toUpperCase() + (appointment.approval_status || 'pending').replace('_', ' ').slice(1);
      const appointmentTypeClass = getAppointmentTypeClass(appointment.appointment_type || 'scheduled');
      const appointmentTypeText = (appointment.appointment_type || 'scheduled').replace('_', ' ').charAt(0).toUpperCase() + (appointment.appointment_type || 'scheduled').replace('_', ' ').slice(1);
      html += `
        <div class="border border-gray-200 rounded-lg p-4 mb-3 bg-white shadow-sm">
          <div class="flex justify-between items-center mb-2">
            <div class="font-semibold text-gray-800">
              📅 ${appointment.patient_name || 'Unknown Patient'}
            </div>
            <div class="flex gap-2">
              <span class="px-2 py-1 text-xs rounded-full ${appointmentTypeClass}">
                ${appointmentTypeText}
              </span>
              <span class="px-2 py-1 text-xs rounded-full ${statusClass}">
                ${statusText}
              </span>
              <span class="px-2 py-1 text-xs rounded-full ${approvalStatusClass}">
                ${approvalStatusText}
              </span>
            </div>
          </div>
          <div class="flex gap-4 text-sm text-gray-600 mb-2">
            <div><i class="fas fa-clock"></i> ${appointment.appointment_time}</div>
            ${appointment.branch_name ? `<div><i class="fas fa-building"></i> ${appointment.branch_name}</div>` : ''}
            ${appointment.dentist_name ? `<div><i class="fas fa-user-md"></i> ${appointment.dentist_name}</div>` : ''}
          </div>
          ${appointment.remarks ? `<div class="text-sm text-gray-500 italic">${appointment.remarks}</div>` : ''}
          ${appointment.decline_reason ? `<div class="text-sm text-red-500 italic">Decline reason: ${appointment.decline_reason}</div>` : ''}
          <div class="mt-3 flex gap-2">
            ${appointment.approval_status === 'pending' && (window.userType === 'admin' || window.userType === 'doctor') ? `
              <button onclick="approveAppointment(${appointment.id})" class="bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1 rounded text-sm">Approve</button>
              <button onclick="declineAppointment(${appointment.id})" class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1 rounded text-sm">Decline</button>
            ` : ''}
            ${window.userType === 'admin' ? `
              <button onclick="editAppointment(${appointment.id})" class="bg-slate-600 hover:bg-slate-700 text-white px-3 py-1 rounded text-sm">Edit</button>
              <button onclick="deleteAppointment(${appointment.id})" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">Delete</button>
            ` : ''}
          </div>
        </div>
      `;
    });
    contentDiv.innerHTML = html;
  }
    
    // Function to get status class for styling
    function getStatusClass(status) {
        switch(status) {
            case 'pending': return 'bg-yellow-100 text-yellow-800';
            case 'scheduled': return 'bg-blue-100 text-blue-800';
            case 'confirmed': return 'bg-green-100 text-green-800';
            case 'completed': return 'bg-green-100 text-green-800';
            case 'cancelled': return 'bg-red-100 text-red-800';
            case 'no_show': return 'bg-gray-100 text-gray-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }
    
    // Function to get approval status class
    function getApprovalStatusClass(approvalStatus) {
        switch(approvalStatus) {
            case 'pending': return 'bg-orange-100 text-orange-800';
            case 'approved': return 'bg-green-100 text-green-800';
            case 'declined': return 'bg-red-100 text-red-800';
            case 'auto_approved': return 'bg-blue-100 text-blue-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }
    
    // Function to get appointment type class
    function getAppointmentTypeClass(appointmentType) {
        switch(appointmentType) {
            case 'scheduled': return 'bg-slate-100 text-slate-800';
            case 'walkin': return 'bg-indigo-100 text-indigo-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }
    
    // Close buttons
    const closeAddAppointmentPanel = document.getElementById('closeAddAppointmentPanel');
    const closeDoctorAvailabilityPanel = document.getElementById('closeDoctorAvailabilityPanel');
    const closeAppointmentInfoPanel = document.getElementById('closeAppointmentInfoPanel');
    const closeEditAppointmentPanel = document.getElementById('closeEditAppointmentPanel');
    
    if (closeAddAppointmentPanel) {
        closeAddAppointmentPanel.addEventListener('click', function() {
            const panel = document.getElementById('addAppointmentPanel');
            if (panel) panel.classList.remove('active');
        });
    }

      /**
       * Fetch appointments for a single date from the server and merge into window.appointments
       * This ensures timeline/day view uses fresh, approved appointment data after approve/decline actions.
       * @param {string} date - YYYY-MM-DD
       * @returns {Promise<void>}
       */
      async function refreshAppointmentsForDate(date) {
        if (!date) return;
        try {
          const url = `${window.baseUrl}appointments/day-appointments?date=${encodeURIComponent(date)}`;
          const resp = await fetch(url, { credentials: 'same-origin' });
          if (!resp.ok) throw new Error('Failed to fetch day appointments');
          const data = await resp.json();
          if (!data || !data.success || !Array.isArray(data.appointments)) {
            console.warn('[refreshAppointmentsForDate] Unexpected response', data);
            return;
          }

          // Normalize server appointment objects to the shape expected by the calendar code
          const normalized = data.appointments.map(a => {
            // dayAppointments returns {id, start, end, duration_minutes, patient_name, dentist_name, dentist_id}
            const obj = Object.assign({}, a);
            if (a.start) {
              obj.appointment_datetime = a.start; // full datetime
              obj.appointment_date = a.start.substring(0,10);
              obj.appointment_time = a.start.substring(11,16);
            }
            // mark as approved so rendering logic treats it as occupied
            obj.approval_status = 'approved';
            return obj;
          });

          // Remove existing appointments for the same date from window.appointments
          window.appointments = (window.appointments || []).filter(apt => {
            const aptDate = apt.appointment_date || (apt.appointment_datetime ? apt.appointment_datetime.substring(0,10) : null);
            return aptDate !== date;
          }).concat(normalized);

          console.info(`[refreshAppointmentsForDate] Merged ${normalized.length} appointments for ${date} into window.appointments (total now ${window.appointments.length})`);
          // Invalidate any cached available slots for this date so selects/menus will ask server again
          try { if (window.__available_slots_cache && window.__available_slots_cache[date]) { delete window.__available_slots_cache[date]; console.debug('[refreshAppointmentsForDate] cleared available slots cache for', date); } } catch(e){}
          // Refresh selects/menus to reflect updated appointment state
          try { if (window.calendarCore && typeof window.calendarCore.refreshAllTimeSlots === 'function') window.calendarCore.refreshAllTimeSlots(); } catch(e){}
          // Allow other code to refresh calendar views
          try { if (typeof rebuildCalendarGrid === 'function') rebuildCalendarGrid(); } catch(e){}
          try { if (typeof updateDayViewForDate === 'function') updateDayViewForDate(date); } catch(e){}
          try { if (typeof updateWeekView === 'function') updateWeekView(); } catch(e){}
        } catch (err) {
          console.warn('[refreshAppointmentsForDate] error', err);
        }
      }
    
    if (closeDoctorAvailabilityPanel) {
        closeDoctorAvailabilityPanel.addEventListener('click', function() {
            const panel = document.getElementById('doctorAvailabilityPanel');
            if (panel) panel.classList.remove('active');
        });
    }
    
    if (closeAppointmentInfoPanel) {
        closeAppointmentInfoPanel.addEventListener('click', function() {
            const panel = document.getElementById('appointmentInfoPanel');
            if (panel) panel.classList.remove('active');
        });
    }
    
    if (closeEditAppointmentPanel) {
        closeEditAppointmentPanel.addEventListener('click', function() {
            const panel = document.getElementById('editAppointmentPanel');
            if (panel) panel.classList.remove('active');
        });
    }
});

// Global functions for appointment actions
// Admin handlers are moved to an external script (public/js/calendar-admin.js)
function editAppointment(appointmentId) {
  if (window.calendarAdmin && typeof window.calendarAdmin.editAppointment === 'function') {
    return window.calendarAdmin.editAppointment(appointmentId);
  }
  alert('Edit function not available');
}

function deleteAppointment(appointmentId) {
  if (window.calendarAdmin && typeof window.calendarAdmin.deleteAppointment === 'function') {
    return window.calendarAdmin.deleteAppointment(appointmentId);
  }
  alert('Delete function not available');
}
// Intercept edit appointment form submit to update via AJAX and refresh UI
document.addEventListener('DOMContentLoaded', function() {
  const editPanel = document.getElementById('editAppointmentPanel');
  if (editPanel) {
    const form = editPanel.querySelector('form');
    if (form) {
      form.onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(form);
        fetch(form.action, {
          method: 'POST',
          body: formData,
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Appointment updated successfully');
            // Update window.appointments with new data (simple reload for now)
            // Optionally, fetch updated appointments via AJAX for more accuracy
            location.reload();
          } else {
            alert('Failed to update appointment: ' + (data.message || 'Unknown error'));
          }
        })
        .catch(() => {
          alert('Failed to update appointment');
        });
      };
    }
  }
});

function approveAppointment(appointmentId) {
  if (window.calendarAdmin && typeof window.calendarAdmin.approveAppointment === 'function') {
    return window.calendarAdmin.approveAppointment(appointmentId);
  }
  alert('Approve function not available');
}

function declineAppointment(appointmentId) {
  if (window.calendarAdmin && typeof window.calendarAdmin.declineAppointment === 'function') {
    return window.calendarAdmin.declineAppointment(appointmentId);
  }
  alert('Decline function not available');
}
// Helper: Always use Asia/Manila timezone for all calendar logic
function getPHDate(dateStr) {
  // Robust Manila-aware parser.
  // - If the string contains an explicit timezone (Z or +hh:mm) or an ISO 'T' with offset, parse as-is.
  // - If the string is date-only or lacks timezone (e.g. "YYYY-MM-DD" or "YYYY-MM-DD HH:mm:ss"),
  //   treat it as Asia/Manila local time (UTC+08:00) and convert to the correct UTC instant.
  // Returns a Date instance representing the correct instant (UTC ms) for comparisons.
  if (!dateStr) return new Date();
  if (dateStr instanceof Date) return new Date(dateStr.getTime());
  if (typeof dateStr === 'number') return new Date(dateStr);

  const s = String(dateStr).trim();
  // Detect explicit timezone / full ISO with offset
  if (/[Tt].*(?:[Zz]|[+\-]\d{2}:\d{2})$/.test(s) || /[Zz]$/.test(s)) {
    const parsed = new Date(s);
    if (!isNaN(parsed.getTime())) return parsed;
  }

  // Parse date/time components for strings like 'YYYY-MM-DD' or 'YYYY-MM-DD HH:mm[:ss]'
  const m = s.match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}):?(\d{2})?)?$/);
  if (m) {
    const y = parseInt(m[1], 10), mo = parseInt(m[2], 10) - 1, d = parseInt(m[3], 10);
    const hh = parseInt(m[4] || '0', 10), mm = parseInt(m[5] || '0', 10), ss = parseInt(m[6] || '0', 10);
    // Convert Manila local datetime to UTC ms: Date.UTC(...) gives ms for that Y-M-D hh:mm:ss in UTC.
    // Manila is UTC+8, so subtract 8 hours to get the UTC instant that corresponds to the Manila local time.
    const utcMs = Date.UTC(y, mo, d, hh, mm, ss) - (8 * 60 * 60 * 1000);
    return new Date(utcMs);
  }

  // Fallback to default Date parsing
  const fallback = new Date(s);
  if (!isNaN(fallback.getTime())) return fallback;
  return new Date();
}

// Set user type for JavaScript
window.userType = '<?= $user['user_type'] ?? 'admin' ?>';
console.log('User type set to:', window.userType);

// Patient Search Functionality
document.addEventListener('DOMContentLoaded', function() {
    const patientSearch = document.getElementById('patientSearch');
    const patientSelect = document.getElementById('patientSelect');
    const patientDropdown = document.getElementById('patientDropdown');
    const selectedPatientDisplay = document.getElementById('selectedPatientDisplay');
    const selectedPatientName = document.getElementById('selectedPatientName');
    const clearPatientSelection = document.getElementById('clearPatientSelection');
    const recentPatientsList = document.getElementById('recentPatientsList');
    const allPatientsList = document.getElementById('allPatientsList');
    const noResults = document.getElementById('noResults');
    
    if (!patientSearch || !patientSelect || !patientDropdown) {
        return; // Elements not found, skip initialization
    }
    
    // Load recent patients from localStorage
    let recentPatients = JSON.parse(localStorage.getItem('recentPatients') || '[]');
    
    // Show dropdown on search focus
    patientSearch.addEventListener('focus', function() {
        showDropdown();
        populateRecentPatients();
    });
    
    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!patientSearch.contains(e.target) && !patientDropdown.contains(e.target)) {
            hideDropdown();
        }
    });
    
    // Search functionality
    patientSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        showDropdown();
        
        if (searchTerm === '') {
            populateRecentPatients();
            showAllPatients();
            return;
        }
        
        // Filter patients
        const patientOptions = allPatientsList.querySelectorAll('.patient-option');
        let hasResults = false;
        
        patientOptions.forEach(option => {
            const patientName = option.dataset.name;
            if (patientName.includes(searchTerm)) {
                option.style.display = 'block';
                hasResults = true;
            } else {
                option.style.display = 'none';
            }
        });
        
        // Show/hide sections based on search
        if (searchTerm) {
            document.getElementById('recentPatientsSection').style.display = 'none';
        } else {
            document.getElementById('recentPatientsSection').style.display = 'block';
            populateRecentPatients();
        }
        
        // Show no results message if needed
        if (!hasResults) {
            showNoResults();
        } else {
            hideNoResults();
        }
    });
    
    // Handle patient selection
    patientDropdown.addEventListener('click', function(e) {
        const patientOption = e.target.closest('.patient-option');
        if (patientOption) {
            selectPatient(patientOption);
        }
    });
    
    // Clear selection
    if (clearPatientSelection) {
        clearPatientSelection.addEventListener('click', function() {
            clearSelection();
        });
    }
    
    function showDropdown() {
        patientDropdown.classList.remove('hidden');
    }
    
    function hideDropdown() {
        patientDropdown.classList.add('hidden');
    }
    
    function selectPatient(option) {
        const patientId = option.dataset.id;
        const patientName = option.dataset.display;
        
        // Update hidden select
        patientSelect.value = patientId;
        
        // Update search input
        patientSearch.value = patientName;
        
        // Show selected patient display
        selectedPatientName.textContent = patientName;
        selectedPatientDisplay.classList.remove('hidden');
        
        // Add to recent patients
        addToRecentPatients(patientId, patientName);
        
        // Hide dropdown
        hideDropdown();
        
        // Trigger change event for any listeners
        const changeEvent = new Event('change', { bubbles: true });
        patientSelect.dispatchEvent(changeEvent);
    }
    
    function clearSelection() {
        patientSelect.value = '';
        patientSearch.value = '';
        selectedPatientDisplay.classList.add('hidden');
        
        // Trigger change event
        const changeEvent = new Event('change', { bubbles: true });
        patientSelect.dispatchEvent(changeEvent);
    }
    
    function addToRecentPatients(id, name) {
        // Remove if already exists
        recentPatients = recentPatients.filter(p => p.id !== id);
        
        // Add to beginning
        recentPatients.unshift({ id, name });
        
        // Keep only last 5
        recentPatients = recentPatients.slice(0, 5);
        
        // Save to localStorage
        localStorage.setItem('recentPatients', JSON.stringify(recentPatients));
    }
    
    function populateRecentPatients() {
        if (recentPatients.length === 0) {
            document.getElementById('recentPatientsSection').style.display = 'none';
            return;
        }
        
        document.getElementById('recentPatientsSection').style.display = 'block';
        recentPatientsList.innerHTML = recentPatients.map(patient => `
            <div class="patient-option px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-50 last:border-b-0" 
                 data-id="${patient.id}" 
                 data-name="${patient.name.toLowerCase()}"
                 data-display="${patient.name}">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-clock text-green-600 text-sm"></i>
                    </div>
                    <div>
                        <div class="font-medium text-gray-900">${patient.name}</div>
                        <div class="text-xs text-green-600">Recent</div>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    function showAllPatients() {
        document.getElementById('allPatientsSection').style.display = 'block';
        const patientOptions = allPatientsList.querySelectorAll('.patient-option');
        patientOptions.forEach(option => {
            option.style.display = 'block';
        });
    }
    
    function showNoResults() {
        document.getElementById('allPatientsSection').style.display = 'none';
        noResults.classList.remove('hidden');
    }
    
    function hideNoResults() {
        document.getElementById('allPatientsSection').style.display = 'block';
        noResults.classList.add('hidden');
    }
});

} catch (e) { console.error('Calendar runtime error', e); }
</script>

<!-- AJAX appointment form submit handler: closes modal, shows notification, updates client appointments -->
<script>
// NOTE: snackbar/fallback UI removed. Message display should use the central modal only.

document.addEventListener('DOMContentLoaded', function(){
  try{
    const form = document.getElementById('appointmentForm');
    if(!form) return;
    if(form.dataset.ajaxBound) return; // already bound
    form.dataset.ajaxBound = '1';

    form.addEventListener('submit', async function(ev){
      ev.preventDefault();
      // Prevent double-submits: if already submitting, ignore further submits
      if (form.dataset.submitted === '1') {
        try{ if (window.showMessageModal) window.showMessageModal('Submission in progress — please wait', 'Please wait'); }catch(e){}
        return;
      }
      form.dataset.submitted = '1';
      // Safety: clear submitted flag after 8 seconds in case of unexpected hang
      const clearSubmittedTimer = setTimeout(()=>{ try{ delete form.dataset.submitted; }catch(e){} }, 8000);

      const submitBtn = form.querySelector('button[type="submit"]');
      if(submitBtn) submitBtn.disabled = true;

      // Build URL from form action
      const action = form.getAttribute('action') || window.location.pathname;
      const url = (action.indexOf('http') === 0) ? action : (window.baseUrl ? (window.baseUrl + action) : action);

      const fd = new FormData(form);
      const body = new URLSearchParams();
      for(const pair of fd.entries()) body.append(pair[0], pair[1]);

      try{
        const resp = await fetch(url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
          },
          credentials: 'same-origin',
          body: body.toString()
        });
        const text = await resp.text();
        let json;
        try{ json = JSON.parse(text); } catch(e){ json = text; }
        if(!resp.ok){
          const errMsg = (json && json.message) ? json.message : (typeof json === 'string' ? json : 'Failed to create appointment');
          showFormErrors(form, errMsg, json);
          if(submitBtn) submitBtn.disabled = false;
          return;
        }

        if(json && json.success){
          const msg = json.message || 'Appointment created';
          // Prefer the central snackbar helper if available
          // Only use the centralized modal for messages while debugging modal behavior
          if (window.showMessageModal) window.showMessageModal(msg, 'Appointment created');
          else console.log('[appointment] ' + msg);

          // Close common booking panels/modals if present or if the form requests auto-close
          const closePanelById = id => {
            const el = document.getElementById(id);
            if(!el) return false;
            // remove active/open classes used by the UI
            el.classList.remove('active');
            el.classList.remove('open');
            if(el.style) el.style.display = 'none';
            return true;
          };

          // Default known panel ids used in various views
          ['addAppointmentPanel', 'appointmentModal', 'bookingPanel', 'appointmentDrawer'].forEach(closePanelById);

          // If the triggering form included a data attribute asking to close its container, honor it
          try{
            const closeSelector = form.getAttribute('data-close-on-success');
            if(closeSelector){
              const target = document.querySelector(closeSelector);
              if(target){ target.classList.remove('active'); target.classList.remove('open'); if(target.style) target.style.display = 'none'; }
            }
          }catch(e){}

          const record = json.record || json.appointment || json.appointment_record || null;
          if(record){
            window.appointments = window.appointments || [];
            const idx = window.appointments.findIndex(a => String(a.id) === String(record.id));
            if(idx >= 0) window.appointments[idx] = record; else window.appointments.push(record);
            window.dispatchEvent(new CustomEvent('appointmentCreated', { detail: record }));
          } else {
            window.dispatchEvent(new CustomEvent('appointmentCreated', { detail: null }));
          }

          try{ form.reset(); }catch(e){}
          if(submitBtn) submitBtn.disabled = false;
          try{ delete form.dataset.submitted; }catch(e){}
          clearTimeout(clearSubmittedTimer);
          return;
        }

        const failMsg = (json && json.message) ? json.message : 'Failed to create appointment';
        showFormErrors(form, failMsg, json);
      }catch(e){
        console.error('Appointment AJAX submit error', e);
        showFormErrors(form, 'Error submitting appointment');
      } finally {
        if(submitBtn) submitBtn.disabled = false;
        try{ delete form.dataset.submitted; }catch(e){}
        clearTimeout(clearSubmittedTimer);
      }
    });
  }catch(e){ console.error('init appointment ajax handler failed', e); }
});

function showFormErrors(form, message, json){
  try{
    let container = form.querySelector('.form-errors');
    if(!container){ container = document.createElement('div'); container.className = 'form-errors text-sm text-red-700 mb-2'; form.insertBefore(container, form.firstChild); }
    container.innerHTML = '';
    if(json && json.errors){
      if(typeof json.errors === 'object') Object.keys(json.errors).forEach(k=>{ const p = document.createElement('div'); p.textContent = json.errors[k]; container.appendChild(p); });
      else if(Array.isArray(json.errors)) json.errors.forEach(m=>{ const p = document.createElement('div'); p.textContent = m; container.appendChild(p); });
    }
    const p = document.createElement('div'); p.textContent = message; container.appendChild(p);
    container.scrollIntoView({behavior:'smooth', block:'center'});
  }catch(e){ console.error('showFormErrors error', e); }
}
</script>

<?php if (!isset($user) || ($user['user_type'] ?? '') !== 'patient'): ?>
  <script src="<?= base_url('js/calendar-admin.js') ?>"></script>
<?php endif; ?>