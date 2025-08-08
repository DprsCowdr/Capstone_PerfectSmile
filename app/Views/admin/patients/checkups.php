<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Checkups Overview - Perfect Smile</title>
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
                        <i class="fas fa-clipboard-check mr-3 text-blue-600"></i>Patient Checkups Overview
                    </h1>
                    <p class="text-gray-600">Monitor patient checkup status and dental health records</p>
                </div>

                <!-- Summary Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white border-l-4 border-blue-400 shadow rounded-lg p-5 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-blue-600 uppercase mb-1">Total Patients</div>
                            <div class="text-2xl font-bold text-gray-800"><?= count($patients) ?></div>
                        </div>
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                    
                    <div class="bg-white border-l-4 border-green-400 shadow rounded-lg p-5 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-green-600 uppercase mb-1">Recent Checkups</div>
                            <div class="text-2xl font-bold text-gray-800">
                                <?= count(array_filter($patients, function($p) { return $p['last_checkup'] && strtotime($p['last_checkup']) > strtotime('-6 months'); })) ?>
                            </div>
                        </div>
                        <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                    </div>
                    
                    <div class="bg-white border-l-4 border-yellow-400 shadow rounded-lg p-5 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-yellow-600 uppercase mb-1">Overdue Checkups</div>
                            <div class="text-2xl font-bold text-gray-800">
                                <?= count(array_filter($patients, function($p) { return !$p['last_checkup'] || strtotime($p['last_checkup']) < strtotime('-6 months'); })) ?>
                            </div>
                        </div>
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                    
                    <div class="bg-white border-l-4 border-red-400 shadow rounded-lg p-5 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-red-600 uppercase mb-1">Teeth Need Treatment</div>
                            <div class="text-2xl font-bold text-gray-800">
                                <?= array_sum(array_column($patients, 'treatment_count')) ?>
                            </div>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>

                <!-- Patients List -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">Patient Checkup Status</h2>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <?php foreach ($patients as $patient): ?>
                                <?php 
                                $isOverdue = !$patient['last_checkup'] || strtotime($patient['last_checkup']) < strtotime('-6 months');
                                $hasRecentCheckup = $patient['last_checkup'] && strtotime($patient['last_checkup']) > strtotime('-6 months');
                                $needsTreatment = $patient['treatment_count'] > 0;
                                
                                $cardClass = 'checkup-card bg-white border-l-4 border-blue-400 rounded-lg shadow p-6';
                                if ($needsTreatment) {
                                    $cardClass = 'checkup-card treatment-needed border-l-4 border-red-400 rounded-lg shadow p-6';
                                } elseif ($hasRecentCheckup) {
                                    $cardClass = 'checkup-card recent-checkup border-l-4 border-green-400 rounded-lg shadow p-6';
                                } elseif ($isOverdue) {
                                    $cardClass = 'checkup-card overdue-checkup border-l-4 border-yellow-400 rounded-lg shadow p-6';
                                }
                                ?>
                                <div class="<?= $cardClass ?>">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex-1">
                                            <h3 class="text-lg font-semibold text-gray-800 mb-2"><?= esc($patient['name']) ?></h3>
                                            <p class="text-gray-600 mb-1">
                                                <i class="fas fa-envelope mr-2"></i><?= esc($patient['email']) ?>
                                            </p>
                                            <p class="text-gray-600 mb-3">
                                                <i class="fas fa-phone mr-2"></i><?= esc($patient['phone']) ?>
                                            </p>
                                            
                                            <!-- Status Badges -->
                                            <div class="flex flex-wrap gap-2 mb-4">
                                                <?php if ($needsTreatment): ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                                        <?= $patient['treatment_count'] ?> Teeth Need Treatment
                                                    </span>
                                                <?php endif; ?>
                                                
                                                <?php if ($isOverdue): ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-clock mr-1"></i>Overdue Checkup
                                                    </span>
                                                <?php elseif ($hasRecentCheckup): ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <i class="fas fa-check mr-1"></i>Recent Checkup
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="text-right">
                                            <!-- Statistics -->
                                            <div class="flex space-x-4 mb-3">
                                                <div class="text-center">
                                                    <div class="text-lg font-bold text-blue-600"><?= $patient['total_appointments'] ?></div>
                                                    <div class="text-xs text-gray-600">Appointments</div>
                                                </div>
                                                <div class="text-center">
                                                    <div class="text-lg font-bold text-green-600"><?= $patient['total_records'] ?></div>
                                                    <div class="text-xs text-gray-600">Records</div>
                                                </div>
                                            </div>
                                            
                                            <!-- Last Checkup -->
                                            <p class="text-sm text-gray-600 mb-3">
                                                <span class="font-medium">Last Checkup:</span><br>
                                                <?php if ($patient['last_checkup']): ?>
                                                    <?= date('M j, Y', strtotime($patient['last_checkup'])) ?>
                                                <?php else: ?>
                                                    <span class="text-yellow-600 font-medium">Never</span>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <div class="border-t pt-4">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="<?= base_url('admin/patients') ?>" 
                                               class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                <i class="fas fa-user mr-2"></i>View Profile
                                            </a>
                                            <a href="<?= base_url('admin/dental-records') ?>?patient=<?= $patient['id'] ?>" 
                                               class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                <i class="fas fa-file-medical-alt mr-2"></i>Records
                                            </a>
                                            <a href="<?= base_url('admin/dental-charts') ?>?patient=<?= $patient['id'] ?>" 
                                               class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                <i class="fas fa-tooth mr-2"></i>Charts
                                            </a>
                                            <?php if (!empty($patient['last_checkup_appointment_id'])): ?>
                                            <a href="<?= base_url('checkup/patient/' . $patient['last_checkup_appointment_id']) ?>"
                                               class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                                <i class="fas fa-stethoscope mr-2"></i>Resume Checkup
                                            </a>
                                            <?php endif; ?>
                                            <?php if ($patient['treatment_count'] > 0): ?>
                                            <button onclick="showTreatmentDetails(<?= $patient['id'] ?>)"
                                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                                <i class="fas fa-exclamation-triangle mr-2"></i>Treatment
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        function showTreatmentDetails(patientId) {
            alert('Treatment details for patient ' + patientId + ' - Feature coming soon!');
        }
    </script>
</body>
</html>
