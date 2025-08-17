<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dental Records - Perfect Smile Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="<?= base_url('vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet">
    <style>
        .folder-icon { transition: transform 0.2s ease; }
        .folder-open { transform: rotate(90deg); }
        .patient-folder { transition: all 0.3s ease; }
        .patient-folder:hover { transform: translateY(-2px); }
        .record-item { transition: background-color 0.2s ease; }
        .record-item:hover { background-color: #f8fafc; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <?= view('templates/sidebar', ['user' => $user]) ?>
        
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <nav class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            <i class="fas fa-folder-open mr-3 text-blue-600"></i>
                            Patient Records
                        </h1>
                        <p class="text-gray-600 mt-1">Organized dental examination records</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">Welcome, <?= esc($user['name'] ?? 'Admin') ?></span>
                        <img class="w-8 h-8 rounded-full" src="<?= base_url('img/undraw_profile.svg') ?>" alt="Profile">
                    </div>
                </div>
            </nav>

            <main class="flex-1 px-6 py-6">
                <!-- Search and Filter Bar -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex-1">
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="text" id="searchPatients" placeholder="Search patients by name or email..." 
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <select id="filterPeriod" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">All Time</option>
                                <option value="1">Last Month</option>
                                <option value="3">Last 3 Months</option>
                                <option value="6">Last 6 Months</option>
                                <option value="12">Last Year</option>
                            </select>
                            <button onclick="expandAllFolders()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-expand-arrows-alt mr-2"></i>Expand All
                            </button>
                            <button onclick="collapseAllFolders()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                                <i class="fas fa-compress-arrows-alt mr-2"></i>Collapse All
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Patient Folders Container -->
                <div class="space-y-4" id="patientFolders">
                    <?php if (!empty($patientRecords)): ?>
                        <?php foreach ($patientRecords as $patient): ?>
                            <div class="patient-folder bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden" 
                                 data-patient-id="<?= $patient['patient_id'] ?>"
                                 data-patient-name="<?= strtolower($patient['patient_name']) ?>"
                                 data-patient-email="<?= strtolower($patient['patient_email']) ?>">
                                
                                <!-- Patient Folder Header -->
                                <div class="folder-header bg-gradient-to-r from-blue-50 to-indigo-50 p-4 cursor-pointer hover:from-blue-100 hover:to-indigo-100 transition-all duration-200" 
                                     onclick="togglePatientFolder(<?= $patient['patient_id'] ?>)">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <!-- Folder Icon -->
                                            <div class="flex items-center">
                                                <i class="fas fa-folder text-yellow-500 text-xl mr-2"></i>
                                                <i class="fas fa-chevron-right folder-icon text-gray-400" id="icon-<?= $patient['patient_id'] ?>"></i>
                                            </div>
                                            
                                            <!-- Patient Info -->
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-900"><?= esc($patient['patient_name']) ?></h3>
                                                <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 text-sm text-gray-600 mt-1">
                                                    <span><i class="fas fa-envelope mr-1"></i><?= esc($patient['patient_email']) ?></span>
                                                    <?php if (!empty($patient['patient_phone'])): ?>
                                                        <span><i class="fas fa-phone mr-1"></i><?= esc($patient['patient_phone']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Patient Stats -->
                                        <div class="flex items-center space-x-6">
                                            <div class="text-center">
                                                <div class="text-2xl font-bold text-blue-600"><?= $patient['total_records'] ?></div>
                                                <div class="text-xs text-gray-500">Record<?= $patient['total_records'] != 1 ? 's' : '' ?></div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm font-medium text-gray-900">Latest Visit</div>
                                                <div class="text-xs text-gray-500"><?= date('M j, Y', strtotime($patient['latest_record_date'])) ?></div>
                                            </div>
                                            <div class="flex items-center">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-circle mr-1 text-green-400"></i>Active
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Patient Records (Collapsible Content) -->
                                <div id="folder-content-<?= $patient['patient_id'] ?>" class="folder-content hidden border-t border-gray-200">
                                    <div class="p-4 bg-gray-50">
                                        <div class="text-sm text-gray-600 mb-3">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            <?= $patient['total_records'] ?> dental examination record<?= $patient['total_records'] != 1 ? 's' : '' ?> found for this patient
                                        </div>
                                        
                                        <div class="space-y-3">
                                            <?php foreach ($patient['records'] as $index => $record): ?>
                                                <div class="record-item bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md transition-all duration-200">
                                                    <div class="flex items-start justify-between">
                                                        <div class="flex-1">
                                                            <!-- Record Header -->
                                                            <div class="flex items-center justify-between mb-2">
                                                                <h4 class="text-sm font-semibold text-gray-900">
                                                                    <i class="fas fa-file-medical-alt mr-2 text-blue-500"></i>
                                                                    Record #<?= $record['id'] ?>
                                                                </h4>
                                                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                                                    <?= date('M j, Y', strtotime($record['record_date'])) ?>
                                                                </span>
                                                            </div>
                                                            
                                                            <!-- Record Details -->
                                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                                                <div class="space-y-1">
                                                                    <p class="text-gray-600">
                                                                        <i class="fas fa-user-md mr-2 text-green-500"></i>
                                                                        <span class="font-medium">Doctor:</span> Dr. <?= esc($record['dentist_name']) ?>
                                                                    </p>
                                                                    <?php if (!empty($record['appointment_datetime'])): ?>
                                                                        <p class="text-gray-600">
                                                                            <i class="fas fa-calendar mr-2 text-purple-500"></i>
                                                                            <span class="font-medium">Appointment:</span> <?= date('M j, Y g:i A', strtotime($record['appointment_datetime'])) ?>
                                                                        </p>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="space-y-1">
                                                                    <?php if (!empty($record['treatment_plan'])): ?>
                                                                        <p class="text-gray-600">
                                                                            <i class="fas fa-clipboard-list mr-2 text-orange-500"></i>
                                                                            <span class="font-medium">Treatment:</span> 
                                                                            <?= strlen($record['treatment_plan']) > 50 ? substr(esc($record['treatment_plan']), 0, 50) . '...' : esc($record['treatment_plan']) ?>
                                                                        </p>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($record['next_appointment_date'])): ?>
                                                                        <p class="text-gray-600">
                                                                            <i class="fas fa-clock mr-2 text-blue-500"></i>
                                                                            <span class="font-medium">Next Visit:</span> <?= date('M j, Y', strtotime($record['next_appointment_date'])) ?>
                                                                        </p>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Diagnosis -->
                                                            <?php if (!empty($record['diagnosis'])): ?>
                                                                <div class="mt-3 p-3 bg-blue-50 rounded-lg">
                                                                    <p class="text-sm">
                                                                        <span class="font-medium text-blue-800">Diagnosis:</span>
                                                                        <span class="text-blue-700">
                                                                            <?= strlen($record['diagnosis']) > 150 ? substr(esc($record['diagnosis']), 0, 150) . '...' : esc($record['diagnosis']) ?>
                                                                        </span>
                                                                    </p>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        
                                                        <!-- Record Actions -->
                                                        <div class="ml-4 flex flex-col space-y-2">
                                                            <a href="<?= base_url('admin/dental/record/' . $record['id']) ?>" 
                                                               class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition-colors">
                                                                <i class="fas fa-eye mr-1"></i>View
                                                            </a>
                                                            <?php if (!empty($record['dental_chart_data'])): ?>
                                                                <a href="<?= base_url('admin/dental/chart/' . $record['id']) ?>" 
                                                                   class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition-colors">
                                                                    <i class="fas fa-tooth mr-1"></i>Chart
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Empty State -->
                        <div class="text-center py-16 bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="mb-4">
                                <i class="fas fa-folder-open text-6xl text-gray-300"></i>
                            </div>
                            <h3 class="text-xl font-medium text-gray-900 mb-2">No Patient Records Found</h3>
                            <p class="text-gray-500 mb-6">No dental examination records have been created yet.</p>
                            <a href="<?= base_url('admin/dental/create-record') ?>" 
                               class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-plus mr-2"></i>Create First Record
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Toggle individual patient folder
        function togglePatientFolder(patientId) {
            const content = document.getElementById(`folder-content-${patientId}`);
            const icon = document.getElementById(`icon-${patientId}`);
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.classList.add('folder-open');
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-chevron-down');
            } else {
                content.classList.add('hidden');
                icon.classList.remove('folder-open');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-right');
            }
        }
        
        // Expand all folders
        function expandAllFolders() {
            const folders = document.querySelectorAll('.folder-content');
            const icons = document.querySelectorAll('.folder-icon');
            
            folders.forEach(folder => folder.classList.remove('hidden'));
            icons.forEach(icon => {
                icon.classList.add('folder-open');
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-chevron-down');
            });
        }
        
        // Collapse all folders
        function collapseAllFolders() {
            const folders = document.querySelectorAll('.folder-content');
            const icons = document.querySelectorAll('.folder-icon');
            
            folders.forEach(folder => folder.classList.add('hidden'));
            icons.forEach(icon => {
                icon.classList.remove('folder-open');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-right');
            });
        }
        
        // Search functionality
        document.getElementById('searchPatients').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const patients = document.querySelectorAll('.patient-folder');
            
            patients.forEach(patient => {
                const name = patient.dataset.patientName;
                const email = patient.dataset.patientEmail;
                
                if (name.includes(searchTerm) || email.includes(searchTerm)) {
                    patient.style.display = 'block';
                } else {
                    patient.style.display = 'none';
                }
            });
        });
        
        // Filter by time period
        document.getElementById('filterPeriod').addEventListener('change', function() {
            const months = parseInt(this.value);
            if (!months) {
                // Show all patients
                document.querySelectorAll('.patient-folder').forEach(folder => {
                    folder.style.display = 'block';
                });
                return;
            }
            
            const cutoffDate = new Date();
            cutoffDate.setMonth(cutoffDate.getMonth() - months);
            
            // This would need to be implemented with actual date filtering
            // For now, we'll just show all patients
            console.log(`Filtering by last ${months} months`);
        });
    </script>
</body>
</html>
