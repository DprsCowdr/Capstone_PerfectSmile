<?= view('templates/header') ?>
<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    <div class="flex-1 px-6 py-6">
        <h1 class="text-2xl font-bold mb-4">My Records</h1>
        <?php if (!empty($records)): ?>
            <ul class="space-y-3">
                <?php foreach ($records as $r): ?>
                    <li class="border p-3 rounded-lg">
                        <div class="text-sm text-gray-600"><?= esc($r['record_date'] ?? '') ?></div>
                        <div class="font-semibold"><?= esc($r['procedure'] ?? 'Procedure') ?></div>
                        <div class="text-sm text-gray-700"><?= esc($r['notes'] ?? '') ?></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-gray-500">No records found.</p>
        <?php endif; ?>
    </div>
</div>
<?= view('templates/footer') ?>
