<!-- DAY VIEW -->
<div class="hidden" id="dayView">
  <?php
    // Use the same selected date as the header
    // $selectedDate is already defined in the header component
  ?>
  <div class="overflow-x-auto">
  <table class="w-full min-w-full table-fixed border-collapse rounded-lg shadow-sm bg-white">
      <thead class="bg-blue-50">
        <tr>
          <th class="w-16 sm:w-24 text-left text-xs text-blue-700 font-bold uppercase tracking-wide py-2 pl-2">Time</th>
          <th class="text-left text-xs text-blue-700 font-bold uppercase tracking-wide py-2">Appointments</th>
        </tr>
      </thead>
      <tbody>
        <!-- All-day row: will be filled by JS -->
        <tr>
          <td class="bg-gray-100 text-xs text-gray-500 py-2 px-1 sm:px-2 align-top font-semibold">All-day</td>
          <td class="bg-gray-100 cursor-pointer hover:bg-blue-100 transition-colors min-h-8 p-1 sm:p-2"></td>
        </tr>
  <!-- Hourly rows: will be filled by JS -->
  <?php for ($h = 8; $h <= 20; $h++): ?>
        <tr>
          <td class="text-xs text-gray-500 py-2 px-1 sm:px-2 align-top border-t font-semibold bg-gray-50"><?= ($h <= 12 ? $h : $h-12) . ($h < 12 ? 'am' : 'pm') ?></td>
          <td class="border-t cursor-pointer hover:bg-blue-50 transition-colors min-h-12 sm:min-h-16 p-1 sm:p-2"></td>
        </tr>
        <?php endfor; ?>
      </tbody>
    </table>
  </div>
</div> 
<!-- Appointment Modal Script -->
<script src="/js/calendar-day-view-modal.js"></script>