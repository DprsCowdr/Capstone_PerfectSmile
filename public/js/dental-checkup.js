/**
 * Patient Checkup Page JavaScript
 * Handles dental chart interactions and 3D model integration
 */

class PatientCheckup {
    constructor() {
        this.dental3DViewer = null;
        this.selectedTooth = null;
        this.popupVisible = false;
        this.autoSaveTimer = null;
        
        this.init();
    }
    
    init() {
        this.initDental3D();
        this.initDentalChart();
        this.initAutoSave();
        this.setupEventListeners();
    }
    
    initDental3D() {
        // Initialize 3D dental model viewer with enhanced mapping
        this.dental3DViewer = new Dental3DViewer('dentalModelViewer', {
            modelUrl: '/img/permanent_dentition-2.glb',
            enableToothSelection: true,
            showControls: true,
            onToothClick: (toothNumber, clickPoint, event, data) => {
                this.handleToothClick(toothNumber, clickPoint, event, data);
            },
            onModelLoaded: () => {
                console.log('🦷 3D Dental model loaded successfully');
                
                // Enable debug mode for detailed mapping information
                this.dental3DViewer.setDebugMode(true);
                
                // Log mapping configuration
                const config = this.dental3DViewer.getMappingConfig();
                console.log('📋 Mapping Configuration:', config);
                
                // Debug the current tooth mapping
                this.dental3DViewer.debugMapping();
                
                // Force-apply manual mapping to ensure specific mesh->tooth mapping
                if (this.dental3DViewer.manualToothMapping) {
                    Object.entries(this.dental3DViewer.manualToothMapping).forEach(([meshIndex, toothNumber]) => {
                        this.dental3DViewer.setManualToothMapping(parseInt(meshIndex, 10), parseInt(toothNumber, 10));
                    });
                    this.dental3DViewer.debugMapping();
                }

                // Update 3D model colors when model is loaded
                this.update3DModelColors();
                // Safety re-apply after a tick in case materials settle
                setTimeout(() => this.update3DModelColors(), 150);
                // Additional safety re-apply after manual mapping is applied
                setTimeout(() => this.update3DModelColors(), 500);
                
                // Log successful initialization
                console.log('✅ 3D Dental viewer initialization complete');
            }
        });
        
        // Initialize after DOM is ready
        setTimeout(() => {
            const success = this.dental3DViewer.init();
            if (!success) {
                console.error('❌ Failed to initialize 3D dental viewer');
            }
        }, 100);
    }
    
    initDentalChart() {
        // Initialize dental chart interactions
        this.updateAllToothAppearances();
        
        // Set up event listeners for chart form changes
        this.setupChartEventListeners();
        
        // Load existing data from the form (which comes from database)
        this.loadExistingToothData();
    }
    
    loadExistingToothData() {
        // This loads existing tooth data from the form fields
        // The form fields are already populated by PHP from the database
        for (let i = 1; i <= 32; i++) {
            const conditionSelect = document.querySelector(`select[name="dental_chart[${i}][condition]"]`);
            if (conditionSelect && conditionSelect.value) {
                // Update both 2D chart appearance and 3D model color
                this.updateToothAppearance(i);
                // 3D model colors will be updated when model loads via onModelLoaded callback
            }
        }
    }
    
    setupChartEventListeners() {
        // Listen for changes in the dental chart form fields
        for (let i = 1; i <= 32; i++) {
            const conditionSelect = document.querySelector(`select[name="dental_chart[${i}][condition]"]`);
            const treatmentSelect = document.querySelector(`select[name="dental_chart[${i}][treatment]"]`);
            const notesTextarea = document.querySelector(`textarea[name="dental_chart[${i}][notes]"]`);
            
            if (conditionSelect) {
                conditionSelect.addEventListener('change', () => {
                    this.updateToothAppearance(i);
                    this.update3DToothColor(i);
                    this.saveFormData();
                });
            }
            
            if (treatmentSelect) {
                treatmentSelect.addEventListener('change', () => {
                    this.saveFormData();
                });
            }
            
            if (notesTextarea) {
                notesTextarea.addEventListener('input', () => {
                    this.saveFormData();
                });
            }
        }
    }
    
