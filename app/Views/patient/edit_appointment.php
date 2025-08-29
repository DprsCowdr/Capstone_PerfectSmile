<?= view('templates/header') ?>

<?= view('templates/sidebar', ['user' => $user ?? null]) ?>

<div class="min-h-screen bg-gray-50 flex">
    <div class="flex-1 flex flex-col min-h-screen min-w-0 overflow-hidden with-sidebar-offset-active" data-sidebar-offset>
        <nav class="flex items-center justify-between bg-white shadow px-6 py-4 mb-6 flex-shrink-0">
            <div class="flex items-center">
                <a href="<?= base_url('patient/appointments') ?>" class="text-gray-600 hover:text-gray-800 mr-4">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <h1 class="text-xl font-semibold text-gray-800">Edit Appointment</h1>
            </div>
        </nav>

        <main class="flex-1 px-6 pb-6 overflow-auto min-w-0" data-sidebar-offset>
            <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow">
                <form method="post" action="<?= base_url('patient/appointments/update/' . ($appointment['id'] ?? '')) ?>">
                    <input type="hidden" name="csrf_test_name" value="<?= csrf_hash() ?>">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Branch</label>
                        <select name="branch_id" class="w-full mt-1 p-2 border rounded">
                            <?php foreach ($branches as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= ($appointment['branch_id'] == $b['id']) ? 'selected' : '' ?>><?= esc($b['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Dentist (optional)</label>
                        <select name="dentist_id" class="w-full mt-1 p-2 border rounded">
                            <option value="">No preference</option>
                            <?php foreach ($dentists as $d): ?>
                                <option value="<?= $d['id'] ?>" <?= ($appointment['dentist_id'] == $d['id']) ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" name="appointment_date" value="<?= esc($appointment['appointment_date']) ?>" class="w-full mt-1 p-2 border rounded">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Time</label>
                            <input type="time" name="appointment_time" value="<?= esc($appointment['appointment_time']) ?>" class="w-full mt-1 p-2 border rounded">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Service</label>
                        <select name="service_id" class="w-full mt-1 p-2 border rounded">
                            <option value="">Select a service</option>
                            <?php foreach ($services as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= (($appointment['service_id'] ?? null) == $s['id']) ? 'selected' : '' ?>><?= esc($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Duration (minutes)</label>
                        <input type="number" name="duration" value="<?= esc($appointment['duration_minutes'] ?? '') ?>" class="w-full mt-1 p-2 border rounded">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Notes / Remarks</label>
                        <textarea name="remarks" rows="4" class="w-full mt-1 p-2 border rounded"><?= esc($appointment['remarks'] ?? '') ?></textarea>
                    </div>

                    <div class="flex justify-end">
                        <a href="<?= base_url('patient/appointments') ?>" class="mr-3 inline-flex items-center px-4 py-2 border rounded text-sm">Cancel</a>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save Changes</button>
                    </div>
                </form>
            </div>
        </main>

    </div>
</div>

<?= view('templates/footer') ?>
