<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D Dental Viewer Example</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Three.js Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
    
    <!-- 3D Dental Viewer -->
    <link rel="stylesheet" href="<?= base_url('css/dental-3d-viewer.css') ?>">
    <script src="<?= base_url('js/dental-3d-viewer.js') ?>"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">3D Dental Viewer Examples</h1>
        
        <!-- Example 1: Basic Viewer -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Basic 3D Dental Model</h2>
            <div id="basicViewer" class="dental-3d-viewer">
                <div class="model-loading">
                    <i class="fas fa-spinner fa-spin mr-2"></i>Loading 3D Model...
                </div>
                <div class="model-error hidden">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <div>Failed to load 3D model</div>
                    <button onclick="location.reload()" class="mt-2 px-3 py-1 bg-blue-500 text-white rounded text-sm">Retry</button>
                </div>
                <canvas class="dental-3d-canvas"></canvas>
                
                <div class="model-controls">
                    <button class="model-control-btn" onclick="basicViewer.resetCamera()" title="Reset View">
                        <i class="fas fa-home"></i>
                    </button>
                    <button class="model-control-btn" onclick="basicViewer.toggleWireframe()" title="Toggle Wireframe">
                        <i class="fas fa-border-all"></i>
                    </button>
                    <button class="model-control-btn" onclick="basicViewer.toggleAutoRotate()" title="Auto Rotate">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Example 2: Interactive Viewer with Tooth Selection -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Interactive 3D Dental Model</h2>
            <p class="text-gray-600 mb-4">Click on teeth to see information</p>
            
            <div id="interactiveViewer" class="dental-3d-viewer">
                <div class="model-loading">
                    <i class="fas fa-spinner fa-spin mr-2"></i>Loading 3D Model...
                </div>
                <div class="model-error hidden">
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
                        <button class="treatment-popup-close" onclick="closePopup()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="treatment-popup-content">
                        <div class="treatment-popup-icon">
                            <i class="fas fa-tooth"></i>
                        </div>
                        <div class="treatment-popup-message" id="popupMessage">Click on a tooth to see details</div>
                        <button class="treatment-popup-button" onclick="showToothDetails()">
                            <i class="fas fa-info mr-2"></i>More Details
                        </button>
                    </div>
                </div>
                
                <div class="model-controls">
                    <button class="model-control-btn" onclick="interactiveViewer.resetCamera()" title="Reset View">
                        <i class="fas fa-home"></i>
                    </button>
                    <button class="model-control-btn" onclick="interactiveViewer.toggleWireframe()" title="Toggle Wireframe">
                        <i class="fas fa-border-all"></i>
                    </button>
                    <button class="model-control-btn" onclick="interactiveViewer.toggleAutoRotate()" title="Auto Rotate">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            
            <!-- Selected Tooth Info -->
            <div id="selectedToothInfo" class="mt-4 p-4 bg-gray-50 rounded-lg hidden">
                <h3 class="font-semibold text-lg mb-2">Selected Tooth Information</h3>
                <div id="toothDetails" class="text-gray-600">
                    No tooth selected
                </div>
            </div>
        </div>
        
        <!-- Example 3: Compact Viewer -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Compact 3D Dental Model</h2>
            <div id="compactViewer" class="dental-3d-viewer" style="height: 300px;">
                <div class="model-loading">
                    <i class="fas fa-spinner fa-spin mr-2"></i>Loading 3D Model...
                </div>
                <div class="model-error hidden">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <div>Failed to load 3D model</div>
                    <button onclick="location.reload()" class="mt-2 px-3 py-1 bg-blue-500 text-white rounded text-sm">Retry</button>
                </div>
                <canvas class="dental-3d-canvas"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Global viewer instances
        let basicViewer, interactiveViewer, compactViewer;
        let selectedToothNumber = null;

        // Initialize viewers when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Basic viewer (no tooth selection)
            basicViewer = new Dental3DViewer('basicViewer', {
                enableToothSelection: false,
                showControls: true
            });
            basicViewer.init();

            // Interactive viewer with tooth selection
            interactiveViewer = new Dental3DViewer('interactiveViewer', {
                enableToothSelection: true,
                showControls: true,
                onToothClick: function(toothNumber, clickPoint, event, data) {
                    handleToothClick(toothNumber, clickPoint, event, data);
                }
            });
            interactiveViewer.init();

            // Compact viewer (no controls, no tooth selection)
            compactViewer = new Dental3DViewer('compactViewer', {
                enableToothSelection: false,
                showControls: false
            });
            compactViewer.init();
        });

        // Handle tooth click in interactive viewer
        function handleToothClick(toothNumber, clickPoint, event, data) {
            selectedToothNumber = toothNumber;
            
            // Show popup
            showToothPopup(toothNumber, clickPoint, event, data);
            
            // Update selected tooth info
            updateSelectedToothInfo(toothNumber, data);
        }

        function showToothPopup(toothNumber, worldPosition, event, data) {
            const popup = document.getElementById('treatmentPopup');
            const highlight = document.getElementById('toothHighlight');
            const title = document.getElementById('popupTitle');
            const message = document.getElementById('popupMessage');

            // Update popup content
            title.textContent = `Tooth ${toothNumber} - ${data.toothName}`;
            message.innerHTML = `
                <div class="text-left">
                    <div class="mb-2"><strong>Tooth Number:</strong> ${toothNumber}</div>
                    <div class="mb-2"><strong>Name:</strong> ${data.toothName}</div>
                    <div class="mb-2"><strong>Mesh Index:</strong> ${data.meshIndex}</div>
                </div>
            `;

            // Position the popup
            const canvas = document.querySelector('#interactiveViewer canvas');
            const rect = canvas.getBoundingClientRect();
            
            const vector = worldPosition.clone();
            vector.project(interactiveViewer.camera);
            
            const x = (vector.x * 0.5 + 0.5) * rect.width;
            const y = (vector.y * -0.5 + 0.5) * rect.height;
            
            popup.style.left = (x + 20) + 'px';
            popup.style.top = (y - 100) + 'px';
            popup.style.display = 'block';

            // Show highlight
            if (highlight) {
                highlight.style.left = (x - 10) + 'px';
                highlight.style.top = (y - 10) + 'px';
                highlight.style.display = 'block';
            }
        }

        function updateSelectedToothInfo(toothNumber, data) {
            const infoDiv = document.getElementById('selectedToothInfo');
            const detailsDiv = document.getElementById('toothDetails');
            
            detailsDiv.innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <strong>Tooth Number:</strong> ${toothNumber}
                    </div>
                    <div>
                        <strong>Name:</strong> ${data.toothName}
                    </div>
                    <div>
                        <strong>Mesh Index:</strong> ${data.meshIndex}
                    </div>
                    <div>
                        <strong>Type:</strong> ${getToothType(toothNumber)}
                    </div>
                </div>
            `;
            
            infoDiv.classList.remove('hidden');
        }

        function getToothType(toothNumber) {
            if ([1, 16, 17, 32].includes(toothNumber)) return 'Wisdom Tooth';
            if ([2, 15, 18, 31].includes(toothNumber)) return 'Second Molar';
            if ([3, 14, 19, 30].includes(toothNumber)) return 'First Molar';
            if ([4, 13, 20, 29].includes(toothNumber)) return 'Second Premolar';
            if ([5, 12, 21, 28].includes(toothNumber)) return 'First Premolar';
            if ([6, 11, 22, 27].includes(toothNumber)) return 'Canine';
            if ([7, 10, 23, 26].includes(toothNumber)) return 'Lateral Incisor';
            if ([8, 9, 24, 25].includes(toothNumber)) return 'Central Incisor';
            return 'Unknown';
        }

        function closePopup() {
            const popup = document.getElementById('treatmentPopup');
            const highlight = document.getElementById('toothHighlight');
            
            if (popup) popup.style.display = 'none';
            if (highlight) highlight.style.display = 'none';
            
            // Reset tooth color
            if (interactiveViewer) {
                interactiveViewer.resetAllTeethColor();
            }
        }

        function showToothDetails() {
            if (selectedToothNumber) {
                alert(`Showing detailed information for Tooth #${selectedToothNumber}`);
                closePopup();
            }
        }

        // Close popup when clicking outside
        document.addEventListener('click', function(event) {
            const popup = document.getElementById('treatmentPopup');
            const canvas = document.querySelector('#interactiveViewer canvas');
            
            if (popup && popup.style.display === 'block' && 
                !popup.contains(event.target) && 
                (!canvas || !canvas.contains(event.target))) {
                closePopup();
            }
        });
    </script>
</body>
</html> 