    initAutoSave() {
        // DISABLED: Auto-save interferes with database data loading
        // Only load from database, not localStorage
        
        // Clear any existing saved data to prevent conflicts
        this.clearAutoSavedData();
        
        // Clear saved data when form is submitted (keep this for cleanup)
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', () => {
                this.clearAutoSavedData();
            });
        }
        
        console.log('Auto-save disabled - dental chart will only load from database');
    }
    
    setupEventListeners() {
        // Close popup when clicking outside
        document.addEventListener('click', (event) => {
            const popup = document.getElementById('treatmentPopup');
            const canvas = document.querySelector('#dentalModelViewer canvas');
            
            if (this.popupVisible && popup && !popup.contains(event.target) && 
                (!canvas || !canvas.contains(event.target))) {
                this.closeTreatmentPopup();
            }
        });
        
        // Close dental chart menus when clicking outside
        document.addEventListener('click', (event) => {
            if (!event.target.closest('.relative')) {
                for (let i = 1; i <= 32; i++) {
                    const menu = document.getElementById(`tooth-menu-${i}`);
                    if (menu) {
                        menu.classList.add('hidden');
                    }
                }
            }
        });
    }
    
    handleToothClick(toothNumber, clickPoint, event, data) {
        console.group(`🦷 Tooth Click Debug - Tooth #${toothNumber}`);
        console.log('📍 Click Details:', {
            toothNumber,
            toothName: data.toothName,
            meshIndex: data.meshIndex,
            clickPoint: {
                x: clickPoint.x.toFixed(3),
                y: clickPoint.y.toFixed(3),
                z: clickPoint.z.toFixed(3)
            }
        });
        
        // Get detailed tooth information
        if (this.dental3DViewer) {
            const toothInfo = this.dental3DViewer.getToothDetails(toothNumber);
            if (toothInfo) {
                console.log('🔍 Tooth Details:', toothInfo);
            }
        }
        
        // Get current form data for this tooth
        const formData = this.getToothConditionFromDatabase(toothNumber);
        console.log('📋 Current Form Data:', formData);
        
        console.groupEnd();
        
        this.selectedTooth = toothNumber;
        this.showTreatmentPopup(toothNumber, clickPoint, event, data);
    }
    
    showTreatmentPopup(toothNumber, worldPosition, event, data) {
        const popup = document.getElementById('treatmentPopup');
        const highlight = document.getElementById('toothHighlight');
        const title = document.getElementById('popupTitle');
        const content = document.querySelector('.treatment-popup-content');
        
        if (!popup || !title || !content) return;
        
        // Update popup title
        title.textContent = `${data.toothName} - Tooth #${toothNumber}`;
        
        // Get existing form data for this tooth
        const conditionSelect = document.querySelector(`select[name="dental_chart[${toothNumber}][condition]"]`);
        const treatmentSelect = document.querySelector(`select[name="dental_chart[${toothNumber}][treatment]"]`);
        const notesTextarea = document.querySelector(`textarea[name="dental_chart[${toothNumber}][notes]"]`);
        
        const currentCondition = conditionSelect ? conditionSelect.value : '';
        const currentTreatment = treatmentSelect ? treatmentSelect.value : '';
        const currentNotes = notesTextarea ? notesTextarea.value : '';
        
        // Create form content
        content.innerHTML = `
            <div class="treatment-popup-icon">
                <i class="fas fa-tooth"></i>
            </div>
            <div class="space-y-3">
                <div>
                    <label for="popup-condition-${toothNumber}" class="block text-xs font-medium text-gray-700 mb-1">Condition:</label>
                    <select id="popup-condition-${toothNumber}" class="w-full px-2 py-1.5 border-2 border-gray-200 rounded-md focus:border-blue-500 focus:ring-1 focus:ring-blue-200 transition-all text-xs">
                        <option value="">Select condition</option>
                        <option value="healthy" ${currentCondition === 'healthy' ? 'selected' : ''}>Healthy</option>
                        <option value="cavity" ${currentCondition === 'cavity' ? 'selected' : ''}>Cavity</option>
                        <option value="filled" ${currentCondition === 'filled' ? 'selected' : ''}>Filled</option>
                        <option value="crown" ${currentCondition === 'crown' ? 'selected' : ''}>Crown</option>
                        <option value="missing" ${currentCondition === 'missing' ? 'selected' : ''}>Missing</option>
                        <option value="root_canal" ${currentCondition === 'root_canal' ? 'selected' : ''}>Root Canal</option>
                        <option value="extraction_needed" ${currentCondition === 'extraction_needed' ? 'selected' : ''}>Extraction Needed</option>
                    </select>
                </div>
                <div>
                    <label for="popup-treatment-${toothNumber}" class="block text-xs font-medium text-gray-700 mb-1">Treatment:</label>
                    <select id="popup-treatment-${toothNumber}" class="w-full px-2 py-1.5 border-2 border-gray-200 rounded-md focus:border-blue-500 focus:ring-1 focus:ring-blue-200 transition-all text-xs">
                        <option value="">Select treatment</option>
                        <option value="no_treatment" ${currentTreatment === 'no_treatment' ? 'selected' : ''}>No Treatment Needed</option>
                        <option value="cleaning" ${currentTreatment === 'cleaning' ? 'selected' : ''}>Cleaning</option>
                        <option value="filling" ${currentTreatment === 'filling' ? 'selected' : ''}>Filling</option>
                        <option value="crown" ${currentTreatment === 'crown' ? 'selected' : ''}>Crown</option>
                        <option value="root_canal" ${currentTreatment === 'root_canal' ? 'selected' : ''}>Root Canal</option>
                        <option value="extraction" ${currentTreatment === 'extraction' ? 'selected' : ''}>Extraction</option>
                        <option value="bridge" ${currentTreatment === 'bridge' ? 'selected' : ''}>Bridge</option>
                        <option value="implant" ${currentTreatment === 'implant' ? 'selected' : ''}>Implant</option>
                    </select>
                </div>
                <div>
                    <label for="popup-notes-${toothNumber}" class="block text-xs font-medium text-gray-700 mb-1">Notes:</label>
                    <textarea id="popup-notes-${toothNumber}" rows="2" class="w-full px-2 py-1.5 border-2 border-gray-200 rounded-md focus:border-blue-500 focus:ring-1 focus:ring-blue-200 transition-all resize-none text-xs" placeholder="Additional notes...">${currentNotes}</textarea>
                </div>
                <div class="flex space-x-2 pt-1">
                    <button onclick="patientCheckup.saveToothData(${toothNumber})" class="flex-1 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-3 py-2 rounded-md font-medium transition-all transform hover:-translate-y-0.5 shadow-sm hover:shadow-md text-xs">
                        <i class="fas fa-save mr-1"></i>Save
                    </button>
                    <button onclick="patientCheckup.closeTreatmentPopup()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-md font-medium transition-all border border-gray-200 hover:border-gray-300 text-xs">
                        <i class="fas fa-times mr-1"></i>Close
                    </button>
                </div>
            </div>
        `;
        
        // Position the popup after content is set
        this.positionPopup(popup, worldPosition, event);
        
        // Show highlight
        if (highlight) {
            const canvas = document.querySelector('#dentalModelViewer canvas');
            const rect = canvas.getBoundingClientRect();
            const vector = worldPosition.clone();
            vector.project(this.dental3DViewer.camera);
            
            const x = (vector.x * 0.5 + 0.5) * rect.width;
            const y = (vector.y * -0.5 + 0.5) * rect.height;
            
            highlight.style.left = (x - 10) + 'px';
            highlight.style.top = (y - 10) + 'px';
            highlight.style.display = 'block';
        }
        
        // Ensure popup is visible (positionPopup already handles display)
        this.popupVisible = true;
        
        console.log(`🎯 Treatment popup shown for tooth ${toothNumber}`);
    }
    
    positionPopup(popup, worldPosition, event) {
        const canvas = document.querySelector('#dentalModelViewer canvas');
        const viewerContainer = document.getElementById('dentalModelViewer');
        
        if (!canvas || !viewerContainer) {
            console.error('❌ Canvas or viewer container not found');
            return;
        }
        
        const rect = canvas.getBoundingClientRect();
        const containerRect = viewerContainer.getBoundingClientRect();
        
        // Convert 3D world position to screen position
        const vector = worldPosition.clone();
        vector.project(this.dental3DViewer.camera);
        
        const screenX = (vector.x * 0.5 + 0.5) * rect.width;
        const screenY = (vector.y * -0.5 + 0.5) * rect.height;
        
        // Ensure popup is visible for measurement
        popup.style.display = 'block';
        popup.style.visibility = 'hidden'; // Hidden for measurement only
        popup.style.position = 'absolute';
        
        // Force a reflow to get accurate dimensions
        popup.offsetHeight;
        
        const pRect = popup.getBoundingClientRect();
        const popupWidth = Math.min(pRect.width || 300, containerRect.width - 30);
        const popupHeight = Math.min(pRect.height || 350, containerRect.height - 30);
        
        // Define safe margins from canvas edges
        const margin = 15;
        const minX = margin;
        const minY = margin;
        const maxX = rect.width - popupWidth - margin;
        const maxY = rect.height - popupHeight - margin;
        
        // Start with preferred position (right of tooth)
        let popupX = screenX + 25;
        let popupY = screenY - popupHeight / 2;
        
        // If popup would go outside right edge, place it on the left
        if (popupX > maxX) {
            popupX = screenX - popupWidth - 25;
        }
        
        // If still outside left edge, center it horizontally
        if (popupX < minX) {
            popupX = Math.max(minX, (rect.width - popupWidth) / 2);
        }
        
        // Ensure horizontal position is within bounds
        popupX = Math.max(minX, Math.min(popupX, maxX));
        
        // Adjust vertical position to stay within bounds
        if (popupY < minY) {
            popupY = minY;
        } else if (popupY > maxY) {
            popupY = maxY;
        }
        
        // Final safety check - if popup is still too big for canvas
        if (popupWidth > rect.width - (margin * 2)) {
            popupX = margin;
            const maxWidth = rect.width - (margin * 2);
            popup.style.maxWidth = `${maxWidth}px`;
            popup.style.overflowX = 'hidden';
        }
        
        if (popupHeight > rect.height - (margin * 2)) {
            popupY = margin;
            const maxHeight = rect.height - (margin * 2);
            popup.style.maxHeight = `${maxHeight}px`;
            popup.style.overflowY = 'auto';
        }
        
        // Apply final position
        popup.style.left = `${popupX}px`;
        popup.style.top = `${popupY}px`;
        popup.style.zIndex = '1000';
        popup.style.visibility = 'visible';
        
        // Verify popup is within bounds after positioning
        setTimeout(() => {
            const finalRect = popup.getBoundingClientRect();
            const canvasRect = canvas.getBoundingClientRect();
            
            if (finalRect.left < canvasRect.left || finalRect.right > canvasRect.right ||
                finalRect.top < canvasRect.top || finalRect.bottom > canvasRect.bottom) {
                
                console.warn('⚠️ Popup positioned outside canvas bounds, repositioning...');
                popup.style.left = `${margin}px`;
                popup.style.top = `${margin}px`;
                popup.style.maxWidth = `${rect.width - (margin * 2)}px`;
                popup.style.maxHeight = `${rect.height - (margin * 2)}px`;
            }
        }, 0);
        
        console.log(`🎯 Popup positioned at (${popupX}, ${popupY}) within canvas (${rect.width}x${rect.height})`);
    }
    
    closeTreatmentPopup() {
        const popup = document.getElementById('treatmentPopup');
        const highlight = document.getElementById('toothHighlight');
        
        if (popup) popup.style.display = 'none';
        if (highlight) highlight.style.display = 'none';
        
        // Reset only tooth highlights, keep condition colors
        if (this.dental3DViewer) {
            this.dental3DViewer.resetHighlights();
        }
        
        this.selectedTooth = null;
        this.popupVisible = false;
    }
    
    saveToothData(toothNumber) {
        // Get values from popup form
        const conditionSelect = document.getElementById(`popup-condition-${toothNumber}`);
        const treatmentSelect = document.getElementById(`popup-treatment-${toothNumber}`);
        const notesTextarea = document.getElementById(`popup-notes-${toothNumber}`);
        
        if (!conditionSelect || !treatmentSelect || !notesTextarea) return;
        
        // Update the actual form fields
        const formConditionSelect = document.querySelector(`select[name="dental_chart[${toothNumber}][condition]"]`);
        const formTreatmentSelect = document.querySelector(`select[name="dental_chart[${toothNumber}][treatment]"]`);
        const formNotesTextarea = document.querySelector(`textarea[name="dental_chart[${toothNumber}][notes]"]`);
        
        if (formConditionSelect) formConditionSelect.value = conditionSelect.value;
        if (formTreatmentSelect) formTreatmentSelect.value = treatmentSelect.value;
        if (formNotesTextarea) formNotesTextarea.value = notesTextarea.value;
        
        // Update tooth appearance in both 2D chart and 3D model
        this.updateToothAppearance(toothNumber);
        this.update3DToothColor(toothNumber);
        
        // Close the popup
        this.closeTreatmentPopup();
        
        // Auto-save the form data
        this.saveFormData();
        
        // Show success feedback
        this.showSaveSuccess();
    }
    
    showSaveSuccess() {
        // Create a temporary success message
        const successMsg = document.createElement('div');
        successMsg.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 text-sm';
        successMsg.innerHTML = '<i class="fas fa-check mr-2"></i>Tooth data saved!';
        document.body.appendChild(successMsg);
        
        // Remove after 2 seconds
        setTimeout(() => {
            if (successMsg.parentNode) {
                successMsg.parentNode.removeChild(successMsg);
            }
        }, 2000);
    }
    
    update3DModelColors() {
        if (!this.dental3DViewer || !this.dental3DViewer.isLoaded) {
            console.warn('⚠️ 3D model not loaded yet, skipping color update');
            return;
        }
        
        console.log('🎨 Updating all 3D model colors...');
        // Update all teeth colors in the 3D model based on current conditions
        for (let i = 1; i <= 32; i++) {
            this.update3DToothColor(i);
        }
        console.log('✅ 3D model colors update complete');
    }
    
    update3DToothColor(toothNumber) {
        if (!this.dental3DViewer) {
            console.warn('⚠️ 3D viewer not available for tooth coloring');
            return;
        }
        
        const conditionSelect = document.querySelector(`select[name="dental_chart[${toothNumber}][condition]"]`);
        if (!conditionSelect) {
            console.warn(`⚠️ No condition select found for tooth ${toothNumber}`);
            return;
        }
        
        let condition = conditionSelect.value;
        let color = null;
        let isMissing = false;
        
        // Define colors based on tooth condition with enhanced visual distinction
        switch (condition) {
            case 'healthy':
                color = { r: 0.2, g: 0.8, b: 0.2 }; // Bright green
                break;
            case 'cavity':
                color = { r: 0.9, g: 0.1, b: 0.1 }; // Bright red
                break;
            case 'filled':
                color = { r: 0.2, g: 0.5, b: 0.9 }; // Bright blue
                break;
            case 'crown':
                color = { r: 1.0, g: 0.8, b: 0.1 }; // Gold/yellow
                break;
            case 'missing':
                isMissing = true; // Special handling for missing teeth
                break;
            case 'root_canal':
                color = { r: 1.0, g: 0.5, b: 0.1 }; // Orange
                break;
            case 'extraction_needed':
                color = { r: 0.7, g: 0.1, b: 0.1 }; // Dark red
                break;
            default:
                color = null; // Default tooth color
        }
        
        // Fallback: if no condition color decided, try using treatment to infer color
        if (!color && !isMissing) {
            const treatmentSelect = document.querySelector(`select[name="dental_chart[${toothNumber}][treatment]"]`);
            const treatment = treatmentSelect ? treatmentSelect.value : '';
            switch (treatment) {
                case 'extraction':
                    condition = 'extraction_needed';
                    color = { r: 0.7, g: 0.1, b: 0.1 }; // Dark red
                    break;
                case 'root_canal':
                    condition = 'root_canal';
                    color = { r: 1.0, g: 0.5, b: 0.1 }; // Orange
                    break;
                case 'filling':
                    condition = 'filled';
                    color = { r: 0.2, g: 0.5, b: 0.9 }; // Blue
                    break;
                case 'crown':
                    condition = 'crown';
                    color = { r: 1.0, g: 0.8, b: 0.1 }; // Yellow
                    break;
                case 'cleaning':
                    condition = 'healthy';
                    color = { r: 0.2, g: 0.8, b: 0.2 }; // Green
                    break;
                default:
                    // leave as default
                    break;
            }
        }
        
        // Apply color/missing status to the 3D model tooth
        const success = this.dental3DViewer.setToothColor(toothNumber, color, isMissing);
        
        if (!success) {
            console.warn(`⚠️ Failed to apply color to tooth ${toothNumber} in 3D model`);
        } else {
            console.log(`✅ Applied ${condition} styling to tooth ${toothNumber}`);
        }
    }
    
    getToothConditionFromDatabase(toothNumber) {
        // This method would typically make an AJAX call to get existing data
        // For now, we'll check if there's already data in the form fields
        const conditionSelect = document.querySelector(`select[name="dental_chart[${toothNumber}][condition]"]`);
        const treatmentSelect = document.querySelector(`select[name="dental_chart[${toothNumber}][treatment]"]`);
        const notesTextarea = document.querySelector(`textarea[name="dental_chart[${toothNumber}][notes]"]`);
        
        return {
            condition: conditionSelect ? conditionSelect.value : '',
            treatment: treatmentSelect ? treatmentSelect.value : '',
            notes: notesTextarea ? notesTextarea.value : ''
        };
    }
    
    addTreatment() {
        if (this.selectedTooth) {
            // Close the popup
            this.closeTreatmentPopup();
            
            // Keep the tooth highlighted for a moment while transitioning to chart
            setTimeout(() => {
                if (!this.popupVisible && this.dental3DViewer) {
                    this.dental3DViewer.resetAllTeethColor();
                }
            }, 3000);
            
            // Scroll to the dental chart
            const chartSection = document.querySelector('.bg-gray-50.rounded-lg');
            if (chartSection) {
                chartSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            
            // Highlight the corresponding tooth in the chart
            const toothButton = document.getElementById(`tooth-${this.selectedTooth}`);
            if (toothButton) {
                toothButton.style.boxShadow = '0 0 0 3px #3b82f6';
                setTimeout(() => {
                    toothButton.style.boxShadow = '';
                }, 2000);
                
                // Open the tooth menu
                this.selectTooth(this.selectedTooth);
            }
        }
    }
    
    selectTooth(toothNumber) {
        // Close all other menus
        for (let i = 1; i <= 32; i++) {
            if (i !== toothNumber) {
                const menu = document.getElementById(`tooth-menu-${i}`);
                if (menu) menu.classList.add('hidden');
            }
        }
        
        // Toggle current menu
        const menu = document.getElementById(`tooth-menu-${toothNumber}`);
        if (menu) {
            menu.classList.toggle('hidden');
        }
        
        // Update tooth appearance
        this.updateToothAppearance(toothNumber);
    }
    
    closeToothMenu(toothNumber) {
        const menu = document.getElementById(`tooth-menu-${toothNumber}`);
        if (menu) menu.classList.add('hidden');
        this.updateToothAppearance(toothNumber);
    }
    
    updateToothAppearance(toothNumber) {
        const tooth = document.getElementById(`tooth-${toothNumber}`);
        const conditionSelect = document.querySelector(`select[name="dental_chart[${toothNumber}][condition]"]`);
        
        if (!tooth || !conditionSelect) return;
        
        // Reset to default classes (matching the responsive structure from HTML)
        tooth.className = 'w-6 h-6 sm:w-8 sm:h-8 border-2 rounded-full hover:border-blue-500 transition-colors text-xs font-bold';
        
        // Apply styling based on condition
        if (conditionSelect.value) {
            switch (conditionSelect.value) {
                case 'healthy':
                    tooth.classList.add('bg-green-100', 'border-green-300');
                    break;
                case 'cavity':
                    tooth.classList.add('bg-red-100', 'border-red-300');
                    break;
                case 'filled':
                    tooth.classList.add('bg-blue-100', 'border-blue-300');
                    break;
                case 'crown':
                    tooth.classList.add('bg-yellow-100', 'border-yellow-300');
                    break;
                case 'missing':
                    tooth.classList.add('bg-gray-100', 'border-gray-300', 'opacity-50');
                    break;
                case 'root_canal':
                    tooth.classList.add('bg-orange-100', 'border-orange-300');
                    break;
                case 'extraction_needed':
                    tooth.classList.add('bg-red-200', 'border-red-400');
                    break;
                default:
                    tooth.classList.add('bg-white', 'border-gray-300');
            }
        } else {
            tooth.classList.add('bg-white', 'border-gray-300');
        }
    }
    
    updateAllToothAppearances() {
        for (let i = 1; i <= 32; i++) {
            this.updateToothAppearance(i);
        }
    }
    
    saveFormData() {
        // DISABLED: Auto-save feature disabled to prevent localStorage interference 
        // with database data loading
        return;
        
        const form = document.querySelector('form');
        if (form) {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            localStorage.setItem('checkupFormData', JSON.stringify(data));
        }
    }
    
    loadAutoSavedData() {
        // DISABLED: Do not load from localStorage to avoid overriding database data
        // The form should only display data from the database via PHP rendering
        return;
        
        const savedData = localStorage.getItem('checkupFormData');
        if (savedData) {
            try {
                const data = JSON.parse(savedData);
                Object.keys(data).forEach(key => {
                    const element = document.querySelector(`[name="${key}"]`);
                    if (element) {
                        element.value = data[key];
                        if (key.includes('dental_chart') && key.includes('condition')) {
                            const toothNumber = key.match(/\[(\d+)\]/)[1];
                            this.updateToothAppearance(parseInt(toothNumber));
                        }
                    }
                });
            } catch (e) {
                console.error('Error loading auto-saved data:', e);
            }
        }
    }
    
    clearAutoSavedData() {
        localStorage.removeItem('checkupFormData');
        if (this.autoSaveTimer) {
            clearInterval(this.autoSaveTimer);
        }
    }
    
    // Model control methods
    resetCamera() {
        if (this.dental3DViewer) {
            this.dental3DViewer.resetCamera();
        }
    }
    
    toggleWireframe() {
        if (this.dental3DViewer) {
            this.dental3DViewer.toggleWireframe();
        }
    }
    
    toggleAutoRotate() {
        if (this.dental3DViewer) {
            this.dental3DViewer.toggleAutoRotate();
        }
    }
    
    debugToothMapping() {
        console.log('=== ENHANCED TOOTH MAPPING DEBUG ===');
        if (this.dental3DViewer) {
            // Use the enhanced debug functionality
            this.dental3DViewer.debugMapping();
            
            // Show mapping configuration
            const config = this.dental3DViewer.getMappingConfig();
            console.log('🔧 Current Configuration:', config);
            
            // Export mapping data for analysis
            console.log('📤 Exporting mapping data for analysis...');
            const exportData = this.dental3DViewer.exportMapping();
            
            // Test a few specific teeth
            console.log('🧪 Testing specific tooth information:');
            [1, 8, 9, 16, 17, 24, 25, 32].forEach(toothNum => {
                const info = this.dental3DViewer.getToothDetails(toothNum);
                if (info) {
                    console.log(`Tooth ${toothNum} (${info.toothName}):`, info);
                } else {
                    console.warn(`⚠️ No information found for tooth ${toothNum}`);
                }
            });
        } else {
            console.error('❌ 3D viewer not available for debugging');
        }
    }
    
    // Method to apply manual mapping data from external source
    applyManualMapping(mappingData) {
        if (!this.dental3DViewer) {
            console.error('❌ 3D viewer not available for applying manual mapping');
            return false;
        }
        
        console.group('🔧 Applying Manual Tooth Mapping');
        
        try {
            // Validate mapping data structure
            if (!mappingData.manualMapping || typeof mappingData.manualMapping !== 'object') {
                throw new Error('Invalid mapping data structure');
            }
            
            const corrections = [];
            
            // Convert manual mapping to corrections format
            Object.entries(mappingData.manualMapping).forEach(([meshIndex, toothNumber]) => {
                corrections.push({
                    meshIndex: parseInt(meshIndex),
                    toothNumber: parseInt(toothNumber)
                });
            });
            
            console.log(`📋 Applying ${corrections.length} manual mappings`);
            
            // Apply corrections to the 3D viewer
            this.dental3DViewer.recalibrateMapping(corrections);
            
            // Update the 3D model colors based on current conditions
            this.update3DModelColors();
            
            console.log('✅ Manual mapping applied successfully');
            console.groupEnd();
            
            return true;
        } catch (error) {
            console.error('❌ Failed to apply manual mapping:', error);
            console.groupEnd();
            return false;
        }
    }
    
    // Method to load manual mapping from JSON string
    loadManualMappingFromJSON(jsonString) {
        try {
            const mappingData = JSON.parse(jsonString);
            return this.applyManualMapping(mappingData);
        } catch (error) {
            console.error('❌ Failed to parse manual mapping JSON:', error);
            return false;
        }
    }
    
    // Method to test all tooth mappings by applying test colors
    testToothMappings() {
        if (!this.dental3DViewer) {
            console.error('❌ 3D viewer not available for testing');
            return;
        }
        
        console.log('🎨 Testing tooth mappings with test colors...');
        
        // Define test colors for each quadrant
        const testColors = {
            'upper_right': { r: 1.0, g: 0.0, b: 0.0 }, // Red for teeth 1-8
            'upper_left': { r: 0.0, g: 1.0, b: 0.0 },  // Green for teeth 9-16
            'lower_left': { r: 0.0, g: 0.0, b: 1.0 },  // Blue for teeth 17-24
            'lower_right': { r: 1.0, g: 1.0, b: 0.0 }  // Yellow for teeth 25-32
        };
        
        // Apply test colors
        for (let tooth = 1; tooth <= 32; tooth++) {
            let color;
            if (tooth >= 1 && tooth <= 8) {
                color = testColors.upper_right;
            } else if (tooth >= 9 && tooth <= 16) {
                color = testColors.upper_left;
            } else if (tooth >= 17 && tooth <= 24) {
                color = testColors.lower_left;
            } else {
                color = testColors.lower_right;
            }
            
            this.dental3DViewer.setToothColor(tooth, color, false);
        }
        
        console.log('🎨 Test colors applied:');
        console.log('  🔴 Red: Upper Right (1-8)');
        console.log('  🟢 Green: Upper Left (9-16)');
        console.log('  🔵 Blue: Lower Left (17-24)');
        console.log('  🟡 Yellow: Lower Right (25-32)');
        
        // Reset colors after 5 seconds
        setTimeout(() => {
            this.dental3DViewer.resetAllTeethColor();
            this.update3DModelColors(); // Restore actual condition colors
            console.log('🔄 Test colors reset, restored original colors');
        }, 5000);
    }
    
    destroy() {
        this.clearAutoSavedData();
        if (this.dental3DViewer) {
            this.dental3DViewer.destroy();
        }
    }
    
    // Public method to force refresh 3D model colors (can be called from visual chart)
    forceRefresh3DColors() {
        console.log('🔄 Force refreshing 3D model colors...');
        if (this.dental3DViewer && this.dental3DViewer.isLoaded) {
            this.update3DModelColors();
        } else {
            console.warn('⚠️ 3D model not loaded, cannot refresh colors');
        }
    }
}

