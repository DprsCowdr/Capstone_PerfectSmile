<?php $user = $user ?? session('user') ?? []; ?>

<?= view('templates/header') ?>

<?= view('templates/sidebar', ['user' => $user ?? null]) ?>

<div class="min-h-screen bg-gray-50 flex">
    <div class="flex-1 flex flex-col min-h-screen min-w-0 overflow-hidden" data-sidebar-offset>
    <?= view('templates/patient_topbar', ['user' => $user ?? null]) ?>

        <main class="flex-1 px-6 pb-6 overflow-auto min-w-0" data-sidebar-offset>
            <div class="container mx-auto px-4 py-4">
                <div class="mb-4">
                    <a href="<?= base_url('patient/records') ?>" class="text-sm text-gray-600 hover:underline">&larr; Back to My Records</a>
                    <h1 class="text-xl font-semibold text-gray-800 mt-1">Prescription #<?= esc($prescription['id']) ?></h1>
                </div>
            <div class="container mx-auto px-4 py-4">
                <div class="bg-white shadow-sm rounded border p-4 mb-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-sm text-gray-600">Issued</div>
                            <div class="font-medium"><?= !empty($prescription['issue_date']) ? date('M j, Y', strtotime($prescription['issue_date'])) : '-' ?></div>
                        </div>
                        <div class="text-sm text-gray-600">Dentist: <span class="font-medium text-gray-900"><?= esc($prescription['dentist_name'] ?? 'Unknown') ?></span></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="bg-white shadow-sm rounded border p-4">
                        <h2 class="font-semibold mb-2">Patient</h2>
                        <div class="text-sm text-gray-700">Name: <?= esc($prescription['patient_name'] ?? '-') ?></div>
                        <div class="text-sm text-gray-700">Age: <?= esc($prescription['patient_age'] ?? '-') ?></div>
                        <div class="text-sm text-gray-700">Gender: <?= esc($prescription['patient_gender'] ?? '-') ?></div>
                        <div class="text-sm text-gray-700">Address: <?= esc($prescription['patient_address'] ?? '-') ?></div>
                    </div>
                    <div class="bg-white shadow-sm rounded border p-4">
                        <h2 class="font-semibold mb-2">Dentist</h2>
                        <div class="text-sm text-gray-700">Name: <?= esc($prescription['dentist_name'] ?? '-') ?></div>
                        <div class="text-sm text-gray-700">License: <?= esc($prescription['license_no'] ?? '-') ?></div>
                        <div class="text-sm text-gray-700">PTR: <?= esc($prescription['ptr_no'] ?? '-') ?></div>
                    </div>
                </div>

                <?php if (!empty($prescription['instructions'])): ?>
                    <div class="bg-white shadow-sm rounded border p-4 mb-4">
                        <h3 class="font-semibold mb-2">Instructions</h3>
                        <div class="text-sm text-gray-700"><?= nl2br(esc($prescription['instructions'])) ?></div>
                    </div>
                <?php endif; ?>

                <div class="bg-white shadow-sm rounded border p-4">
                    <h3 class="font-semibold mb-3">Medicines</h3>
                    <?php if (!empty($items)): ?>
                        <ul class="space-y-2">
                            <?php foreach ($items as $it): ?>
                                <li class="p-3 border rounded bg-gray-50">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <div class="font-medium"><?= esc($it['medicine_name']) ?> <span class="text-sm text-gray-600 ml-2"><?= esc($it['dosage']) ?></span></div>
                                            <div class="text-sm text-gray-600">Frequency: <?= esc($it['frequency']) ?> · Duration: <?= esc($it['duration']) ?></div>
                                        </div>
                                    </div>
                                    <?php if (!empty($it['instructions'])): ?>
                                        <div class="mt-2 text-sm text-gray-700">Instructions: <?= esc($it['instructions']) ?></div>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-gray-500">No medicines listed for this prescription.</div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?= view('templates/footer') ?? '' ?>
