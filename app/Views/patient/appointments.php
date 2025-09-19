<?php $user = $user ?? session('user') ?? []; ?>

<?= view('templates/header') ?>

<?= view('templates/sidebar', ['user' => $user ?? null]) ?>

<div class="min-h-screen bg-gray-50 flex">
    <div class="flex-1 flex flex-col min-h-screen min-w-0 overflow-hidden" data-sidebar-offset>
    <?= view('templates/patient_topbar', ['user' => $user ?? null]) ?>

    <!-- Main Content -->
    <main class="flex-1 px-6 pb-6 overflow-auto min-w-0" data-sidebar-offset>
        <h1 class="text-xl font-semibold text-gray-800 mb-4">My Appointments</h1>
            <?php if (!empty($appointments)): ?>
                <div id="myAppointmentsList" class="grid grid-cols-1 gap-6">
                    <?php foreach ($appointments as $appointment): ?>
                        <div class="bg-white rounded-lg shadow-lg p-6" <?= !empty($appointment['dentist_id']) ? 'data-dentist-id="' . esc($appointment['dentist_id']) . '"' : '' ?> >
                            <div class="flex flex-col md:flex-row md:justify-between md:items-start">
                                <div class="flex-1 mb-4 md:mb-0">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>
                                        <h3 class="text-lg font-semibold text-gray-800">
                                            <?= date('F j, Y', strtotime($appointment['appointment_datetime'])) ?>
                                        </h3>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                                        <div>
                                            <i class="fas fa-clock mr-2"></i>
                                            <strong>Time:</strong> <?= date('g:i A', strtotime($appointment['appointment_datetime'])) ?>
                                        </div>
                                        <div>
                                            <i class="fas fa-building mr-2"></i>
                                            <strong>Branch:</strong> <?= esc($appointment['branch_name'] ?? 'Not specified') ?>
                                        </div>
                                        <div>
                                            <i class="fas fa-user-md mr-2"></i>
                                            <strong>Dentist:</strong>
                                            <?= (isset($appointment['dentist_name']) && $appointment['dentist_name']) ? ('Dr. ' . esc($appointment['dentist_name'])) : 'Not assigned yet' ?>
                                        </div>
                                        <div>
                                            <i class="fas fa-tag mr-2"></i>
                                            <strong>Type:</strong> <?= ucfirst($appointment['appointment_type'] ?? 'Scheduled') ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($appointment['remarks'])): ?>
                                        <div class="mt-3 text-sm text-gray-600">
                                            <i class="fas fa-comment mr-2"></i>
                                            <strong>Notes:</strong> <?= esc($appointment['remarks']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="text-right md:ml-4">
                                    <div class="mb-2">
                                        <?php 
                                        $statusClass = match($appointment['approval_status'] ?? 'pending') {
                                            'approved' => 'bg-green-100 text-green-800',
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'declined' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                        ?>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full <?= $statusClass ?>">
                                            <?= ucfirst($appointment['approval_status'] ?? 'Pending') ?>
                                        </span>
                                    </div>
                                    <div>
                                        <?php 
                                        $appointmentStatusClass = match($appointment['status'] ?? 'pending') {
                                            'confirmed', 'scheduled' => 'bg-blue-100 text-blue-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            'no_show' => 'bg-gray-100 text-gray-800',
                                            default => 'bg-yellow-100 text-yellow-800'
                                        };
                                        ?>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full <?= $appointmentStatusClass ?>">
                                            <?= ucfirst(str_replace('_', ' ', $appointment['status'] ?? 'Pending')) ?>
                                        </span>
                                    </div>
                                    <?php if (($appointment['pending_change'] ?? 0) == 1): ?>
                                    <div class="mt-2">
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800 flex items-center">
                                            <i class="fas fa-clock mr-1"></i>
                                            Change Pending Review
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="mt-3">
                                        <!-- Patient actions: View, Cancel -->
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="<?= base_url('appointments/view/' . ($appointment['id'] ?? '')) ?>" class="text-sm text-indigo-600 hover:underline">View</a>
                                            <!-- Cancel form: patients can still cancel their appointment -->
                                            <form method="post" action="<?= base_url('patient/appointments/cancel/' . ($appointment['id'] ?? '')) ?>" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                                <input type="hidden" name="csrf_test_name" value="<?= csrf_hash() ?>">
                                                <button type="submit" class="text-sm text-yellow-600 hover:underline">Cancel</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No Appointments Yet</h3>
                    <p class="text-gray-500 mb-6">You haven't booked any appointments yet.</p>
                    <div class="text-center">
                        <div class="text-gray-500">To book an appointment, please use the Booking page or contact the clinic.</div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Cancel reason modal (hidden by default) -->
<div id="cancelModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-96">
        <h3 class="text-lg font-semibold mb-2">Reason for cancelling</h3>
        <textarea id="cancelReason" rows="4" class="w-full p-2 border rounded mb-4"></textarea>
        <div class="flex justify-end space-x-2">
            <button id="cancelModalClose" class="px-3 py-1 border rounded">Close</button>
            <button id="cancelModalConfirm" class="px-3 py-1 bg-yellow-600 text-white rounded">Confirm Cancel</button>
        </div>
    </div>
</div>

<?= view('templates/footer') ?>

<script>
// AJAX cancel and delete handlers with cancel reason modal
(() => {
    const list = document.getElementById('myAppointmentsList');
    let activeCancelForm = null;

    document.addEventListener('click', (e) => {
        // Intercept Cancel buttons (forms)
        if (e.target.matches('form button') && e.target.closest('form') && e.target.closest('form').action && e.target.closest('form').action.includes('/cancel/')) {
            e.preventDefault();
            activeCancelForm = e.target.closest('form');
            // open modal
            document.getElementById('cancelModal').classList.remove('hidden');
            document.getElementById('cancelModal').classList.add('flex');
        }

        // Intercept Delete buttons
            if (e.target.matches('form button') && e.target.closest('form') && e.target.closest('form').action && e.target.closest('form').action.includes('/delete/')) {
            e.preventDefault();
            if (!confirm('This will permanently delete the appointment. Continue?')) return;
            const form = e.target.closest('form');
            const action = form.action;
            const token = form.querySelector('input[name="csrf_test_name"]').value;
                fetch(action, {method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded'}, body: `csrf_test_name=${encodeURIComponent(token)}`})
                .then(r => r.json())
                .then(j => {
                    if (j.success) {
                        // remove the appointment card
                        const card = form.closest('.bg-white.rounded-lg');
                        if (card) card.remove();
                        alert('Appointment deleted');
                    } else {
                        alert(j.message || 'Failed to delete');
                    }
                }).catch(() => alert('Network error'));
        }
    });

    document.getElementById('cancelModalClose').addEventListener('click', () => {
        document.getElementById('cancelModal').classList.add('hidden');
        document.getElementById('cancelModal').classList.remove('flex');
        document.getElementById('cancelReason').value = '';
        activeCancelForm = null;
    });

    document.getElementById('cancelModalConfirm').addEventListener('click', () => {
        const reason = document.getElementById('cancelReason').value;
        if (!activeCancelForm) return;
        const action = activeCancelForm.action;
        const token = activeCancelForm.querySelector('input[name="csrf_test_name"]').value;
        const payload = `csrf_test_name=${encodeURIComponent(token)}&reason=${encodeURIComponent(reason)}`;
        fetch(action, {method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded'}, body: payload})
            .then(r => r.json())
            .then(j => {
                if (j.success) {
                    const card = activeCancelForm.closest('.bg-white.rounded-lg');
                    if (card) {
                        // update status badge visually
                        const status = card.querySelector('span.rounded-full');
                        if (status) status.textContent = 'Cancelled';
                        // optionally remove after short delay
                        setTimeout(() => { if (card) card.remove(); }, 800);
                    }
                    alert('Appointment cancelled');
                } else {
                    alert(j.message || 'Failed to cancel');
                }
            }).catch(() => alert('Network error'))
            .finally(() => {
                document.getElementById('cancelModalClose').click();
            });
    });
})();
</script>
