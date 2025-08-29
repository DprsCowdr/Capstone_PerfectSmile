<?= view('templates/header') ?>

<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    <div class="flex-1 flex flex-col min-h-screen bg-white">
        <main class="flex-1 px-6 py-8 bg-white">
            <!-- Patient-specific header -->
            <!-- <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Book Your Appointment</h1>
                <p class="text-gray-600">Select a date and time that works for youu</p>
            </div> -->

            <?= view('templates/appointmentTable', [
                'appointments' => $appointments ?? [], 
                'user' => $user,
                'patients' => [$user], // Only show current patient
                'branches' => $branches ?? [],
                'dentists' => $dentists ?? [],
                'isPatientView' => true // Flag to customize the calendar
            ]) ?>

            <!-- Available slots & Time Taken moved into header quick-buttons menus above. -->
        </main>
    </div>
</div>

<?= view('templates/footer') ?>
