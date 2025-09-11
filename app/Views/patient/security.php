<?php $user = $user ?? session('user') ?? []; ?>
<?= view('templates/header') ?>
<?= view('templates/sidebar', ['user' => $user ?? null]) ?>
<div class="min-h-screen bg-gray-50 flex">
    <div class="flex-1 flex flex-col min-h-screen min-w-0 overflow-hidden" data-sidebar-offset>
        <?= view('templates/patient_topbar', ['user' => $user ?? null]) ?>
        <main class="flex-1 px-6 pb-6 overflow-auto min-w-0" data-sidebar-offset>
            <div class="container mx-auto px-4 py-6">
                <h1 class="text-2xl font-bold mb-4">Account & Security</h1>
                <div class="bg-white p-6 rounded shadow">
                    <p class="text-sm text-gray-600 mb-4">Change email, update password, and review recent sessions.</p>
                    <a href="#change-email" class="text-blue-600 hover:underline">Change Email</a> · <a href="#change-password" class="text-blue-600 hover:underline">Change Password</a>
                </div>
            </div>
        </main>
    </div>
</div>
<?= view('templates/footer') ?? '' ?>
