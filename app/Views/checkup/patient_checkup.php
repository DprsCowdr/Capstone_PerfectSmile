<?= view('templates/header') ?>

<!-- Three.js Library for 3D Dental Model -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>

<!-- 3D Dental Viewer Styles and Scripts -->
<link rel="stylesheet" href="<?= base_url('css/dental-3d-viewer.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/annotation-tools.css') ?>">
<script src="<?= base_url('js/dental-3d-viewer.js') ?>"></script>
<script src="<?= base_url('js/dental-checkup.js') ?>"></script>

<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    
    <!-- Main content area -->
    <div class="flex-1 flex flex-col min-h-screen">
        <!-- Topbar -->
        <nav class="flex items-center justify-between bg-white shadow px-4 sm:px-6 py-4 mb-6">
            <!-- Mobile sidebar toggle is now in sidebar template -->
            <div class="lg:hidden w-10"></div> <!-- Spacer for mobile menu button -->
            
            <div class="flex items-center ml-auto">
                <span class="mr-4 hidden lg:inline text-gray-600 font-semibold"><?= $user['name'] ?? 'Dentist' ?></span>
                <div class="relative">
                    <button class="focus:outline-none">
                        <img class="w-8 h-8 sm:w-10 sm:h-10 rounded-full border-2 border-gray-200" src="<?= base_url('img/undraw_profile.svg') ?>" alt="Profile">
                    </button>
                </div>
            </div>
        </nav>
        
        <main class="flex-1 p-4 sm:p-6 lg:p-8">

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center py-4 sm:py-6 space-y-4 sm:space-y-0">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Patient Checkup</h1>
                    <p class="mt-1 text-sm text-gray-500">Complete dental examination and record</p>
                </div>
                
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i>
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php 
                $error = session()->getFlashdata('error');
                if (is_array($error)) {
                    foreach ($error as $err) {
                        echo $err . '<br>';
                    }
                } else {
                    echo $error;
                }
                ?>
            </div>
        <?php endif; ?>

        <!-- Patient Information -->
        <div class="bg-white rounded-xl shadow-lg mb-6 sm:mb-8">
            <div class="p-4 sm:p-6 border-b border-gray-200">
                <h2 class="text-lg sm:text-xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-user text-blue-500 mr-2 sm:mr-3"></i>
                    Patient Information
                </h2>
            </div>
            <div class="p-4 sm:p-6">
                <!-- Basic Patient Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">Basic Information</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Patient Name</label>
                            <p class="text-base sm:text-lg font-semibold text-gray-900"><?= $appointment['patient_name'] ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <p class="text-sm sm:text-base text-gray-600 break-words"><?= $appointment['patient_email'] ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <p class="text-sm sm:text-base text-gray-600"><?= $appointment['patient_phone'] ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                            <p class="text-sm sm:text-base text-gray-600"><?= $appointment['patient_dob'] ? date('M j, Y', strtotime($appointment['patient_dob'])) : 'Not provided' ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                            <p class="text-sm sm:text-base text-gray-600"><?= ucfirst($appointment['patient_gender']) ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Appointment Time</label>
                            <p class="text-sm sm:text-base text-gray-600"><?= date('g:i A', strtotime($appointment['appointment_time'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checkup Form -->
        <form id="checkupForm" action="/checkup/save/<?= $appointment['id'] ?>" method="POST" class="space-y-8">
            <!-- Hidden input for appointment ID -->
            <input type="hidden" name="appointment_id" value="<?= $appointment['id'] ?>">
            
            <!-- Hidden fields for all teeth surface and service data -->
            <?php for ($i = 1; $i <= 32; $i++): ?>
                <?php 
                $prev = isset($prevChartByTooth[$i]) ? $prevChartByTooth[$i] : [];
                ?>
                <input type="hidden" name="dental_chart[<?= $i ?>][surface]" value="<?= isset($prev['surface']) ? esc($prev['surface']) : '' ?>" id="tooth-<?= $i ?>-surface">
                <input type="hidden" name="dental_chart[<?= $i ?>][service_id]" value="<?= isset($prev['service_id']) ? esc($prev['service_id']) : '' ?>" id="tooth-<?= $i ?>-service">
            <?php endfor; ?>
            
            <!-- Dental Chart Section -->
            <div class="bg-white rounded-xl shadow-lg">
                <div class="p-4 sm:p-6 border-b border-gray-200">
                    <h2 class="text-lg sm:text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-tooth text-blue-500 mr-2 sm:mr-3"></i>
                        Dental Chart
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-600 mt-1">Click on teeth to mark conditions and treatments</p>
                </div>
                <div class="p-4 sm:p-6">
                    <!-- Dental Chart and 3D Model Layout -->
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 lg:gap-8">
                        <!-- Chart View -->
                        <div class="order-2 xl:order-1">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                                <i class="fas fa-list mr-2 text-blue-600"></i>Dental Chart
                            </h3>
                            <!-- Dental Chart -->
                            <div class="bg-gray-50 rounded-lg p-4 sm:p-6 mb-6">
                                <div class="text-center mb-4">
                                    <h4 class="text-sm sm:text-md font-semibold text-gray-800">TOOTH CHART</h4>
                                    <p class="text-xs sm:text-sm text-gray-600">Permanent Dentition (Universal Numbering System)</p>
                                </div>
                                
                                <?php
                                // Build a lookup for previous chart by tooth number
                                $prevChartByTooth = [];
                                if (!empty($previousChart)) {
                                    foreach ($previousChart as $tooth) {
                                        $prevChartByTooth[$tooth['tooth_number']] = $tooth;
                                    }
                                }
                                
                                // Tooth names mapping based on Universal Numbering System
                                $toothNames = [
                                    1 => '3rd Molar (Wisdom)',
                                    2 => '2nd Molar (12-yr)',
                                    3 => '1st Molar (6-yr)',
                                    4 => '2nd Bicuspid',
                                    5 => '1st Bicuspid',
                                    6 => 'Cuspid (Canine)',
                                    7 => 'Lateral Incisor',
                                    8 => 'Central Incisor',
                                    9 => 'Central Incisor',
                                    10 => 'Lateral Incisor',
                                    11 => 'Cuspid (Canine)',
                                    12 => '1st Bicuspid',
                                    13 => '2nd Bicuspid',
                                    14 => '1st Molar (6-yr)',
                                    15 => '2nd Molar (12-yr)',
                                    16 => '3rd Molar (Wisdom)',
                                    17 => '3rd Molar (Wisdom)',
                                    18 => '2nd Molar (12-yr)',
                                    19 => '1st Molar (6-yr)',
                                    20 => '2nd Bicuspid',
                                    21 => '1st Bicuspid',
                                    22 => 'Cuspid (Canine)',
                                    23 => 'Lateral Incisor',
                                    24 => 'Central Incisor',
                                    25 => 'Central Incisor',
                                    26 => 'Lateral Incisor',
                                    27 => 'Cuspid (Canine)',
                                    28 => '1st Bicuspid',
                                    29 => '2nd Bicuspid',
                                    30 => '1st Molar (6-yr)',
                                    31 => '2nd Molar (12-yr)',
                                    32 => '3rd Molar (Wisdom)'
                                ];
                                ?>
                                <script>
                                window.prevChartByTooth = <?php echo json_encode($prevChartByTooth); ?>;
                                </script>
                                
                                <!-- Upper Teeth -->
                                <div class="mb-6">
                                    <h4 class="text-center font-semibold text-gray-700 mb-3 text-sm sm:text-base">Upper Arch (Maxilla)</h4>
                                    <div class="grid grid-cols-16 gap-0.5 sm:gap-1 justify-center">
                                        <?php for ($i = 1; $i <= 16; $i++):
                                            $prev = $prevChartByTooth[$i] ?? [];
                                            $conditionClass = '';
                                            if (isset($prev['condition'])) {
                                                switch ($prev['condition']) {
                                                    case 'healthy': $conditionClass = 'bg-green-100 border-green-300'; break;
                                                    case 'cavity': $conditionClass = 'bg-red-100 border-red-300'; break;
                                                    case 'filled': $conditionClass = 'bg-blue-100 border-blue-300'; break;
                                                    case 'crown': $conditionClass = 'bg-slate-100 border-slate-300'; break;
                                                    default: $conditionClass = '';
                                                }
                                            }
                                        ?>
                                            <div class="relative">
                                                <button type="button" 
                                                        onclick="selectTooth(<?= $i ?>)" 
                                                        id="tooth-<?= $i ?>"
                                                        class="w-6 h-6 sm:w-8 sm:h-8 border-2 rounded-full hover:border-blue-500 transition-colors text-xs font-bold <?= $conditionClass ?>"
                                                        title="<?= $toothNames[$i] ?>">
                                                    <span class="text-xs sm:text-sm"><?= $i ?></span>
                                                </button>
                                                <div id="tooth-menu-<?= $i ?>" class="hidden absolute top-8 sm:top-10 left-0 z-10 bg-white border border-gray-300 rounded-lg shadow-lg p-3 min-w-48 max-w-xs">
                                                    <div class="mb-2">
                                                        <h5 class="font-semibold text-sm text-gray-800"><?= $toothNames[$i] ?></h5>
                                                        <p class="text-xs text-gray-500">Tooth #<?= $i ?></p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Condition:</label>
                                                        <select name="dental_chart[<?= $i ?>][condition]" class="w-full text-xs border border-gray-300 rounded px-2 py-1">
                                                            <option value="">Select condition</option>
                                                            <?php foreach ($toothConditions as $value => $label): ?>
                                                                <option value="<?= $value ?>" <?= (isset($prev['condition']) && $prev['condition'] === $value) ? 'selected' : '' ?>><?= $label ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Treatment:</label>
                                                        <select name="dental_chart[<?= $i ?>][treatment]" class="w-full text-xs border border-gray-300 rounded px-2 py-1">
                                                            <option value="">Select treatment</option>
                                                            <?php foreach ($treatmentOptions as $value => $label): ?>
                                                                <option value="<?= $value ?>" <?= (isset($prev['status']) && $prev['status'] === $value) ? 'selected' : '' ?>><?= $label ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Notes:</label>
                                                        <textarea name="dental_chart[<?= $i ?>][notes]" rows="2" class="w-full text-xs border border-gray-300 rounded px-2 py-1" placeholder="Additional notes..."><?= isset($prev['notes']) ? esc($prev['notes']) : '' ?></textarea>
                                                    </div>
                                                    <button type="button" onclick="closeToothMenu(<?= $i ?>)" class="w-full bg-blue-500 hover:bg-blue-600 text-white text-xs px-3 py-1 rounded">
                                                        Done
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>

                                <!-- Lower Teeth -->
                                <div>
                                    <h4 class="text-center font-semibold text-gray-700 mb-3 text-sm sm:text-base">Lower Arch (Mandible)</h4>
                                    <div class="grid grid-cols-16 gap-0.5 sm:gap-1 justify-center">
                                        <?php for ($i = 17; $i <= 32; $i++):
                                            $prev = $prevChartByTooth[$i] ?? [];
                                            $conditionClass = '';
                                            if (isset($prev['condition'])) {
                                                switch ($prev['condition']) {
                                                    case 'healthy': $conditionClass = 'bg-green-100 border-green-300'; break;
                                                    case 'cavity': $conditionClass = 'bg-red-100 border-red-300'; break;
                                                    case 'filled': $conditionClass = 'bg-blue-100 border-blue-300'; break;
                                                    case 'crown': $conditionClass = 'bg-slate-100 border-slate-300'; break;
                                                    default: $conditionClass = '';
                                                }
                                            }
                                        ?>
                                            <div class="relative">
                                                <button type="button" 
                                                        onclick="selectTooth(<?= $i ?>)" 
                                                        id="tooth-<?= $i ?>"
                                                        class="w-6 h-6 sm:w-8 sm:h-8 border-2 rounded-full hover:border-blue-500 transition-colors text-xs font-bold <?= $conditionClass ?>"
                                                        title="<?= $toothNames[$i] ?>">
                                                    <span class="text-xs sm:text-sm"><?= $i ?></span>
                                                </button>
                                                <div id="tooth-menu-<?= $i ?>" class="hidden absolute top-8 sm:top-10 left-0 z-10 bg-white border border-gray-300 rounded-lg shadow-lg p-3 min-w-48 max-w-xs">
                                                    <div class="mb-2">
                                                        <h5 class="font-semibold text-sm text-gray-800"><?= $toothNames[$i] ?></h5>
                                                        <p class="text-xs text-gray-500">Tooth #<?= $i ?></p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Condition:</label>
                                                        <select name="dental_chart[<?= $i ?>][condition]" class="w-full text-xs border border-gray-300 rounded px-2 py-1">
                                                            <option value="">Select condition</option>
                                                            <?php foreach ($toothConditions as $value => $label): ?>
                                                                <option value="<?= $value ?>" <?= (isset($prev['condition']) && $prev['condition'] === $value) ? 'selected' : '' ?>><?= $label ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Treatment:</label>
                                                        <select name="dental_chart[<?= $i ?>][treatment]" class="w-full text-xs border border-gray-300 rounded px-2 py-1">
                                                            <option value="">Select treatment</option>
                                                            <?php foreach ($treatmentOptions as $value => $label): ?>
                                                                <option value="<?= $value ?>" <?= (isset($prev['status']) && $prev['status'] === $value) ? 'selected' : '' ?>><?= $label ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Notes:</label>
                                                        <textarea name="dental_chart[<?= $i ?>][notes]" rows="2" class="w-full text-xs border border-gray-300 rounded px-2 py-1" placeholder="Additional notes..."><?= isset($prev['notes']) ? esc($prev['notes']) : '' ?></textarea>
                                                    </div>
                                                    <button type="button" onclick="closeToothMenu(<?= $i ?>)" class="w-full bg-blue-500 hover:bg-blue-600 text-white text-xs px-3 py-1 rounded">
                                                        Done
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Chart Legend -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-4 text-xs sm:text-sm">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 sm:w-4 sm:h-4 bg-green-100 border border-green-300 rounded mr-2"></div>
                                    <span>Healthy</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 sm:w-4 sm:h-4 bg-red-100 border border-red-300 rounded mr-2"></div>
                                    <span>Cavity</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 sm:w-4 sm:h-4 bg-blue-100 border border-blue-300 rounded mr-2"></div>
                                    <span>Filled</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 sm:w-4 sm:h-4 bg-slate-100 border border-slate-300 rounded mr-2"></div>
                                    <span>Crown</span>
                                </div>
                            </div>
                        </div>

                        <!-- 3D Model Viewer -->
                        <div class="order-3 xl:order-3">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-800">
                                    <i class="fas fa-cube mr-2 text-blue-600"></i>3D Dental Model
                                </h3>
                                <button id="toggle3DMinBtn" type="button" class="px-3 py-1.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded border border-gray-200">
                                    Minimize
                                </button>
                            </div>

                            <div id="threeDPanelBody">
                            <div class="dental-3d-viewer-container">
                                <div id="dentalModelViewer" class="dental-3d-viewer">
                                    <div class="model-loading" id="modelLoading">
                                        <i class="fas fa-spinner fa-spin mr-2"></i>Loading 3D Model...
                                    </div>
                                    <div class="model-error hidden" id="modelError">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        <div>Failed to load 3D model</div>
                                        <button onclick="location.reload()" class="mt-2 px-3 py-1 bg-blue-500 text-white rounded text-sm">Retry</button>
                                    </div>
                                    <canvas class="dental-3d-canvas"></canvas>
                                    
                                    <!-- Tooth Highlight Indicator -->
                                    <div class="tooth-highlight" id="toothHighlight"></div>
                                    
                                    <!-- Treatment Popup -->
                                    <div class="treatment-popup" id="treatmentPopup">
                                        <div class="treatment-popup-header">
                                            <span class="treatment-popup-title" id="popupTitle">Tooth Information</span>
                                            <button type="button" class="treatment-popup-close" onclick="closeTreatmentPopup()">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="treatment-popup-content">
                                            <!-- Content will be dynamically populated by JavaScript -->
                                        </div>
                                    </div>
                                    
                                    <div class="model-controls">
                                        <button class="model-control-btn" onclick="resetCamera()" title="Reset View">
                                            <i class="fas fa-home"></i>
                                        </button>
                                        <button class="model-control-btn" onclick="toggleWireframe()" title="Toggle Wireframe">
                                            <i class="fas fa-border-all"></i>
                                        </button>
                                        <button class="model-control-btn" onclick="toggleAutoRotate()" title="Auto Rotate">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                        <button class="model-control-btn lg:block hidden" onclick="debugToothMapping()" title="Debug Tooth Mapping">
                                            <i class="fas fa-bug"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 3D Model Color Legend -->
                            <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">3D Model Color Legend:</h4>
                                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 text-xs">
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 bg-green-400 rounded mr-2"></div>
                                        <span>Healthy</span>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 bg-red-500 rounded mr-2"></div>
                                        <span>Cavity</span>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 bg-blue-500 rounded mr-2"></div>
                                        <span>Filled</span>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 bg-yellow-400 rounded mr-2"></div>
                                        <span>Crown</span>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 border-2 border-dashed border-gray-400 rounded mr-2 bg-transparent"></div>
                                        <span>Missing (Invisible)</span>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 bg-orange-400 rounded mr-2"></div>
                                        <span>Root Canal</span>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 bg-red-700 rounded mr-2"></div>
                                        <span>Extraction Needed</span>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 bg-gray-200 border border-gray-300 rounded mr-2"></div>
                                        <span>Normal</span>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visual Dental Chart Section -->
            <div class="bg-white rounded-xl shadow-lg">
                <div class="p-4 sm:p-6 border-b border-gray-200">
                    <h2 class="text-lg sm:text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-tooth mr-2 text-green-600"></i>
                        Visual Dental Chart
                    </h2>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="bg-white rounded-lg border border-gray-200 p-4">
                        <!-- Drawing Controls -->
                        <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6">
                                <!-- Tool Selection -->
                                <div class="flex-shrink-0">
                                    <h4 class="text-sm font-semibold text-gray-800 mb-2">Tools</h4>
                                    <div class="flex flex-wrap gap-2" id="annotation-tools">
                                        <button type="button" data-tool="draw" class="annotation-tool-btn">
                                            <i class="fas fa-pencil-alt w-4 text-center"></i>
                                            <span class="ml-2">Draw</span>
                                        </button>
                                        <button type="button" data-tool="erase" class="annotation-tool-btn">
                                            <i class="fas fa-eraser w-4 text-center"></i>
                                            <span class="ml-2">Erase</span>
                                        </button>
                                        <button type="button" data-tool="clear" class="annotation-tool-btn hover:bg-red-100 hover:border-red-300 hover:text-red-700">
                                            <i class="fas fa-trash-alt w-4 text-center"></i>
                                            <span class="ml-2">Clear</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Properties -->
                                <div class="flex-1 flex flex-col sm:flex-row gap-6">
                                    <!-- Color Palette -->
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-800 mb-2">Color</h4>
                                        <div class="flex flex-wrap gap-2" id="color-palette">
                                            <button type="button" data-color="#dc2626" class="color-swatch" style="background-color: #dc2626;" title="Cavities/Problems"></button>
                                            <button type="button" data-color="#2563eb" class="color-swatch" style="background-color: #2563eb;" title="Restorations"></button>
                                            <button type="button" data-color="#16a34a" class="color-swatch" style="background-color: #16a34a;" title="Healthy"></button>
                                            <button type="button" data-color="#eab308" class="color-swatch" style="background-color: #eab308;" title="Crown/Bridge"></button>
                                            <button type="button" data-color="#9333ea" class="color-swatch" style="background-color: #9333ea;" title="Root Canal"></button>
                                            <button type="button" data-color="#000000" class="color-swatch" style="background-color: #000000;" title="Missing"></button>
                                        </div>
                                    </div>

                                    <!-- Brush Size -->
                                    <div class="flex-1 min-w-[120px]">
                                        <h4 class="text-sm font-semibold text-gray-800 mb-2">Brush Size</h4>
                                        <div class="flex items-center gap-3">
                                            <input type="range" id="visualBrushSize" min="2" max="20" value="5" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                                            <span id="visualBrushSizeDisplay" class="text-sm text-gray-600 font-mono w-8 text-center bg-white px-2 py-1 rounded-md border border-gray-300">5</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Interactive Dental Chart Container -->
                        <div class="visual-chart-scroll w-full">
                            <div class="image-container-checkup relative border-2 border-gray-200 rounded-lg overflow-hidden bg-white">
                                <img id="visualDentalChart" src="<?= base_url('img/d.jpg') ?>" alt="Interactive Dental Chart" usemap="#dental-map" class="block cursor-pointer w-full h-auto">
                                <canvas id="visualDrawingCanvas" class="visual-drawing-canvas absolute top-0 left-0 pointer-events-none"></canvas>
                            </div>
                        </div>

                        <!-- Save Drawing Data -->
                        <input type="hidden" name="visual_chart_data" id="visualChartData" value="<?= htmlspecialchars($existingVisualChartData ?? '') ?>">
                        
                        <!-- Chart Information -->
                        <div class="mt-3 p-3 bg-blue-50 rounded-lg">
                            <p class="text-xs text-blue-700">
                                <i class="fas fa-info-circle mr-1"></i>
                                Click and drag to draw on the chart. Use different colors to mark various conditions. 
                                Your annotations will be saved with the patient record.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Treatment Section -->
            <div class="bg-white rounded-xl shadow-lg">
                <div class="p-4 sm:p-6 border-b border-gray-200">
                    <h2 class="text-lg sm:text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-stethoscope text-blue-500 mr-2 sm:mr-3"></i>
                        Treatment <span class="text-red-500 text-sm font-normal">(Required)</span>
                    </h2>
                    <p class="text-sm text-gray-600 mt-2">Record the services and procedures performed during this checkup.</p>
                </div>
                <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">

                    <div>
                        <label for="treatment" class="block text-sm font-medium text-gray-700 mb-2">
                            Treatment Performed <span class="text-red-500">*</span>
                            <span class="text-gray-500 font-normal">(Services and procedures completed)</span>
                        </label>
                        
                        <!-- Quick Treatment Templates -->
                        <div class="mb-3">
                            <label class="block text-xs font-medium text-gray-600 mb-2">Common Treatments (click to use):</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                <button type="button" onclick="setTreatment('Routine dental cleaning and oral examination completed.')" 
                                        class="px-3 py-1 bg-green-100 hover:bg-green-200 text-green-800 text-xs rounded-lg border border-green-300 transition-colors">
                                    ✓ Routine Cleaning
                                </button>
                                <button type="button" onclick="setTreatment('Dental filling procedure completed on affected tooth.')" 
                                        class="px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs rounded-lg border border-blue-300 transition-colors">
                                    🔧 Filling
                                </button>
                                <button type="button" onclick="setTreatment('Deep cleaning (scaling and root planing) performed.')" 
                                        class="px-3 py-1 bg-orange-100 hover:bg-orange-200 text-orange-800 text-xs rounded-lg border border-orange-300 transition-colors">
                                    📋 Deep Cleaning
                                </button>
                                <button type="button" onclick="setTreatment('Fluoride treatment applied for cavity prevention.')" 
                                        class="px-3 py-1 bg-purple-100 hover:bg-purple-200 text-purple-800 text-xs rounded-lg border border-purple-300 transition-colors">
                                    🦷 Fluoride Treatment
                                </button>
                                <button type="button" onclick="setTreatment('Crown placement procedure completed.')" 
                                        class="px-3 py-1 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 text-xs rounded-lg border border-yellow-300 transition-colors">
                                    👑 Crown Placement
                                </button>
                                <button type="button" onclick="setTreatment('Root canal therapy performed.')" 
                                        class="px-3 py-1 bg-red-100 hover:bg-red-200 text-red-800 text-xs rounded-lg border border-red-300 transition-colors">
                                    � Root Canal
                                </button>
                                <button type="button" onclick="setTreatment('Tooth extraction completed.')" 
                                        class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs rounded-lg border border-gray-300 transition-colors">
                                    ❌ Extraction
                                </button>
                                <button type="button" onclick="setTreatment('Oral examination and consultation only - no procedures performed.')" 
                                        class="px-3 py-1 bg-indigo-100 hover:bg-indigo-200 text-indigo-800 text-xs rounded-lg border border-indigo-300 transition-colors">
                                    � Consultation
                                </button>
                                <button type="button" onclick="clearTreatment()" 
                                        class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs rounded-lg border border-gray-300 transition-colors">
                                    🗑️ Clear Field
                                </button>
                            </div>
                        </div>
                        
                        <textarea id="treatment" name="treatment" rows="4" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base"
                                  placeholder="Enter treatment performed (e.g., 'Routine cleaning completed', 'Filling placed on tooth #14', 'Root canal therapy on tooth #3', etc.)..."><?= old('treatment') ?></textarea>
                        <p class="text-xs text-gray-500 mt-1">Describe the actual services and procedures performed during this visit.</p>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Additional Notes <span class="text-gray-500 font-normal">(Optional)</span></label>
                        <textarea id="notes" name="notes" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base"
                                  placeholder="Any additional observations, patient concerns, or special instructions (leave blank if none)..."><?= old('notes') ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label for="next_appointment_date" class="block text-sm font-medium text-gray-700 mb-2">Next Appointment Date <span class="text-gray-500 font-normal">(Optional)</span></label>
                            <input type="date" id="next_appointment_date" name="next_appointment_date"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base"
                                   value="<?= old('next_appointment_date') ?>">
                        </div>

                        <div>
                            <label for="next_appointment_time" class="block text-sm font-medium text-gray-700 mb-2">Next Appointment Time <span class="text-gray-500 font-normal">(Optional)</span></label>
                            <input type="time" id="next_appointment_time" name="next_appointment_time"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base"
                                   value="<?= old('next_appointment_time') ?>">
                        </div>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-500 mt-2"><span class="font-medium">Note:</span> Leave appointment fields empty if no follow-up is needed. All medical history fields are optional - staff can skip any fields if patient doesn't have the information or prefers not to answer.</p>
                </div>
            </div>

            <!-- Services section removed per request -->

            <!-- Patient History Section -->
            <?php if (!empty($previousRecords)): ?>
            <div class="bg-white rounded-xl shadow-lg">
                <div class="p-4 sm:p-6 border-b border-gray-200">
                    <h2 class="text-lg sm:text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-history text-blue-500 mr-2 sm:mr-3"></i>
                        Patient History
                    </h2>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="space-y-3 sm:space-y-4">
                        <?php foreach (array_slice($previousRecords, 0, 3) as $record): ?>
                            <div class="border border-gray-200 rounded-lg p-3 sm:p-4">
                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-2 space-y-1 sm:space-y-0">
                                    <h4 class="font-semibold text-gray-800 text-sm sm:text-base"><?= date('M j, Y', strtotime($record['record_date'])) ?></h4>
                                    <span class="text-xs sm:text-sm text-gray-500">Dr. <?= $record['dentist_name'] ?></span>
                                b</div>
                                <p class="text-xs sm:text-sm text-gray-600"><strong>Treatment:</strong> <?= $record['treatment'] ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Submit Buttons -->
            <div class="flex flex-col sm:flex-row justify-end space-y-4 sm:space-y-0 sm:space-x-4">
                <a href="/checkup" class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold transition-colors text-center">
                    Cancel
                </a>
                <button type="submit" class="w-full sm:w-auto bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                    <i class="fas fa-save mr-2"></i>Complete Checkup
                </button>
            </div>
        </form>

        <script>
        (function() {
            const form = document.getElementById('checkupForm');
            if (!form) return;
            const prev = (window.prevChartByTooth || {});
            const normalize = (v) => {
                if (v === undefined || v === null) return '';
                return ('' + v).trim();
            };
            form.addEventListener('submit', function() {
                console.log('=== FORM SUBMISSION DEBUG ===');
                const changedTeeth = new Set();
                for (let i = 1; i <= 32; i++) {
                    const condEl = form.querySelector(`select[name="dental_chart[${i}][condition]"]`);
                    const treatEl = form.querySelector(`select[name="dental_chart[${i}][treatment]"]`);
                    const notesEl = form.querySelector(`textarea[name="dental_chart[${i}][notes]"]`);
                    const serviceEl = document.getElementById(`tooth-${i}-service`);
                    const surfaceEl = document.getElementById(`tooth-${i}-surface`);
                    if (!condEl && !treatEl && !notesEl) continue;
                    const curCond = normalize(condEl ? condEl.value : '');
                    const curTreat = normalize(treatEl ? treatEl.value : '');
                    const curNotes = normalize(notesEl ? notesEl.value : '');
                    const curService = normalize(serviceEl ? serviceEl.value : '');
                    const curSurface = normalize(surfaceEl ? surfaceEl.value : '');
                    
                    // Debug logging for teeth with any data
                    if (curCond || curTreat || curNotes || curService || curSurface) {
                        console.log(`Tooth ${i}:`, {
                            condition: curCond,
                            treatment: curTreat, 
                            notes: curNotes,
                            service_id: curService,
                            surface: curSurface
                        });
                    }
                    
                    // Special debugging for tooth 29 (FDI 45)
                    if (i === 29) {
                        console.log('*** TOOTH 29 (FDI 45) SUBMISSION CHECK ***');
                        console.log('Form elements:');
                        console.log('- Service element:', serviceEl ? 'FOUND' : 'NOT FOUND');
                        console.log('- Surface element:', surfaceEl ? 'FOUND' : 'NOT FOUND');
                        console.log('Current values:');
                        console.log('- Service:', curService);
                        console.log('- Surface:', curSurface);
                        console.log('Previous values from window.prevChartByTooth:');
                        console.log('- Service:', prevService);
                        console.log('- Surface:', prevSurface);
                        console.log('Has any data:', hasAny);
                        console.log('Changed detection:', changed);
                    }
                    
                    const p = prev[i] || {};
                    const prevCond = normalize(p.condition);
                    const prevTreat = normalize(p.status);
                    const prevNotes = normalize(p.notes);
                    const prevService = normalize(p.service_id);
                    const prevSurface = normalize(p.surface);
                    const hasAny = (curCond !== '') || (curTreat !== '') || (curNotes !== '') || (curService !== '') || (curSurface !== '');
                    const changed = (curCond !== prevCond) || (curTreat !== prevTreat) || (curNotes !== prevNotes) || (curService !== prevService) || (curSurface !== prevSurface);
                    if (hasAny && changed) {
                        changedTeeth.add(i);
                        console.log(`Tooth ${i} CHANGED - will be saved`);
                    }
                }
                console.log('Total changed teeth:', Array.from(changedTeeth));
                console.log('=== END FORM DEBUG ===');
                
                for (let i = 1; i <= 32; i++) {
                    if (changedTeeth.has(i)) continue;
                    ['condition', 'treatment', 'notes'].forEach((field) => {
                        const el = form.querySelector(`[name="dental_chart[${i}][${field}]"]`);
                        if (el) el.disabled = true;
                    });
                    // Handle service and surface fields by ID
                    const serviceEl = document.getElementById(`tooth-${i}-service`);
                    const surfaceEl = document.getElementById(`tooth-${i}-surface`);
                    if (serviceEl) serviceEl.disabled = true;
                    if (surfaceEl) surfaceEl.disabled = true;
                }
            });
        })();
        </script>
    </div>
</div>

<style>
.grid-cols-16 {
    grid-template-columns: repeat(16, minmax(0, 1fr));
}

/* Visual Dental Chart Styles */
.image-container-checkup {
    position: relative;
    display: inline-block;

}

.visual-drawing-canvas {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10;
    pointer-events: none;
    cursor: default;
}

.visual-drawing-canvas.active {
    pointer-events: all;
    cursor: crosshair;
}

.visual-drawing-canvas.eraser {
    cursor: grab;
}

#visualDentalChart {
    display: block;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    /* width and max-width are handled by Tailwind classes on the element */
}

/* Responsive adjustments for visual chart */
@media (max-width: 768px) {
    /* Adjustments can be made here if needed for smaller screens */
}

/* Visual chart scroll wrapper is now just a container */
.visual-chart-scroll {
    -webkit-overflow-scrolling: touch;
}
</style>

<script>
function toggleTreatmentCondition() {
    const underTreatmentYes = document.querySelector('input[name="under_treatment"][value="yes"]');
    const treatmentConditionDiv = document.getElementById('treatment_condition_div');
    
    if (underTreatmentYes.checked) {
        treatmentConditionDiv.classList.remove('hidden');
    } else {
        treatmentConditionDiv.classList.add('hidden');
    }
}

function toggleIllnessDetails() {
    const seriousIllnessYes = document.querySelector('input[name="serious_illness"][value="yes"]');
    const illnessDetailsDiv = document.getElementById('illness_details_div');
    
    if (seriousIllnessYes.checked) {
        illnessDetailsDiv.classList.remove('hidden');
    } else {
        illnessDetailsDiv.classList.add('hidden');
    }
}

function toggleHospitalizationDetails() {
    const hospitalizedYes = document.querySelector('input[name="hospitalized"][value="yes"]');
    const hospitalizationDetailsDiv = document.getElementById('hospitalization_details_div');
    
    if (hospitalizedYes.checked) {
        hospitalizationDetailsDiv.classList.remove('hidden');
    } else {
        hospitalizationDetailsDiv.classList.add('hidden');
    }
}

function updateHiddenField(fieldName, value) {
    const hiddenField = document.querySelector(`input[type="hidden"][name="${fieldName}"]`);
    if (hiddenField) {
        hiddenField.value = value;
    }
}

function updateHiddenFieldRadio(fieldName, value) {
    updateHiddenField(fieldName, value);
    // Also handle conditional fields
    if (fieldName === 'under_treatment') {
        toggleTreatmentCondition();
    } else if (fieldName === 'serious_illness') {
        toggleIllnessDetails();
    } else if (fieldName === 'hospitalized') {
        toggleHospitalizationDetails();
    }
}

function updateMedicalConditions() {
    // Remove existing hidden medical condition inputs
    const existingConditions = document.querySelectorAll('input[type="hidden"][name="medical_conditions[]"]');
    existingConditions.forEach(input => input.remove());
    
    // Add new hidden inputs for checked conditions
    const checkedConditions = document.querySelectorAll('input[name="medical_conditions[]"]:checked');
    const form = document.querySelector('form[action*="/checkup/save/"]');
    
    checkedConditions.forEach(checkbox => {
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'medical_conditions[]';
        hiddenInput.value = checkbox.value;
        form.appendChild(hiddenInput);
    });
}

// Initialize form state on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check if any conditional fields should be shown
    toggleTreatmentCondition();
    toggleIllnessDetails();
    toggleHospitalizationDetails();
    
    // Populate existing medical history data
    <?php if (isset($patient)): ?>
        // Set radio button values
        <?php if (!empty($patient['good_health'])): ?>
            const goodHealthRadio = document.querySelector(`input[name="good_health"][value="<?= $patient['good_health'] ?>"]`);
            if (goodHealthRadio) goodHealthRadio.checked = true;
        <?php endif; ?>
        
        <?php if (!empty($patient['under_treatment'])): ?>
            const underTreatmentRadio = document.querySelector(`input[name="under_treatment"][value="<?= $patient['under_treatment'] ?>"]`);
            if (underTreatmentRadio) underTreatmentRadio.checked = true;
        <?php endif; ?>
        
        <?php if (!empty($patient['serious_illness'])): ?>
            const seriousIllnessRadio = document.querySelector(`input[name="serious_illness"][value="<?= $patient['serious_illness'] ?>"]`);
            if (seriousIllnessRadio) seriousIllnessRadio.checked = true;
        <?php endif; ?>
        
        <?php if (!empty($patient['hospitalized'])): ?>
            const hospitalizedRadio = document.querySelector(`input[name="hospitalized"][value="<?= $patient['hospitalized'] ?>"]`);
            if (hospitalizedRadio) hospitalizedRadio.checked = true;
        <?php endif; ?>
        
        <?php if (!empty($patient['tobacco_use'])): ?>
            const tobaccoRadio = document.querySelector(`input[name="tobacco_use"][value="<?= $patient['tobacco_use'] ?>"]`);
            if (tobaccoRadio) tobaccoRadio.checked = true;
        <?php endif; ?>
        
        <?php if (!empty($patient['pregnant'])): ?>
            const pregnantRadio = document.querySelector(`input[name="pregnant"][value="<?= $patient['pregnant'] ?>"]`);
            if (pregnantRadio) pregnantRadio.checked = true;
        <?php endif; ?>
        
        <?php if (!empty($patient['nursing'])): ?>
            const nursingRadio = document.querySelector(`input[name="nursing"][value="<?= $patient['nursing'] ?>"]`);
            if (nursingRadio) nursingRadio.checked = true;
        <?php endif; ?>
        
        <?php if (!empty($patient['birth_control'])): ?>
            const birthControlRadio = document.querySelector(`input[name="birth_control"][value="<?= $patient['birth_control'] ?>"]`);
            if (birthControlRadio) birthControlRadio.checked = true;
        <?php endif; ?>
        
        // Set medical conditions checkboxes
        <?php if (isset($patient['medical_conditions']) && is_array($patient['medical_conditions'])): ?>
            <?php foreach ($patient['medical_conditions'] as $condition): ?>
                const conditionCheckbox = document.querySelector(`input[name="medical_conditions[]"][value="<?= esc($condition) ?>"]`);
                if (conditionCheckbox) conditionCheckbox.checked = true;
            <?php endforeach; ?>
        <?php endif; ?>
        
        // Re-check conditional field visibility after populating data
        toggleTreatmentCondition();
        toggleIllnessDetails();
        toggleHospitalizationDetails();
    <?php endif; ?>
    
    // Add event listeners for medical conditions
    const medicalConditionCheckboxes = document.querySelectorAll('input[name="medical_conditions[]"]');
    medicalConditionCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateMedicalConditions);
    });

    // On submit, make sure all visible fields (outside the <form>) are synced into the hidden inputs inside the form
    const form = document.querySelector('form[action*="/checkup/save/"]');
    if (form) {
        form.addEventListener('submit', function() {
            // Always refresh medical conditions hidden fields
            updateMedicalConditions();

            // Helper to sync text/textarea inputs
            const syncField = (name) => {
                const hidden = document.querySelector(`input[type="hidden"][name="${name}"]`);
                const input = document.querySelector(`[name="${name}"]`);
                if (hidden && input) hidden.value = input.value ?? '';
            };

            // Helper to sync radio groups
            const syncRadio = (name) => {
                const checked = document.querySelector(`input[name="${name}"]:checked`);
                const hidden = document.querySelector(`input[type="hidden"][name="${name}"]`);
                if (hidden) hidden.value = checked ? checked.value : '';
            };

            // Dental history
            syncField('previous_dentist');
            syncField('last_dental_visit');

            // Physician details
            syncField('physician_name');
            syncField('physician_specialty');
            syncField('physician_phone');
            syncField('physician_address');

            // General health
            syncRadio('good_health');
            syncRadio('under_treatment');
            syncField('treatment_condition');
            syncRadio('serious_illness');
            syncField('illness_details');
            syncRadio('hospitalized');
            syncField('hospitalization_where');
            syncField('hospitalization_when');
            syncField('hospitalization_why');
            syncRadio('tobacco_use');
            syncField('blood_pressure');
            syncField('allergies');

            // Women-only
            syncRadio('pregnant');
            syncRadio('nursing');
            syncRadio('birth_control');

            // Other
            syncField('other_conditions');

            // Sync visual chart data before form submission
            saveVisualChartData();
            
            // Debug: Check what's actually in the hidden input
            const visualChartInput = document.getElementById('visualChartData');
            if (visualChartInput) {
                console.log('📤 Form submission: Visual chart data being sent:', visualChartInput.value.substring(0, 200) + '...');
                console.log('📤 Visual chart data length:', visualChartInput.value.length);
                console.log('📤 Is JSON format?', visualChartInput.value.trim().startsWith('{'));
            } else {
                console.error('❌ Visual chart input not found during form submission!');
            }
            
            console.log('Form submission: Visual chart data synced');
        });
    }
});

