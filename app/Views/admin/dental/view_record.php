<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dental Record Details - Perfect Smile Admin</title>
    <link href="<?= base_url('vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .tooth-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(60px, 1fr));
            gap: 8px;
            margin: 15px 0;
        }
        .tooth-item {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 8px;
            text-align: center;
            font-size: 11px;
            transition: all 0.3s;
        }
        .tooth-healthy { background-color: #d1fae5; border-color: #10b981; }
        .tooth-cavity { background-color: #fee2e2; border-color: #ef4444; }
        .tooth-filling { background-color: #fef3c7; border-color: #f59e0b; }
        .tooth-crown { background-color: #dbeafe; border-color: #3b82f6; }
        .tooth-extraction { background-color: #fecaca; border-color: #ef4444; }
        .tooth-missing { background-color: #f3f4f6; border-color: #6b7280; }
        .tooth-root-canal { background-color: #ede9fe; border-color: #8b5cf6; }
    </style>
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
                                <i class="fas fa-file-medical-alt mr-3 text-blue-600"></i>Dental Record Details
                            </h1>
                            <p class="text-gray-600">Complete examination record and dental chart</p>
                        </div>
                        <div class="mt-4 sm:mt-0">
                            <a href="<?= base_url('admin/dental-records') ?>" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-arrow-left mr-2"></i>Back to Records
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Record Header -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg p-6 mb-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex-1">
                            <h2 class="text-2xl font-bold mb-4">
                                <i class="fas fa-user-md mr-2"></i>
                                <?= esc($record['patient_name']) ?> - Record #<?= $record['id'] ?>
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="mb-2"><i class="fas fa-calendar mr-2"></i>Record Date: <?= date('F j, Y', strtotime($record['record_date'])) ?></p>
                                    <p class="mb-2"><i class="fas fa-user-md mr-2"></i>Examining Dentist: Dr. <?= esc($record['dentist_name']) ?></p>
                                </div>
                                <div>
                                    <?php if ($record['appointment_datetime']): ?>
                                    <p class="mb-2"><i class="fas fa-clock mr-2"></i>Appointment: <?= date('F j, Y g:i A', strtotime($record['appointment_datetime'])) ?></p>
                                    <?php endif; ?>
                                    <?php if ($record['next_appointment_date']): ?>
                                    <p class="mb-2"><i class="fas fa-calendar-plus mr-2"></i>Next Visit: <?= date('F j, Y', strtotime($record['next_appointment_date'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="lg:ml-6 mt-4 lg:mt-0">
                            <div class="flex flex-col space-y-2">
                                <button onclick="printRecord()" 
                                        class="inline-flex items-center px-4 py-2 border border-white text-sm font-medium rounded-md text-white hover:bg-white hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white">
                                    <i class="fas fa-print mr-2"></i>Print Record
                                </button>
                                <?php if ($record['appointment_id']): ?>
                                <a href="<?= base_url('admin/dental-charts/' . $record['appointment_id']) ?>" 
                                   class="inline-flex items-center px-4 py-2 border border-white text-sm font-medium rounded-md text-white hover:bg-white hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white">
                                    <i class="fas fa-tooth mr-2"></i>View Chart
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Record Information -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Diagnosis -->
                        <div class="bg-white rounded-lg shadow border-l-4 border-blue-500 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">
                                <i class="fas fa-diagnoses mr-2 text-blue-600"></i>Diagnosis
                            </h3>
                            <p class="text-gray-700 whitespace-pre-line"><?= nl2br(esc($record['diagnosis'])) ?></p>
                        </div>

                        <!-- Treatment -->
                        <?php if ($record['treatment']): ?>
                        <div class="bg-white rounded-lg shadow border-l-4 border-green-500 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">
                                <i class="fas fa-procedures mr-2 text-green-600"></i>Treatment Provided
                            </h3>
                            <p class="text-gray-700 whitespace-pre-line"><?= nl2br(esc($record['treatment'])) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Clinical Notes -->
                        <?php if ($record['notes']): ?>
                        <div class="bg-white rounded-lg shadow border-l-4 border-yellow-500 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">
                                <i class="fas fa-sticky-note mr-2 text-yellow-600"></i>Clinical Notes
                            </h3>
                            <p class="text-gray-700 whitespace-pre-line"><?= nl2br(esc($record['notes'])) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Dental Chart -->
                        <?php if (!empty($record['dental_chart'])): ?>
                        <div class="bg-white rounded-lg shadow border-l-4 border-green-500 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                <i class="fas fa-tooth mr-2 text-green-600"></i>Dental Chart Findings
                            </h3>
                            
                            <h4 class="font-medium text-gray-900 mb-3">Permanent Teeth (Adult)</h4>
                            <div class="tooth-grid">
                                <?php 
                                $permanentTeeth = array_filter($record['dental_chart'], function($tooth) {
                                    return $tooth['tooth_number'] >= 11 && $tooth['tooth_number'] <= 48;
                                });
                                foreach ($permanentTeeth as $tooth): 
                                ?>
                                <div class="tooth-item tooth-<?= $tooth['status'] ?>">
                                    <div class="font-bold"><?= $tooth['tooth_number'] ?></div>
                                    <div class="text-xs"><?= ucfirst(str_replace('_', ' ', $tooth['status'])) ?></div>
                                    <?php if ($tooth['condition'] && $tooth['condition'] !== 'Normal'): ?>
                                    <div class="text-xs text-gray-600"><?= esc($tooth['condition']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <?php 
                            $primaryTeeth = array_filter($record['dental_chart'], function($tooth) {
                                return $tooth['tooth_number'] >= 51;
                            });
                            if (!empty($primaryTeeth)): 
                            ?>
                            <h4 class="font-medium text-gray-900 mb-3 mt-6">Primary Teeth (Children)</h4>
                            <div class="tooth-grid">
                                <?php foreach ($primaryTeeth as $tooth): ?>
                                <div class="tooth-item tooth-<?= $tooth['status'] ?>">
                                    <div class="font-bold"><?= $tooth['tooth_number'] ?></div>
                                    <div class="text-xs"><?= ucfirst(str_replace('_', ' ', $tooth['status'])) ?></div>
                                    <?php if ($tooth['condition'] && $tooth['condition'] !== 'Normal'): ?>
                                    <div class="text-xs text-gray-600"><?= esc($tooth['condition']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Sidebar Information -->
                    <div class="space-y-6">
                        <!-- X-Ray Images -->
                        <?php if ($record['xray_image_url']): ?>
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                <i class="fas fa-x-ray mr-2 text-purple-600"></i>X-Ray Images
                            </h3>
                            <div class="text-center">
                                <img src="<?= base_url($record['xray_image_url']) ?>" 
                                     alt="X-Ray Image" 
                                     class="w-full rounded-lg shadow-sm mb-3 cursor-pointer hover:shadow-lg transition-shadow"
                                     onclick="viewFullImage('<?= base_url($record['xray_image_url']) ?>')">
                                <p class="text-sm text-gray-500">Click to view full size</p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Treatment Summary -->
                        <?php if (!empty($record['dental_chart'])): ?>
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                <i class="fas fa-chart-pie mr-2 text-indigo-600"></i>Treatment Summary
                            </h3>
                            <?php
                            $statusCounts = [];
                            foreach ($record['dental_chart'] as $tooth) {
                                $status = $tooth['status'];
                                $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
                            }
                            ?>
                            <div class="space-y-3">
                                <?php foreach ($statusCounts as $status => $count): ?>
                                <div class="flex justify-between items-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $status === 'healthy' ? 'bg-green-100 text-green-800' : ($status === 'cavity' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') ?>">
                                        <?= ucfirst(str_replace('_', ' ', $status)) ?>
                                    </span>
                                    <span class="font-semibold text-gray-900"><?= $count ?> teeth</span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Follow-up Actions -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                <i class="fas fa-tasks mr-2 text-green-600"></i>Follow-up Actions
                            </h3>
                            
                            <?php if ($record['next_appointment_date']): ?>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                                <div class="text-sm">
                                    <i class="fas fa-calendar-plus mr-2 text-blue-600"></i>
                                    <span class="font-medium text-blue-900">Next Appointment Scheduled</span><br>
                                    <span class="text-blue-700"><?= date('F j, Y', strtotime($record['next_appointment_date'])) ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="space-y-2">
                                <button class="w-full inline-flex items-center justify-center px-3 py-2 border border-blue-300 text-sm font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="fas fa-calendar-plus mr-2"></i>Schedule Follow-up
                                </button>
                                <button class="w-full inline-flex items-center justify-center px-3 py-2 border border-indigo-300 text-sm font-medium rounded-md text-indigo-700 bg-indigo-50 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <i class="fas fa-phone mr-2"></i>Contact Patient
                                </button>
                                <button class="w-full inline-flex items-center justify-center px-3 py-2 border border-green-300 text-sm font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <i class="fas fa-procedures mr-2"></i>Schedule Treatment
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Full Image Modal -->
    <div id="imageModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">X-Ray Image</h3>
                <button onclick="closeImageModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times fa-lg"></i>
                </button>
            </div>
            <div class="text-center">
                <img id="fullImage" src="" alt="X-Ray" class="max-w-full h-auto">
            </div>
        </div>
    </div>

    <script>
        function printRecord() {
            window.print();
        }

        function viewFullImage(imageUrl) {
            document.getElementById('fullImage').src = imageUrl;
            document.getElementById('imageModal').classList.remove('hidden');
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });
    </script>
</body>
</html>
