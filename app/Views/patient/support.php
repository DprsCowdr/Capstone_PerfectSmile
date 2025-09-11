<?php $user = $user ?? session('user') ?? []; ?>
<?= view('templates/header') ?>
<?= view('templates/sidebar', ['user' => $user ?? null]) ?>
<div class="min-h-screen bg-gray-50 flex">
    <div class="flex-1 flex flex-col min-h-screen min-w-0 overflow-hidden" data-sidebar-offset>
        <?= view('templates/patient_topbar', ['user' => $user ?? null]) ?>
        <main class="flex-1 px-6 pb-6 overflow-auto min-w-0" data-sidebar-offset>
            <div class="container mx-auto px-4 py-6">
                <h1 class="text-2xl font-bold mb-4">Support</h1>
                <div class="bg-white p-6 rounded shadow">
                    <p class="text-sm text-gray-600 mb-4">Need help? Contact the clinic or open a support ticket.</p>
                    <a href="mailto:support@example.com" class="text-blue-600 hover:underline">Contact Support</a>
                </div>
            </div>
        </main>
    </div>
</div>
<?= view('templates/footer') ?? '' ?>
