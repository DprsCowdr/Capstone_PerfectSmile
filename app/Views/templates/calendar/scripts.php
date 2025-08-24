<script>
// Make showWeekAppointmentDetails globally available for week view appointment clicks
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
  // Handles the All Appointments modal logic for the calendar
function showAllAppointments() {
  let modal = document.getElementById('allAppointmentsModal');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'allAppointmentsModal';
    modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40';
    modal.innerHTML = `
      <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full p-6 relative animate-fade-in">
        <button id="closeAllAppointmentsModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl font-bold">&times;</button>
        <h2 class="text-xl font-bold mb-4 text-blue-700">All Appointments <span class='ml-2 text-base text-gray-500 font-normal'>(${getFilteredAppointments().length})</span></h2>
        <div id="allAppointmentsList" class="max-h-[60vh] overflow-y-auto"></div>
      </div>
    `;
    document.body.appendChild(modal);
  }
  // Populate the list
  const list = modal.querySelector('#allAppointmentsList');
  const appointments = getFilteredAppointments(); // Use branch-filtered appointments
  if (appointments.length === 0) {
    list.innerHTML = '<div class="text-gray-500">No appointments found.</div>';
  } else {
    list.innerHTML = `
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 rounded-xl shadow">
          <thead class="bg-blue-50 sticky top-0 z-10">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-bold text-blue-700 uppercase">Patient</th>
              <th class="px-4 py-2 text-left text-xs font-bold text-blue-700 uppercase">Date</th>
              <th class="px-4 py-2 text-left text-xs font-bold text-blue-700 uppercase">Time</th>
              <th class="px-4 py-2 text-left text-xs font-bold text-blue-700 uppercase">Status</th>
              <th class="px-4 py-2 text-left text-xs font-bold text-blue-700 uppercase">Dentist</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            ${appointments.map((apt, i) => `
              <tr class="${i % 2 === 0 ? 'bg-white' : 'bg-blue-50'} hover:bg-blue-100 transition">
                <td class="px-4 py-2 font-semibold text-blue-800">${apt.patient_name || 'Unknown'}</td>
                <td class="px-4 py-2 text-gray-600">${apt.appointment_date || (apt.appointment_datetime ? apt.appointment_datetime.substring(0,10) : '')}</td>
                <td class="px-4 py-2 text-gray-600">${apt.appointment_time || (apt.appointment_datetime ? apt.appointment_datetime.substring(11,16) : '')}</td>
                <td class="px-4 py-2 text-xs">
                  <span class="inline-block rounded-full px-2 py-1 ${getStatusBadgeClass(apt.status)}">${apt.status || ''}</span>
                </td>
                <td class="px-4 py-2 text-gray-500">${apt.dentist_name || ''}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>
    `;
  }
// Helper for status badge color
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
  modal.classList.remove('hidden');
  // Close handler
  modal.querySelector('#closeAllAppointmentsModal').onclick = () => {
    modal.classList.add('hidden');
  };
}

document.addEventListener('DOMContentLoaded', function () {
  const allBtn = document.getElementById('allAppointmentsBtn');
  if (allBtn) {
    allBtn.addEventListener('click', showAllAppointments);
  }
});
</script>
 
<script>
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
    window.currentBranchFilter = 'all';
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
  
  // Create time slots from 8:00 AM to 6:00 PM (30-minute intervals)
  const startHour = 8;
  const endHour = 18;
  let availableSlots = 0;
  let bookedSlots = 0;
  
  for (let hour = startHour; hour < endHour; hour++) {
    for (let minute = 0; minute < 60; minute += 30) {
      const timeStr = String(hour).padStart(2, '0') + ':' + String(minute).padStart(2, '0');
      const displayTime = formatTime(timeStr);
      
      // Check if this time slot is already booked
      const isBooked = dateAppointments.some(apt => {
        const aptTime = apt.appointment_time || (apt.appointment_datetime ? apt.appointment_datetime.substring(11, 16) : null);
        return aptTime === timeStr;
      });
      
      const option = document.createElement('option');
      option.value = timeStr;
      
      if (isBooked) {
        option.textContent = `${displayTime} (Unavailable)`;
        option.disabled = true;
        option.style.color = '#ef4444';
        bookedSlots++;
      } else {
        option.textContent = displayTime;
        availableSlots++;
      }
      
      timeSelect.appendChild(option);
    }
  }
  
  // Show availability message
  const availabilityMessage = document.getElementById('availabilityMessage');
  const unavailableMessage = document.getElementById('unavailableMessage');
  const availabilityText = document.getElementById('availabilityText');
  const unavailableText = document.getElementById('unavailableText');
  
  if (availableSlots > 0) {
    if (availabilityMessage && availabilityText) {
      availabilityText.textContent = `${availableSlots} time slots available`;
      availabilityMessage.style.display = 'block';
    }
    if (unavailableMessage) {
      unavailableMessage.style.display = 'none';
    }
  } else {
    if (unavailableMessage && unavailableText) {
      unavailableText.textContent = 'No available time slots for this date';
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
      if (typeof showAllAppointments === 'function') {
        showAllAppointments();
      } else {
        alert('All Appointments function not loaded.');
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
  
  // Rebuild the calendar grid for month view
  rebuildCalendarGrid();
  
  // Update day view if it's currently active
  updateDayView();

  if (dropdownLabel && dropdownLabel.textContent === 'Week') {
    updateWeekView();
  }
}

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
            ${apt.appointment_time ? `<span class='text-gray-500 text-xs sm:ml-2'>(${apt.appointment_time})</span>` : ''}
          </div>
          <span class=\"text-xs ${textColor} font-semibold mt-1 sm:mt-0\">${statusText}</span>
        </div>
        ${apt.remarks ? `<div class='text-gray-600 italic text-xs mt-1'>${apt.remarks}</div>` : ''}
      `;
      div.onclick = function(e) { e.stopPropagation(); window.showDayAppointmentDetails(apt.id); };
      allDayRow.appendChild(div);
    });
  }

  // --- Update hourly slots (6 AM to 4 PM) ---
  const hourlyRows = tbody.querySelectorAll('tr:not(:first-child)');
  hourlyRows.forEach((row, index) => {
    const hour = index + 6;
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
  list.innerHTML = `
    <div class="mb-2"><span class="font-semibold">Patient:</span> ${appointment.patient_name || ''}</div>
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
        if (typeof window.showPastAppointments === 'function') {
          if (isPast && !window.showPastAppointments()) showCount = false;
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
    list.innerHTML = dayAppointments.map(apt => `
      <div class="border rounded p-2 mb-2 bg-blue-50 hover:bg-blue-100 cursor-pointer" onclick="editAppointment(${apt.id})">
        <div class="font-semibold text-blue-800">${apt.patient_name || 'Unknown'}</div>
        <div class="text-xs text-gray-500">${apt.appointment_time || (apt.appointment_datetime ? apt.appointment_datetime.substring(11,16) : '')}</div>
        <div class="text-xs text-gray-400">${apt.remarks ? apt.remarks : ''}</div>
      </div>
    `).join('');
  }
  modal.classList.remove('hidden');
  modal.querySelector('#closeDayAppointmentsModal').onclick = () => {
    modal.classList.add('hidden');
  };
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

  // Checkbox: show past appointments? (shared toggle)
  const showPastCheckbox = document.getElementById('showPastAppointmentsToggle');
  const showPast = showPastCheckbox && showPastCheckbox.checked;

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
  for (let h = 6; h <= 16; h++) {
    const time = String(h).padStart(2, '0') + ':00';
    html += `<tr><td class="text-xs text-gray-400 py-2 px-2 align-top border-t">${h <= 12 ? h : h-12}${h < 12 ? 'am' : 'pm'}</td>`;
    weekDays.forEach(wd => {
      const today = new Date();
      const wdDate = new Date(wd.date);
      wdDate.setHours(0,0,0,0); today.setHours(0,0,0,0);
      const isToday = wdDate.getTime() === today.getTime();
      const isPast = wdDate < today;
      if (!showPast && !isToday) {
        html += `<td class="border-t bg-gray-50 text-gray-300 cursor-not-allowed"></td>`;
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
                <span class="font-bold text-blue-900 text-xs sm:text-sm">${apt.patient_name || 'Appointment'}</span>
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
              <span class="font-bold text-blue-900 text-xs sm:text-sm">${apt.patient_name || 'Appointment'}</span>
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
  // Populate form fields
  const form = panel.querySelector('form');
  if (form) {
    form.action = `${window.baseUrl}admin/appointments/update/${appointmentId}`;
    if (form.elements['patient']) form.elements['patient'].value = appointment.patient_id || '';
    if (form.elements['branch']) form.elements['branch'].value = appointment.branch_id || '';
    if (form.elements['date']) form.elements['date'].value = appointment.appointment_date || (appointment.appointment_datetime ? appointment.appointment_datetime.substring(0,10) : '');
    if (form.elements['time']) form.elements['time'].value = appointment.appointment_time || (appointment.appointment_datetime ? appointment.appointment_datetime.substring(11,16) : '');
    if (form.elements['remarks']) form.elements['remarks'].value = appointment.remarks || '';
  }
  panel.classList.add('active');
  // Hide the day appointments modal if open
  const modal = document.getElementById('dayAppointmentsModal');
  if (modal) modal.classList.add('hidden');
}

function deleteAppointment(appointmentId) {
  if (!confirm('Are you sure you want to delete this appointment?')) return;
  fetch(`${window.baseUrl}admin/appointments/delete/${appointmentId}`, {
    method: 'DELETE',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: JSON.stringify({
      '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert('Appointment deleted successfully');
      // Remove from window.appointments and update calendar UI
      window.appointments = (window.appointments || []).filter(apt => apt.id != appointmentId);
      updateCalendarDisplay();
      // Also close any open modals/panels
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
    const appointment = window.appointments.find(apt => apt.id == appointmentId);
    if (!appointment) {
        alert('Appointment not found');
        return;
    }
    
    const dentistId = prompt('Enter dentist ID to assign to this appointment:');
    if (!dentistId) {
        alert('Dentist ID is required');
        return;
    }
    
    const formData = new FormData();
    formData.append('dentist_id', dentistId);
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    
    fetch(`<?= base_url() ?>admin/appointments/approve/${appointmentId}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Appointment approved successfully');
            location.reload();
        } else {
            alert('Failed to approve appointment: ' + data.message);
        }
    })
    .catch(error => {
        alert('Failed to approve appointment');
    });
}

