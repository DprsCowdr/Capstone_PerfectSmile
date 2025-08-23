<!-- Shared toggle for showing total appointments for past days -->
<div class="flex items-center mb-3 sm:mb-4">
  <label for="showPastAppointmentsToggle" class="mr-2 text-xs sm:text-sm font-medium text-gray-700">Show total appointments for past days</label>
  <input type="checkbox" id="showPastAppointmentsToggle" class="form-checkbox h-4 w-4 sm:h-5 sm:w-5 text-purple-600" checked>
</div>
<script>
// Shared toggle state for both views
const showPastAppointmentsToggle = document.getElementById('showPastAppointmentsToggle');
let showPastAppointments = true;
if (showPastAppointmentsToggle) {
  showPastAppointmentsToggle.addEventListener('change', function() {
    showPastAppointments = this.checked;
    // Notify all listeners (month/week views)
    const event = new CustomEvent('showPastAppointmentsChanged', { detail: showPastAppointments });
    window.dispatchEvent(event);
  });
}
window.showPastAppointments = () => showPastAppointments;
</script>
