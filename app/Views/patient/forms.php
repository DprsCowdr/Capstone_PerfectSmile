<?= view('templates/header') ?>
<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    <div class="flex-1 px-6 py-6">
        <h1 class="text-2xl font-bold mb-4">Forms</h1>
        <div class="bg-white border rounded-lg p-6">
            <h3 class="font-semibold mb-2">Medical History</h3>
            <?php if (!empty($medicalHistory)): ?>
                <pre class="text-sm text-gray-700 bg-gray-50 p-3 rounded"><?= esc(json_encode($medicalHistory, JSON_PRETTY_PRINT)) ?></pre>
            <?php else: ?>
                <p class="text-gray-500">No medical history found. You can fill this from the Quick Actions.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= view('templates/footer') ?>
