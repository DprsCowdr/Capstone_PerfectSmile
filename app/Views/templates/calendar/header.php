<?php
// Default title - JavaScript will update this dynamically
$calendarTitle = date('F Y');
?>

<!-- Appointment Calendar Modern Header -->
<div class="mx-auto w-full max-w-6xl bg-white rounded-xl shadow overflow-hidden">
  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-3 sm:px-6 py-3 sm:py-4 border-b space-y-3 sm:space-y-0">
    <!-- View and Branch Dropdowns -->
    <div class="flex items-center gap-3">
      <!-- View Dropdown -->
      <div class="relative" id="dropdownContainer">
        <button id="viewDropdownBtn" type="button" class="flex items-center gap-2 px-3 py-2 rounded bg-white border text-gray-700 font-medium shadow-sm focus:outline-none text-sm sm:text-base min-h-10 touch-manipulation">
          <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"></rect>
            <path d="M3 10h18" stroke="currentColor" stroke-width="2"></path>
          </svg>
          <span id="viewDropdownLabel">Month</span>
          <svg class="w-3 h-3 sm:w-4 sm:h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <!-- Dropdown (hidden by default) -->
        <div id="viewDropdownMenu" class="absolute left-0 mt-2 w-36 rounded shadow bg-white border z-50 hidden">
          <div data-view="Day" class="dropdown-option px-3 sm:px-4 py-2 flex items-center gap-2 text-gray-700 hover:bg-gray-100 cursor-pointer text-sm sm:text-base touch-manipulation">
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"></rect><path d="M3 10h18" stroke="currentColor" stroke-width="2"></path></svg>Day
          </div>
          <div data-view="Week" class="dropdown-option px-3 sm:px-4 py-2 flex items-center gap-2 text-gray-700 hover:bg-gray-100 cursor-pointer text-sm sm:text-base touch-manipulation">
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"></rect><path d="M3 10h18" stroke="currentColor" stroke-width="2"></path><path d="M9 4v18" stroke="currentColor" stroke-width="2"></path><path d="M15 4v18" stroke="currentColor" stroke-width="2"></path></svg>Week
          </div>
          <div data-view="Month" class="dropdown-option px-3 sm:px-4 py-2 flex items-center gap-2 text-gray-700 hover:bg-gray-100 cursor-pointer bg-gray-100 text-sm sm:text-base touch-manipulation">
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"></rect></svg>Month
          </div>
          <div data-view="All" class="dropdown-option px-3 sm:px-4 py-2 flex items-center gap-2 text-blue-700 hover:bg-blue-100 cursor-pointer text-sm sm:text-base touch-manipulation">
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"></circle><path d="M8 12h8" stroke="currentColor" stroke-width="2"></path></svg>All
          </div>
        </div>
      </div>

      <!-- Branch Filter Dropdown -->
      <div class="relative" id="branchDropdownContainer">
        <button id="branchDropdownBtn" type="button" class="flex items-center gap-2 px-3 py-2 rounded bg-white border text-gray-700 font-medium shadow-sm focus:outline-none text-sm sm:text-base min-h-10 touch-manipulation">
          <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16m14 0H5M9 7h6m-6 4h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span id="branchDropdownLabel" class="text-gray-900">All</span>
          <svg class="w-3 h-3 sm:w-4 sm:h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <!-- Branch Dropdown Menu -->
        <div id="branchDropdownMenu" class="absolute left-0 mt-2 w-36 rounded shadow bg-white border z-50 hidden">
          <div data-branch="all" class="branch-filter-option px-3 sm:px-4 py-2 flex items-center gap-2 text-gray-700 hover:bg-gray-100 cursor-pointer bg-gray-100 text-sm sm:text-base touch-manipulation">
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"></circle>
              <path d="M8 12h8" stroke="currentColor" stroke-width="2"></path>
            </svg>All
          </div>
          <div data-branch="nabua" class="branch-filter-option px-3 sm:px-4 py-2 flex items-center gap-2 text-green-700 hover:bg-green-100 cursor-pointer text-sm sm:text-base touch-manipulation">
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16m14 0H5M9 7h6m-6 4h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>Nabua
          </div>
          <div data-branch="iriga" class="branch-filter-option px-3 sm:px-4 py-2 flex items-center gap-2 text-blue-700 hover:bg-blue-100 cursor-pointer text-sm sm:text-base touch-manipulation">
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16m14 0H5M9 7h6m-6 4h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>Iriga
          </div>
        </div>
      </div>

                <!-- Quick buttons: Available slots & Time Taken (patient-only) -->
      <?php $sess = session(); if ($sess->get('user_type') === 'patient'): ?>
      <div class="flex items-center gap-2">
        <div class="relative">
          <button id="availableSlotsBtn" type="button" class="flex items-center gap-2 px-3 py-2 rounded bg-purple-600 text-white border border-purple-600 hover:bg-purple-700 text-sm sm:text-base">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7V3M16 7V3M3 11h18M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span>Available slots</span>
          </button>
          <div id="availableSlotsMenu" class="hidden absolute right-0 mt-2 w-64 bg-white border rounded shadow z-50">
            <div class="p-3 text-sm text-gray-600" id="availableSlotsMenuContent">Loading...</div>
          </div>
        </div>

        <div class="relative">
          <button id="timeTakenBtn" type="button" class="flex items-center gap-2 px-3 py-2 rounded bg-white border text-gray-700 hover:bg-gray-50 text-sm sm:text-base">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/></svg>
            <span>Time Taken</span>
          </button>
          <div id="timeTakenMenu" class="hidden absolute right-0 mt-2 w-72 bg-white border rounded shadow z-50">
            <div class="p-3 text-sm text-gray-600" id="timeTakenMenuContent">Loading...</div>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>

    
    <!-- Date Controls and Actions -->
    <div class="flex flex-col sm:flex-row items-center gap-3 sm:gap-4">
      <!-- Date Navigation -->
      <div class="flex items-center gap-2 sm:gap-4">
        <button type="button" class="p-2 rounded hover:bg-gray-100 touch-manipulation min-h-10 min-w-10" id="prevBtn" onclick="handleCalendarNav(-1)">
          <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <h2 id="calendarTitle" class="text-base sm:text-lg font-semibold text-gray-700 select-none min-w-32 text-center"><?= $calendarTitle ?></h2>
        <button type="button" class="p-2 rounded hover:bg-gray-100 touch-manipulation min-h-10 min-w-10" id="nextBtn" onclick="handleCalendarNav(1)">
          <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
      </div>
      
      <!-- Action Buttons -->
      <!-- <div class="flex items-center gap-2">
        <button type="button" class="px-3 sm:px-4 py-2 bg-red-500 text-white rounded font-semibold shadow text-sm sm:text-base touch-manipulation min-h-10" id="todayBtn" onclick="goToToday()">Today</button>
        <button type="button" class="px-3 sm:px-4 py-2 bg-blue-500 text-white rounded font-semibold shadow text-sm sm:text-base touch-manipulation min-h-10" id="allAppointmentsBtn">All</button>
        <button class="p-2 rounded hover:bg-gray-100 touch-manipulation min-h-10 min-w-10">
          <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M4 6h16M8 6v12M16 6v12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
      </div> -->
    </div>
  </div>
</div> 