// Template functions for Treatment
function setTreatment(text) {
    const treatmentField = document.getElementById('treatment');
    treatmentField.value = text;
    treatmentField.focus();
    // Add a small visual feedback
    treatmentField.style.borderColor = '#10B981';
    setTimeout(() => {
        treatmentField.style.borderColor = '';
    }, 1000);
}

function clearTreatment() {
    const treatmentField = document.getElementById('treatment');
    treatmentField.value = '';
    treatmentField.focus();
    // Add a small visual feedback
    treatmentField.style.borderColor = '#EF4444';
    setTimeout(() => {
        treatmentField.style.borderColor = '';
    }, 1000);
}

// ==================== VISUAL DENTAL CHART FUNCTIONALITY ====================

// Visual dental chart variables
let visualCanvas, visualCtx;
let visualAnnotations = [];
let visualCurrentStroke = null;
let isVisualDrawing = false;
let isVisualDrawingMode = false;
let isVisualEraserMode = false;
let visualCurrentColor = '#dc2626';
let visualBrushSize = 5;
let visualLastX = 0;
let visualLastY = 0;

// Initialize visual dental chart when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeVisualChart();
    initializeResponsiveMap();
});

function initializeResponsiveMap() {
    const image = document.getElementById('visualDentalChart');
    const map = document.querySelector('map[name="dental-map"]');
    if (!image || !map) return;

    const areas = map.getElementsByTagName('area');
    const areasCoords = [];

    // Store original coordinates
    for (let i = 0; i < areas.length; i++) {
        areasCoords[i] = areas[i].coords.split(',');
    }

    function resizeMap() {
        if (!image.complete) {
            image.addEventListener('load', resizeMap);
            return;
        }

        const scale = image.offsetWidth / image.naturalWidth;

        for (let i = 0; i < areas.length; i++) {
            const newCoords = [];
            for (let j = 0; j < areasCoords[i].length; j++) {
                newCoords.push(Math.round(parseInt(areasCoords[i][j]) * scale));
            }
            areas[i].coords = newCoords.join(',');
        }
    }

    // Initial resize and on window resize
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(resizeMap, 100);
    });

    if (image.complete) {
        resizeMap();
    } else {
        image.addEventListener('load', resizeMap);
    }
}

