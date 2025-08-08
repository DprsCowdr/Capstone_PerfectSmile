<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dental Chart - <?= esc($appointment['patient_name']) ?> - Perfect Smile Admin</title>
    <link href="<?= base_url('vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Three.js Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
    <style>
        .tooth-grid {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 10px;
            margin: 20px 0;
        }
        .tooth-item {
            border: 3px solid #e5e7eb;
            border-radius: 12px;
            padding: 15px 8px;
            text-align: center;
            font-size: 12px;
            transition: all 0.3s;
            cursor: pointer;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .tooth-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .tooth-number {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 8px;
        }
        .tooth-status {
            font-weight: 600;
            text-transform: capitalize;
            margin-bottom: 5px;
        }
        .tooth-condition {
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 5px;
        }
        .tooth-service {
            font-size: 9px;
            background: rgba(0,0,0,0.1);
            padding: 2px 4px;
            border-radius: 4px;
        }
        
        /* Tooth Status Colors */
        .tooth-healthy { 
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); 
            border-color: #10b981; 
            color: #065f46;
        }
        .tooth-cavity { 
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); 
            border-color: #ef4444; 
            color: #7f1d1d;
        }
        .tooth-filling { 
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); 
            border-color: #f59e0b; 
            color: #92400e;
        }
        .tooth-crown { 
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); 
            border-color: #3b82f6; 
            color: #1e3a8a;
        }
        .tooth-extraction { 
            background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%); 
            border-color: #ef4444; 
            color: #7f1d1d;
        }
        .tooth-missing { 
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); 
            border-color: #6b7280; 
            color: #374151;
        }
        .tooth-root-canal { 
            background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%); 
            border-color: #8b5cf6; 
            color: #4c1d95;
        }
        .tooth-needs-treatment { 
            background: linear-gradient(135deg, #fde68a 0%, #fcd34d 100%); 
            border-color: #f59e0b; 
            color: #92400e;
        }

        .quadrant-header {
            text-align: center;
            font-weight: bold;
            color: #374151;
            margin: 20px 0 10px 0;
            padding: 10px;
            background: #f9fafb;
            border-radius: 8px;
        }
        
        .priority-urgent {
            border: 3px dashed #ef4444 !important;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        /* 3D Model Viewer Styles */
        #dentalModelViewer {
            width: 100%;
            height: 400px;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }

        #dentalModelCanvas {
            width: 100%;
            height: 100%;
            display: block;
        }

        .model-controls {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            z-index: 10;
        }

        .model-control-btn {
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.9);
            color: #374151;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            backdrop-filter: blur(10px);
        }

        .model-control-btn:hover {
            background: rgba(255, 255, 255, 1);
            transform: scale(1.05);
        }

        .model-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #6b7280;
            font-size: 14px;
            z-index: 5;
        }

        .model-error {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #ef4444;
            font-size: 14px;
            text-align: center;
            z-index: 5;
        }

        .view-toggle {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }

        .view-toggle-btn {
            padding: 8px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            background: white;
            color: #374151;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }

        .view-toggle-btn.active {
            border-color: #3b82f6;
            background: #3b82f6;
            color: white;
        }

        .view-toggle-btn:hover {
            border-color: #3b82f6;
            color: #3b82f6;
        }

        .view-toggle-btn.active:hover {
            color: white;
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
                                <i class="fas fa-tooth mr-3 text-green-600"></i>Dental Chart
                            </h1>
                            <p class="text-gray-600">Interactive dental examination chart</p>
                        </div>
                        <div class="mt-4 sm:mt-0 flex space-x-2">
                            <?php if (!empty($dentalChart)): ?>
                            <a href="<?= base_url('admin/dental-charts/edit/' . $appointment['id']) ?>" 
                               class="inline-flex items-center px-4 py-2 border border-blue-300 shadow-sm text-sm font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-edit mr-2"></i>Edit Chart
                            </a>
                            <?php endif; ?>
                            <a href="<?= base_url('admin/dental-charts') ?>" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-arrow-left mr-2"></i>Back to Charts
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Chart Header -->
                <div class="bg-gradient-to-r from-green-500 to-teal-500 text-white rounded-lg p-6 mb-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex-1">
                            <h2 class="text-2xl font-bold mb-4">
                                <i class="fas fa-user mr-2"></i>
                                <?= esc($appointment['patient_name']) ?> - Dental Chart
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
                        <div class="lg:ml-6 mt-4 lg:mt-0">
                            <div class="flex flex-col space-y-2">
                                <button onclick="printChart()" 
                                        class="inline-flex items-center px-4 py-2 border border-white text-sm font-medium rounded-md text-white hover:bg-white hover:text-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white">
                                    <i class="fas fa-print mr-2"></i>Print Chart
                                </button>
                                <?php if ($dentalRecord): ?>
                                <a href="<?= base_url('admin/dental-records/' . $dentalRecord['id']) ?>" 
                                   class="inline-flex items-center px-4 py-2 border border-white text-sm font-medium rounded-md text-white hover:bg-white hover:text-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white">
                                    <i class="fas fa-file-medical-alt mr-2"></i>View Record
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($dentalChart)): ?>
                <!-- View Toggle -->
                <div class="view-toggle mb-6">
                    <button class="view-toggle-btn active" onclick="switchView('chart')">
                        <i class="fas fa-list mr-2"></i>Chart View
                    </button>
                    <button class="view-toggle-btn" onclick="switchView('3d')">
                        <i class="fas fa-cube mr-2"></i>3D Model View
                    </button>
                </div>

                <!-- Chart View -->
                    <div class="lg:col-span-5">
                <div id="chartView" class="grid grid-cols-1 lg:grid-cols-6 gap-6">
                        <!-- Permanent Teeth Chart -->
                        <div class="bg-white rounded-lg shadow border-l-4 border-green-500 p-6 mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                <i class="fas fa-tooth mr-2 text-green-600"></i>Permanent Teeth (Adult Dentition)
                            </h3>
                            
                            <!-- Upper Right Quadrant -->
                            <div class="quadrant-header">Upper Right Quadrant (11-18)</div>
                            <div class="tooth-grid">
                                <?php 
                                $upperRight = array_filter($dentalChart, function($tooth) {
                                    return $tooth['tooth_number'] >= 11 && $tooth['tooth_number'] <= 18;
                                });
                                usort($upperRight, function($a, $b) { return $a['tooth_number'] - $b['tooth_number']; });
                                foreach ($upperRight as $tooth): 
                                ?>
                                <div class="tooth-item tooth-<?= $tooth['status'] ?> <?= $tooth['priority'] === 'urgent' ? 'priority-urgent' : '' ?>" 
                                     onclick="showToothDetails(<?= htmlspecialchars(json_encode($tooth)) ?>)">
                                    <div class="tooth-number"><?= $tooth['tooth_number'] ?></div>
                                    <div class="tooth-status"><?= str_replace('_', ' ', $tooth['status']) ?></div>
                                    <?php if ($tooth['condition'] && $tooth['condition'] !== 'Normal'): ?>
                                    <div class="tooth-condition"><?= esc($tooth['condition']) ?></div>
                                    <?php endif; ?>
                                    <?php if ($tooth['service_name']): ?>
                                    <div class="tooth-service"><?= esc($tooth['service_name']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Upper Left Quadrant -->
                            <div class="quadrant-header">Upper Left Quadrant (21-28)</div>
                            <div class="tooth-grid">
                                <?php 
                                $upperLeft = array_filter($dentalChart, function($tooth) {
                                    return $tooth['tooth_number'] >= 21 && $tooth['tooth_number'] <= 28;
                                });
                                usort($upperLeft, function($a, $b) { return $a['tooth_number'] - $b['tooth_number']; });
                                foreach ($upperLeft as $tooth): 
                                ?>
                                <div class="tooth-item tooth-<?= $tooth['status'] ?> <?= $tooth['priority'] === 'urgent' ? 'priority-urgent' : '' ?>" 
                                     onclick="showToothDetails(<?= htmlspecialchars(json_encode($tooth)) ?>)">
                                    <div class="tooth-number"><?= $tooth['tooth_number'] ?></div>
                                    <div class="tooth-status"><?= str_replace('_', ' ', $tooth['status']) ?></div>
                                    <?php if ($tooth['condition'] && $tooth['condition'] !== 'Normal'): ?>
                                    <div class="tooth-condition"><?= esc($tooth['condition']) ?></div>
                                    <?php endif; ?>
                                    <?php if ($tooth['service_name']): ?>
                                    <div class="tooth-service"><?= esc($tooth['service_name']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Lower Left Quadrant -->
                            <div class="quadrant-header">Lower Left Quadrant (31-38)</div>
                            <div class="tooth-grid">
                                <?php 
                                $lowerLeft = array_filter($dentalChart, function($tooth) {
                                    return $tooth['tooth_number'] >= 31 && $tooth['tooth_number'] <= 38;
                                });
                                usort($lowerLeft, function($a, $b) { return $a['tooth_number'] - $b['tooth_number']; });
                                foreach ($lowerLeft as $tooth): 
                                ?>
                                <div class="tooth-item tooth-<?= $tooth['status'] ?> <?= $tooth['priority'] === 'urgent' ? 'priority-urgent' : '' ?>" 
                                     onclick="showToothDetails(<?= htmlspecialchars(json_encode($tooth)) ?>)">
                                    <div class="tooth-number"><?= $tooth['tooth_number'] ?></div>
                                    <div class="tooth-status"><?= str_replace('_', ' ', $tooth['status']) ?></div>
                                    <?php if ($tooth['condition'] && $tooth['condition'] !== 'Normal'): ?>
                                    <div class="tooth-condition"><?= esc($tooth['condition']) ?></div>
                                    <?php endif; ?>
                                    <?php if ($tooth['service_name']): ?>
                                    <div class="tooth-service"><?= esc($tooth['service_name']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Lower Right Quadrant -->
                            <div class="quadrant-header">Lower Right Quadrant (41-48)</div>
                            <div class="tooth-grid">
                                <?php 
                                $lowerRight = array_filter($dentalChart, function($tooth) {
                                    return $tooth['tooth_number'] >= 41 && $tooth['tooth_number'] <= 48;
                                });
                                usort($lowerRight, function($a, $b) { return $a['tooth_number'] - $b['tooth_number']; });
                                foreach ($lowerRight as $tooth): 
                                ?>
                                <div class="tooth-item tooth-<?= $tooth['status'] ?> <?= $tooth['priority'] === 'urgent' ? 'priority-urgent' : '' ?>" 
                                     onclick="showToothDetails(<?= htmlspecialchars(json_encode($tooth)) ?>)">
                                    <div class="tooth-number"><?= $tooth['tooth_number'] ?></div>
                                    <div class="tooth-status"><?= str_replace('_', ' ', $tooth['status']) ?></div>
                                    <?php if ($tooth['condition'] && $tooth['condition'] !== 'Normal'): ?>
                                    <div class="tooth-condition"><?= esc($tooth['condition']) ?></div>
                                    <?php endif; ?>
                                    <?php if ($tooth['service_name']): ?>
                                    <div class="tooth-service"><?= esc($tooth['service_name']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Primary Teeth Chart (if any) -->
                        <?php 
                        $primaryTeeth = array_filter($dentalChart, function($tooth) {
                            return $tooth['tooth_number'] >= 51;
                        });
                        if (!empty($primaryTeeth)): 
                        ?>
                        <div class="bg-white rounded-lg shadow border-l-4 border-blue-500 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                <i class="fas fa-child mr-2 text-blue-600"></i>Primary Teeth (Children's Dentition)
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <?php foreach ($primaryTeeth as $tooth): ?>
                                <div class="tooth-item tooth-<?= $tooth['status'] ?> <?= $tooth['priority'] === 'urgent' ? 'priority-urgent' : '' ?>" 
                                     onclick="showToothDetails(<?= htmlspecialchars(json_encode($tooth)) ?>)">
                                    <div class="tooth-number"><?= $tooth['tooth_number'] ?></div>
                                    <div class="tooth-status"><?= str_replace('_', ' ', $tooth['status']) ?></div>
                                    <?php if ($tooth['condition'] && $tooth['condition'] !== 'Normal'): ?>
                                    <div class="tooth-condition"><?= esc($tooth['condition']) ?></div>
                                    <?php endif; ?>
                                    <?php if ($tooth['service_name']): ?>
                                    <div class="tooth-service"><?= esc($tooth['service_name']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    </div>
                    <!-- Legend and Summary -->
                    <div class="space-y-6">
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                <i class="fas fa-info-circle mr-2 text-blue-600"></i>Chart Legend
                            </h3>
                            <div class="space-y-3">
                                <div class="flex items-center">
                                    <div class="w-6 h-6 rounded tooth-healthy mr-3"></div>
                                    <span class="text-sm">Healthy</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-6 h-6 rounded tooth-cavity mr-3"></div>
                                    <span class="text-sm">Cavity</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-6 h-6 rounded tooth-filling mr-3"></div>
                                    <span class="text-sm">Filling</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-6 h-6 rounded tooth-crown mr-3"></div>
                                    <span class="text-sm">Crown</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-6 h-6 rounded tooth-missing mr-3"></div>
                                    <span class="text-sm">Missing</span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                <i class="fas fa-tools mr-2 text-purple-600"></i>Quick Actions
                            </h3>
                            <div class="space-y-2">
                                <button class="w-full inline-flex items-center justify-center px-3 py-2 border border-blue-300 text-sm font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="fas fa-calendar-plus mr-2"></i>Schedule Follow-up
                                </button>
                                <button class="w-full inline-flex items-center justify-center px-3 py-2 border border-green-300 text-sm font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <i class="fas fa-procedures mr-2"></i>Plan Treatment
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3D Model View -->
                <div id="modelView" class="hidden">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-cube mr-2 text-blue-600"></i>3D Dental Model Viewer
                        </h3>
                        <p class="text-gray-600 mb-6">Interactive 3D model of permanent dentition. Use mouse to rotate, scroll to zoom, and right-click to pan.</p>
                        
                        <div id="dentalModelViewer">
                            <div class="model-loading" id="modelLoading">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Loading 3D Model...
                            </div>
                            <div class="model-error hidden" id="modelError">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <div>Failed to load 3D model</div>
                                <button onclick="loadDentalModel()" class="mt-2 px-3 py-1 bg-blue-500 text-white rounded text-sm">Retry</button>
                            </div>
                            <canvas id="dentalModelCanvas"></canvas>
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
                            </div>
                        </div>
                    </div>
                </div>

                <?php else: ?>
                <div class="bg-white rounded-lg shadow p-12 text-center">
                    <i class="fas fa-tooth fa-3x text-gray-400 mb-4"></i>
                    <h4 class="text-lg font-semibold text-gray-800 mb-2">No Dental Chart Available</h4>
                    <p class="text-gray-600 mb-6">This appointment doesn't have a dental chart yet. Dental charts are created during patient examinations.</p>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-left">
                        <h5 class="font-semibold text-blue-900 mb-2">How to Create a Dental Chart:</h5>
                        <ol class="text-sm text-blue-800 space-y-1">
                            <li>1. Dentist conducts patient examination</li>
                            <li>2. Dentist records findings using the dental chart form</li>
                            <li>3. Chart includes tooth-by-tooth conditions, treatments needed, and priority levels</li>
                            <li>4. Once saved, the chart becomes available for admin review</li>
                        </ol>
                    </div>
                    
                    <div class="flex justify-center space-x-4">
                        <a href="<?= base_url('admin/dental-charts/create/' . $appointment['id']) ?>" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <i class="fas fa-plus mr-2"></i>Create Dental Chart
                        </a>
                        <a href="<?= base_url('admin/dental-charts') ?>" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Charts
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Tooth Details Modal -->
    <div id="toothModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Tooth Details</h3>
                <button onclick="closeToothModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times fa-lg"></i>
                </button>
            </div>
            <div id="toothDetails">
                <!-- Tooth details will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        // Three.js Variables
        let scene, camera, renderer, controls, model;
        let autoRotate = false;
        let wireframeMode = false;

        // View switching
        function switchView(view) {
            const chartView = document.getElementById('chartView');
            const modelView = document.getElementById('modelView');
            const buttons = document.querySelectorAll('.view-toggle-btn');
            
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            if (view === 'chart') {
                chartView.classList.remove('hidden');
                modelView.classList.add('hidden');
            } else {
                chartView.classList.add('hidden');
                modelView.classList.remove('hidden');
                if (!renderer) {
                    initDentalModel();
                }
            }
        }

        // Initialize 3D Dental Model
        function initDentalModel() {
            const canvas = document.getElementById('dentalModelCanvas');
            const container = document.getElementById('dentalModelViewer');
            
            // Scene setup
            scene = new THREE.Scene();
            scene.background = new THREE.Color(0xf8fafc);
            
            // Camera setup
            camera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 0.1, 1000);
            camera.position.set(0, 0, 5);
            
            // Renderer setup
            renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true });
            renderer.setSize(container.clientWidth, container.clientHeight);
            renderer.setPixelRatio(window.devicePixelRatio);
            renderer.shadowMap.enabled = true;
            renderer.shadowMap.type = THREE.PCFSoftShadowMap;
            
            // Lighting
            const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
            scene.add(ambientLight);
            
            const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
            directionalLight.position.set(10, 10, 5);
            directionalLight.castShadow = true;
            scene.add(directionalLight);
            
            const pointLight = new THREE.PointLight(0xffffff, 0.5);
            pointLight.position.set(-10, -10, -5);
            scene.add(pointLight);
            
            // Controls
            controls = new THREE.OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;
            controls.dampingFactor = 0.05;
            controls.screenSpacePanning = false;
            controls.minDistance = 2;
            controls.maxDistance = 20;
            controls.maxPolarAngle = Math.PI;
            
            // Load the model
            loadDentalModel();
            
            // Animation loop
            animate();
            
            // Handle window resize
            window.addEventListener('resize', onWindowResize);
        }

        // Load the GLB model
        function loadDentalModel() {
            const loadingDiv = document.getElementById('modelLoading');
            const errorDiv = document.getElementById('modelError');
            
            loadingDiv.classList.remove('hidden');
            errorDiv.classList.add('hidden');
            
            const loader = new THREE.GLTFLoader();
            const modelUrl = '<?= base_url('img/permanent_dentition-2.glb') ?>';
            
            loader.load(
                modelUrl,
                function (gltf) {
                    model = gltf.scene;
                    
                    // Center and scale the model
                    const box = new THREE.Box3().setFromObject(model);
                    const center = box.getCenter(new THREE.Vector3());
                    const size = box.getSize(new THREE.Vector3());
                    
                    const maxDim = Math.max(size.x, size.y, size.z);
                    const scale = 3 / maxDim;
                    model.scale.setScalar(scale);
                    
                    model.position.sub(center.multiplyScalar(scale));
                    
                    // Add shadows
                    model.traverse((child) => {
                        if (child.isMesh) {
                            child.castShadow = true;
                            child.receiveShadow = true;
                        }
                    });
                    
                    scene.add(model);
                    loadingDiv.classList.add('hidden');
                },
                function (xhr) {
                    // Progress callback
                    const percent = (xhr.loaded / xhr.total) * 100;
                    loadingDiv.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i>Loading 3D Model... ${Math.round(percent)}%`;
                },
                function (error) {
                    console.error('Error loading model:', error);
                    loadingDiv.classList.add('hidden');
                    errorDiv.classList.remove('hidden');
                }
            );
        }

        // Animation loop
        function animate() {
            requestAnimationFrame(animate);
            
            if (controls) {
                controls.update();
            }
            
            if (renderer && scene && camera) {
                renderer.render(scene, camera);
            }
        }

        // Handle window resize
        function onWindowResize() {
            const container = document.getElementById('dentalModelViewer');
            if (camera && renderer && container) {
                camera.aspect = container.clientWidth / container.clientHeight;
                camera.updateProjectionMatrix();
                renderer.setSize(container.clientWidth, container.clientHeight);
            }
        }

        // Model controls
        function resetCamera() {
            if (controls) {
                controls.reset();
            }
        }

        function toggleWireframe() {
            if (model) {
                wireframeMode = !wireframeMode;
                model.traverse((child) => {
                    if (child.isMesh) {
                        child.material.wireframe = wireframeMode;
                    }
                });
            }
        }

        function toggleAutoRotate() {
            if (controls) {
                autoRotate = !autoRotate;
                controls.autoRotate = autoRotate;
                controls.autoRotateSpeed = 2.0;
            }
        }

        function printChart() {
            window.print();
        }

        function showToothDetails(tooth) {
            const priorityColors = {
                'urgent': 'bg-red-100 text-red-800',
                'high': 'bg-yellow-100 text-yellow-800',
                'medium': 'bg-blue-100 text-blue-800',
                'low': 'bg-gray-100 text-gray-800'
            };
            
            const details = `
                <div class="space-y-4">
                    <div class="text-center">
                        <h6 class="text-xl font-bold text-gray-900 mb-2">Tooth #${tooth.tooth_number}</h6>
                        <div class="border-t pt-3">
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">Status:</span>
                                    <span class="text-sm text-gray-900">${tooth.status.replace('_', ' ')}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">Condition:</span>
                                    <span class="text-sm text-gray-900">${tooth.condition || 'Normal'}</span>
                                </div>
                                ${tooth.service_name ? `
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">Recommended Service:</span>
                                    <span class="text-sm text-gray-900">${tooth.service_name}</span>
                                </div>
                                ` : ''}
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">Priority:</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${priorityColors[tooth.priority] || 'bg-gray-100 text-gray-800'}">${tooth.priority}</span>
                                </div>
                                ${tooth.notes ? `
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Notes:</span>
                                    <p class="text-sm text-gray-900 mt-1">${tooth.notes}</p>
                                </div>
                                ` : ''}
                                ${tooth.created_at ? `
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">Recorded:</span>
                                    <span class="text-sm text-gray-900">${new Date(tooth.created_at).toLocaleDateString()}</span>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('toothDetails').innerHTML = details;
            document.getElementById('toothModal').classList.remove('hidden');
        }

        function closeToothModal() {
            document.getElementById('toothModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('toothModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeToothModal();
            }
        });
    </script>
</body>
</html>
