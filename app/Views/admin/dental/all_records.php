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
// Render inline visual charts (JSON or base64) inside All Records list
document.addEventListener('DOMContentLoaded', function() {
    const containers = document.querySelectorAll('.visual-chart-container');
    containers.forEach((container) => {
        const raw = container.getAttribute('data-chart-data') || '';
        const canvas = container.querySelector('.visual-chart-canvas');
        if (!canvas || !raw.trim()) return;
        const ctx = canvas.getContext('2d');

        function drawStrokes(ctx, strokes) {
            (strokes || []).forEach((s) => {
                if (!s || !Array.isArray(s.points) || s.points.length === 0) return;
                ctx.save();
                ctx.lineJoin = 'round';
                ctx.lineCap = 'round';
                ctx.lineWidth = Number(s.size) || 2;
                if (s.tool === 'eraser') {
                    ctx.globalCompositeOperation = 'destination-out';
                    ctx.strokeStyle = 'rgba(0,0,0,1)';
                } else {
                    ctx.globalCompositeOperation = 'source-over';
                    ctx.strokeStyle = s.color || '#ff0000';
                }
                ctx.beginPath();
                ctx.moveTo(s.points[0].x, s.points[0].y);
                for (let i = 1; i < s.points.length; i++) {
                    ctx.lineTo(s.points[i].x, s.points[i].y);
                }
                ctx.stroke();
                ctx.restore();
            });
        }

        // JSON state
        if (raw.trim().startsWith('{')) {
            try {
                const state = JSON.parse(raw);
                canvas.width = state.width || 1000;
                canvas.height = state.height || 600;
                if (state.background) {
                    const bg = new Image();
                    bg.onload = () => {
                        ctx.drawImage(bg, 0, 0, canvas.width, canvas.height);
                        drawStrokes(ctx, state.strokes || []);
                    };
                    bg.onerror = () => drawStrokes(ctx, state.strokes || []);
                    bg.src = state.background;
                } else {
                    drawStrokes(ctx, state.strokes || []);
                }
            } catch (e) {
                console.warn('Invalid visual_chart_data JSON:', e);
                canvas.style.display = 'none';
            }
            return;
        }

        // Legacy data URL
        if (raw.startsWith('data:image/')) {
            const img = new Image();
            img.onload = () => {
                canvas.width = img.width;
                canvas.height = img.height;
                ctx.drawImage(img, 0, 0);
            };
            img.onerror = () => { canvas.style.display = 'none'; };
            img.src = raw;
            return;
        }

        canvas.style.display = 'none';
    });
});
// Set base URL for JavaScript modules
window.BASE_URL = '<?= base_url() ?>';
</script>

