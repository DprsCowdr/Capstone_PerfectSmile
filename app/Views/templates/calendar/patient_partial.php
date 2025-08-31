<?php // Patient calendar partial: injects JS data and includes patient JS (no header/footer) ?>
<!-- Patient data for JS (partial) -->
<script>
// Marker: patient calendar partial loaded
window.patientCalendarLoaded = window.patientCalendarLoaded || true;
// If the global calendar was already loaded, warn in console to help detect accidental double-includes
if (window.globalCalendarLoaded) {
	console.warn('Patient calendar partial loaded but global calendar scripts are also present. Remove global calendar includes from patient pages.');
}
// explicit patient context
window.userType = 'patient';
window.currentUserId = <?= isset($user['id']) ? $user['id'] : 'null' ?>;
window.appointments = <?= json_encode($appointments ?? []) ?>;
window.baseUrl = '<?= base_url() ?>';
window.branches = <?= json_encode($branches ?? []) ?>;
if (window.branches && window.branches.length) window.currentBranchId = window.branches[0].id;
</script>

<script src="<?= base_url('js/calendar-core.js') ?>"></script>
<script src="<?= base_url('js/calendar-patient.js') ?>"></script>