// Global functions for backward compatibility
let patientCheckup;

function selectTooth(toothNumber) {
    if (patientCheckup) {
        patientCheckup.selectTooth(toothNumber);
    }
}

function closeToothMenu(toothNumber) {
    if (patientCheckup) {
        patientCheckup.closeToothMenu(toothNumber);
    }
}

function resetCamera() {
    if (patientCheckup) {
        patientCheckup.resetCamera();
    }
}

function toggleWireframe() {
    if (patientCheckup) {
        patientCheckup.toggleWireframe();
    }
}

function toggleAutoRotate() {
    if (patientCheckup) {
        patientCheckup.toggleAutoRotate();
    }
}

function debugToothMapping() {
    if (patientCheckup) {
        patientCheckup.debugToothMapping();
    }
}

function testToothMappings() {
    if (patientCheckup) {
        patientCheckup.testToothMappings();
    }
}

function correctToothMapping(meshIndex, toothNumber) {
    if (patientCheckup) {
        return patientCheckup.dental3DViewer.mapMeshToTooth(meshIndex, toothNumber);
    }
    return false;
}

function applyManualMapping(mappingData) {
    if (patientCheckup) {
        return patientCheckup.applyManualMapping(mappingData);
    }
    return false;
}

function loadManualMappingFromJSON(jsonString) {
    if (patientCheckup) {
        return patientCheckup.loadManualMappingFromJSON(jsonString);
    }
    return false;
}

