/**
 * Dental 3D Manager - Handles all 3D viewer operations
 * Manages 3D model initialization, data application, and interactions
 */

class Dental3DManager {
    constructor(baseUrl) {
        this.baseUrl = baseUrl || '';
        this.modalDental3DViewer = null;
        this.conditionsAnalyzer = null; // Will be injected
    }

    setConditionsAnalyzer(analyzer) {
        this.conditionsAnalyzer = analyzer;
    }

    // ==================== 3D VIEWER INITIALIZATION ====================

    initModal3D(chartData, onToothClickCallback = null) {
        const container = document.getElementById('dentalModalViewer');
        if (!container) {
            console.error('❌ 3D viewer container not found');
            return;
        }

        const canvas = container.querySelector('.dental-3d-canvas');
        const loadingEl = document.getElementById('modalModelLoading');
        const errorEl = document.getElementById('modalModelError');

        if (!canvas) {
            console.error('❌ 3D viewer canvas not found');
            return;
        }

        try {
            this.cleanup();

            // Show loading
            loadingEl?.classList.remove('hidden');
            errorEl?.classList.add('hidden');

            // Initialize the 3D viewer for INTERACTIVE mode with click functionality
            this.modalDental3DViewer = new window.Dental3DViewer('dentalModalViewer', {
                modelUrl: this.baseUrl + '/img/permanent_dentition-2.glb',
                width: canvas.offsetWidth || 400,
                height: canvas.offsetHeight || 300,
                autoRotate: false,
                showControls: true,
                backgroundColor: 0xf8f9fa,
                enableToothSelection: true, // ENABLED for interactive clicking
                enableHover: true, // Enable hover tooltips
                onToothHover: (toothNumber, toothData) => {
                    console.log(`Hovering over tooth ${toothNumber}`, toothData);
                },
                onToothClick: (toothNumber, clickPoint, event, details) => {
                    console.log('🦷 Tooth clicked:', toothNumber, details);
                    // Show detailed tooth information modal with proper tooth name
                    const toothData = details.toothData || [];
                    const toothName = details.toothName || `Tooth ${toothNumber}`;
                    
                    if (onToothClickCallback) {
                        onToothClickCallback(toothNumber, toothData, toothName);
                    }
                },
                onModelLoaded: () => {
                    console.log('✅ Modal 3D model loaded successfully');
                    loadingEl?.classList.add('hidden');
                    errorEl?.classList.add('hidden');
                    
                    // Apply real checkup data to the model once loaded
                    this.applyChartDataToViewer(chartData);
                }
            });
            
            // Initialize the viewer
            if (this.modalDental3DViewer.init()) {
                console.log('✅ 3D Dental viewer initialized successfully');
                
                // Wait for 3D model to fully load before applying tooth data
                setTimeout(() => {
                    this.applyChartDataToViewer(chartData);
                }, 2000); // Give model time to load
            } else {
                throw new Error('Failed to initialize 3D viewer');
            }
        } catch (error) {
            console.error('❌ Error initializing 3D viewer:', error);
            this.showError(error.message);
        }
    }

    // ==================== DATA APPLICATION ====================

    applyChartDataToViewer(chartData) {
        console.log('🎯 APPLYING REAL CHECKUP DATA TO 3D VIEWER');
        console.log('chartData received:', chartData);
        
        if (!this.modalDental3DViewer) {
            console.error('❌ 3D viewer not initialized');
            return;
        }

        if (!chartData || !chartData.teeth_data) {
            console.log('⚠️ No teeth data available');
            return;
        }

        const groupedData = this.processTeethData(chartData.teeth_data);
        this.applyColorsToTeeth(groupedData);
        
        // Generate conditions summary after applying colors
        if (this.conditionsAnalyzer) {
            this.conditionsAnalyzer.generateSummary(groupedData);
        }
    }

    processTeethData(teethData) {
        console.log('=== PROCESSING TEETH DATA START ===');
        console.log('teeth_data keys:', Object.keys(teethData));
        
        const groupedData = {};
        
        Object.keys(teethData).forEach(toothNumber => {
            const toothRecords = teethData[toothNumber];
            
            if (Array.isArray(toothRecords) && toothRecords.length > 0) {
                // Sort by date (most recent first)
                const sortedRecords = toothRecords.sort((a, b) => 
                    new Date(b.created_at) - new Date(a.created_at)
                );
                
                groupedData[toothNumber] = sortedRecords;
                console.log(`📊 Tooth ${toothNumber}: ${sortedRecords.length} records, latest condition: ${sortedRecords[0].condition}`);
            }
        });
        
        console.log('=== PROCESSING TEETH DATA END ===');
        return groupedData;
    }

