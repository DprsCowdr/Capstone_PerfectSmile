    <!-- Bootstrap JS bundle (includes Popper) needed by sb-admin script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom scripts for all pages-->
    <script src="<?= base_url('js/sb-admin-2.min.js') ?>"></script>
    <script>
        // Delegated handler: show confirm dialog for elements (forms or links) that opt-in via data-confirm
        (function(){
            function handleAction(e) {
                var el = e.target;
                // Walk up to nearest element that has data-confirm
                while (el && el !== document) {
                    if (el.dataset && el.dataset.confirm) break;
                    el = el.parentNode;
                }
                if (!el || el === document) return;
                var message = el.dataset.confirm || 'Are you sure?';

                // If it's a link, confirm before navigating
                if (el.tagName === 'A') {
                    if (!confirm(message)) {
                        e.preventDefault();
                    }
                    return;
                }

                // If inside a form/button structure, let the form submission be intercepted
                // Find nearest form
                var form = el.tagName === 'FORM' ? el : el.closest('form');
                if (form) {
                    if (!confirm(message)) {
                        e.preventDefault();
                        return;
                    }
                    // allow submission
                }
            }

            // Attach to document for delegation (clicks and submits)
            document.addEventListener('click', function(e){
                try { handleAction(e); } catch(err) { /* noop */ }
            }, true);

            // For forms that are submitted programmatically or via Enter key, handle submit event as well
            document.addEventListener('submit', function(e){
                try {
                    var form = e.target;
                    if (!form) return;
                    // If the form itself has data-confirm, prompt
                    if (form.dataset && form.dataset.confirm) {
                        if (!confirm(form.dataset.confirm)) {
                            e.preventDefault();
                        }
                    }
                } catch(err) { /* noop */ }
            }, true);
        })();
    </script>
    <script src="<?= base_url('js/availability-badges.js') ?>"></script>
