<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D Viewer Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= base_url('css/dental-3d-viewer.css') ?>">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">3D Dental Viewer Test</h1>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Test 3D Dental Model</h2>
            
            <!-- 3D Viewer Container -->
            <div class="dental-3d-viewer-container">
                <div id="testDentalViewer" class="dental-3d-viewer" style="height: 400px;">
                    <div class="model-loading" id="testLoading">
                        <i class="fas fa-spinner fa-spin mr-2"></i>Loading 3D Model...
                    </div>
                    <div class="model-error hidden" id="testError">
                        <i class="fas fa-exclamation-triangle mr-2 text-red-500"></i>
                        <div class="text-red-600">Failed to load 3D model</div>
                    </div>
                    <canvas class="dental-3d-canvas"></canvas>
                </div>
            </div>
            
            <div class="mt-4">
                <button id="testInit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    Initialize 3D Viewer
                </button>
                <button id="testRefresh" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 ml-2">
                    Refresh
                </button>
            </div>
            
            <div id="testStatus" class="mt-4 p-4 bg-gray-50 rounded-lg">
                <h3 class="font-semibold mb-2">Status:</h3>
                <ul id="statusList" class="space-y-1 text-sm">
                    <li>Initializing...</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Three.js and dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
    <script src="<?= base_url('js/dental-3d-viewer.js') ?>"></script>
    
    <script>
        let testViewer = null;
        const statusList = document.getElementById('statusList');
        
        function addStatus(message, type = 'info') {
            const li = document.createElement('li');
            li.className = type === 'error' ? 'text-red-600' : type === 'success' ? 'text-green-600' : 'text-gray-700';
            li.textContent = `${new Date().toLocaleTimeString()}: ${message}`;
            statusList.appendChild(li);
            statusList.scrollTop = statusList.scrollHeight;
        }
        
        function initTest() {
            addStatus('Starting 3D viewer test...');
            
            // Check dependencies
            if (typeof THREE === 'undefined') {
                addStatus('THREE.js not loaded!', 'error');
                return;
            }
            addStatus('THREE.js loaded successfully', 'success');
            
            if (typeof Dental3DViewer === 'undefined') {
                addStatus('Dental3DViewer not loaded!', 'error');
                return;
            }
            addStatus('Dental3DViewer loaded successfully', 'success');
            
            try {
                // Clean up existing viewer
                if (testViewer) {
                    addStatus('Cleaning up existing viewer...');
                    testViewer.destroy();
                    testViewer = null;
                }
                
                // Create new viewer
                addStatus('Creating new Dental3DViewer...');
                testViewer = new Dental3DViewer('testDentalViewer', {
                    enableInteraction: true,
                    showControls: true,
                    autoRotate: false
                });
                
                // Initialize viewer
                addStatus('Initializing viewer...');
                const result = testViewer.init();
                
                if (result) {
                    addStatus('3D viewer initialized successfully!', 'success');
                } else {
                    addStatus('3D viewer initialization failed!', 'error');
                }
                
            } catch (error) {
                addStatus(`Error: ${error.message}`, 'error');
                console.error('Test error:', error);
            }
        }
        
        // Event listeners
        document.getElementById('testInit').addEventListener('click', initTest);
        document.getElementById('testRefresh').addEventListener('click', () => {
            location.reload();
        });
        
        // Auto-initialize when page loads
        document.addEventListener('DOMContentLoaded', () => {
            addStatus('Page loaded, checking dependencies...');
            setTimeout(initTest, 1000); // Wait a bit for all scripts to load
        });
    </script>
</body>
</html>
