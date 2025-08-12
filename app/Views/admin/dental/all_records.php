<?= view('templates/header') ?>

<!-- Meta tag for base URL (for JavaScript) -->
<meta name="base-url" content="<?= base_url() ?>">

<!-- Three.js Library for 3D Dental Model -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>

<!-- 3D Dental Viewer Styles and Scripts -->
<link rel="stylesheet" href="<?= base_url('css/dental-3d-viewer.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/records-management.css') ?>">
<script src="<?= base_url('js/dental-3d-viewer.js') ?>"></script>

<!-- Modular Records Management System -->
<script src="<?= base_url('js/modules/records-utilities.js') ?>"></script>
<script src="<?= base_url('js/modules/modal-controller.js') ?>"></script>
<script src="<?= base_url('js/modules/data-loader.js') ?>"></script>
<script src="<?= base_url('js/modules/display-manager.js') ?>"></script>
<script src="<?= base_url('js/modules/dental-3d-manager.js') ?>"></script>
<script src="<?= base_url('js/modules/conditions-analyzer.js') ?>"></script>
<script src="<?= base_url('js/modules/records-manager.js') ?>"></script>

<script>
// Set base URL for JavaScript modules
window.BASE_URL = '<?= base_url() ?>';
</script>

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

        <!-- Main Content Area - Full Width Records Table -->
        <div class="bg-white shadow rounded-lg">
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
                <table class="full-width-table min-w-full divide-y divide-gray-200">
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
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
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
                                                <div class="text-sm font-medium text-gray-900"><?= esc($record['patient_name']) ?></div>
                                                <div class="text-sm text-gray-500">
                                                    <?= esc($record['patient_email']) ?>
                                                    <?php if (!empty($record['patient_phone'])): ?>
                                                        • <?= esc($record['patient_phone']) ?>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (!empty($record['allergies'])): ?>
                                                    <div class="text-xs text-red-600 mt-1">
                                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                                        Allergies: <?= esc($record['allergies']) ?>
                                                    </div>
                                                <?php endif; ?>
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
                                    <td class="px-6 py-4 whitespace-nowrap records-table-actions">
                                        <div class="flex flex-wrap gap-2">
                                            <button onclick="window.recordsManager?.openPatientRecordsModal(<?= $record['user_id'] ?>)"
                                                    class="action-btn action-btn-view">
                                                <i class="fas fa-eye mr-1"></i>
                                                <span>View</span>
                                            </button>
                                            <a href="<?= base_url('admin/dental-records/edit/' . $record['id']) ?>" 
                                               class="action-btn action-btn-edit">
                                                <i class="fas fa-edit mr-1"></i>
                                                <span>Edit</span>
                                            </a>
                                            <button onclick="deleteRecord(<?= $record['id'] ?>)"
                                                    class="action-btn action-btn-delete">
                                                <i class="fas fa-trash mr-1"></i>
                                                <span>Delete</span>
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
    </div>
</div>

<!-- Patient Records Modal -->
<div id="patientRecordsModal" class="modal-overlay hidden fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm z-50 transition-all duration-300 ease-in-out">
    <div id="modalDialog" class="modal-container flex items-center justify-center min-h-screen p-4">
        <div class="resizable-modal relative bg-white rounded-xl shadow-2xl border border-gray-200 transition-all duration-300 ease-in-out transform scale-95 opacity-0" 
             style="width: 90%; max-width: 1200px; min-width: 800px; height: 85vh; max-height: 95vh; min-height: 600px;">
            
            <!-- Modal Header -->
            <div class="modal-header-resizable flex justify-between items-center p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-t-xl">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-md text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Patient Records</h3>
                        <p class="text-sm text-gray-600">Comprehensive patient information</p>
                    </div>
                </div>
                
                <!-- Modal Controls -->
                <div class="modal-controls flex items-center space-x-2">
                    <button id="fullscreenToggle" type="button" class="fullscreen-btn" title="Toggle Fullscreen">
                        <i class="fas fa-expand"></i>
                    </button>
                    <button type="button" onclick="window.recordsManager?.closePatientRecordsModal()" 
                            class="text-gray-400 hover:text-gray-600 p-2 rounded-lg hover:bg-white/50 transition-colors">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>
            
            <!-- Modal Navigation -->
            <div class="flex space-x-1 px-6 pt-4 border-b border-gray-100">
                <button id="basic-info-tab" onclick="window.recordsManager?.showRecordTab('basic-info')" 
                        class="record-tab px-4 py-3 text-sm font-medium rounded-t-lg bg-blue-600 text-white shadow-sm transition-all duration-200">
                    <i class="fas fa-user mr-2"></i>Basic Info
                </button>
                <button id="dental-records-tab" onclick="window.recordsManager?.showRecordTab('dental-records')" 
                        class="record-tab px-4 py-3 text-sm font-medium rounded-t-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-all duration-200">
                    <i class="fas fa-tooth mr-2"></i>Dental Records
                </button>
                <button id="dental-chart-tab" onclick="window.recordsManager?.showRecordTab('dental-chart')" 
                        class="record-tab px-4 py-3 text-sm font-medium rounded-t-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-all duration-200">
                    <i class="fas fa-chart-line mr-2"></i>Dental Chart
                </button>
                <button id="appointments-tab" onclick="window.recordsManager?.showRecordTab('appointments')" 
                        class="record-tab px-4 py-3 text-sm font-medium rounded-t-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-all duration-200">
                    <i class="fas fa-calendar mr-2"></i>Appointments
                </button>
                <button id="treatments-tab" onclick="window.recordsManager?.showRecordTab('treatments')" 
                        class="record-tab px-4 py-3 text-sm font-medium rounded-t-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-all duration-200">
                    <i class="fas fa-procedures mr-2"></i>Treatments
                </button>
                <button id="medical-records-tab" onclick="window.recordsManager?.showRecordTab('medical-records')" 
                        class="record-tab px-4 py-3 text-sm font-medium rounded-t-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-all duration-200">
                    <i class="fas fa-file-medical mr-2"></i>Medical Records
                </button>
            </div>
            
            <!-- Modal Content -->
            <div class="modal-content-resizable p-6 overflow-y-auto" style="height: calc(100% - 140px);">
                <div id="modalContent" class="w-full h-full">
                    <div class="flex items-center justify-center h-32">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin text-2xl text-blue-500 mb-2"></i>
                            <p class="text-gray-600">Loading patient information...</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Resize Handle -->
            <div class="resize-handle-se absolute bottom-0 right-0 w-5 h-5 cursor-se-resize opacity-60 hover:opacity-100 transition-opacity">
                <i class="fas fa-grip-lines-vertical text-gray-400 text-xs transform rotate-45"></i>
            </div>
        </div>
    </div>
</div>

<?= view('templates/footer') ?>