<div class="flex min-h-screen bg-gray-50">
    <!-- Include existing sidebar -->
    <?= view('templates/sidebar') ?>

    <!-- Main Content Area -->
    <div class="flex-1 lg:ml-0 p-8 space-y-8">
        <!-- Page Header -->
        <header class="mb-2">
            <h1 class="text-xl font-semibold text-gray-800 tracking-tight">Records Management</h1>
            <p class="text-sm text-gray-500">Comprehensive dental records management system</p>
        </header>

        <!-- Main Content Area - Branch-Categorized Records View -->
        <section class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-gray-700">Patient Records by Branch</h2>
                    <div class="flex items-center gap-2">
                        <button id="viewToggle" onclick="toggleView()" class="px-3 py-2 text-xs font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-md transition-colors flex items-center gap-1">
                            <i id="viewIcon" class="fas fa-list"></i>
                            <span id="viewText">List View</span>
                        </button>
                    </div>
                </div>
                
                <!-- Advanced Search Bar -->
                <div class="bg-gray-50 rounded-md p-4">
                    <div class="flex flex-col md:flex-row gap-4 md:items-center">
                        <!-- Main Search Input -->
                        <div class="flex-1 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   id="recordsSearchInput" 
                                   class="block w-full pl-9 pr-3 py-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-sm placeholder:text-gray-400"
                                   placeholder="Search patient name, email, phone, or branch...">
                        </div>
                        
                        <!-- Search Filters -->
                        <div class="flex gap-3 md:w-auto">
                            <select id="branchFilter" class="px-3 py-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-xs text-gray-700">
                                <option value="">All Branches</option>
                                <?php foreach ($branches as $branch): ?>
                                    <option value="<?= $branch['id'] ?>"><?= esc($branch['name']) ?></option>
                                <?php endforeach; ?>
                                <option value="unassigned">Unassigned</option>
                            </select>
                            
                            <select id="statusFilter" class="px-3 py-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-xs text-gray-700">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            
                            <button id="clearSearch" class="px-3 py-2.5 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-xs font-medium transition-colors flex items-center gap-1 shadow-sm">
                                <i class="fas fa-times text-[11px]"></i><span>Clear</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Search Results Summary -->
                    <div id="searchSummary" class="mt-3 text-xs text-gray-600 hidden">
                        <i class="fas fa-info-circle mr-1"></i>
                        <span id="searchResultsCount">0</span> patient folders found
                        <span id="searchTermDisplay"></span>
                    </div>
                </div>
            </div>

            <!-- Branch-Categorized Records View -->
            <div id="foldersView" class="p-6 space-y-6">
                
                <!-- Records by Branch -->
                <?php if (!empty($recordsByBranch)): ?>
                    <?php foreach ($recordsByBranch as $branchId => $branchData): ?>
                        <div class="branch-section border border-gray-200 rounded-lg overflow-hidden" data-branch-id="<?= $branchId ?>">
                            <!-- Branch Header -->
                            <div class="branch-header bg-gradient-to-r from-blue-50 to-indigo-50 px-4 py-3 cursor-pointer hover:from-blue-100 hover:to-indigo-100 transition-all duration-200" onclick="toggleBranchSection(<?= $branchId ?>)">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center">
                                            <i class="fas fa-building text-blue-600 text-lg mr-2"></i>
                                            <i class="fas fa-chevron-right branch-chevron text-gray-400 transition-transform" id="branch-icon-<?= $branchId ?>"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-base font-semibold text-gray-900"><?= esc($branchData['branch']['name'] ?? 'Unknown Branch') ?></h3>
                                            <p class="text-sm text-gray-600"><?= esc($branchData['branch']['address'] ?? '') ?></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 text-sm text-gray-600">
                                        <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full font-medium">
                                            <?= count($branchData['records']) ?> record<?= count($branchData['records']) !== 1 ? 's' : '' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Branch Records -->
                            <div class="branch-records hidden border-t border-gray-100" id="branch-records-<?= $branchId ?>">
                                <div class="p-4 bg-gray-50">
                                    <?php 
                                    // Group records by patient within this branch
                                    $branchPatientGroups = [];
                                    foreach ($branchData['records'] as $record) {
                                        $patientId = $record['user_id'];
                                        if (!isset($branchPatientGroups[$patientId])) {
                                            $branchPatientGroups[$patientId] = [
                                                'patient_info' => [
                                                    'id' => $patientId,
                                                    'name' => $record['patient_name'],
                                                    'email' => $record['patient_email'],
                                                    'phone' => $record['patient_phone'] ?? '',
                                                    'status' => $record['status'] ?? 'active'
                                                ],
                                                'records' => []
                                            ];
                                        }
                                        $branchPatientGroups[$patientId]['records'][] = $record;
                                    }
                                    ?>
                                    
                                    <div class="space-y-3">
                                        <?php foreach ($branchPatientGroups as $patientId => $group): ?>
                                            <div class="patient-folder border border-gray-200 rounded-lg hover:border-gray-300 transition-colors bg-white" data-patient-id="<?= $patientId ?>" data-branch-id="<?= $branchId ?>">
                                                <!-- Patient Folder Header -->
                                                <div class="folder-header flex items-center justify-between p-4 cursor-pointer hover:bg-gray-50 transition-colors" onclick="togglePatientFolder(<?= $patientId ?>, <?= $branchId ?>)">
                                                    <div class="flex items-center gap-3">
                                                        <div class="flex items-center gap-2">
                                                            <i class="folder-icon fas fa-folder text-yellow-500 text-lg"></i>
                                                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-sm font-medium">
                                                                <?= strtoupper(substr($group['patient_info']['name'], 0, 1)) ?>
                                                            </div>
                                                        </div>
                                                        <div class="min-w-0 flex-1">
                                                            <div class="flex items-center gap-2">
                                                                <h4 class="text-sm font-medium text-gray-900 truncate"><?= esc($group['patient_info']['name']) ?></h4>
                                                                <span class="px-2 py-0.5 inline-flex text-[10px] font-medium rounded-full tracking-wide <?= $group['patient_info']['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' ?>">
                                                                    <?= ucfirst($group['patient_info']['status']) ?>
                                                                </span>
                                                            </div>
                                                            <div class="text-xs text-gray-500 mt-0.5">
                                                                <?= esc($group['patient_info']['email']) ?>
                                                                <?php if (!empty($group['patient_info']['phone'])): ?>
                                                                    • <?= esc($group['patient_info']['phone']) ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-3 text-xs text-gray-500">
                                                        <span class="bg-gray-100 px-2 py-1 rounded-full">
                                                            <?= count($group['records']) ?> record<?= count($group['records']) !== 1 ? 's' : '' ?>
                                                        </span>
                                                        <i class="patient-chevron fas fa-chevron-down text-gray-400 transition-transform" id="patient-icon-<?= $patientId ?>-<?= $branchId ?>"></i>
                                                    </div>
                                                </div>

                                                <!-- Patient Records -->
                                                <div class="patient-records hidden border-t border-gray-100 bg-gray-50" id="patient-records-<?= $patientId ?>-<?= $branchId ?>">
                                                    <div class="p-4 space-y-2">
                                                        <?php foreach ($group['records'] as $record): ?>
                                                            <div class="record-item flex items-center justify-between p-3 bg-white rounded-md border border-gray-100 hover:border-gray-200 transition-colors">
                                                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                                                    <div class="flex-shrink-0">
                                                                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                                                                            <i class="fas fa-file-medical text-gray-500 text-xs"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="min-w-0 flex-1">
                                                                        <div class="flex items-center gap-2">
                                                                            <span class="text-sm font-medium text-gray-900">Record #<?= $record['id'] ?></span>
                                                                            <span class="text-xs text-gray-500">•</span>
                                                                            <span class="text-xs text-gray-500"><?= date('M j, Y', strtotime($record['record_date'])) ?></span>
                                                                            <?php if (!empty($record['dentist_name'])): ?>
                                                                                <span class="text-xs text-gray-500">•</span>
                                                                                <span class="text-xs text-gray-500">Dr. <?= esc($record['dentist_name']) ?></span>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                        <?php if (!empty($record['diagnosis'])): ?>
                                                                            <div class="text-xs text-gray-600 mt-1 truncate">
                                                                                <i class="fas fa-stethoscope mr-1"></i>
                                                                                <?= esc(substr($record['diagnosis'], 0, 100)) ?><?= strlen($record['diagnosis']) > 100 ? '...' : '' ?>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                                <div class="flex items-center gap-2 ml-3">
                                                                    <button onclick="window.recordsManager?.openPatientRecordsModal(<?= $record['user_id'] ?>); event.stopPropagation();" class="px-2 py-1 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded transition-colors">
                                                                        View
                                                                    </button>
                                                                    <button onclick="deleteRecord(<?= $record['id'] ?>); event.stopPropagation();" class="px-2 py-1 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded transition-colors">
                                                                        Delete
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Unassigned Records Section -->
                <?php if (!empty($unassignedRecords)): ?>
                    <div class="branch-section border border-amber-200 rounded-lg overflow-hidden" data-branch-id="unassigned">
                        <!-- Unassigned Header -->
                        <div class="branch-header bg-gradient-to-r from-amber-50 to-yellow-50 px-4 py-3 cursor-pointer hover:from-amber-100 hover:to-yellow-100 transition-all duration-200" onclick="toggleBranchSection('unassigned')">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center">
                                        <i class="fas fa-exclamation-triangle text-amber-600 text-lg mr-2"></i>
                                        <i class="fas fa-chevron-right branch-chevron text-gray-400 transition-transform" id="branch-icon-unassigned"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900">Unassigned Records</h3>
                                        <p class="text-sm text-gray-600">Records not associated with any branch</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 text-sm text-gray-600">
                                    <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full font-medium">
                                        <?= count($unassignedRecords) ?> record<?= count($unassignedRecords) !== 1 ? 's' : '' ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Unassigned Records -->
                        <div class="branch-records hidden border-t border-amber-100" id="branch-records-unassigned">
                            <div class="p-4 bg-amber-50">
                                <?php 
                                // Group unassigned records by patient
                                $unassignedPatientGroups = [];
                                foreach ($unassignedRecords as $record) {
                                    $patientId = $record['user_id'];
                                    if (!isset($unassignedPatientGroups[$patientId])) {
                                        $unassignedPatientGroups[$patientId] = [
                                            'patient_info' => [
                                                'id' => $patientId,
                                                'name' => $record['patient_name'],
                                                'email' => $record['patient_email'],
                                                'phone' => $record['patient_phone'] ?? '',
                                                'status' => $record['status'] ?? 'active'
                                            ],
                                            'records' => []
                                        ];
                                    }
                                    $unassignedPatientGroups[$patientId]['records'][] = $record;
                                }
                                ?>
                                
                                <div class="space-y-3">
                                    <?php foreach ($unassignedPatientGroups as $patientId => $group): ?>
                                        <div class="patient-folder border border-amber-200 rounded-lg hover:border-amber-300 transition-colors bg-white" data-patient-id="<?= $patientId ?>" data-branch-id="unassigned">
                                            <!-- Patient Folder Header -->
                                            <div class="folder-header flex items-center justify-between p-4 cursor-pointer hover:bg-amber-50 transition-colors" onclick="togglePatientFolder(<?= $patientId ?>, 'unassigned')">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex items-center gap-2">
                                                        <i class="folder-icon fas fa-folder text-amber-500 text-lg"></i>
                                                        <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 text-sm font-medium">
                                                            <?= strtoupper(substr($group['patient_info']['name'], 0, 1)) ?>
                                                        </div>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <div class="flex items-center gap-2">
                                                            <h4 class="text-sm font-medium text-gray-900 truncate"><?= esc($group['patient_info']['name']) ?></h4>
                                                            <span class="px-2 py-0.5 inline-flex text-[10px] font-medium rounded-full tracking-wide <?= $group['patient_info']['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' ?>">
                                                                <?= ucfirst($group['patient_info']['status']) ?>
                                                            </span>
                                                        </div>
                                                        <div class="text-xs text-gray-500 mt-0.5">
                                                            <?= esc($group['patient_info']['email']) ?>
                                                            <?php if (!empty($group['patient_info']['phone'])): ?>
                                                                • <?= esc($group['patient_info']['phone']) ?>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-3 text-xs text-gray-500">
                                                    <span class="bg-amber-100 px-2 py-1 rounded-full">
                                                        <?= count($group['records']) ?> record<?= count($group['records']) !== 1 ? 's' : '' ?>
                                                    </span>
                                                    <i class="patient-chevron fas fa-chevron-down text-gray-400 transition-transform" id="patient-icon-<?= $patientId ?>-unassigned"></i>
                                                </div>
                                            </div>

                                            <!-- Patient Records -->
                                            <div class="patient-records hidden border-t border-amber-100 bg-amber-50" id="patient-records-<?= $patientId ?>-unassigned">
                                                <div class="p-4 space-y-2">
                                                    <?php foreach ($group['records'] as $record): ?>
                                                        <div class="record-item flex items-center justify-between p-3 bg-white rounded-md border border-amber-100 hover:border-amber-200 transition-colors">
                                                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                                                <div class="flex-shrink-0">
                                                                    <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center">
                                                                        <i class="fas fa-file-medical text-amber-600 text-xs"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="min-w-0 flex-1">
                                                                    <div class="flex items-center gap-2">
                                                                        <span class="text-sm font-medium text-gray-900">Record #<?= $record['id'] ?></span>
                                                                        <span class="text-xs text-gray-500">•</span>
                                                                        <span class="text-xs text-gray-500"><?= date('M j, Y', strtotime($record['record_date'])) ?></span>
                                                                        <?php if (!empty($record['dentist_name'])): ?>
                                                                            <span class="text-xs text-gray-500">•</span>
                                                                            <span class="text-xs text-gray-500">Dr. <?= esc($record['dentist_name']) ?></span>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <?php if (!empty($record['diagnosis'])): ?>
                                                                        <div class="text-xs text-gray-600 mt-1 truncate">
                                                                            <i class="fas fa-stethoscope mr-1"></i>
                                                                            <?= esc(substr($record['diagnosis'], 0, 100)) ?><?= strlen($record['diagnosis']) > 100 ? '...' : '' ?>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center gap-2 ml-3">
                                                                <button onclick="window.recordsManager?.openPatientRecordsModal(<?= $record['user_id'] ?>); event.stopPropagation();" class="px-2 py-1 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded transition-colors">
                                                                    View
                                                                </button>
                                                                <button onclick="deleteRecord(<?= $record['id'] ?>); event.stopPropagation();" class="px-2 py-1 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded transition-colors">
                                                                    Delete
                                                                </button>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if (!empty($record['visual_chart_data'])): ?>
                                        <div class="mt-2 p-2 bg-gray-50 rounded border border-gray-100">
                                            <div class="text-[11px] text-gray-600 mb-1">Visual Dental Chart - <?= date('M j, Y', strtotime($record['record_date'])) ?></div>
                                            <div class="visual-chart-container" data-chart-data='<?= htmlspecialchars($record['visual_chart_data'], ENT_QUOTES) ?>'>
                                                <canvas class="visual-chart-canvas" style="max-height: 300px; max-width: 100%; display: block;"></canvas>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (empty($recordsByBranch) && empty($unassignedRecords)): ?>
                    <div class="text-center py-12">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-folder-open text-4xl text-gray-300 mb-4"></i>
                            <p class="text-sm font-medium text-gray-700">No patient records found</p>
                            <p class="text-xs text-gray-500 mt-1">Create a new dental record to get started.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Original Table View (Hidden by default) -->
            <div id="tableView" class="hidden overflow-x-auto scrollbar-thin">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/70">
                        <tr>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Patient</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Type</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100 text-sm">
                        <?php if (!empty($records)): ?>
                            <?php foreach ($records as $record): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-5 py-3 whitespace-nowrap text-[13px] text-gray-800">
                                        <?= date('M j, Y', strtotime($record['record_date'])) ?>
                                    </td>
                                    <td class="px-5 py-3 whitespace-nowrap">
                                        <div class="flex items-start gap-4">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-sm font-medium">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div class="min-w-[180px] space-y-1">
                                                <div class="text-[13px] font-medium text-gray-900 leading-tight tracking-tight">
                                                    <?= esc($record['patient_name']) ?>
                                                </div>
                                                <div class="text-[11px] text-gray-500">
                                                    <?= esc($record['patient_email']) ?>
                                                    <?php if (!empty($record['patient_phone'])): ?>
                                                        • <?= esc($record['patient_phone']) ?>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (!empty($record['allergies'])): ?>
                                                    <div class="text-[10px] text-red-600 mt-1 flex items-center gap-1">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        <span><?= esc($record['allergies']) ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 whitespace-nowrap text-[13px] text-gray-700">
                                        <?= ucfirst($record['record_type'] ?? 'General') ?>
                                    </td>
                                    <td class="px-5 py-3 whitespace-nowrap">
                                        <span class="px-2 py-0.5 inline-flex text-[10px] font-medium rounded-full tracking-wide <?= ($record['status'] ?? 'active') === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' ?>">
                                            <?= ucfirst($record['status'] ?? 'Active') ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <button onclick="window.recordsManager?.openPatientRecordsModal(<?= $record['user_id'] ?>)" class="px-3 py-1.5 rounded-md bg-blue-600 text-white text-[11px] font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1 shadow-sm">
                                                View
                                            </button>
                                            <button onclick="deleteRecord(<?= $record['id'] ?>)" class="px-3 py-1.5 rounded-md bg-red-50 text-red-600 text-[11px] font-medium hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-1">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<!-- Patient Records Modal -->