<?= view('partials/message_modal') ?>
<script>
    // Global reschedule helper: available on any page that renders the footer.
    (function(){
        if (window.reschedulePatient) return; // don't overwrite if already present
        window.reschedulePatient = async function(appointmentId){
            try {
                const resp = await fetch('/queue/reschedule', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ appointmentId })
                });
                const data = await resp.json();
                if (!data.success) {
                    // Prefer modal message if available
                    if (window.showMessageModal) return window.showMessageModal(data.message || 'No suggestions', 'Reschedule', 'info');
                    return alert(data.message || 'No suggestions');
                }

                const suggestions = data.suggestions || [];
                // Helper: format HH:MM (24h) or full datetime -> h:MM AM/PM
                function prettyTime(hm) {
                    if (!hm) return hm;
                    if (typeof hm !== 'string') return hm;
                    let timePart = hm;
                    if (hm.indexOf(' ') !== -1) timePart = hm.split(' ')[1];
                    const parts = timePart.split(':');
                    if (parts.length < 2) return hm;
                    let hh = parseInt(parts[0], 10);
                    const mm = parts[1];
                    const ampm = hh >= 12 ? 'PM' : 'AM';
                    hh = hh % 12; if (hh === 0) hh = 12;
                    return hh + ':' + mm + ' ' + ampm;
                }
                // Build content DOM
                const content = document.createElement('div');
                content.className = 'p-6';
                const title = document.createElement('h3');
                title.className = 'text-lg font-semibold text-gray-900 mb-3';
                title.textContent = 'You are about to reschedule this appointment';
                content.appendChild(title);

                // System prompt (show old schedule) — display in 12-hour format and include the date when available
                const systemPrompt = document.createElement('div');
                systemPrompt.className = 'text-sm text-gray-700 mb-3';
                try {
                    let datePart = '';
                    // Attempt to extract YYYY-MM-DD from old_time or first suggestion
                    if (data.old_time && typeof data.old_time === 'string' && data.old_time.indexOf(' ') !== -1) {
                        datePart = data.old_time.split(' ')[0];
                    } else if (suggestions && suggestions.length && typeof suggestions[0] === 'string' && suggestions[0].indexOf(' ') !== -1) {
                        datePart = suggestions[0].split(' ')[0];
                    }
                    let prettyDate = '';
                    if (datePart) {
                        try {
                            // Format to a human-friendly date when possible
                            const dt = new Date(datePart);
                            if (!isNaN(dt.getTime())) {
                                prettyDate = dt.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                            }
                        } catch (e) { /* noop */ }
                    }
                    const pretty = prettyTime(data.old_time) || 'unknown';
                    systemPrompt.textContent = 'The old schedule is ' + pretty + (prettyDate ? (' on ' + prettyDate) : '') + '. Please select a new date and time.';
                } catch (e) {
                    systemPrompt.textContent = 'The old schedule is ' + (prettyTime(data.old_time) || 'unknown') + '. Please select a new date and time.';
                }
                content.appendChild(systemPrompt);
                content.appendChild(title);

                const list = document.createElement('div');
                list.className = 'space-y-2';
                    content.appendChild(list);

                    // Confirmation helper line
                        const confirmHelper = document.createElement('div');
                        confirmHelper.className = 'mt-3 text-sm text-gray-600';
                        confirmHelper.textContent = 'Confirming will remove this patient from the waiting queue and set their appointment to scheduled at the chosen time.';
                    content.appendChild(confirmHelper);
                    suggestions.forEach((s, idx) => {
                    const label = document.createElement('label');
                    label.className = 'flex items-center space-x-3 p-2 border rounded hover:bg-gray-50 cursor-pointer';
                    const radio = document.createElement('input');
                    radio.type = 'radio';
                    radio.name = 'reschedule_choice_' + appointmentId;
                    radio.value = s;
                    if (idx === 0) radio.checked = true;
                    const txt = document.createElement('span');
                    txt.className = 'text-gray-700';
                    txt.textContent = prettyTime(s);
                    label.appendChild(radio);
                    label.appendChild(txt);
                    list.appendChild(label);
                        // update helper when suggestion selected
                        radio.addEventListener('change', () => { if (radio.checked) confirmHelper.textContent = 'Confirming will remove this patient from the waiting queue and set their appointment to scheduled at ' + prettyTime(s) + '.'; });
                });
                content.appendChild(list);

                // Prefill the date input with the appointment's current date when available
                try {
                    // data.old_time may be a full datetime like 'YYYY-MM-DD HH:MM:SS' or an appointment_datetime
                    if (typeof dateInput !== 'undefined') {
                        let prefillDate = null;
                        if (data.old_time && typeof data.old_time === 'string' && data.old_time.indexOf(' ') !== -1) {
                            prefillDate = data.old_time.split(' ')[0];
                        } else if (suggestions && suggestions.length && suggestions[0] && suggestions[0].indexOf(' ') !== -1) {
                            prefillDate = suggestions[0].split(' ')[0];
                        }
                        if (prefillDate) dateInput.value = prefillDate;
                    }
                } catch (e) { /* noop */ }

                const btnRow = document.createElement('div');
                btnRow.className = 'mt-4 flex justify-end space-x-2';

                const cancelBtn = document.createElement('button');
                cancelBtn.className = 'px-4 py-2 rounded bg-gray-100 text-gray-700';
                cancelBtn.textContent = 'Cancel';
                cancelBtn.addEventListener('click', () => { if (window.popModal) window.popModal(); });

                // Choose another: show inline date + time inputs as a labeled radio option
                const chooseAnotherWrapper = document.createElement('label');
                chooseAnotherWrapper.className = 'flex items-center space-x-3 p-2 border rounded hover:bg-gray-50 cursor-pointer';
                const chooseRadio = document.createElement('input');
                chooseRadio.type = 'radio';
                chooseRadio.name = 'reschedule_choice_' + appointmentId;
                chooseRadio.value = '';
                // Date input (prefilled later when modal opens)
                const dateInput = document.createElement('input');
                dateInput.type = 'date';
                dateInput.className = 'ml-2 border px-2 py-1 rounded text-sm';
                dateInput.addEventListener('change', () => {
                    // prefer date+time when both filled
                    if (timeInput && timeInput.value) {
                        chooseRadio.value = (dateInput.value || '') + ' ' + timeInput.value;
                        chooseRadio.checked = true;
                        confirmHelper.textContent = 'Confirming will remove this patient from the waiting queue and set their appointment to scheduled at ' + dateInput.value + ' ' + timeInput.value + '.';
                    }
                });
                const timeInput = document.createElement('input');
                timeInput.type = 'time';
                timeInput.className = 'ml-2 border px-2 py-1 rounded text-sm';
                timeInput.addEventListener('change', () => {
                    // When time chosen, mark radio and combine with chosen date (or default date will be used server-side)
                    chooseRadio.value = (dateInput.value || '') + ' ' + timeInput.value;
                    chooseRadio.checked = true;
                    confirmHelper.textContent = 'Confirming will remove this patient from the waiting queue and set their appointment to scheduled at ' + (dateInput.value ? (dateInput.value + ' ') : '') + timeInput.value + '.';
                });
                const chooseSpan = document.createElement('span');
                chooseSpan.className = 'text-gray-700';
                chooseSpan.textContent = 'Choose another date & time';
                chooseAnotherWrapper.appendChild(chooseRadio);
                chooseAnotherWrapper.appendChild(chooseSpan);
                chooseAnotherWrapper.appendChild(dateInput);
                chooseAnotherWrapper.appendChild(timeInput);
                list.appendChild(chooseAnotherWrapper);

                const confirmBtn = document.createElement('button');
                confirmBtn.className = 'px-4 py-2 rounded bg-purple-600 text-white';
                confirmBtn.textContent = 'Confirm reschedule';
                confirmBtn.addEventListener('click', async () => {
                    const radios = document.getElementsByName('reschedule_choice_' + appointmentId);
                    let chosen = null; Array.from(radios).forEach(r => { if (r.checked) chosen = r.value; });
                    if (!chosen) { alert('Please choose a time'); return; }
                    try {
                        // chosen may be "HH:MM" or "YYYY-MM-DD HH:MM" depending on the inputs.
                        let payloadChosen = {};
                        if (chosen.indexOf(' ') !== -1) {
                            const parts = chosen.split(' ');
                            payloadChosen.chosenDate = parts[0];
                            payloadChosen.chosenTime = parts[1];
                            payloadChosen.chosen = chosen; // full
                        } else {
                            payloadChosen.chosenTime = chosen;
                        }
                        const r = await fetch('/queue/reschedule', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            body: JSON.stringify(Object.assign({ appointmentId, confirm: true }, payloadChosen))
                        });
                        const res = await r.json();
                        if (res.success) {
                            // Show patient/staff/admin messages if returned
                            if (res.patient_message) {
                                // enqueue branch notification for patient (persisted server-side) — client shows a friendly message
                                try { if (window.showMessageModal) window.showMessageModal(res.patient_message, 'Patient notification', 'info'); else alert(res.patient_message); } catch(e) {}
                            }
                            if (res.staff_message) {
                                try { console.log('Staff message:', res.staff_message); } catch(e){}
                            }
                            if (res.admin_message) {
                                try { console.log('Admin message:', res.admin_message); } catch(e){}
                            }

                            if (window.showMessageModal) window.showMessageModal('Reschedule successful ✅. Notifications queued.', 'Success', 'info');
                            else alert('Reschedule successful ✅. Notifications queued.');
                            if (window.popModal) window.popModal();
                            // Try to call in-page refresh hook, otherwise reload
                            if (typeof window.refreshQueue === 'function') { try { window.refreshQueue(); } catch(e){} }
                            else window.location.reload();
                        } else if (res.status === 409 || res.code === 'conflict') {
                            // Conflict handling: show an in-modal conflict pane offering Auto-adjust or Pick another
                            const conflictPane = document.createElement('div');
                            conflictPane.className = 'mt-4 p-4 border rounded bg-yellow-50';
                            const cm = document.createElement('div');
                            cm.className = 'text-sm text-yellow-800 mb-2';
                            cm.textContent = res.message || ('The slot at ' + prettyTime(chosen) + ' is already booked.');
                            conflictPane.appendChild(cm);

                            const btnWrap = document.createElement('div');
                            btnWrap.className = 'flex space-x-2 justify-end';

                            const autoBtn = document.createElement('button');
                            autoBtn.className = 'px-3 py-2 bg-yellow-600 text-white rounded';
                            autoBtn.textContent = 'Auto-adjust to ' + (prettyTime(res.adjusted_time) || 'next available');
                            autoBtn.addEventListener('click', async () => {
                                if (!res.adjusted_time) {
                                    if (window.showMessageModal) window.showMessageModal('No adjusted slot available', 'Error', 'error');
                                    else alert('No adjusted slot available');
                                    return;
                                }
                                // Submit using adjusted_time
                                try {
                                    const r2 = await fetch('/queue/reschedule', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                        body: JSON.stringify({ appointmentId, confirm: true, chosenTime: res.adjusted_time, autoAdjusted: true })
                                    });
                                    const res2 = await r2.json();
                                        if (res2.success) {
                                        // res2.newTime may be a server-side formatted string; prefer prettyTime when possible
                                        const displayNew = res2.newTimeFormatted ? res2.newTimeFormatted : prettyTime(res2.newTime) || res2.newTime;
                                        if (window.showMessageModal) window.showMessageModal('Appointment rescheduled to ' + displayNew, 'Success', 'info');
                                        else alert('Appointment rescheduled to ' + displayNew);
                                        if (window.popModal) window.popModal();
                                        if (typeof window.refreshQueue === 'function') { try { window.refreshQueue(); } catch(e){} }
                                        else window.location.reload();
                                    } else {
                                        if (window.showMessageModal) window.showMessageModal(res2.message || 'Failed to reschedule after auto-adjust', 'Error', 'error');
                                        else alert(res2.message || 'Failed to reschedule after auto-adjust');
                                    }
                                } catch (err) {
                                    console.error('Auto-adjust submit failed', err);
                                    if (window.showMessageModal) window.showMessageModal('Error during auto-adjust', 'Error', 'error');
                                    else alert('Error during auto-adjust');
                                }
                            });

                            const pickBtn = document.createElement('button');
                            pickBtn.className = 'px-3 py-2 bg-gray-100 text-gray-800 rounded';
                            pickBtn.textContent = 'Pick another time';
                            pickBtn.addEventListener('click', () => {
                                // Remove conflict pane so user can choose another option in the modal
                                if (conflictPane && conflictPane.parentNode) conflictPane.parentNode.removeChild(conflictPane);
                            });

                            btnWrap.appendChild(pickBtn);
                            btnWrap.appendChild(autoBtn);
                            conflictPane.appendChild(btnWrap);
                            // Insert conflict pane before the action row so it's visible
                            content.insertBefore(conflictPane, btnRow);
                            // Scroll into view to ensure staff notices it
                            conflictPane.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            return;
                        } else {
                            if (window.showMessageModal) window.showMessageModal(res.message || 'Failed to reschedule', 'Error', 'info');
                            else alert(res.message || 'Failed to reschedule');
                        }
                    } catch (err) {
                        console.error('Confirm reschedule error', err);
                        if (window.showMessageModal) window.showMessageModal('An error occurred while rescheduling', 'Error', 'error');
                        else alert('An error occurred while rescheduling');
                    }
                });

                btnRow.appendChild(cancelBtn);
                btnRow.appendChild(confirmBtn);
                content.appendChild(btnRow);

                if (window.pushModal) {
                    window.pushModal({ title: 'Suggested next slot', html: content, suppressActions: true });
                } else {
                    if (confirm('Reschedule appointment?')) {
                        await fetch('/queue/reschedule', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify({ appointmentId, confirm: true }) });
                        window.location.reload();
                    }
                }

            } catch (err) {
                console.error('Reschedule error', err);
                if (window.showMessageModal) window.showMessageModal('An error occurred while fetching suggestions', 'Error', 'error');
                else alert('An error occurred while fetching suggestions');
            }
        };
    })();