    applyColorsToTeeth(groupedData) {
        console.log('🎨 APPLYING COLORS TO TEETH');
        
        let appliedCount = 0;
        
        Object.keys(groupedData).forEach(toothNumber => {
            const toothData = groupedData[toothNumber];
            console.log(`🦷 Processing tooth ${toothNumber}:`, toothData);
            
            if (toothData && toothData.length > 0) {
                const latestRecord = toothData[0];
                console.log(`🦷 TOOTH ${toothNumber}: Condition = '${latestRecord.condition}'`);
                
                if (latestRecord.condition === 'missing') {
                    this.hideMissingTooth(toothNumber);
                } else {
                    this.applyToothColor(toothNumber, latestRecord.condition);
                }
                
                // Set hover data for both missing and non-missing teeth
                this.setToothHoverData(toothNumber, toothData);
                appliedCount++;
            } else {
                console.log(`⚪ No checkup data for tooth ${toothNumber}`);
            }
        });
        
        console.log(`🎯 COMPLETED: Applied colors to ${appliedCount} teeth based on real checkup data`);
        this.forceRenderUpdate();
    }

    hideMissingTooth(toothNumber) {
        console.log(`👻 HIDING missing tooth ${toothNumber}`);
        try {
            const result = this.modalDental3DViewer.setToothColor(parseInt(toothNumber), null, true);
            console.log(`✅ SUCCESS: Missing tooth ${toothNumber} hidden, result:`, result);
            
            // Additional debug: Check if tooth is actually hidden
            setTimeout(() => {
                if (typeof this.modalDental3DViewer.debugVisibleMeshes === 'function') {
                    this.modalDental3DViewer.debugVisibleMeshes();
                }
            }, 500);
        } catch (error) {
            console.error(`❌ Error hiding tooth ${toothNumber}:`, error);
        }
    }

    applyToothColor(toothNumber, condition) {
        const color = this.getToothColor(condition);
        console.log(`🎨 Color for '${condition}': ${color} (hex: 0x${color.toString(16)})`);
        
        // Convert to RGB for 3D viewer
        const rgbColor = {
            r: ((color >> 16) & 255) / 255,
            g: ((color >> 8) & 255) / 255,
            b: (color & 255) / 255
        };
        console.log(`🌈 RGB color:`, rgbColor);
        
        // Apply color to 3D model
        try {
            console.log(`🎨 Applying color to tooth ${toothNumber}...`);
            this.modalDental3DViewer.setToothColor(parseInt(toothNumber), rgbColor, false);
            console.log(`✅ SUCCESS: Color applied to tooth ${toothNumber}`);
        } catch (error) {
            console.error(`❌ Error applying color to tooth ${toothNumber}:`, error);
        }
    }

    setToothHoverData(toothNumber, toothData) {
        try {
            if (typeof this.modalDental3DViewer.setToothData === 'function') {
                this.modalDental3DViewer.setToothData(parseInt(toothNumber), toothData);
                console.log(`✅ Hover data set for tooth ${toothNumber}`);
            }
        } catch (error) {
            console.error(`❌ Error setting hover data for tooth ${toothNumber}:`, error);
        }
    }

    forceRenderUpdate() {
        // Force a render update
        if (typeof this.modalDental3DViewer.render === 'function') {
            this.modalDental3DViewer.render();
            console.log('🔄 Forced render update');
        }
    }

    // ==================== COLOR MAPPING ====================

    getToothColor(condition) {
        const colors = {
            'healthy': 0x00FF00,      // Bright green
            'cavity': 0xFF0000,       // Bright red
            'filled': 0xFFD700,       // Gold
            'crown': 0x9932CC,        // Dark orchid
            'root_canal': 0x1E90FF,   // Dodger blue
            'fractured': 0xFF6600,    // Orange
            'loose': 0xFFA500,        // Orange
            'sensitive': 0xFFFF00,    // Yellow
            'bleeding': 0xDC143C,     // Crimson
            'swollen': 0xFF69B4,      // Hot pink
            'impacted': 0x8B4513,     // Saddle brown
            'default': 0xDCDCDC       // Gainsboro
            // Note: 'missing' teeth are hidden, not colored
        };
        return colors[condition] || colors.default;
    }

    // ==================== CONTROL METHODS ====================

    resetCamera() {
        if (this.modalDental3DViewer && typeof this.modalDental3DViewer.resetCamera === 'function') {
            this.modalDental3DViewer.resetCamera();
        }
    }