<div id="patientRecordsModal" class="hidden fixed inset-0 z-50 flex items-start justify-center bg-gray-900/50 backdrop-blur-sm p-4 overflow-y-auto">
    <div id="modalDialog" class="w-full max-w-5xl mx-auto">
        <div class="modal-panel relative bg-white rounded-lg border border-gray-200 shadow-xl overflow-hidden transform transition-all scale-95 opacity-0" style="min-height:560px;">
            
            <!-- Modal Header -->
            <header class="flex justify-between items-center px-5 py-3 border-b border-gray-100 bg-white">
                <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-user-md text-blue-600"></i>
                    Patient Records
                </h3>
                <button type="button" onclick="window.recordsManager?.closePatientRecordsModal()" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-md hover:bg-gray-100">
                    <span class="sr-only">Close</span>
                    <i class="fas fa-times text-sm"></i>
                </button>
            </header>
            
            <!-- Modal Navigation -->
            <nav class="flex gap-1 px-5 pt-3 border-b border-gray-100 bg-white overflow-x-auto text-xs">
                <button id="basic-info-tab" onclick="window.recordsManager?.showRecordTab('basic-info')" class="record-tab px-3 py-2 rounded-md bg-blue-600 text-white font-medium">Basic Info</button>
                <button id="dental-records-tab" onclick="window.recordsManager?.showRecordTab('dental-records')" class="record-tab px-3 py-2 rounded-md text-gray-600 hover:bg-gray-100">Dental Records</button>
                <button id="dental-chart-tab" onclick="window.recordsManager?.showRecordTab('dental-chart')" class="record-tab px-3 py-2 rounded-md text-gray-600 hover:bg-gray-100">Dental Chart</button>
                <button id="appointments-tab" onclick="window.recordsManager?.showRecordTab('appointments')" class="record-tab px-3 py-2 rounded-md text-gray-600 hover:bg-gray-100">Appointments</button>
                <button id="treatments-tab" onclick="window.recordsManager?.showRecordTab('treatments')" class="record-tab px-3 py-2 rounded-md text-gray-600 hover:bg-gray-100">Treatments</button>
                <button id="medical-records-tab" onclick="window.recordsManager?.showRecordTab('medical-records')" class="record-tab px-3 py-2 rounded-md text-gray-600 hover:bg-gray-100">Medical Records</button>
                <button id="invoice-history-tab" onclick="window.recordsManager?.showRecordTab('invoice-history')" class="record-tab px-3 py-2 rounded-md text-gray-600 hover:bg-gray-100">Invoices</button>
                <button id="prescriptions-tab" onclick="window.recordsManager?.showRecordTab('prescriptions')" class="record-tab px-3 py-2 rounded-md text-gray-600 hover:bg-gray-100">Prescriptions</button>
            </nav>
            
            <!-- Modal Content -->
            <div class="px-5 py-4 overflow-y-auto" style="height: calc(100% - 92px);">
                <div id="modalContent" class="w-full h-full text-sm">
                    <div class="flex items-center justify-center h-40">
                        <div class="text-center space-y-2">
                            <i class="fas fa-spinner fa-spin text-xl text-blue-500"></i>
                            <p class="text-xs text-gray-500">Loading patient information...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Folder Management and Enhanced Search Functionality -->
