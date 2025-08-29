<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto p-6" data-sidebar-offset>
    <h1 class="text-2xl font-semibold mb-4"><?= esc($title) ?></h1>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="bg-white shadow rounded p-6">
        <dl class="grid grid-cols-1 gap-4">
            <div>
                <dt class="font-medium text-sm text-gray-500">Date & Time</dt>
                <dd class="mt-1 text-lg"><?= esc($appointment['appointment_date']) ?> <?= esc($appointment['appointment_time']) ?></dd>
            </div>

            <div>
                <dt class="font-medium text-sm text-gray-500">Branch</dt>
                <dd class="mt-1"><?= esc($appointment['branch_name'] ?? 'Not specified') ?></dd>
            </div>

            <div>
                <dt class="font-medium text-sm text-gray-500">Dentist</dt>
                <dd class="mt-1"><?= esc(($appointment['dentist_first_name'] ?? '') . ' ' . ($appointment['dentist_last_name'] ?? '')) ?: 'Not specified' ?></dd>
            </div>

            <div>
                <dt class="font-medium text-sm text-gray-500">Service</dt>
                <dd class="mt-1"><?= esc($appointment['service_title'] ?? 'Not specified') ?></dd>
            </div>

            <div>
                <dt class="font-medium text-sm text-gray-500">Status</dt>
                <dd class="mt-1"> <span class="px-2 py-1 bg-gray-100 rounded"><?= esc($appointment['status']) ?></span></dd>
            </div>

            <div>
                <dt class="font-medium text-sm text-gray-500">Remarks</dt>
                <dd class="mt-1 whitespace-pre-wrap"><?= esc($appointment['remarks'] ?? '') ?></dd>
            </div>

            <?php if (! empty($appointment['pending_change'])): ?>
                <div>
                    <dt class="font-medium text-sm text-gray-500">Change request</dt>
                    <dd class="mt-1 text-yellow-600">A change is pending review by staff.</dd>
                </div>
            <?php endif; ?>
        </dl>

        <div class="mt-6 flex space-x-2">
            <a href="/appointments" class="inline-flex items-center px-4 py-2 bg-gray-200 rounded">Back</a>
            <?php if ($appointment['status'] !== 'cancelled'): ?>
                <form method="post" action="/appointments/cancel/<?= (int) $appointment['id'] ?>" onsubmit="return confirm('Cancel this appointment?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-500 text-white rounded">Cancel</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
