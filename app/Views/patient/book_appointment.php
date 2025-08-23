<?= view('templates/header') ?>

<div class="min-h-screen bg-gray-50 flex">
    <div class="flex-1 flex flex-col min-h-screen min-w-0 overflow-hidden">
        <!-- Topbar -->
        <nav class="flex items-center justify-between bg-white shadow px-6 py-4 mb-6 flex-shrink-0">
            <div class="flex items-center">
                <a href="<?= base_url('patient/dashboard') ?>" class="text-gray-600 hover:text-gray-800 mr-4">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <h1 class="text-xl font-semibold text-gray-800">Book Appointments</h1>
            </div>
            <div class="flex items-center ml-auto">
                <span class="mr-4 hidden lg:inline text-gray-600 font-semibold"><?= $user['name'] ?? 'Patient' ?></span>
                <div class="relative">
                    <button class="focus:outline-none">
                        <img class="w-10 h-10 rounded-full border-2 border-gray-200" src="<?= base_url('img/undraw_profile.svg') ?>" alt="Profile">
                    </button>
                    <div class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50" id="userDropdownMenu">
                        <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100"><i class="fas fa-user mr-2 text-gray-400"></i>Profile</a>
                        <div class="border-t my-1"></div>
                        <a href="<?= base_url('auth/logout') ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100"><i class="fas fa-sign-out-alt mr-2 text-gray-400"></i>Logout</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1 px-6 pb-6 overflow-auto min-w-0">
            <div class="max-w-2xl mx-auto">
                <!-- Flash Messages -->
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <!-- Booking Form -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <form method="POST" action="<?= base_url('patient/book-appointment') ?>">
                        <?= csrf_field() ?>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Branch Selection -->
                            <div>
                                <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-2">Branch *</label>
                                <select id="branch_id" name="branch_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Branch</option>
                                    <?php foreach ($branches as $branch): ?>
                                        <option value="<?= $branch['id'] ?>" <?= old('branch_id') == $branch['id'] ? 'selected' : '' ?>>
                                            <?= esc($branch['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Dentist Selection -->
                            <div>
                                <label for="dentist_id" class="block text-sm font-medium text-gray-700 mb-2">Preferred Dentist (Optional)</label>
                                <select id="dentist_id" name="dentist_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Any Available</option>
                                    <?php foreach ($dentists as $dentist): ?>
                                        <option value="<?= $dentist['id'] ?>" <?= old('dentist_id') == $dentist['id'] ? 'selected' : '' ?>>
                                            Dr. <?= esc($dentist['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Appointment Date -->
                            <div>
                                <label for="appointment_date" class="block text-sm font-medium text-gray-700 mb-2">Appointment Date *</label>
                                <input type="date" id="appointment_date" name="appointment_date" required 
                                       min="<?= date('Y-m-d') ?>"
                                       value="<?= old('appointment_date') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            <!-- Appointment Time -->
                            <div>
                                <label for="appointment_time" class="block text-sm font-medium text-gray-700 mb-2">Appointment Time *</label>
                                <select id="appointment_time" name="appointment_time" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Time</option>
                                    <?php 
                                    for ($hour = 8; $hour <= 17; $hour++) {
                                        for ($minute = 0; $minute < 60; $minute += 30) {
                                            $time = sprintf('%02d:%02d', $hour, $minute);
                                            $display = date('g:i A', strtotime($time));
                                            $selected = old('appointment_time') == $time ? 'selected' : '';
                                            echo "<option value=\"$time\" $selected>$display</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- Remarks -->
                        <div class="mt-6">
                            <label for="remarks" class="block text-sm font-medium text-gray-700 mb-2">Additional Notes (Optional)</label>
                            <textarea id="remarks" name="remarks" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                      placeholder="Any specific concerns or requests..."><?= old('remarks') ?></textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-8 flex justify-end space-x-4">
                            <a href="<?= base_url('patient/dashboard') ?>" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-calendar-plus mr-2"></i>
                                Submit Request
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Information Card -->
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mr-3 mt-0.5"></i>
                        <div class="text-sm text-blue-700">
                            <p class="font-medium mb-2">Please Note:</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li>Your appointment request will be reviewed and confirmed by our staff</li>
                                <li>You will receive a confirmation once your appointment is approved</li>
                                <li>Clinic hours are 8:00 AM to 6:00 PM, Monday to Saturday</li>
                                <li>Please arrive 15 minutes before your scheduled appointment</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?= view('templates/footer') ?>
