<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dental Records - Perfect Smile Admin</title>
    <link href="<?= base_url('vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/style.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/admin.css') ?>" rel="stylesheet">
</head>
<body class="admin-body">
    <div class="min-h-screen flex bg-white">ml>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dental Records - Perfect Smile Admin</title>
    <link href="<?= base_url('vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/style.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/admin.css') ?>" rel="stylesheet">
</head>
<body class="admin-main-bg">
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
                                <i class="fas fa-file-medical-alt mr-3 text-blue-600"></i>Dental Records Management
                            </h1>
                            <p class="text-gray-600">View and manage all patient dental examination records</p>
                        </div>
                        <?php if (!empty($appointmentsWithoutRecords)): ?>
                        <div class="mt-4 sm:mt-0">
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-2 text-sm text-yellow-800">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <?= count($appointmentsWithoutRecords) ?> appointment(s) need records
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white border-l-4 border-blue-400 shadow rounded-lg p-5 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-blue-600 uppercase mb-1">Total Records</div>
                            <div class="text-2xl font-bold text-gray-800"><?= count($records) ?></div>
                        </div>
                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                    </div>
                    
                    <div class="bg-white border-l-4 border-green-400 shadow rounded-lg p-5 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-green-600 uppercase mb-1">This Month</div>
                            <div class="text-2xl font-bold text-gray-800">
                                <?= count(array_filter($records, function($r) { return strtotime($r['record_date']) > strtotime('-30 days'); })) ?>
                            </div>
                        </div>
                        <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                    </div>
                    
                    <div class="bg-white border-l-4 border-yellow-400 shadow rounded-lg p-5 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-yellow-600 uppercase mb-1">Pending Records</div>
                            <div class="text-2xl font-bold text-gray-800">
                                <?= count($appointmentsWithoutRecords ?? []) ?>
                            </div>
                        </div>
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                    
                    <div class="bg-white border-l-4 border-purple-400 shadow rounded-lg p-5 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-purple-600 uppercase mb-1">Active Dentists</div>
                            <div class="text-2xl font-bold text-gray-800">
                                <?= count(array_unique(array_column($records, 'dentist_id'))) ?>
                            </div>
                        </div>
                        <i class="fas fa-user-md fa-2x text-gray-300"></i>
                    </div>
                </div>

                <!-- Records List -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <h2 class="text-xl font-semibold text-gray-800 mb-4 sm:mb-0">All Dental Records</h2>
                            <div class="flex flex-col sm:flex-row gap-3">
                                <input type="text" id="searchRecords" placeholder="Search records..." 
                                       class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <select id="filterMonth" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="">All Months</option>
                                    <option value="1">This Month</option>
                                    <option value="3">Last 3 Months</option>
                                    <option value="6">Last 6 Months</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <?php if (!empty($records)): ?>
                            <div class="space-y-6" id="recordsList">
                                <?php foreach ($records as $record): ?>
                                    <div class="record-card bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg" data-record='<?= json_encode($record) ?>'>
                                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between">
                                            <div class="flex-1 lg:mr-6">
                                                <div class="flex items-center justify-between mb-3">
                                                    <h3 class="text-lg font-semibold text-gray-900">
                                                        <?= esc($record['patient_name']) ?>
                                                        <span class="text-sm text-gray-500 font-normal">- Record #<?= $record['id'] ?></span>
                                                    </h3>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        <?= date('M j, Y', strtotime($record['record_date'])) ?>
                                                    </span>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <p class="text-gray-600 text-sm">
                                                        <i class="fas fa-user-md mr-2"></i>Dr. <?= esc($record['dentist_name']) ?>
                                                        <?php if ($record['appointment_datetime']): ?>
                                                            <i class="fas fa-calendar ml-4 mr-2"></i><?= date('M j, Y g:i A', strtotime($record['appointment_datetime'])) ?>
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <h4 class="font-medium text-gray-900 mb-1">Diagnosis:</h4>
                                                    <p class="text-gray-700"><?= esc($record['diagnosis']) ?></p>
                                                </div>
                                                
                                                <?php if ($record['treatment']): ?>
                                                <div class="mb-3">
                                                    <h4 class="font-medium text-gray-900 mb-1">Treatment:</h4>
                                                    <p class="text-gray-700"><?= esc($record['treatment']) ?></p>
                                                </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($record['notes']): ?>
                                                <div class="mb-3">
                                                    <h4 class="font-medium text-gray-900 mb-1">Notes:</h4>
                                                    <p class="text-gray-600"><?= esc($record['notes']) ?></p>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="lg:w-48 mt-4 lg:mt-0">
                                                <?php if ($record['next_appointment_date']): ?>
                                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                                                        <div class="text-sm">
                                                            <i class="fas fa-calendar-plus mr-2 text-blue-600"></i>
                                                            <span class="font-medium text-blue-900">Next Visit:</span><br>
                                                            <span class="text-blue-700"><?= date('M j, Y', strtotime($record['next_appointment_date'])) ?></span>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="space-y-2">
                                                    <a href="<?= base_url('admin/dental-records/' . $record['id']) ?>" 
                                                       class="w-full inline-flex justify-center items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                        <i class="fas fa-eye mr-2"></i>View Details
                                                    </a>
                                                    <?php if ($record['appointment_id']): ?>
                                                    <a href="<?= base_url('admin/dental-charts/' . $record['appointment_id']) ?>" 
                                                       class="w-full inline-flex justify-center items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                        <i class="fas fa-tooth mr-2"></i>Dental Chart
                                                    </a>
                                                    <?php endif; ?>
                                                    <button onclick="printRecord(<?= $record['id'] ?>)"
                                                            class="w-full inline-flex justify-center items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        <i class="fas fa-print mr-2"></i>Print
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-12">
                                <i class="fas fa-file-medical-alt fa-3x text-gray-300 mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No Dental Records Found</h3>
                                <p class="text-gray-500">No dental examination records have been created yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Appointments Without Records -->
                <?php if (!empty($appointmentsWithoutRecords)): ?>
                <div class="bg-white rounded-lg shadow mt-8">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">
                            <i class="fas fa-plus-circle mr-2 text-green-600"></i>Appointments Needing Records
                        </h2>
                        <p class="text-gray-600 mt-1">Confirmed appointments that don't have dental records yet</p>
                    </div>
                    
                    <div class="p-6">
                        <div class="space-y-4">
                            <?php foreach ($appointmentsWithoutRecords as $appointment): ?>
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 flex flex-col lg:flex-row lg:items-center lg:justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-start space-x-4">
                                            <div class="flex-shrink-0">
                                                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-calendar-plus text-yellow-600"></i>
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="text-lg font-semibold text-gray-900"><?= esc($appointment['patient_name']) ?></h4>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-2">
                                                    <p class="text-sm text-gray-600">
                                                        <i class="fas fa-calendar mr-2"></i>
                                                        <span class="font-medium">Appointment:</span> <?= date('F j, Y g:i A', strtotime($appointment['appointment_datetime'])) ?>
                                                    </p>
                                                    <p class="text-sm text-gray-600">
                                                        <i class="fas fa-user-md mr-2"></i>
                                                        <span class="font-medium">Dentist:</span> Dr. <?= esc($appointment['dentist_name']) ?>
                                                    </p>
                                                    <p class="text-sm text-gray-600">
                                                        <i class="fas fa-building mr-2"></i>
                                                        <span class="font-medium">Branch:</span> <?= esc($appointment['branch_name']) ?>
                                                    </p>
                                                    <p class="text-sm text-gray-600">
                                                        <i class="fas fa-info-circle mr-2"></i>
                                                        <span class="font-medium">Status:</span> 
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                            <?= ucfirst($appointment['status']) ?>
                                                        </span>
                                                    </p>
                                                </div>
                                                <?php if ($appointment['remarks']): ?>
                                                <p class="text-sm text-gray-600 mt-2">
                                                    <i class="fas fa-sticky-note mr-2"></i>
                                                    <span class="font-medium">Notes:</span> <?= esc($appointment['remarks']) ?>
                                                </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="lg:w-56 mt-4 lg:mt-0 lg:ml-6">
                                        <div class="space-y-2">
                                            <a href="<?= base_url('admin/dental-records/create/' . $appointment['id']) ?>" 
                                               class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                <i class="fas fa-plus mr-2"></i>Create Record
                                            </a>
                                            <a href="<?= base_url('admin/dental-charts/create/' . $appointment['id']) ?>" 
                                               class="w-full inline-flex justify-center items-center px-4 py-2 border border-blue-300 text-sm font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                <i class="fas fa-tooth mr-2"></i>Create Record + Chart
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchRecords').addEventListener('input', function() {
            filterRecords();
        });

        document.getElementById('filterMonth').addEventListener('change', function() {
            filterRecords();
        });

        function filterRecords() {
            const searchTerm = document.getElementById('searchRecords').value.toLowerCase();
            const monthFilter = document.getElementById('filterMonth').value;
            const records = document.querySelectorAll('.record-card');
            
            records.forEach(function(record) {
                const recordData = JSON.parse(record.getAttribute('data-record'));
                const patientName = recordData.patient_name.toLowerCase();
                const dentistName = recordData.dentist_name.toLowerCase();
                const diagnosis = recordData.diagnosis.toLowerCase();
                
                // Search filter
                const matchesSearch = !searchTerm || 
                    patientName.includes(searchTerm) || 
                    dentistName.includes(searchTerm) || 
                    diagnosis.includes(searchTerm);
                
                // Date filter
                let matchesDate = true;
                if (monthFilter) {
                    const recordDate = new Date(recordData.record_date);
                    const cutoffDate = new Date();
                    cutoffDate.setMonth(cutoffDate.getMonth() - parseInt(monthFilter));
                    matchesDate = recordDate >= cutoffDate;
                }
                
                record.style.display = (matchesSearch && matchesDate) ? 'block' : 'none';
            });
        }

        function printRecord(recordId) {
            window.open('<?= base_url('admin/dental-records/') ?>' + recordId + '?print=1', '_blank');
        }
    </script>
</body>
</html>
