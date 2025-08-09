<!-- Admin Appointment Panel -->
<?php if (in_array($user['user_type'], ['admin', 'staff'])): ?>
<div id="addAppointmentPanel" class="slide-in-panel">
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
      <select name="patient" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors text-sm sm:text-base" required>
        <option value="">Select Patient</option>
        <?php if (isset($patients) && is_array($patients)): ?>
          <?php foreach ($patients as $p): ?>
            <option value="<?= $p['id'] ?>"><?= $p['name'] ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
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
            <?php $isDefault = (strpos($d['name'], 'Dr. Minnie Gonowon') !== false || strpos($d['email'], 'dr.gonowon') !== false); ?>
            <option value="<?= $d['id'] ?>" <?= $isDefault ? 'selected' : '' ?>><?= $d['name'] ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
      <div id="dentistNote" class="text-xs text-gray-500 mt-1">
        For scheduled appointments, all requests go through waitlist approval process
      </div>
    </div>

    <div class="mb-3 sm:mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Time</label>
      <input type="time" name="time" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors text-sm sm:text-base" required>
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
<?php if ($user['user_type'] === 'dentist'): ?>
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
<div id="appointmentInfoPanel" class="slide-in-panel">
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
<div id="editAppointmentPanel" class="slide-in-panel">
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