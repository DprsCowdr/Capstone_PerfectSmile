<?= view('templates/header') ?>
<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    <div class="flex-1 px-6 py-6">
        <h1 class="text-2xl font-bold mb-4">Prescriptions</h1>
        <?php if (!empty($prescriptions)): ?>
            <ul class="space-y-3">
                <?php foreach ($prescriptions as $p): ?>
                    <li class="border p-3 rounded-lg">
                        <div class="text-sm text-gray-500"><?= esc($p['issued_at'] ?? '') ?></div>
                        <div class="font-semibold"><?= esc($p['medication'] ?? 'Prescription') ?></div>
                        <div class="text-sm text-gray-700"><?= esc($p['instructions'] ?? '') ?></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-gray-500">No prescriptions found.</p>
        <?php endif; ?>
    </div>
</div>
<?= view('templates/footer') ?>