<script>
let currentView = 'folders'; // 'folders' or 'table'

// Toggle between folder and table view
function toggleView() {
    const foldersView = document.getElementById('foldersView');
    const tableView = document.getElementById('tableView');
    const viewIcon = document.getElementById('viewIcon');
    const viewText = document.getElementById('viewText');
    
    if (currentView === 'folders') {
        // Switch to table view
        foldersView.classList.add('hidden');
        tableView.classList.remove('hidden');
        viewIcon.className = 'fas fa-folder';
        viewText.textContent = 'Folder View';
        currentView = 'table';
    } else {
        // Switch to folder view
        tableView.classList.add('hidden');
        foldersView.classList.remove('hidden');
        viewIcon.className = 'fas fa-list';
        viewText.textContent = 'List View';
        currentView = 'folders';
    }
    
    // Re-initialize search for the new view
    initializeRecordsSearch();
}

// Toggle branch section open/close
function toggleBranchSection(branchId) {
    const branchSection = document.querySelector(`[data-branch-id="${branchId}"]`);
    if (!branchSection) return;
    
    const content = branchSection.querySelector('.branch-records');
    const chevron = branchSection.querySelector('.branch-chevron');
    
    if (content.classList.contains('hidden')) {
        // Open branch section
        content.classList.remove('hidden');
        chevron.style.transform = 'rotate(90deg)';
        
        // Add smooth animation
        content.style.maxHeight = '0px';
        content.style.overflow = 'hidden';
        content.style.transition = 'max-height 0.3s ease-out';
        
        // Calculate and set max height
        setTimeout(() => {
            const scrollHeight = content.scrollHeight;
            content.style.maxHeight = `${scrollHeight}px`;
        }, 10);
        
        // Remove inline styles after animation
        setTimeout(() => {
            content.style.maxHeight = '';
            content.style.overflow = '';
            content.style.transition = '';
        }, 350);
        
    } else {
        // Close branch section
        content.style.maxHeight = `${content.scrollHeight}px`;
        content.style.overflow = 'hidden';
        content.style.transition = 'max-height 0.3s ease-out';
        
        // Trigger reflow
        content.offsetHeight;
        
        content.style.maxHeight = '0px';
        chevron.style.transform = 'rotate(0deg)';
        
        // Hide after animation
        setTimeout(() => {
            content.classList.add('hidden');
            content.style.maxHeight = '';
            content.style.overflow = '';
            content.style.transition = '';
        }, 350);
    }
}