</script>
<?php if (isset($_GET['debugModal']) && $_GET['debugModal'] == '1'): ?>
<script>
    // Quick diagnostic: if you load any page with ?debugModal=1 this will report modal helper availability
    (function(){
        try {
            const avail = {
                pushModal: typeof window.pushModal === 'function',
                popModal: typeof window.popModal === 'function',
                showMessageModal: typeof window.showMessageModal === 'function',
                reschedulePatient: typeof window.reschedulePatient === 'function',
            };
            console.log('Modal diagnostic:', avail);
            // If everything is present, show a small test modal
            if (avail.pushModal && avail.showMessageModal && avail.reschedulePatient) {
                window.showMessageModal('Modal helpers detected. Reschedule helper exist: ' + avail.reschedulePatient, 'Modal Diagnostic');
            } else {
                alert('Modal helpers missing — check console for details');
            }
        } catch(e){ console.error('modal diagnostic failed', e); }
    })();
</script>
<?php endif; ?>

<?php if (isset($_GET['checkBtn']) && $_GET['checkBtn'] == '1'): ?>
<script>
    (function(){
        try {
            // Count elements with inline onclick that reference reschedulePatient
            const nodes = Array.from(document.querySelectorAll('[onclick]'));
            const matches = nodes.filter(n => (n.getAttribute('onclick') || '').indexOf('reschedulePatient(') !== -1);
            const visible = matches.filter(n => !!(n.offsetWidth || n.offsetHeight || n.getClientRects().length));
            console.log('reschedule button nodes found:', matches.length, 'visible on screen:', visible.length, matches);
            alert('Reschedule buttons found: ' + matches.length + '\nVisible on screen: ' + visible.length);
        } catch(e) { console.error('checkBtn failed', e); alert('checkBtn failed; see console'); }
    })();
</script>
<?php endif; ?>

</body>
</html>