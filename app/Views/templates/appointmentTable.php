<!-- Appointment Calendar -->
<?php
  // Always default to current month/year - JavaScript handles all navigation
  $currentMonth = date('n');
  $currentYear = date('Y');
  $selectedDate = date('Y-m-d');
  
  $monthNames = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
  $daysInMonth = date('t', mktime(0, 0, 0, $currentMonth, 1, $currentYear));
  $firstDay = date('w', mktime(0, 0, 0, $currentMonth, 1, $currentYear));
?>

<!-- Add this wrapper around your main content -->
<div class="main-content">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 sm:mb-8 space-y-4 sm:space-y-0">
      <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-purple-700 tracking-tight">
        📅 Appointment Calendar
      </h1>
      <!-- Show different message based on user type -->
      <?php if ($user['user_type'] === 'admin'): ?>
        <div class="text-green-600 font-semibold text-sm sm:text-base">
          <!-- ✅ Full Access - You can create, edit, and delete appointments -->
        </div>
      <?php elseif (in_array($user['user_type'], ['doctor', 'dentist'])): ?>
        <div class="text-blue-600 font-semibold text-sm sm:text-base">
          <!-- 🩺 Dentist Access - You can view appointments and patient information -->
        </div>
      <?php elseif ($user['user_type'] === 'staff'): ?>
        <div class="text-purple-600 font-semibold text-sm sm:text-base">
          <!-- 👥 Staff Access - You can create appointments (pending approval) -->
        </div>
      <?php else: ?>
        <div class="text-orange-600 font-semibold text-sm sm:text-base">
          <!-- 👁️ View Only - You can view appointments and doctor availability -->
        </div>
      <?php endif; ?>
    </div>

    <?php if ($flashSuccess = session()->getFlashdata('success')): ?>
      <div class="bg-green-100 border border-green-400 text-green-800 px-3 sm:px-4 py-3 rounded mb-4 sm:mb-5 text-sm sm:text-base">
        <?php if (is_array($flashSuccess)): ?>
          <ul class="list-disc pl-5">
            <?php foreach ($flashSuccess as $msg): ?>
              <li><?= esc($msg) ?></li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <?= esc($flashSuccess) ?>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if ($flashErr = session()->getFlashdata('error')): ?>
      <div class="bg-red-100 border border-red-400 text-red-800 px-3 sm:px-4 py-3 rounded mb-4 sm:mb-5 text-sm sm:text-base">
        <?php if (is_array($flashErr)): ?>
          <ul class="list-disc pl-5">
            <?php foreach ($flashErr as $err): ?>
              <li><?= esc($err) ?></li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <?= esc($flashErr) ?>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <!-- Calendar Header -->
    <?= view('templates/calendar/header') ?>

    <!-- Calendar Views -->
    <div class="px-2 sm:px-4 pb-4 sm:pb-6 pt-2">
      <!-- Shared Toggle for Past Appointments (only include ONCE above all views) -->
      <?php include __DIR__ . '/calendar/appointments_toggle.php'; ?>

      <!-- Day View -->
      <?= view('templates/calendar/day_view', [
        'appointments' => $appointments,
        'selectedDate' => $selectedDate
      ]) ?>

      <!-- Week View -->
      <?= view('templates/calendar/week_view') ?>

      <!-- Month View -->
      <?= view('templates/calendar/month_view', [
        'appointments' => $appointments,
        'currentMonth' => $currentMonth,
        'currentYear' => $currentYear,
        'daysInMonth' => $daysInMonth,
        'firstDay' => $firstDay
      ]) ?>
    </div>
  </div>
</div>

<!-- Appointment Panels -->
<?= view('templates/calendar/panels', [
  'user' => $user,
  'patients' => $patients ?? [],
  'branches' => $branches ?? [],
  'dentists' => $dentists ?? []
]) ?>

<!-- JavaScript -->
<?php
// Ensure patient pages always load the patient-scoped calendar assets.
$isPatientContext = (isset($isPatientView) && $isPatientView) || (isset($user['user_type']) && $user['user_type'] === 'patient');
if ($isPatientContext):
  // Patient context: include patient_partial to inject patient-scoped data and markers,
  // then include the main calendar scripts so the full calendar UI (views, branch filters,
  // and panel handlers) are available for patients as well.
  echo view('templates/calendar/patient_partial', [
    'user' => $user,
    'appointments' => $appointments ?? [],
    'branches' => $branches ?? []
  ]);
  echo view('templates/calendar/scripts', [
    'user' => $user,
    'appointments' => $appointments ?? [],
    'branches' => $branches ?? []
  ]);
else:
  echo view('templates/calendar/scripts', [
    'user' => $user,
    'appointments' => $appointments ?? [],
    'branches' => $branches ?? []
  ]);
endif;
?>

<!-- CSS -->
<?= view('templates/calendar/styles') ?>