// Toggle individual patient folder within a branch
function togglePatientFolder(patientId, branchId) {
    const folder = document.querySelector(`[data-patient-id="${patientId}"][data-branch-id="${branchId}"]`);
    if (!folder) return;
    
    const content = folder.querySelector('.patient-records');
    const icon = folder.querySelector('.folder-icon');
    const chevron = folder.querySelector('.patient-chevron');
    
    if (content.classList.contains('hidden')) {
        // Open folder
        content.classList.remove('hidden');
        icon.className = 'folder-icon fas fa-folder-open text-yellow-500 text-lg';
        chevron.style.transform = 'rotate(180deg)';
        
        // Add smooth animation
        content.style.maxHeight = '0px';
        content.style.overflow = 'hidden';
        content.style.transition = 'max-height 0.3s ease-out';
        
        // Calculate and set max height
        setTimeout(() => {
            const scrollHeight = content.scrollHeight;
            content.style.maxHeight = `${scrollHeight}px`;
        }, 10);
        
        // Remove inline styles after animation
        setTimeout(() => {
            content.style.maxHeight = '';
            content.style.overflow = '';
            content.style.transition = '';
        }, 350);
        
    } else {
        // Close folder
        content.style.maxHeight = `${content.scrollHeight}px`;
        content.style.overflow = 'hidden';
        content.style.transition = 'max-height 0.3s ease-out';
        
        // Trigger reflow
        content.offsetHeight;
        
        content.style.maxHeight = '0px';
        icon.className = branchId === 'unassigned' ? 'folder-icon fas fa-folder text-amber-500 text-lg' : 'folder-icon fas fa-folder text-yellow-500 text-lg';
        chevron.style.transform = 'rotate(0deg)';
        
        // Hide after animation
        setTimeout(() => {
            content.classList.add('hidden');
            content.style.maxHeight = '';
            content.style.overflow = '';
            content.style.transition = '';
        }, 350);
    }
}

