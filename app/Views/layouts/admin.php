<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Perfect Smile Admin') ?></title>
    <link href="<?= base_url('vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/style.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/admin.css') ?>" rel="stylesheet">
    <?= $additionalCSS ?? '' ?>
    <?= $this->renderSection('head') ?>
</head>
<body class="admin-body">
    <div class="min-h-screen flex bg-white">
        <?= view('templates/sidebar', ['user' => $user ?? null]) ?>
        
        <div class="flex-1 flex flex-col bg-white">
            <!-- Topbar -->
            <nav class="flex items-center justify-between bg-white shadow px-6 py-4 mb-6">
                <button id="sidebarToggleTop" class="block lg:hidden text-gray-600 mr-3 text-2xl focus:outline-none">
                    <i class="fa fa-bars"></i>
                </button>
                <div class="flex items-center ml-auto">
                    <span class="mr-4 hidden lg:inline text-gray-600 font-semibold"><?= esc($user['name'] ?? 'Admin') ?></span>
                    <div class="relative">
                        <button class="focus:outline-none">
                            <img class="w-10 h-10 rounded-full border-2 border-gray-200" src="<?= base_url('img/undraw_profile.svg') ?>" alt="Profile">
                        </button>
                    </div>
                </div>
            </nav>

            <main class="flex-1 px-6 pb-6 bg-white">
                <?= $this->renderSection('content') ?>
            </main>
        </div>
    </div>
    
    <?= $additionalJS ?? '' ?>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
