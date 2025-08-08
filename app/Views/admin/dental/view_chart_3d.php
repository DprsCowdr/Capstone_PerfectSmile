<!-- 3D Dental Model Viewer Component -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">
        <i class="fas fa-cube mr-2 text-blue-600"></i>3D Dental Model Viewer
    </h3>
    <p class="text-gray-600 mb-6">Interactive 3D model of permanent dentition. Use mouse to rotate, scroll to zoom, and right-click to pan.</p>
    
    <div id="dentalModelViewer" style="width: 100%; height: 400px; border-radius: 12px; overflow: hidden; position: relative; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
        <div id="modelLoading" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #6b7280; font-size: 14px; z-index: 5;">
            <i class="fas fa-spinner fa-spin mr-2"></i>Loading 3D Model...
        </div>
        <div id="modelError" class="hidden" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #ef4444; font-size: 14px; text-align: center; z-index: 5;">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <div>Failed to load 3D model</div>
            <button onclick="loadDentalModel()" class="mt-2 px-3 py-1 bg-blue-500 text-white rounded text-sm">Retry</button>
        </div>
        <canvas id="dentalModelCanvas" style="width: 100%; height: 100%; display: block;"></canvas>
        <div class="model-controls" style="position: absolute; top: 10px; right: 10px; display: flex; flex-direction: column; gap: 8px; z-index: 10;">
            <button class="model-control-btn" onclick="resetCamera()" title="Reset View" style="width: 40px; height: 40px; border: none; border-radius: 8px; background: rgba(255, 255, 255, 0.9); color: #374151; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s; backdrop-filter: blur(10px);">
                <i class="fas fa-home"></i>
            </button>
            <button class="model-control-btn" onclick="toggleWireframe()" title="Toggle Wireframe" style="width: 40px; height: 40px; border: none; border-radius: 8px; background: rgba(255, 255, 255, 0.9); color: #374151; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s; backdrop-filter: blur(10px);">
                <i class="fas fa-border-all"></i>
            </button>
            <button class="model-control-btn" onclick="toggleAutoRotate()" title="Auto Rotate" style="width: 40px; height: 40px; border: none; border-radius: 8px; background: rgba(255, 255, 255, 0.9); color: #374151; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s; backdrop-filter: blur(10px);">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>
</div>

<script>
// Three.js Variables
let scene, camera, renderer, controls, model;
let autoRotate = false;
let wireframeMode = false;

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

// Initialize when the component is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if Three.js is loaded
    if (typeof THREE !== 'undefined') {
        initDentalModel();
    } else {
        console.error('Three.js not loaded');
    }
});
</script> 