// Legacy function for backward compatibility (now calls togglePatientFolder)
function toggleFolder(patientId) {
    // Try to find the patient folder in any branch
    const folder = document.querySelector(`[data-patient-id="${patientId}"]`);
    if (!folder) return;
    
    const branchId = folder.dataset.branchId || 'unknown';
    togglePatientFolder(patientId, branchId);
}

// Expand all folders
function expandAllFolders() {
    const folders = document.querySelectorAll('.patient-folder');
    folders.forEach(folder => {
        const patientId = folder.dataset.patientId;
        const content = folder.querySelector('.folder-content');
        if (content.classList.contains('hidden')) {
            toggleFolder(parseInt(patientId));
        }
    });
}

// Collapse all folders
function collapseAllFolders() {
    const folders = document.querySelectorAll('.patient-folder');
    folders.forEach(folder => {
        const patientId = folder.dataset.patientId;
        const content = folder.querySelector('.folder-content');
        if (!content.classList.contains('hidden')) {
            toggleFolder(parseInt(patientId));
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize search functionality
    initializeRecordsSearch();
});

function initializeRecordsSearch() {
    const searchInput = document.getElementById('recordsSearchInput');
    const statusFilter = document.getElementById('statusFilter');
    const branchFilter = document.getElementById('branchFilter');
    const clearButton = document.getElementById('clearSearch');
    const searchSummary = document.getElementById('searchSummary');
    const searchResultsCount = document.getElementById('searchResultsCount');
    const searchTermDisplay = document.getElementById('searchTermDisplay');
    
    if (currentView === 'folders') {
        // Branch-based folder view search
        const allFolders = Array.from(document.querySelectorAll('.patient-folder'));
        const allBranchSections = Array.from(document.querySelectorAll('.branch-section'));
        
        let searchTimeout;
        
        function performFolderSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchTerm = searchInput.value.toLowerCase().trim();
                const statusValue = statusFilter.value.toLowerCase();
                const branchValue = branchFilter.value;
                
                let visibleCount = 0;
                let visibleBranches = 0;
                
                // Handle branch filtering and search
                allBranchSections.forEach(branchSection => {
                    const branchId = branchSection.dataset.branchId;
                    const branchFolders = branchSection.querySelectorAll('.patient-folder');
                    let branchHasVisibleFolders = false;
                    
                    // Check if this branch should be shown based on branch filter
                    const shouldShowBranch = !branchValue || branchValue === branchId;
                    
                    if (shouldShowBranch) {
                        // Check each patient folder within this branch
                        branchFolders.forEach(folder => {
                            const shouldShow = matchesFolderCriteria(folder, searchTerm, statusValue, branchValue);
                            
                            if (shouldShow) {
                                folder.style.display = '';
                                folder.classList.add('search-match');
                                visibleCount++;
                                branchHasVisibleFolders = true;
                                
                                // If searching, auto-expand matching folders
                                if (searchTerm) {
                                    const patientId = folder.dataset.patientId;
                                    const branchId = folder.dataset.branchId;
                                    const content = folder.querySelector('.patient-records');
                                    if (content && content.classList.contains('hidden')) {
                                        togglePatientFolder(parseInt(patientId), branchId);
                                    }
                                }
                            } else {
                                folder.style.display = 'none';
                                folder.classList.remove('search-match');
                            }
                        });
                        
                        // Show/hide the entire branch section based on whether it has visible folders
                        if (branchHasVisibleFolders) {
                            branchSection.style.display = '';
                            visibleBranches++;
                            
                            // Auto-expand branch if searching
                            if (searchTerm || statusValue) {
                                const branchRecords = branchSection.querySelector('.branch-records');
                                if (branchRecords && branchRecords.classList.contains('hidden')) {
                                    toggleBranchSection(branchId);
                                }
                            }
                        } else {
                            branchSection.style.display = 'none';
                        }
                    } else {
                        // Hide entire branch if not matching branch filter
                        branchSection.style.display = 'none';
                    }
                });
                
                updateSearchSummary(visibleCount, searchTerm, statusValue, branchValue, visibleBranches);
                handleEmptyFolderResults(visibleCount);
            }, 300);
        }
        
        function matchesFolderCriteria(folder, searchTerm, statusValue, branchValue) {
            const patientName = folder.querySelector('h4')?.textContent.toLowerCase() || '';
            const patientEmail = folder.querySelector('.text-xs.text-gray-500')?.textContent.toLowerCase() || '';
            const patientStatus = folder.querySelector('.rounded-full')?.textContent.toLowerCase() || '';
            const folderBranchId = folder.dataset.branchId;
            
            const matchesSearchTerm = !searchTerm || 
                patientName.includes(searchTerm) ||
                patientEmail.includes(searchTerm);
            
            const matchesStatus = !statusValue || patientStatus.includes(statusValue);
            const matchesBranch = !branchValue || branchValue === folderBranchId;
            
            return matchesSearchTerm && matchesStatus && matchesBranch;
        }
        
        function updateSearchSummary(visibleCount, searchTerm, statusValue, branchValue, visibleBranches) {
            if (searchTerm || statusValue || branchValue) {
                searchSummary.classList.remove('hidden');
                searchResultsCount.textContent = visibleCount;
                
                let summaryText = '';
                if (searchTerm) summaryText += ` for "${searchTerm}"`;
                if (statusValue) summaryText += ` • Status: ${statusValue}`;
                if (branchValue) {
                    const branchName = branchValue === 'unassigned' ? 'Unassigned' : 
                        document.querySelector(`option[value="${branchValue}"]`)?.textContent || 'Selected Branch';
                    summaryText += ` • Branch: ${branchName}`;
                }
                if (visibleBranches > 0) summaryText += ` • ${visibleBranches} branch${visibleBranches !== 1 ? 'es' : ''}`;
                
                searchTermDisplay.textContent = summaryText;
            } else {
                searchSummary.classList.add('hidden');
            }
        }
        
        function handleEmptyFolderResults(visibleCount) {
            const foldersContainer = document.getElementById('foldersView');
            const existingEmptyMessage = foldersContainer.querySelector('.search-empty-message');
            
            if (visibleCount === 0 && allFolders.length > 0) {
                if (!existingEmptyMessage) {
                    const emptyMessage = document.createElement('div');
                    emptyMessage.className = 'search-empty-message text-center py-12';
                    emptyMessage.innerHTML = `
                        <div class="flex flex-col items-center">
                            <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
                            <p class="text-sm font-medium text-gray-700">No matching patient folders found</p>
                            <p class="text-xs text-gray-500 mt-1">Try adjusting your search criteria or clearing the filters.</p>
                        </div>
                    `;
                    foldersContainer.appendChild(emptyMessage);
                }
            } else if (existingEmptyMessage) {
                existingEmptyMessage.remove();
            }
        }
        
        function clearAllFilters() {
            searchInput.value = '';
            statusFilter.value = '';
            branchFilter.value = '';
            
            allFolders.forEach(folder => {
                folder.style.display = '';
                folder.classList.remove('search-match');
            });
            
            allBranchSections.forEach(branchSection => {
                branchSection.style.display = '';
            });
            
            searchSummary.classList.add('hidden');
            
            const existingEmptyMessage = document.getElementById('foldersView').querySelector('.search-empty-message');
            if (existingEmptyMessage) {
                existingEmptyMessage.remove();
            }
            
            searchInput.focus();
        }
        
        // Event listeners for folder search
        searchInput.addEventListener('input', performFolderSearch);
        statusFilter.addEventListener('change', performFolderSearch);
        branchFilter.addEventListener('change', performFolderSearch);
        clearButton.addEventListener('click', clearAllFilters);
        
    } else {
        // Table view search (original functionality)
        const tableBody = document.querySelector('#tableView tbody');
        const originalRows = Array.from(tableBody.querySelectorAll('tr')).filter(row => 
            !row.querySelector('td[colspan]')
        );
        
        let searchTimeout;
        
        function performTableSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchTerm = searchInput.value.toLowerCase().trim();
                const statusValue = statusFilter.value.toLowerCase();
                
                let visibleCount = 0;
                
                originalRows.forEach(row => {
                    const shouldShow = matchesTableCriteria(row, searchTerm, statusValue);
                    
                    if (shouldShow) {
                        row.style.display = '';
                        row.classList.add('search-match');
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                        row.classList.remove('search-match');
                    }
                });
                
                updateTableSearchSummary(visibleCount, searchTerm, statusValue);
                handleEmptyTableResults(visibleCount);
            }, 300);
        }
        
        function matchesTableCriteria(row, searchTerm, statusValue) {
            const dateCell = row.querySelector('td:nth-child(1)')?.textContent.toLowerCase() || '';
            const patientCell = row.querySelector('td:nth-child(2)');
            const typeCell = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
            const statusCell = row.querySelector('td:nth-child(4)')?.textContent.toLowerCase() || '';
            
            const patientName = patientCell?.querySelector('.font-medium')?.textContent.toLowerCase() || '';
            const patientContact = patientCell?.querySelector('.text-gray-500')?.textContent.toLowerCase() || '';
            const allergies = patientCell?.querySelector('.text-red-600')?.textContent.toLowerCase() || '';
            
            const matchesSearchTerm = !searchTerm || 
                dateCell.includes(searchTerm) ||
                patientName.includes(searchTerm) ||
                patientContact.includes(searchTerm) ||
                typeCell.includes(searchTerm) ||
                statusCell.includes(searchTerm) ||
                allergies.includes(searchTerm);
            
            const matchesStatus = !statusValue || statusCell.includes(statusValue);
            
            return matchesSearchTerm && matchesStatus;
        }
        
        function updateTableSearchSummary(visibleCount, searchTerm, statusValue) {
            if (searchTerm || statusValue) {
                searchSummary.classList.remove('hidden');
                searchResultsCount.textContent = visibleCount;
                
                let summaryText = '';
                if (searchTerm) summaryText += ` for "${searchTerm}"`;
                if (statusValue) summaryText += ` • Status: ${statusValue}`;
                
                searchTermDisplay.textContent = summaryText;
            } else {
                searchSummary.classList.add('hidden');
            }
        }
        
        function handleEmptyTableResults(visibleCount) {
            const tableBody = document.querySelector('#tableView tbody');
            const existingEmptyRow = tableBody.querySelector('.search-empty-row');
            
            if (visibleCount === 0 && originalRows.length > 0) {
                if (!existingEmptyRow) {
                    const emptyRow = document.createElement('tr');
                    emptyRow.className = 'search-empty-row';
                    emptyRow.innerHTML = `
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-search text-gray-300 text-3xl mb-3"></i>
                                <p class="text-lg font-medium">No matching records found</p>
                                <p class="text-sm">Try adjusting your search criteria or clearing the filters.</p>
                            </div>
                        </td>
                    `;
                    tableBody.appendChild(emptyRow);
                }
            } else if (existingEmptyRow) {
                existingEmptyRow.remove();
            }
        }
        
        function clearAllFilters() {
            searchInput.value = '';
            statusFilter.value = '';
            
            originalRows.forEach(row => {
                row.style.display = '';
                row.classList.remove('search-match');
            });
            
            searchSummary.classList.add('hidden');
            
            const existingEmptyRow = document.querySelector('#tableView tbody .search-empty-row');
            if (existingEmptyRow) {
                existingEmptyRow.remove();
            }
            
            searchInput.focus();
        }
        
        // Event listeners for table search
        searchInput.addEventListener('input', performTableSearch);
        statusFilter.addEventListener('change', performTableSearch);
        clearButton.addEventListener('click', clearAllFilters);
    }
    
    // Keyboard shortcuts (works for both views)
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + F to focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
        
        // Escape to clear search
        if (e.key === 'Escape' && document.activeElement === searchInput) {
            const clearButton = document.getElementById('clearSearch');
            clearButton.click();
        }
        
        // Folder view specific shortcuts
        if (currentView === 'folders') {
            // Ctrl/Cmd + E to expand all folders
            if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
                e.preventDefault();
                expandAllFolders();
            }
            
            // Ctrl/Cmd + R to collapse all folders
            if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                e.preventDefault();
                collapseAllFolders();
            }
        }
    });
    
    // Initialize with focus on search input
    searchInput.focus();
}

