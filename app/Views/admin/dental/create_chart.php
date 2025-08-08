<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Dental Chart - <?= esc($appointment['patient_name']) ?> - Perfect Smile Admin</title>
    <link href="<?= base_url('vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
    <style>
        .tooth-grid {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 10px;
            margin: 20px 0;
        }
        .tooth-item {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
            min-height: 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .tooth-item:hover {
            border-color: #3b82f6;
            box-shadow: 0 2px 8px rgba(59,130,246,0.3);
            transform: translateY(-1px);
        }
        .tooth-item.selected {
            border-color: #3b82f6;
            background-color: #dbeafe;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }
        .tooth-item.has-condition {
            border-color: #ef4444;
            background-color: #fecaca;
        }
        .tooth-number {
            font-weight: bold;
            font-size: 16px;
            color: #374151;
            margin-bottom: 4px;
        }
        .tooth-name {
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 4px;
        }
        .tooth-status {
            font-size: 8px;
            padding: 2px 4px;
            border-radius: 3px;
            display: inline-block;
            font-weight: 500;
        }
        .status-healthy { background-color: #d1fae5; color: #065f46; }
        .status-cavity { background-color: #fecaca; color: #7f1d1d; }
        .status-filling { background-color: #fef3c7; color: #92400e; }
        .status-crown { background-color: #dbeafe; color: #1e3a8a; }
        .status-extraction_needed { background-color: #fca5a5; color: #7f1d1d; }
        .status-missing { background-color: #e5e7eb; color: #374151; }
        .status-root_canal { background-color: #ede9fe; color: #4c1d95; }
        .status-needs_treatment { background-color: #fde68a; color: #92400e; }
        
        .jaw-section {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #f9fafb;
        }
        .jaw-title {
            font-weight: bold;
            margin-bottom: 15px;
            color: #374151;
            font-size: 18px;
        }
        .tooth-detail-form {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            z-index: 1000;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        
        /* 3D Tooth Styles */
        .tooth-3d-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }
        .tooth-3d-canvas {
            width: 100%;
            height: 400px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        .tooth-controls {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        .tooth-control-btn {
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 6px;
            color: white;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }
        .tooth-control-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-1px);
        }
        .tooth-control-btn.active {
            background: rgba(59, 130, 246, 0.8);
            border-color: #3b82f6;
        }
        .tooth-info {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 14px;
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="bg-[#F5ECFE]">
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
                                <i class="fas fa-plus mr-3 text-green-600"></i>Create Dental Chart
                            </h1>
                            <p class="text-gray-600">Record dental examination findings for patient</p>
                        </div>
                        <div class="mt-4 sm:mt-0">
                            <a href="<?= base_url('admin/dental-charts') ?>" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-arrow-left mr-2"></i>Back to Charts
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Patient & Appointment Info -->
                <div class="bg-gradient-to-r from-green-500 to-teal-500 text-white rounded-lg p-6 mb-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex-1">
                            <h2 class="text-2xl font-bold mb-4">
                                <i class="fas fa-user mr-2"></i>
                                <?= esc($appointment['patient_name']) ?> - New Dental Chart
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="mb-2"><i class="fas fa-calendar mr-2"></i>Appointment: <?= date('F j, Y g:i A', strtotime($appointment['appointment_datetime'])) ?></p>
                                    <p class="mb-2"><i class="fas fa-user-md mr-2"></i>Examining Dentist: Dr. <?= esc($appointment['dentist_name']) ?></p>
                                </div>
                                <div>
                                    <p class="mb-2"><i class="fas fa-building mr-2"></i>Branch: <?= esc($appointment['branch_name']) ?></p>
                                    <p class="mb-2"><i class="fas fa-info-circle mr-2"></i>Status: 
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><?= ucfirst($appointment['status']) ?></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3D Tooth Visualization -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-cube mr-2 text-purple-600"></i>3D Tooth Visualization
                    </h3>
                    <p class="text-gray-600 mb-4">Click on any tooth in the 3D model to select it for examination. Use the controls below to rotate and zoom the view.</p>
                    
                    <div class="tooth-3d-container">
                        <div class="tooth-info" id="tooth-info">
                            <strong>Loading:</strong> Initializing 3D model...
                        </div>
                        <div id="loading-indicator" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 rounded-lg">
                            <div class="text-white text-center">
                                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-white mx-auto mb-4"></div>
                                <p>Loading Dental Model...</p>
                                <p id="loading-progress" class="text-sm mt-2">0%</p>
                            </div>
                        </div>
                        <canvas id="tooth3dCanvas" class="tooth-3d-canvas"></canvas>
                        
                        <div class="tooth-controls">
                            <button class="tooth-control-btn" onclick="resetView()">
                                <i class="fas fa-home mr-1"></i>Reset View
                            </button>
                            <button class="tooth-control-btn" onclick="toggleRotation()" id="rotationBtn">
                                <i class="fas fa-sync mr-1"></i>Auto Rotate
                            </button>
                            <button class="tooth-control-btn" onclick="zoomIn()">
                                <i class="fas fa-search-plus mr-1"></i>Zoom In
                            </button>
                            <button class="tooth-control-btn" onclick="zoomOut()">
                                <i class="fas fa-search-minus mr-1"></i>Zoom Out
                            </button>
                            <button class="tooth-control-btn" onclick="toggleWireframe()" id="wireframeBtn">
                                <i class="fas fa-border-all mr-1"></i>Wireframe
                            </button>
                            <button class="tooth-control-btn" onclick="viewFromFront()">
                                <i class="fas fa-eye mr-1"></i>Front View
                            </button>
                            <button class="tooth-control-btn" onclick="viewFromSide()">
                                <i class="fas fa-eye mr-1"></i>Side View
                            </button>
                            <button class="tooth-control-btn" onclick="viewFromTop()">
                                <i class="fas fa-eye mr-1"></i>Top View
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form action="<?= base_url('admin/dental-records/store') ?>" method="POST" class="space-y-6">
                    <input type="hidden" name="patient_id" value="<?= $appointment['user_id'] ?>">
                    <input type="hidden" name="appointment_id" value="<?= $appointment['id'] ?>">
                    
                    <!-- Dental Record Information -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-file-medical mr-2 text-blue-600"></i>Examination Record
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Examining Dentist</label>
                                <select name="dentist_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                    <option value="">Select Dentist</option>
                                    <option value="<?= $appointment['dentist_id'] ?>" selected>Dr. <?= esc($appointment['dentist_name']) ?> (Assigned)</option>
                                    <?php
                                    // You could add other dentists here if needed
                                    ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Next Appointment Date & Time</label>
                                <input type="datetime-local" name="next_appointment_datetime" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" min="<?= date('Y-m-d\TH:i') ?>">
                                <p class="mt-1 text-sm text-gray-500">Select the date and time for the next appointment.</p>
                            </div>
                        </div>

                        <!-- Automatically Create Appointment Option -->
                        <div class="mb-6">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="create_appointment" name="create_appointment" type="checkbox" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="create_appointment" class="font-medium text-gray-700">Automatically create next appointment</label>
                                    <p class="text-gray-500">When checked, this will automatically create the next appointment in the system when a date/time is provided above.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-6 mt-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Diagnosis</label>
                                <textarea name="diagnosis" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter diagnosis findings..."></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Treatment Plan</label>
                                <textarea name="treatment" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter treatment plan..."></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Additional Notes</label>
                                <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Additional notes or observations..."></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">X-Ray Image URL (optional)</label>
                                <input type="url" name="xray_image_url" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="https://...">
                            </div>
                        </div>
                    </div>

                    <!-- Dental Chart -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-tooth mr-2 text-green-600"></i>Interactive Dental Chart
                        </h3>
                        <p class="text-gray-600 mb-6">Click on each tooth to record its condition. Only teeth with conditions will be saved.</p>

                        <!-- Upper Jaw -->
                        <div class="jaw-section">
                            <div class="jaw-title">Upper Jaw (Maxilla)</div>
                            <div class="grid grid-cols-2 gap-8">
                                <!-- Upper Right -->
                                <div>
                                    <h4 class="font-medium text-gray-700 mb-3">Upper Right (11-18)</h4>
                                    <div class="tooth-grid" style="grid-template-columns: repeat(4, 1fr);">
                                        <?php for ($i = 11; $i <= 18; $i++): ?>
                                        <div class="tooth-item" data-tooth="<?= $i ?>" onclick="openToothForm(<?= $i ?>)">
                                            <div class="tooth-number"><?= $i ?></div>
                                            <div class="tooth-name"><?= $toothLayout[$i] ?? 'Tooth ' . $i ?></div>
                                            <div class="tooth-status status-healthy" id="status-<?= $i ?>">Healthy</div>
                                        </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <!-- Upper Left -->
                                <div>
                                    <h4 class="font-medium text-gray-700 mb-3">Upper Left (21-28)</h4>
                                    <div class="tooth-grid" style="grid-template-columns: repeat(4, 1fr);">
                                        <?php for ($i = 21; $i <= 28; $i++): ?>
                                        <div class="tooth-item" data-tooth="<?= $i ?>" onclick="openToothForm(<?= $i ?>)">
                                            <div class="tooth-number"><?= $i ?></div>
                                            <div class="tooth-name"><?= $toothLayout[$i] ?? 'Tooth ' . $i ?></div>
                                            <div class="tooth-status status-healthy" id="status-<?= $i ?>">Healthy</div>
                                        </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Lower Jaw -->
                        <div class="jaw-section">
                            <div class="jaw-title">Lower Jaw (Mandible)</div>
                            <div class="grid grid-cols-2 gap-8">
                                <!-- Lower Left -->
                                <div>
                                    <h4 class="font-medium text-gray-700 mb-3">Lower Left (31-38)</h4>
                                    <div class="tooth-grid" style="grid-template-columns: repeat(4, 1fr);">
                                        <?php for ($i = 31; $i <= 38; $i++): ?>
                                        <div class="tooth-item" data-tooth="<?= $i ?>" onclick="openToothForm(<?= $i ?>)">
                                            <div class="tooth-number"><?= $i ?></div>
                                            <div class="tooth-name"><?= $toothLayout[$i] ?? 'Tooth ' . $i ?></div>
                                            <div class="tooth-status status-healthy" id="status-<?= $i ?>">Healthy</div>
                                        </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <!-- Lower Right -->
                                <div>
                                    <h4 class="font-medium text-gray-700 mb-3">Lower Right (41-48)</h4>
                                    <div class="tooth-grid" style="grid-template-columns: repeat(4, 1fr);">
                                        <?php for ($i = 41; $i <= 48; $i++): ?>
                                        <div class="tooth-item" data-tooth="<?= $i ?>" onclick="openToothForm(<?= $i ?>)">
                                            <div class="tooth-number"><?= $i ?></div>
                                            <div class="tooth-name"><?= $toothLayout[$i] ?? 'Tooth ' . $i ?></div>
                                            <div class="tooth-status status-healthy" id="status-<?= $i ?>">Healthy</div>
                                        </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-end space-x-4">
                            <a href="<?= base_url('admin/dental-charts') ?>" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <i class="fas fa-save mr-2"></i>Save Dental Chart
                            </button>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <!-- Overlay -->
    <div class="overlay" onclick="closeToothForm()"></div>

    <!-- Tooth Detail Form -->
    <div class="tooth-detail-form">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Tooth Details</h3>
            <button onclick="closeToothForm()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times fa-lg"></i>
            </button>
        </div>
        
        <div id="tooth-form-content">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tooth Number</label>
                <input type="text" id="current-tooth" readonly class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="tooth-status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="healthy">Healthy</option>
                    <option value="cavity">Cavity</option>
                    <option value="filling">Filling</option>
                    <option value="crown">Crown</option>
                    <option value="root_canal">Root Canal</option>
                    <option value="extraction_needed">Extraction Needed</option>
                    <option value="missing">Missing</option>
                    <option value="needs_treatment">Needs Treatment</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Condition</label>
                <select id="tooth-condition" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select condition</option>
                    <?php foreach ($toothConditions as $condition): ?>
                    <option value="<?= $condition ?>"><?= $condition ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                <select id="tooth-priority" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Recommended Service</label>
                <select id="tooth-service" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">No service needed</option>
                    <?php foreach ($services as $service): ?>
                    <option value="<?= $service['id'] ?>"><?= esc($service['name']) ?> - $<?= number_format($service['price'], 2) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea id="tooth-notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Additional notes for this tooth..."></textarea>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Estimated Cost</label>
                <input type="number" id="tooth-cost" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="0.00">
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="clearTooth()" class="px-4 py-2 border border-red-300 text-red-700 rounded-md hover:bg-red-50">
                    Clear
                </button>
                <button type="button" onclick="closeToothForm()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                    Cancel
                </button>
                <button type="button" onclick="saveTooth()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Save Tooth
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentTooth = null;
        let toothData = {};

        function openToothForm(toothNumber) {
            currentTooth = toothNumber;
            document.getElementById('current-tooth').value = 'Tooth #' + toothNumber;
            
            // Update 3D visualization if available
            if (typeof selectTooth === 'function') {
                selectTooth(toothNumber);
            }
            
            // Load existing data if any
            if (toothData[toothNumber]) {
                const data = toothData[toothNumber];
                document.getElementById('tooth-status').value = data.status || 'healthy';
                document.getElementById('tooth-condition').value = data.condition || '';
                document.getElementById('tooth-priority').value = data.priority || 'medium';
                document.getElementById('tooth-service').value = data.recommended_service_id || '';
                document.getElementById('tooth-notes').value = data.notes || '';
                document.getElementById('tooth-cost').value = data.estimated_cost || '';
            } else {
                // Reset form
                document.getElementById('tooth-status').value = 'healthy';
                document.getElementById('tooth-condition').value = '';
                document.getElementById('tooth-priority').value = 'medium';
                document.getElementById('tooth-service').value = '';
                document.getElementById('tooth-notes').value = '';
                document.getElementById('tooth-cost').value = '';
            }
            
            document.querySelector('.overlay').style.display = 'block';
            document.querySelector('.tooth-detail-form').style.display = 'block';
        }

        function closeToothForm() {
            document.querySelector('.overlay').style.display = 'none';
            document.querySelector('.tooth-detail-form').style.display = 'none';
            currentTooth = null;
        }

        function saveTooth() {
            if (!currentTooth) return;
            
            const status = document.getElementById('tooth-status').value;
            const condition = document.getElementById('tooth-condition').value;
            const priority = document.getElementById('tooth-priority').value;
            const serviceId = document.getElementById('tooth-service').value;
            const notes = document.getElementById('tooth-notes').value;
            const cost = document.getElementById('tooth-cost').value;
            
            // Save data
            toothData[currentTooth] = {
                status: status,
                condition: condition,
                priority: priority,
                recommended_service_id: serviceId,
                notes: notes,
                estimated_cost: cost,
                tooth_type: 'permanent'
            };
            
            // Update visual status
            const statusElement = document.getElementById('status-' + currentTooth);
            statusElement.textContent = status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
            statusElement.className = 'tooth-status status-' + status;
            
            // Update tooth appearance
            const toothElement = document.querySelector(`[data-tooth="${currentTooth}"]`);
            if (status !== 'healthy' || condition || notes) {
                toothElement.classList.add('has-condition');
            } else {
                toothElement.classList.remove('has-condition');
            }
            
            // Update 3D tooth visualization
            update3DToothStatus(currentTooth, status);
            
            // Create hidden inputs for form submission
            updateHiddenInputs();
            
            closeToothForm();
        }

        function update3DToothStatus(toothNumber, status) {
            if (!teeth) return;
            
            const tooth = teeth.find(t => t.userData.toothNumber === toothNumber);
            if (!tooth) return;
            
            // Create new material based on status
            const newMaterial = tooth.userData.originalMaterial.clone();
            
            // Color mapping based on status
            const colorMap = {
                'healthy': 0xf5f5dc,
                'cavity': 0xff6b6b,
                'filling': 0xffd93d,
                'crown': 0x4ecdc4,
                'root_canal': 0xa8e6cf,
                'extraction_needed': 0xff8a80,
                'missing': 0x9e9e9e,
                'needs_treatment': 0xffb74d
            };
            
            const color = colorMap[status] || 0xf5f5dc;
            
            if (status === 'missing') {
                // For missing teeth, make them transparent
                newMaterial.transparent = true;
                newMaterial.opacity = 0.3;
                newMaterial.color.setHex(color);
            } else if (status === 'healthy') {
                // For healthy teeth, use original material
                newMaterial = tooth.userData.originalMaterial.clone();
            } else {
                // For other conditions, apply color tint
                newMaterial.color.setHex(color);
                newMaterial.emissive = new THREE.Color(color);
                newMaterial.emissiveIntensity = 0.2;
            }
            
            tooth.material = newMaterial;
        }

        function clearTooth() {
            if (!currentTooth) return;
            
            delete toothData[currentTooth];
            
            // Reset visual
            const statusElement = document.getElementById('status-' + currentTooth);
            statusElement.textContent = 'Healthy';
            statusElement.className = 'tooth-status status-healthy';
            
            const toothElement = document.querySelector(`[data-tooth="${currentTooth}"]`);
            toothElement.classList.remove('has-condition');
            
            updateHiddenInputs();
            closeToothForm();
        }

        function updateHiddenInputs() {
            // Remove existing hidden inputs
            const existingInputs = document.querySelectorAll('input[name^="tooth["]');
            existingInputs.forEach(input => input.remove());
            
            // Add new hidden inputs
            const form = document.querySelector('form');
            Object.keys(toothData).forEach(toothNumber => {
                const data = toothData[toothNumber];
                Object.keys(data).forEach(key => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `tooth[${toothNumber}][${key}]`;
                    input.value = data[key];
                    form.appendChild(input);
                });
            });
        }

        // Auto-populate cost when service is selected
        document.getElementById('tooth-service').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                const priceMatch = selectedOption.text.match(/\$([0-9,]+\.?\d*)/);
                if (priceMatch) {
                    document.getElementById('tooth-cost').value = priceMatch[1].replace(',', '');
                }
            } else {
                document.getElementById('tooth-cost').value = '';
            }
        });

        // 3D Tooth Visualization
        let scene, camera, renderer, teeth = [], selectedTooth = null;
        let isRotating = false, isWireframe = false;
        let mouse = new THREE.Vector2();
        let raycaster = new THREE.Raycaster();
        let dentalModel = null;
        let loader = new THREE.GLTFLoader();

        function init3DTooth() {
            const canvas = document.getElementById('tooth3dCanvas');
            const container = canvas.parentElement;
            
            // Scene setup
            scene = new THREE.Scene();
            scene.background = new THREE.Color(0x1a1a2e);
            
            // Camera setup
            camera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 0.1, 1000);
            camera.position.set(0, 0, 15);
            
            // Renderer setup
            renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true });
            renderer.setSize(container.clientWidth, container.clientHeight);
            renderer.shadowMap.enabled = true;
            renderer.shadowMap.type = THREE.PCFSoftShadowMap;
            
            // Lighting
            const ambientLight = new THREE.AmbientLight(0x404040, 0.6);
            scene.add(ambientLight);
            
            const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
            directionalLight.position.set(10, 10, 5);
            directionalLight.castShadow = true;
            scene.add(directionalLight);
            
            const pointLight = new THREE.PointLight(0xffffff, 0.5);
            pointLight.position.set(-10, -10, -5);
            scene.add(pointLight);
            
            // Load dental model
            loadDentalModel();
            
            // Event listeners
            canvas.addEventListener('click', onMouseClick);
            canvas.addEventListener('mousemove', onMouseMove);
            window.addEventListener('resize', onWindowResize);
            
            // Start animation
            animate();
        }

        function loadDentalModel() {
            const loadingManager = new THREE.LoadingManager();
            
            loadingManager.onLoad = function() {
                console.log('Dental model loaded successfully');
                // Enable interactions after model is loaded
                document.getElementById('tooth-info').innerHTML = '<strong>Ready:</strong> Click on any tooth to select';
                document.getElementById('loading-indicator').style.display = 'none';
                document.getElementById('loading-progress').textContent = '100%';
            };
            
            loadingManager.onError = function(url) {
                console.error('Error loading dental model:', url);
                document.getElementById('tooth-info').innerHTML = '<strong>Error:</strong> Could not load dental model';
                document.getElementById('loading-indicator').style.display = 'none';
                document.getElementById('loading-progress').textContent = '0%';
            };
            
            const gltfLoader = new THREE.GLTFLoader(loadingManager);
            
            gltfLoader.load(
                '<?= base_url('img/permanent_dentition-2.glb') ?>',
                function(gltf) {
                    dentalModel = gltf.scene;
                    
                    // Scale and position the model
                    dentalModel.scale.set(5, 5, 5);
                    dentalModel.position.set(0, 0, 0);
                    
                    // Enable shadows for all meshes
                    dentalModel.traverse(function(child) {
                        if (child.isMesh) {
                            child.castShadow = true;
                            child.receiveShadow = true;
                            
                            // Store original material for each tooth
                            if (child.name && child.name.includes('tooth')) {
                                child.userData.originalMaterial = child.material.clone();
                                child.userData.toothNumber = extractToothNumber(child.name);
                                teeth.push(child);
                            }
                        }
                    });
                    
                    scene.add(dentalModel);
                },
                function(progress) {
                    console.log('Loading progress:', (progress.loaded / progress.total * 100) + '%');
                    document.getElementById('loading-progress').textContent = Math.round(progress.loaded / progress.total * 100) + '%';
                },
                function(error) {
                    console.error('Error loading GLB file:', error);
                    document.getElementById('tooth-info').innerHTML = '<strong>Error:</strong> Could not load dental model';
                    document.getElementById('loading-indicator').style.display = 'none';
                    document.getElementById('loading-progress').textContent = '0%';
                }
            );
        }

        function extractToothNumber(toothName) {
            // Extract tooth number from the mesh name
            // This will depend on how your GLB file names the teeth
            const match = toothName.match(/(\d+)/);
            if (match) {
                return parseInt(match[1]);
            }
            
            // Fallback mapping based on common dental naming conventions
            const toothMapping = {
                'upper_right_third_molar': 18,
                'upper_right_second_molar': 17,
                'upper_right_first_molar': 16,
                'upper_right_second_premolar': 15,
                'upper_right_first_premolar': 14,
                'upper_right_canine': 13,
                'upper_right_lateral_incisor': 12,
                'upper_right_central_incisor': 11,
                'upper_left_central_incisor': 21,
                'upper_left_lateral_incisor': 22,
                'upper_left_canine': 23,
                'upper_left_first_premolar': 24,
                'upper_left_second_premolar': 25,
                'upper_left_first_molar': 26,
                'upper_left_second_molar': 27,
                'upper_left_third_molar': 28,
                'lower_left_third_molar': 38,
                'lower_left_second_molar': 37,
                'lower_left_first_molar': 36,
                'lower_left_second_premolar': 35,
                'lower_left_first_premolar': 34,
                'lower_left_canine': 33,
                'lower_left_lateral_incisor': 32,
                'lower_left_central_incisor': 31,
                'lower_right_central_incisor': 41,
                'lower_right_lateral_incisor': 42,
                'lower_right_canine': 43,
                'lower_right_first_premolar': 44,
                'lower_right_second_premolar': 45,
                'lower_right_first_molar': 46,
                'lower_right_second_molar': 47,
                'lower_right_third_molar': 48
            };
            
            return toothMapping[toothName.toLowerCase()] || 0;
        }

        function onMouseClick(event) {
            if (!dentalModel) return;
            
            const canvas = document.getElementById('tooth3dCanvas');
            const rect = canvas.getBoundingClientRect();
            mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
            mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;
            
            raycaster.setFromCamera(mouse, camera);
            const intersects = raycaster.intersectObjects(teeth, true);
            
            if (intersects.length > 0) {
                const clickedTooth = intersects[0].object;
                if (clickedTooth.userData.toothNumber) {
                    selectTooth(clickedTooth.userData.toothNumber);
                }
            }
        }

        function onMouseMove(event) {
            if (!dentalModel) return;
            
            const canvas = document.getElementById('tooth3dCanvas');
            const rect = canvas.getBoundingClientRect();
            mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
            mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;
            
            raycaster.setFromCamera(mouse, camera);
            const intersects = raycaster.intersectObjects(teeth, true);
            
            // Reset all teeth to normal
            teeth.forEach(tooth => {
                if (tooth.userData.originalMaterial) {
                    tooth.material = tooth.userData.originalMaterial.clone();
                }
                tooth.scale.set(1, 1, 1);
            });
            
            // Highlight hovered tooth
            if (intersects.length > 0) {
                const hoveredTooth = intersects[0].object;
                if (hoveredTooth.userData.toothNumber) {
                    // Create highlight material
                    const highlightMaterial = hoveredTooth.userData.originalMaterial.clone();
                    highlightMaterial.emissive = new THREE.Color(0x90EE90);
                    highlightMaterial.emissiveIntensity = 0.3;
                    hoveredTooth.material = highlightMaterial;
                    hoveredTooth.scale.set(1.05, 1.05, 1.05);
                }
            }
        }

        function selectTooth(toothNumber) {
            selectedTooth = toothNumber;
            
            // Update info display
            document.getElementById('tooth-info').innerHTML = `<strong>Selected:</strong> Tooth #${toothNumber}`;
            
            // Highlight selected tooth
            teeth.forEach(tooth => {
                if (tooth.userData.toothNumber === toothNumber) {
                    // Create selection material
                    const selectionMaterial = tooth.userData.originalMaterial.clone();
                    selectionMaterial.emissive = new THREE.Color(0x3b82f6);
                    selectionMaterial.emissiveIntensity = 0.5;
                    tooth.material = selectionMaterial;
                    tooth.scale.set(1.1, 1.1, 1.1);
                } else {
                    // Reset to original material
                    if (tooth.userData.originalMaterial) {
                        tooth.material = tooth.userData.originalMaterial.clone();
                    }
                    tooth.scale.set(1, 1, 1);
                }
            });
            
            // Open tooth form
            openToothForm(toothNumber);
        }

        function animate() {
            requestAnimationFrame(animate);
            
            if (isRotating) {
                scene.rotation.y += 0.01;
            }
            
            renderer.render(scene, camera);
        }

        function resetView() {
            camera.position.set(0, 0, 15);
            scene.rotation.set(0, 0, 0);
            isRotating = false;
            document.getElementById('rotationBtn').classList.remove('active');
        }

        function toggleRotation() {
            isRotating = !isRotating;
            const btn = document.getElementById('rotationBtn');
            if (isRotating) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        }

        function zoomIn() {
            camera.position.z = Math.max(5, camera.position.z - 2);
        }

        function zoomOut() {
            camera.position.z = Math.min(25, camera.position.z + 2);
        }

        function toggleWireframe() {
            isWireframe = !isWireframe;
            teeth.forEach(tooth => {
                tooth.material.wireframe = isWireframe;
            });
            const btn = document.getElementById('wireframeBtn');
            if (isWireframe) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        }

        function viewFromFront() {
            camera.position.set(0, 0, 15);
            camera.lookAt(0, 0, 0);
        }

        function viewFromSide() {
            camera.position.set(15, 0, 0);
            camera.lookAt(0, 0, 0);
        }

        function viewFromTop() {
            camera.position.set(0, 15, 0);
            camera.lookAt(0, 0, 0);
        }

        function onWindowResize() {
            const canvas = document.getElementById('tooth3dCanvas');
            const container = canvas.parentElement;
            camera.aspect = container.clientWidth / container.clientHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(container.clientWidth, container.clientHeight);
        }

        // Initialize 3D scene when page loads
        window.addEventListener('load', function() {
            setTimeout(init3DTooth, 100);
        });
    </script>
</body>
</html>