function switchToManualMapping() {
    if (patientCheckup && patientCheckup.dental3DViewer) {
        return patientCheckup.dental3DViewer.switchMappingMethod('manual');
    }
    return false;
}

function switchToAutoMapping() {
    if (patientCheckup && patientCheckup.dental3DViewer) {
        return patientCheckup.dental3DViewer.switchMappingMethod('auto');
    }
    return false;
}

function verifyManualMapping() {
    if (patientCheckup && patientCheckup.dental3DViewer) {
        console.log('🔍 Verifying Manual Mapping Integration...');
        const config = patientCheckup.dental3DViewer.getCurrentMappingInfo();
        console.table(config);
        
        // Test a few specific mappings
        const testMappings = [
            { mesh: 0, expectedTooth: 24 },
            { mesh: 17, expectedTooth: 8 },
            { mesh: 32, expectedTooth: 1 }
        ];
        
        testMappings.forEach(test => {
            const actualTooth = patientCheckup.dental3DViewer.mapMeshIndexToToothNumber(test.mesh);
            const status = actualTooth === test.expectedTooth ? '✅' : '❌';
            console.log(`${status} Mesh ${test.mesh}: Expected tooth ${test.expectedTooth}, Got tooth ${actualTooth}`);
        });
        
        return config;
    }
    return null;
}

