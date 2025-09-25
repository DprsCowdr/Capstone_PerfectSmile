<!-- Patient Appointment Panel -->
<?php if ($user['user_type'] === 'patient'): ?>
<div id="addAppointmentPanel" class="slide-in-panel">
  <button class="close-btn" id="closeAddAppointmentPanel" aria-label="Close">&times;</button>
  <h5 class="mb-4 sm:mb-6 text-lg sm:text-xl font-bold text-gray-600">
    Book Your Appointment
  </h5>
  
  <form id="appointmentForm" action="<?= base_url('patient/book-appointment') ?>" method="post" novalidate data-close-on-success="#addAppointmentPanel">
    <?= csrf_field() ?>
    <input type="hidden" name="appointment_date" id="appointmentDate">
    
    <div class="mb-3 sm:mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Selected Date</label>
      <input type="text" id="selectedDateDisplay" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg bg-gray-50 text-gray-700 focus:border-purple-500 focus:outline-none transition-colors text-sm sm:text-base" readonly>
    </div>

    <!-- Patient info (hidden, auto-filled) -->
    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">

    <div class="mb-3 sm:mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Patient</label>
      <input type="text" value="<?= esc($user['name']) ?>" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg bg-gray-50 text-gray-700 text-sm sm:text-base" readonly>
      <div class="text-xs text-blue-600 mt-1">
        <i class="fas fa-info-circle"></i> You are booking for yourself
      </div>
    </div>

    <div class="mb-3 sm:mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
      <?php $selBranch = session('selected_branch_id') ?? ''; ?>
      <select name="branch_id" id="branchSelect" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors text-sm sm:text-base mobile-friendly-select" required>
        <option value="">Select Branch</option>
        <?php if (isset($branches) && is_array($branches)): ?>
          <?php foreach ($branches as $branch): $s = ($selBranch == $branch['id']) ? 'selected' : ''; ?>
            <option value="<?= $branch['id'] ?>" <?= $s ?>><?= esc($branch['name']) ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
    </div>

    <div class="mb-3 sm:mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Preferred Dentist (Optional)</label>
      <?php // Determine preferred dentist: prefer old input, then user's saved preference ?>
      <?php $preferredDentist = old('dentist_id') ?: ($user['preferred_dentist_id'] ?? $user['preferred_dentist'] ?? ''); ?>
      <select name="dentist_id" id="dentistSelect" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors text-sm sm:text-base">
        <option value="" <?= $preferredDentist === '' ? 'selected' : '' ?>>Any Available Dentist</option>
        <?php if (isset($dentists) && is_array($dentists)): ?>
            <?php foreach ($dentists as $dentist): ?>
              <?php $dentistDisplay = $dentist['name'] ?? trim(($dentist['first_name'] ?? '') . ' ' . ($dentist['last_name'] ?? '')); ?>
              <option value="<?= $dentist['id'] ?>" data-dentist-id="<?= $dentist['id'] ?>" <?= ((string)$preferredDentist === (string)$dentist['id']) ? 'selected' : '' ?>>Dr. <?= esc($dentistDisplay) ?></option>
            <?php endforeach; ?>
        <?php endif; ?>
      </select>
    </div>

    <div class="mb-3 sm:mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Service</label>
      <?php $serviceModel = new \App\Models\ServiceModel(); $servicesList = $serviceModel->findAll(); ?>
      <select name="service_id" id="service_id" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors text-sm sm:text-base" required>
        <option value="">Select Service</option>
        <?php if (!empty($servicesList) && is_array($servicesList)): foreach ($servicesList as $svc): ?>
          <?php $dataDur = isset($svc['duration_minutes']) ? 'data-duration="'.(int)$svc['duration_minutes'].'"' : ''; ?>
          <?php $dataDurMax = isset($svc['duration_max_minutes']) ? 'data-duration-max="'.(int)$svc['duration_max_minutes'].'"' : ''; ?>
          <?php // Hide prices in booking UI to avoid confusing users; prices shown in admin/procedures views only ?>
          <option value="<?= $svc['id'] ?>" <?= old('service_id') == $svc['id'] ? 'selected' : '' ?> <?= $dataDur ?> <?= $dataDurMax ?>><?= esc($svc['name']) ?></option>
        <?php endforeach; endif; ?>
      </select>
    </div>

    <div class="mb-3 sm:mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Preferred Time</label>
      <select name="appointment_time" id="timeSelect" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors text-sm sm:text-base" required>
        <option value="">Select Time</option>
        <!-- Time options will be populated by JavaScript based on availability -->
      </select>
      <div class="text-xs text-green-600 mt-1" id="availabilityMessage" style="display: none;">
        <i class="fas fa-check-circle"></i> <span id="availabilityText"></span>
      </div>
      <div class="text-xs text-red-600 mt-1" id="unavailableMessage" style="display: none;">
        <i class="fas fa-exclamation-triangle"></i> <span id="unavailableText"></span>
      </div>
    </div>

    <div class="mb-4 sm:mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-2">Additional Notes (Optional)</label>
      <textarea name="remarks" rows="3" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors text-sm sm:text-base" placeholder="Any specific concerns or requests..."></textarea>
    </div>

    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
      <button type="button" id="closeAddAppointmentPanel" class="flex-1 px-4 sm:px-6 py-2 sm:py-3 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm sm:text-base font-medium">
        Cancel
      </button>
      <button type="submit" class="flex-1 px-4 sm:px-6 py-2 sm:py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm sm:text-base font-medium">
        <i class="fas fa-calendar-plus mr-2"></i>
        Book Appointment
      </button>
    </div>

    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
      <div class="text-xs text-blue-700">
        <i class="fas fa-info-circle mr-1"></i>
        Your appointment request will be reviewed and confirmed by our staff.
      </div>
    </div>
    <div id="appointmentSuccessMessage" class="mt-3" style="display:none;">
      <div class="bg-green-100 border border-green-300 text-green-800 px-3 py-2 rounded text-sm">
        <div id="appointmentSuccessMain"></div>
        <div class="text-xs text-gray-600 mt-1">Please wait until staff review your request.</div>
      </div>
    </div>
  </form>
