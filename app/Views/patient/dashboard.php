<?= view('templates/header') ?>
<div id="wrapper">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    <div id="content-wrapper" class="flex flex-col">
        <div id="content">
            <div class="max-w-7xl mx-auto px-4 mt-5">
                <h1>Patient Dashboard</h1>
                <p>Welcome, <?= esc($user['name'] ?? 'Patient') ?>!</p>
                <p>This is your dashboard. Add your widgets and stats here.</p>
            </div>
        </div>
    </div>
</div>
<?= view('templates/footer') ?> 