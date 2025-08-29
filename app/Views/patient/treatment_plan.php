<?= view('templates/header') ?>
<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    <div class="flex-1 px-6 py-6">
        <h1 class="text-2xl font-bold mb-4">Treatment Plan & Progress</h1>
        <?php if (!empty($plan)): ?>
            <ul class="space-y-3">
                <?php foreach ($plan as $item): ?>
                    <li class="border p-3 rounded-lg">
                        <div class="text-sm text-gray-500"><?= esc($item['created_at'] ?? '') ?></div>
                        <div class="font-semibold"><?= esc($item['title'] ?? 'Plan Item') ?></div>
                        <div class="text-sm text-gray-700"><?= esc($item['notes'] ?? '') ?></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-gray-500">No active treatment plan.</p>
        <?php endif; ?>
    </div>
</div>
<?= view('templates/footer') ?>
