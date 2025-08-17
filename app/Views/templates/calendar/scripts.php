<script>
// Pass data to JavaScript
window.userType = '<?= $user['user_type'] ?>';
window.appointments = <?= json_encode($appointments ?? []) ?>;
window.baseUrl = '<?= base_url() ?>';

// Add some test appointments for debugging if none exist
if (!window.appointments || window.appointments.length === 0) {
    console.log('No appointments found, adding test data for conflict detection debugging');
    window.appointments = [
        {
            id: 999,
            appointment_date: '2025-08-09',
            appointment_time: '10:00',
            appointment_datetime: '2025-08-09 10:00:00',
            patient_name: 'Test Patient',
            dentist_name: 'Dr. Test',
            status: 'approved',
            approval_status: 'approved'
        },
        {
            id: 998,
            appointment_date: '2025-08-09',
            appointment_time: '14:30',
            appointment_datetime: '2025-08-09 14:30:00',
            patient_name: 'Another Patient',
            dentist_name: 'Dr. Smith',
            status: 'confirmed',
            approval_status: 'approved'
        }
    ];
}

console.log('Loaded appointments for conflict detection:', window.appointments);

// Calendar state management - Initialize these first
let currentCalendarDate = new Date();
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
    const today = new Date();
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
    const today = new Date();
    currentSelectedDay = today.getDate();
    currentDisplayedMonth = today.getMonth();
    currentDisplayedYear = today.getFullYear();
  }
  // Reset week state when switching to week view
  if (view === 'Week') {
    const today = new Date();
    currentWeekStart = new Date(today);
    currentWeekStart.setDate(today.getDate() - today.getDay());
    currentDisplayedYear = currentWeekStart.getFullYear();
    currentDisplayedMonth = currentWeekStart.getMonth();
    currentSelectedDay = currentWeekStart.getDate();
  }
  
  updateCalendarDisplay();
  dropdownMenu.classList.add('hidden');
}

viewOptions.forEach(opt => {
  opt.addEventListener('click', function(e) {
    switchView(opt.getAttribute('data-view'));
  });
});

// Initialize with Month view
switchView('Month');

// Initialize calendar display
updateCalendarDisplay();

