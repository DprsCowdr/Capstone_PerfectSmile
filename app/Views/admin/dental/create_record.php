<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Dental Record - <?= esc($appointment['patient_name']) ?> - Perfect Smile Admin</title>
    <link href="<?= base_url('vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#F5ECFE]">
    <div class="min-h-screen flex">
        <?= view('templates/sidebar', ['user' => $user]) ?>
        
        <div class="flex-1 flex flex-col">
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

            <main class="flex-1 px-6 pb-6">
                <!-- Page Header -->
                <div class="mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800 mb-2">
                                <i class="fas fa-plus mr-3 text-blue-600"></i>Create Dental Record
                            </h1>
                            <p class="text-gray-600">Create a new dental examination record for patient</p>
                        </div>
                        <div class="mt-4 sm:mt-0">
                            <a href="<?= base_url('admin/dental-records') ?>" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-arrow-left mr-2"></i>Back to Records
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Patient & Appointment Info -->
                <div class="bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-lg p-6 mb-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex-1">
                            <h2 class="text-2xl font-bold mb-4">
                                <i class="fas fa-user mr-2"></i>
                                <?= esc($appointment['patient_name']) ?> - New Dental Record
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="mb-2"><i class="fas fa-calendar mr-2"></i>Appointment: <?= date('F j, Y g:i A', strtotime($appointment['appointment_datetime'])) ?></p>
                                    <p class="mb-2"><i class="fas fa-user-md mr-2"></i>Examining Dentist: Dr. <?= esc($appointment['dentist_name']) ?></p>
                                </div>
                                <div>
                                    <p class="mb-2"><i class="fas fa-building mr-2"></i>Branch: <?= esc($appointment['branch_name']) ?></p>
                                    <p class="mb-2"><i class="fas fa-info-circle mr-2"></i>Status: 
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"><?= ucfirst($appointment['status']) ?></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-file-medical mr-2 text-blue-600"></i>Examination Record Details
                        </h3>
                    </div>
                    
                    <form action="<?= base_url('admin/dental-records/store-basic') ?>" method="POST" class="p-6">
                        <input type="hidden" name="patient_id" value="<?= $appointment['user_id'] ?>">
                        <input type="hidden" name="appointment_id" value="<?= $appointment['id'] ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Examining Dentist</label>
                                <select name="dentist_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                    <option value="">Select Dentist</option>
                                    <option value="<?= $appointment['dentist_id'] ?>" selected>Dr. <?= esc($appointment['dentist_name']) ?> (Assigned)</option>
                                    <?php
                                    // You could add other dentists here if needed
                                    ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Next Appointment Date & Time</label>
                                <input type="datetime-local" name="next_appointment_datetime" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" min="<?= date('Y-m-d\TH:i') ?>">
                                <p class="mt-1 text-sm text-gray-500">Select the date and time for the next appointment.</p>
                            </div>
                        </div>

                        <!-- Automatically Create Appointment Option -->
                        <div class="mb-6">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="create_appointment" name="create_appointment" type="checkbox" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="create_appointment" class="font-medium text-gray-700">Automatically create next appointment</label>
                                    <p class="text-gray-500">When checked, this will automatically create the next appointment in the system when a date/time is provided above.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Diagnosis</label>
                                <textarea name="diagnosis" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter diagnosis findings..." required></textarea>
                                <p class="mt-1 text-sm text-gray-500">Describe the examination findings and any diagnosed conditions.</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Treatment Plan</label>
                                <textarea name="treatment" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter treatment plan..." required></textarea>
                                <p class="mt-1 text-sm text-gray-500">Outline the recommended treatment plan for the patient.</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Additional Notes</label>
                                <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Additional notes or observations..."></textarea>
                                <p class="mt-1 text-sm text-gray-500">Any additional observations, patient feedback, or special considerations.</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">X-Ray Image URL (optional)</label>
                                <input type="url" name="xray_image_url" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="https://...">
                                <p class="mt-1 text-sm text-gray-500">Link to X-ray images if available.</p>
                            </div>
                        </div>

                        <!-- Information Box -->
                        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-400"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">What's Next?</h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <p>After creating this dental record, you can:</p>
                                        <ul class="list-disc list-inside mt-1">
                                            <li>Create a detailed dental chart with tooth-by-tooth examination</li>
                                            <li>Schedule follow-up appointments</li>
                                            <li>Print the record for patient files</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-8 flex justify-end space-x-4">
                            <a href="<?= base_url('admin/dental-records') ?>" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-save mr-2"></i>Create Record
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
