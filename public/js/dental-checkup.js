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
        // Initialize 3D dental model viewer
        this.dental3DViewer = new Dental3DViewer('dentalModelViewer', {
            modelUrl: '/img/permanent_dentition-2.glb',
            enableToothSelection: true,
            showControls: true,
            onToothClick: (toothNumber, clickPoint, event, data) => {
                this.handleToothClick(toothNumber, clickPoint, event, data);
            },
            onModelLoaded: () => {
                // Update 3D model colors when model is loaded
                this.update3DModelColors();
            }
        });
        
        // Initialize after DOM is ready
        setTimeout(() => {
            this.dental3DViewer.init();
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
        // Auto-save form data every 30 seconds
        this.autoSaveTimer = setInterval(() => {
            this.saveFormData();
        }, 30000);
        
        // Load previously saved data
        this.loadAutoSavedData();
        
        // Clear saved data when form is submitted
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', () => {
                this.clearAutoSavedData();
            });
        }
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
        
        // Position the popup
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
        
        popup.style.display = 'block';
        this.popupVisible = true;
    }
    
    positionPopup(popup, worldPosition, event) {
        const canvas = document.querySelector('#dentalModelViewer canvas');
        const rect = canvas.getBoundingClientRect();
        
        // Convert 3D world position to screen position
        const vector = worldPosition.clone();
        vector.project(this.dental3DViewer.camera);
        
        const x = (vector.x * 0.5 + 0.5) * rect.width;
        const y = (vector.y * -0.5 + 0.5) * rect.height;
        
        // Get popup dimensions - smaller sizes
        const popupWidth = window.innerWidth <= 480 ? 240 : window.innerWidth <= 768 ? 280 : 300;
        const popupHeight = 250; // Reduced from 320
        
        // Calculate initial position
        let popupX = x + 20;
        let popupY = y - popupHeight / 2;
        
        // Adjust if popup would go outside canvas bounds
        if (popupX + popupWidth > rect.width) {
            popupX = x - popupWidth - 20;
        }
        if (popupX < 0) {
            popupX = 10;
        }
        
        if (popupY < 0) {
            popupY = 10;
        }
        if (popupY + popupHeight > rect.height) {
            popupY = rect.height - popupHeight - 10;
        }
        
        // Ensure popup stays within viewport
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        const canvasLeft = rect.left;
        const canvasTop = rect.top;
        
        const absoluteX = canvasLeft + popupX;
        const absoluteY = canvasTop + popupY;
        
        // Adjust for viewport boundaries
        if (absoluteX + popupWidth > viewportWidth) {
            popupX = viewportWidth - canvasLeft - popupWidth - 10;
        }
        if (absoluteY + popupHeight > viewportHeight) {
            popupY = viewportHeight - canvasTop - popupHeight - 10;
        }
        
        // Final bounds check
        popupX = Math.max(10, Math.min(popupX, rect.width - popupWidth - 10));
        popupY = Math.max(10, Math.min(popupY, rect.height - popupHeight - 10));
        
        popup.style.left = popupX + 'px';
        popup.style.top = popupY + 'px';
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
        // Update all teeth colors in the 3D model based on current conditions
        for (let i = 1; i <= 32; i++) {
            this.update3DToothColor(i);
        }
    }
    
    update3DToothColor(toothNumber) {
        if (!this.dental3DViewer) return;
        
        const conditionSelect = document.querySelector(`select[name="dental_chart[${toothNumber}][condition]"]`);
        if (!conditionSelect) return;
        
        const condition = conditionSelect.value;
        let color = null;
        
        // Define colors based on tooth condition - more vibrant to match chart colors
        switch (condition) {
            case 'healthy':
                color = { r: 0.4, g: 0.9, b: 0.4 }; // Bright green
                break;
            case 'cavity':
                color = { r: 0.9, g: 0.2, b: 0.2 }; // Bright red
                break;
            case 'filled':
                color = { r: 0.3, g: 0.6, b: 1.0 }; // Bright blue
                break;
            case 'crown':
                color = { r: 1.0, g: 0.8, b: 0.2 }; // Gold/yellow
                break;
            case 'missing':
                color = { r: 0.0, g: 0.0, b: 0.0 }; // Black with full transparency (will be invisible)
                break;
            case 'root_canal':
                color = { r: 1.0, g: 0.6, b: 0.2 }; // Orange
                break;
            case 'extraction_needed':
                color = { r: 0.8, g: 0.1, b: 0.1 }; // Dark red
                break;
            default:
                color = null; // Default tooth color
        }
        
        // Apply color to the 3D model tooth
        this.dental3DViewer.setToothColor(toothNumber, color);
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
        const form = document.querySelector('form');
        if (form) {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            localStorage.setItem('checkupFormData', JSON.stringify(data));
        }
    }
    
    loadAutoSavedData() {
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
        console.log('=== TOOTH MAPPING DEBUG ===');
        if (this.dental3DViewer && this.dental3DViewer.toothMeshes) {
            console.log(`Total tooth meshes found: ${this.dental3DViewer.toothMeshes.length}`);
            
            this.dental3DViewer.toothMeshes.forEach((mesh, index) => {
                const position = mesh.position;
                console.log(`Mesh ${index}: Position (${position.x.toFixed(2)}, ${position.y.toFixed(2)}, ${position.z.toFixed(2)}) -> Name: "${mesh.name}"`);
            });
        }
    }
    
    destroy() {
        this.clearAutoSavedData();
        if (this.dental3DViewer) {
            this.dental3DViewer.destroy();
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
}); 