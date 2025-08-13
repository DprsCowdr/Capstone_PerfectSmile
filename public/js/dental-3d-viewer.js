/**
 * 3D Dental Model Viewer
 * Reusable component for displaying and interacting with 3D dental models
 */

class Dental3DViewer {
    constructor(containerId, options = {}) {
        this.containerId = containerId;
        this.modelUrl = options.modelUrl || '/img/permanent_dentition-2.glb';
        this.enableToothSelection = options.enableToothSelection !== false;
        this.enableHover = options.enableHover !== false; // New option for hover
        this.showControls = options.showControls !== false;
        this.onToothClick = options.onToothClick || null;
        this.onToothHover = options.onToothHover || null;
        this.onModelLoaded = options.onModelLoaded || null;
        
        // Three.js objects
        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.controls = null;
        this.model = null;
        this.toothMeshes = [];
        
        // Raycasting for tooth selection and hover
        this.raycaster = null;
        this.mouse = null;
        
        // Hover and tooltip state
        this.hoveredTooth = null;
        this.tooltip = null;
        this.toothData = new Map(); // Store tooth checkup data
        
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
        
        // Enhanced tooth names mapping with proper dental terminology
        this.toothNames = {
            1: 'Upper Right Third Molar (Wisdom Tooth)', 2: 'Upper Right Second Molar', 3: 'Upper Right First Molar',
            4: 'Upper Right Second Premolar (Bicuspid)', 5: 'Upper Right First Premolar (Bicuspid)', 6: 'Upper Right Canine (Cuspid)',
            7: 'Upper Right Lateral Incisor', 8: 'Upper Right Central Incisor', 9: 'Upper Left Central Incisor',
            10: 'Upper Left Lateral Incisor', 11: 'Upper Left Canine (Cuspid)', 12: 'Upper Left First Premolar (Bicuspid)',
            13: 'Upper Left Second Premolar (Bicuspid)', 14: 'Upper Left First Molar', 15: 'Upper Left Second Molar',
            16: 'Upper Left Third Molar (Wisdom Tooth)', 17: 'Lower Left Third Molar (Wisdom Tooth)', 18: 'Lower Left Second Molar',
            19: 'Lower Left First Molar', 20: 'Lower Left Second Premolar (Bicuspid)', 21: 'Lower Left First Premolar (Bicuspid)',
            22: 'Lower Left Canine (Cuspid)', 23: 'Lower Left Lateral Incisor', 24: 'Lower Left Central Incisor',
            25: 'Lower Right Central Incisor', 26: 'Lower Right Lateral Incisor', 27: 'Lower Right Canine (Cuspid)',
            28: 'Lower Right First Premolar (Bicuspid)', 29: 'Lower Right Second Premolar (Bicuspid)', 30: 'Lower Right First Molar',
            31: 'Lower Right Second Molar', 32: 'Lower Right Third Molar (Wisdom Tooth)'
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
            
            // Add hover functionality
            this.renderer.domElement.addEventListener('mousemove', (event) => {
                this.onCanvasMouseMove(event);
            });
            
            this.renderer.domElement.addEventListener('mouseleave', () => {
                this.onCanvasMouseLeave();
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
        
        // Create tooltip element
        this.createTooltip();
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
        // If tooth selection is disabled (view-only mode), don't allow clicking
        if (!this.enableToothSelection) {
            return;
        }
        
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
                // NO HIGHLIGHTING - just trigger the callback for the panel
                if (this.onToothClick) {
                    this.onToothClick(toothNumber, clickPoint, event, {
                        meshIndex,
                        toothName: this.toothNames[toothNumber],
                        toothData: this.toothData.get(toothNumber) // Include tooth data in callback
                    });
                }
            }
        }
        // Removed the reset highlights section - no highlighting at all
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
    
    setToothColor(toothNumber, color, isMissing = false) {
        console.log(`🦷 setToothColor called: tooth ${toothNumber}, color:`, color, `missing: ${isMissing}`);
        
        // Auto-detect missing from null color if not explicitly specified
        if (color === null && isMissing === false) {
            isMissing = true;
        }
        
        if (!this.toothMeshes) {
            console.log('❌ No tooth meshes available');
            return;
        }
        
        // Find the mesh index for this tooth number
        const meshIndex = this.getMeshIndexFromToothNumber(toothNumber);
        console.log(`🔍 Tooth ${toothNumber} -> mesh index: ${meshIndex}`);
        
        if (meshIndex === -1) {
            console.log(`❌ Invalid mesh index for tooth ${toothNumber}, trying alternative approach`);
            
            // FALLBACK: Apply a distinctive color to help identify which tooth should change
            // This helps with debugging the mesh mapping
            this.applyFallbackToothColor(toothNumber, color, isMissing);
            return;
        }
        
        const mesh = this.toothMeshes[meshIndex];
        if (!mesh || !mesh.material) {
            console.log(`❌ No mesh or material found at index ${meshIndex} for tooth ${toothNumber}`);
            return;
        }
        
        console.log(`✅ Found mesh for tooth ${toothNumber} at index ${meshIndex}`);
        
        // Store original material if not already stored
        if (!mesh.userData.originalMaterial) {
            mesh.userData.originalMaterial = mesh.material.clone();
        }

        console.log(`🎨 Processing tooth ${toothNumber}: color:`, color, `missing: ${isMissing}`);
        
        // Handle missing teeth first (highest priority)
        if (isMissing || color === null) {
            console.log(`👻 HIDING missing tooth ${toothNumber}`);
            mesh.visible = false;
            mesh.userData.isHidden = true;
            mesh.userData.toothNumber = toothNumber;
            mesh.userData.condition = 'missing';
            
            // Force a render update
            if (this.renderer) {
                this.renderer.render(this.scene, this.camera);
                console.log(`🔄 Forced render update for hidden tooth ${toothNumber}`);
            }
            
            console.log(`✅ Missing tooth ${toothNumber} hidden successfully`);
            return true;
        }
        
        // Ensure tooth is visible for non-missing conditions
        mesh.visible = true;
        mesh.userData.isHidden = false;
        
        // Create permanent condition color material with enhanced visibility
        const conditionMaterial = new THREE.MeshStandardMaterial({
            color: new THREE.Color(color.r, color.g, color.b),
            transparent: false, // No transparency for visible teeth
            opacity: 1.0, // Full opacity for visible teeth
            metalness: 0.1,
            roughness: 0.3, // Less rough for better color visibility
            // Enhanced emissive glow for better visibility
            emissive: new THREE.Color(color.r * 0.4, color.g * 0.4, color.b * 0.4),
            // Keep depth write enabled for better rendering
            depthWrite: true,
            // Ensure material renders from all angles
            side: THREE.DoubleSide
        });
        
        mesh.material = conditionMaterial;
        // Store this as the condition material
        mesh.userData.conditionMaterial = conditionMaterial.clone();
        mesh.userData.toothNumber = toothNumber;
        mesh.userData.condition = color;
        
        console.log(`✅ Material applied to tooth ${toothNumber}`);
        
        // Force a render update
        if (this.renderer) {
            this.renderer.render(this.scene, this.camera);
            console.log(`🔄 Forced render update for tooth ${toothNumber}`);
        }
        
        console.log(`✅ Tooth ${toothNumber} processing completed successfully`);
        return true;
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
        console.log(`🔍 Looking up mesh index for tooth number: ${toothNumber}`);
        
        // The tooth mapping needs to be corrected for proper indexing
        // Based on the checkup data, we have teeth 8 and 26 with conditions
        // Let's create a direct mapping that works with the 3D model
        
        const toothToMeshMap = {
            // Try different possible numbering systems
            1: 0, 2: 1, 3: 2, 4: 3, 5: 4, 6: 5, 7: 6, 8: 7,
            9: 8, 10: 9, 11: 10, 12: 11, 13: 12, 14: 13, 15: 14, 16: 15,
            17: 16, 18: 17, 19: 18, 20: 19, 21: 20, 22: 21, 23: 22, 24: 23,
            25: 24, 26: 25, 27: 26, 28: 27, 29: 28, 30: 29, 31: 30, 32: 31,
            
            // Alternative FDI numbering
            11: 16, 12: 17, 13: 18, 14: 19, 15: 20, 16: 21, 17: 22, 18: 23,
            21: 24, 22: 25, 23: 26, 24: 27, 25: 28, 26: 29, 27: 30, 28: 31,
            31: 0, 32: 1, 33: 2, 34: 3, 35: 4, 36: 5, 37: 6, 38: 7,
            41: 8, 42: 9, 43: 10, 44: 11, 45: 12, 46: 13, 47: 14, 48: 15
        };
        
        let meshIndex = toothToMeshMap[toothNumber];
        
        if (meshIndex !== undefined) {
            console.log(`✅ Tooth ${toothNumber} mapped to mesh index: ${meshIndex}`);
            // Validate the mesh exists
            if (this.toothMeshes && this.toothMeshes[meshIndex]) {
                return meshIndex;
            }
        }
        
        // If direct mapping failed, try to find a mesh by searching
        if (this.toothMeshes) {
            console.log(`❌ Direct mapping failed for tooth ${toothNumber}, searching through ${this.toothMeshes.length} meshes`);
            
            // Try simple sequential mapping as fallback
            for (let i = 0; i < Math.min(this.toothMeshes.length, 32); i++) {
                if (this.toothMeshes[i]) {
                    console.log(`Mesh ${i}: name="${this.toothMeshes[i].name || 'unnamed'}"`);
                }
            }
            
            // For teeth 8 and 26, try some common indices
            if (toothNumber === 8) {
                const possibleIndices = [7, 8, 16, 23]; // Common positions for tooth 8
                for (const idx of possibleIndices) {
                    if (this.toothMeshes[idx]) {
                        console.log(`🎯 Using fallback index ${idx} for tooth 8`);
                        return idx;
                    }
                }
            }
            
            if (toothNumber === 26) {
                const possibleIndices = [25, 26, 29, 5]; // Common positions for tooth 26  
                for (const idx of possibleIndices) {
                    if (this.toothMeshes[idx]) {
                        console.log(`🎯 Using fallback index ${idx} for tooth 26`);
                        return idx;
                    }
                }
            }
        }
        
        console.log(`❌ No valid mesh found for tooth ${toothNumber}`);
        return -1;
    }
    
    // Fallback method to apply colors when mesh mapping fails
    applyFallbackToothColor(toothNumber, color, isMissing = false) {
        console.log(`🚨 FALLBACK: Attempting to handle tooth ${toothNumber} using alternative method, missing: ${isMissing}`);
        
        if (!this.toothMeshes || this.toothMeshes.length === 0) {
            console.log('❌ No meshes available for fallback');
            return;
        }
        
        // For debugging: Handle specific meshes based on tooth number to help identify correct mapping
        let targetIndices = [];
        
        if (toothNumber === 8) {
            // Try multiple indices for tooth 8 (upper right lateral incisor)
            targetIndices = [7, 8, 16, 23, 1]; 
        } else if (toothNumber === 26) {
            // Try multiple indices for tooth 26 (upper left first molar)
            targetIndices = [25, 26, 29, 5, 13];
        } else {
            // For other teeth, try a range around the tooth number
            const base = Math.min(toothNumber - 1, this.toothMeshes.length - 1);
            targetIndices = [base, base + 1, base - 1].filter(i => i >= 0 && i < this.toothMeshes.length);
        }
        
        let applied = false;
        for (const idx of targetIndices) {
            if (this.toothMeshes[idx] && this.toothMeshes[idx].material) {
                console.log(`🎯 FALLBACK: Handling mesh ${idx} for tooth ${toothNumber}, missing: ${isMissing}`);
                
                const mesh = this.toothMeshes[idx];
                
                if (isMissing) {
                    // Hide missing teeth
                    console.log(`👻 FALLBACK: Hiding missing tooth ${toothNumber} at mesh ${idx}`);
                    mesh.visible = false;
                    mesh.userData.isHidden = true;
                    mesh.userData.toothNumber = toothNumber;
                    mesh.userData.condition = 'missing';
                    applied = true;
                    continue;
                }
                
                // Ensure tooth is visible for non-missing
                mesh.visible = true;
                mesh.userData.isHidden = false;
                
                // Store original material if not already stored
                if (!mesh.userData.originalMaterial) {
                    mesh.userData.originalMaterial = mesh.material.clone();
                }
                
                // Create a bright, distinctive material for testing
                const testMaterial = new THREE.MeshStandardMaterial({
                    color: new THREE.Color(color.r, color.g, color.b),
                    metalness: 0.2,
                    roughness: 0.3,
                    emissive: new THREE.Color(color.r * 0.3, color.g * 0.3, color.b * 0.3), // Bright glow
                    transparent: false,
                    opacity: 1.0
                });
                
                mesh.material = testMaterial;
                mesh.userData.toothNumber = toothNumber;
                mesh.userData.condition = color;
                mesh.userData.isFallback = true;
                
                applied = true;
                console.log(`✅ FALLBACK: Applied to mesh ${idx}`);
                break; // Only apply to first available mesh
            }
        }
        
        if (!applied) {
            console.log(`❌ FALLBACK: No suitable mesh found for tooth ${toothNumber}`);
            
            // Last resort: apply to first few meshes with a distinctive pattern
            for (let i = 0; i < Math.min(3, this.toothMeshes.length); i++) {
                if (this.toothMeshes[i] && this.toothMeshes[i].material) {
                    const mesh = this.toothMeshes[i];
                    const rainbowMaterial = new THREE.MeshStandardMaterial({
                        color: new THREE.Color(1, 0, 1), // Magenta for visibility
                        emissive: new THREE.Color(0.2, 0, 0.2),
                        metalness: 0.3,
                        roughness: 0.3
                    });
                    
                    mesh.material = rainbowMaterial;
                    mesh.userData.debugTooth = toothNumber;
                    console.log(`🌈 DEBUG: Applied magenta to mesh ${i} for tooth ${toothNumber}`);
                }
            }
        }
        
        // Force render
        if (this.renderer) {
            this.renderer.render(this.scene, this.camera);
        }
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
    
    // ==================== TOOLTIP AND HOVER FUNCTIONALITY ====================
    
    createTooltip() {
        this.tooltip = document.createElement('div');
        this.tooltip.className = 'dental-3d-tooltip';
        this.tooltip.style.cssText = `
            position: absolute;
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-family: Arial, sans-serif;
            pointer-events: none;
            z-index: 1000;
            display: none;
            max-width: 300px;
            min-width: 200px;
            line-height: 1.4;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        `;
        document.body.appendChild(this.tooltip);
    }
    
    onCanvasMouseMove(event) {
        // Only process hover if enabled
        if (!this.enableHover || !this.isLoaded || !this.raycaster) return;
        
        // Calculate mouse position in normalized device coordinates
        const rect = this.renderer.domElement.getBoundingClientRect();
        this.mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
        this.mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;
        
        // Update the picking ray with the camera and mouse position
        this.raycaster.setFromCamera(this.mouse, this.camera);
        
        // Calculate objects intersecting the picking ray
        const intersects = this.raycaster.intersectObjects(this.toothMeshes);
        
        if (intersects.length > 0) {
            const intersectedObject = intersects[0].object;
            const toothIndex = this.toothMeshes.indexOf(intersectedObject);
            const toothNumber = this.toothMapping[toothIndex];
            
            if (toothNumber && this.hoveredTooth !== toothNumber) {
                this.hoveredTooth = toothNumber;
                this.showTooltip(event, toothNumber);
                this.renderer.domElement.style.cursor = this.enableToothSelection ? 'pointer' : 'default';
                
                // Call hover callback if provided
                if (this.onToothHover) {
                    const toothData = this.toothData.get(toothNumber);
                    this.onToothHover(toothNumber, toothData);
                }
            }
            
            // Update tooltip position
            this.updateTooltipPosition(event);
        } else {
            this.hideTooltip();
            this.hoveredTooth = null;
            this.renderer.domElement.style.cursor = 'default';
        }
    }
    
    onCanvasMouseLeave() {
        this.hideTooltip();
        this.hoveredTooth = null;
        this.renderer.domElement.style.cursor = 'default';
    }
    
    showTooltip(event, toothNumber) {
        console.log(`Showing tooltip for tooth ${toothNumber}`);
        
        const toothName = this.getToothName(toothNumber);
        const toothData = this.toothData.get(toothNumber);
        
        console.log(`Tooth data for ${toothNumber}:`, toothData);
        
        let tooltipContent = `
            <div style="font-weight: bold; margin-bottom: 6px; color: #fff; border-bottom: 1px solid #555; padding-bottom: 4px;">
                <i class="fas fa-tooth" style="margin-right: 4px;"></i>Tooth #${toothNumber}
            </div>
            <div style="color: #ccc; margin-bottom: 8px; font-style: italic; font-size: 11px; line-height: 1.3;">${toothName}</div>
        `;
        
        if (toothData && toothData.length > 0) {
            tooltipContent += '<div style="border-top: 1px solid #444; padding-top: 8px;">';
            
            // Show the most recent checkup record
            const latestRecord = toothData[0];
            console.log(`Latest record for tooth ${toothNumber}:`, latestRecord);
            
            // Enhanced condition display with icons
            const conditionIcon = this.getConditionIcon(latestRecord.condition);
            const conditionColor = this.getConditionDisplayColor(latestRecord.condition);
            
            tooltipContent += `
                <div style="color: ${conditionColor}; font-weight: bold; margin-bottom: 6px; font-size: 12px;">
                    <i class="${conditionIcon}" style="margin-right: 4px;"></i>
                    Status: ${this.getConditionDisplayName(latestRecord.condition)}
                </div>
            `;
            
            // Show severity/urgency if available
            if (latestRecord.severity) {
                const severityColor = this.getSeverityColor(latestRecord.severity);
                tooltipContent += `
                    <div style="color: ${severityColor}; font-size: 11px; margin-bottom: 4px;">
                        <i class="fas fa-exclamation-triangle" style="margin-right: 4px;"></i>
                        Severity: ${latestRecord.severity.charAt(0).toUpperCase() + latestRecord.severity.slice(1)}
                    </div>
                `;
            }
            
            // Show treatment recommendation
            if (latestRecord.treatment && latestRecord.treatment.trim()) {
                tooltipContent += `
                    <div style="color: #90ee90; font-size: 11px; margin-bottom: 6px; padding: 4px; background: rgba(144, 238, 144, 0.1); border-radius: 3px;">
                        <i class="fas fa-stethoscope" style="margin-right: 4px;"></i>
                        <strong>Treatment:</strong> ${latestRecord.treatment}
                    </div>
                `;
            }
            
            // Show detailed notes
            if (latestRecord.notes && latestRecord.notes.trim()) {
                tooltipContent += `
                    <div style="color: #ccc; font-size: 11px; margin-bottom: 6px; padding: 4px; background: rgba(255, 255, 255, 0.05); border-radius: 3px;">
                        <i class="fas fa-clipboard" style="margin-right: 4px;"></i>
                        <strong>Notes:</strong> ${latestRecord.notes}
                    </div>
                `;
            }
            
            // Show follow-up information
            if (latestRecord.follow_up_date) {
                const followUpDate = new Date(latestRecord.follow_up_date).toLocaleDateString();
                tooltipContent += `
                    <div style="color: #ffeb3b; font-size: 11px; margin-bottom: 4px;">
                        <i class="fas fa-calendar-check" style="margin-right: 4px;"></i>
                        Follow-up: ${followUpDate}
                    </div>
                `;
            }
            
            // Show pain level if recorded
            if (latestRecord.pain_level !== undefined && latestRecord.pain_level !== null) {
                const painColor = this.getPainLevelColor(latestRecord.pain_level);
                tooltipContent += `
                    <div style="color: ${painColor}; font-size: 11px; margin-bottom: 4px;">
                        <i class="fas fa-heartbeat" style="margin-right: 4px;"></i>
                        Pain Level: ${latestRecord.pain_level}/10
                    </div>
                `;
            }
            
            // Last examination date
            if (latestRecord.created_at) {
                const date = new Date(latestRecord.created_at).toLocaleDateString();
                const time = new Date(latestRecord.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                tooltipContent += `
                    <div style="color: #999; font-size: 10px; margin-top: 6px; border-top: 1px solid #555; padding-top: 4px;">
                        <i class="fas fa-clock" style="margin-right: 4px;"></i>
                        Last examined: ${date} at ${time}
                    </div>
                `;
            }
            
            // Show doctor information if available
            if (latestRecord.dentist_name) {
                tooltipContent += `
                    <div style="color: #999; font-size: 10px; margin-top: 2px;">
                        <i class="fas fa-user-md" style="margin-right: 4px;"></i>
                        Dr. ${latestRecord.dentist_name}
                    </div>
                `;
            }
            
            if (toothData.length > 1) {
                tooltipContent += `
                    <div style="color: #999; font-size: 10px; margin-top: 4px; text-align: center;">
                        <i class="fas fa-history" style="margin-right: 4px;"></i>
                        ${toothData.length} total records
                    </div>
                `;
            }
            
            // Add click instruction
            tooltipContent += `
                <div style="color: #4CAF50; font-size: 10px; margin-top: 6px; text-align: center; border-top: 1px solid #555; padding-top: 4px;">
                    <i class="fas fa-hand-pointer" style="margin-right: 4px;"></i>
                    Click for detailed history
                </div>
            `;
            
            tooltipContent += '</div>';
        } else {
            tooltipContent += `
                <div style="color: #999; font-size: 11px; padding-top: 6px; border-top: 1px solid #444;">
                    No checkup records found
                </div>
            `;
            
            // Add click instruction even for teeth without data
            tooltipContent += `
                <div style="color: #4CAF50; font-size: 10px; margin-top: 6px; text-align: center; border-top: 1px solid #555; padding-top: 4px;">
                    <i class="fas fa-hand-pointer" style="margin-right: 4px;"></i>
                    Click for detailed view
                </div>
            `;
        }
        
        console.log(`Setting tooltip content:`, tooltipContent);
        
        if (!this.tooltip) {
            console.error('Tooltip element not created');
            return;
        }
        
        this.tooltip.innerHTML = tooltipContent;
        this.tooltip.style.display = 'block';
        this.updateTooltipPosition(event);
        
        console.log('Tooltip displayed:', this.tooltip.style.display);
    }
    
    updateTooltipPosition(event) {
        if (this.tooltip.style.display === 'none') return;
        
        const tooltipRect = this.tooltip.getBoundingClientRect();
        let x = event.clientX + 10;
        let y = event.clientY - 10;
        
        // Prevent tooltip from going off-screen
        if (x + tooltipRect.width > window.innerWidth) {
            x = event.clientX - tooltipRect.width - 10;
        }
        if (y - tooltipRect.height < 0) {
            y = event.clientY + 20;
        }
        
        this.tooltip.style.left = x + 'px';
        this.tooltip.style.top = y + 'px';
    }
    
    hideTooltip() {
        if (this.tooltip) {
            this.tooltip.style.display = 'none';
        }
    }
    
    // Method to set tooth data from checkup records
    setToothData(toothNumber, checkupRecords) {
        this.toothData.set(toothNumber, checkupRecords);
    }
    
    // Method to clear all tooth data
    clearToothData() {
        this.toothData.clear();
    }

    // ==================== ENHANCED CONDITION DISPLAY METHODS ====================

    /**
     * Get appropriate icon for a dental condition
     */
    getConditionIcon(condition) {
        const icons = {
            'healthy': 'fas fa-check-circle',
            'cavity': 'fas fa-exclamation-triangle',
            'filled': 'fas fa-circle',
            'crown': 'fas fa-crown',
            'root_canal': 'fas fa-stethoscope',
            'missing': 'fas fa-times-circle',
            'fractured': 'fas fa-bolt',
            'loose': 'fas fa-arrows-alt',
            'sensitive': 'fas fa-thermometer-half',
            'bleeding': 'fas fa-tint',
            'swollen': 'fas fa-circle-notch',
            'impacted': 'fas fa-compress',
            'default': 'fas fa-tooth'
        };
        return icons[condition] || icons.default;
    }

    /**
     * Get display color for a dental condition
     */
    getConditionDisplayColor(condition) {
        const colors = {
            'healthy': '#00FF00',
            'cavity': '#FF4444',
            'filled': '#FFD700',
            'crown': '#9932CC',
            'root_canal': '#1E90FF',
            'missing': '#888888',
            'fractured': '#FF6600',
            'loose': '#FFA500',
            'sensitive': '#FFFF00',
            'bleeding': '#DC143C',
            'swollen': '#FF69B4',
            'impacted': '#8B4513',
            'default': '#FFFFFF'
        };
        return colors[condition] || colors.default;
    }

    /**
     * Get user-friendly display name for a dental condition
     */
    getConditionDisplayName(condition) {
        const names = {
            'healthy': 'Healthy',
            'cavity': 'Cavity Detected',
            'filled': 'Filled/Restored',
            'crown': 'Crown Placed',
            'root_canal': 'Root Canal Treatment',
            'missing': 'Missing Tooth',
            'fractured': 'Fractured/Broken',
            'loose': 'Loose Tooth',
            'sensitive': 'Sensitive',
            'bleeding': 'Bleeding/Inflamed',
            'swollen': 'Swollen Gums',
            'impacted': 'Impacted',
            'default': 'Unknown Condition'
        };
        return names[condition] || (condition ? condition.charAt(0).toUpperCase() + condition.slice(1) : names.default);
    }

    /**
     * Get color for severity levels
     */
    getSeverityColor(severity) {
        const colors = {
            'low': '#90EE90',
            'mild': '#FFFF00',
            'moderate': '#FFA500',
            'high': '#FF4444',
            'severe': '#8B0000',
            'urgent': '#FF0000'
        };
        return colors[severity] || '#FFFFFF';
    }

    /**
     * Get color for pain levels (0-10 scale)
     */
    getPainLevelColor(painLevel) {
        if (painLevel <= 2) return '#90EE90';  // Low pain - green
        if (painLevel <= 4) return '#FFFF00';  // Mild pain - yellow
        if (painLevel <= 6) return '#FFA500';  // Moderate pain - orange
        if (painLevel <= 8) return '#FF4444';  // High pain - red
        return '#8B0000';  // Severe pain - dark red
    }
    
    destroy() {
        // Clean up tooltip
        if (this.tooltip && this.tooltip.parentNode) {
            this.tooltip.parentNode.removeChild(this.tooltip);
        }
        
        if (this.renderer) {
            this.renderer.dispose();
        }
        if (this.controls) {
            this.controls.dispose();
        }
        
        // Remove event listeners
        window.removeEventListener('resize', this.onWindowResize);
        
        // Clear tooth data
        this.clearToothData();
    }

    /**
     * Restore a hidden tooth (for when condition changes from missing to something else)
     */
    restoreTooth(toothNumber) {
        const meshIndex = this.getMeshIndexFromToothNumber(toothNumber);
        if (meshIndex === -1) {
            console.log(`❌ Cannot restore tooth ${toothNumber}: mesh not found`);
            return false;
        }

        const mesh = this.toothMeshes[meshIndex];
        if (!mesh) {
            console.log(`❌ Cannot restore tooth ${toothNumber}: mesh at index ${meshIndex} not found`);
            return false;
        }

        console.log(`👀 Restoring tooth ${toothNumber} to visible state`);
        mesh.visible = true;
        mesh.userData.isHidden = false;
        
        // Restore original material if available
        if (mesh.userData.originalMaterial) {
            mesh.material = mesh.userData.originalMaterial.clone();
        }
        
        if (this.renderer && this.scene && this.camera) {
            this.renderer.render(this.scene, this.camera);
        }
        console.log(`✅ Tooth ${toothNumber} restored successfully`);
        return true;
    }

    /**
     * Debug method to test missing teeth functionality
     */
    testMissingTooth(toothNumber) {
        console.log(`🧪 TESTING: Hiding tooth ${toothNumber} for debugging`);
        return this.setToothColor(toothNumber, null, true);
    }

    /**
     * Debug method to show all visible meshes
     */
    debugVisibleMeshes() {
        console.log('🔍 DEBUG: Visible meshes status:');
        this.toothMeshes.forEach((mesh, index) => {
            if (mesh) {
                console.log(`Mesh ${index}: visible = ${mesh.visible}, hidden = ${mesh.userData.isHidden || false}`);
            }
        });
    }
}

// Export for use in other files
window.Dental3DViewer = Dental3DViewer; 