function navigateMonth(direction) {
  // Safety check - ensure variables are initialized
  if (typeof currentDisplayedMonth === 'undefined' || typeof currentDisplayedYear === 'undefined') {
    const today = new Date();
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
    const today = new Date();
    currentSelectedDay = today.getDate();
    currentDisplayedMonth = today.getMonth();
    currentDisplayedYear = today.getFullYear();
  }
  
  const currentView = dropdownLabel ? dropdownLabel.textContent : 'Month';
  console.log('navigateDay called with direction:', direction, 'currentView:', currentView);
  
  if (currentView === 'Day') {
    // Navigate by day
    const currentDate = new Date(currentDisplayedYear, currentDisplayedMonth, currentSelectedDay);
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
    const today = new Date(currentDisplayedYear, currentDisplayedMonth, currentSelectedDay);
    currentWeekStart = new Date(today);
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
  const today = new Date();
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
  if (!dayView) return;
  
  const tbody = dayView.querySelector('tbody');
  if (!tbody) return;
  
  // Get appointments for the selected date
  const appointments = window.appointments || [];
  const dayAppointments = appointments.filter(apt => {
    const apt_date = apt.appointment_date || (apt.appointment_datetime ? apt.appointment_datetime.substring(0, 10) : null);
    return apt_date === selectedDate;
  });
  
  // Update hourly slots (6 AM to 4 PM)
  const hourlyRows = tbody.querySelectorAll('tr:not(:first-child)'); // Skip the all-day row
  
  hourlyRows.forEach((row, index) => {
    const hour = index + 6; // Starting from 6 AM
    const appointmentCell = row.querySelector('td:last-child');
    if (!appointmentCell) return;
    
    // Clear existing appointments
    appointmentCell.innerHTML = '';
    
    // Set click handler
    const time = String(hour).padStart(2, '0') + ':00';
    appointmentCell.onclick = () => openAddAppointmentPanelWithTime(selectedDate, time);
    
    // Find appointments for this hour
    const hourAppointments = dayAppointments.filter(apt => {
      const apt_time = apt.appointment_time || (apt.appointment_datetime ? apt.appointment_datetime.substring(11, 16) : null);
      if (!apt_time) return false;
      
      // Extract hour from appointment time
      const apt_hour = parseInt(apt_time.split(':')[0]);
      return apt_hour === hour;
    });
    
    // Add appointment indicators
    hourAppointments.forEach(apt => {
      const appointmentDiv = document.createElement('div');
      appointmentDiv.className = 'bg-blue-50 border border-blue-200 rounded-lg px-2 py-1 text-xs mb-1 relative';
      
      const content = document.createElement('div');
      content.className = 'flex items-center text-blue-700';
      
      const icon = document.createElement('i');
      icon.className = 'fas fa-calendar-check mr-1 text-blue-600';
      content.appendChild(icon);
      
      const patientName = document.createElement('span');
      patientName.className = 'font-medium text-blue-800';
      patientName.textContent = apt.patient_name || 'Appointment';
      
      const timeSpan = document.createElement('span');
      timeSpan.className = 'text-green-600 ml-2';
      timeSpan.textContent = `(${apt.appointment_time || apt_time})`;
      
      content.appendChild(patientName);
      content.appendChild(timeSpan);
      appointmentDiv.appendChild(content);
      
      if (apt.remarks) {
        const remarksSpan = document.createElement('span');
        remarksSpan.className = 'ml-2 text-gray-600 italic';
        remarksSpan.textContent = apt.remarks;
        appointmentDiv.appendChild(remarksSpan);
      }
      
      const statusSpan = document.createElement('span');
      statusSpan.className = 'ml-2 text-xs text-blue-700';
      statusSpan.textContent = apt.status || 'scheduled';
      appointmentDiv.appendChild(statusSpan);
      
      appointmentCell.appendChild(appointmentDiv);
    });
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
        const appointments = window.appointments || [];
        const dayAppointments = appointments.filter(apt => {
          const apt_date = apt.appointment_date || (apt.appointment_datetime ? apt.appointment_datetime.substring(0, 10) : null);
          return apt_date === cell.date;
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
          appointmentDiv.className = 'relative z-10 mt-1 text-xs text-blue-700 font-medium flex items-center justify-center';
          const span = document.createElement('span');
          span.className = 'bg-blue-100 px-2 py-0.5 rounded-full border border-blue-200';
          span.innerHTML = `<i class="fas fa-calendar-check mr-1 text-blue-600"></i>${dayAppointments.length} apt${dayAppointments.length > 1 ? 's' : ''}`;
          appointmentDiv.appendChild(span);
          td.appendChild(appointmentDiv);
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
    weekDays.map(wd => `<th class="text-center text-xs text-gray-500 font-medium">${wd.label}<br><span class="font-normal">Others</span></th>`).join('');

  // Render body
  let html = '';
  // All-day row
  html += '<tr><td class="bg-gray-100 text-xs text-gray-400 py-2 px-2 align-top">all-day</td>' +
    weekDays.map(wd => `<td class="bg-gray-100 cursor-pointer" onclick="openAddAppointmentPanelWithTime('${wd.date}', '')"></td>`).join('') + '</tr>';
  // Hourly rows
  for (let h = 6; h <= 16; h++) {
    const time = String(h).padStart(2, '0') + ':00';
    html += `<tr><td class="text-xs text-gray-400 py-2 px-2 align-top border-t">${h <= 12 ? h : h-12}${h < 12 ? 'am' : 'pm'}</td>` +
      weekDays.map(wd => `<td class="border-t cursor-pointer hover:bg-gray-50" onclick="openAddAppointmentPanelWithTime('${wd.date}', '${time}')"></td>`).join('') + '</tr>';
  }
  weekViewBody.innerHTML = html;
}

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
  
  if (userType === 'admin' || userType === 'staff') {
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
      
      // Trigger conflict detection if both date and time are set
      if (dateInput && dateInput.value && timeInput && timeInput.value) {
        console.log('Triggering conflict detection from openAddAppointmentPanelWithTime');
        // Dispatch a change event to trigger the conflict checker
        const event = new Event('change', { bubbles: true });
        timeInput.dispatchEvent(event);
      } else if (dateInput && dateInput.value) {
        console.log('Date set, waiting for time input to trigger conflict detection');
        // Dispatch a change event on the date input
        const event = new Event('change', { bubbles: true });
        dateInput.dispatchEvent(event);
      }
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
        
        const appointments = window.appointments || [];
        const dayAppointments = appointments.filter(apt => {
            const apt_date = apt.appointment_date || (apt.appointment_datetime ? apt.appointment_datetime.substring(0, 10) : null);
            return apt_date === date;
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
    // Implement edit functionality
}

function deleteAppointment(appointmentId) {
    if (confirm('Are you sure you want to delete this appointment?')) {
        // Implement delete functionality
    }
}

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

// Clean Appointment Conflict Detection System
class ConflictDetector {
    constructor() {
        this.timeoutId = null;
        this.init();
    }

    init() {
        console.log('ConflictDetector: Initializing...');
        
        const timeInput = document.getElementById('appointmentTime');
        const dateInput = document.getElementById('appointmentDate');
        
        if (timeInput && dateInput) {
            timeInput.addEventListener('change', () => this.scheduleCheck());
            dateInput.addEventListener('change', () => this.scheduleCheck());
            console.log('ConflictDetector: Event listeners attached');
        } else {
            console.warn('ConflictDetector: Required form elements not found');
        }
    }

    scheduleCheck() {
        // Clear previous timeout
        if (this.timeoutId) {
            clearTimeout(this.timeoutId);
        }
        
        // Schedule new check after 500ms delay
        this.timeoutId = setTimeout(() => this.checkConflicts(), 500);
    }

    async checkConflicts() {
        const dateInput = document.getElementById('appointmentDate');
        const timeInput = document.getElementById('appointmentTime');
        
        if (!dateInput?.value || !timeInput?.value) {
            this.hideWarning();
            return;
        }

        try {
            const formData = new FormData();
            formData.append('appointment_date', dateInput.value);
            formData.append('appointment_time', timeInput.value);
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

            const response = await fetch('<?= base_url() ?>staff/appointments/check-conflicts', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success && data.has_conflicts) {
                this.showWarning(data.conflicts);
            } else {
                this.hideWarning();
            }
            
        } catch (error) {
            console.error('Conflict check error:', error);
            this.hideWarning();
        }
    }

    showWarning(conflicts) {
        const warningDiv = document.getElementById('timeConflictWarning');
        const messageSpan = document.getElementById('conflictMessage');
        
        if (!warningDiv || !messageSpan) return;

        let message = `⚠️ Scheduling conflict detected! `;
        if (conflicts.length === 1) {
            const conflict = conflicts[0];
            message += `${conflict.patient_name} has an appointment at ${conflict.appointment_time}`;
            if (conflict.dentist_name && conflict.dentist_name !== 'Unassigned') {
                message += ` with ${conflict.dentist_name}`;
            }
            message += ` (${Math.round(conflict.time_difference)} minutes apart)`;
        } else {
            message += `${conflicts.length} conflicting appointments found`;
        }

        messageSpan.textContent = message;
        warningDiv.classList.remove('hidden');
        
        // Highlight input field
        const timeInput = document.getElementById('appointmentTime');
        if (timeInput) {
            timeInput.classList.add('border-red-500');
            timeInput.classList.remove('border-gray-300');
        }
    }

    hideWarning() {
        const warningDiv = document.getElementById('timeConflictWarning');
        const timeInput = document.getElementById('appointmentTime');
        
        if (warningDiv) {
            warningDiv.classList.add('hidden');
        }
        
        if (timeInput) {
            timeInput.classList.remove('border-red-500');
            timeInput.classList.add('border-gray-300');
        }
    }
}

// Initialize conflict detector when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new ConflictDetector();
});

</script> 