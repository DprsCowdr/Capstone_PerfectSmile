<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Dental Chart - <?= esc($appointment['patient_name']) ?> - Perfect Smile Admin</title>
    <link href="<?= base_url('vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
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
                                <i class="fas fa-edit mr-3 text-blue-600"></i>Edit Dental Chart
                            </h1>
                            <p class="text-gray-600">Update dental examination findings for patient</p>
                        </div>
                        <div class="mt-4 sm:mt-0">
                            <a href="<?= base_url('admin/dental-charts/' . $appointment['id']) ?>" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-arrow-left mr-2"></i>Back to Chart
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Patient & Appointment Info -->
                <div class="bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-lg p-6 mb-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex-1">
                            <h2 class="text-2xl font-bold mb-4">
                                <i class="fas fa-user mr-2"></i>
                                <?= esc($appointment['patient_name']) ?> - Edit Dental Chart
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="mb-2"><i class="fas fa-calendar mr-2"></i>Appointment: <?= date('F j, Y g:i A', strtotime($appointment['appointment_datetime'])) ?></p>
                                    <p class="mb-2"><i class="fas fa-user-md mr-2"></i>Examining Dentist: Dr. <?= esc($appointment['dentist_name']) ?></p>
                                </div>
                                <div>
                                    <p class="mb-2"><i class="fas fa-building mr-2"></i>Branch: <?= esc($appointment['branch_name']) ?></p>
                                    <p class="mb-2"><i class="fas fa-clock mr-2"></i>Last Updated: 
                                        <?= $dentalRecord ? date('F j, Y g:i A', strtotime($dentalRecord['updated_at'] ?? $dentalRecord['created_at'])) : 'N/A' ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <?php if ($dentalRecord): ?>
                <form action="<?= base_url('admin/dental-records/update/' . $dentalRecord['id']) ?>" method="POST" class="space-y-6">
                    <input type="hidden" name="appointment_id" value="<?= $appointment['id'] ?>">
                    
                    <!-- Dental Record Information -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-file-medical mr-2 text-blue-600"></i>Examination Record
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Next Appointment Date & Time</label>
                                <input type="datetime-local" name="next_appointment_datetime" value="<?= $dentalRecord['next_appointment_date'] ? date('Y-m-d\TH:i', strtotime($dentalRecord['next_appointment_date'])) : '' ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" min="<?= date('Y-m-d\TH:i') ?>">
                                <p class="mt-1 text-sm text-gray-500">Select the date and time for the next appointment.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Record Date</label>
                                <input type="text" value="<?= date('F j, Y', strtotime($dentalRecord['record_date'])) ?>" readonly class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-6 mt-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Diagnosis</label>
                                <textarea name="diagnosis" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter diagnosis findings..."><?= esc($dentalRecord['diagnosis']) ?></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Treatment Plan</label>
                                <textarea name="treatment" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter treatment plan..."><?= esc($dentalRecord['treatment']) ?></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Additional Notes</label>
                                <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Additional notes or observations..."><?= esc($dentalRecord['notes']) ?></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">X-Ray Image URL (optional)</label>
                                <input type="url" name="xray_image_url" value="<?= esc($dentalRecord['xray_image_url']) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="https://...">
                            </div>
                        </div>
                    </div>

                    <!-- Dental Chart -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-tooth mr-2 text-green-600"></i>Interactive Dental Chart
                        </h3>
                        <p class="text-gray-600 mb-6">Click on each tooth to update its condition. Changes are highlighted in the chart.</p>

                        <!-- Upper Jaw -->
                        <div class="jaw-section">
                            <div class="jaw-title">Upper Jaw (Maxilla)</div>
                            <div class="grid grid-cols-2 gap-8">
                                <!-- Upper Right -->
                                <div>
                                    <h4 class="font-medium text-gray-700 mb-3">Upper Right (11-18)</h4>
                                    <div class="tooth-grid" style="grid-template-columns: repeat(4, 1fr);">
                                        <?php for ($i = 11; $i <= 18; $i++): ?>
                                        <?php
                                        $toothChart = null;
                                        foreach ($dentalChart as $chart) {
                                            if ($chart['tooth_number'] == $i) {
                                                $toothChart = $chart;
                                                break;
                                            }
                                        }
                                        $status = $toothChart ? $toothChart['status'] : 'healthy';
                                        $hasCondition = $toothChart && ($toothChart['status'] !== 'healthy' || $toothChart['condition'] || $toothChart['notes']);
                                        ?>
                                        <div class="tooth-item <?= $hasCondition ? 'has-condition' : '' ?>" data-tooth="<?= $i ?>" onclick="openToothForm(<?= $i ?>)">
                                            <div class="tooth-number"><?= $i ?></div>
                                            <div class="tooth-name"><?= $toothLayout[$i] ?? 'Tooth ' . $i ?></div>
                                            <div class="tooth-status status-<?= $status ?>" id="status-<?= $i ?>"><?= str_replace('_', ' ', ucfirst($status)) ?></div>
                                        </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <!-- Upper Left -->
                                <div>
                                    <h4 class="font-medium text-gray-700 mb-3">Upper Left (21-28)</h4>
                                    <div class="tooth-grid" style="grid-template-columns: repeat(4, 1fr);">
                                        <?php for ($i = 21; $i <= 28; $i++): ?>
                                        <?php
                                        $toothChart = null;
                                        foreach ($dentalChart as $chart) {
                                            if ($chart['tooth_number'] == $i) {
                                                $toothChart = $chart;
                                                break;
                                            }
                                        }
                                        $status = $toothChart ? $toothChart['status'] : 'healthy';
                                        $hasCondition = $toothChart && ($toothChart['status'] !== 'healthy' || $toothChart['condition'] || $toothChart['notes']);
                                        ?>
                                        <div class="tooth-item <?= $hasCondition ? 'has-condition' : '' ?>" data-tooth="<?= $i ?>" onclick="openToothForm(<?= $i ?>)">
                                            <div class="tooth-number"><?= $i ?></div>
                                            <div class="tooth-name"><?= $toothLayout[$i] ?? 'Tooth ' . $i ?></div>
                                            <div class="tooth-status status-<?= $status ?>" id="status-<?= $i ?>"><?= str_replace('_', ' ', ucfirst($status)) ?></div>
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
                                        <?php
                                        $toothChart = null;
                                        foreach ($dentalChart as $chart) {
                                            if ($chart['tooth_number'] == $i) {
                                                $toothChart = $chart;
                                                break;
                                            }
                                        }
                                        $status = $toothChart ? $toothChart['status'] : 'healthy';
                                        $hasCondition = $toothChart && ($toothChart['status'] !== 'healthy' || $toothChart['condition'] || $toothChart['notes']);
                                        ?>
                                        <div class="tooth-item <?= $hasCondition ? 'has-condition' : '' ?>" data-tooth="<?= $i ?>" onclick="openToothForm(<?= $i ?>)">
                                            <div class="tooth-number"><?= $i ?></div>
                                            <div class="tooth-name"><?= $toothLayout[$i] ?? 'Tooth ' . $i ?></div>
                                            <div class="tooth-status status-<?= $status ?>" id="status-<?= $i ?>"><?= str_replace('_', ' ', ucfirst($status)) ?></div>
                                        </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <!-- Lower Right -->
                                <div>
                                    <h4 class="font-medium text-gray-700 mb-3">Lower Right (41-48)</h4>
                                    <div class="tooth-grid" style="grid-template-columns: repeat(4, 1fr);">
                                        <?php for ($i = 41; $i <= 48; $i++): ?>
                                        <?php
                                        $toothChart = null;
                                        foreach ($dentalChart as $chart) {
                                            if ($chart['tooth_number'] == $i) {
                                                $toothChart = $chart;
                                                break;
                                            }
                                        }
                                        $status = $toothChart ? $toothChart['status'] : 'healthy';
                                        $hasCondition = $toothChart && ($toothChart['status'] !== 'healthy' || $toothChart['condition'] || $toothChart['notes']);
                                        ?>
                                        <div class="tooth-item <?= $hasCondition ? 'has-condition' : '' ?>" data-tooth="<?= $i ?>" onclick="openToothForm(<?= $i ?>)">
                                            <div class="tooth-number"><?= $i ?></div>
                                            <div class="tooth-name"><?= $toothLayout[$i] ?? 'Tooth ' . $i ?></div>
                                            <div class="tooth-status status-<?= $status ?>" id="status-<?= $i ?>"><?= str_replace('_', ' ', ucfirst($status)) ?></div>
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
                            <a href="<?= base_url('admin/dental-charts/' . $appointment['id']) ?>" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-save mr-2"></i>Update Dental Chart
                            </button>
                        </div>
                    </div>
                </form>
                <?php else: ?>
                <div class="bg-white rounded-lg shadow p-12 text-center">
                    <i class="fas fa-exclamation-triangle fa-3x text-yellow-400 mb-4"></i>
                    <h4 class="text-lg font-semibold text-gray-800 mb-2">No Dental Record Found</h4>
                    <p class="text-gray-600 mb-6">Cannot edit chart without an existing dental record.</p>
                    <a href="<?= base_url('admin/dental-charts/create/' . $appointment['id']) ?>" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <i class="fas fa-plus mr-2"></i>Create New Chart
                    </a>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Overlay -->
    <div class="overlay" onclick="closeToothForm()"></div>

    <!-- Tooth Detail Form -->
    <div class="tooth-detail-form">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Edit Tooth Details</h3>
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
        
        // Load existing dental chart data
        <?php if (!empty($dentalChart)): ?>
        const existingChartData = <?= json_encode($dentalChart) ?>;
        existingChartData.forEach(tooth => {
            toothData[tooth.tooth_number] = {
                status: tooth.status,
                condition: tooth.condition,
                priority: tooth.priority,
                recommended_service_id: tooth.recommended_service_id,
                notes: tooth.notes,
                estimated_cost: tooth.estimated_cost,
                tooth_type: tooth.tooth_type || 'permanent'
            };
        });
        <?php endif; ?>

        function openToothForm(toothNumber) {
            currentTooth = toothNumber;
            document.getElementById('current-tooth').value = 'Tooth #' + toothNumber;
            
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
            
            // Create hidden inputs for form submission
            updateHiddenInputs();
            
            closeToothForm();
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
            if (form) {
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

        // Initialize hidden inputs on page load
        updateHiddenInputs();
    </script>
</body>
</html>
