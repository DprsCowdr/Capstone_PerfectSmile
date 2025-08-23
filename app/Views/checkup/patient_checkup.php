<?= view('templates/header') ?>

<!-- Three.js Library for 3D Dental Model -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>

<!-- 3D Dental Viewer Styles and Scripts -->
<link rel="stylesheet" href="<?= base_url('css/dental-3d-viewer.css') ?>">
<script src="<?= base_url('js/dental-3d-viewer.js') ?>"></script>
<script src="<?= base_url('js/dental-checkup.js') ?>"></script>

<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    
    <!-- Main content area -->
    <div class="flex-1 flex flex-col min-h-screen lg:ml-0">
        <!-- Topbar -->
        <nav class="flex items-center justify-between bg-white shadow px-4 sm:px-6 py-4 mb-6 lg:ml-0">
            <!-- Mobile sidebar toggle is now in sidebar template -->
            <div class="lg:hidden w-10"></div> <!-- Spacer for mobile menu button -->
            
            <div class="flex items-center ml-auto">
                <span class="mr-4 hidden lg:inline text-gray-600 font-semibold"><?= $user['name'] ?? 'Doctor' ?></span>
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
        <form action="/checkup/save/<?= $appointment['id'] ?>" method="POST" class="space-y-8">
            
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
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
                        <!-- Chart View -->
                        <div class="order-2 lg:order-1">
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
                        <div class="order-1 lg:order-2">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                                <i class="fas fa-cube mr-2 text-blue-600"></i>3D Dental Model
                            </h3>
                     
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
                                            <button class="treatment-popup-close" onclick="closeTreatmentPopup()">
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

            <!-- Diagnosis and Treatment Section -->
            <div class="bg-white rounded-xl shadow-lg">
                <div class="p-4 sm:p-6 border-b border-gray-200">
                    <h2 class="text-lg sm:text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-stethoscope text-blue-500 mr-2 sm:mr-3"></i>
                        Diagnosis & Treatment <span class="text-red-500 text-sm font-normal">(Required)</span>
                    </h2>
                    <p class="text-sm text-gray-600 mt-2">Complete the essential medical findings and treatment plan for this checkup.</p>
                </div>
                <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
                    <div>
                        <label for="diagnosis" class="block text-sm font-medium text-gray-700 mb-2">
                            Diagnosis <span class="text-red-500">*</span>
                            <span class="text-gray-500 font-normal">(Required for medical records)</span>
                        </label>
                        
                        <!-- Quick Diagnosis Templates -->
                        <div class="mb-3">
                            <label class="block text-xs font-medium text-gray-600 mb-2">Quick Templates (click to use):</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                <button type="button" onclick="setDiagnosis('Routine dental cleaning completed. No cavities or issues detected.')" 
                                        class="px-3 py-1 bg-green-100 hover:bg-green-200 text-green-800 text-xs rounded-lg border border-green-300 transition-colors">
                                    ✓ Routine Cleaning
                                </button>
                                <button type="button" onclick="setDiagnosis('Cavity detected requiring filling treatment.')" 
                                        class="px-3 py-1 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 text-xs rounded-lg border border-yellow-300 transition-colors">
                                    ⚠ Cavity Found
                                </button>
                                <button type="button" onclick="setDiagnosis('Gingivitis - mild gum inflammation detected.')" 
                                        class="px-3 py-1 bg-orange-100 hover:bg-orange-200 text-orange-800 text-xs rounded-lg border border-orange-300 transition-colors">
                                    📋 Gingivitis
                                </button>
                                <button type="button" onclick="setDiagnosis('Plaque and tartar buildup - professional cleaning performed.')" 
                                        class="px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs rounded-lg border border-blue-300 transition-colors">
                                    🦷 Plaque Removal
                                </button>
                                <button type="button" onclick="setDiagnosis('Tooth sensitivity reported by patient.')" 
                                        class="px-3 py-1 bg-purple-100 hover:bg-purple-200 text-purple-800 text-xs rounded-lg border border-purple-300 transition-colors">
                                    😬 Sensitivity
                                </button>
                                <button type="button" onclick="setDiagnosis('Orthodontic consultation and evaluation completed.')" 
                                        class="px-3 py-1 bg-indigo-100 hover:bg-indigo-200 text-indigo-800 text-xs rounded-lg border border-indigo-300 transition-colors">
                                    📐 Orthodontic
                                </button>
                                <button type="button" onclick="clearDiagnosis()" 
                                        class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs rounded-lg border border-gray-300 transition-colors">
                                    🗑️ Clear Field
                                </button>
                            </div>
                        </div>
                        
                        <textarea id="diagnosis" name="diagnosis" rows="4" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base"
                                  placeholder="Enter detailed diagnosis (e.g., 'Routine dental cleaning completed', 'Cavity detected on tooth #14', 'Gingivitis - mild', etc.)..."><?= old('diagnosis') ?></textarea>
                        <p class="text-xs text-gray-500 mt-1">Include dental conditions found, cleaning performed, or note 'No issues detected' for routine checkups.</p>
                    </div>

                    <div>
                        <label for="treatment" class="block text-sm font-medium text-gray-700 mb-2">
                            Treatment Plan <span class="text-red-500">*</span>
                            <span class="text-gray-500 font-normal">(Required for medical records)</span>
                        </label>
                        
                        <!-- Quick Treatment Templates -->
                        <div class="mb-3">
                            <label class="block text-xs font-medium text-gray-600 mb-2">Quick Templates (click to use):</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                <button type="button" onclick="setTreatment('Continue regular oral hygiene routine. Return in 6 months for routine cleaning.')" 
                                        class="px-3 py-1 bg-green-100 hover:bg-green-200 text-green-800 text-xs rounded-lg border border-green-300 transition-colors">
                                    ✓ Routine Care
                                </button>
                                <button type="button" onclick="setTreatment('Schedule appointment for filling procedure within 2 weeks.')" 
                                        class="px-3 py-1 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 text-xs rounded-lg border border-yellow-300 transition-colors">
                                    🔧 Schedule Filling
                                </button>
                                <button type="button" onclick="setTreatment('Improve brushing and flossing technique. Use antibacterial mouthwash daily.')" 
                                        class="px-3 py-1 bg-orange-100 hover:bg-orange-200 text-orange-800 text-xs rounded-lg border border-orange-300 transition-colors">
                                    📋 Oral Hygiene
                                </button>
                                <button type="button" onclick="setTreatment('Professional cleaning completed. Use sensitive toothpaste as recommended.')" 
                                        class="px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs rounded-lg border border-blue-300 transition-colors">
                                    🦷 Sensitivity Care
                                </button>
                                <button type="button" onclick="setTreatment('Follow-up appointment in 3 months to monitor progress.')" 
                                        class="px-3 py-1 bg-purple-100 hover:bg-purple-200 text-purple-800 text-xs rounded-lg border border-purple-300 transition-colors">
                                    📅 Follow-up
                                </button>
                                <button type="button" onclick="setTreatment('Refer to orthodontist for detailed evaluation and treatment planning.')" 
                                        class="px-3 py-1 bg-indigo-100 hover:bg-indigo-200 text-indigo-800 text-xs rounded-lg border border-indigo-300 transition-colors">
                                    📐 Orthodontic Referral
                                </button>
                                <button type="button" onclick="clearTreatment()" 
                                        class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs rounded-lg border border-gray-300 transition-colors">
                                    🗑️ Clear Field
                                </button>
                            </div>
                        </div>
                        
                        <textarea id="treatment" name="treatment" rows="4" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base"
                                  placeholder="Enter treatment plan (e.g., 'Continue regular oral hygiene', 'Schedule filling for tooth #14', 'Return in 6 months for routine cleaning', etc.)..."><?= old('treatment') ?></textarea>
                        <p class="text-xs text-gray-500 mt-1">Describe immediate treatments performed and future treatment recommendations.</p>
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
                                <p class="text-xs sm:text-sm text-gray-600 mb-2"><strong>Diagnosis:</strong> <?= $record['diagnosis'] ?></p>
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
    </div>
</div>

<style>
.grid-cols-16 {
    grid-template-columns: repeat(16, minmax(0, 1fr));
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
        });
    }
});

// Template functions for Diagnosis & Treatment
function setDiagnosis(text) {
    const diagnosisField = document.getElementById('diagnosis');
    diagnosisField.value = text;
    diagnosisField.focus();
    // Add a small visual feedback
    diagnosisField.style.borderColor = '#10B981';
    setTimeout(() => {
        diagnosisField.style.borderColor = '';
    }, 1000);
}

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

function clearDiagnosis() {
    const diagnosisField = document.getElementById('diagnosis');
    diagnosisField.value = '';
    diagnosisField.focus();
    // Add a small visual feedback
    diagnosisField.style.borderColor = '#EF4444';
    setTimeout(() => {
        diagnosisField.style.borderColor = '';
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
</script>

        </main>
    </div>
</div>

<?= view('templates/footer') ?> 