</div>

  <script>
  document.addEventListener('DOMContentLoaded', function(){
    try{
      var sel = document.getElementById('branchSelect');
      if(!sel) return;
      var opts = Array.from(sel.options).filter(o => o.value !== '');
      if(opts.length === 1 && !sel.value){ sel.value = opts[0].value; sel.dispatchEvent(new Event('change')); }
    }catch(e){ console.error('calendar branch select init error', e); }
  });
  // mark dentist select options with data attributes (helps badge helper map names to rows)
  try{
    document.querySelectorAll('select[name="dentist_id"] option[data-dentist-id]').forEach(opt => {
      // nothing to do here; presence of data-dentist-id on option helps some scripts
    });
  }catch(e){}
  </script>

<!-- Admin Appointment Panel -->
<?php elseif (in_array($user['user_type'], ['admin', 'staff'])): ?>
<div id="addAppointmentPanel" class="slide-in-panel" style="width: 800px !important; max-width: 90vw !important;">
  <button class="close-btn" id="closeAddAppointmentPanel" aria-label="Close">&times;</button>
  <h5 class="mb-4 sm:mb-6 text-lg sm:text-xl font-bold text-gray-600">
    <?php if ($user['user_type'] === 'staff'): ?>
      Create New Appointment (Pending Approval)
    <?php else: ?>
      Create New Appointment
    <?php endif; ?>
  </h5>
  
  <form id="appointmentForm" action="<?= base_url($user['user_type'] . '/appointments/create') ?>" method="post" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="date" id="appointmentDate">
    
    <div class="mb-3 sm:mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Selected Date</label>
      <input type="text" id="selectedDateDisplay" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg bg-gray-50 text-gray-700 focus:border-purple-500 focus:outline-none transition-colors text-sm sm:text-base" readonly>
    </div>

    <div class="mb-3 sm:mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Appointment Type</label>
      <select name="appointment_type" id="appointmentType" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors text-sm sm:text-base" required>
        <option value="scheduled">📅 Scheduled Appointment</option>
        <option value="walkin">🚶 Walk-in Appointment</option>
      </select>
      <?php if ($user['user_type'] === 'staff'): ?>
        <div class="text-xs text-orange-600 mt-1">
          <i class="fas fa-info-circle"></i> Scheduled appointments require admin/dentist approval
        </div>
      <?php endif; ?>
    </div>

    <div class="mb-3 sm:mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Patient</label>
      <div class="relative">
        <!-- Search Input -->
        <input 
          type="text" 
          id="patientSearch" 
          placeholder="Search patients by name..." 
          class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors text-sm sm:text-base"
          autocomplete="off"
        >
        <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
          <i class="fas fa-search text-gray-400"></i>
        </div>
        
        <!-- Hidden select for form submission -->
        <select name="patient" id="patientSelect" class="hidden" required>
          <option value="">Select Patient</option>
          <?php if (isset($patients) && is_array($patients)): ?>
            <?php foreach ($patients as $p): ?>
              <option value="<?= $p['id'] ?>" data-name="<?= esc(strtolower($p['name'])) ?>"><?= esc($p['name']) ?></option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
        
        <!-- Dropdown Results -->
        <div id="patientDropdown" class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
          <!-- Recent Patients Section -->
          <div id="recentPatientsSection" class="border-b border-gray-100">
            <div class="px-3 py-2 bg-gray-50 text-xs font-semibold text-gray-600 uppercase tracking-wide">
              <i class="fas fa-clock mr-1"></i> Recent Patients
            </div>
            <div id="recentPatientsList">
              <!-- Recent patients will be populated here -->
            </div>
          </div>
          
          <!-- All Patients Section -->
          <div id="allPatientsSection">
            <div class="px-3 py-2 bg-gray-50 text-xs font-semibold text-gray-600 uppercase tracking-wide">
              <i class="fas fa-users mr-1"></i> All Patients
            </div>
            <div id="allPatientsList">
              <?php if (isset($patients) && is_array($patients)): ?>
                <?php foreach ($patients as $p): ?>
                  <div class="patient-option px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-50 last:border-b-0" 
                       data-id="<?= $p['id'] ?>" 
                       data-name="<?= esc(strtolower($p['name'])) ?>"
                       data-display="<?= esc($p['name']) ?>">
                    <div class="flex items-center">
                      <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-user text-blue-600 text-sm"></i>
                      </div>
                      <div>
                        <div class="font-medium text-gray-900"><?= esc($p['name']) ?></div>
                        <div class="text-xs text-gray-500">ID: <?= $p['id'] ?></div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="px-3 py-4 text-center text-gray-500">
                  <i class="fas fa-users text-2xl mb-2"></i>
                  <div>No patients found</div>
                </div>
              <?php endif; ?>
            </div>
          </div>
          
          <!-- No Results Message -->
          <div id="noResults" class="px-3 py-4 text-center text-gray-500 hidden">
            <i class="fas fa-search text-2xl mb-2"></i>
            <div>No patients found matching your search</div>
          </div>
        </div>
        
        <!-- Selected Patient Display -->
        <div id="selectedPatientDisplay" class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-lg hidden">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                <i class="fas fa-user text-blue-600 text-xs"></i>
              </div>
              <span id="selectedPatientName" class="text-sm font-medium text-blue-900"></span>
            </div>
            <button type="button" id="clearPatientSelection" class="text-blue-600 hover:text-blue-800">
              <i class="fas fa-times text-sm"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="mb-3 sm:mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
      <?php $currentBranch = session('selected_branch_id') ?? (isset($branches) && !empty($branches) ? $branches[0]['id'] : ''); ?>
      <select name="branch" id="branchSelect" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors text-sm sm:text-base" required>
        <option value="">Select Branch</option>
        <?php if (isset($branches) && is_array($branches)): ?>
          <?php foreach ($branches as $b): ?>
            <option value="<?= $b['id'] ?>" <?= ($currentBranch == $b['id']) ? 'selected' : '' ?>><?= $b['name'] ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
      <div class="text-xs text-blue-600 mt-1">
        <i class="fas fa-info-circle"></i> Current branch: <?= isset($branches) && $currentBranch ? (array_filter($branches, fn($b) => $b['id'] == $currentBranch)[0]['name'] ?? 'Unknown') : 'Default' ?>
      </div>
    </div>

    <div class="mb-3 sm:mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Dentist</label>
      <select name="dentist" id="dentistSelect" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors text-sm sm:text-base">
        <option value="">Select Dentist (Optional for scheduled appointments)</option>
      <?php if (isset($dentists) && is_array($dentists)): ?>
        <?php foreach ($dentists as $d): ?>
          <?php $dDisplay = $d['name'] ?? trim(($d['first_name'] ?? '') . ' ' . ($d['last_name'] ?? '')); ?>
          <option value="<?= $d['id'] ?>"><?= esc($dDisplay) ?></option>
        <?php endforeach; ?>
      <?php endif; ?>
      </select>
      <div id="dentistNote" class="text-xs text-gray-500 mt-1">
        For scheduled appointments, all requests go through waitlist approval process
      </div>
    </div>

    <div class="mb-3 sm:mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Service</label>
      <?php $serviceModel = new \App\Models\ServiceModel(); $servicesList = $serviceModel->findAll(); ?>
      <select name="service_id" id="service_id" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors text-sm sm:text-base">
        <option value="">Select Service (optional)</option>
        <?php if (!empty($servicesList) && is_array($servicesList)): foreach ($servicesList as $svc): ?>
          <?php $dataDur = isset($svc['duration_minutes']) ? 'data-duration="'.(int)$svc['duration_minutes'].'"' : ''; ?>
          <?php $dataDurMax = isset($svc['duration_max_minutes']) ? 'data-duration-max="'.(int)$svc['duration_max_minutes'].'"' : ''; ?>
          <option value="<?= $svc['id'] ?>" <?= old('service_id') == $svc['id'] ? 'selected' : '' ?> <?= $dataDur ?> <?= $dataDurMax ?>><?= esc($svc['name']) ?></option>
        <?php endforeach; endif; ?>
      </select>
    </div>

    <div class="mb-3 sm:mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Time Selection</label>
      
      <!-- Time Table Modal Button -->
      <button type="button" id="openTimeTableModal" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-blue-500 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 focus:border-blue-600 focus:outline-none transition-colors text-sm sm:text-base font-medium">
        <i class="fas fa-table mr-2"></i>
        <span id="timeTableButtonText">Open Time Table</span>
      </button>
      
      <!-- Hidden time select for form submission -->
      <select name="appointment_time" id="timeSelect" class="hidden" required>
        <option value="">Select Time</option>
      </select>
      
      <!-- Selected time display -->
      <div id="selectedTimeDisplay" class="mt-2 p-2 bg-green-50 border border-green-200 rounded-lg hidden">
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <i class="fas fa-clock text-green-600 mr-2"></i>
            <div>
              <span class="text-sm font-medium text-green-900" id="selectedTimeText"></span>
              <div class="text-xs text-green-700" id="selectedTimeDuration"></div>
            </div>
          </div>
          <button type="button" id="clearTimeSelection" class="text-green-600 hover:text-green-800">
            <i class="fas fa-times text-sm"></i>
          </button>
        </div>
      </div>
      
      <div class="text-xs text-green-600 mt-1" id="availabilityMessage" style="display: none;">
        <i class="fas fa-check-circle"></i> <span id="availabilityText"></span>
      </div>
      <div class="text-xs text-red-600 mt-1" id="unavailableMessage" style="display: none;">
        <i class="fas fa-exclamation-triangle"></i> <span id="unavailableText"></span>
      </div>
      <div id="timeConflictWarning" class="text-xs text-red-600 mt-1 hidden">
        <i class="fas fa-exclamation-triangle"></i> <span id="conflictMessage"></span>
      </div>
    </div>

    <?php if ($user['user_type'] === 'admin'): ?>
    <div class="mb-3 sm:mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Procedure duration (minutes) — admin only (optional)</label>
      <input type="number" name="procedure_duration" id="procedureDuration" min="1" step="1" placeholder="e.g. 30" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors text-sm sm:text-base">
      <div class="text-xs text-gray-500 mt-1">If set, this will override the service default duration for this booking.</div>
    </div>
    <?php endif; ?>

    <div class="mb-4 sm:mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
      <textarea name="remarks" rows="3" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors resize-none text-sm sm:text-base" placeholder="Optional remarks"></textarea>
    </div>

    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 sm:px-6 rounded-lg transition-colors duration-200 shadow-lg hover:shadow-xl text-sm sm:text-base min-h-12 touch-manipulation">
      Create Appointment
    </button>
  </form>
