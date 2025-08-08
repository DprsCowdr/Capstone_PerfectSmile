/**
 * 3D Dental Model Viewer
 * Reusable component for displaying and interacting with 3D dental models
 */

class Dental3DViewer {
    constructor(containerId, options = {}) {
        this.containerId = containerId;
        this.modelUrl = options.modelUrl || '/img/permanent_dentition-2.glb';
        this.enableToothSelection = options.enableToothSelection !== false;
        this.showControls = options.showControls !== false;
        this.onToothClick = options.onToothClick || null;
        this.onModelLoaded = options.onModelLoaded || null;
        
        // Three.js objects
        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.controls = null;
        this.model = null;
        this.toothMeshes = [];
        
        // Raycasting for tooth selection
        this.raycaster = null;
        this.mouse = null;
        
        // State
        this.isLoaded = false;
        this.isWireframe = false;
        this.autoRotate = false;
        
        // Tooth mapping - mesh index to Universal Numbering System
        this.toothMapping = [
            24, 23, 22, 21, 20, 19, 18, 17, // 0-7: Lower left
            32, 31, 30, 28, 29, 27, 26, 25, // 8-15: Lower right
            8, 9, 10, 11, 12, 13, 7, 2,     // 16-23: Upper left/right mix
            15, 5, 4, 6, 3, 1, 12, 13, 16   // 24-32: Upper mix
        ];
        
        // Tooth names mapping
        this.toothNames = {
            1: '3rd Molar (Wisdom)', 2: '2nd Molar (12-yr)', 3: '1st Molar (6-yr)',
            4: '2nd Bicuspid', 5: '1st Bicuspid', 6: 'Cuspid (Canine)',
            7: 'Lateral Incisor', 8: 'Central Incisor', 9: 'Central Incisor',
            10: 'Lateral Incisor', 11: 'Cuspid (Canine)', 12: '1st Bicuspid',
            13: '2nd Bicuspid', 14: '1st Molar (6-yr)', 15: '2nd Molar (12-yr)',
            16: '3rd Molar (Wisdom)', 17: '3rd Molar (Wisdom)', 18: '2nd Molar (12-yr)',
            19: '1st Molar (6-yr)', 20: '2nd Bicuspid', 21: '1st Bicuspid',
            22: 'Cuspid (Canine)', 23: 'Lateral Incisor', 24: 'Central Incisor',
            25: 'Central Incisor', 26: 'Lateral Incisor', 27: 'Cuspid (Canine)',
            28: '1st Bicuspid', 29: '2nd Bicuspid', 30: '1st Molar (6-yr)',
            31: '2nd Molar (12-yr)', 32: '3rd Molar (Wisdom)'
        };
    }
    
    init() {
        const container = document.getElementById(this.containerId);
        if (!container) {
            console.error(`Container with ID '${this.containerId}' not found`);
            return false;
        }
        
        this.setupScene(container);
        this.setupLighting();
        this.setupControls();
        this.setupEventListeners();
        this.loadModel();
        this.animate();
        
        return true;
    }
    
    setupScene(container) {
        // Scene
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(0xf8fafc);
        
        // Camera - positioned closer for better view
        this.camera = new THREE.PerspectiveCamera(
            75, 
            container.clientWidth / container.clientHeight, 
            0.1, 
            1000
        );
        this.camera.position.set(0, 0, 3); // Moved closer from 5 to 3
        
        // Renderer
        const canvas = container.querySelector('canvas');
        this.renderer = new THREE.WebGLRenderer({ 
            canvas: canvas, 
            antialias: true 
        });
        this.renderer.setSize(container.clientWidth, container.clientHeight);
        this.renderer.setPixelRatio(window.devicePixelRatio);
        this.renderer.shadowMap.enabled = true;
        this.renderer.shadowMap.type = THREE.PCFSoftShadowMap;
        
        // Raycasting for click detection
        if (this.enableToothSelection) {
            this.raycaster = new THREE.Raycaster();
            this.mouse = new THREE.Vector2();
        }
    }
    
