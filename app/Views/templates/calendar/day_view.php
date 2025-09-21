<!-- DAY VIEW -->
<div class="hidden" id="dayView">
  <?php
    // Use the same selected date as the header
    // $selectedDate is already defined in the header component
    // Attempt to read branch operating hours server-side to render a sensible hour range
    $branchId = session()->get('branch_id') ?? 1; // fallback to branch 1
    $operatingHours = null;
  $startHour = 8; // default fallback (8 AM)
  $endHour = 20;  // default fallback (8 PM)

    try {
      $db = \Config\Database::connect();
      $branch = $db->table('branches')->select('operating_hours')->where('id', $branchId)->get()->getRowArray();

      if ($branch && !empty($branch['operating_hours'])) {
        $operatingHours = json_decode($branch['operating_hours'], true);
        if (is_array($operatingHours)) {
          // Get day of week for selected date
          $dayOfWeek = strtolower(date('l', strtotime($selectedDate ?? date('Y-m-d'))));

          if (isset($operatingHours[$dayOfWeek]) && isset($operatingHours[$dayOfWeek]['enabled']) && $operatingHours[$dayOfWeek]['enabled']) {
            $open = $operatingHours[$dayOfWeek]['open'] ?? '06:00';
            $close = $operatingHours[$dayOfWeek]['close'] ?? '16:00';

            // Convert to 24-hour integers
            $startHour = (int)substr($open, 0, 2);
            $endHour = (int)substr($close, 0, 2);
          }
        }
      }
    } catch (\Exception $e) {
      // Log error and use defaults
      log_message('warning', 'Failed to get operating hours for day view: ' . $e->getMessage());
    }
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
        <!-- Dynamic hourly rows based on operating hours -->
        <?php for ($h = $startHour; $h <= $endHour; $h++): ?>
        <tr>
          <td class="text-xs text-gray-500 py-2 px-1 sm:px-2 align-top border-t font-semibold bg-gray-50"><?= ($h <= 12 ? $h : $h-12) . ($h < 12 ? 'am' : 'pm') ?></td>
          <td class="border-t cursor-pointer hover:bg-blue-50 transition-colors min-h-12 sm:min-h-16 p-1 sm:p-2"></td>
        </tr>
        <?php endfor; ?>
      </tbody>
    </table>
  </div>

  <!-- Hidden div to expose operating hours and branch id to JavaScript -->
  <div id="dayViewOperatingHours" style="display: none;"
       data-start-hour="<?= $startHour ?>"
       data-end-hour="<?= $endHour ?>"
       data-branch-id="<?= $branchId ?>">
  </div>
</div>
<!-- Appointment Modal Script -->
<script src="/js/calendar-day-view-modal.js"></script>