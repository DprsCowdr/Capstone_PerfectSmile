<?php // Patient-specific calendar view: includes core markup + patient-only JS/data ?>
<?= view('templates/header') ?>
<div class="min-h-screen bg-white flex">
  <?= view('templates/sidebar', ['user' => $user]) ?>
  <div class="flex-1 flex flex-col min-h-screen bg-white">
    <main class="flex-1 px-6 py-8 bg-white">
      <?php // Include core calendar skeleton ?>
      <?= view('templates/calendar/core', 
      ['appointments' => $appointments ?? [],
       'selectedDate' => $selectedDate ?? date('Y-m-d'),
       'currentMonth' => $currentMonth ?? date('n') - 1, 
       'currentYear' => $currentYear ?? date('Y'), 
       'daysInMonth' => $daysInMonth ?? date('t'), 
       'firstDay' => $firstDay ?? date('w')
       ]) ?>
    </main>
  </div>
</div>

<!-- Patient data for JS is provided by the patient_partial to avoid duplicate script includes -->
<?= view('templates/footer') ?>
