<?= view('templates/header') ?>
<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    <div class="flex-1 px-6 py-6">
        <h1 class="text-2xl font-bold mb-4">My Profile</h1>
        <div class="bg-white border rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm text-gray-600">Name</p>
                    <div class="font-semibold"><?= esc($user['name'] ?? '') ?></div>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Email</p>
                    <div class="font-semibold"><?= esc($user['email'] ?? '') ?></div>
                </div>
            </div>
        </div>
        <div class="bg-white border rounded-lg p-6 mt-6">
            <h2 class="text-lg font-semibold mb-3">Preferences</h2>
            <form method="post" action="/patient/save-profile">
                <?= csrf_field() ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-sm text-gray-600 block">Preferred Dentist (optional)</label>
                        <select name="preferred_dentist_id" class="w-full border rounded p-2">
                            <option value="">-- No preference --</option>
                            <?php foreach (($dentists ?? []) as $d): ?>
                                <option value="<?= esc($d['id']) ?>" <?= (isset($user['preferred_dentist_id']) && (string)$user['preferred_dentist_id'] === (string)$d['id']) ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save Preferences</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?= view('templates/footer') ?>
