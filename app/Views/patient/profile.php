<?php $user = $user ?? session('user') ?? []; ?>

<?= view('templates/header') ?>

<?= view('templates/sidebar', ['user' => $user ?? null]) ?>

<div class="min-h-screen bg-gray-50 flex">
    <div class="flex-1 flex flex-col min-h-screen min-w-0 overflow-hidden" data-sidebar-offset>
        <?= view('templates/patient_topbar', ['user' => $user ?? null]) ?>

        <main class="flex-1 px-6 pb-6 overflow-auto min-w-0" data-sidebar-offset>
            <div class="container mx-auto px-4 py-6">
                <h1 class="text-2xl font-bold mb-4">My Profile</h1>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded"><?= esc(session()->getFlashdata('success')) ?></div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded"><?= esc(session()->getFlashdata('error')) ?></div>
                <?php endif; ?>

                <form action="<?= base_url('patient/save-profile') ?>" method="post" enctype="multipart/form-data" class="max-w-2xl bg-white p-6 rounded shadow">
                    <?= csrf_field() ?>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full name</label>
                        <input name="name" type="text" value="<?= esc(old('name', $user['name'] ?? '')) ?>" class="w-full border px-3 py-2 rounded" required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input name="email" type="email" value="<?= esc(old('email', $user['email'] ?? '')) ?>" class="w-full border px-3 py-2 rounded">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input name="phone" type="text" value="<?= esc(old('phone', $user['phone'] ?? '')) ?>" class="w-full border px-3 py-2 rounded">
                        </div>
                    </div>

                    <div class="mb-4 mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <input name="address" type="text" value="<?= esc(old('address', $user['address'] ?? '')) ?>" class="w-full border px-3 py-2 rounded">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Preferred dentist</label>
                        <select name="preferred_dentist_id" class="w-full border px-3 py-2 rounded">
                            <option value="">— No preference —</option>
                            <?php foreach ($dentists as $d): ?>
                                <option value="<?= $d['id'] ?>" <?= (old('preferred_dentist_id', $user['preferred_dentist_id'] ?? '') == $d['id']) ? 'selected' : '' ?>><?= esc($d['name'] ?? $d['full_name'] ?? $d['username'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Avatar</label>
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 rounded-full bg-gray-100 overflow-hidden flex items-center justify-center">
                                <?php if (!empty($user['avatar'])): ?>
                                    <img src="<?= esc($user['avatar']) ?>" alt="avatar" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <span class="text-gray-600"><?= esc(substr($user['name'] ?? '',0,1)) ?></span>
                                <?php endif; ?>
                            </div>
                            <input type="file" name="avatar" accept="image/*">
                        </div>
                        <div class="text-xs text-gray-500 mt-1">Optional. Max 2MB recommended.</div>
                    </div>

                    <div class="flex items-center justify-between mt-6">
                        <div>
                            <a href="<?= base_url('patient/security') ?>" class="text-sm text-gray-600 hover:underline">Change password / Security</a>
                        </div>
                        <div>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

<?= view('templates/footer') ?? '' ?>