function initializeVisualChart() {
    visualCanvas = document.getElementById('visualDrawingCanvas');
    if (!visualCanvas) return;
    
    visualCtx = visualCanvas.getContext('2d');
    const image = document.getElementById('visualDentalChart');
    
    // Set canvas size to match image
    function resizeVisualCanvas() {
        if (!image.complete) {
            image.addEventListener('load', resizeVisualCanvas);
            return;
        }
        const width = image.naturalWidth || image.width;
        const height = image.naturalHeight || image.height;
        visualCanvas.width = width;
        visualCanvas.height = height;
    }
    
    // Initial resize and load
    if (image.complete) {
        resizeVisualCanvas();
        loadExistingVisualChartData();
    } else {
        image.addEventListener('load', function() {
            resizeVisualCanvas();
            loadExistingVisualChartData();
        });
    }
    
    // Throttled resize
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(resizeVisualCanvas, 100);
    });
    
    // Set up event listeners for the new toolbar
    setupAnnotationToolbar();
    setupVisualCanvasEvents();
}

function setupAnnotationToolbar() {
    const toolButtons = document.querySelectorAll('.annotation-tool-btn');
    const colorSwatches = document.querySelectorAll('.color-swatch');
    const brushSizeSlider = document.getElementById('visualBrushSize');

    toolButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const tool = btn.dataset.tool;
            if (tool === 'draw') enableVisualDrawing();
            else if (tool === 'erase') enableVisualEraser();
            else if (tool === 'clear') clearVisualDrawing();
        });
    });

    colorSwatches.forEach(swatch => {
        swatch.addEventListener('click', () => {
            setVisualDrawColor(swatch.dataset.color);
        });
    });

    brushSizeSlider.addEventListener('input', (e) => {
        setVisualBrushSize(e.target.value);
    });

    // Set initial active states
    updateActiveTool('draw');
    updateActiveColor('#dc2626');
}

function updateActiveTool(activeTool) {
    document.querySelectorAll('.annotation-tool-btn').forEach(btn => {
        btn.classList.toggle('active-tool', btn.dataset.tool === activeTool);
    });
}

function updateActiveColor(activeColor) {
    document.querySelectorAll('.color-swatch').forEach(swatch => {
        swatch.classList.toggle('active-color', swatch.dataset.color === activeColor);
    });
}

function setupVisualCanvasEvents() {
    if (!visualCanvas) return;
    
    visualCanvas.addEventListener('mousedown', startVisualDrawing);
    visualCanvas.addEventListener('mousemove', visualDraw);
    visualCanvas.addEventListener('mouseup', stopVisualDrawing);
    visualCanvas.addEventListener('mouseout', stopVisualDrawing);
    
    // Touch events for mobile
    visualCanvas.addEventListener('touchstart', handleVisualTouch);
    visualCanvas.addEventListener('touchmove', handleVisualTouch);
    visualCanvas.addEventListener('touchend', stopVisualDrawing);
}

function enableVisualDrawing() {
    isVisualDrawingMode = true;
    isVisualEraserMode = false;
    visualCanvas.classList.add('active');
    visualCanvas.style.pointerEvents = 'all';
    visualCanvas.style.cursor = 'crosshair';
    updateActiveTool('draw');
}

function enableVisualEraser() {
    isVisualDrawingMode = true;
    isVisualEraserMode = true;
    visualCanvas.classList.add('active');
    visualCanvas.style.pointerEvents = 'all';
    visualCanvas.style.cursor = 'grab';
    updateActiveTool('erase');
}

function startVisualDrawing(e) {
    if (!isVisualDrawingMode) return;
    
    isVisualDrawing = true;
    const rect = visualCanvas.getBoundingClientRect();
    const scaleX = visualCanvas.width / rect.width;
    const scaleY = visualCanvas.height / rect.height;

    visualLastX = (e.clientX - rect.left) * scaleX;
    visualLastY = (e.clientY - rect.top) * scaleY;

    // Start a new stroke
    visualCurrentStroke = {
        tool: isVisualEraserMode ? 'eraser' : 'pen',
        color: visualCurrentColor,
        size: Number(visualBrushSize),
        points: [{ x: visualLastX, y: visualLastY }]
    };
    visualAnnotations.push(visualCurrentStroke);
}

function visualDraw(e) {
    if (!isVisualDrawing || !isVisualDrawingMode) return;
    
    const rect = visualCanvas.getBoundingClientRect();
    const scaleX = visualCanvas.width / rect.width;
    const scaleY = visualCanvas.height / rect.height;
    const currentX = (e.clientX - rect.left) * scaleX;
    const currentY = (e.clientY - rect.top) * scaleY;
    
    visualCtx.lineWidth = visualBrushSize;
    visualCtx.lineCap = 'round';
    visualCtx.lineJoin = 'round';
    
    if (isVisualEraserMode) {
        visualCtx.globalCompositeOperation = 'destination-out';
    } else {
        visualCtx.globalCompositeOperation = 'source-over';
        visualCtx.strokeStyle = visualCurrentColor;
    }
    
    visualCtx.beginPath();
    visualCtx.moveTo(visualLastX, visualLastY);
    visualCtx.lineTo(currentX, currentY);
    visualCtx.stroke();
    
    visualLastX = currentX;
    visualLastY = currentY;
    
    // Record point into current stroke
    if (visualCurrentStroke) {
        visualCurrentStroke.points.push({ x: currentX, y: currentY });
    }
    
    // Save drawing data (with throttling to improve performance)
    clearTimeout(window.visualSaveTimeout);
    window.visualSaveTimeout = setTimeout(saveVisualChartData, 100);
}

function stopVisualDrawing() {
    isVisualDrawing = false;
    visualCurrentStroke = null;
}

function handleVisualTouch(e) {
    e.preventDefault();
    const touch = e.touches[0];
    const mouseEvent = new MouseEvent(e.type === 'touchstart' ? 'mousedown' : 
                                    e.type === 'touchmove' ? 'mousemove' : 'mouseup', {
        clientX: touch.clientX,
        clientY: touch.clientY
    });
    visualCanvas.dispatchEvent(mouseEvent);
}

function setVisualDrawColor(color) {
    visualCurrentColor = color;
    if (isVisualDrawingMode && !isVisualEraserMode) {
        visualCtx.strokeStyle = color;
    }
    updateActiveColor(color);
}

function setVisualBrushSize(size) {
    visualBrushSize = size;
    document.getElementById('visualBrushSizeDisplay').textContent = size;
}

function clearVisualDrawing() {
    if (confirm('Are you sure you want to clear all annotations on the visual chart?')) {
        visualCtx.clearRect(0, 0, visualCanvas.width, visualCanvas.height);
        visualAnnotations = [];
        saveVisualChartData();
    }
}

function saveVisualChartData() {
    if (!visualCanvas || !visualCtx) return;
    
    // Helper: compress strokes (round coords, dedupe, downsample)
    function simplifyStrokes(strokes) {
        const simplified = [];
        for (const s of (strokes || [])) {
            if (!s || !Array.isArray(s.points) || s.points.length === 0) continue;
            const points = s.points;
            const deduped = [];
            let lastX = null, lastY = null;
            for (let i = 0; i < points.length; i++) {
                // Downsample: keep every 2nd point, but always keep first/last
                if (i !== 0 && i !== points.length - 1 && (i % 2 === 1)) continue;
                const rx = Math.round(points[i].x);
                const ry = Math.round(points[i].y);
                if (lastX === rx && lastY === ry) continue; // drop duplicates
                deduped.push({ x: rx, y: ry });
                lastX = rx; lastY = ry;
            }
            if (deduped.length === 0) continue;
            simplified.push({
                tool: s.tool === 'eraser' ? 'eraser' : 'pen',
                color: s.color || '#ff0000',
                size: Number(s.size) || 2,
                points: deduped
            });
        }
        return simplified;
    }

    // Save annotations state as JSON instead of image data URL (with compression)
    const backgroundImage = document.getElementById('visualDentalChart');
    let bg = backgroundImage ? backgroundImage.getAttribute('src') : null;
    try {
        if (bg && bg.startsWith(window.location.origin)) {
            bg = bg.substring(window.location.origin.length); // make relative
        }
        // Also handle cases where it might be stored as absolute URL
        if (bg && bg.startsWith('http://localhost:8080/')) {
            bg = bg.replace('http://localhost:8080', ''); // make relative
        }
        console.log('🖼️ Background image path:', bg);
    } catch (e) {
        console.log('❌ Error processing background path:', e);
    }

    const visualChartState = {
        version: 1,
        background: bg,
        width: visualCanvas.width,
        height: visualCanvas.height,
        strokes: simplifyStrokes(visualAnnotations)
    };
    const json = JSON.stringify(visualChartState);
    console.log('💾 Saving visual chart as JSON:', json.substring(0, 200) + '...');
    const hiddenInput = document.getElementById('visualChartData');
    if (hiddenInput) {
        hiddenInput.value = json;
        console.log('✅ Visual chart JSON saved to hidden input, length:', json.length);
    } else {
        console.error('❌ Hidden input for visualChartData not found!');
    }
}

// Load existing visual chart data onto canvas
function loadExistingVisualChartData() {
    if (!visualCanvas || !visualCtx) return;
    
    const hiddenInput = document.getElementById('visualChartData');
    if (!hiddenInput || !hiddenInput.value) {
        console.log('No existing visual chart data found');
        return;
    }
    
    const existingData = hiddenInput.value;
    console.log('Attempting to load visual chart data, length:', existingData.length);
    
    if (existingData && existingData.trim().startsWith('{')) {
        // JSON format: restore strokes
        try {
            const state = JSON.parse(existingData);
            visualAnnotations = Array.isArray(state.strokes) ? state.strokes : [];

            // Redraw strokes onto overlay canvas
            function drawStrokes(ctx, strokes) {
                for (const s of strokes) {
                    if (!s || !Array.isArray(s.points) || s.points.length === 0) continue;
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
                }
            }

            // Clear and redraw
            visualCtx.clearRect(0, 0, visualCanvas.width, visualCanvas.height);
            drawStrokes(visualCtx, visualAnnotations);
        } catch (err) {
            console.warn('Failed to parse visual chart JSON state:', err);
        }
    } else if (existingData && existingData.startsWith('data:image/')) {
        const img = new Image();
        img.onload = function() {
            // Clear canvas first
            visualCtx.clearRect(0, 0, visualCanvas.width, visualCanvas.height);
            
            // Get the background image
            const backgroundImage = document.getElementById('visualDentalChart');
            
            if (backgroundImage && backgroundImage.complete) {
                // Create temporary canvases to extract just the drawing data
                const tempCanvas1 = document.createElement('canvas');
                const tempCanvas2 = document.createElement('canvas');
                tempCanvas1.width = tempCanvas2.width = visualCanvas.width;
                tempCanvas1.height = tempCanvas2.height = visualCanvas.height;
                const tempCtx1 = tempCanvas1.getContext('2d');
                const tempCtx2 = tempCanvas2.getContext('2d');
                
                // Draw the composite image (background + drawings)
                tempCtx1.drawImage(img, 0, 0, tempCanvas1.width, tempCanvas1.height);
                
                // Draw just the background
                tempCtx2.drawImage(backgroundImage, 0, 0, tempCanvas2.width, tempCanvas2.height);
                
                // Get image data for both
                const compositeData = tempCtx1.getImageData(0, 0, tempCanvas1.width, tempCanvas1.height);
                const backgroundData = tempCtx2.getImageData(0, 0, tempCanvas2.width, tempCanvas2.height);
                
                // Extract the drawing by finding differences
                const drawingData = tempCtx1.createImageData(tempCanvas1.width, tempCanvas1.height);
                
                for (let i = 0; i < compositeData.data.length; i += 4) {
                    const compR = compositeData.data[i];
                    const compG = compositeData.data[i + 1];
                    const compB = compositeData.data[i + 2];
                    const compA = compositeData.data[i + 3];
                    
                    const bgR = backgroundData.data[i];
                    const bgG = backgroundData.data[i + 1];
                    const bgB = backgroundData.data[i + 2];
                    const bgA = backgroundData.data[i + 3];
                    
                    // If pixel is significantly different from background, it's likely a drawing
                    const threshold = 30;
                    const rDiff = Math.abs(compR - bgR);
                    const gDiff = Math.abs(compG - bgG);
                    const bDiff = Math.abs(compB - bgB);
                    
                    if (rDiff > threshold || gDiff > threshold || bDiff > threshold) {
                        // This pixel is part of the drawing
                        drawingData.data[i] = compR;
                        drawingData.data[i + 1] = compG;
                        drawingData.data[i + 2] = compB;
                        drawingData.data[i + 3] = compA;
                    } else {
                        // This pixel is background, make it transparent
                        drawingData.data[i] = 0;
                        drawingData.data[i + 1] = 0;
                        drawingData.data[i + 2] = 0;
                        drawingData.data[i + 3] = 0;
                    }
                }
                
                // Put the extracted drawing data onto the canvas
                visualCtx.putImageData(drawingData, 0, 0);
                console.log('Successfully extracted and loaded drawing data onto canvas');
            } else {
                // Fallback: just draw the composite image (this will include background)
                visualCtx.drawImage(img, 0, 0, visualCanvas.width, visualCanvas.height);
                console.log('Loaded composite image (background + drawings)');
            }
        };
        img.onerror = function() {
            console.warn('Failed to load existing visual chart data');
        };
        img.src = existingData;
    } else {
        console.log('Visual chart data not found or unrecognized format');
    }
}