    setupLighting() {
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
        this.scene.add(ambientLight);
        
        const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
        directionalLight.position.set(10, 10, 5);
        directionalLight.castShadow = true;
        this.scene.add(directionalLight);
        
        const pointLight = new THREE.PointLight(0xffffff, 0.5);
        pointLight.position.set(-10, -10, -5);
        this.scene.add(pointLight);
    }
    
    setupControls() {
        this.controls = new THREE.OrbitControls(this.camera, this.renderer.domElement);
        this.controls.enableDamping = true;
        this.controls.dampingFactor = 0.05;
        this.controls.screenSpacePanning = false;
        this.controls.minDistance = 1.5; // Reduced from 2 to 1.5
        this.controls.maxDistance = 8; // Reduced from 20 to 8
        this.controls.maxPolarAngle = Math.PI;
        this.controls.target.set(0, 0, 0); // Center the target
    }
    
    setupEventListeners() {
        if (this.enableToothSelection) {
            // Handle both click and touch events for mobile compatibility
            this.renderer.domElement.addEventListener('click', (event) => {
                this.onCanvasClick(event);
            });
            
            // Add touch support for mobile devices
            this.renderer.domElement.addEventListener('touchend', (event) => {
                event.preventDefault();
                if (event.changedTouches.length === 1) {
                    // Convert touch event to click-like event
                    const touch = event.changedTouches[0];
                    const clickEvent = {
                        clientX: touch.clientX,
                        clientY: touch.clientY,
                        preventDefault: () => {},
                        stopPropagation: () => {}
                    };
                    this.onCanvasClick(clickEvent);
                }
            });
        }
        
        window.addEventListener('resize', () => {
            this.onWindowResize();
        });
        
        // Handle orientation change on mobile devices
        window.addEventListener('orientationchange', () => {
            setTimeout(() => {
                this.onWindowResize();
            }, 100);
        });
    }
    
