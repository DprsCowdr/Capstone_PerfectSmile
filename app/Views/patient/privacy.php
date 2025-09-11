<?php $user = $user ?? session('user') ?? []; ?>
<?= view('templates/header') ?>
<?= view('templates/sidebar', ['user' => $user ?? null]) ?>
<div class="min-h-screen bg-gray-50 flex">
    <div class="flex-1 flex flex-col min-h-screen min-w-0 overflow-hidden" data-sidebar-offset>
        <?= view('templates/patient_topbar', ['user' => $user ?? null]) ?>
        <main class="flex-1 px-6 pb-6 overflow-auto min-w-0" data-sidebar-offset>
            <div class="container mx-auto px-4 py-6">
                <h1 class="text-2xl font-bold mb-4">Privacy</h1>
                <div class="bg-white p-6 rounded shadow">
                    <p class="text-sm text-gray-600 mb-4">Download your data or deactivate your account.</p>
                    <div class="space-x-2">
                        <a href="#download" class="px-3 py-2 bg-gray-100 rounded hover:bg-gray-200">Download my data</a>
                        <a href="#deactivate" class="px-3 py-2 bg-red-100 text-red-700 rounded hover:bg-red-200">Deactivate account</a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<?= view('templates/footer') ?? '' ?>
