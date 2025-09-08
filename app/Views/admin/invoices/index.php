<?= view('templates/header', ['title' => 'Invoices']) ?>

<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user ?? null]) ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <div class="px-6 py-6">
            <!-- Blank boilerplate per request -->
        </div>
    </div>
    </div>
    </div>
</div>

<?= view('templates/footer') ?>
