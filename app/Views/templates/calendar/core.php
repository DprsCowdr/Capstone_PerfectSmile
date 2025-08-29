<?php // Minimal calendar core markup - used by both patient and admin variants ?>
<div id="calendarRoot" class="calendar-root">
  <?= view('templates/calendar/header') ?>
  <div class="px-2 sm:px-4 pb-4 sm:pb-6 pt-2">
    <?= view('templates/calendar/day_view', ['appointments' => $appointments ?? [], 'selectedDate' => $selectedDate ?? date('Y-m-d')]) ?>
    <?= view('templates/calendar/week_view') ?>
    <?= view('templates/calendar/month_view', ['appointments' => $appointments ?? [], 'currentMonth' => $currentMonth ?? date('n') - 1, 'currentYear' => $currentYear ?? date('Y'), 'daysInMonth' => $daysInMonth ?? date('t'), 'firstDay' => $firstDay ?? date('w') ]) ?>
  </div>
  <?= view('templates/calendar/panels', ['user' => $user, 'patients' => $patients ?? [], 'branches' => $branches ?? [], 'dentists' => $dentists ?? []]) ?>
</div>
