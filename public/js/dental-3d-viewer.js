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
        this.isDestroyed = false;
        this.animationId = null;
        
        // Tooth mapping configuration
        this.toothMapping = null; // Will be set to manual mapping or auto-generated
        // Use the explicit manual mapping provided for this model
        this.mappingMethod = 'manual'; // 'auto', 'position', or 'manual'
        this.debugMappingEnabled = true; // Enable detailed mapping debug output
        
        // Manual mapping from user - integrated tooth mapping data
        this.manualToothMapping = {
            0: 24, 1: 23, 2: 22, 3: 21, 4: 20, 5: 19, 6: 18, 7: 17,
            8: 32, 9: 31, 10: 30, 11: 28, 12: 29, 13: 27, 14: 26, 15: 25,
            16: 9, 17: 8, 18: 7, 19: 6, 21: 3, 22: 10, 23: 15, 24: 2,
            25: 12, 26: 13, 27: 11, 28: 14, 29: 16, 30: 5, 31: 4, 32: 1
            // Note: mesh index 20 is unmapped (likely gum tissue or non-tooth structure)
        };
        
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
                // Mark viewer as loaded so external callers can safely update colors
                this.isLoaded = true;
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
        const meshAnalysis = [];
        
        this.model.traverse((child) => {
            if (child.isMesh) {
                this.toothMeshes.push(child);
                
                // Analyze mesh properties for auto-mapping
                const boundingBox = new THREE.Box3().setFromObject(child);
                const center = boundingBox.getCenter(new THREE.Vector3());
                const size = boundingBox.getSize(new THREE.Vector3());
                
                meshAnalysis.push({
                    index: this.toothMeshes.length - 1,
                    mesh: child,
                    name: child.name || `mesh_${this.toothMeshes.length - 1}`,
                    position: center.clone(),
                    size: size.clone(),
                    volume: size.x * size.y * size.z
                });
            }
        });
        
        console.log(`Found ${this.toothMeshes.length} tooth meshes for click detection`);
        
        // Always generate a complete position-based mapping first
        this.generateToothMapping(meshAnalysis);

        // Then, if manual mapping is enabled and provided, overlay the manual entries
        if (this.mappingMethod === 'manual' && Object.keys(this.manualToothMapping).length > 0) {
            if (this.debugMappingEnabled) {
                console.group('🧩 Overlaying Manual Mapping on Auto Mapping');
            }
            Object.entries(this.manualToothMapping).forEach(([meshIndex, toothNumber]) => {
                const index = parseInt(meshIndex);
                if (index >= 0 && index < this.toothMapping.length) {
                    this.toothMapping[index] = toothNumber;
                }
            });
            if (this.debugMappingEnabled) {
                console.log('✅ Manual mapping overlay complete');
                console.groupEnd();
            }
        }
    }
    
    applyManualMapping() {
        if (this.debugMappingEnabled) {
            console.group('🦷 Applying Manual Tooth Mapping');
        }
        
        // Create array-based mapping from the manual mapping object
        this.toothMapping = new Array(this.toothMeshes.length).fill(null);
        
        // Apply manual mappings
        Object.entries(this.manualToothMapping).forEach(([meshIndex, toothNumber]) => {
            const index = parseInt(meshIndex);
            if (index >= 0 && index < this.toothMapping.length) {
                this.toothMapping[index] = toothNumber;
            }
        });
        
        if (this.debugMappingEnabled) {
            console.log(`✅ Applied ${Object.keys(this.manualToothMapping).length} manual mappings`);
            this.logManualMappingResults();
            console.groupEnd();
        }
    }
    
    logManualMappingResults() {
        console.log('📋 Manual Tooth Mapping Applied:');
        console.table(this.toothMapping.map((toothNum, meshIndex) => ({
            MeshIndex: meshIndex,
            ToothNumber: toothNum,
            ToothName: this.toothNames[toothNum] || 'Unknown',
            MeshName: this.toothMeshes[meshIndex]?.name || 'unnamed',
            MappingSource: toothNum ? 'Manual' : 'Unmapped'
        })));
        
        // Validation
        const issues = this.validateToothMapping();
        if (issues.length > 0) {
            console.warn('⚠️ Manual Mapping Issues:', issues);
        } else {
            console.log('✅ Manual mapping validation passed');
        }
        
        // Statistics
        const mappedCount = this.toothMapping.filter(t => t !== null).length;
        const uniqueTeeth = new Set(this.toothMapping.filter(t => t !== null)).size;
        console.log(`📊 Mapping Statistics:
        - Total Meshes: ${this.toothMeshes.length}
        - Mapped Meshes: ${mappedCount}
        - Unique Teeth: ${uniqueTeeth}
        - Coverage: ${((mappedCount / this.toothMeshes.length) * 100).toFixed(1)}%`);
    }
    
    generateToothMapping(meshAnalysis) {
        if (this.debugMappingEnabled) {
            console.group('🦷 Dental Model Analysis & Mapping Generation');
        }
        
        // Sort meshes by position to create logical mapping
        const sortedMeshes = this.analyzeMeshPositions(meshAnalysis);
        
        // Generate mapping based on dental anatomy
        this.toothMapping = this.createPositionBasedMapping(sortedMeshes);
        
        if (this.debugMappingEnabled) {
            this.logMappingResults(sortedMeshes);
            console.groupEnd();
        }
    }
    
    analyzeMeshPositions(meshAnalysis) {
        if (this.debugMappingEnabled) {
            console.log('📊 Analyzing mesh positions...');
        }
        
        // Separate upper and lower teeth based on Y position
        const upperTeeth = meshAnalysis.filter(mesh => mesh.position.y > 0);
        const lowerTeeth = meshAnalysis.filter(mesh => mesh.position.y <= 0);
        
        // Sort by X position (left to right from patient's perspective)
        const sortUpper = upperTeeth.sort((a, b) => b.position.x - a.position.x); // Right to left
        const sortLower = lowerTeeth.sort((a, b) => a.position.x - b.position.x); // Left to right
        
        if (this.debugMappingEnabled) {
            console.log(`Upper teeth detected: ${upperTeeth.length}`);
            console.log(`Lower teeth detected: ${lowerTeeth.length}`);
        }
        
        return {
            upper: sortUpper,
            lower: sortLower,
            all: meshAnalysis
        };
    }
    
    createPositionBasedMapping(sortedMeshes) {
        const mapping = new Array(this.toothMeshes.length).fill(null);
        
        // Universal Numbering System:
        // Upper Right: 1-8 (from back to front)
        // Upper Left: 9-16 (from front to back)  
        // Lower Left: 17-24 (from front to back)
        // Lower Right: 25-32 (from back to front)
        
        // Map upper teeth (1-16)
        this.mapUpperTeeth(sortedMeshes.upper, mapping);
        
        // Map lower teeth (17-32)
        this.mapLowerTeeth(sortedMeshes.lower, mapping);
        
        // Fill any unmapped meshes with sequential numbers
        this.fillUnmappedMeshes(mapping);
        
        return mapping;
    }
    
    mapUpperTeeth(upperTeeth, mapping) {
        const midline = 0; // X=0 is the center line
        const rightSide = upperTeeth.filter(tooth => tooth.position.x > midline);
        const leftSide = upperTeeth.filter(tooth => tooth.position.x <= midline);
        
        // Upper Right (1-8): from posterior to anterior
        rightSide.sort((a, b) => a.position.x - b.position.x); // furthest right first
        rightSide.forEach((tooth, index) => {
            if (index < 8) {
                mapping[tooth.index] = index + 1; // Teeth 1-8
            }
        });
        
        // Upper Left (9-16): from anterior to posterior  
        leftSide.sort((a, b) => b.position.x - a.position.x); // closest to center first
        leftSide.forEach((tooth, index) => {
            if (index < 8) {
                mapping[tooth.index] = index + 9; // Teeth 9-16
            }
        });
    }
    
    mapLowerTeeth(lowerTeeth, mapping) {
        const midline = 0; // X=0 is the center line
        const leftSide = lowerTeeth.filter(tooth => tooth.position.x <= midline);
        const rightSide = lowerTeeth.filter(tooth => tooth.position.x > midline);
        
        // Lower Left (17-24): from anterior to posterior
        leftSide.sort((a, b) => b.position.x - a.position.x); // closest to center first
        leftSide.forEach((tooth, index) => {
            if (index < 8) {
                mapping[tooth.index] = index + 17; // Teeth 17-24
            }
        });
        
        // Lower Right (25-32): from posterior to anterior
        rightSide.sort((a, b) => a.position.x - b.position.x); // furthest right first  
        rightSide.forEach((tooth, index) => {
            if (index < 8) {
                mapping[tooth.index] = index + 25; // Teeth 25-32
            }
        });
    }
    
    fillUnmappedMeshes(mapping) {
        // Find unmapped indices and assign sequential tooth numbers
        const usedNumbers = new Set(mapping.filter(n => n !== null));
        let nextNumber = 1;
        
        for (let i = 0; i < mapping.length; i++) {
            if (mapping[i] === null) {
                // Find next available tooth number
                while (usedNumbers.has(nextNumber) && nextNumber <= 32) {
                    nextNumber++;
                }
                if (nextNumber <= 32) {
                    mapping[i] = nextNumber;
                    usedNumbers.add(nextNumber);
                    nextNumber++;
                }
            }
        }
    }
    
    logMappingResults(sortedMeshes) {
        console.table(this.toothMapping.map((toothNum, meshIndex) => ({
            MeshIndex: meshIndex,
            ToothNumber: toothNum,
            ToothName: this.toothNames[toothNum] || 'Unknown',
            MeshName: this.toothMeshes[meshIndex]?.name || 'unnamed',
            Position: this.toothMeshes[meshIndex] ? 
                `(${this.toothMeshes[meshIndex].position.x.toFixed(2)}, ${this.toothMeshes[meshIndex].position.y.toFixed(2)}, ${this.toothMeshes[meshIndex].position.z.toFixed(2)})` : 
                'N/A'
        })));
        
        // Check for mapping issues
        const issues = this.validateToothMapping();
        if (issues.length > 0) {
            console.warn('⚠️ Mapping Issues Detected:', issues);
        } else {
            console.log('✅ Mapping validation passed');
        }
    }
    
    validateToothMapping() {
        const issues = [];
        const usedNumbers = new Set();
        const unmapped = [];
        
        this.toothMapping.forEach((toothNum, meshIndex) => {
            if (toothNum === null || toothNum === undefined) {
                unmapped.push(meshIndex);
            } else if (usedNumbers.has(toothNum)) {
                issues.push(`Duplicate tooth number ${toothNum} at mesh indices`);
            } else if (toothNum < 1 || toothNum > 32) {
                issues.push(`Invalid tooth number ${toothNum} at mesh index ${meshIndex}`);
            } else {
                usedNumbers.add(toothNum);
            }
        });
        
        if (unmapped.length > 0) {
            issues.push(`Unmapped mesh indices: ${unmapped.join(', ')}`);
        }
        
        // Check for missing standard tooth numbers
        const missing = [];
        for (let i = 1; i <= 32; i++) {
            if (!usedNumbers.has(i)) {
                missing.push(i);
            }
        }
        if (missing.length > 0) {
            issues.push(`Missing tooth numbers: ${missing.join(', ')}`);
        }
        
        return issues;
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
        // Validate input
        if (meshIndex < 0 || !this.toothMapping || meshIndex >= this.toothMapping.length) {
            if (this.debugMappingEnabled) {
                console.warn(`⚠️ Invalid mesh index ${meshIndex}, using fallback mapping`);
            }
            return this.getFallbackToothNumber(meshIndex);
        }
        
        const toothNumber = this.toothMapping[meshIndex];
        
        // Validate mapped tooth number
        if (toothNumber === null || toothNumber === undefined || toothNumber < 1 || toothNumber > 32) {
            if (this.debugMappingEnabled) {
                console.warn(`⚠️ Invalid tooth number ${toothNumber} for mesh ${meshIndex}, using fallback`);
            }
            return this.getFallbackToothNumber(meshIndex);
        }
        
        return toothNumber;
    }
    
    getFallbackToothNumber(meshIndex) {
        // Simple fallback: assume sequential mapping
        const fallbackNumber = (meshIndex % 32) + 1;
        if (this.debugMappingEnabled) {
            console.log(`🔄 Using fallback mapping: mesh ${meshIndex} -> tooth ${fallbackNumber}`);
        }
        return fallbackNumber;
    }
    
    // Enhanced method to find mesh indices for a given tooth number
    getToothMeshIndices(toothNumber) {
        if (!this.toothMapping || toothNumber < 1 || toothNumber > 32) {
            return [];
        }
        
        const indices = [];
        this.toothMapping.forEach((mappedNumber, meshIndex) => {
            if (mappedNumber === toothNumber) {
                indices.push(meshIndex);
            }
        });
        
        return indices;
    }
    
    // Method to switch mapping methods
    setMappingMethod(method) {
        const validMethods = ['auto', 'position', 'manual'];
        if (!validMethods.includes(method)) {
            console.error(`❌ Invalid mapping method: ${method}. Valid options: ${validMethods.join(', ')}`);
            return false;
        }
        
        const previousMethod = this.mappingMethod;
        this.mappingMethod = method;
        
        console.log(`🔧 Switching mapping method from '${previousMethod}' to '${method}'`);
        
        // Reprocess teeth with new mapping method
        if (this.toothMeshes && this.toothMeshes.length > 0) {
            this.processTeethForClickDetection();
        }
        
        return true;
    }
    
    // Method to update manual mapping at runtime
    updateManualMapping(newMappings) {
        console.log('🔄 Updating manual tooth mapping...');
        
        // Merge new mappings with existing ones
        this.manualToothMapping = { ...this.manualToothMapping, ...newMappings };
        
        // If currently using manual mapping, reapply it
        if (this.mappingMethod === 'manual') {
            this.applyManualMapping();
        }
        
        console.log('✅ Manual mapping updated successfully');
    }
    
    // Method to get current mapping information
    getCurrentMappingInfo() {
        return {
            method: this.mappingMethod,
            totalMeshes: this.toothMeshes ? this.toothMeshes.length : 0,
            mappingArray: this.toothMapping ? [...this.toothMapping] : null,
            manualMappings: { ...this.manualToothMapping },
            mappedCount: this.toothMapping ? this.toothMapping.filter(t => t !== null).length : 0,
            uniqueTeeth: this.toothMapping ? new Set(this.toothMapping.filter(t => t !== null)).size : 0
        };
    }
    
    // Method to manually override tooth mapping for specific meshes
    setManualToothMapping(meshIndex, toothNumber) {
        if (!this.toothMapping) {
            this.toothMapping = new Array(this.toothMeshes.length).fill(null);
        }
        
        if (meshIndex >= 0 && meshIndex < this.toothMapping.length && toothNumber >= 1 && toothNumber <= 32) {
            this.toothMapping[meshIndex] = toothNumber;
            // Also update the manual mapping object
            this.manualToothMapping[meshIndex] = toothNumber;
            if (this.debugMappingEnabled) {
                console.log(`🔧 Manual mapping set: mesh ${meshIndex} -> tooth ${toothNumber}`);
            }
            return true;
        }
        
        console.error(`❌ Invalid manual mapping: mesh ${meshIndex} -> tooth ${toothNumber}`);
        return false;
    }
    
    // Method to recalibrate mapping based on user corrections
    recalibrateMapping(corrections = []) {
        if (corrections.length > 0) {
            console.log('🔄 Recalibrating tooth mapping with user corrections...');
            corrections.forEach(({ meshIndex, toothNumber }) => {
                this.setManualToothMapping(meshIndex, toothNumber);
            });
            
            if (this.debugMappingEnabled) {
                this.debugLogToothMapping();
            }
        }
    }
    
    highlightTooth(toothIndex) {
        // Reset all teeth to their condition colors first
        this.resetHighlights();
        
        if (!this.toothMeshes || toothIndex < 0 || toothIndex >= this.toothMeshes.length) return;
        
        const mesh = this.toothMeshes[toothIndex];
        if (mesh && mesh.material) {
            // Store current material so we can restore it when closing popup
            if (!mesh.userData.prevMaterial) {
                mesh.userData.prevMaterial = mesh.material.clone();
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
        if (!this.toothMeshes) {
            console.warn('⚠️ No tooth meshes available for coloring');
            return false;
        }

        // Find all mesh indices that map to this tooth number
        const meshIndices = this.getToothMeshIndices(toothNumber);
        
        if (meshIndices.length === 0) {
            if (this.debugMapping) {
                console.warn(`⚠️ No mesh found for tooth ${toothNumber}`);
            }
            return false;
        }

        let success = false;
        meshIndices.forEach((meshIndex) => {
            const mesh = this.toothMeshes[meshIndex];
            if (!mesh || !mesh.material) return;

            // Store original material if not already stored
            if (!mesh.userData.originalMaterial) {
                mesh.userData.originalMaterial = mesh.material.clone();
            }

            if (isMissing) {
                // Handle missing teeth - make completely invisible
                const missingMaterial = new THREE.MeshStandardMaterial({
                    transparent: true,
                    opacity: 0.0,
                    visible: false
                });
                mesh.material = missingMaterial;
                mesh.visible = false;
                mesh.userData.conditionMaterial = missingMaterial.clone();
                
                if (this.debugMappingEnabled) {
                    console.log(`👻 Set tooth ${toothNumber} as missing (mesh ${meshIndex})`);
                }
            } else if (color) {
                // Apply condition color with enhanced material properties
                const conditionMaterial = new THREE.MeshStandardMaterial({
                    color: new THREE.Color(color.r, color.g, color.b),
                    transparent: false,
                    opacity: 1.0,
                    metalness: 0.1,
                    roughness: 0.4,
                    emissive: new THREE.Color(color.r * 0.1, color.g * 0.1, color.b * 0.1),
                    depthWrite: true
                });

                mesh.material = conditionMaterial;
                mesh.visible = true;
                mesh.userData.conditionMaterial = conditionMaterial.clone();
                
                if (this.debugMappingEnabled) {
                    console.log(`🎨 Applied color to tooth ${toothNumber} (mesh ${meshIndex}): RGB(${color.r}, ${color.g}, ${color.b})`);
                }
            } else {
                // Reset to original material
                if (mesh.userData.originalMaterial) {
                    mesh.material = mesh.userData.originalMaterial.clone();
                    mesh.visible = true;
                    mesh.userData.conditionMaterial = null;
                    
                    if (this.debugMappingEnabled) {
                        console.log(`🔄 Reset tooth ${toothNumber} to original color (mesh ${meshIndex})`);
                    }
                }
            }
            
            success = true;
        });

        return success;
    }
    
    // Method to reset all teeth to original colors
    resetAllTeethColor() {
        this.toothMeshes.forEach((mesh, index) => {
            if (mesh && mesh.material && mesh.userData.originalMaterial) {
                mesh.material = mesh.userData.originalMaterial.clone();
                mesh.visible = true;
                mesh.userData.conditionMaterial = null;
            }
        });
        
        if (this.debugMappingEnabled) {
            console.log('🔄 Reset all teeth to original colors');
        }
    }
    
    // Enhanced method to get detailed tooth information
    getToothInfo(toothNumber) {
        const meshIndices = this.getToothMeshIndices(toothNumber);
        
        if (meshIndices.length === 0) {
            return null;
        }
        
        const meshInfo = meshIndices.map(index => {
            const mesh = this.toothMeshes[index];
            return {
                meshIndex: index,
                meshName: mesh?.name || `mesh_${index}`,
                position: mesh ? {
                    x: parseFloat(mesh.position.x.toFixed(3)),
                    y: parseFloat(mesh.position.y.toFixed(3)),
                    z: parseFloat(mesh.position.z.toFixed(3))
                } : null,
                hasConditionColor: !!(mesh?.userData.conditionMaterial),
                isVisible: mesh?.visible !== false
            };
        });
        
        return {
            toothNumber,
            toothName: this.toothNames[toothNumber] || 'Unknown',
            meshCount: meshIndices.length,
            meshes: meshInfo
        };
    }
    
    resetHighlights() {
        // Reset only the highlight effects, keep condition colors
        this.toothMeshes.forEach((tooth) => {
            if (tooth.userData.prevMaterial) {
                tooth.material = tooth.userData.prevMaterial.clone();
                tooth.userData.prevMaterial = null;
                return;
            }
            // If tooth has a condition color, restore it
            if (tooth.userData.conditionMaterial) {
                tooth.material = tooth.userData.conditionMaterial.clone();
            } else if (tooth.userData.originalMaterial) {
                // Otherwise restore original material
                tooth.material = tooth.userData.originalMaterial.clone();
            }
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
    
    resetHighlights() {
        // Reset only the highlight effects, keep condition colors
        this.toothMeshes.forEach((tooth) => {
            if (tooth.userData.prevMaterial) {
                tooth.material = tooth.userData.prevMaterial.clone();
                tooth.userData.prevMaterial = null;
                return;
            }
            if (tooth.userData.conditionMaterial) {
                tooth.material = tooth.userData.conditionMaterial.clone();
            } else if (tooth.userData.originalMaterial) {
                tooth.material = tooth.userData.originalMaterial.clone();
            }
        });
    }

    // Enhanced debug method with detailed analysis
    debugLogToothMapping() {
        if (!this.toothMapping) {
            console.warn('⚠️ No tooth mapping available to debug');
            return;
        }

        console.group('🦷 Dental3DViewer Detailed Tooth Mapping Debug');
        
        // Basic mapping table
        console.log('📋 Current Tooth Mapping:');
        console.table(this.toothMapping.map((toothNum, meshIndex) => ({
            MeshIndex: meshIndex,
            ToothNumber: toothNum,
            ToothName: this.toothNames[toothNum] || 'Unknown',
            MeshName: this.toothMeshes[meshIndex]?.name || 'unnamed',
            Position: this.toothMeshes[meshIndex] ? 
                `X:${this.toothMeshes[meshIndex].position.x.toFixed(2)} Y:${this.toothMeshes[meshIndex].position.y.toFixed(2)} Z:${this.toothMeshes[meshIndex].position.z.toFixed(2)}` : 
                'N/A'
        })));
        
        // Validation results
        const issues = this.validateToothMapping();
        if (issues.length > 0) {
            console.group('⚠️ Mapping Issues');
            issues.forEach(issue => console.warn(issue));
            console.groupEnd();
        } else {
            console.log('✅ Mapping validation passed - no issues detected');
        }
        
        // Quadrant analysis
        this.analyzeQuadrants();
        
        // Coverage analysis
        this.analyzeCoverage();
        
        console.groupEnd();
    }
    
    analyzeQuadrants() {
        console.group('🗂️ Quadrant Analysis');
        
        const quadrants = {
            'Upper Right (1-8)': [],
            'Upper Left (9-16)': [],
            'Lower Left (17-24)': [],
            'Lower Right (25-32)': []
        };
        
        this.toothMapping.forEach((toothNum, meshIndex) => {
            if (toothNum >= 1 && toothNum <= 8) {
                quadrants['Upper Right (1-8)'].push({ meshIndex, toothNum });
            } else if (toothNum >= 9 && toothNum <= 16) {
                quadrants['Upper Left (9-16)'].push({ meshIndex, toothNum });
            } else if (toothNum >= 17 && toothNum <= 24) {
                quadrants['Lower Left (17-24)'].push({ meshIndex, toothNum });
            } else if (toothNum >= 25 && toothNum <= 32) {
                quadrants['Lower Right (25-32)'].push({ meshIndex, toothNum });
            }
        });
        
        Object.entries(quadrants).forEach(([quadrant, teeth]) => {
            console.log(`${quadrant}: ${teeth.length} teeth mapped`);
            if (teeth.length > 0) {
                console.log(`  Teeth: ${teeth.map(t => t.toothNum).sort((a, b) => a - b).join(', ')}`);
            }
        });
        
        console.groupEnd();
    }
    
    analyzeCoverage() {
        console.group('📊 Coverage Analysis');
        
        const mappedNumbers = new Set(this.toothMapping.filter(n => n !== null && n !== undefined));
        const totalMeshes = this.toothMeshes.length;
        const mappedMeshes = this.toothMapping.filter(n => n !== null && n !== undefined).length;
        
        console.log(`Total meshes: ${totalMeshes}`);
        console.log(`Mapped meshes: ${mappedMeshes}`);
        console.log(`Mapping coverage: ${((mappedMeshes / totalMeshes) * 100).toFixed(1)}%`);
        console.log(`Unique tooth numbers: ${mappedNumbers.size}`);
        
        // Find gaps in tooth numbering
        const missingNumbers = [];
        for (let i = 1; i <= 32; i++) {
            if (!mappedNumbers.has(i)) {
                missingNumbers.push(i);
            }
        }
        
        if (missingNumbers.length > 0) {
            console.warn(`Missing tooth numbers: ${missingNumbers.join(', ')}`);
        }
        
        // Find duplicates
        const numberCounts = {};
        this.toothMapping.forEach(num => {
            if (num !== null && num !== undefined) {
                numberCounts[num] = (numberCounts[num] || 0) + 1;
            }
        });
        
        const duplicates = Object.entries(numberCounts).filter(([num, count]) => count > 1);
        if (duplicates.length > 0) {
            console.warn('Duplicate mappings:');
            duplicates.forEach(([num, count]) => {
                console.warn(`  Tooth ${num}: ${count} meshes`);
            });
        }
        
        console.groupEnd();
    }
    
    // Method to export current mapping for manual correction
    exportMappingForCorrection() {
        const exportData = this.toothMapping.map((toothNum, meshIndex) => ({
            meshIndex,
            currentToothNumber: toothNum,
            meshName: this.toothMeshes[meshIndex]?.name || `mesh_${meshIndex}`,
            position: this.toothMeshes[meshIndex] ? {
                x: parseFloat(this.toothMeshes[meshIndex].position.x.toFixed(3)),
                y: parseFloat(this.toothMeshes[meshIndex].position.y.toFixed(3)),
                z: parseFloat(this.toothMeshes[meshIndex].position.z.toFixed(3))
            } : null,
            suggestedToothNumber: toothNum // Can be manually corrected
        }));
        
        console.log('📤 Tooth mapping export data:');
        console.log(JSON.stringify(exportData, null, 2));
        
        return exportData;
    }
    
    animate() {
        // Check if viewer has been destroyed
        if (this.isDestroyed) {
            return;
        }
        
        this.animationId = requestAnimationFrame(() => this.animate());
        
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
    
    // Public mapping control methods
    debugMapping() {
        this.debugLogToothMapping();
    }
    
    exportMapping() {
        return this.exportMappingForCorrection();
    }
    
    // Method to apply mapping corrections from external source
    applyMappingCorrections(corrections) {
        this.recalibrateMapping(corrections);
    }
    
    // Method to get detailed information about a specific tooth
    getToothDetails(toothNumber) {
        return this.getToothInfo(toothNumber);
    }
    
    // Method to manually map a mesh to a tooth number
    mapMeshToTooth(meshIndex, toothNumber) {
        return this.setManualToothMapping(meshIndex, toothNumber);
    }
    
    // Method to get current mapping configuration
    getMappingConfig() {
        return this.getCurrentMappingInfo();
    }
    
    // Method to enable/disable debug output
    setDebugMode(enabled) {
        this.debugMappingEnabled = enabled;
        console.log(`🔧 Debug mode ${enabled ? 'enabled' : 'disabled'} for tooth mapping`);
    }
    
    // Method to switch between mapping methods
    switchMappingMethod(method) {
        return this.setMappingMethod(method);
    }
    
    // Method to apply new manual mappings
    applyManualMappings(mappings) {
        this.updateManualMapping(mappings);
    }
    
    getToothName(toothNumber) {
        return this.toothNames[toothNumber] || 'Unknown';
    }
    
    destroy() {
        console.log('🧹 Destroying 3D viewer...');
        
        // Stop animation loop
        this.isDestroyed = true;
        
        // Clear any timeout/interval references
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
            this.animationId = null;
        }
        
        // Dispose of Three.js objects
        if (this.renderer) {
            this.renderer.dispose();
            this.renderer.forceContextLoss();
            this.renderer = null;
        }
        
        if (this.controls) {
            this.controls.dispose();
            this.controls = null;
        }
        
        // Clear scene
        if (this.scene) {
            this.scene.clear();
            this.scene = null;
        }
        
        // Clear references
        this.camera = null;
        this.model = null;
        this.toothMeshes = [];
        this.raycaster = null;
        this.mouse = null;
        
        // Remove event listeners
        if (this.onWindowResize) {
            window.removeEventListener('resize', this.onWindowResize);
        }
        
        console.log('✅ 3D viewer destroyed');
    }
}

// Export for use in other files
window.Dental3DViewer = Dental3DViewer; 