// Disable visual drawing mode when clicking on regular dental chart
function selectTooth(toothNumber) {
    // Show tooth information popup
    showToothInfoPopup(toothNumber);
    
    // Disable visual drawing mode when interacting with the regular chart
    isVisualDrawingMode = false;
    isVisualEraserMode = false;
    if (visualCanvas) {
        visualCanvas.classList.remove('active');
        visualCanvas.style.pointerEvents = 'none';
        visualCanvas.style.cursor = 'default';
    }
    
    // Update button states
    updateActiveTool(null);
    
    // Continue with original tooth selection logic
    originalSelectTooth(toothNumber);
}

// Store original selectTooth function
const originalSelectTooth = window.selectTooth || function(toothNumber) {
    // Close all other tooth menus
    const allMenus = document.querySelectorAll('[id^="tooth-menu-"]');
    allMenus.forEach(menu => {
        if (menu.id !== `tooth-menu-${toothNumber}`) {
            menu.classList.add('hidden');
        }
    });
    
    // Toggle the clicked tooth menu
    const menu = document.getElementById(`tooth-menu-${toothNumber}`);
    if (menu) {
        menu.classList.toggle('hidden');
    }
    
    // Update tooth appearance based on condition
    const tooth = document.getElementById(`tooth-${toothNumber}`);
    const condition = menu.querySelector('select[name*="[condition]"]').value;
    
    // Remove existing condition classes
    tooth.className = tooth.className.replace(/bg-\w+-100 border-\w+-300/g, '');
    
    // Add new condition class
    switch (condition) {
        case 'healthy':
            tooth.classList.add('bg-green-100', 'border-green-300');
            break;
        case 'cavity':
            tooth.classList.add('bg-red-100', 'border-red-300');
            break;
        case 'filled':
            tooth.classList.add('bg-blue-100', 'border-blue-300');
            break;
        case 'crown':
            tooth.classList.add('bg-slate-100', 'border-slate-300');
            break;
    }
};

// ==================== NUMBERED TOOTH CHART POPUP FUNCTIONALITY ====================

// Universal to FDI conversion mapping
const universalToFDI = {
    1: '18', 2: '17', 3: '16', 4: '15', 5: '14', 6: '13', 7: '12', 8: '11',
    9: '21', 10: '22', 11: '23', 12: '24', 13: '25', 14: '26', 15: '27', 16: '28',
    17: '48', 18: '47', 19: '46', 20: '45', 21: '44', 22: '43', 23: '42', 24: '41',
    25: '31', 26: '32', 27: '33', 28: '34', 29: '35', 30: '36', 31: '37', 32: '38'
};

// Tooth information database for Universal numbering
const universalToothInfo = {
    1: { name: 'Upper Right Third Molar', type: 'Wisdom Tooth', quadrant: 'Upper Right (Q1)' },
    2: { name: 'Upper Right Second Molar', type: 'Molar', quadrant: 'Upper Right (Q1)' },
    3: { name: 'Upper Right First Molar', type: 'Molar', quadrant: 'Upper Right (Q1)' },
    4: { name: 'Upper Right Second Premolar', type: 'Premolar', quadrant: 'Upper Right (Q1)' },
    5: { name: 'Upper Right First Premolar', type: 'Premolar', quadrant: 'Upper Right (Q1)' },
    6: { name: 'Upper Right Canine', type: 'Canine', quadrant: 'Upper Right (Q1)' },
    7: { name: 'Upper Right Lateral Incisor', type: 'Incisor', quadrant: 'Upper Right (Q1)' },
    8: { name: 'Upper Right Central Incisor', type: 'Incisor', quadrant: 'Upper Right (Q1)' },
    9: { name: 'Upper Left Central Incisor', type: 'Incisor', quadrant: 'Upper Left (Q2)' },
    10: { name: 'Upper Left Lateral Incisor', type: 'Incisor', quadrant: 'Upper Left (Q2)' },
    11: { name: 'Upper Left Canine', type: 'Canine', quadrant: 'Upper Left (Q2)' },
    12: { name: 'Upper Left First Premolar', type: 'Premolar', quadrant: 'Upper Left (Q2)' },
    13: { name: 'Upper Left Second Premolar', type: 'Premolar', quadrant: 'Upper Left (Q2)' },
    14: { name: 'Upper Left First Molar', type: 'Molar', quadrant: 'Upper Left (Q2)' },
    15: { name: 'Upper Left Second Molar', type: 'Molar', quadrant: 'Upper Left (Q2)' },
    16: { name: 'Upper Left Third Molar', type: 'Wisdom Tooth', quadrant: 'Upper Left (Q2)' },
    17: { name: 'Lower Right Third Molar', type: 'Wisdom Tooth', quadrant: 'Lower Right (Q4)' },
    18: { name: 'Lower Right Second Molar', type: 'Molar', quadrant: 'Lower Right (Q4)' },
    19: { name: 'Lower Right First Molar', type: 'Molar', quadrant: 'Lower Right (Q4)' },
    20: { name: 'Lower Right Second Premolar', type: 'Premolar', quadrant: 'Lower Right (Q4)' },
    21: { name: 'Lower Right First Premolar', type: 'Premolar', quadrant: 'Lower Right (Q4)' },
    22: { name: 'Lower Right Canine', type: 'Canine', quadrant: 'Lower Right (Q4)' },
    23: { name: 'Lower Right Lateral Incisor', type: 'Incisor', quadrant: 'Lower Right (Q4)' },
    24: { name: 'Lower Right Central Incisor', type: 'Incisor', quadrant: 'Lower Right (Q4)' },
    25: { name: 'Lower Left Central Incisor', type: 'Incisor', quadrant: 'Lower Left (Q3)' },
    26: { name: 'Lower Left Lateral Incisor', type: 'Incisor', quadrant: 'Lower Left (Q3)' },
    27: { name: 'Lower Left Canine', type: 'Canine', quadrant: 'Lower Left (Q3)' },
    28: { name: 'Lower Left First Premolar', type: 'Premolar', quadrant: 'Lower Left (Q3)' },
    29: { name: 'Lower Left Second Premolar', type: 'Premolar', quadrant: 'Lower Left (Q3)' },
    30: { name: 'Lower Left First Molar', type: 'Molar', quadrant: 'Lower Left (Q3)' },
    31: { name: 'Lower Left Second Molar', type: 'Molar', quadrant: 'Lower Left (Q3)' },
    32: { name: 'Lower Left Third Molar', type: 'Wisdom Tooth', quadrant: 'Lower Left (Q3)' }
};

