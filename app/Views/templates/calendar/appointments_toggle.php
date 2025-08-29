<!-- Shared toggle for showing total appointments for past days -->
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