</div>
<?php endif; ?>

<!-- Dentist Dashboard Information Panel (Read-only) -->
<?php if (in_array($user['user_type'], ['doctor', 'dentist'])): ?>
<div id="doctorAvailabilityPanel" class="slide-in-panel">
  <button class="close-btn" id="closeDoctorAvailabilityPanel" aria-label="Close">&times;</button>
  <h5 class="mb-4 sm:mb-6 text-lg sm:text-xl font-bold text-gray-600">📅 Appointment Calendar - Dentist View</h5>
  
  <div class="space-y-4">
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
      <h6 class="font-semibold text-blue-800 mb-2">👁️ View Mode</h6>
      <p class="text-blue-700 text-sm">
        You can view all appointment information by clicking on appointments in the calendar. 
        This includes patient details, appointment times, and service information.
      </p>
    </div>
    
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
      <h6 class="font-semibold text-green-800 mb-2">📋 How to Use</h6>
      <ul class="text-green-700 text-sm space-y-1">
        <li>• Click any appointment to view detailed information</li>
        <li>• Use the calendar navigation to browse different dates</li>
        <li>• Switch between Day, Week, and Month views</li>
        <li>• View patient contact information and appointment status</li>
      </ul>
    </div>
    
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
      <h6 class="font-semibold text-gray-800 mb-2">ℹ️ Information Available</h6>
      <ul class="text-gray-700 text-sm space-y-1">
        <li>• Patient name and contact details</li>
        <li>• Appointment date and time</li>
        <li>• Service type and duration</li>
        <li>• Branch location</li>
        <li>• Appointment status</li>
      </ul>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Appointment Information Modal (centered popup) -->
