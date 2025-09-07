<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dental Charts - Perfect Smile Admin</title>
    <link href="<?= base_url('vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/style.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/admin.css') ?>" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom styles for dental chart */
        .tooth-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(45px, 1fr));
            gap: 4px;
            max-width: 100%;
        }
        
        @media (min-width: 640px) {
            .tooth-grid {
                grid-template-columns: repeat(8, 1fr);
                gap: 8px;
            }
        }
        
        .tooth-item {
            aspect-ratio: 1;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 4px;
            text-align: center;
            font-size: 10px;
            transition: all 0.3s;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 50px;
        }
        
        @media (min-width: 640px) {
            .tooth-item {
                min-height: 70px;
                font-size: 12px;
                padding: 8px;
            }
        }
        
        .tooth-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .tooth-number {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 2px;
        }
        
        @media (min-width: 640px) {
            .tooth-number {
                font-size: 16px;
                margin-bottom: 4px;
            }
        }
        
        /* Tooth condition colors */
        .tooth-healthy { background: #dcfce7; border-color: #16a34a; color: #15803d; }
        .tooth-cavity { background: #fef3c7; border-color: #f59e0b; color: #d97706; }
        .tooth-filled { background: #dbeafe; border-color: #3b82f6; color: #2563eb; }
        .tooth-crown { background: #fde68a; border-color: #f59e0b; color: #d97706; }
        .tooth-missing { background: #f3f4f6; border-color: #6b7280; color: #4b5563; }
        .tooth-root-canal { background: #fecaca; border-color: #ef4444; color: #dc2626; }
        .tooth-extraction { background: #fee2e2; border-color: #ef4444; color: #dc2626; }
        
        
        .visual-chart-container {
            position: relative;
            max-width: 100%;
            overflow-x: auto;
        }
        
        #dentalChartImage {
            max-width: 600px;
            width: 100%;
            height: auto;
        }
        
        @media (max-width: 768px) {
            #dentalChartImage {
                max-width: 450px;
            }
        }
        
        @media (max-width: 480px) {
            #dentalChartImage {
                max-width: 320px;
            }
        }
        
        .annotation-tools {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            padding: 12px;
            background: #f8fafc;
            border-radius: 8px;
            margin-bottom: 16px;
        }
        
        @media (max-width: 640px) {
            .annotation-tools {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }
            
            .annotation-tools > div {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                justify-content: center;
            }
        }
        
        .color-picker {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 2px solid #fff;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .color-picker:hover {
            transform: scale(1.1);
        }
        
        .color-picker.active {
            border-color: #374151;
            transform: scale(1.2);
        }
    </style>
</head>
<body class="admin-body bg-gray-50">
    <div class="min-h-screen flex">
        <?= view('templates/sidebar', ['user' => $user]) ?>
        
        <div class="flex-1 flex flex-col">
            <!-- Mobile-friendly Header -->
            <nav class="bg-white shadow-sm px-4 sm:px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <button id="sidebarToggleTop" class="block lg:hidden text-gray-600 mr-3 text-xl focus:outline-none">
                            <i class="fa fa-bars"></i>
                        </button>
                        <h1 class="text-lg sm:text-xl font-bold text-gray-800">
                            <i class="fas fa-tooth mr-2 text-green-600"></i>
                            <span class="hidden sm:inline">Dental Chart</span>
                            <span class="sm:hidden">Chart</span>
                        </h1>
                    </div>
                    <div class="flex items-center space-x-2 sm:space-x-4">
                        <span class="hidden sm:inline text-sm text-gray-600 font-medium"><?= $user['name'] ?? 'Admin' ?></span>
                        <div class="relative">
                            <img class="w-8 h-8 sm:w-10 sm:h-10 rounded-full border-2 border-gray-200" 
                                 src="<?= base_url('img/undraw_profile.svg') ?>" alt="Profile">
                        </div>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mt-2">Click on teeth to mark conditions and treatments</p>
            </nav>

            <main class="flex-1 px-4 sm:px-6 pb-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-6 mb-6">
                    <div class="bg-white border-l-4 border-green-400 shadow rounded-lg p-3 sm:p-5">
                        <div class="text-xs font-bold text-green-600 uppercase mb-1">Healthy</div>
                        <div class="text-lg sm:text-2xl font-bold text-gray-800">24</div>
                    </div>
                    <div class="bg-white border-l-4 border-yellow-400 shadow rounded-lg p-3 sm:p-5">
                        <div class="text-xs font-bold text-yellow-600 uppercase mb-1">Cavity</div>
                        <div class="text-lg sm:text-2xl font-bold text-gray-800">3</div>
                    </div>
                    <div class="bg-white border-l-4 border-blue-400 shadow rounded-lg p-3 sm:p-5">
                        <div class="text-xs font-bold text-blue-600 uppercase mb-1">Filled</div>
                        <div class="text-lg sm:text-2xl font-bold text-gray-800">4</div>
                    </div>
                    <div class="bg-white border-l-4 border-red-400 shadow rounded-lg p-3 sm:p-5">
                        <div class="text-xs font-bold text-red-600 uppercase mb-1">Missing</div>
                        <div class="text-lg sm:text-2xl font-bold text-gray-800">1</div>
                    </div>
                </div>
                
                <!-- Dental Chart Section -->
                <div class="bg-white rounded-lg shadow-sm mb-8">
                    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">
                            <i class="fas fa-list mr-3 text-blue-600"></i>Dental Chart
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">Permanent Dentition (Universal Numbering System)</p>
                    </div>
                    
                    <div class="p-4 sm:p-6">
                        <!-- Upper Arch -->
                        <div class="mb-8">
                            <h3 class="text-md font-medium text-gray-700 mb-4 text-center">Upper Arch (Maxilla)</h3>
                            <div class="tooth-grid mb-4">
                                <?php for($i = 1; $i <= 16; $i++): ?>
                                <div class="tooth-item tooth-healthy" data-tooth="<?= $i ?>" onclick="selectTooth(<?= $i ?>)">
                                    <div class="tooth-number"><?= $i ?></div>
                                    <div class="text-xs">Healthy</div>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <!-- Lower Arch -->
                        <div class="mb-6">
                            <h3 class="text-md font-medium text-gray-700 mb-4 text-center">Lower Arch (Mandible)</h3>
                            <div class="tooth-grid">
                                <?php for($i = 32; $i >= 17; $i--): ?>
                                <div class="tooth-item tooth-healthy" data-tooth="<?= $i ?>" onclick="selectTooth(<?= $i ?>)">
                                    <div class="tooth-number"><?= $i ?></div>
                                    <div class="text-xs">Healthy</div>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <!-- Legend -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Legend</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2 text-xs">
                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-green-200 border border-green-500 rounded mr-2"></div>
                                    <span>Healthy</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-yellow-200 border border-yellow-500 rounded mr-2"></div>
                                    <span>Cavity</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-blue-200 border border-blue-500 rounded mr-2"></div>
                                    <span>Filled</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-yellow-300 border border-yellow-600 rounded mr-2"></div>
                                    <span>Crown</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-gray-200 border border-gray-500 rounded mr-2"></div>
                                    <span>Missing</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-red-200 border border-red-500 rounded mr-2"></div>
                                    <span>Root Canal</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-red-300 border border-red-600 rounded mr-2"></div>
                                    <span>Extraction</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visual Dental Chart Section -->
                <div class="bg-white rounded-lg shadow-sm mb-8">
                    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">
                            <i class="fas fa-tooth mr-3 text-green-600"></i>Visual Dental Chart
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">Interactive dental chart with annotation tools</p>
                    </div>
                    
                    <div class="p-4 sm:p-6">
                        <!-- Annotation Tools -->
                        <div class="annotation-tools">
                            <div>
                                <button type="button" class="px-3 py-2 text-xs bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors" onclick="enableDrawing()">
                                    <i class="fas fa-pencil-alt mr-1"></i> Draw
                                </button>
                                <button type="button" class="px-3 py-2 text-xs bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors" onclick="enableEraser()">
                                    <i class="fas fa-eraser mr-1"></i> Erase
                                </button>
                                <button type="button" class="px-3 py-2 text-xs bg-red-500 text-white rounded hover:bg-red-600 transition-colors" onclick="clearDrawing()">
                                    <i class="fas fa-trash mr-1"></i> Clear
                                </button>
                            </div>
                            
                            <div>
                                <span class="text-xs font-medium text-gray-600 mr-2">Colors:</span>
                                <div class="color-picker active" style="background-color: #ef4444;" onclick="setColor('#ef4444')" data-color="#ef4444"></div>
                                <div class="color-picker" style="background-color: #3b82f6;" onclick="setColor('#3b82f6')" data-color="#3b82f6"></div>
                                <div class="color-picker" style="background-color: #10b981;" onclick="setColor('#10b981')" data-color="#10b981"></div>
                                <div class="color-picker" style="background-color: #f59e0b;" onclick="setColor('#f59e0b')" data-color="#f59e0b"></div>
                                <div class="color-picker" style="background-color: #8b5cf6;" onclick="setColor('#8b5cf6')" data-color="#8b5cf6"></div>
                                <div class="color-picker" style="background-color: #000000;" onclick="setColor('#000000')" data-color="#000000"></div>
                            </div>
                            
                            <div>
                                <span class="text-xs font-medium text-gray-600 mr-2">Size:</span>
                                <input type="range" id="brushSize" min="2" max="15" value="5" class="w-16 sm:w-20" onchange="setBrushSize(this.value)">
                                <span id="brushSizeDisplay" class="text-xs text-gray-500 ml-2">5px</span>
                            </div>
                        </div>
                        
                        <!-- Visual Chart Container -->
                        <div class="visual-chart-container bg-gray-50 rounded-lg p-4">
                            <div class="flex justify-center">
                                <div class="relative inline-block border-2 border-gray-200 rounded-lg overflow-hidden bg-white">
                                    <img id="dentalChartImage" src="<?= base_url('img/d.jpg') ?>" alt="Interactive Dental Chart" 
                                         class="block cursor-crosshair">
                                    <canvas id="drawingCanvas" class="absolute top-0 left-0 pointer-events-none"></canvas>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Chart Information -->
                        <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                            <h4 class="text-sm font-medium text-blue-900 mb-2">
                                <i class="fas fa-info-circle mr-2"></i>How to Use
                            </h4>
                            <ul class="text-sm text-blue-800 space-y-1">
                                <li>• Select drawing tool and color from the toolbar above</li>
                                <li>• Click and drag on the dental chart to annotate</li>
                                <li>• Use different colors to mark different conditions</li>
                                <li>• Adjust brush size for fine or broad annotations</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- 3D Dental Model Section -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">
                            <i class="fas fa-cube mr-3 text-purple-600"></i>3D Dental Model
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">Interactive 3D visualization of dental structure</p>
                    </div>
                    
                    <div class="p-4 sm:p-6">
                        <!-- 3D Model Container -->
                        <div class="bg-gray-900 rounded-lg overflow-hidden" style="height: 400px; min-height: 300px;">
                            <div id="dental3DViewer" class="w-full h-full flex items-center justify-center">
                                <div class="text-center text-gray-400">
                                    <i class="fas fa-cube fa-3x mb-4"></i>
                                    <h3 class="text-lg font-medium mb-2">3D Model Loading...</h3>
                                    <p class="text-sm">Interactive 3D dental model will appear here</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 3D Model Controls -->
                        <div class="mt-4 bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">3D Model Color Legend</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 text-xs">
                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-green-400 rounded mr-2"></div>
                                    <span>Healthy</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-red-400 rounded mr-2"></div>
                                    <span>Cavity</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-blue-400 rounded mr-2"></div>
                                    <span>Filled</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-yellow-400 rounded mr-2"></div>
                                    <span>Crown</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-orange-400 rounded mr-2"></div>
                                    <span>Root Canal</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-gray-400 rounded mr-2"></div>
                                    <span>Missing</span>
                                </div>
                            </div>
                            
                            <div class="mt-4 text-center">
                                <button class="px-4 py-2 bg-gray-600 text-white text-sm rounded hover:bg-gray-700 transition-colors mr-2">
                                    <i class="fas fa-expand mr-2"></i>Fullscreen
                                </button>
                                <button class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-redo mr-2"></i>Reset View
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>

        // Tooth selection functionality
        let selectedTooth = null;
        
        function selectTooth(toothNumber) {
            // Remove previous selection
            if (selectedTooth) {
                selectedTooth.classList.remove('ring-4', 'ring-blue-500', 'ring-opacity-50');
            }
            
            // Select new tooth
            const toothElement = document.querySelector(`[data-tooth="${toothNumber}"]`);
            if (toothElement) {
                toothElement.classList.add('ring-4', 'ring-blue-500', 'ring-opacity-50');
                selectedTooth = toothElement;
                
                // Show tooth condition modal or sidebar (can be implemented later)
                showToothConditionModal(toothNumber);
            }
        }
        
        function showToothConditionModal(toothNumber) {
            // Simple alert for now - can be replaced with a proper modal
            const conditions = ['Healthy', 'Cavity', 'Filled', 'Crown', 'Missing', 'Root Canal', 'Extraction'];
            const selectedCondition = prompt(`Select condition for tooth ${toothNumber}:\n${conditions.map((c, i) => `${i+1}. ${c}`).join('\n')}\n\nEnter number (1-7):`);
            
            if (selectedCondition && selectedCondition >= 1 && selectedCondition <= 7) {
                const condition = conditions[selectedCondition - 1].toLowerCase().replace(' ', '-');
                const toothElement = document.querySelector(`[data-tooth="${toothNumber}"]`);
                
                // Remove all condition classes
                toothElement.classList.remove('tooth-healthy', 'tooth-cavity', 'tooth-filled', 'tooth-crown', 'tooth-missing', 'tooth-root-canal', 'tooth-extraction');
                
                // Add new condition class
                toothElement.classList.add(`tooth-${condition}`);
                
                // Update text
                const textElement = toothElement.querySelector('.text-xs');
                if (textElement) {
                    textElement.textContent = conditions[selectedCondition - 1];
                }
                
                // Update statistics
                updateStatistics();
            }
        }
        
        function updateStatistics() {
            // Count teeth by condition
            const healthy = document.querySelectorAll('.tooth-healthy').length;
            const cavity = document.querySelectorAll('.tooth-cavity').length;
            const filled = document.querySelectorAll('.tooth-filled').length;
            const missing = document.querySelectorAll('.tooth-missing').length;
            
            // Update summary cards (simplified - you can expand this)
            console.log(`Statistics - Healthy: ${healthy}, Cavity: ${cavity}, Filled: ${filled}, Missing: ${missing}`);
        }

        // Visual Chart Drawing functionality
        let canvas, ctx;
        let isDrawing = false;
        let currentTool = 'draw';
        let currentColor = '#ef4444';
        let currentSize = 5;
        
        function initializeCanvas() {
            const img = document.getElementById('dentalChartImage');
            canvas = document.getElementById('drawingCanvas');
            
            if (!img || !canvas) return;
            
            // Wait for image to load
            img.onload = function() {
                setupCanvas();
            };
            
            // If image is already loaded
            if (img.complete) {
                setupCanvas();
            }
        }
        
        function setupCanvas() {
            const img = document.getElementById('dentalChartImage');
            ctx = canvas.getContext('2d');
            
            // Set canvas size to match image
            canvas.width = img.offsetWidth;
            canvas.height = img.offsetHeight;
            
            // Enable drawing
            canvas.style.pointerEvents = 'auto';
            
            // Mouse events
            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseout', stopDrawing);
            
            // Touch events for mobile
            canvas.addEventListener('touchstart', handleTouch);
            canvas.addEventListener('touchmove', handleTouch);
            canvas.addEventListener('touchend', stopDrawing);
        }
        
        function startDrawing(e) {
            isDrawing = true;
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            ctx.beginPath();
            ctx.moveTo(x, y);
        }
        
        function draw(e) {
            if (!isDrawing) return;
            
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            ctx.lineWidth = currentSize;
            ctx.lineCap = 'round';
            
            if (currentTool === 'draw') {
                ctx.globalCompositeOperation = 'source-over';
                ctx.strokeStyle = currentColor;
            } else if (currentTool === 'erase') {
                ctx.globalCompositeOperation = 'destination-out';
            }
            
            ctx.lineTo(x, y);
            ctx.stroke();
        }
        
        function stopDrawing() {
            isDrawing = false;
        }
        
        function handleTouch(e) {
            e.preventDefault();
            const touch = e.touches[0];
            const mouseEvent = new MouseEvent(e.type === 'touchstart' ? 'mousedown' : 
                                            e.type === 'touchmove' ? 'mousemove' : 'mouseup', {
                clientX: touch.clientX,
                clientY: touch.clientY
            });
            canvas.dispatchEvent(mouseEvent);
        }
        
        function enableDrawing() {
            currentTool = 'draw';
            canvas.style.cursor = 'crosshair';
        }
        
        function enableEraser() {
            currentTool = 'erase';
            canvas.style.cursor = 'grab';
        }
        
        function clearDrawing() {
            if (ctx) {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
        }
        
        function setColor(color) {
            currentColor = color;
            
            // Update active color picker
            document.querySelectorAll('.color-picker').forEach(picker => {
                picker.classList.remove('active');
            });
            document.querySelector(`[data-color="${color}"]`).classList.add('active');
        }
        
        function setBrushSize(size) {
            currentSize = size;
            document.getElementById('brushSizeDisplay').textContent = size + 'px';
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize canvas for visual chart
            initializeCanvas();
        });
        
        // Handle window resize for canvas
        window.addEventListener('resize', function() {
            setTimeout(initializeCanvas, 100);
        });
    </script>
</body>
</html>
