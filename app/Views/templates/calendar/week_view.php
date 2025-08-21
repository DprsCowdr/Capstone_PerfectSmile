

<!-- WEEK VIEW -->
<div class="hidden" id="weekView">
  <div class="overflow-x-auto">
    <table class="w-full min-w-full table-fixed border-collapse">
      <thead>
        <tr id="weekDaysHeaderRow">
          <th class="w-16 sm:w-24 text-xs text-left text-gray-400 font-normal"></th>
          <!-- JS will render week day headers here -->
        </tr>
      </thead>
      <tbody id="weekViewBody">
        <!-- JS will render week rows here -->
      </tbody>
    </table>
  </div>
</div>


<script>
// Listen for the shared toggle event and update the week view only
window.addEventListener('showPastAppointmentsChanged', function(e) {
  if (typeof updateWeekViewDisplay === 'function') updateWeekViewDisplay();
});
</script>