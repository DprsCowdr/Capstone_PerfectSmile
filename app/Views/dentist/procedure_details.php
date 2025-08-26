<?php if (!service('request')->isAJAX() && !isset($_GET['modal'])): ?>
<?= view('templates/header') ?>
<div class="max-w-xl mx-auto mt-10 bg-white rounded-xl shadow p-8">
<?php else: ?>
<div class="p-6">
<?php endif; ?>

    <h2 id="procedurePanelTitle" class="text-2xl font-bold mb-6 text-indigo-700">
        Procedure Details
    </h2>

    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
            <div class="w-full border rounded px-3 py-2 bg-gray-50"><?= esc($procedure['title'] ?? $procedure['procedure_name'] ?? '') ?></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
            <div class="w-full border rounded px-3 py-2 bg-gray-50"><?= esc($procedure['procedure_date'] ?? '') ?></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <div class="w-full border rounded px-3 py-2 bg-gray-50"><?= esc($procedure['category'] ?? '') ?></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fee</label>
            <div class="w-full border rounded px-3 py-2 bg-gray-50">$<?= number_format($procedure['fee'] ?? 0, 2) ?></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Treatment Area</label>
            <div class="w-full border rounded px-3 py-2 bg-gray-50"><?= esc($procedure['treatment_area'] ?? '') ?></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <div class="w-full border rounded px-3 py-2 bg-gray-50"><?= ucfirst(str_replace('_',' ', $procedure['status'] ?? 'scheduled')) ?></div>
        </div>

        <?php if (!empty($procedureWithServices)): ?>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Services</label>
            <ul class="list-disc pl-5 text-sm text-gray-700">
                <?php foreach ($procedureWithServices as $svc): ?>
                    <li><?= esc($svc['service_name'] ?? $svc['name'] ?? '') ?> - $<?= number_format($svc['price'] ?? 0, 2) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="flex gap-3 mt-4">
            <a href="<?= base_url('dentist/procedures') ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">Back</a>
        </div>
    </div>

<?php if (!service('request')->isAJAX() && !isset($_GET['modal'])): ?>
<?= view('templates/footer') ?>
<?php endif; ?>