// Enhanced search suggestions
function enhanceSearchExperience() {
    const searchInput = document.getElementById('recordsSearchInput');
    
    const suggestions = [
        'Active patients', 'Inactive patients', 'Recent records', 
        'This month', 'Last week', 'Patients with allergies'
    ];
    
    const suggestionsContainer = document.createElement('div');
    suggestionsContainer.className = 'absolute top-full left-0 right-0 bg-white border border-gray-300 rounded-b-lg shadow-lg z-10 hidden max-h-48 overflow-y-auto';
    suggestionsContainer.id = 'searchSuggestions';
    
    searchInput.parentNode.classList.add('relative');
    searchInput.parentNode.appendChild(suggestionsContainer);
    
    searchInput.addEventListener('focus', () => {
        if (searchInput.value.length === 0) {
            showAllSuggestions();
        }
    });
    
    searchInput.addEventListener('blur', () => {
        setTimeout(() => suggestionsContainer.classList.add('hidden'), 150);
    });
    
    function showAllSuggestions() {
        suggestionsContainer.innerHTML = suggestions.map(suggestion => 
            `<div class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm border-b border-gray-100 last:border-b-0" onclick="selectSuggestion('${suggestion}')">${suggestion}</div>`
        ).join('');
        suggestionsContainer.classList.remove('hidden');
    }
    
    window.selectSuggestion = function(suggestion) {
        searchInput.value = suggestion;
        suggestionsContainer.classList.add('hidden');
        searchInput.dispatchEvent(new Event('input'));
        searchInput.focus();
    };
}

// Initialize enhanced search features
document.addEventListener('DOMContentLoaded', function() {
    enhanceSearchExperience();
});
</script>

<?= view('templates/footer') ?>
