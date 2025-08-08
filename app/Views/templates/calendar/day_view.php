<!-- DAY VIEW -->
<div class="hidden" id="dayView">
  <?php
    // Use the same selected date as the header
    // $selectedDate is already defined in the header component
  ?>
  <div class="overflow-x-auto">
    <table class="w-full min-w-full table-fixed border-collapse">
      <thead>
        <tr>
          <th class="w-16 sm:w-24 text-left text-xs text-gray-400 font-normal"> </th>
          <th class="text-left text-xs text-gray-500 font-medium">Appointments</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="bg-gray-100 text-xs text-gray-400 py-2 px-1 sm:px-2 align-top">all-day</td>
          <td class="bg-gray-100 cursor-pointer hover:bg-gray-200 transition-colors min-h-8" onclick="openAddAppointmentPanelWithTime('<?= $selectedDate ?>', '')"></td>
        </tr>
        <!-- Hourly rows -->
        <?php for ($h = 6; $h <= 16; $h++):
          $time = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
          $appointmentsForHour = [];
          if (isset($appointments) && is_array($appointments)) {
            foreach ($appointments as $apt) {
              $apt_date = $apt['appointment_date'] ?? (isset($apt['appointment_datetime']) ? substr($apt['appointment_datetime'], 0, 10) : null);
              $apt_time = $apt['appointment_time'] ?? (isset($apt['appointment_datetime']) ? substr($apt['appointment_datetime'], 11, 5) : null);
              
              // Check if appointment is on the selected date
              if ($apt_date === $selectedDate && $apt_time) {
                // Extract hour from appointment time (handle formats like "6:00", "06:00", "6:30", etc.)
                $apt_hour = (int)substr($apt_time, 0, strpos($apt_time, ':'));
                if ($apt_hour === $h) {
                  $appointmentsForHour[] = $apt;
                }
              }
            }
          }
        ?>
              <tr>
          <td class="text-xs text-gray-400 py-2 px-1 sm:px-2 align-top border-t"><?= ($h <= 12 ? $h : $h-12) . ($h < 12 ? 'am' : 'pm') ?></td>
          <td class="border-t cursor-pointer hover:bg-gray-50 transition-colors min-h-12 sm:min-h-16 p-1 sm:p-2" onclick="openAddAppointmentPanelWithTime('<?= $selectedDate ?>', '<?= $time ?>')">
            <?php foreach ($appointmentsForHour as $apt): ?>
              <?php
                // Since pending_approval appointments are excluded from main table,
                // all appointments shown here are approved/confirmed
                $bgColor = 'bg-blue-100';
                $textColor = 'text-blue-800';
                $statusText = $apt['status'] === 'confirmed' ? 'Confirmed' : 'Scheduled';
              ?>
              <div class="<?= $bgColor ?> rounded p-1 sm:p-2 text-xs <?= $textColor ?> mb-1 hover:bg-blue-200 transition-colors">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                  <div class="flex flex-col sm:flex-row sm:items-center">
                    <span class="font-bold text-gray-700 text-xs sm:text-sm"><?= esc($apt['patient_name'] ?? 'Appointment') ?></span>
                    <span class="text-gray-500 text-xs sm:ml-2">(<?= esc($apt['appointment_time']) ?>)</span>
                  </div>
                  <span class="text-xs <?= $textColor ?> font-semibold mt-1 sm:mt-0"><?= esc($statusText) ?></span>
                </div>
                <?php if (!empty($apt['remarks'])): ?>
                <div class="text-gray-600 italic text-xs mt-1"><?= esc($apt['remarks']) ?></div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </td>
        </tr>
        <?php endfor; ?>
      </tbody>
    </table>
  </div>
</div> 