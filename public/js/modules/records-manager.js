/**
 * Core Records Manager - Main Coordinator
 * Handles modal operations and coordinates between modules
 */

class RecordsManager {
    constructor() {
        this.currentPatientId = null;
        this.baseUrl = window.BASE_URL || '';
        
        // Initialize modules
        this.modalController = new ModalController();
        this.dataLoader = new DataLoader(this.baseUrl);
        this.displayManager = new DisplayManager();
        this.dental3DManager = new Dental3DManager(this.baseUrl);
        this.conditionsAnalyzer = new ConditionsAnalyzer();
        this.utilities = new RecordsUtilities(this.baseUrl);
        
        this.init();
    }

    init() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            this.modalController.setupModalEventListeners();
        });
    }

    // ==================== MAIN MODAL OPERATIONS ====================

    openPatientRecordsModal(patientId) {
        this.currentPatientId = patientId;
        this.modalController.openModal();
        this.loadPatientInfo(patientId);
        this.showRecordTab('basic-info');
    }

    closePatientRecordsModal() {
        this.modalController.closeModal();
        this.dental3DManager.cleanup();
        this.currentPatientId = null;
    }

    // ==================== TAB MANAGEMENT ====================

    showRecordTab(tabType) {
        this.modalController.setActiveTab(tabType);
        
        const patientId = this.currentPatientId;
        if (!patientId) return;

        switch(tabType) {
            case 'basic-info':
                this.loadPatientInfo(patientId);
                break;
            case 'dental-records':
                this.loadDentalRecords(patientId);
                break;
            case 'dental-chart':
                this.loadDentalChart(patientId);
                break;
            case 'appointments':
                this.loadAppointments(patientId);
                break;
            case 'treatments':
                this.loadTreatments(patientId);
                break;
            case 'medical-records':
                this.loadMedicalRecords(patientId);
                break;
            case 'invoice-history':
                this.loadInvoiceHistory(patientId);
                break;
            case 'prescriptions':
                this.loadPrescriptions(patientId);
                break;
        }
    }

    // ==================== DATA LOADING METHODS ====================

    async loadPatientInfo(patientId) {
        try {
            const data = await this.dataLoader.loadPatientInfo(patientId);
            if (data.success) {
                this.displayManager.displayPatientInfo(data.patient);
            } else {
                this.utilities.showAlert(data.message || 'Failed to load patient information', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.utilities.showAlert('An error occurred while loading patient information', 'error');
        }
    }

    async loadDentalRecords(patientId) {
        this.modalController.setLoadingState('Loading dental records...');
        try {
            const data = await this.dataLoader.loadDentalRecords(patientId);
            if (data.success) {
                this.displayManager.displayDentalRecords(data.records);
            } else {
                this.utilities.showAlert(data.message || 'Failed to load dental records', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.utilities.showAlert('An error occurred while loading dental records', 'error');
        }
    }

    async loadDentalChart(patientId) {
        this.modalController.setLoadingState('Loading dental chart...');
        try {
            const data = await this.dataLoader.loadDentalChart(patientId);
            if (data.success) {
                this.displayManager.displayDentalChart(data);
                // Initialize 3D viewer after content is loaded
                setTimeout(() => {
                    this.dental3DManager.initModal3D(data, (toothNumber, toothData, toothName) => {
                        this.showToothDetails(toothNumber, toothData, toothName);
                    });
                }, 100);
            } else {
                this.utilities.showAlert(data.message || 'Failed to load dental chart', 'error');
            }
        } catch (error) {
            console.error('Error loading dental chart:', error);
            this.modalController.setErrorState('Failed to Load Dental Chart', error.message, 
                () => this.loadDentalChart(patientId));
        }
    }

    async loadAppointments(patientId) {
        this.modalController.setLoadingState('Loading appointments...');
        try {
            console.log('🔄 Records Manager: Loading appointments for patient:', patientId);
            
            const data = await this.dataLoader.loadAppointments(patientId);
            console.log('📋 Records Manager: Received appointment data:', data);
            
            if (data && data.success) {
                // Check if we have any appointments at all
                const hasAppointments = (data.present_appointments && data.present_appointments.length > 0) ||
                                      (data.past_appointments && data.past_appointments.length > 0) ||
                                      (data.appointments && data.appointments.length > 0) ||
                                      (data.total_appointments > 0);
                
                if (hasAppointments) {
                    console.log('✅ Records Manager: Displaying appointments');
                    this.displayManager.displayAppointments(data);
                } else {
                    console.log('ℹ️ Records Manager: No appointments found, showing empty state');
                    this.displayManager.displayAppointments({
                        success: true,
                        present_appointments: [],
                        past_appointments: [],
                        total_appointments: 0,
                        message: 'No appointments found for this patient'
                    });
                }
            } else {
                console.error('❌ Records Manager: Failed to load appointments:', data);
                const errorMessage = data?.message || 'Failed to load appointments';
                
                // Show error state with retry option
                this.modalController.setErrorState('Failed to Load Appointments', errorMessage, 
                    () => this.loadAppointments(patientId));
            }
        } catch (error) {
            console.error('💥 Records Manager: Exception loading appointments:', error);
            
            // Show a user-friendly error with retry option
            this.modalController.setErrorState('Unable to Load Appointments', 
                'There was a problem connecting to the server. Please try again.', 
                () => this.loadAppointments(patientId));
        }
    }

    async loadTreatments(patientId) {
        this.modalController.setLoadingState('Loading treatments...');
        try {
            const data = await this.dataLoader.loadTreatments(patientId);
            if (data.success) {
                this.displayManager.displayTreatments(data);
            } else {
                this.utilities.showAlert(data.message || 'Failed to load treatments', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.utilities.showAlert('An error occurred while loading treatments', 'error');
        }
    }

    async loadMedicalRecords(patientId) {
        this.modalController.setLoadingState('Loading medical records...');
        try {
            const data = await this.dataLoader.loadMedicalRecords(patientId);
            if (data.success) {
                this.displayManager.displayMedicalRecords(data);
            } else {
                this.utilities.showAlert(data.message || 'Failed to load medical records', 'error');
            }
        } catch (error) {
            console.error('Error loading medical records:', error);
            this.utilities.showAlert('An error occurred while loading medical records', 'error');
        }
    }

    async loadInvoiceHistory(patientId) {
        this.modalController.setLoadingState('Loading invoices...');
        try {
            const data = await this.dataLoader.loadInvoiceHistory(patientId);
            if (data.success) {
                this.displayManager.displayInvoiceHistory(data);
            } else {
                this.utilities.showAlert(data.message || 'Failed to load invoices', 'error');
            }
        } catch (error) {
            console.error('Error loading invoices:', error);
            this.utilities.showAlert('An error occurred while loading invoices', 'error');
        }
    }

    async loadPrescriptions(patientId) {
        this.modalController.setLoadingState('Loading prescriptions...');
        try {
            const data = await this.dataLoader.loadPrescriptions(patientId);
            if (data.success) {
                this.displayManager.displayPrescriptions(data);
            } else {
                this.utilities.showAlert(data.message || 'Failed to load prescriptions', 'error');
            }
        } catch (error) {
            console.error('Error loading prescriptions:', error);
            this.utilities.showAlert('An error occurred while loading prescriptions', 'error');
        }
    }

    // ==================== TOOTH DETAILS MODAL ====================

    showToothDetails(toothNumber, toothData, toothName) {
        this.displayManager.showToothDetailsModal(toothNumber, toothData, toothName);
    }

    // ==================== CONDITIONS ANALYSIS ====================

    toggleConditionsDetail() {
        this.conditionsAnalyzer.toggleDetailPanel();
    }

    // ==================== UTILITY METHODS ====================

    // Modal control methods
    toggleFullscreen() {
        this.modalController.toggleFullscreen();
    }

    centerModal() {
        this.modalController.centerModal();
    }

    // Quick actions
    exportPatientData(patientId) {
        this.utilities.exportPatientData(patientId);
    }

    scheduleAppointment(patientId) {
        this.utilities.scheduleAppointment(patientId);
    }

    addTreatment(patientId) {
        this.utilities.addTreatment(patientId);
    }

    updateMedical(patientId) {
        this.utilities.updateMedical(patientId);
    }

    generateReport(patientId) {
        this.utilities.generateReport(patientId);
    }

    // Record management
    async deleteRecord(recordId) {
        const confirmed = await this.utilities.confirmDelete();
        if (confirmed) {
            try {
                await this.dataLoader.deleteRecord(recordId);
                this.utilities.showAlert('Record deleted successfully', 'success');
                // Refresh current tab
                if (this.currentPatientId) {
                    this.loadDentalRecords(this.currentPatientId);
                }
            } catch (error) {
                console.error('Error deleting record:', error);
                this.utilities.showAlert('Failed to delete record', 'error');
            }
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.recordsManager = new RecordsManager();
});

// Global function wrappers for backward compatibility
function deleteRecord(recordId) {
    if (window.recordsManager) {
        window.recordsManager.deleteRecord(recordId);
    }
}

// Export for use
window.RecordsManager = RecordsManager;