function declineAppointment(appointmentId) {
    const reason = prompt('Please provide a reason for declining this appointment:');
    if (!reason) {
        alert('Decline reason is required');
        return;
    }
    
    const formData = new FormData();
    formData.append('reason', reason);
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    
    fetch(`<?= base_url() ?>admin/appointments/decline/${appointmentId}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Appointment declined successfully');
            location.reload();
        } else {
            alert('Failed to decline appointment: ' + data.message);
        }
    })
    .catch(error => {
        alert('Failed to decline appointment');
    });
}
// Helper: Always use Asia/Manila timezone for all calendar logic
function getPHDate(dateStr) {
  // Always return a Date object in Asia/Manila timezone
  let d;
  if (dateStr) {
    if (typeof dateStr === 'string') {
      // If dateStr is YYYY-MM-DD or YYYY-MM-DD HH:mm:ss
      d = new Date(dateStr.replace(/-/g, '/'));
    } else if (dateStr instanceof Date) {
      d = new Date(dateStr.getTime());
    } else if (typeof dateStr === 'number') {
      d = new Date(dateStr);
    } else {
      // Try to coerce to string and parse
      d = new Date(String(dateStr));
    }
  } else {
    d = new Date();
  }
  // Adjust for Manila timezone offset (UTC+8)
  let utc = d.getTime() + (d.getTimezoneOffset() * 60000);
  return new Date(utc + (8 * 60 * 60000));
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

</script> 