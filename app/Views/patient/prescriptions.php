<?php $user = $user ?? session('user') ?? []; ?>

<?= view('templates/header') ?>

<?= view('templates/sidebar', ['user' => $user ?? null]) ?>

<div class="min-h-screen bg-gray-50 flex">
    <div class="flex-1 flex flex-col min-h-screen min-w-0 overflow-hidden" data-sidebar-offset>
    <?= view('templates/patient_topbar', ['user' => $user ?? null]) ?>

        <main class="flex-1 px-6 pb-6 overflow-auto min-w-0" data-sidebar-offset>
            <div class="container mx-auto px-4 py-4">
                <h1 class="text-2xl font-bold mb-4">My Prescriptions (Records → Prescriptions)</h1>
                <div class="mb-3"><a href="<?= site_url('patient/records') ?>" class="text-sm text-gray-600 hover:underline">&larr; Back to My Records</a></div>

                <?php if (empty($prescriptions)): ?>
                    <div class="text-gray-500">You have no prescriptions yet.</div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($prescriptions as $p): ?>
                            <div class="bg-white p-4 rounded border">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="font-medium">Issued: <?= !empty($p['issue_date']) ? date('M j, Y', strtotime($p['issue_date'])) : '-' ?></div>
                                        <div class="text-sm text-gray-600">Dentist: <?= esc($p['dentist_name'] ?? 'Unknown') ?></div>
                                    </div>
                                    <div>
                                        <a href="<?= base_url('patient/prescriptions/'.$p['id']) ?>" class="text-blue-600 mr-3">View</a>
                                        <a href="<?= base_url('patient/prescriptions/'.$p['id'].'/download-file') ?>" class="text-sm inline-flex items-center px-2 py-1 bg-green-600 text-white rounded" target="_blank">Download</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<!-- Preview removed: only View and Download remain for patient list -->

<?= view('templates/footer') ?? '' ?>
