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
                <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-4 w-full sm:w-auto">
                    <a href="/checkup" class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white px-3 sm:px-4 py-2 rounded-lg text-sm font-semibold transition-colors text-center">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                    <a href="/admin/dental-charts/<?= $appointment['id'] ?>" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-3 sm:px-4 py-2 rounded-lg text-sm font-semibold transition-colors text-center">
                        <i class="fas fa-tooth mr-2"></i>Go to Chart
                    </a>
                    <a href="/auth/logout" class="w-full sm:w-auto bg-red-500 hover:bg-red-600 text-white px-3 sm:px-4 py-2 rounded-lg text-sm font-semibold transition-colors text-center">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
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
                        Diagnosis & Treatment
                    </h2>
                </div>
                <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
                    <div>
                        <label for="diagnosis" class="block text-sm font-medium text-gray-700 mb-2">Diagnosis *</label>
                        <textarea id="diagnosis" name="diagnosis" rows="4" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base"
                                  placeholder="Enter detailed diagnosis..."><?= old('diagnosis') ?></textarea>
                    </div>

                    <div>
                        <label for="treatment" class="block text-sm font-medium text-gray-700 mb-2">Treatment Plan *</label>
                        <textarea id="treatment" name="treatment" rows="4" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base"
                                  placeholder="Enter treatment plan..."><?= old('treatment') ?></textarea>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Additional Notes</label>
                        <textarea id="notes" name="notes" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base"
                                  placeholder="Any additional notes or observations..."><?= old('notes') ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label for="next_appointment_date" class="block text-sm font-medium text-gray-700 mb-2">Next Appointment Date</label>
                            <input type="date" id="next_appointment_date" name="next_appointment_date"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base"
                                   value="<?= old('next_appointment_date') ?>">
                        </div>

                        <div>
                            <label for="next_appointment_time" class="block text-sm font-medium text-gray-700 mb-2">Next Appointment Time</label>
                            <input type="time" id="next_appointment_time" name="next_appointment_time"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base"
                                   value="<?= old('next_appointment_time') ?>">
                        </div>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-500">Leave appointment fields empty if no follow-up is needed</p>
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

        </main>
    </div>
</div>

<?= view('templates/footer') ?> 