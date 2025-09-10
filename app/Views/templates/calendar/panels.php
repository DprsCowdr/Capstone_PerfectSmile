<!-- Patient Appointment Panel -->
<?php if ($user['user_type'] === 'patient'): ?>
<div id="addAppointmentPanel" class="slide-in-panel">
  <button class="close-btn" id="closeAddAppointmentPanel" aria-label="Close">&times;</button>
  <h5 class="mb-4 sm:mb-6 text-lg sm:text-xl font-bold text-gray-600">
    Book Your Appointment
  </h5>
  
  <form id="appointmentForm" action="<?= base_url('patient/book-appointment') ?>" method="post" novalidate>
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
              <option value="<?= $dentist['id'] ?>" <?= ((string)$preferredDentist === (string)$dentist['id']) ? 'selected' : '' ?>>Dr. <?= esc($dentistDisplay) ?></option>
            <?php endforeach; ?>
        <?php endif; ?>
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
      <select name="branch" id="branchSelect" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors text-sm sm:text-base" required>
        <option value="">Select Branch</option>
        <?php if (isset($branches) && is_array($branches)): ?>
          <?php foreach ($branches as $b): ?>
            <option value="<?= $b['id'] ?>"><?= $b['name'] ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
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
      <label class="block text-sm font-medium text-gray-700 mb-2">Time</label>
      <input type="time" name="time" id="appointmentTime" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors text-sm sm:text-base" required>
      <div id="timeConflictWarning" class="text-xs text-red-600 mt-1 hidden">
        <i class="fas fa-exclamation-triangle"></i> <span id="conflictMessage"></span>
      </div>
    </div>

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

<!-- Doctor Availability Panel -->
<?php if ($user['user_type'] === 'doctor'): ?>
<div id="doctorAvailabilityPanel" class="slide-in-panel">
  <button class="close-btn" id="closeDoctorAvailabilityPanel" aria-label="Close">&times;</button>
  <h5 class="mb-4 sm:mb-6 text-lg sm:text-xl font-bold text-gray-600">Set Your Availability</h5>
  
  <form id="availabilityForm" action="<?= base_url('dentist/availability/set') ?>" method="post" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="date" id="availabilityDate">
    
    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Selected Date</label>
      <input type="text" id="selectedAvailabilityDateDisplay" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-gray-50 text-gray-700 focus:border-purple-500 focus:outline-none transition-colors" readonly>
    </div>

    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
      <select name="status" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors" required>
        <option value="">Select Status</option>
        <option value="available">✅ Available</option>
        <option value="unavailable">❌ Unavailable</option>
      </select>
    </div>

    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Start Time (if available)</label>
      <input type="time" name="start_time" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors">
    </div>

    <div class="mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
      <textarea name="notes" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors resize-none" placeholder="Optional notes (e.g., reason for unavailability, special instructions)"></textarea>
    </div>

    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 shadow-lg hover:shadow-xl">
      Set Availability
    </button>
  </form>
</div>
<?php endif; ?>

<!-- Appointment Information Panel -->
<div id="appointmentInfoPanel" class="slide-in-panel" style="width: 800px !important; max-width: 90vw !important;">
  <button class="close-btn" id="closeAppointmentInfoPanel" aria-label="Close">&times;</button>
  <h5 class="mb-4" style="font-weight:700; color:#888; font-size:1.35rem;">Appointment Information</h5>
  
  <div id="appointmentInfoContent">
    <!-- Content will be loaded here -->
    <div class="text-center py-8">
      <div class="text-gray-500 text-lg">Test Panel Content</div>
      <div class="mt-4 text-gray-600 text-sm">This panel is working! 🎉</div>
      <div class="mt-2 text-gray-400 text-xs">Click the X to close this panel</div>
    </div>
  </div>
</div>

<!-- Edit Appointment Panel -->
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

    <?php if ($user['user_type'] === 'admin'): ?>
    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Patient</label>
      <select name="patient" id="editPatientSelect" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors" required>
        <option value="">Select Patient</option>
        <?php if (isset($patients) && is_array($patients)): ?>
          <?php foreach ($patients as $p): ?>
            <option value="<?= $p['id'] ?>"><?= $p['name'] ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
    </div>

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

    <!-- Show original values -->
    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
      <h4 class="font-semibold text-gray-700 mb-2">Original Values:</h4>
      <div id="originalValues" class="text-sm text-gray-600">
        <!-- Original values will be populated here -->
      </div>
    </div>

    <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 shadow-lg hover:shadow-xl">
      <span id="editSubmitText">Update Appointment</span>
    </button>
  </form>
</div> 