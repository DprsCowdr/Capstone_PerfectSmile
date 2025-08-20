<?= view('templates/header') ?>
<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    <div class="flex-1 flex flex-col min-h-screen bg-white">
        <main class="flex-1 px-6 py-8 bg-white">
            <?= view('templates/invoicesTable', ['invoices' => $invoices, 'user' => $user]) ?>
        </main>
    </div>
</div>
<?= view('templates/footer') ?>
