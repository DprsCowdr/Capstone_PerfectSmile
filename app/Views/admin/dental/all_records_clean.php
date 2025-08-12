<?= view('templates/header') ?>

<!-- Meta tag for base URL (for JavaScript) -->
<meta name="base-url" content="<?= base_url() ?>">

<!-- Three.js Library for 3D Dental Model -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>

<!-- 3D Dental Viewer Styles and Scripts -->
<link rel="stylesheet" href="<?= base_url('css/dental-3d-viewer.css') ?>">
<script src="<?= base_url('js/dental-3d-viewer.js') ?>"></script>

<!-- Modular Records Management System -->
<script src="<?= base_url('js/modules/records-utilities.js') ?>"></script>
<script src="<?= base_url('js/modules/modal-controller.js') ?>"></script>
<script src="<?= base_url('js/modules/data-loader.js') ?>"></script>
<script src="<?= base_url('js/modules/display-manager.js') ?>"></script>
<script src="<?= base_url('js/modules/dental-3d-manager.js') ?>"></script>
<script src="<?= base_url('js/modules/conditions-analyzer.js') ?>"></script>
<script src="<?= base_url('js/modules/records-manager.js') ?>"></script>

<div class="flex min-h-screen bg-gray-100">
    <!-- Include existing sidebar -->
    <?= view('templates/sidebar') ?>

    <!-- Main Content Area -->
    <div class="flex-1 lg:ml-0 p-6">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Records Management</h1>
            <p class="text-gray-600">Comprehensive dental records management system</p>
        </div>

        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Total Records Card -->
            <div class="bg-white border-l-4 border-blue-400 shadow rounded-lg p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-blue-600 uppercase mb-1">Total Records</div>
                        <div class="text-2xl font-bold text-gray-800"><?= number_format($stats['total_records']) ?></div>
                    </div>
                    <i class="fas fa-folder fa-2x text-gray-300"></i>
                </div>
            </div>

            <!-- Active Patients Card -->
            <div class="bg-white border-l-4 border-green-400 shadow rounded-lg p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-green-600 uppercase mb-1">Active Patients</div>
                        <div class="text-2xl font-bold text-gray-800"><?= number_format($stats['active_patients']) ?></div>
                    </div>
                    <i class="fas fa-user-check fa-2x text-gray-300"></i>
                </div>
            </div>

            <!-- With X-rays Card -->
            <div class="bg-white border-l-4 border-purple-400 shadow rounded-lg p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-purple-600 uppercase mb-1">With X-rays</div>
                        <div class="text-2xl font-bold text-gray-800"><?= number_format($stats['with_xrays']) ?></div>
                    </div>
                    <i class="fas fa-x-ray fa-2x text-gray-300"></i>
                </div>
            </div>

            <!-- Pending Follow-ups Card -->
            <div class="bg-white border-l-4 border-orange-400 shadow rounded-lg p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-orange-600 uppercase mb-1">Follow-ups</div>
                        <div class="text-2xl font-bold text-gray-800"><?= number_format($stats['pending_followups']) ?></div>
                    </div>
                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Records Table -->
            <div class="lg:col-span-2 bg-white shadow rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-bold text-gray-800">Recent Records</h2>
                        <div class="flex space-x-2">
                            <button class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm">
                                <i class="fas fa-filter mr-2"></i>Filter
                            </button>
                            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                                <i class="fas fa-plus mr-2"></i>New Record
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (!empty($records)): ?>
                                <?php foreach ($records as $record): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= date('M j, Y', strtotime($record['record_date'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                        <i class="fas fa-user text-blue-600"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900"><?= $record['patient_name'] ?></div>
                                                    <div class="text-sm text-gray-500">ID: <?= $record['user_id'] ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= ucfirst($record['record_type'] ?? 'General') ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?= ($record['status'] ?? 'active') === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                                <?= ucfirst($record['status'] ?? 'Active') ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <button onclick="openPatientRecordsModal(<?= $record['user_id'] ?>)"
                                                        class="text-blue-600 hover:text-blue-900 px-3 py-1 rounded border border-blue-200 hover:bg-blue-50">
                                                    <i class="fas fa-eye mr-1"></i>View
                                                </button>
                                                <a href="<?= base_url('admin/dental-records/edit/' . $record['id']) ?>" 
                                                   class="text-indigo-600 hover:text-indigo-900 px-3 py-1 rounded border border-indigo-200 hover:bg-indigo-50">
                                                    <i class="fas fa-edit mr-1"></i>Edit
                                                </a>
                                                <button onclick="deleteRecord(<?= $record['id'] ?>)"
                                                        class="text-red-600 hover:text-red-900 px-3 py-1 rounded border border-red-200 hover:bg-red-50">
                                                    <i class="fas fa-trash mr-1"></i>Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        <div class="flex flex-col items-center py-8">
                                            <i class="fas fa-file-medical fa-3x text-gray-300 mb-4"></i>
                                            <p class="text-lg font-medium">No records found</p>
                                            <p class="text-sm">Get started by creating a new dental record.</p>
                                            <a href="<?= base_url('admin/dental-records/create') ?>" 
                                               class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                                <i class="fas fa-plus mr-2"></i>Create New Record
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Sidebar Info Panel -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="<?= base_url('admin/dental-records/create') ?>" 
                           class="block w-full bg-blue-600 text-white text-center py-2 rounded-lg hover:bg-blue-700">
                            <i class="fas fa-plus mr-2"></i>Create New Record
                        </a>
                        <a href="<?= base_url('admin/patients') ?>" 
                           class="block w-full bg-green-600 text-white text-center py-2 rounded-lg hover:bg-green-700">
                            <i class="fas fa-users mr-2"></i>View Patients
                        </a>
                        <a href="#" 
                           class="block w-full bg-purple-600 text-white text-center py-2 rounded-lg hover:bg-purple-700">
                            <i class="fas fa-download mr-2"></i>Export Records
                        </a>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Activity</h3>
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-plus text-blue-600 text-xs"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900">New record created for John Doe</p>
                                <p class="text-xs text-gray-500">2 hours ago</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-edit text-green-600 text-xs"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900">Record updated for Jane Smith</p>
                                <p class="text-xs text-gray-500">5 hours ago</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-x-ray text-purple-600 text-xs"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900">X-ray uploaded for Mike Johnson</p>
                                <p class="text-xs text-gray-500">1 day ago</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Patient Records Modal -->
<div id="patientRecordsModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
        <!-- Modal Header -->
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg font-bold text-gray-900">Patient Records</h3>
            <button type="button" onclick="closePatientRecordsModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Modal Navigation -->
        <div class="flex space-x-2 my-4 border-b">
            <button id="basic-info-tab" onclick="showRecordTab('basic-info')" 
                    class="record-tab px-4 py-2 text-sm font-medium rounded-t-lg bg-blue-600 text-white">
                <i class="fas fa-user mr-2"></i>Basic Info
            </button>
            <button id="dental-records-tab" onclick="showRecordTab('dental-records')" 
                    class="record-tab px-4 py-2 text-sm font-medium rounded-t-lg bg-gray-200 text-gray-700 hover:bg-gray-300">
                <i class="fas fa-tooth mr-2"></i>Dental Records
            </button>
            <button id="dental-chart-tab" onclick="showRecordTab('dental-chart')" 
                    class="record-tab px-4 py-2 text-sm font-medium rounded-t-lg bg-gray-200 text-gray-700 hover:bg-gray-300">
                <i class="fas fa-chart-line mr-2"></i>Dental Chart
            </button>
            <button id="appointments-tab" onclick="showRecordTab('appointments')" 
                    class="record-tab px-4 py-2 text-sm font-medium rounded-t-lg bg-gray-200 text-gray-700 hover:bg-gray-300">
                <i class="fas fa-calendar mr-2"></i>Appointments
            </button>
            <button id="treatments-tab" onclick="showRecordTab('treatments')" 
                    class="record-tab px-4 py-2 text-sm font-medium rounded-t-lg bg-gray-200 text-gray-700 hover:bg-gray-300">
                <i class="fas fa-procedures mr-2"></i>Treatments
            </button>
            <button id="medical-records-tab" onclick="showRecordTab('medical-records')" 
                    class="record-tab px-4 py-2 text-sm font-medium rounded-t-lg bg-gray-200 text-gray-700 hover:bg-gray-300">
                <i class="fas fa-file-medical mr-2"></i>Medical Records
            </button>
        </div>
        
        <!-- Modal Content -->
        <div id="modalContent" class="mt-4 max-h-96 overflow-y-auto">
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin"></i> Loading...
            </div>
        </div>
    </div>
</div>

<?= view('templates/footer') ?>

<script>
// Set base URL for JavaScript modules
window.BASE_URL = '<?= base_url() ?>';
</script>
