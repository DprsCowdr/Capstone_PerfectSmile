<?= view('templates/header') ?>
<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    <div class="flex-1 px-6 py-6">
        <h1 class="text-2xl font-bold mb-4">Billing & Payments</h1>
        <?php if (!empty($bills)): ?>
            <ul class="space-y-3">
                <?php foreach ($bills as $b): ?>
                    <li class="border p-3 rounded-lg flex justify-between items-center">
                        <div>
                            <div class="font-semibold"><?= esc($b['description'] ?? 'Invoice') ?></div>
                            <div class="text-sm text-gray-500"><?= esc($b['created_at'] ?? '') ?></div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold">PHP <?= esc(number_format($b['amount'] ?? 0, 2)) ?></div>
                            <a href="#" class="text-sm text-blue-600">Pay Now</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-gray-500">No invoices found.</p>
        <?php endif; ?>
    </div>
</div>
<?= view('templates/footer') ?>