// Show tooth information popup for numbered teeth
function showToothInfoPopup(toothNumber) {
    const info = universalToothInfo[toothNumber];
    if (!info) return;

    // Create popup if it doesn't exist
    let popup = document.getElementById('toothInfoPopup');
    if (!popup) {
        popup = document.createElement('div');
        popup.id = 'toothInfoPopup';
        popup.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        popup.innerHTML = `
            <div class="bg-white rounded-lg p-6 max-w-md mx-4 shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900" id="popupToothTitle"></h3>
                    <button onclick="closeToothInfoPopup()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <!-- Condition Section -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Condition:</label>
                        <select id="popupCondition" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="healthy">Healthy</option>
                            <option value="cavity">Cavity</option>
                            <option value="filled">Filled</option>
                            <option value="crown">Crown</option>
                            <option value="missing">Missing</option>
                            <option value="root_canal">Root Canal</option>
                        </select>
                    </div>

                    
                    <!-- Service/Procedure Section (no 'Add' button; saved with Save) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Procedure/Service:</label>
                        <div class="space-y-2">
                            <select id="popupService" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Select procedure/service</option>
                                <!-- Options will be loaded dynamically -->
                            </select>
                        </div>
                    </div>
                    
                    <!-- Current Services for this Tooth -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Services for this Tooth:</label>
                        <div id="popupToothServices" class="space-y-2 max-h-32 overflow-y-auto">
                            <!-- Services will be loaded here -->
                        </div>
                    </div>
                    
                    <!-- Notes Section -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes:</label>
                        <textarea id="popupNotes" rows="3" placeholder="Additional notes..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex space-x-3 pt-4">
                        <button onclick="saveToothData()" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md font-medium transition-colors">
                            Save
                        </button>
                        <button onclick="closeToothInfoPopup()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md font-medium transition-colors">
                            Close
                        </button>
                    </div>
                </div>
                
                <!-- 3D Model Color Legend -->
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">3D Model Color Legend:</h4>
                    <div class="flex flex-wrap gap-2 text-xs">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 rounded mr-1"></div>
                            <span>Healthy</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-red-500 rounded mr-1"></div>
                            <span>Cavity</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-blue-500 rounded mr-1"></div>
                            <span>Filled</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-yellow-500 rounded mr-1"></div>
                            <span>Crown</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-black rounded mr-1"></div>
                            <span>Missing</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(popup);
    }

    // Update popup title with proper format
    const toothType = info.name.split(' ').pop(); // Get the last word (e.g., "Incisor")
    document.getElementById('popupToothTitle').textContent = `${toothType} - Tooth #${toothNumber}`;

    // Load existing data if available
    const existingMenu = document.getElementById(`tooth-menu-${toothNumber}`);
    if (existingMenu) {
        const conditionSelect = existingMenu.querySelector('select[name*="[condition]"]');
        const notesTextarea = existingMenu.querySelector('textarea[name*="[notes]"]');
        
        if (conditionSelect) document.getElementById('popupCondition').value = conditionSelect.value;
        if (notesTextarea) document.getElementById('popupNotes').value = notesTextarea.value;
    }

    // Store current tooth number for saving
    popup.setAttribute('data-tooth-number', toothNumber);

    // Load available services
    loadAvailableServices();
    
    // Load existing services for this tooth
    loadToothServices(toothNumber);

    // Show popup
    popup.classList.remove('hidden');
    popup.style.display = 'flex';
}

// Save tooth data from popup
function saveToothData() {
    const popup = document.getElementById('toothInfoPopup');
    const toothNumber = popup.getAttribute('data-tooth-number');
    
    const condition = document.getElementById('popupCondition').value;
    const notes = document.getElementById('popupNotes').value;
    const serviceId = document.getElementById('popupService') ? document.getElementById('popupService').value : '';
    const surface = (window.currentVisualHighlightMeta && window.currentVisualHighlightMeta.surface) || '';
    
    // Update the corresponding form fields
    const menu = document.getElementById(`tooth-menu-${toothNumber}`);
    if (menu) {
        const conditionSelect = menu.querySelector('select[name*="[condition]"]');
        const notesTextarea = menu.querySelector('textarea[name*="[notes]"]');
        const serviceInput = menu.querySelector('input[name*="[service_id]"]');
        const surfaceInput = menu.querySelector('input[name*="[surface]"]');
        
        if (conditionSelect) conditionSelect.value = condition;
        if (notesTextarea) notesTextarea.value = notes;
        if (serviceInput) serviceInput.value = serviceId || '';
        if (surfaceInput) surfaceInput.value = surface || '';
    }
    
    // Update tooth appearance
    const tooth = document.getElementById(`tooth-${toothNumber}`);
    if (tooth) {
        // Remove existing condition classes
        tooth.className = tooth.className.replace(/bg-\w+-100 border-\w+-300/g, '');
        
        // Add new condition class
        switch (condition) {
            case 'healthy':
                tooth.classList.add('bg-green-100', 'border-green-300');
                break;
            case 'cavity':
                tooth.classList.add('bg-red-100', 'border-red-300');
                break;
            case 'filled':
                tooth.classList.add('bg-blue-100', 'border-blue-300');
                break;
            case 'crown':
                tooth.classList.add('bg-yellow-100', 'border-yellow-300');
                break;
            case 'missing':
                tooth.classList.add('bg-gray-100', 'border-gray-300');
                break;
            case 'root_canal':
                tooth.classList.add('bg-purple-100', 'border-purple-300');
                break;
        }
    }
    
    // Close popup
    closeToothInfoPopup();
    
    // Show success message
    const successMsg = document.createElement('div');
    successMsg.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50';
    successMsg.textContent = `Tooth #${toothNumber} updated successfully!`;
    document.body.appendChild(successMsg);
    
    setTimeout(() => {
        successMsg.remove();
    }, 3000);
}

// Close tooth information popup
function closeToothInfoPopup() {
    const popup = document.getElementById('toothInfoPopup');
    if (popup) {
        popup.style.display = 'none';
    }
}

// Close popup when clicking outside
document.addEventListener('click', function(event) {
    const popup = document.getElementById('toothInfoPopup');
    if (popup && event.target === popup) {
        closeToothInfoPopup();
    }
});

// ==================== END NUMBERED TOOTH CHART POPUP FUNCTIONALITY ====================

// ==================== SERVICE MANAGEMENT FUNCTIONS ====================

// Load available services for dropdown
async function loadAvailableServices() {
    try {
        const response = await fetch('<?= base_url() ?>/checkup/services/all', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (response.ok) {
            const payload = await response.json();
            const services = Array.isArray(payload) ? payload : (payload.services || []);
            const serviceSelect = document.getElementById('popupService');
            
            // Clear existing options except the first one
            serviceSelect.innerHTML = '<option value="">Select procedure/service</option>';
            
            // Add services to dropdown
            services.forEach(service => {
                const option = document.createElement('option');
                option.value = service.id;
                const sName = service.name || service.service_name || service.description || `Service #${service.id}`;
                const sPrice = parseFloat(service.price ?? service.service_price ?? 0).toFixed(2);
                option.textContent = `${sName} - $${sPrice}`;
                serviceSelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading services:', error);
    }
}

// Load existing services for a specific tooth
async function loadToothServices(toothNumber) {
    try {
        const appointmentIdElement = document.querySelector('input[name="appointment_id"]');
        if (!appointmentIdElement) {
            console.error('Appointment ID not found');
            return;
        }
        
        const appointmentId = appointmentIdElement.value;
        const response = await fetch(`<?= base_url() ?>/checkup/${appointmentId}/services?tooth_number=${toothNumber}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (response.ok) {
            const services = await response.json();
            const servicesContainer = document.getElementById('popupToothServices');
            
            // Clear existing services
            servicesContainer.innerHTML = '';
            
            if (services.length === 0) {
                servicesContainer.innerHTML = '<p class="text-gray-500 text-sm">No services added for this tooth</p>';
            } else {
                services.forEach(service => {
                    const removeId = service.appointment_service_id ?? service.id;
                    const name = service.name || service.service_name || service.description || `Service #${service.service_id || service.id || ''}`;
                    const price = parseFloat(service.price ?? service.service_price ?? 0).toFixed(2);
                    const toothFdi = universalToFdi(service.tooth_number ?? service.toothNumber ?? '');
                    const serviceDiv = document.createElement('div');
                    serviceDiv.className = 'flex justify-between items-center p-2 bg-gray-50 rounded border';
                    serviceDiv.innerHTML = `
                        <div class=\"flex-1\">
                            <span class=\"font-medium\">${name}</span>
                            ${toothFdi ? `<span class=\\\"text-gray-600 text-sm\\\"> (Tooth ${toothFdi}${service.surface ? ' - ' + service.surface : ''})</span>` : (service.surface ? `<span class=\\\"text-gray-600 text-sm\\\"> (${service.surface})</span>` : '')}
                            <div class=\"text-green-600 font-medium\">$${price}</div>
                        </div>
                        <button onclick=\"removeToothService(${removeId})\" class=\"text-red-500 hover:text-red-700 p-1\">
                            <svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                                <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1 1h-4a1 1 0 00-1 1v3M4 7h16\"></path>
                            </svg>
                        </button>
                    `;
                    servicesContainer.appendChild(serviceDiv);
                });
            }
        }
    } catch (error) {
        console.error('Error loading tooth services:', error);
    }
}

// [removed] addServiceToAppointment: replaced by saving with form submit
/* async function addServiceToAppointment() {
    const popup = document.getElementById('toothInfoPopup');
    const toothNumber = popup.getAttribute('data-tooth-number');
    const serviceId = document.getElementById('popupService').value;
    
    if (!serviceId) {
        alert('Please select a service/procedure');
        return;
    }
    
    const appointmentIdElement = document.querySelector('input[name="appointment_id"]');
    if (!appointmentIdElement) {
        console.error('Appointment ID not found');
        alert('Error: Appointment ID not found');
        return;
    }
    
    const appointmentId = appointmentIdElement.value;
    
    try {
        const response = await fetch(`<?= base_url() ?>/checkup/${appointmentId}/services`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                service_id: serviceId,
                tooth_number: toothNumber,
                surface: null,
                notes: `Service added for tooth #${toothNumber}`
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Reset service dropdown
            document.getElementById('popupService').value = '';
            
            // Reload services for this tooth
            loadToothServices(toothNumber);
            
            // Show success message
            showSuccessMessage(`Service added to tooth #${toothNumber} successfully!`);
        } else {
            alert('Error adding service: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error adding service:', error);
        alert('Error adding service');
    }
} */

// Remove service from appointment
async function removeToothService(serviceId) {
    if (!confirm('Are you sure you want to remove this service?')) {
        return;
    }
    
    try {
        const appointmentIdElement = document.querySelector('input[name="appointment_id"]');
        if (!appointmentIdElement) {
            console.error('Appointment ID not found');
            alert('Error: Appointment ID not found');
            return;
        }
        
        const appointmentId = appointmentIdElement.value;
        const response = await fetch(`<?= base_url() ?>/checkup/${appointmentId}/services/${serviceId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            const popup = document.getElementById('toothInfoPopup');
            const toothNumber = popup.getAttribute('data-tooth-number');
            
            // Reload services for this tooth
            loadToothServices(toothNumber);
            
            showSuccessMessage('Service removed successfully!');
        } else {
            alert('Error removing service: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error removing service:', error);
        alert('Error removing service');
    }
}

// Show success message
function showSuccessMessage(message) {
    const successMsg = document.createElement('div');
    successMsg.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50';
    successMsg.textContent = message;
    document.body.appendChild(successMsg);
    
    setTimeout(() => {
        successMsg.remove();
    }, 3000);
}

// ==================== END SERVICE MANAGEMENT FUNCTIONS ====================

// ==================== VISUAL DENTAL CHART POPUP FUNCTIONALITY ====================

// Tooth information for visual chart (FDI notation)
const toothInfo = {
    '18': { name: 'Upper Right Third Molar', type: 'Wisdom Tooth', quadrant: 'Upper Right (Q1)' },
    '17': { name: 'Upper Right Second Molar', type: 'Molar', quadrant: 'Upper Right (Q1)' },
    '16': { name: 'Upper Right First Molar', type: 'Molar', quadrant: 'Upper Right (Q1)' },
    '15': { name: 'Upper Right Second Premolar', type: 'Premolar', quadrant: 'Upper Right (Q1)' },
    '14': { name: 'Upper Right First Premolar', type: 'Premolar', quadrant: 'Upper Right (Q1)' },
    '13': { name: 'Upper Right Canine', type: 'Canine', quadrant: 'Upper Right (Q1)' },
    '12': { name: 'Upper Right Lateral Incisor', type: 'Incisor', quadrant: 'Upper Right (Q1)' },
    '11': { name: 'Upper Right Central Incisor', type: 'Incisor', quadrant: 'Upper Right (Q1)' },
    '21': { name: 'Upper Left Central Incisor', type: 'Incisor', quadrant: 'Upper Left (Q2)' },
    '22': { name: 'Upper Left Lateral Incisor', type: 'Incisor', quadrant: 'Upper Left (Q2)' },
    '23': { name: 'Upper Left Canine', type: 'Canine', quadrant: 'Upper Left (Q2)' },
    '24': { name: 'Upper Left First Premolar', type: 'Premolar', quadrant: 'Upper Left (Q2)' },
    '25': { name: 'Upper Left Second Premolar', type: 'Premolar', quadrant: 'Upper Left (Q2)' },
    '26': { name: 'Upper Left First Molar', type: 'Molar', quadrant: 'Upper Left (Q2)' },
    '27': { name: 'Upper Left Second Molar', type: 'Molar', quadrant: 'Upper Left (Q2)' },
    '28': { name: 'Upper Left Third Molar', type: 'Wisdom Tooth', quadrant: 'Upper Left (Q2)' },
    '38': { name: 'Lower Left Third Molar', type: 'Wisdom Tooth', quadrant: 'Lower Left (Q3)' },
    '37': { name: 'Lower Left Second Molar', type: 'Molar', quadrant: 'Lower Left (Q3)' },
    '36': { name: 'Lower Left First Molar', type: 'Molar', quadrant: 'Lower Left (Q3)' },
    '35': { name: 'Lower Left Second Premolar', type: 'Premolar', quadrant: 'Lower Left (Q3)' },
    '34': { name: 'Lower Left First Premolar', type: 'Premolar', quadrant: 'Lower Left (Q3)' },
    '33': { name: 'Lower Left Canine', type: 'Canine', quadrant: 'Lower Left (Q3)' },
    '32': { name: 'Lower Left Lateral Incisor', type: 'Incisor', quadrant: 'Lower Left (Q3)' },
    '31': { name: 'Lower Left Central Incisor', type: 'Incisor', quadrant: 'Lower Left (Q3)' },
    '41': { name: 'Lower Right Central Incisor', type: 'Incisor', quadrant: 'Lower Right (Q4)' },
    '42': { name: 'Lower Right Lateral Incisor', type: 'Incisor', quadrant: 'Lower Right (Q4)' },
    '43': { name: 'Lower Right Canine', type: 'Canine', quadrant: 'Lower Right (Q4)' },
    '44': { name: 'Lower Right First Premolar', type: 'Premolar', quadrant: 'Lower Right (Q4)' },
    '45': { name: 'Lower Right Second Premolar', type: 'Premolar', quadrant: 'Lower Right (Q4)' },
    '46': { name: 'Lower Right First Molar', type: 'Molar', quadrant: 'Lower Right (Q4)' },
    '47': { name: 'Lower Right Second Molar', type: 'Molar', quadrant: 'Lower Right (Q4)' },
    '48': { name: 'Lower Right Third Molar', type: 'Wisdom Tooth', quadrant: 'Lower Right (Q4)' }
};

// Variable to track current visual chart highlight and metadata
let currentVisualHighlight = null;
let currentVisualHighlightMeta = null; // { coords, toothNumberFDI, surface }

// Handle tooth click for visual chart
function handleToothClick(event, toothNumber, surface) {
    const coords = event.target.getAttribute('coords');
    
    // Show tooth info popup
    showVisualToothInfo(toothNumber, surface, event);
    
    // Highlight the tooth
    if (coords) {
        const uni = fdiToUniversal(toothNumber);
        let condition = '';
        if (uni) {
            const formCondition = document.querySelector(`select[name="dental_chart[${uni}][condition]"]`);
            condition = formCondition ? formCondition.value : '';
        }
        highlightVisualTooth(coords, toothNumber, surface, condition);
        currentVisualHighlightMeta = { coords, toothNumberFDI: toothNumber, surface };
    }
    
    // Sync with 3D viewer (map FDI -> Universal and focus)
    try {
        if (window.patientCheckup && window.patientCheckup.dental3DViewer) {
            const worldCenter = focus3DFrom2D(toothNumber, surface);
            // Also open the 3D treatment popup with surface context
            const uni = fdiToUniversal(toothNumber);
            if (uni) {
                const viewer = window.patientCheckup.dental3DViewer;
                const toothName = viewer.getToothName(uni) || `Tooth #${uni}`;
                const pos = worldCenter || new THREE.Vector3(0, 0, 0);
                window.patientCheckup.showTreatmentPopup(uni, pos, { clientX: 0, clientY: 0 }, { toothName }, { surface });
            }
        }
    } catch (err) {
        console.warn('3D sync failed:', err);
    }
    
    return false;
}

// Show tooth popup for visual chart (same UI as 3D popup)
function showVisualToothInfo(toothNumber, surface, clickEvent) {
    const info = toothInfo[toothNumber];
    if (!info) return;
    // Create anchored popup (reuse 3D treatment popup style) inside image container
    const container = document.querySelector('.image-container-checkup');
    if (!container) return;
    let popup = document.getElementById('visualTreatmentPopup');
    if (!popup) {
        popup = document.createElement('div');
        popup.id = 'visualTreatmentPopup';
        popup.className = 'treatment-popup';
        popup.style.display = 'none';
        popup.innerHTML = `
            <div class="treatment-popup-header">
                <span class="treatment-popup-title" id="visualToothTitle">Tooth Information</span>
                <button type="button" class="treatment-popup-close" onclick="closeVisualToothInfoPopup()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="treatment-popup-content">
                <div class="treatment-popup-icon"><i class="fas fa-tooth"></i></div>
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Tooth (FDI)</label>
                            <input id="visualToothNumber" class="w-full px-2 py-1.5 border-2 border-gray-200 rounded-md text-xs bg-gray-50" readonly />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Surface</label>
                            <select id="visualToothSurface" class="w-full px-2 py-1.5 border-2 border-gray-200 rounded-md text-xs">
                                <option value="">None</option>
                                <option value="Crown">Crown</option>
                                <option value="Middle">Middle</option>
                                <option value="Root">Root</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Condition</label>
                            <select id="visualToothCondition" class="w-full px-2 py-1.5 border-2 border-gray-200 rounded-md text-xs">
                                <option value="">Select condition</option>
                                <option value="healthy">Healthy</option>
                                <option value="cavity">Cavity</option>
                                <option value="filled">Filled</option>
                                <option value="crown">Crown</option>
                                <option value="missing">Missing</option>
                                <option value="root_canal">Root Canal</option>
                                <option value="extraction_needed">Extraction Needed</option>
                            </select>
                        </div>
                        <div></div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Notes</label>
                        <textarea id="visualToothNotes" rows="2" class="w-full px-2 py-1.5 border-2 border-gray-200 rounded-md text-xs" placeholder="Additional notes..."></textarea>
                    </div>
                    
                    <!-- Service/Procedure Section -->
                    <div class="border-t pt-3 mt-3">
                        <label class="block text-xs font-medium text-gray-700 mb-2">Procedure/Service:</label>
                        <div class="space-y-2">
                            <select id="visualPopupService" class="w-full px-2 py-1.5 border-2 border-gray-200 rounded-md text-xs">
                                <option value="">Select procedure/service</option>
                                <!-- Options will be loaded dynamically -->
                            </select>
                        </div>
                    </div>
                    
                    <!-- Current Services for this Tooth -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-2">Services for this Tooth:</label>
                        <div id="visualPopupToothServices" class="space-y-1 max-h-24 overflow-y-auto">
                            <!-- Services will be loaded here -->
                        </div>
                    </div>
                    
                    <div class="flex gap-2 pt-1">
                        <button onclick="saveVisualToothData()" class="flex-1 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-3 py-2 rounded-md text-xs font-semibold">Save</button>
                        <button onclick="closeVisualToothInfoPopup()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-md border border-gray-200 text-xs font-semibold">Close</button>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(popup);
    }

    // Update popup content
    document.getElementById('visualToothTitle').textContent = `Tooth ${toothNumber} - ${surface}`;
    document.getElementById('visualToothNumber').value = toothNumber;
    document.getElementById('visualToothSurface').value = surface || '';

    // Preload existing data from saved chart or main form
    const uni = fdiToUniversal(toothNumber);
    const saved = (window.prevChartByTooth && uni) ? window.prevChartByTooth[uni] : null;
    const conditionSelect = document.querySelector(`select[name="dental_chart[${uni}][condition]"]`);
    const notesTextarea = document.querySelector(`textarea[name="dental_chart[${uni}][notes]"]`);
    document.getElementById('visualToothCondition').value = saved?.condition || (conditionSelect ? conditionSelect.value : '');
    document.getElementById('visualToothNotes').value = saved?.notes || (notesTextarea ? notesTextarea.value : '');

    // Live update highlight color on condition change
    const condEl = document.getElementById('visualToothCondition');
    condEl.onchange = () => {
        const newCond = condEl.value;
        const coords = currentVisualHighlightMeta?.coords;
        if (coords) highlightVisualTooth(coords, toothNumber, surface, newCond);
    };

    // Load available services
    loadVisualAvailableServices();
    
    // Load existing services for this tooth (convert FDI to Universal for consistency)
    const universalNumber = fdiToUniversal(toothNumber);
    if (universalNumber) {
        loadVisualToothServices(universalNumber);
    }

    // Show and position near click within the image container
    popup.style.display = 'block';
    positionVisualPopup(popup, clickEvent);
}

function saveVisualToothData() {
    const toothNumberFDI = document.getElementById('visualToothNumber').value;
    const surface = document.getElementById('visualToothSurface').value;
    const condition = document.getElementById('visualToothCondition').value;
    const notes = document.getElementById('visualToothNotes').value;
    const serviceIdEl = document.getElementById('visualPopupService');
    const serviceId = serviceIdEl ? serviceIdEl.value : '';

    console.log('=== DEBUGGING SAVE VISUAL TOOTH DATA ===');
    console.log('FDI Tooth Number:', toothNumberFDI);
    console.log('Surface:', surface);
    console.log('Condition:', condition);
    console.log('Notes:', notes);
    console.log('Service ID:', serviceId);

    // Map to Universal for the main form fields that drive 3D
    const uni = fdiToUniversal(toothNumberFDI);
    console.log('Universal Number:', uni);
    
    // Special debugging for tooth 45
    if (toothNumberFDI == '45') {
        console.log('*** SPECIAL DEBUG FOR TOOTH 45 ***');
        console.log('FDI 45 should map to Universal 29');
        console.log('Actual mapping result:', uni);
    }
    
    if (uni) {
        const formCondition = document.querySelector(`select[name="dental_chart[${uni}][condition]"]`);
        const formNotes = document.querySelector(`textarea[name="dental_chart[${uni}][notes]"]`);
        const formService = document.getElementById(`tooth-${uni}-service`);
        const formSurface = document.getElementById(`tooth-${uni}-surface`);
        
        console.log('Form elements found:');
        console.log('- Condition select:', formCondition ? 'YES' : 'NO');
        console.log('- Notes textarea:', formNotes ? 'YES' : 'NO'); 
        console.log('- Service input:', formService ? 'YES' : 'NO');
        console.log('- Surface input:', formSurface ? 'YES' : 'NO');
        
        if (formCondition) {
            formCondition.value = condition;
            console.log('Set condition to:', condition);
        }
        
        if (formNotes) {
            formNotes.value = notes;
            console.log('Set notes to:', notes);
        }
        
        if (formService) {
            const oldServiceValue = formService.value;
            formService.value = serviceId || '';
            console.log('Service input old value:', oldServiceValue);
            console.log('Set service_id to:', serviceId || '');
            console.log('Service input new value:', formService.value);
            console.log('Service input name attribute:', formService.name);
            console.log('Service input type:', formService.type);
            
            // Force a change event and mark as manually changed
            formService.dispatchEvent(new Event('change'));
            formService.setAttribute('data-manually-changed', 'true');
        } else {
            console.log('ERROR: Service input not found for tooth', uni);
            console.log('Looking for ID:', `tooth-${uni}-service`);
            // Check if any similar elements exist
            const allServiceInputs = document.querySelectorAll('input[name*="service_id"]');
            console.log('All service inputs found:', allServiceInputs.length);
            allServiceInputs.forEach((input, index) => {
                console.log(`Service input ${index}:`, input.name);
            });
        }
        
        if (formSurface) {
            const oldValue = formSurface.value;
            formSurface.value = surface || '';
            console.log('Surface input old value:', oldValue);
            console.log('Set surface to:', surface || '');
            console.log('Surface input new value:', formSurface.value);
            console.log('Surface input name attribute:', formSurface.name);
            console.log('Surface input type:', formSurface.type);
            
            // Force a change event to ensure the form knows it changed
            formSurface.dispatchEvent(new Event('change'));
            
            // Mark the field as manually changed to help with debugging
            formSurface.setAttribute('data-manually-changed', 'true');
        } else {
            console.log('ERROR: Surface input not found for tooth', uni);
            console.log('Looking for ID:', `tooth-${uni}-surface`);
            // Check if any similar elements exist
            const allSurfaceInputs = document.querySelectorAll('input[name*="surface"]');
            console.log('All surface inputs found:', allSurfaceInputs.length);
            allSurfaceInputs.forEach((input, index) => {
                console.log(`Surface input ${index}:`, input.name);
            });
        }

        // Update 2D appearance and 3D immediately
        if (window.patientCheckup) {
            window.patientCheckup.updateToothAppearance(uni);
            window.patientCheckup.update3DToothColor(uni);
            if (surface) window.patientCheckup.focus3DBySurface(uni, surface);
        }
    }

    // Re-apply visual highlight with the newly saved condition
    if (currentVisualHighlightMeta) {
        highlightVisualTooth(
            currentVisualHighlightMeta.coords,
            currentVisualHighlightMeta.toothNumberFDI,
            currentVisualHighlightMeta.surface,
            condition
        );
    }

    closeVisualToothInfoPopup();
    
    console.log('=== END DEBUGGING ===');
}

function closeVisualToothInfoPopup() {
    const popup = document.getElementById('visualTreatmentPopup');
    if (popup) popup.style.display = 'none';
}

// ==================== VISUAL CHART SERVICE MANAGEMENT FUNCTIONS ====================

// Load available services for visual chart dropdown
async function loadVisualAvailableServices() {
    try {
        const response = await fetch('<?= base_url() ?>/checkup/services/all', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            const serviceSelect = document.getElementById('visualPopupService');
            
            // Clear existing options except the first one
            serviceSelect.innerHTML = '<option value="">Select procedure/service</option>';
            
            // Add services to dropdown
            if (data.success && data.services) {
                data.services.forEach(service => {
                    const option = document.createElement('option');
                    option.value = service.id;
                    option.textContent = `${service.name} - $${service.price}`;
                    serviceSelect.appendChild(option);
                });
            }
        }
    } catch (error) {
        console.error('Error loading services:', error);
    }
}

// Load existing services for a specific tooth (visual chart)
async function loadVisualToothServices(toothNumber) {
    try {
        const appointmentIdElement = document.querySelector('input[name="appointment_id"]');
        if (!appointmentIdElement) {
            console.error('Appointment ID not found');
            return;
        }
        
        const appointmentId = appointmentIdElement.value;
        const response = await fetch(`<?= base_url() ?>/checkup/${appointmentId}/services?tooth_number=${toothNumber}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (response.ok) {
            const services = await response.json();
            const servicesContainer = document.getElementById('visualPopupToothServices');
            
            // Clear existing services
            servicesContainer.innerHTML = '';
            
            if (services.length === 0) {
                servicesContainer.innerHTML = '<p class="text-gray-500 text-xs">No services added for this tooth</p>';
            } else {
                services.forEach(service => {
                    const serviceDiv = document.createElement('div');
                    serviceDiv.className = 'flex justify-between items-center p-1 bg-gray-50 rounded border text-xs';
                    serviceDiv.innerHTML = `
                        <div class="flex-1">
                            <span class="font-medium">${service.name}</span>
                            ${service.surface ? `<span class="text-gray-600"> (${service.surface})</span>` : ''}
                            <div class="text-green-600 font-medium">$${service.price}</div>
                        </div>
                        <button onclick="removeVisualToothService(${service.id})" class="text-red-500 hover:text-red-700 p-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    `;
                    servicesContainer.appendChild(serviceDiv);
                });
            }
        }
    } catch (error) {
        console.error('Error loading tooth services:', error);
    }
}

// Add service to appointment for specific tooth (visual chart)
/* async function addVisualServiceToAppointment() {
    const toothNumberElement = document.getElementById('visualToothNumber');
    const serviceElement = document.getElementById('visualPopupService');
    
    if (!toothNumberElement || !serviceElement) {
        console.error('Required elements not found');
        alert('Error: Required form elements not found');
        return;
    }
    
    const toothNumberFDI = toothNumberElement.value;
    const universalNumber = fdiToUniversal(toothNumberFDI);
    const serviceId = serviceElement.value;
    
    if (!serviceId) {
        alert('Please select a service/procedure');
        return;
    }
    
    if (!toothNumberFDI) {
        alert('No tooth number selected');
        return;
    }
    
    const appointmentId = document.querySelector('input[name="appointment_id"]').value;
    
    try {
        const response = await fetch(`<?= base_url() ?>/checkup/${appointmentId}/services`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                service_id: serviceId,
                tooth_number: universalNumber,
                surface: null,
                notes: `Service added for tooth #${universalNumber} (FDI: ${toothNumberFDI})`
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Reset service dropdown
            document.getElementById('visualPopupService').value = '';
            
            // Reload services for this tooth
            loadVisualToothServices(universalNumber);
            
            // Show success message
            showSuccessMessage(`Service added to tooth #${universalNumber} successfully!`);
        } else {
            alert('Error adding service: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error adding service:', error);
        alert('Error adding service');
    }
} */

// Remove service from appointment (visual chart)
async function removeVisualToothService(serviceId) {
    if (!confirm('Are you sure you want to remove this service?')) {
        return;
    }
    
    try {
        const appointmentIdElement = document.querySelector('input[name="appointment_id"]');
        if (!appointmentIdElement) {
            console.error('Appointment ID not found');
            alert('Error: Appointment ID not found');
            return;
        }
        
        const appointmentId = appointmentIdElement.value;
        const response = await fetch(`<?= base_url() ?>/checkup/${appointmentId}/services/${serviceId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            const toothNumberFDI = document.getElementById('visualToothNumber').value;
            const universalNumber = fdiToUniversal(toothNumberFDI);
            
            // Reload services for this tooth
            loadVisualToothServices(universalNumber);
            
            showSuccessMessage('Service removed successfully!');
        } else {
            alert('Error removing service: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error removing service:', error);
        alert('Error removing service');
    }
}

// ==================== END VISUAL CHART SERVICE MANAGEMENT FUNCTIONS ====================

// Map FDI numbering (11-48) to Universal (1-32)
function fdiToUniversal(fdi) {
    fdi = parseInt(fdi, 10);
    if (fdi >= 11 && fdi <= 18) return 19 - fdi;      // 18 -> 1, 11 -> 8
    if (fdi >= 21 && fdi <= 28) return fdi - 12;      // 21 -> 9, 28 -> 16
    if (fdi >= 31 && fdi <= 38) return 55 - fdi;      // 38 -> 17, 31 -> 24
    if (fdi >= 41 && fdi <= 48) return fdi - 16;      // 41 -> 25, 48 -> 32
    return null;
}

// Map Universal (1-32) to FDI (11-48)
function universalToFdi(universal) {
    const u = parseInt(universal, 10);
    const mapped = universalToFDI[u];
    return mapped ? String(mapped) : (universal ? String(universal) : '');
}

// Focus and highlight the corresponding 3D tooth from a 2D click
function focus3DFrom2D(fdiToothNumber, surface) {
    const viewer = window.patientCheckup?.dental3DViewer;
    if (!viewer || !viewer.isLoaded) return;
    
    const uni = fdiToUniversal(fdiToothNumber);
    if (!uni) return;
    
    const indices = viewer.getToothMeshIndices(uni);
    if (!indices || indices.length === 0) return;
    
    // Highlight first mapped mesh (most models have 1 mesh per tooth)
    const meshIndex = indices[0];
    viewer.highlightTooth(meshIndex);
    
    // Compute tooth center to focus camera and overlay
    const mesh = viewer.toothMeshes[meshIndex];
    if (!mesh) return null;
    const bbox = new THREE.Box3().setFromObject(mesh);
    const center = bbox.getCenter(new THREE.Vector3());
    
    // Move camera based on surface and arch (upper vs lower via Y)
    const isUpper = center.y > 0; // per viewer’s analysis logic
    const cam = viewer.camera;
    const controls = viewer.controls;
    
    // Base offsets
    const up = isUpper ? 1 : -1;
    const crownOffset = new THREE.Vector3(0, 1.2 * up, 0.25);
    const midOffset   = new THREE.Vector3(0, 0.4 * up, 1.4);
    const rootOffset  = new THREE.Vector3(0, -1.0 * up, 0.6);
    
    let offset = midOffset; // default
    const s = (surface || '').toLowerCase();
    if (s.includes('crown') || s.includes('up')) offset = crownOffset;
    else if (s.includes('root') || s.includes('bottom')) offset = rootOffset;
    
    cam.position.set(center.x + offset.x, center.y + offset.y, center.z + offset.z);
    controls.target.copy(center);
    cam.lookAt(center);
    controls.update();
    return center;
}

// Highlight tooth on visual chart
function highlightVisualTooth(coords, toothNumber, surface, condition = '') {
    // Remove existing highlight
    if (currentVisualHighlight) {
        currentVisualHighlight.remove();
    }
    
    // The coordinates from the <area> tag are already scaled by our responsive map logic.
    // We just need to format them for the SVG polygon.
    const coordArray = coords.split(',');
    if (coordArray.length < 6) return;

    const image = document.getElementById('visualDentalChart');
    const container = image.parentElement;
    
    // Create SVG overlay for highlighting
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.style.position = 'absolute';
    svg.style.top = '0';
    svg.style.left = '0';
    svg.style.width = image.offsetWidth + 'px';
    svg.style.height = image.offsetHeight + 'px';
    svg.style.pointerEvents = 'none';
    svg.style.zIndex = '10';
    
    // Create polygon for highlight
    const polygon = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
    
    // Join the already-scaled coordinates into a string for the 'points' attribute.
    const points = coordArray.join(' ');
    
    polygon.setAttribute('points', points);
    // Color mapping based on condition to match 3D legend
    const colors = {
        healthy: { fill: 'rgba(34,197,94,0.28)', stroke: '#22c55e' },
        cavity: { fill: 'rgba(239,68,68,0.30)', stroke: '#ef4444' },
        filled: { fill: 'rgba(59,130,246,0.30)', stroke: '#3b82f6' },
        crown: { fill: 'rgba(250,204,21,0.35)', stroke: '#eab308' },
        missing: { fill: 'rgba(156,163,175,0.18)', stroke: '#9ca3af', dash: '5,3' },
        root_canal: { fill: 'rgba(249,115,22,0.30)', stroke: '#f97316' },
        extraction_needed: { fill: 'rgba(185,28,28,0.30)', stroke: '#b91c1c' },
        default: { fill: 'rgba(59,130,246,0.25)', stroke: '#3B82F6' }
    };
    const c = colors[condition] || colors.default;
    polygon.setAttribute('fill', c.fill);
    polygon.setAttribute('stroke', c.stroke);
    polygon.setAttribute('stroke-width', '2');
    if (c.dash) polygon.setAttribute('stroke-dasharray', c.dash);
    
    svg.appendChild(polygon);
    container.appendChild(svg);
    
    // Store reference for removal
    currentVisualHighlight = svg;
    
    console.log(`Visual chart: Highlighted Tooth ${toothNumber} - ${surface} (condition: ${condition || 'none'})`);
}

// Close visual popup when clicking outside
document.addEventListener('click', function(event) {
    const popup = document.getElementById('visualTreatmentPopup');
    if (!popup || popup.style.display === 'none') return;
    const container = document.querySelector('.image-container-checkup');
    const clickedInsidePopup = popup.contains(event.target);
    const clickedArea = event.target.closest('area');
    const insideContainer = container && container.contains(event.target);
    if (!clickedInsidePopup && insideContainer && !clickedArea) {
        closeVisualToothInfoPopup();
    }
});

// Position the visual popup within the image container near the click
function positionVisualPopup(popup, clickEvent) {
    const container = document.querySelector('.image-container-checkup');
    if (!container) return;
    const wrapper = container.parentElement;
    const rect = container.getBoundingClientRect();
    // Force a reflow to get correct popup size
    const pRect = popup.getBoundingClientRect();
    let x = (clickEvent?.clientX || (rect.left + rect.width / 2)) - rect.left + 12;
    let y = (clickEvent?.clientY || (rect.top + rect.height / 2)) - rect.top - (pRect.height / 2);
    const pad = 10;
    const maxX = rect.width - pRect.width - pad;
    const maxY = rect.height - pRect.height - pad;
    x = Math.max(pad, Math.min(x, maxX));
    y = Math.max(pad, Math.min(y, maxY));
    popup.style.left = `${x}px`;
    popup.style.top = `${y}px`;

    // Ensure the popup is fully visible inside the scroll wrapper viewport
    if (wrapper && wrapper.classList.contains('visual-chart-scroll')) {
        const popupRight = x + pRect.width;
        const viewportLeft = wrapper.scrollLeft;
        const viewportRight = viewportLeft + wrapper.clientWidth;
        const margin = 12;
        if (popupRight + margin > viewportRight) {
            const target = popupRight - wrapper.clientWidth + margin;
            wrapper.scrollTo({ left: Math.max(0, target), behavior: 'smooth' });
        } else if (x - margin < viewportLeft) {
            wrapper.scrollTo({ left: Math.max(0, x - margin), behavior: 'smooth' });
        }
    }
}

// ==================== END VISUAL DENTAL CHART POPUP FUNCTIONALITY ====================

// ==================== END VISUAL DENTAL CHART FUNCTIONALITY ====================

</script>

<!-- Dental Chart Image Map for Visual Chart -->
<map name="dental-map">
    <!-- Crown Surfaces -->
    <area alt="Tooth 18 Crown" title="Upper Right Third Molar Crown" href="#" coords="114,276,111,292,109,304,107,316,105,327,105,337,105,346,101,351,97,361,98,372,104,378,111,384,128,381,134,383,138,385,147,387,155,382,157,374,157,367,155,352,152,346,152,338,151,320,148,301,141,285,129,275,120,274" shape="poly" onclick="return handleToothClick(event, '18', 'Crown');">
    <area alt="Tooth 17 Crown" title="Upper Right Second Molar Crown" href="#" coords="178,260,175,268,177,276,178,284,179,290,181,300,181,308,181,317,181,325,179,331,176,337,172,344,169,356,169,362,173,370,179,374,187,377,196,375,200,370,208,376,214,380,223,381,229,375,234,367,234,354,230,343,227,333,227,327,227,318,228,308,228,301,227,291,227,283,225,275,218,265,209,256,203,249,200,250,198,261,198,269,198,278,196,285,192,290" shape="poly" onclick="return handleToothClick(event, '17', 'Crown');">
    <area alt="Tooth 16 Crown" title="Upper Right First Molar Crown" href="#" coords="243,253,241,263,241,270,244,280,245,288,249,296,252,306,254,316,255,324,254,330,254,335,248,345,247,355,248,360,250,367,257,373,264,376,270,376,275,373,279,369,280,360,284,371,288,375,294,376,302,375,307,371,311,363,314,357,314,349,311,340,307,330,306,321,307,311,308,303,309,293,309,285,308,274,305,265,298,253,292,245,290,250,293,258,294,267,294,276,294,282,293,288,287,276,277,250,269,241,265,244,265,256,265,263,265,269,264,281,262,287,256,282,251,271,249,264,245,253" shape="poly" onclick="return handleToothClick(event, '16', 'Crown');">
    <area alt="Tooth 15 Crown" title="Upper Right Second Premolar Crown" href="#" coords="342,263,342,285,340,296,337,306,337,316,337,325,337,335,337,347,334,356,328,368,328,380,340,389,349,394,361,385,370,381,368,366,361,349,363,337,363,321,361,306,358,291,349,265,342,263" shape="poly" onclick="return handleToothClick(event, '15', 'Crown');">
    <area alt="Tooth 14 Crown" title="Upper Right First Premolar Crown" href="#" coords="389,266,389,274,389,264,389,283,390,298,389,310,389,319,387,333,387,346,387,355,383,365,382,379,387,384,394,388,401,393,407,391,418,383,423,374,418,353,416,341,414,326,413,315,409,302,406,288,397,272" shape="poly" onclick="return handleToothClick(event, '14', 'Crown');">
    <area alt="Tooth 13 Crown" title="Upper Right Canine Crown" href="#" coords="442,253,444,265,444,275,445,284,445,301,444,310,444,322,440,341,444,353,444,363,438,377,435,385,435,401,442,404,456,413,466,416,473,411,483,406,488,392,485,371,476,354,474,327,468,294,457,268,450,254,444,249" shape="poly" onclick="return handleToothClick(event, '13', 'Crown');">
    <area alt="Tooth 12 Crown" title="Upper Right Lateral Incisor Crown" href="#" coords="512,283,512,297,511,312,511,324,507,336,505,348,505,362,502,377,499,388,499,398,500,408,511,415,519,417,529,417,536,417,538,398,538,384,531,358,531,343,528,326,526,312,523,298,517,286" shape="poly" onclick="return handleToothClick(event, '12', 'Crown');">
    <area alt="Tooth 11 Crown" title="Upper Right Central Incisor Crown" href="#" coords="566,274,564,285,566,295,567,304,564,321,560,333,559,350,559,372,554,381,552,390,552,398,552,410,557,417,567,417,579,419,591,417,597,415,598,395,593,371,591,345,590,326,584,305,578,288,572,276,566,274" shape="poly" onclick="return handleToothClick(event, '11', 'Crown');">
    <area alt="Tooth 21 Crown" title="Upper Left Central Incisor Crown" href="#" coords="658,274,646,285,643,300,638,309,636,321,634,331,633,343,631,355,631,365,627,376,627,391,626,405,627,415,640,419,652,417,665,415,672,412,672,391,665,367,665,352,662,333,657,315,657,290,660,274" shape="poly" onclick="return handleToothClick(event, '21', 'Crown');">
    <area alt="Tooth 22 Crown" title="Upper Left Lateral Incisor Crown" href="#" coords="707,281,701,297,700,309,696,319,695,331,693,353,693,362,688,379,686,391,684,400,688,415,693,419,703,417,720,412,725,401,724,381,719,367,719,350,713,333,713,317,713,303,712,291,712,285,707,281" shape="poly" onclick="return handleToothClick(event, '22', 'Crown');">
    <area alt="Tooth 23 Crown" title="Upper Left Canine Crown" href="#" coords="777,249,767,266,762,280,758,294,753,308,751,321,750,330,750,340,748,351,748,359,739,373,737,387,737,399,751,409,758,414,768,413,777,406,789,399,789,378,780,363,782,349,782,315,777,294,777,275,782,253,777,249" shape="poly" onclick="return handleToothClick(event, '23', 'Crown');">
    <area alt="Tooth 24 Crown" title="Upper Left First Premolar Crown" href="#" coords="827,269,823,281,817,291,815,303,811,317,811,327,810,336,810,346,810,355,803,364,803,374,801,381,810,384,801,381,815,389,823,393,832,388,841,383,842,372,839,357,839,336,837,321,834,303,834,291,835,271,837,264" shape="poly" onclick="return handleToothClick(event, '24', 'Crown');">
    <area alt="Tooth 25 Crown" title="Upper Left Second Premolar Crown" href="#" coords="880,263,868,277,865,301,861,316,861,332,861,342,861,354,856,370,854,382,877,394,892,385,897,375,887,351,887,323,882,297,882,277" shape="poly" onclick="return handleToothClick(event, '25', 'Crown');">
    <area alt="Tooth 26 Crown" title="Upper Left First Molar Crown" href="#" coords="933,248,928,249,920,263,916,278,916,289,916,308,916,316,918,327,916,335,911,347,911,358,913,366,918,373,933,378,940,373,946,359,947,371,959,376,975,368,978,349,970,328,971,309,973,301,978,287,982,273,982,254,976,265,968,282,961,289,959,263,958,242,947,249,940,266,928,287,933,248" shape="poly" onclick="return handleToothClick(event, '26', 'Crown');">
    <area alt="Tooth 27 Crown" title="Upper Left Second Molar Crown" href="#" coords="1025,248,1006,264,997,278,995,298,997,312,995,331,992,350,992,360,992,370,999,379,1009,381,1019,376,1023,360,1025,370,1033,376,1045,377,1056,364,1054,346,1043,329,1043,312,1043,298,1049,278,1045,262,1037,279,1028,288" shape="poly" onclick="return handleToothClick(event, '27', 'Crown');">
    <area alt="Tooth 28 Crown" title="Upper Left Third Molar Crown" href="#" coords="1102,272,1081,290,1074,302,1071,315,1071,334,1071,348,1066,372,1068,379,1078,386,1086,383,1099,377,1107,381,1117,383,1128,365,1123,353,1117,338,1119,329,1111,295,1111,276" shape="poly" onclick="return handleToothClick(event, '28', 'Crown');">
    
    <!-- Lower Crown Surfaces -->
    <area alt="Tooth 48 Crown" title="Lower Right Third Molar Crown" href="#" coords="124,646,124,656,122,670,122,680,119,694,117,704,115,719,108,726,108,745,119,750,131,750,148,756,160,750,165,735,160,714,155,689,146,668,132,646" shape="poly" onclick="return handleToothClick(event, '48', 'Crown');">
    <area alt="Tooth 47 Crown" title="Lower Right Second Molar Crown" href="#" coords="175,641,177,660,181,674,182,684,184,698,186,715,179,727,175,741,182,753,194,755,208,751,222,756,232,751,237,741,232,719,230,701,232,679,227,660,222,646,211,638,208,643,215,660,213,677,213,688,206,693,196,669,187,648,179,641" shape="poly" onclick="return handleToothClick(event, '47', 'Crown');">
    <area alt="Tooth 46 Crown" title="Lower Right First Molar Crown" href="#" coords="258,633,258,644,260,663,261,676,263,687,265,697,265,706,260,716,254,733,254,744,261,750,273,752,289,754,301,754,313,754,325,745,320,719,316,704,318,683,316,656,311,637,299,627,297,630,301,646,301,658,297,670,294,682,291,690,278,663,273,649,260,628" shape="poly" onclick="return handleToothClick(event, '46', 'Crown');">
    <area alt="Tooth 45 Crown" title="Lower Right Second Premolar Crown" href="#" coords="349,626,346,633,347,650,347,660,346,669,344,681,344,691,342,701,342,712,342,734,340,751,344,758,359,765,368,760,376,753,375,737,368,712,370,696,366,669,361,646,356,634,349,626" shape="poly" onclick="return handleToothClick(event, '45', 'Crown');">
    <area alt="Tooth 44 Crown" title="Lower Right First Premolar Crown" href="#" coords="402,634,404,648,401,664,401,674,401,682,395,695,395,705,395,719,397,734,390,743,392,755,402,762,409,767,414,767,421,760,428,753,423,739,419,729,419,712,419,695,418,669,414,652,407,633" shape="poly" onclick="return handleToothClick(event, '44', 'Crown');">
    <area alt="Tooth 43 Crown" title="Lower Right Canine Crown" href="#" coords="454,633,450,660,449,686,447,710,449,731,444,739,440,753,440,763,445,774,464,784,478,777,488,765,487,751,478,732,476,710,466,672,464,653,461,638" shape="poly" onclick="return handleToothClick(event, '43', 'Crown');">
    <area alt="Tooth 42 Crown" title="Lower Right Lateral Incisor Crown" href="#" coords="514,646,512,674,511,695,507,710,505,724,507,734,502,748,499,762,500,775,509,782,519,782,529,782,538,775,536,755,531,731,531,715,531,698,528,672,523,655" shape="poly" onclick="return handleToothClick(event, '42', 'Crown');">
    <area alt="Tooth 41 Crown" title="Lower Right Central Incisor Crown" href="#" coords="571,655,566,682,566,708,564,731,562,751,557,762,559,777,567,784,579,784,588,780,591,768,588,739,586,720,583,691,579,674,578,660" shape="poly" onclick="return handleToothClick(event, '41', 'Crown');">
    <area alt="Tooth 31 Crown" title="Lower Left Central Incisor Crown" href="#" coords="652,656,645,668,643,682,641,692,638,709,638,721,638,731,636,742,634,750,634,773,638,781,646,785,658,783,665,778,665,759,658,737,658,726,658,713,658,702,657,683,655,663" shape="poly" onclick="return handleToothClick(event, '31', 'Crown');">
    <area alt="Tooth 32 Crown" title="Lower Left Lateral Incisor Crown" href="#" coords="708,648,698,662,696,674,693,707,693,727,691,734,688,744,684,760,686,779,698,784,713,784,725,772,722,753,715,736,719,725,715,700,712,679,710,658" shape="poly" onclick="return handleToothClick(event, '32', 'Crown');">
    <area alt="Tooth 33 Crown" title="Lower Left Canine Crown" href="#" coords="767,632,760,646,758,658,755,676,751,694,748,709,746,730,741,742,736,752,736,764,739,771,751,778,758,786,774,776,784,771,784,761,782,749,779,740,777,719,775,692,772,652,772,639,767,632,772,639" shape="poly" onclick="return handleToothClick(event, '33', 'Crown');">
    <area alt="Tooth 34 Crown" title="Lower Left First Premolar Crown" href="#" coords="817,632,810,646,806,659,805,671,805,685,805,704,805,718,805,733,796,752,799,759,806,764,813,768,822,764,834,754,830,737,827,725,829,709,825,682,818,646,818,635" shape="poly" onclick="return handleToothClick(event, '34', 'Crown');">
    <area alt="Tooth 35 Crown" title="Lower Left Second Premolar Crown" href="#" coords="873,627,861,646,860,665,858,682,856,696,854,713,856,727,849,734,848,753,861,763,877,760,884,750,880,720,882,703,877,662,878,634" shape="poly" onclick="return handleToothClick(event, '35', 'Crown');">
    <area alt="Tooth 36 Crown" title="Lower Left First Molar Crown" href="#" coords="925,630,911,640,908,652,906,668,904,682,904,692,908,711,901,728,901,737,904,749,911,754,920,757,933,754,946,754,966,747,968,737,966,721,958,711,958,701,959,682,963,670,961,649,964,637,970,632,958,632,952,646,944,666,939,675,930,685,925,670,920,659,928,632" shape="poly" onclick="return handleToothClick(event, '36', 'Crown');">
    <area alt="Tooth 37 Crown" title="Lower Left Second Molar Crown" href="#" coords="1011,637,999,654,994,670,994,685,994,706,994,718,988,730,987,742,990,750,999,754,1011,754,1018,749,1026,752,1035,756,1043,752,1049,742,1047,726,1040,713,1040,701,1040,685,1043,663,1049,647,1045,642,1035,649,1030,664,1023,680,1016,692,1009,683,1011,651,1016,639,1011,637" shape="poly" onclick="return handleToothClick(event, '37', 'Crown');">
    <area alt="Tooth 38 Crown" title="Lower Left Third Molar Crown" href="#" coords="1088,646,1076,668,1068,694,1062,709,1061,718,1061,731,1061,740,1062,747,1074,757,1083,754,1093,749,1107,750,1116,742,1116,730,1107,711,1104,695,1100,666,1099,646" shape="poly" onclick="return handleToothClick(event, '38', 'Crown');">
    
    <!-- Middle Surfaces -->
    <area alt="Tooth 18 Middle" title="Upper Right Third Molar Middle" href="#" coords="136,393,120,396,105,405,100,415,100,429,107,443,119,453,136,456,144,448,150,439,153,417,155,407,150,398" shape="poly" onclick="return handleToothClick(event, '18', 'Middle');">
    <area alt="Tooth 17 Middle" title="Upper Right Second Molar Middle" href="#" coords="213,389,196,395,182,399,175,407,174,423,177,440,184,450,198,452,211,452,222,447,229,435,229,418,232,402,223,392" shape="poly" onclick="return handleToothClick(event, '17', 'Middle');">
    <area alt="Tooth 16 Middle" title="Upper Right First Molar Middle" href="#" coords="282,388,265,391,254,398,249,417,249,434,253,446,263,455,278,455,291,455,301,455,309,441,311,429,313,407,313,396,304,388,294,386" shape="poly" onclick="return handleToothClick(event, '16', 'Middle');">
    <area alt="Tooth 15 Middle" title="Upper Right Second Premolar Middle" href="#" coords="346,400,335,405,325,415,327,424,334,439,344,448,352,448,363,438,368,420,364,408,354,403" shape="poly" onclick="return handleToothClick(event, '15', 'Middle');">
    <area alt="Tooth 14 Middle" title="Upper Right First Premolar Middle" href="#" coords="401,401,387,407,382,419,383,429,387,441,392,448,402,453,414,448,418,431,423,419,414,405" shape="poly" onclick="return handleToothClick(event, '14', 'Middle');">
    <area alt="Tooth 24 Middle" title="Upper Left First Premolar Middle" href="#" coords="817,401,806,407,801,417,805,431,808,444,815,451,823,453,834,448,841,434,842,419,839,410,832,403" shape="poly" onclick="return handleToothClick(event, '24', 'Middle');">
    <area alt="Tooth 25 Middle" title="Upper Left Second Premolar Middle" href="#" coords="875,401,860,406,856,414,858,425,861,435,870,447,882,447,892,438,897,423,897,411,884,401" shape="poly" onclick="return handleToothClick(event, '25', 'Middle');">
    <area alt="Tooth 26 Middle" title="Upper Left First Molar Middle" href="#" coords="921,385,911,394,909,409,911,431,914,447,925,456,940,456,962,452,971,445,973,428,971,406,962,392" shape="poly" onclick="return handleToothClick(event, '26', 'Middle');">
    <area alt="Tooth 27 Middle" title="Upper Left Second Molar Middle" href="#" coords="1007,392,997,399,992,411,997,437,1002,451,1018,454,1037,454,1049,442,1050,416,1045,401" shape="poly" onclick="return handleToothClick(event, '27', 'Middle');">
    <area alt="Tooth 28 Middle" title="Upper Left Third Molar Middle" href="#" coords="1086,395,1074,399,1069,409,1071,425,1076,445,1088,456,1107,454,1121,438,1124,419,1119,404,1099,395" shape="poly" onclick="return handleToothClick(event, '28', 'Middle');">
    
    <!-- Lower Middle Surfaces -->
    <area alt="Tooth 48 Middle" title="Lower Right Third Molar Middle" href="#" coords="138,761,110,768,103,780,103,799,110,812,122,819,141,821,156,817,165,802,162,786,153,769" shape="poly" onclick="return handleToothClick(event, '48', 'Middle');">
    <area alt="Tooth 47 Middle" title="Lower Right Second Molar Middle" href="#" coords="206,764,191,766,181,771,175,785,177,804,182,819,201,823,230,823,239,811,237,789,236,772,222,765" shape="poly" onclick="return handleToothClick(event, '47', 'Middle');">
    <area alt="Tooth 46 Middle" title="Lower Right First Molar Middle" href="#" coords="304,764,284,762,270,762,263,769,256,780,258,795,261,809,270,819,287,821,315,823,325,811,323,795,318,774,315,766" shape="poly" onclick="return handleToothClick(event, '46', 'Middle');">
    <area alt="Tooth 45 Middle" title="Lower Right Second Premolar Middle" href="#" coords="364,771,347,773,339,792,340,805,349,811,364,814,376,805,378,795,371,774" shape="poly" onclick="return handleToothClick(event, '45', 'Middle');">
    <area alt="Tooth 44 Middle" title="Lower Right First Premolar Middle" href="#" coords="406,776,394,792,392,805,401,814,418,816,425,807,428,792,414,776" shape="poly" onclick="return handleToothClick(event, '44', 'Middle');">
    <area alt="Tooth 34 Middle" title="Lower Left First Premolar Middle" href="#" coords="811,776,801,785,798,802,805,812,813,819,827,812,830,802,830,790,823,780" shape="poly" onclick="return handleToothClick(event, '34', 'Middle');">
    <area alt="Tooth 35 Middle" title="Lower Left Second Premolar Middle" href="#" coords="853,772,849,779,846,789,846,796,846,803,849,808,853,811,859,814,867,815,874,814,886,805,887,796,885,788,883,779,874,773" shape="poly" onclick="return handleToothClick(event, '35', 'Middle');">
    <area alt="Tooth 36 Middle" title="Lower Left First Molar Middle" href="#" coords="909,767,905,776,903,785,900,800,899,807,901,814,905,818,909,823,918,823,925,823,932,823,941,823,949,821,959,815,964,811,966,803,969,793,968,786,966,776,960,767,952,763,944,762,936,764,925,763,916,765" shape="poly" onclick="return handleToothClick(event, '36', 'Middle');">
    <area alt="Tooth 37 Middle" title="Lower Left Second Molar Middle" href="#" coords="997,764,987,778,986,793,985,806,986,815,993,820,999,823,1014,823,1031,823,1045,816,1049,800,1048,780,1043,770,1035,764" shape="poly" onclick="return handleToothClick(event, '37', 'Middle');">
    <area alt="Tooth 38 Middle" title="Lower Left Third Molar Middle" href="#" coords="1089,762,1079,764,1069,770,1063,787,1061,801,1064,813,1070,818,1088,822,1110,816,1119,808,1122,792,1120,775,1112,765" shape="poly" onclick="return handleToothClick(event, '38', 'Middle');">
    
    <!-- Root/Bottom Surfaces -->
    <area alt="Tooth 18 Root" title="Upper Right Third Molar Root" href="#" coords="131,463,120,467,113,469,104,476,97,482,97,490,102,498,106,505,105,515,106,526,109,536,112,551,113,563,113,572,120,577,122,580,130,570,134,568,146,551,150,522,150,505,155,490,156,480,144,469" shape="poly" onclick="return handleToothClick(event, '18', 'Root');">
    <area alt="Tooth 17 Root" title="Upper Right Second Molar Root" href="#" coords="189,466,179,468,172,473,171,481,171,489,172,498,176,503,184,506,181,514,181,523,181,537,177,561,177,575,179,580,188,561,196,549,200,565,200,584,201,590,210,582,222,573,227,557,229,531,229,506,227,498,231,492,232,481,231,469,226,462,217,458,202,460" shape="poly" onclick="return handleToothClick(event, '17', 'Root');">
    <area alt="Tooth 16 Root" title="Upper Right First Molar Root" href="#" coords="280,464,271,468,259,469,253,474,248,482,250,490,252,495,257,503,259,514,256,523,252,535,250,545,244,561,242,574,244,586,248,582,257,558,264,554,265,570,265,582,265,595,271,599,278,588,292,557,295,552,295,571,292,584,292,592,305,582,310,566,309,546,306,527,305,506,310,496,314,483,314,474,303,469" shape="poly" onclick="return handleToothClick(event, '16', 'Root');">
    <area alt="Tooth 15 Root" title="Upper Right Second Premolar Root" href="#" coords="347,457,338,461,328,471,331,478,332,485,340,492,338,524,341,547,341,561,341,580,348,580,357,561,364,532,361,493,368,476,368,465" shape="poly" onclick="return handleToothClick(event, '15', 'Root');">
    <area alt="Tooth 14 Root" title="Upper Right First Premolar Root" href="#" coords="401,459,385,469,383,478,390,497,390,506,390,528,390,549,390,573,393,584,403,576,418,522,415,503,416,494,422,485,422,467" shape="poly" onclick="return handleToothClick(event, '14', 'Root');">
    <area alt="Tooth 13 Root" title="Upper Right Canine Root" href="#" coords="462,426,447,437,436,443,433,460,447,482,443,494,445,549,444,592,452,588,462,571,474,521,477,502,477,489,487,460,490,444,477,433" shape="poly" onclick="return handleToothClick(event, '13', 'Root');">
    <area alt="Tooth 12 Root" title="Upper Right Lateral Incisor Root" href="#" coords="520,431,503,437,498,445,499,454,502,464,506,474,510,489,511,502,512,510,514,520,515,532,515,548,516,563,523,558,532,531,536,511,537,485,540,453,537,433" shape="poly" onclick="return handleToothClick(event, '12', 'Root');">
    <area alt="Tooth 11 Root" title="Upper Right Central Incisor Root" href="#" coords="596,429,557,431,550,440,553,459,559,477,567,523,570,548,566,570,574,568,580,552,588,538,594,503,596,485,600,464,600,448,601,436" shape="poly" onclick="return handleToothClick(event, '11', 'Root');">
    <area alt="Tooth 21 Root" title="Upper Left Central Incisor Root" href="#" coords="624,428,624,465,628,478,629,506,632,525,642,550,649,566,657,570,654,540,657,514,663,490,663,477,670,462,675,450,670,431" shape="poly" onclick="return handleToothClick(event, '21', 'Root');">
    <area alt="Tooth 22 Root" title="Upper Left Lateral Incisor Root" href="#" coords="687,432,687,464,688,481,689,496,691,523,696,546,707,563,709,545,710,508,716,479,724,458,725,444,714,433,704,429" shape="poly" onclick="return handleToothClick(event, '22', 'Root');">
    <area alt="Tooth 23 Root" title="Upper Left Canine Root" href="#" coords="763,426,748,433,734,442,736,459,741,473,746,485,748,492,748,512,751,528,755,545,760,567,770,586,774,588,779,591,777,576,777,554,780,529,782,505,779,483,786,466,791,456,789,445" shape="poly" onclick="return handleToothClick(event, '23', 'Root');">
    <area alt="Tooth 24 Root" title="Upper Left First Premolar Root" href="#" coords="820,459,803,470,801,480,805,489,811,501,808,506,808,521,811,540,817,561,822,576,832,585,832,549,834,525,834,508,834,494,841,478,839,470" shape="poly" onclick="return handleToothClick(event, '24', 'Root');">
    <area alt="Tooth 25 Root" title="Upper Left Second Premolar Root" href="#" coords="878,458,861,463,856,467,861,489,868,494,861,506,861,525,863,549,868,563,873,575,878,580,882,575,880,560,884,541,887,520,887,496,894,477,894,467" shape="poly" onclick="return handleToothClick(event, '25', 'Root');">
    <area alt="Tooth 26 Root" title="Upper Left First Molar Root" href="#" coords="942,464,929,470,920,473,911,477,911,485,912,493,914,499,918,507,920,513,922,527,918,535,916,546,916,566,918,580,926,590,932,594,934,590,930,572,930,552,950,594,954,600,959,592,959,568,963,554,965,556,975,574,979,584,983,574,979,560,967,519,967,503,971,497,975,485,969,473,959,469" shape="poly" onclick="return handleToothClick(event, '26', 'Root');">
    <area alt="Tooth 27 Root" title="Upper Left Second Molar Root" href="#" coords="995,501,995,529,995,546,997,556,999,566,1009,580,1013,582,1017,582,1021,588,1027,584,1023,568,1027,546,1036,560,1040,568,1046,580,1048,576,1048,564,1044,546,1042,527,1042,509,1050,501,1054,489,1054,481,1050,473,1044,466,1031,469,1025,462,1015,460,1005,460,997,464,991,473" shape="poly" onclick="return handleToothClick(event, '27', 'Root');">
    <area alt="Tooth 28 Root" title="Upper Left Third Molar Root" href="#" coords="1088,464,1076,472,1068,482,1070,494,1072,499,1078,505,1074,511,1076,527,1076,535,1078,549,1084,559,1090,567,1096,571,1100,573,1104,580,1106,580,1111,567,1115,537,1121,509,1119,501,1125,490,1125,480,1121,476,1111,470,1102,466,1094,466" shape="poly" onclick="return handleToothClick(event, '28', 'Root');">
    
    <!-- Lower Root Surfaces -->
    <area alt="Tooth 48 Root" title="Lower Right Third Molar Root" href="#" coords="132,836,116,832,107,840,105,850,107,862,113,868,113,882,120,901,122,917,122,929,122,941,126,941,128,937,136,937,140,931,146,919,156,901,158,882,160,868,164,850,162,836,152,828,140,830" shape="poly" onclick="return handleToothClick(event, '48', 'Root');">
    <area alt="Tooth 47 Root" title="Lower Right Second Molar Root" href="#" coords="209,839,199,833,188,835,180,839,176,847,180,859,184,869,190,875,186,884,186,904,184,920,180,934,178,944,184,946,199,924,209,894,211,898,215,906,217,920,217,930,217,934,211,944,213,950,227,940,233,916,233,892,233,873,235,863,239,851,237,837,225,831" shape="poly" onclick="return handleToothClick(event, '47', 'Root');">
    <area alt="Tooth 46 Root" title="Lower Right First Molar Root" href="#" coords="296,837,280,835,261,839,257,850,261,839,257,854,259,868,267,878,267,896,263,918,263,939,259,957,270,947,288,900,296,902,300,916,302,939,302,949,296,955,308,955,316,939,320,912,318,884,318,870,322,854,322,837,308,831" shape="poly" onclick="return handleToothClick(event, '46', 'Root');">
    <area alt="Tooth 45 Root" title="Lower Right Second Premolar Root" href="#" coords="357,823,342,831,338,845,347,863,342,863,340,877,340,890,345,912,349,934,349,953,347,961,353,959,359,955,367,921,369,888,369,866,375,850,375,832,363,822" shape="poly" onclick="return handleToothClick(event, '45', 'Root');">
    <area alt="Tooth 44 Root" title="Lower Right First Premolar Root" href="#" coords="409,825,391,839,393,853,399,863,397,873,397,896,399,910,405,922,405,944,407,957,415,946,419,906,422,867,426,853,428,837" shape="poly" onclick="return handleToothClick(event, '44', 'Root');">
    <area alt="Tooth 43 Root" title="Lower Right Canine Root" href="#" coords="470,798,456,800,442,809,444,829,450,843,450,859,450,884,452,916,454,942,456,952,466,940,470,908,478,873,480,847,486,827,484,805" shape="poly" onclick="return handleToothClick(event, '43', 'Root');">
    <area alt="Tooth 42 Root" title="Lower Right Lateral Incisor Root" href="#" coords="523,794,509,796,501,809,503,825,509,837,509,843,509,859,513,882,517,898,517,918,519,926,525,918,529,900,531,871,531,851,537,827,537,807,533,796" shape="poly" onclick="return handleToothClick(event, '42', 'Root');">
    <area alt="Tooth 41 Root" title="Lower Right Central Incisor Root" href="#" coords="573,797,559,799,557,815,559,834,565,844,563,870,565,892,567,911,571,921,578,911,582,872,584,850,588,822,588,799" shape="poly" onclick="return handleToothClick(event, '41', 'Root');">
    <area alt="Tooth 31 Root" title="Lower Left Central Incisor Root" href="#" coords="651,796,638,796,634,804,634,820,640,843,642,859,644,879,646,901,651,918,653,922,659,903,661,857,663,837,665,820,663,796" shape="poly" onclick="return handleToothClick(event, '31', 'Root');">
    <area alt="Tooth 32 Root" title="Lower Left Lateral Incisor Root" href="#" coords="711,795,690,796,689,808,689,830,692,848,694,875,694,896,697,916,701,925,708,926,711,878,717,857,716,838,723,820,721,800" shape="poly" onclick="return handleToothClick(event, '32', 'Root');">
    <area alt="Tooth 33 Root" title="Lower Left Canine Root" href="#" coords="750,796,740,808,740,833,746,850,747,863,746,876,753,902,757,932,765,954,770,955,772,933,774,907,775,876,774,849,783,824,782,808,766,800" shape="poly" onclick="return handleToothClick(event, '33', 'Root');">
    <area alt="Tooth 34 Root" title="Lower Left First Premolar Root" href="#" coords="812,824,796,835,795,843,803,856,805,879,804,908,808,937,814,955,820,954,822,916,830,888,827,859,836,843,827,830" shape="poly" onclick="return handleToothClick(event, '34', 'Root');">
    <area alt="Tooth 35 Root" title="Lower Left Second Premolar Root" href="#" coords="865,822,852,833,848,839,849,849,854,859,857,875,857,889,859,895,860,911,862,930,863,938,867,948,870,962,876,964,878,946,879,908,885,887,883,864,885,844,886,835,878,830" shape="poly" onclick="return handleToothClick(event, '35', 'Root');">
    <area alt="Tooth 36 Root" title="Lower Left First Molar Root" href="#" coords="915,831,909,834,903,839,902,848,903,855,904,862,906,869,910,874,907,883,906,894,906,900,906,910,906,920,906,926,908,938,913,949,917,953,926,957,929,954,923,941,925,929,926,913,933,902,939,900,941,907,947,925,953,940,959,951,964,954,967,947,964,926,960,906,959,887,957,875,963,867,969,859,969,848,964,842,955,839,944,835,930,837" shape="poly" onclick="return handleToothClick(event, '36', 'Root');">
    <area alt="Tooth 37 Root" title="Lower Left Second Molar Root" href="#" coords="1014,837,1003,834,994,835,986,838,985,854,991,868,990,890,993,919,996,933,1004,945,1011,951,1013,944,1006,924,1013,897,1021,902,1027,924,1036,940,1041,948,1048,941,1039,896,1039,874,1049,856,1046,840,1039,834" shape="poly" onclick="return handleToothClick(event, '37', 'Root');">
    <area alt="Tooth 38 Root" title="Lower Left Third Molar Root" href="#" coords="1094,836,1079,826,1063,836,1062,854,1067,869,1065,886,1071,906,1076,919,1086,936,1096,941,1102,943,1107,898,1115,866,1122,851,1115,836" shape="poly" onclick="return handleToothClick(event, '38', 'Root');">
</map>

        </main>
    </div>
</div>

<!-- Service Selection Modal -->
<div id="serviceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-hidden">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-lg font-semibold">Add Service/Procedure</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeServiceModal()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="p-6">
                <!-- Service Search -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Services</label>
                    <input type="text" id="serviceSearch" placeholder="Search by service name or code..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           onkeyup="searchServices()">
                </div>
                
                <!-- Service List -->
                <div class="mb-4 max-h-60 overflow-y-auto border border-gray-200 rounded-md">
                    <div id="serviceList" class="divide-y divide-gray-200">
                        <!-- Services will be loaded here -->
                    </div>
                </div>
                
                <!-- Selected Service Details -->
                <div id="selectedServiceDetails" class="hidden mb-4 p-4 bg-gray-50 rounded-md">
                    <h4 class="font-medium mb-3">Service Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tooth Number (Optional)</label>
                            <select id="selectedTooth" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Tooth</option>
                                <?php for($i = 11; $i <= 18; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                                <?php for($i = 21; $i <= 28; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                                <?php for($i = 31; $i <= 38; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                                <?php for($i = 41; $i <= 48; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                            <textarea id="serviceNotes" rows="2" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Additional notes for this service..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end gap-3 px-6 py-4 border-t bg-gray-50">
                <button type="button" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50" 
                        onclick="closeServiceModal()">Cancel</button>
                <button type="button" id="addServiceBtn" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50" 
                        onclick="addSelectedService()" disabled>Add Service</button>
            </div>
        </div>
    </div>
</div>

<script>
// Services Management
let selectedService = null;
let appointmentServices = [];

// Load appointment services on page load
document.addEventListener('DOMContentLoaded', function() {
    loadAppointmentServices();
    loadAllServices();
});

// Load services for current appointment
function loadAppointmentServices() {
    const appointmentId = <?= $appointment['id'] ?>;
    console.log('Loading appointment services for ID:', appointmentId);
    
    fetch(`/checkup/${appointmentId}/services`)
        .then(response => response.json())
        .then(data => {
            console.log('Appointment services response:', data);
            if (data.success) {
                appointmentServices = data.services;
                updateServicesTable();
                updateServicesTotals();
            } else {
                console.error('Failed to load services:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading appointment services:', error);
        });
}

// Load all available services for selection
function loadAllServices() {
    fetch('/checkup/services/all')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayServiceList(data.services);
            }
        })
        .catch(error => {
            console.error('Error loading services:', error);
        });
}

// Search services
function searchServices() {
    const query = document.getElementById('serviceSearch').value;
    
    if (query.length < 2) {
        loadAllServices();
        return;
    }
    
    fetch(`/checkup/services/search?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayServiceList(data.services);
            }
        })
        .catch(error => {
            console.error('Error searching services:', error);
        });
}

// Display service list in modal
function displayServiceList(services) {
    const serviceList = document.getElementById('serviceList');
    
    if (services.length === 0) {
        serviceList.innerHTML = '<div class="p-4 text-gray-500 text-center">No services found</div>';
        return;
    }
    
    serviceList.innerHTML = services.map(service => `
        <div class="p-4 hover:bg-gray-50 cursor-pointer service-item" onclick="selectService(${service.id})">
            <div class="flex justify-between items-start">
                <div>
                    <h5 class="font-medium text-gray-900">${service.name}</h5>
                    <p class="text-sm text-gray-600">Service ID: ${service.id}</p>
                    ${service.description ? `<p class="text-sm text-gray-500 mt-1">${service.description}</p>` : ''}
                </div>
                <div class="text-right">
                    <span class="font-semibold text-gray-900">$${parseFloat(service.price).toFixed(2)}</span>
                </div>
            </div>
        </div>
    `).join('');
}

// Select a service from the list
function selectService(serviceId) {
    console.log('selectService called with ID:', serviceId, 'type:', typeof serviceId);
    
    // Remove previous selection
    document.querySelectorAll('.service-item').forEach(item => {
        item.classList.remove('bg-blue-50', 'border-blue-200');
    });
    
    // Add selection to clicked item
    event.currentTarget.classList.add('bg-blue-50', 'border-blue-200');
    
    // Find selected service
    fetch(`/checkup/services/all`)
        .then(response => response.json())
        .then(data => {
            console.log('Services response:', data);
            if (data.success) {
                // Convert serviceId to number for comparison (in case it's passed as string)
                const numericServiceId = parseInt(serviceId);
                console.log('Looking for service with ID:', numericServiceId);
                console.log('Available services:', data.services.map(s => ({id: s.id, name: s.name, idType: typeof s.id})));
                
                selectedService = data.services.find(s => parseInt(s.id) === numericServiceId);
                console.log('Selected service:', selectedService);
                
                if (selectedService) {
                    showServiceDetails();
                } else {
                    console.error('Service not found with ID:', numericServiceId);
                    alert('Service not found. Please try selecting again.');
                }
            } else {
                console.error('Failed to load services for selection');
            }
        })
        .catch(error => {
            console.error('Error in selectService:', error);
        });
}

// Show service details section
function showServiceDetails() {
    const detailsSection = document.getElementById('selectedServiceDetails');
    const addBtn = document.getElementById('addServiceBtn');
    
    detailsSection.classList.remove('hidden');
    addBtn.disabled = false;
}

// Add selected service to appointment
function addSelectedService() {
    console.log('addSelectedService called, selectedService:', selectedService);
    
    if (!selectedService) {
        console.error('No service selected');
        alert('Please select a service first');
        return;
    }
    
    const appointmentId = <?= $appointment['id'] ?>;
    const toothNumberFDI = document.getElementById('selectedTooth').value;
    const toothNumber = toothNumberFDI ? fdiToUniversal(toothNumberFDI) : null;
    const notes = document.getElementById('serviceNotes').value;
    
    console.log('Adding service:', {
        selectedService,
        appointmentId,
        toothNumber,
        notes
    });
    
    const serviceData = {
        service_id: selectedService.id,
        tooth_number: toothNumber || null,
        surface: null,
        notes: notes || null
    };
    
    console.log('Service data being sent:', serviceData);
    
    fetch(`/checkup/${appointmentId}/services`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(serviceData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadAppointmentServices(); // Refresh the table
            closeServiceModal();
            showSuccessMessage('Service added successfully');
        } else {
            showErrorMessage(data.message || 'Error adding service');
        }
    })
    .catch(error => {
        console.error('Error adding service:', error);
        showErrorMessage('Error adding service');
    });
}

// Remove service from appointment
function removeService(appointmentServiceId) {
    if (!confirm('Are you sure you want to remove this service?')) return;
    
    const appointmentId = <?= $appointment['id'] ?>;
    
    fetch(`/checkup/${appointmentId}/services/${appointmentServiceId}`, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadAppointmentServices(); // Refresh the table
            showSuccessMessage('Service removed successfully');
        } else {
            showErrorMessage(data.message || 'Error removing service');
        }
    })
    .catch(error => {
        console.error('Error removing service:', error);
        showErrorMessage('Error removing service');
    });
}

// Update services table
function updateServicesTable() {
    const tbody = document.getElementById('servicesTableBody');
    const noServicesMessage = document.getElementById('noServicesMessage');
    const servicesTable = document.getElementById('servicesTable');
    
    if (appointmentServices.length === 0) {
        noServicesMessage.classList.remove('hidden');
        servicesTable.classList.add('hidden');
        return;
    }
    
    noServicesMessage.classList.add('hidden');
    servicesTable.classList.remove('hidden');
    
    tbody.innerHTML = appointmentServices.map(service => {
        const displayName = service.name || service.service_name || service.procedure_name || service.service_description || service.description || `Service #${service.service_id || service.id || ''}`;
        const displayPrice = parseFloat(service.price ?? service.service_price ?? service.amount ?? 0);
        const toothNum = service.tooth_number ?? service.toothNumber ?? (service.tooth_numbers ?? '');
        const fdiTooth = toothNum ? universalToFdi(toothNum) : '';
        const surface = service.surface ?? service.tooth_surface ?? '';
        const appSvcId = service.appointment_service_id ?? service.id ?? service.appointmentServiceId;
        return `
        <tr>
            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                ${displayName}
            </td>
            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                $${displayPrice.toFixed(2)}
            </td>
            <td class="px-4 py-3 text-sm text-gray-500">
                ${fdiTooth ? 'Tooth ' + fdiTooth : '-'}${surface ? ' - ' + surface : ''}
            </td>
            <td class="px-4 py-3 text-sm font-medium">
                <button onclick="removeService(${appSvcId})" 
                        class="text-red-600 hover:text-red-900 text-xs bg-red-50 hover:bg-red-100 px-2 py-1 rounded">
                    Remove
                </button>
            </td>
        </tr>`;
    }).join('');
}

// Update services totals
function updateServicesTotals() {
    const total = appointmentServices.reduce((sum, service) => {
        const p = parseFloat(service.price ?? service.service_price ?? service.amount ?? 0);
        return sum + (isNaN(p) ? 0 : p);
    }, 0);
    const totalElement = document.getElementById('servicesTotal');
    
    if (totalElement) {
        totalElement.textContent = `$${total.toFixed(2)}`;
    }
}

// Modal functions
function openServiceModal() {
    document.getElementById('serviceModal').classList.remove('hidden');
    document.getElementById('serviceSearch').value = '';
    document.getElementById('selectedServiceDetails').classList.add('hidden');
    document.getElementById('addServiceBtn').disabled = true;
    selectedService = null;
    
    // Reset form fields
    document.getElementById('selectedTooth').value = '';
    document.getElementById('serviceNotes').value = '';
    
    loadAllServices();
}

function closeServiceModal() {
    document.getElementById('serviceModal').classList.add('hidden');
}

// Proceed to invoice
function proceedToInvoice() {
    if (appointmentServices.length === 0) {
        showErrorMessage('Please add at least one service before proceeding to invoice');
        return;
    }
    
    const appointmentId = <?= $appointment['id'] ?>;
    window.location.href = `/invoice/create/${appointmentId}`;
}

// Utility functions
function showSuccessMessage(message) {
    // You can implement a toast notification here
    alert(message); // Simple alert for now
}

function showErrorMessage(message) {
    // You can implement a toast notification here
    alert(message); // Simple alert for now
}
</script>

<?= view('templates/footer') ?> 