    loadModel() {
        const container = document.getElementById(this.containerId);
        const loadingDiv = container.querySelector('.model-loading');
        const errorDiv = container.querySelector('.model-error');
        
        if (loadingDiv) loadingDiv.classList.remove('hidden');
        if (errorDiv) errorDiv.classList.add('hidden');
        
        const loader = new THREE.GLTFLoader();
        
        loader.load(
            this.modelUrl,
            (gltf) => {
                this.model = gltf.scene;
                this.processModel();
                this.scene.add(this.model);
                if (loadingDiv) loadingDiv.classList.add('hidden');
                if (this.onModelLoaded) {
                    this.onModelLoaded();
                }
            },
            (xhr) => {
                const percent = (xhr.loaded / xhr.total) * 100;
                if (loadingDiv) {
                    loadingDiv.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i>Loading 3D Model... ${Math.round(percent)}%`;
                }
            },
            (error) => {
                console.error('Error loading model:', error);
                if (loadingDiv) loadingDiv.classList.add('hidden');
                if (errorDiv) errorDiv.classList.remove('hidden');
            }
        );
    }
    
    processModel() {
        // Center and scale the model
        const box = new THREE.Box3().setFromObject(this.model);
        const center = box.getCenter(new THREE.Vector3());
        const size = box.getSize(new THREE.Vector3());
        
        const maxDim = Math.max(size.x, size.y, size.z);
        const scale = 4 / maxDim; // Increased from 3 to 4 for larger appearance
        this.model.scale.setScalar(scale);
        this.model.position.sub(center.multiplyScalar(scale));
        
        // Process teeth for click detection
        this.processTeethForClickDetection();
        
        // Add shadows
        this.model.traverse((child) => {
            if (child.isMesh) {
                child.castShadow = true;
                child.receiveShadow = true;
            }
        });
    }
    
    processTeethForClickDetection() {
        this.toothMeshes = [];
        
        this.model.traverse((child) => {
            if (child.isMesh) {
                this.toothMeshes.push(child);
            }
        });
        
        console.log(`Found ${this.toothMeshes.length} tooth meshes for click detection`);
    }
    
    onCanvasClick(event) {
        if (!this.raycaster || !this.mouse) return;
        
        const canvas = this.renderer.domElement;
        const rect = canvas.getBoundingClientRect();
        
        this.mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
        this.mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;
        
        this.raycaster.setFromCamera(this.mouse, this.camera);
        const intersects = this.raycaster.intersectObjects(this.toothMeshes, true);
        
        if (intersects.length > 0) {
            const clickedMesh = intersects[0].object;
            const clickPoint = intersects[0].point;
            const meshIndex = this.toothMeshes.indexOf(clickedMesh);
            const toothNumber = this.mapMeshIndexToToothNumber(meshIndex);
            
            if (toothNumber) {
                this.highlightTooth(meshIndex);
                
                if (this.onToothClick) {
                    this.onToothClick(toothNumber, clickPoint, event, {
                        meshIndex,
                        toothName: this.toothNames[toothNumber]
                    });
                }
            }
        } else {
            // Reset only highlights, keep condition colors
            this.resetHighlights();
        }
    }
    
    mapMeshIndexToToothNumber(meshIndex) {
        if (meshIndex < this.toothMapping.length && this.toothMapping[meshIndex]) {
            return this.toothMapping[meshIndex];
        }
        return (meshIndex % 32) + 1;
    }
    
    highlightTooth(toothIndex) {
        // Reset all teeth to their condition colors first
        this.resetHighlights();
        
        if (!this.toothMeshes || toothIndex < 0 || toothIndex >= this.toothMeshes.length) return;
        
        const mesh = this.toothMeshes[toothIndex];
        if (mesh && mesh.material) {
            // Store current material as temporary
            if (!mesh.userData.tempMaterial) {
                mesh.userData.tempMaterial = mesh.material.clone();
            }
            
            // Check if this is a missing tooth (completely transparent)
            const isMissingTooth = mesh.userData.conditionMaterial && 
                                 mesh.userData.conditionMaterial.opacity === 0.0;
            
            // Create bright yellow highlight material
            const highlightMaterial = new THREE.MeshStandardMaterial({
                color: 0xffff00, // Bright yellow highlight
                transparent: true,
                opacity: isMissingTooth ? 0.6 : 0.9, // Less opacity for missing teeth but still visible
                metalness: 0.2,
                roughness: 0.2,
                emissive: 0x444400, // Slight glow effect
                depthWrite: true // Make sure highlight is visible
            });
            
            mesh.material = highlightMaterial;
        }
    }
    
    setToothColor(toothNumber, color) {
        if (!this.toothMeshes) return;
        
        // Find the mesh index for this tooth number
        const meshIndex = this.getMeshIndexFromToothNumber(toothNumber);
        if (meshIndex === -1) return;
        
        const mesh = this.toothMeshes[meshIndex];
        if (!mesh || !mesh.material) return;
        
        // Store original material if not already stored
        if (!mesh.userData.originalMaterial) {
            mesh.userData.originalMaterial = mesh.material.clone();
        }
        
        if (color) {
            // Check if this is a missing tooth (black color = missing)
            const isMissingTooth = color.r === 0.0 && color.g === 0.0 && color.b === 0.0;
            
            // Create permanent condition color material
            const conditionMaterial = new THREE.MeshStandardMaterial({
                color: new THREE.Color(color.r, color.g, color.b),
                transparent: isMissingTooth || color.r === 0.3 && color.g === 0.3 && color.b === 0.3,
                opacity: isMissingTooth ? 0.0 : 1.0, // Completely invisible for missing teeth
                metalness: 0.1,
                roughness: 0.4,
                // Add slight emissive glow for better visibility (except for missing teeth)
                emissive: isMissingTooth ? 
                    new THREE.Color(0, 0, 0) : // No glow for missing teeth
                    new THREE.Color(color.r * 0.1, color.g * 0.1, color.b * 0.1),
                // Disable depth write for missing teeth so they don't block other teeth
                depthWrite: !isMissingTooth
            });
            
            mesh.material = conditionMaterial;
            // Store this as the condition material
            mesh.userData.conditionMaterial = conditionMaterial.clone();
        } else {
            // Reset to original material
            if (mesh.userData.originalMaterial) {
                mesh.material = mesh.userData.originalMaterial.clone();
                mesh.userData.conditionMaterial = null;
            }
        }
    }
    
    resetHighlights() {
        // Reset only the highlight effects, keep condition colors
        this.toothMeshes.forEach((tooth) => {
            // If tooth has a condition color, restore it
            if (tooth.userData.conditionMaterial) {
                tooth.material = tooth.userData.conditionMaterial.clone();
            } 
            // Otherwise restore original material
            else if (tooth.userData.originalMaterial) {
                tooth.material = tooth.userData.originalMaterial.clone();
            }
            
            // Clear temporary highlight material
            tooth.userData.tempMaterial = null;
        });
    }
    
    resetAllTeethColor() {
        // Reset all teeth to original materials (removes both highlights and condition colors)
        this.toothMeshes.forEach((tooth) => {
            if (tooth.userData.originalMaterial) {
                tooth.material = tooth.userData.originalMaterial.clone();
                tooth.userData.conditionMaterial = null;
                tooth.userData.tempMaterial = null;
            }
        });
    }
    
    getMeshIndexFromToothNumber(toothNumber) {
        // Convert tooth number to mesh index using the tooth mapping
        const toothMapping = [
            24, // 0 - lower left central incisor (#24)
            23, // 1 - lower left lateral incisor (#23)
            22, // 2 - lower left canine (#22)
            21, // 3 - lower left first premolar (#21)
            20, // 4 - lower left second premolar (#20)
            19, // 5 - lower left first molar (#19)
            18, // 6 - lower left second molar (#18)
            17, // 7 - lower left third molar (#17)
            32, // 8 - lower right third molar (#32)
            31, // 9 - lower right second molar (#31)
            30, // 10 - lower right first molar (#30)
            28, // 11 - lower right second premolar (#28)
            29, // 12 - lower right first premolar (#29)
            27, // 13 - lower right canine (#27)
            26, // 14 - lower right lateral incisor (#26)
            25, // 15 - lower right central incisor (#25)
            8,  // 16 - upper left central incisor (#8)
            9,  // 17 - upper left lateral incisor (#9)
            10, // 18 - upper left canine (#10)
            11, // 19 - upper left first premolar (#11)
            12, // 20 - upper left second premolar (#12)
            13, // 21 - upper left first molar (#13)
            7,  // 22 - upper left canine (#7)
            2,  // 23 - upper right lateral incisor (#2)
            15, // 24 - upper left second molar (#15)
            5,  // 25 - upper right first premolar (#5)
            4,  // 26 - upper right second premolar (#4)
            6,  // 27 - upper right canine (#6)
            3,  // 28 - upper right first molar (#3)
            1,  // 29 - upper right third molar (#1)
            12, // 30 - upper left second premolar (#12)
            13, // 31 - upper left first molar (#13)
            16  // 32 - upper left third molar (#16)
        ];
        
        // Find the index where the tooth number matches
        return toothMapping.findIndex(mappedNumber => mappedNumber === toothNumber);
    }
    
    animate() {
        requestAnimationFrame(() => this.animate());
        
        if (this.controls) {
            this.controls.update();
        }
        
        if (this.renderer && this.scene && this.camera) {
            this.renderer.render(this.scene, this.camera);
        }
    }
    
    onWindowResize() {
        const container = document.getElementById(this.containerId);
        if (this.camera && this.renderer && container) {
            this.camera.aspect = container.clientWidth / container.clientHeight;
            this.camera.updateProjectionMatrix();
            this.renderer.setSize(container.clientWidth, container.clientHeight);
        }
    }
    
    // Public methods for external control
    resetCamera() {
        if (this.controls) {
            this.controls.reset();
        }
    }
    
    toggleWireframe() {
        if (this.model) {
            this.isWireframe = !this.isWireframe;
            this.model.traverse((child) => {
                if (child.isMesh) {
                    child.material.wireframe = this.isWireframe;
                }
            });
        }
    }
    
    toggleAutoRotate() {
        if (this.controls) {
            this.autoRotate = !this.autoRotate;
            this.controls.autoRotate = this.autoRotate;
            this.controls.autoRotateSpeed = 2.0;
        }
    }
    
    getToothName(toothNumber) {
        return this.toothNames[toothNumber] || 'Unknown';
    }
    
    destroy() {
        if (this.renderer) {
            this.renderer.dispose();
        }
        if (this.controls) {
            this.controls.dispose();
        }
        window.removeEventListener('resize', this.onWindowResize);
    }
}

// Export for use in other files
window.Dental3DViewer = Dental3DViewer; 