<!-- MONTH VIEW (shown by default) -->
<div id="monthView">
  <div class="overflow-x-auto">
    <table class="w-full min-w-full table-fixed border-collapse border border-gray-200">
      <thead>
        <tr>
          <th class="text-xs text-center text-gray-400 font-medium py-2 border-b border-gray-200 w-1/7">Sun</th>
          <th class="text-xs text-center text-gray-400 font-medium py-2 border-b border-gray-200 w-1/7">Mon</th>
          <th class="text-xs text-center text-gray-400 font-medium py-2 border-b border-gray-200 w-1/7">Tue</th>
          <th class="text-xs text-center text-gray-400 font-medium py-2 border-b border-gray-200 w-1/7">Wed</th>
          <th class="text-xs text-center text-gray-400 font-medium py-2 border-b border-gray-200 w-1/7">Thu</th>
          <th class="text-xs text-center text-gray-400 font-medium py-2 border-b border-gray-200 w-1/7">Fri</th>
          <th class="text-xs text-center text-gray-400 font-medium py-2 border-b border-gray-200 w-1/7">Sat</th>
        </tr>
      </thead>
      <tbody id="monthViewBody">
        <?php
        $day = 1;
        $started = false;
        $weeks = [];
        $week = [];
        // Fill the first week
        for ($i = 0; $i < 7; $i++) {
          if ($i < $firstDay) {
            $week[] = ['day' => '', 'date' => '', 'inactive' => true];
          } else {
            $date = $currentYear . '-' . str_pad($currentMonth, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
            $week[] = ['day' => $day, 'date' => $date, 'inactive' => false];
            $day++;
          }
        }
        $weeks[] = $week;
        // Fill the rest of the weeks
        while ($day <= $daysInMonth) {
          $week = [];
          for ($i = 0; $i < 7; $i++) {
            if ($day > $daysInMonth) {
              $week[] = ['day' => '', 'date' => '', 'inactive' => true];
            } else {
              $date = $currentYear . '-' . str_pad($currentMonth, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
              $week[] = ['day' => $day, 'date' => $date, 'inactive' => false];
              $day++;
            }
          }
          $weeks[] = $week;
        }
        foreach ($weeks as $week) {
          echo '<tr>';
          foreach ($week as $cell) {
            $classes = 'calendar-cell align-top text-xs transition relative';
            $date = $cell['date'];
            $isPast = false;
            if ($cell['inactive']) {
              $classes .= ' text-gray-300 bg-gray-50';
            } else {
              $today = date('Y-m-d');
              if ($date && $date < $today) {
                $isPast = true;
                $classes .= ' text-gray-400 bg-gray-50 cursor-not-allowed';
              } else {
                $classes .= ' text-gray-700 bg-white hover:bg-blue-50 cursor-pointer touch-manipulation';
              }
            }
            
            $hasAppointments = false;
            $appointmentCount = 0;
            $approvedCount = 0;
            if ($date && isset($appointments) && is_array($appointments)) {
              foreach ($appointments as $apt) {
                $apt_date = $apt['appointment_date'] ?? (isset($apt['appointment_datetime']) ? substr($apt['appointment_datetime'], 0, 10) : null);
                if ($apt_date === $date) {
                  $hasAppointments = true;
                  $appointmentCount++;
                  $approvedCount++;
                }
              }
            }
            
            echo '<td class="' . $classes . '" data-date="' . $date . '"';
            if ($isPast) {
              echo ' title="Cannot book in the past"';
            }
            if (!$cell['inactive'] && !$isPast) {
              echo ' onclick="openAddAppointmentPanelWithTime(\'' . $date . '\', \'\')"';
            }
            echo '>';
            
            // Wrap content in a div for proper positioning
            echo '<div class="calendar-cell-content">';
            
            // Day number in top area
            if (!empty($cell['day'])) {
              echo '<div class="day-number">';
              echo '<span class="text-xs sm:text-sm font-semibold text-gray-700">' . $cell['day'] . '</span>';
              echo '</div>';
            }
            
            // Appointment indicator in bottom area
            if ($hasAppointments && $approvedCount > 0) {
              echo '<div class="appointment-badge">';
              echo '<div class="flex items-center justify-center w-4 h-4 sm:w-5 sm:h-5 bg-blue-500 text-white rounded-full text-xs font-bold shadow-sm">';
              echo $approvedCount;
              echo '</div>';
              echo '</div>';
            }
            
            echo '</div>'; // Close content wrapper div
            echo '</td>';
          }
          echo '</tr>';
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<style>
.w-1\/7 {
  width: 14.2857%;
}

.calendar-cell-content {
  position: relative;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 4px;
}

.day-number {
  align-self: flex-start;
}

.appointment-badge {
  align-self: flex-end;
  margin-top: auto;
}

/* Mobile: Keep square layout */
@media (max-width: 767px) {
  #monthView table {
    table-layout: fixed;
    width: 100%;
  }

  #monthView td {
    width: 14.2857%;
    height: 0;
    padding-bottom: 14.2857%; /* Square aspect ratio for mobile */
    position: relative;
    border: 1px solid #e5e7eb;
  }

  #monthView td .calendar-cell-content {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    padding: 2px;
  }
}

/* Desktop: Bigger square layout 100px */
@media (min-width: 768px) {
  #monthView table {
    table-layout: fixed;
    width: 100%;
    border-collapse: collapse;
  }

  #monthView td {
    width: 14.2857%;
    height: 100px; /* Bigger 100px height */
    padding: 0;
    position: relative;
    border: 1px solid #e5e7eb;
    vertical-align: top;
  }

  #monthView td .calendar-cell-content {
    padding: 8px;
    height: 100%;
  }
}

/* Large desktop: Even bigger */
@media (min-width: 1024px) {
  #monthView td {
    height: 110px; /* Even bigger for large screens */
  }
  
  #monthView td .calendar-cell-content {
    padding: 10px;
  }
}

.calendar-cell {
  /* Base styles */
}
</style>


<script>
// Listen for the shared toggle event and update the month view only
window.addEventListener('showPastAppointmentsChanged', function(e) {
  if (typeof updateCalendarDisplay === 'function') updateCalendarDisplay();
});
</script>