<?php $user = $user ?? session('user') ?? []; ?>
<?= view('templates/header') ?>
<?= view('templates/sidebar', ['user' => $user ?? null]) ?>
<div class="min-h-screen bg-gray-50 flex">
    <div class="flex-1 flex flex-col min-h-screen min-w-0 overflow-hidden" data-sidebar-offset>
        <?= view('templates/patient_topbar', ['user' => $user ?? null]) ?>
        <main class="flex-1 px-6 pb-6 overflow-auto min-w-0" data-sidebar-offset>
            <div class="container mx-auto px-4 py-6">
                <h1 class="text-2xl font-bold mb-4">Preferences</h1>
                <div class="bg-white p-6 rounded shadow space-y-4">
                    <div>
                        <label class="flex items-center space-x-2"><input type="checkbox"> <span>Enable appointment notifications</span></label>
                    </div>
                    <div>
                        <label class="block text-sm">Language</label>
                        <select class="border rounded px-2 py-1"><option>English</option></select>
                    </div>
                    <div>
                        <label class="block text-sm">Theme</label>
                        <select class="border rounded px-2 py-1"><option>Light</option><option>Dark</option></select>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<?= view('templates/footer') ?? '' ?>