    toggleWireframe() {
        if (this.modalDental3DViewer && typeof this.modalDental3DViewer.toggleWireframe === 'function') {
            this.modalDental3DViewer.toggleWireframe();
        }
    }

    toggleAutoRotate() {
        if (this.modalDental3DViewer && typeof this.modalDental3DViewer.toggleAutoRotate === 'function') {
            this.modalDental3DViewer.toggleAutoRotate();
        }
    }

    // ==================== ERROR HANDLING ====================

    showError(message) {
        const loadingEl = document.getElementById('modalModelLoading');
        const errorEl = document.getElementById('modalModelError');
        
        loadingEl?.classList.add('hidden');
        errorEl?.classList.remove('hidden');
        
        if (errorEl) {
            errorEl.innerHTML = `
                <i class="fas fa-exclamation-triangle text-red-500 text-2xl mb-2"></i>
                <p class="text-red-600 mb-2">Failed to load 3D model</p>
                <p class="text-sm text-gray-600 mb-3">${message}</p>
                <button onclick="recordsManager.dental3DManager.retryInit()" class="px-3 py-1 bg-blue-500 text-white rounded text-sm hover:bg-blue-600">
                    Retry
                </button>
            `;
        }
    }

    retryInit() {
        console.log('🔄 Retrying 3D viewer initialization...');
        // This would need the chart data to be available
        // You might want to store it in the instance for retry purposes
        this.initModal3D(this.lastChartData);
    }

    // ==================== CLEANUP ====================

    cleanup() {
        if (this.modalDental3DViewer) {
            try {
                console.log('🧹 Cleaning up 3D viewer...');
                this.modalDental3DViewer.destroy();
            } catch (error) {
                console.warn('⚠️ Error destroying modal 3D viewer:', error);
            }
            this.modalDental3DViewer = null;
        }
    }

    // ==================== DEBUG METHODS ====================

    addDebugColors() {
        if (!this.modalDental3DViewer) {
            console.error('❌ 3D viewer not initialized');
            return;
        }
        
        console.log('🌈 Applying debug colors to all meshes...');
        
        // Test colors for each mesh index
        const debugColors = [
            '#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#FF00FF', '#00FFFF',
            '#FFA500', '#800080', '#008000', '#FFC0CB', '#A52A2A', '#808080',
            '#FFD700', '#FF69B4', '#32CD32', '#8A2BE2', '#FF1493', '#00CED1',
            '#FF4500', '#9ACD32', '#DC143C', '#00BFFF', '#FA8072', '#90EE90',
            '#F0E68C', '#DDA0DD', '#87CEEB', '#F4A460', '#98FB98', '#F5DEB3',
            '#CD853F', '#DCDCDC'
        ];
        
        // Try different tooth numbering systems
        for (let i = 0; i < 32; i++) {
            const toothNumber = i + 1;
            const colorHex = debugColors[i % debugColors.length];
            
            // Convert hex to RGB
            const hex = colorHex.replace('#', '');
            const r = parseInt(hex.substr(0, 2), 16) / 255;
            const g = parseInt(hex.substr(2, 2), 16) / 255;
            const b = parseInt(hex.substr(4, 2), 16) / 255;
            
            console.log(`🎨 Debug tooth ${toothNumber}: ${colorHex} -> RGB(${r.toFixed(2)}, ${g.toFixed(2)}, ${b.toFixed(2)})`);
            
            try {
                this.modalDental3DViewer.setToothColor(toothNumber, { r, g, b }, false);
            } catch (error) {
                console.warn(`⚠️ Failed to set debug color for tooth ${toothNumber}:`, error);
            }
        }
        
        console.log('🌈 Debug colors applied to teeth 1-32');
    }

    testMissingTooth(toothNumber) {
        if (this.modalDental3DViewer && typeof this.modalDental3DViewer.testMissingTooth === 'function') {
            return this.modalDental3DViewer.testMissingTooth(toothNumber);
        }
        console.warn('⚠️ testMissingTooth method not available');
    }

    debugVisibleMeshes() {
        if (this.modalDental3DViewer && typeof this.modalDental3DViewer.debugVisibleMeshes === 'function') {
            this.modalDental3DViewer.debugVisibleMeshes();
        }
    }

    // ==================== UTILITY METHODS ====================

    isInitialized() {
        return this.modalDental3DViewer !== null;
    }

    getViewer() {
        return this.modalDental3DViewer;
    }

    // Store chart data for retry functionality
    setChartData(chartData) {
        this.lastChartData = chartData;
    }
}

// Export for use
window.Dental3DManager = Dental3DManager;
