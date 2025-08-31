<?= view('templates/header') ?>

<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    <div class="flex-1 flex flex-col min-h-screen bg-white">
        <!-- Topbar -->
        <nav class="flex items-center justify-between bg-white shadow px-6 py-4 mb-6">
            <button id="sidebarToggleTop" class="block lg:hidden text-gray-600 mr-3 text-2xl focus:outline-none">
                <i class="fa fa-bars"></i>
            </button>
            <div class="flex items-center ml-auto">
                <span class="mr-4 hidden lg:inline text-gray-600 font-semibold"><?= $user['name'] ?? 'Admin' ?></span>
                <div class="relative">
                    <button class="focus:outline-none">
                        <img class="w-10 h-10 rounded-full border-2 border-gray-200" src="<?= base_url('img/undraw_profile.svg') ?>" alt="Profile">
                    </button>
                </div>
            </div>
        </nav>
        <!-- End of Topbar -->

        <main class="flex-1 px-6 pb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Branches Management</h1>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Branches</h3>
                <?php if (!empty($branches)): ?>
                    <div class="space-y-4">
                        <?php foreach ($branches as $b): ?>
                            <div class="border rounded p-4 flex items-center justify-between">
                                <div>
                                    <div class="font-semibold text-gray-800"><?= esc($b['name']) ?></div>
                                    <div class="text-sm text-gray-600"><?= esc($b['address'] ?? '') ?></div>
                                </div>
                                <div>
                                    <form method="post" action="<?= base_url('admin/branches/' . $b['id'] . '/save-hours') ?>">
                                        <div class="flex items-center gap-2">
                                            <label class="text-sm text-gray-600">Start</label>
                                            <input type="time" name="start_time" value="<?= esc($b['start_time'] ?? '08:00:00') ?>" class="border rounded px-2 py-1" />
                                            <label class="text-sm text-gray-600">End</label>
                                            <input type="time" name="end_time" value="<?= esc($b['end_time'] ?? '20:00:00') ?>" class="border rounded px-2 py-1" />
                                            <button class="ml-2 bg-blue-600 text-white px-3 py-1 rounded">Save</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12 text-gray-600">No branches found.</div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?= view('templates/footer') ?>