<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dental Charts - Perfect Smile Admin</title>
    <link href="<?= base_url('vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/style.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/admin.css') ?>" rel="stylesheet">
</head>
<body class="admin-body">
    <div class="min-h-screen flex bg-white">
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
                    <h1 class="text-2xl font-bold text-gray-800 mb-2">
                        <i class="fas fa-tooth mr-3 text-green-600"></i>Dental Charts Management
                    </h1>
                    <p class="text-gray-600">View and manage dental examination charts for all appointments</p>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white border-l-4 border-blue-400 shadow rounded-lg p-5 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-blue-600 uppercase mb-1">Total Appointments</div>
                            <div class="text-2xl font-bold text-gray-800"><?= count($appointments) ?></div>
                        </div>
                        <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                    </div>
                    
                    <div class="bg-white border-l-4 border-green-400 shadow rounded-lg p-5 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-green-600 uppercase mb-1">Charts Completed</div>
                            <div class="text-2xl font-bold text-gray-800">
                                <?= count(array_filter($appointments, function($a) { return $a['has_chart']; })) ?>
                            </div>
                        </div>
                        <i class="fas fa-tooth fa-2x text-gray-300"></i>
                    </div>
                    
                    <div class="bg-white border-l-4 border-yellow-400 shadow rounded-lg p-5 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-yellow-600 uppercase mb-1">Pending Charts</div>
                            <div class="text-2xl font-bold text-gray-800">
                                <?= count(array_filter($appointments, function($a) { return !$a['has_chart']; })) ?>
                            </div>
                        </div>
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                    
                    <div class="bg-white border-l-4 border-purple-400 shadow rounded-lg p-5 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-purple-600 uppercase mb-1">Completion Rate</div>
                            <div class="text-2xl font-bold text-gray-800">
                                <?php 
                                $percentage = count($appointments) > 0 ? round((count(array_filter($appointments, function($a) { return $a['has_chart']; })) / count($appointments)) * 100) : 0;
                                echo $percentage;
                                ?>%
                            </div>
                        </div>
                        <i class="fas fa-percentage fa-2x text-gray-300"></i>
                    </div>
                </div>

                <!-- Charts List -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <h2 class="text-xl font-semibold text-gray-800 mb-4 sm:mb-0">Appointment Dental Charts</h2>
                            <div class="flex flex-col sm:flex-row gap-3">
                                <input type="text" id="searchCharts" placeholder="Search appointments..." 
                                       class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <select id="filterStatus" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="">All Status</option>
                                    <option value="completed">Charts Completed</option>
                                    <option value="pending">Charts Pending</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <?php if (!empty($appointments)): ?>
                            <div class="space-y-6" id="chartsList">
                                <?php foreach ($appointments as $appointment): ?>
                                    <div class="chart-card border border-gray-200 rounded-lg p-6 hover:shadow-lg <?= $appointment['has_chart'] ? 'has-chart border-l-4 border-l-green-400' : 'no-chart border-l-4 border-l-gray-400' ?>" 
                                         data-appointment='<?= json_encode($appointment) ?>'>
                                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between">
                                            <div class="flex-1 lg:mr-6">
                                                <div class="flex items-center justify-between mb-3">
                                                    <h3 class="text-lg font-semibold text-gray-900">
                                                        <?= esc($appointment['patient_name']) ?>
                                                        <span class="text-sm text-gray-500 font-normal">- Appointment #<?= $appointment['id'] ?></span>
                                                    </h3>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $appointment['has_chart'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                                        <?= $appointment['has_chart'] ? 'Chart Complete' : 'Chart Pending' ?>
                                                    </span>
                                                </div>
                                                
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                                    <div>
                                                        <p class="text-gray-600 text-sm mb-1">
                                                            <i class="fas fa-calendar mr-2"></i>
                                                            <span class="font-medium">Date:</span> <?= date('M j, Y g:i A', strtotime($appointment['appointment_datetime'])) ?>
                                                        </p>
                                                        <p class="text-gray-600 text-sm">
                                                            <i class="fas fa-user-md mr-2"></i>
                                                            <span class="font-medium">Dentist:</span> Dr. <?= esc($appointment['dentist_name']) ?>
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <p class="text-gray-600 text-sm mb-1">
                                                            <i class="fas fa-building mr-2"></i>
                                                            <span class="font-medium">Branch:</span> <?= esc($appointment['branch_name']) ?>
                                                        </p>
                                                        <p class="text-gray-600 text-sm">
                                                            <i class="fas fa-info-circle mr-2"></i>
                                                            <span class="font-medium">Status:</span> 
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?= $appointment['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                                                <?= ucfirst($appointment['status']) ?>
                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>
                                                
                                                <?php if ($appointment['remarks']): ?>
                                                <div class="mb-3">
                                                    <h4 class="font-medium text-gray-900 mb-1">Appointment Notes:</h4>
                                                    <p class="text-gray-600"><?= esc($appointment['remarks']) ?></p>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="lg:w-48 mt-4 lg:mt-0">
                                                <?php if ($appointment['has_chart']): ?>
                                                    <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4">
                                                        <div class="text-sm">
                                                            <i class="fas fa-check-circle mr-2 text-green-600"></i>
                                                            <span class="font-medium text-green-900">Dental chart completed</span><br>
                                                            <span class="text-green-700">Ready for review</span>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                                                        <div class="text-sm">
                                                            <i class="fas fa-hourglass-half mr-2 text-yellow-600"></i>
                                                            <span class="font-medium text-yellow-900">Chart not started</span><br>
                                                            <span class="text-yellow-700">Awaiting examination</span>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="space-y-2">
                                                    <?php if ($appointment['has_chart']): ?>
                                                    <a href="<?= base_url('admin/dental-charts/' . $appointment['id']) ?>" 
                                                       class="w-full inline-flex justify-center items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                        <i class="fas fa-tooth mr-2"></i>View Chart
                                                    </a>
                                                    <a href="<?= base_url('admin/dental-charts/edit/' . $appointment['id']) ?>" 
                                                       class="w-full inline-flex justify-center items-center px-3 py-2 border border-orange-300 text-sm leading-4 font-medium rounded-md text-orange-700 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                                        <i class="fas fa-edit mr-2"></i>Edit Chart
                                                    </a>
                                                    <?php else: ?>
                                                    <a href="<?= base_url('admin/dental-charts/create/' . $appointment['id']) ?>" 
                                                       class="w-full inline-flex justify-center items-center px-3 py-2 border border-green-300 text-sm leading-4 font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                        <i class="fas fa-plus mr-2"></i>Create Chart
                                                    </a>
                                                    <?php endif; ?>
                                                    
                                                    <a href="<?= base_url('admin/appointments') ?>?id=<?= $appointment['id'] ?>" 
                                                       class="w-full inline-flex justify-center items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        <i class="fas fa-calendar mr-2"></i>View Appointment
                                                    </a>
                                                    
                                                    <?php if ($appointment['has_chart']): ?>
                                                    <button onclick="printChart(<?= $appointment['id'] ?>)"
                                                            class="w-full inline-flex justify-center items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        <i class="fas fa-print mr-2"></i>Print Chart
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-12">
                                <i class="fas fa-tooth fa-3x text-gray-300 mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No Appointments Found</h3>
                                <p class="text-gray-500">No confirmed appointments available for dental charting.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchCharts').addEventListener('input', function() {
            filterCharts();
        });

        document.getElementById('filterStatus').addEventListener('change', function() {
            filterCharts();
        });

        function filterCharts() {
            const searchTerm = document.getElementById('searchCharts').value.toLowerCase();
            const statusFilter = document.getElementById('filterStatus').value;
            const charts = document.querySelectorAll('.chart-card');
            
            charts.forEach(function(chart) {
                const appointmentData = JSON.parse(chart.getAttribute('data-appointment'));
                const patientName = appointmentData.patient_name.toLowerCase();
                const dentistName = appointmentData.dentist_name.toLowerCase();
                
                // Search filter
                const matchesSearch = !searchTerm || 
                    patientName.includes(searchTerm) || 
                    dentistName.includes(searchTerm);
                
                // Status filter
                let matchesStatus = true;
                if (statusFilter === 'completed') {
                    matchesStatus = appointmentData.has_chart;
                } else if (statusFilter === 'pending') {
                    matchesStatus = !appointmentData.has_chart;
                }
                
                chart.style.display = (matchesSearch && matchesStatus) ? 'block' : 'none';
            });
        }

        function printChart(appointmentId) {
            window.open('<?= base_url('admin/dental-charts/') ?>' + appointmentId + '?print=1', '_blank');
        }
    </script>
</body>
</html>