function closeTreatmentPopup() {
    if (patientCheckup) {
        patientCheckup.closeTreatmentPopup();
    }
}

function addTreatment() {
    if (patientCheckup) {
        patientCheckup.addTreatment();
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    patientCheckup = new PatientCheckup();
    
    // Add helpful console commands for debugging
    console.log(`
🦷 DENTAL 3D VIEWER DEBUG COMMANDS
=====================================
Use these commands in the browser console:

📋 Basic Debugging:
- debugToothMapping()     : Show detailed mapping analysis
- testToothMappings()     : Apply test colors to verify mapping
- patientCheckup.dental3DViewer.debugMapping() : Raw mapping debug

🔧 Mapping Control:
- patientCheckup.dental3DViewer.switchMappingMethod('manual') : Use manual mapping
- patientCheckup.dental3DViewer.switchMappingMethod('auto') : Use auto mapping
- patientCheckup.dental3DViewer.getMappingConfig() : Get current mapping info

🎨 Visual Testing:
- patientCheckup.testToothMappings() : Apply quadrant colors for 5 seconds
- patientCheckup.dental3DViewer.resetAllTeethColor() : Reset all colors

📊 Information:
- patientCheckup.dental3DViewer.getCurrentMappingInfo() : Detailed mapping info
- patientCheckup.dental3DViewer.getToothDetails(N) : Get tooth N details

📥 Manual Mapping Integration:
- loadManualMappingFromJSON('{"manualMapping":{...}}') : Load manual mapping
- applyManualMapping(mappingData) : Apply mapping data object

🔗 Access Manual Mapping Tool:
- Visit: http://localhost:8083/manual-tooth-mapping.html

✅ MANUAL MAPPING ACTIVE:
Your manual tooth mapping has been integrated and is now the default!
Use 'manual' mapping method for best results.

Example Usage:
- debugToothMapping()
- testToothMappings()
- patientCheckup.dental3DViewer.switchMappingMethod('manual')
=====================================
    `);
}); 