<div id="appointmentInfoPanel" class="hidden fixed inset-0 z-60 flex items-center justify-center px-4" role="dialog" aria-modal="true" aria-labelledby="appointmentInfoTitle" aria-hidden="true">
  <div class="absolute inset-0 bg-black bg-opacity-40" data-close-panel></div>
  <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-5 z-10 animate-fade-in">
    <button class="absolute top-3 right-3 text-2xl text-gray-500 hover:text-gray-700" id="closeAppointmentInfoPanel" aria-label="Close" data-close-panel>&times;</button>
    <h5 id="appointmentInfoTitle" class="mb-3 text-lg font-semibold text-gray-800">Appointment Information</h5>
    <div id="appointmentInfoShort" class="text-sm text-gray-500 mb-3">&nbsp;</div>
    <div id="appointmentInfoContent" class="space-y-3 text-sm text-gray-700" tabindex="-1">
      <!-- Content will be loaded here -->
      <div class="text-center py-6">
        <div class="text-gray-500 text-lg">No appointment selected</div>
        <div class="mt-3 text-gray-600 text-sm">Select an appointment to view details.</div>
      </div>
    </div>
  </div>
</div>

<!-- Edit Appointment Panel (Admin/Staff Only) -->
<?php if (in_array($user['user_type'], ['admin', 'staff'])): ?>
<div id="editAppointmentPanel" class="slide-in-panel" style="width: 800px !important; max-width: 90vw !important;">
  <button class="close-btn" id="closeEditAppointmentPanel" aria-label="Close">&times;</button>
  <h5 class="mb-4" style="font-weight:700; color:#888; font-size:1.35rem;">Edit Appointment</h5>
  
  <form id="editAppointmentForm" method="post" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="_method" value="PUT">
    <input type="hidden" name="appointment_id" id="editAppointmentId">
    
    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Appointment Date</label>
      <input type="date" name="date" id="editAppointmentDate" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors" required>
    </div>

    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Time</label>
      <input type="time" name="time" id="editAppointmentTime" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors" required>
    </div>

    <!-- End Time removed per UI request -->

    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Appointment Type</label>
      <select name="appointment_type" id="editAppointmentType" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors" required>
        <option value="scheduled">📅 Scheduled Appointment</option>
        <option value="walkin">🚶 Walk-in Appointment</option>
      </select>
    </div>

    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Dentist</label>
      <select name="dentist" id="editDentistSelect" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors">
        <option value="">Select Dentist (Optional)</option>
        <?php if (isset($dentists) && is_array($dentists)): ?>
          <?php foreach ($dentists as $d): ?>
            <?php $dDisplay = $d['name'] ?? trim(($d['first_name'] ?? '') . ' ' . ($d['last_name'] ?? '')); ?>
            <option value="<?= $d['id'] ?>"><?= esc($dDisplay) ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
    </div>

    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Service</label>
      <?php $serviceModel = new \App\Models\ServiceModel(); $servicesList = $serviceModel->findAll(); ?>
      <select name="service_id" id="editServiceId" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors">
        <option value="">Select Service (optional)</option>
        <?php if (!empty($servicesList) && is_array($servicesList)): foreach ($servicesList as $svc): ?>
          <?php $dataDur = isset($svc['duration_minutes']) ? 'data-duration="'.(int)$svc['duration_minutes'].'"' : ''; ?>
          <option value="<?= $svc['id'] ?>" <?= old('service_id') == $svc['id'] ? 'selected' : '' ?> <?= $dataDur ?>><?= esc($svc['name']) ?></option>
        <?php endforeach; endif; ?>
      </select>
    </div>

    <?php if ($user['user_type'] === 'admin'): ?>
    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
      <select name="branch" id="editBranchSelect" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors" required>
        <option value="">Select Branch</option>
        <?php if (isset($branches) && is_array($branches)): ?>
          <?php foreach ($branches as $b): ?>
            <option value="<?= $b['id'] ?>"><?= $b['name'] ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
    </div>
    <?php endif; ?>

    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
      <select name="status" id="editAppointmentStatus" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors" required>
        <option value="scheduled">Scheduled</option>
        <option value="rescheduled">Re-Scheduled</option>
        <option value="confirmed">Confirmed</option>
        <option value="completed">Completed</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>

    <div class="mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
      <textarea name="remarks" id="editAppointmentRemarks" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors resize-none" placeholder="Optional remarks"></textarea>
    </div>

    <!-- original values block removed as per UI update -->

    <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 shadow-lg hover:shadow-xl">
      <span id="editSubmitText">Update Appointment</span>
    </button>
  </form>
