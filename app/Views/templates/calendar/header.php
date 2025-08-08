<?php
// Default title - JavaScript will update this dynamically
$calendarTitle = date('F Y');
?>

<!-- Appointment Calendar Modern Header -->
<div class="mx-auto w-full max-w-6xl bg-white rounded-xl shadow overflow-hidden">
  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-3 sm:px-6 py-3 sm:py-4 border-b space-y-3 sm:space-y-0">
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
      </div>
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
      <div class="flex items-center gap-2">
        <button type="button" class="px-3 sm:px-4 py-2 bg-red-500 text-white rounded font-semibold shadow text-sm sm:text-base touch-manipulation min-h-10" id="todayBtn" onclick="goToToday()">Today</button>
        <button class="p-2 rounded hover:bg-gray-100 touch-manipulation min-h-10 min-w-10">
          <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M4 6h16M8 6v12M16 6v12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
      </div>
    </div>
  </div>
</div> 