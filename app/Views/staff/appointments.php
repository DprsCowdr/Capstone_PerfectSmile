<?= view('templates/header') ?>
<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <main class="flex-1 px-6 py-8">
            <?= view('templates/appointmentTable', [
                'appointments' => $appointments, 
                'user' => $user,
                'patients' => $patients ?? [],
                'branches' => $branches ?? [],
                'dentists' => $dentists ?? [],
                'availability' => $availability ?? []
            ]) ?>
        </main>
    </div>
</div>
<?= view('templates/footer') ?> 