</div>
<?php endif; ?>

<!-- Time Table Modal (Admin/Staff Only) -->
<?php if (in_array($user['user_type'], ['admin', 'staff'])): ?>
<div id="timeTableModal" class="hidden fixed inset-0 z-70 flex items-center justify-center px-4" role="dialog" aria-modal="true" aria-labelledby="timeTableModalTitle" aria-hidden="true">
  <div class="absolute inset-0 bg-black bg-opacity-50" id="timeTableModalBackdrop"></div>
  <div class="relative bg-white rounded-lg shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-hidden z-10 animate-fade-in">
    
    <!-- Modal Header -->
    <div class="flex items-center justify-between p-4 border-b bg-gradient-to-r from-blue-600 to-purple-600 text-white">
      <div class="flex items-center space-x-3">
        <i class="fas fa-table text-xl"></i>
        <div>
          <h5 id="timeTableModalTitle" class="text-lg font-semibold">Available Time Slots</h5>
          <div class="text-sm opacity-90" id="timeTableDate">Select a time slot for your appointment</div>
        </div>
      </div>
      <div class="flex items-center space-x-3">
        <!-- Branch Selector -->
        <div class="flex items-center space-x-2">
          <label class="text-sm opacity-90">Branch:</label>
          <select id="timeTableBranchSelect" class="px-3 py-1 bg-white text-gray-800 rounded border text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
            <!-- Options populated by JavaScript -->
          </select>
        </div>
        <button id="closeTimeTableModal" class="text-white hover:text-gray-200 text-2xl font-bold" aria-label="Close">&times;</button>
      </div>
    </div>

    <!-- Modal Body -->
    <div class="flex-1 overflow-hidden">
      <!-- Loading State -->
      <div id="timeTableLoading" class="flex items-center justify-center h-64">
        <div class="text-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <div class="text-gray-600">Loading time slots...</div>
        </div>
      </div>

      <!-- Time Table Container -->
      <div id="timeTableContainer" class="hidden h-full">
        <!-- Header: Date + legend (service duration removed) -->
        <div class="px-4 py-3 bg-gray-50 border-b flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <div class="text-sm text-gray-700">
              <span class="font-medium">Date:</span>
              <span id="selectedDateDisplay" class="text-purple-600 font-medium"></span>
            </div>
            <div class="text-sm text-gray-700">
              <span class="font-medium">Today:</span>
              <span id="todayDateDisplay" class="text-green-600 font-medium"></span>
            </div>
          </div>
          <div class="flex items-center space-x-4">
            <!-- Legend -->
            <div class="flex items-center space-x-3 text-sm">
              <div class="flex items-center space-x-1">
                <div class="w-4 h-4 bg-green-500 rounded border"></div>
                <span class="text-gray-700">Available</span>
              </div>
              <div class="flex items-center space-x-1">
                <div class="w-4 h-4 bg-red-500 rounded border"></div>
                <span class="text-gray-700">Occupied</span>
              </div>
              <div class="flex items-center space-x-1">
                <div class="w-4 h-4 bg-gray-300 rounded border"></div>
                <span class="text-gray-700">Outside Hours</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Time Grid -->
        <div class="overflow-auto h-96">
          <div class="p-4">
            <div id="timeGrid" class="grid gap-2" style="grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));">
              <!-- Time slots populated by JavaScript -->
            </div>
          </div>
        </div>

        <!-- Occupied Appointments Info -->
        <div id="occupiedInfoSection" class="border-t bg-gray-50 p-4 max-h-48 overflow-auto">
          <h6 class="text-sm font-semibold text-gray-800 mb-2 flex items-center">
            <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
            Occupied Time Slots
          </h6>
          <div id="occupiedAppointmentsList" class="space-y-2">
            <!-- Occupied appointments populated by JavaScript -->
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div id="timeTableError" class="hidden flex items-center justify-center h-64">
        <div class="text-center">
          <i class="fas fa-exclamation-triangle text-red-500 text-3xl mb-4"></i>
          <div class="text-gray-800 font-medium mb-2">Unable to load time slots</div>
          <div class="text-gray-600 text-sm mb-4" id="timeTableErrorMessage"></div>
          <button id="retryTimeTable" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
            <i class="fas fa-redo mr-2"></i>Retry
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Footer -->
    <div class="border-t bg-gray-50 px-4 py-3 flex items-center justify-between">
      <div class="text-sm text-gray-600">
        <span id="availableSlotsCount">0</span> available slots | 
        <span id="occupiedSlotsCount">0</span> occupied slots
      </div>
      <div class="flex items-center space-x-2">
        <button id="refreshTimeTable" class="px-3 py-1 text-blue-600 hover:bg-blue-50 rounded text-sm transition-colors">
          <i class="fas fa-sync-alt mr-1"></i>Refresh
        </button>
        <button id="cancelTimeSelection" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors text-sm">
          Cancel
        </button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?> 