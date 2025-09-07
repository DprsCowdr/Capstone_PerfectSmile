<?= view('templates/header') ?>

<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <!-- Topbar -->
        <nav class="flex items-center justify-between bg-white shadow px-6 py-4 mb-6">
            <button id="sidebarToggleTop" class="block lg:hidden text-gray-600 mr-3 text-2xl focus:outline-none">
                <i class="fa fa-bars"></i>
            </button>
            <div class="flex items-center ml-auto">
                <span class="mr-4 hidden lg:inline text-gray-600 font-semibold"><?= $user['name'] ?? 'Dentist' ?></span>
                <div class="relative">
                    <button class="focus:outline-none">
                        <img class="w-10 h-10 rounded-full border-2 border-gray-200" src="<?= base_url('img/undraw_profile.svg') ?>" alt="Profile">
                    </button>
                </div>
            </div>
        </nav>
        
        <main class="flex-1 p-8">

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Dental Record</h1>
                    <p class="mt-1 text-sm text-gray-500">View patient dental examination details</p>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/checkup" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                    <a href="/auth/logout" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Patient Information -->
        <div class="bg-white rounded-xl shadow-lg mb-8">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-user text-blue-500 mr-3"></i>
                    Patient Information
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Patient Name</label>
                        <p class="text-lg font-semibold text-gray-900"><?= $record['patient_name'] ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dentist</label>
                        <p class="text-gray-600">Dr. <?= $record['dentist_name'] ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Record Date</label>
                        <p class="text-gray-600"><?= date('M j, Y', strtotime($record['record_date'])) ?></p>
                    </div>
                    <?php if ($record['appointment_datetime']): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Appointment Time</label>
                        <p class="text-gray-600"><?= date('g:i A', strtotime($record['appointment_datetime'])) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($record['next_appointment_date']): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Next Appointment</label>
                        <p class="text-gray-600"><?= date('M j, Y', strtotime($record['next_appointment_date'])) ?></p>
                        <?php if (isset($record['next_appointment_id']) && $record['next_appointment_id']): ?>
                        <p class="text-sm text-blue-600 mt-1">
                            <i class="fas fa-link mr-1"></i>
                            <a href="/admin/appointments" class="hover:underline">View Follow-up Appointment</a>
                        </p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Chart Summary -->
        <?php if (!empty($chartSummary)): ?>
        <div class="bg-white rounded-xl shadow-lg mb-8">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-chart-pie text-blue-500 mr-3"></i>
                    Dental Chart Summary
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-green-600"><?= $chartSummary['healthy_teeth'] ?></div>
                        <div class="text-sm text-green-700">Healthy Teeth</div>
                    </div>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-red-600"><?= $chartSummary['cavities'] ?></div>
                        <div class="text-sm text-red-700">Cavities</div>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-blue-600"><?= $chartSummary['filled_teeth'] ?></div>
                        <div class="text-sm text-blue-700">Filled Teeth</div>
                    </div>
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-slate-600"><?= $chartSummary['crowns'] ?></div>
                        <div class="text-sm text-slate-700">Crowns</div>
                    </div>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-yellow-600"><?= $chartSummary['root_canals'] ?></div>
                        <div class="text-sm text-yellow-700">Root Canals</div>
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-gray-600"><?= $chartSummary['missing_teeth'] ?></div>
                        <div class="text-sm text-gray-700">Missing Teeth</div>
                    </div>
                </div>

                <?php if (!empty($chartSummary['treatments_needed'])): ?>
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Treatments Needed</h3>
                    <div class="space-y-2">
                        <?php foreach ($chartSummary['treatments_needed'] as $treatment): ?>
                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
                            <div class="flex justify-between items-center">
                                <span class="font-semibold text-orange-800">Tooth <?= $treatment['tooth'] ?> - <?= ucfirst($treatment['treatment']) ?></span>
                                <?php if ($treatment['notes']): ?>
                                <span class="text-sm text-orange-600"><?= $treatment['notes'] ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Diagnosis and Treatment -->
        <div class="bg-white rounded-xl shadow-lg mb-8">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-stethoscope text-blue-500 mr-3"></i>
                    Diagnosis & Treatment
                </h2>
            </div>
            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Diagnosis</label>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <p class="text-gray-800"><?= nl2br(htmlspecialchars($record['diagnosis'])) ?></p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Treatment Plan</label>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <p class="text-gray-800"><?= nl2br(htmlspecialchars($record['treatment'])) ?></p>
                    </div>
                </div>

                <?php if ($record['notes']): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Additional Notes</label>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <p class="text-gray-800"><?= nl2br(htmlspecialchars($record['notes'])) ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Visual Dental Chart -->
        <?php if (!empty($record['visual_chart_data'])): ?>
        <div class="bg-white rounded-xl shadow-lg mb-8">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-image text-green-500 mr-3"></i>
                    Visual Dental Chart
                </h2>
                <p class="text-sm text-gray-600 mt-1">Dentist's visual annotations and markings</p>
            </div>
            <div class="p-6">
                <div class="flex justify-center">
                    <div class="border-2 border-gray-200 rounded-lg overflow-hidden bg-white max-w-full">
                        <!-- Display the complete saved visual chart (background + annotations) -->
                        <img src="<?= htmlspecialchars($record['visual_chart_data']) ?>" alt="Complete Dental Chart with Annotations" class="max-w-full h-auto block">
                    </div>
                </div>
                
                <!-- Chart Information -->
                <div class="mt-4 p-4 bg-green-50 rounded-lg">
                    <p class="text-sm text-green-700">
                        <i class="fas fa-info-circle mr-2"></i>
                        This visual chart shows the dentist's markings and annotations made during the examination on <strong><?= date('M j, Y', strtotime($record['record_date'])) ?></strong>.
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Dental Chart Details -->
        <?php if (!empty($record['dental_chart'])): ?>
        <div class="bg-white rounded-xl shadow-lg">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-tooth text-blue-500 mr-3"></i>
                    Detailed Dental Chart
                </h2>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tooth</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Condition</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Treatment</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($record['dental_chart'] as $tooth): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    Tooth <?= $tooth['tooth_number'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php if ($tooth['tooth_condition']): ?>
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            <?php
                                            switch($tooth['tooth_condition']) {
                                                case 'healthy': echo 'bg-green-100 text-green-800'; break;
                                                case 'cavity': echo 'bg-red-100 text-red-800'; break;
                                                case 'filled': echo 'bg-blue-100 text-blue-800'; break;
                                                case 'crown': echo 'bg-slate-100 text-slate-800'; break;
                                                case 'missing': echo 'bg-gray-100 text-gray-800'; break;
                                                case 'root_canal': echo 'bg-yellow-100 text-yellow-800'; break;
                                                case 'extraction_needed': echo 'bg-red-200 text-red-800'; break;
                                                default: echo 'bg-gray-100 text-gray-800';
                                            }
                                            ?>">
                                            <?= ucfirst(str_replace('_', ' ', $tooth['tooth_condition'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php if ($tooth['treatment_needed'] && $tooth['treatment_needed'] !== 'none'): ?>
                                        <span class="px-2 py-1 text-xs rounded-full bg-orange-100 text-orange-800">
                                            <?= ucfirst($tooth['treatment_needed']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400">None</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?= $tooth['treatment_notes'] ?: '-' ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

        </main>
    </div>
</div>

<?= view('templates/footer') ?> 