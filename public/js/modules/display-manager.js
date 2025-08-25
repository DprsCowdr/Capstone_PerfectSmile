/**
 * Display Manager - Handles all UI rendering and content display
 * Separates presentation logic from business logic
 */

class DisplayManager {
    constructor() {
        this.utilities = new RecordsUtilities();
    }

    // ==================== PATIENT INFO DISPLAY ====================

    displayPatientInfo(patient) {
        const content = this.generatePatientInfoHTML(patient);
        this.setModalContent(content);
    }

    generatePatientInfoHTML(patient) {
        return `
            <div class="bg-white text-[13px] leading-relaxed">
                ${this.generatePatientHeader(patient)}
                <div class="space-y-8">
                    ${this.generatePersonalInfoSection(patient)}
                    ${this.generateMedicalHistorySection(patient)}
                    ${this.generateDentalHistorySection(patient)}
                    ${this.generateRecentRecordsSummary(patient)}
                    ${this.generateNotesSection(patient)}
                </div>
            </div>`;
    }

    generatePatientHeader(patient) {
        return `
            <div class="mb-6 border border-gray-200 rounded-md p-4 bg-gray-50">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                            <i class="fas fa-user text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-gray-800 tracking-tight">${patient.name}</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Patient ID: ${patient.id}</p>
                            <div class="flex flex-wrap items-center gap-2 mt-2">
                                <span class="px-2 py-0.5 text-[11px] font-medium rounded-full ${patient.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'}">${patient.status || 'Active'}</span>
                                <span class="px-2 py-0.5 text-[11px] font-medium rounded-full bg-blue-100 text-blue-700"><i class="fas fa-calendar mr-1"></i>${patient.created_at ? new Date(patient.created_at).getFullYear() : 'N/A'}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="window.print()" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-medium rounded-md">Print</button>
                        <button onclick="recordsManager.exportPatientData(${patient.id})" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-[11px] font-medium rounded-md border border-blue-200">Export</button>
                    </div>
                </div>
            </div>`;
    }

    generatePersonalInfoSection(patient) {
        return `
            <section class="space-y-3">
                <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2"><i class="fas fa-id-card text-blue-500"></i>Personal</h3>
                <div class="border border-gray-200 rounded-md p-4 bg-white">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        ${this.generateInfoField('Full Name', patient.name)}
                        ${this.generateInfoField('Date of Birth', patient.date_of_birth ? new Date(patient.date_of_birth).toLocaleDateString() : 'N/A')}
                        ${this.generateInfoField('Age', patient.age ? `${patient.age} yrs` : 'N/A')}
                        ${this.generateInfoField('Email', patient.email)}
                        ${this.generateInfoField('Phone', patient.phone)}
                        ${this.generateInfoField('Gender', patient.gender ? patient.gender.charAt(0).toUpperCase() + patient.gender.slice(1) : 'N/A')}
                        ${this.generateInfoField('Occupation', patient.occupation)}
                        ${this.generateInfoField('Emergency Contact', patient.emergency_contact)}
                        ${this.generateInfoField('Insurance', patient.insurance_provider)}
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        ${this.generateInfoField('Address', patient.address, 'block')}
                    </div>
                </div>
            </section>`;
    }

    generateMedicalHistorySection(patient) {
        return `
            <section class="space-y-3">
                <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2"><i class="fas fa-heartbeat text-blue-500"></i>Medical</h3>
                <div class="border border-gray-200 rounded-md p-4 bg-white">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        ${this.generateInfoField('Physician', patient.physician_name || 'N/A')}
                        ${this.generateInfoField('Specialty', patient.physician_specialty || 'N/A')}
                        ${this.generateInfoField('Physician Phone', patient.physician_phone || 'N/A')}
                        ${this.generateInfoField('Blood Pressure', patient.blood_pressure || 'N/A')}
                        ${this.generateInfoField('Allergies', patient.allergies || 'None')}
                        ${this.generateInfoField('Medications', patient.medications || 'None')}
                        ${this.generateInfoField('Tobacco', patient.tobacco_use === 'yes' ? 'Yes' : patient.tobacco_use === 'no' ? 'No' : 'N/A')}
                        ${this.generateInfoField('General Health', patient.good_health === 'yes' ? 'Good' : patient.good_health === 'no' ? 'Issues' : 'N/A')}
                    </div>
                    ${patient.medical_conditions ? `<div class='mt-4 pt-4 border-t border-gray-100'>${this.generateInfoField('Medical Conditions', patient.medical_conditions, 'block')}</div>` : ''}
                </div>
            </section>`;
    }

    generateDentalHistorySection(patient) {
        return `
            <section class="space-y-3">
                <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2"><i class="fas fa-tooth text-blue-500"></i>Dental</h3>
                <div class="border border-gray-200 rounded-md p-4 bg-white">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        ${this.generateInfoField('Previous Dentist', patient.previous_dentist || 'N/A')}
                        ${this.generateInfoField('Last Visit', patient.last_dental_visit ? new Date(patient.last_dental_visit).toLocaleDateString() : 'N/A')}
                        ${this.generateInfoField('Concerns', patient.dental_concerns || 'None')}
                        ${this.generateInfoField('Brushing', patient.brushing_frequency || 'N/A')}
                        ${this.generateInfoField('Flossing', patient.flossing_frequency || 'N/A')}
                        ${this.generateInfoField('Pain/Sensitivity', patient.dental_pain === 'yes' ? 'Yes' : patient.dental_pain === 'no' ? 'No' : 'N/A')}
                    </div>
                </div>
            </section>`;
    }

    generateRecentRecordsSummary(patient) {
        return `
            <section class="space-y-3">
                <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2"><i class="fas fa-clipboard-list text-blue-500"></i>Visits</h3>
                <div class="border border-gray-200 rounded-md p-4 bg-white">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <div class="text-base font-semibold text-gray-800">${patient.total_visits || '0'}</div>
                            <div class="text-[11px] text-gray-500 mt-0.5">Total</div>
                        </div>
                        <div>
                            <div class="text-base font-semibold text-gray-800">${patient.last_visit_date ? new Date(patient.last_visit_date).toLocaleDateString() : 'N/A'}</div>
                            <div class="text-[11px] text-gray-500 mt-0.5">Last</div>
                        </div>
                        <div>
                            <div class="text-base font-semibold text-gray-800">${patient.next_appointment_date ? new Date(patient.next_appointment_date).toLocaleDateString() : 'N/A'}</div>
                            <div class="text-[11px] text-gray-500 mt-0.5">Next</div>
                        </div>
                    </div>
                    ${patient.last_diagnosis ? `<div class='mt-4 pt-4 border-t border-gray-100'>${this.generateInfoField('Last Diagnosis', patient.last_diagnosis, 'block')}</div>` : ''}
                </div>
            </section>`;
    }

    generateNotesSection(patient) {
        return `
            <section class="space-y-3">
                <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2"><i class="fas fa-note-sticky text-blue-500"></i>Notes</h3>
                <div class="border border-gray-200 rounded-md p-4 bg-white" data-patient-notes>
                    <p class="text-gray-700">${patient.special_notes || 'No special notes recorded.'}</p>
                </div>
            </section>`;
    }

    generateInfoField(label, value, type = 'inline') {
        const val = value || 'N/A';
        if (type === 'block') {
            return `<div><p class="text-[11px] uppercase tracking-wide text-gray-500 mb-1">${label}</p><p class="text-[13px] text-gray-800">${val}</p></div>`;
        }
        return `<div><p class="text-[11px] uppercase tracking-wide text-gray-500 mb-1">${label}</p><p class="text-[13px] font-medium text-gray-800">${val}</p></div>`;
    }

    // ==================== DENTAL RECORDS DISPLAY ====================

    displayDentalRecords(records) {
        let content = '<div class="bg-white p-6"><h3 class="text-lg font-bold mb-4">Dental Records</h3>';
        
        if (records.length === 0) {
            content += '<p class="text-gray-500 text-center py-8">No dental records found</p>';
        } else {
            content += '<div class="space-y-4">';
            records.forEach(record => {
                content += this.generateDentalRecordCard(record);
            });
            content += '</div>';
        }
        
        content += '</div>';
        this.setModalContent(content);
    }

    generateDentalRecordCard(record) {
        return `
            <div class="border rounded-lg p-4">
                <div class="flex justify-between items-start mb-2">
                    <h4 class="font-semibold">${new Date(record.record_date).toLocaleDateString()}</h4>
                    <span class="text-sm text-gray-600">Dr. ${record.dentist_name}</span>
                </div>
                ${record.chief_complaint ? `<p class="text-sm mb-2"><strong>Chief Complaint:</strong> ${record.chief_complaint}</p>` : ''}
                ${record.treatment ? `<p class="text-sm mb-2"><strong>Treatment:</strong> ${record.treatment}</p>` : ''}
                ${record.notes ? `<p class="text-sm text-gray-600">${record.notes}</p>` : ''}
            </div>
        `;
    }

    // ==================== DENTAL CHART DISPLAY ====================

    displayDentalChart(chartResponse) {
        const content = this.generateDentalChartHTML(chartResponse);
        this.setModalContent(content);
    }

    generateDentalChartHTML(chartResponse) {
        return `
        <div class="bg-white p-6">
            <h3 class="text-lg font-bold mb-4">
                <i class="fas fa-chart-line text-blue-500 mr-2"></i>
                Dental Chart
                <span class="text-sm font-normal text-gray-600 ml-2">(Interactive View)</span>
            </h3>
            
            <!-- Enhanced 3D Dental Model Viewer -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h4 class="font-semibold text-gray-800 mb-4 text-center">
                    <i class="fas fa-cube text-blue-500 mr-2"></i>
                    Interactive 3D Dental Model
                </h4>
                
                <!-- Instructions -->
                <div class="bg-blue-50 rounded-lg p-3 mb-4 text-sm text-blue-800">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle mt-0.5 mr-2"></i>
                        <div>
                            <strong>Instructions:</strong> Click and drag to rotate • Scroll to zoom • Hover over teeth to see checkup details • Click teeth for complete history
                        </div>
                    </div>
                </div>
                
                <div class="dental-3d-viewer relative" id="dentalModalViewer" style="height: 500px;">
                    <div class="model-loading text-center py-8" id="modalModelLoading">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2 text-blue-500"></i>
                        <p class="text-gray-600">Loading 3D Model...</p>
                    </div>
                    <div class="model-error hidden text-center py-8" id="modalModelError">
                        <i class="fas fa-exclamation-triangle text-red-500 text-2xl mb-2"></i>
                        <p class="text-red-600 mb-2">Failed to load 3D model</p>
                        <button onclick="recordsManager.dental3DManager.initModal3D()" class="px-3 py-1 bg-blue-500 text-white rounded text-sm hover:bg-blue-600">
                            Retry
                        </button>
                    </div>
                    <canvas class="dental-3d-canvas"></canvas>
                    
                    <!-- Enhanced Model Controls -->
                    <div class="model-controls" style="position: absolute; top: 10px; right: 10px; display: flex; flex-direction: column; gap: 8px;">
                        <button class="model-control-btn" onclick="recordsManager.dental3DManager.resetCamera()" title="Reset View" style="padding: 8px; background: rgba(0,0,0,0.7); color: white; border: none; border-radius: 4px; cursor: pointer;">
                            <i class="fas fa-home"></i>
                        </button>
                        <button class="model-control-btn" onclick="recordsManager.dental3DManager.toggleWireframe()" title="Toggle Wireframe" style="padding: 8px; background: rgba(0,0,0,0.7); color: white; border: none; border-radius: 4px; cursor: pointer;">
                            <i class="fas fa-border-all"></i>
                        </button>
                        <button class="model-control-btn" onclick="recordsManager.dental3DManager.toggleAutoRotate()" title="Auto Rotate" style="padding: 8px; background: rgba(0,0,0,0.7); color: white; border: none; border-radius: 4px; cursor: pointer;">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                
                ${this.generateColorLegend()}
                ${this.generateConditionsSummaryPanel()}
            </div>
            
            ${this.generateChartSummary(chartResponse)}
        </div>
        `;
    }

    generateColorLegend() {
        return `
            <!-- Enhanced 3D Model Color Legend -->
            <div class="mt-6 p-4 bg-white rounded-lg border">
                <h5 class="text-sm font-semibold text-gray-700 mb-3 text-center">
                    <i class="fas fa-palette text-blue-500 mr-2"></i>
                    Tooth Condition Color Legend
                </h5>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-green-400 rounded mr-2 border"></div>
                        <span><i class="fas fa-check-circle text-green-500 mr-1"></i>Healthy</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-red-500 rounded mr-2 border"></div>
                        <span><i class="fas fa-exclamation-triangle text-red-500 mr-1"></i>Cavity</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-yellow-500 rounded mr-2 border"></div>
                        <span><i class="fas fa-circle text-yellow-500 mr-1"></i>Filled</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-purple-500 rounded mr-2 border"></div>
                        <span><i class="fas fa-crown text-purple-500 mr-1"></i>Crown</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-gray-800 rounded mr-2 border"></div>
                        <span><i class="fas fa-times-circle text-gray-600 mr-1"></i>Missing</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-blue-500 rounded mr-2 border"></div>
                        <span><i class="fas fa-stethoscope text-blue-500 mr-1"></i>Root Canal</span>
                    </div>
                </div>
                
                <!-- Enhanced interaction hints -->
                <div class="mt-4 pt-3 border-t border-gray-200">
                    <div class="text-xs text-gray-600 space-y-1">
                        <div><i class="fas fa-mouse-pointer text-blue-500 mr-2"></i><strong>Hover:</strong> View detailed condition information</div>
                        <div><i class="fas fa-hand-pointer text-green-500 mr-2"></i><strong>Click:</strong> See complete tooth history</div>
                        <div><i class="fas fa-eye-slash text-gray-500 mr-2"></i><strong>Missing teeth:</strong> Hidden from view (not colored)</div>
                    </div>
                </div>
            </div>
        `;
    }

    generateConditionsSummaryPanel() {
        return `
            <!-- Detailed Conditions Summary Panel -->
            <div class="mt-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200" id="conditionsSummaryPanel">
                <h5 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                    <i class="fas fa-chart-bar text-blue-500 mr-2"></i>
                    Dental Conditions Summary
                    <button onclick="recordsManager.toggleConditionsDetail()" class="ml-auto text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600" id="conditionsToggleBtn">
                        Show Details
                    </button>
                </h5>
                
                <div id="conditionsSummaryContent" class="text-sm">
                    <div class="text-center text-gray-500 py-2">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Loading conditions summary...
                    </div>
                </div>
                
                <div id="conditionsDetailContent" class="hidden mt-4 pt-4 border-t border-blue-200">
                    <!-- Detailed conditions will be populated here -->
                </div>
            </div>
        `;
    }

    generateChartSummary(chartResponse) {
        if (!chartResponse.chart || chartResponse.chart.length === 0) return '';
        
        return `
            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                <h5 class="text-sm font-semibold text-gray-700 mb-2">Chart Summary</h5>
                <div class="grid grid-cols-3 gap-4 text-center text-sm">
                    <div>
                        <div class="text-lg font-bold text-blue-600">${chartResponse.chart.length}</div>
                        <div class="text-gray-600">Total Records</div>
                    </div>
                    <div>
                        <div class="text-lg font-bold text-green-600">${Object.keys(chartResponse.teeth_data || {}).length}</div>
                        <div class="text-gray-600">Teeth Recorded</div>
                    </div>
                    <div>
                        <div class="text-lg font-bold text-orange-600">${chartResponse.chart.filter(r => r.created_at && new Date(r.created_at) > new Date(Date.now() - 30*24*60*60*1000)).length}</div>
                        <div class="text-gray-600">Recent (30 days)</div>
                    </div>
                </div>
            </div>
        `;
    }

    // ==================== OTHER DISPLAY METHODS ====================

    displayAppointments(appointmentData) {
        const content = this.generateAppointmentsHTML(appointmentData);
        this.setModalContent(content);
    }

    displayTreatments(treatmentData) {
        const content = this.generateTreatmentsHTML(treatmentData);
        this.setModalContent(content);
    }

    displayMedicalRecords(medicalData) {
        const content = this.generateMedicalRecordsHTML(medicalData);
        this.setModalContent(content);
    }

    // ==================== HELPER METHODS ====================

    setModalContent(content) {
        const modalContent = document.getElementById('modalContent');
        if (modalContent) {
            modalContent.innerHTML = content;
        }
    }

    formatDate(dateString) {
        if (!dateString) return 'N/A';
        try {
            return new Date(dateString).toLocaleDateString();
        } catch (error) {
            console.error('Error formatting date:', error);
            return 'Invalid Date';
        }
    }

    // Placeholder methods for appointments, treatments, and medical records
    // These can be implemented based on your specific requirements
    generateAppointmentsHTML(appointmentData) {
        console.log('📋 Generating appointments HTML with data:', appointmentData);
        
        // Handle both old and new data formats
        let presentAppointments = [];
        let pastAppointments = [];
        let totalAppointments = 0;
        
        if (appointmentData && appointmentData.success) {
            // Check for categorized format (new)
            if (appointmentData.present_appointments !== undefined || appointmentData.past_appointments !== undefined) {
                presentAppointments = appointmentData.present_appointments || [];
                pastAppointments = appointmentData.past_appointments || [];
                totalAppointments = appointmentData.total_appointments || (presentAppointments.length + pastAppointments.length);
            }
            // Check for flat appointments array (old format)
            else if (appointmentData.appointments && Array.isArray(appointmentData.appointments)) {
                const appointments = appointmentData.appointments;
                const currentDateTime = new Date().toISOString();
                
                presentAppointments = appointments.filter(apt => {
                    const aptDateTime = apt.appointment_datetime || `${apt.appointment_date} ${apt.appointment_time}`;
                    return aptDateTime >= currentDateTime;
                });
                
                pastAppointments = appointments.filter(apt => {
                    const aptDateTime = apt.appointment_datetime || `${apt.appointment_date} ${apt.appointment_time}`;
                    return aptDateTime < currentDateTime;
                });
                
                totalAppointments = appointments.length;
            }
        }
        
        // If no appointments found
        if (totalAppointments === 0) {
            return `
                <div class="bg-white p-6">
                    <h3 class="text-lg font-bold mb-4">
                        <i class="fas fa-calendar text-blue-600 mr-2"></i>Appointments
                    </h3>
                    <div class="text-center py-8">
                        <i class="far fa-calendar-alt text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 text-sm">No appointments found for this patient.</p>
                        <p class="text-gray-400 text-xs mt-2">Appointments will appear here once scheduled.</p>
                    </div>
                </div>
            `;
        }

        // Helper function for status styling (similar to patientsTable.js)
        const getStatusClass = (status) => {
            switch(status?.toLowerCase()) {
                case 'completed':
                    return 'bg-green-100 text-green-800';
                case 'confirmed':
                case 'scheduled':
                    return 'bg-blue-100 text-blue-800';
                case 'pending':
                case 'pending_approval':
                    return 'bg-yellow-100 text-yellow-800';
                case 'ongoing':
                    return 'bg-purple-100 text-purple-800';
                case 'cancelled':
                    return 'bg-red-100 text-red-800';
                case 'no_show':
                    return 'bg-orange-100 text-orange-800';
                default:
                    return 'bg-gray-100 text-gray-800';
            }
        };

        // Helper function to format appointment card (enhanced version of patientsTable.js approach)
        const formatAppointmentCard = (appointment, isUpcoming = true) => {
            const date = appointment.appointment_datetime 
                ? new Date(appointment.appointment_datetime).toLocaleDateString('en-US', { 
                    weekday: 'short', 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                })
                : (appointment.appointment_date ? new Date(appointment.appointment_date).toLocaleDateString() : 'Date not specified');
                
            const time = appointment.appointment_datetime 
                ? new Date(appointment.appointment_datetime).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})
                : (appointment.appointment_time || 'Time not specified');
                
            const statusClass = getStatusClass(appointment.status);
            const cardBorderClass = isUpcoming ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-gray-50';
            
            return `
                <div class="border ${cardBorderClass} rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-calendar text-blue-600"></i>
                            <h4 class="font-semibold text-gray-800">Appointment #${appointment.id}</h4>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full font-medium ${statusClass}">
                            ${appointment.status || 'Scheduled'}
                        </span>
                    </div>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2 text-gray-700">
                            <i class="fas fa-clock text-gray-400 w-4"></i>
                            <span><strong>Date:</strong> ${date} at ${time}</span>
                        </div>
                        
                        ${appointment.appointment_type ? `
                            <div class="flex items-center gap-2 text-gray-700">
                                <i class="fas fa-tag text-gray-400 w-4"></i>
                                <span><strong>Type:</strong> ${appointment.appointment_type.charAt(0).toUpperCase() + appointment.appointment_type.slice(1)}</span>
                            </div>
                        ` : ''}
                        
                        ${appointment.branch_name ? `
                            <div class="flex items-center gap-2 text-gray-700">
                                <i class="fas fa-building text-gray-400 w-4"></i>
                                <span><strong>Branch:</strong> ${appointment.branch_name}</span>
                            </div>
                        ` : ''}
                        
                        ${appointment.dentist_name ? `
                            <div class="flex items-center gap-2 text-gray-700">
                                <i class="fas fa-user-md text-gray-400 w-4"></i>
                                <span><strong>Dentist:</strong> Dr. ${appointment.dentist_name}</span>
                            </div>
                        ` : ''}
                        
                        ${appointment.remarks ? `
                            <div class="flex items-start gap-2 text-gray-700 mt-3 pt-2 border-t border-gray-200">
                                <i class="fas fa-comment text-gray-400 w-4 mt-0.5"></i>
                                <span><strong>Notes:</strong> ${appointment.remarks}</span>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        };

        return `
            <div class="bg-white p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-800">
                        <i class="fas fa-calendar text-blue-600 mr-2"></i>Appointments
                    </h3>
                    <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                        ${totalAppointments} total
                    </span>
                </div>
                
                ${presentAppointments.length > 0 ? `
                    <div class="mb-6">
                        <h4 class="text-md font-semibold text-green-700 mb-4 flex items-center gap-2">
                            <i class="fas fa-clock text-green-600"></i>
                            Upcoming Appointments
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full">
                                ${presentAppointments.length}
                            </span>
                        </h4>
                        <div class="space-y-3">
                            ${presentAppointments.map(appointment => formatAppointmentCard(appointment, true)).join('')}
                        </div>
                    </div>
                ` : ''}

                ${pastAppointments.length > 0 ? `
                    <div class="mb-4">
                        <h4 class="text-md font-semibold text-gray-700 mb-4 flex items-center gap-2">
                            <i class="fas fa-history text-gray-600"></i>
                            Past Appointments
                            <span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-full">
                                ${pastAppointments.length}
                            </span>
                        </h4>
                        <div class="space-y-3 max-h-80 overflow-y-auto">
                            ${pastAppointments.map(appointment => formatAppointmentCard(appointment, false)).join('')}
                        </div>
                    </div>
                ` : ''}
                
                ${presentAppointments.length === 0 && pastAppointments.length === 0 ? `
                    <div class="text-center py-8">
                        <i class="far fa-calendar-alt text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 text-sm">No appointments found for this patient.</p>
                        <p class="text-gray-400 text-xs mt-2">Appointments will appear here once scheduled.</p>
                    </div>
                ` : ''}
            </div>
        `;
    }

    generateTreatmentsHTML(treatmentData) {
        console.log('🦷 Generating treatments HTML with data:', treatmentData);
        
        if (!treatmentData || !treatmentData.treatments || treatmentData.treatments.length === 0) {
            return `
                <div class="bg-white p-6">
                    <h3 class="text-lg font-bold mb-4">
                        <i class="fas fa-procedures text-purple-600 mr-2"></i>Treatments
                    </h3>
                    <p class="text-gray-500">No treatments found for this patient.</p>
                </div>
            `;
        }

        const treatments = treatmentData.treatments;
        const totalTreatments = treatmentData.total_treatments || treatments.length;

        return `
            <div class="bg-white p-6">
                <h3 class="text-lg font-bold mb-4">
                    <i class="fas fa-procedures text-purple-600 mr-2"></i>Treatments
                    <span class="text-sm font-normal text-gray-500">(${totalTreatments} total)</span>
                </h3>
                
                <div class="space-y-4">
                    ${treatments.map(treatment => `
                        <div class="border border-purple-200 rounded-lg p-4 bg-purple-50">
                            <div class="flex justify-between items-start mb-2">
                                <div class="font-medium text-gray-900">${treatment.treatment_name || 'Treatment'}</div>
                                <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded-full">
                                    ${treatment.status || 'Completed'}
                                </span>
                            </div>
                            <div class="text-sm text-gray-600 mb-2">
                                <i class="fas fa-calendar mr-1"></i>Date: ${this.utilities.formatDate(treatment.treatment_date)}
                                ${treatment.tooth_number ? `<i class="fas fa-tooth ml-3 mr-1"></i>Tooth #${treatment.tooth_number}` : ''}
                            </div>
                            ${treatment.description ? `
                                <div class="text-sm text-gray-700 mb-2">
                                    <i class="fas fa-file-text mr-1"></i>Description: ${treatment.description}
                                </div>
                            ` : ''}
                            <div class="text-sm text-gray-600">
                                <i class="fas fa-user-md mr-1"></i>Dr. ${treatment.dentist_name || 'Not specified'}
                                ${treatment.cost ? `<i class="fas fa-dollar-sign ml-3 mr-1"></i>$${treatment.cost}` : ''}
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    generateMedicalRecordsHTML(medicalData) {
        console.log('🏥 Generating medical records HTML with data:', medicalData);
        
        if (!medicalData || !medicalData.medical_records) {
            return `
                <div class="bg-white p-6">
                    <h3 class="text-lg font-bold mb-4">
                        <i class="fas fa-file-medical text-red-600 mr-2"></i>Medical Records
                    </h3>
                    <p class="text-gray-500">No medical records found for this patient.</p>
                </div>
            `;
        }

        const medicalRecords = medicalData.medical_records || [];
        const patientInfo = medicalData.patient_info || {};
        const diagnoses = medicalData.diagnoses || [];
        const xrays = medicalData.xrays || [];

        return `
            <div class="bg-white p-6">
                <h3 class="text-lg font-bold mb-4">
                    <i class="fas fa-file-medical text-red-600 mr-2"></i>Medical Records
                </h3>
                
                ${patientInfo && Object.keys(patientInfo).length > 0 ? `
                    <div class="mb-6 bg-blue-50 rounded-lg p-4">
                        <h4 class="text-md font-semibold text-blue-700 mb-3">
                            <i class="fas fa-user text-blue-600 mr-2"></i>Patient Information
                        </h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            ${patientInfo.allergies ? `<div><strong>Allergies:</strong> ${patientInfo.allergies}</div>` : ''}
                            ${patientInfo.medical_conditions ? `<div><strong>Medical Conditions:</strong> ${patientInfo.medical_conditions}</div>` : ''}
                            ${patientInfo.medications ? `<div><strong>Medications:</strong> ${patientInfo.medications}</div>` : ''}
                            ${patientInfo.emergency_contact ? `<div><strong>Emergency Contact:</strong> ${patientInfo.emergency_contact}</div>` : ''}
                        </div>
                    </div>
                ` : ''}

                ${diagnoses.length > 0 ? `
                    <div class="mb-6">
                        <h4 class="text-md font-semibold text-orange-700 mb-3">
                            <i class="fas fa-stethoscope text-orange-600 mr-2"></i>Diagnoses
                        </h4>
                        <div class="space-y-2">
                            ${diagnoses.map(diagnosis => `
                                <div class="border border-orange-200 rounded-lg p-3 bg-orange-50">
                                    <div class="font-medium text-gray-900">${diagnosis.diagnosis_name || 'Diagnosis'}</div>
                                    <div class="text-sm text-gray-600">
                                        <i class="fas fa-calendar mr-1"></i>Date: ${this.utilities.formatDate(diagnosis.diagnosis_date)}
                                    </div>
                                    ${diagnosis.description ? `
                                        <div class="text-sm text-gray-700 mt-1">${diagnosis.description}</div>
                                    ` : ''}
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}

                ${xrays.length > 0 ? `
                    <div class="mb-6">
                        <h4 class="text-md font-semibold text-purple-700 mb-3">
                            <i class="fas fa-x-ray text-purple-600 mr-2"></i>X-rays
                        </h4>
                        <div class="grid grid-cols-2 gap-4">
                            ${xrays.map(xray => `
                                <div class="border border-purple-200 rounded-lg p-3 bg-purple-50">
                                    <div class="font-medium text-gray-900">${xray.xray_type || 'X-ray'}</div>
                                    <div class="text-sm text-gray-600">
                                        <i class="fas fa-calendar mr-1"></i>${this.utilities.formatDate(xray.xray_date)}
                                    </div>
                                    ${xray.notes ? `
                                        <div class="text-sm text-gray-700 mt-1">${xray.notes}</div>
                                    ` : ''}
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}

                ${medicalRecords.length > 0 ? `
                    <div class="mb-4">
                        <h4 class="text-md font-semibold text-green-700 mb-3">
                            <i class="fas fa-notes-medical text-green-600 mr-2"></i>Medical Records
                        </h4>
                        <div class="space-y-3">
                            ${medicalRecords.map(record => `
                                <div class="border border-green-200 rounded-lg p-4 bg-green-50">
                                    <div class="font-medium text-gray-900">${record.record_type || 'Medical Record'}</div>
                                    <div class="text-sm text-gray-600 mb-2">
                                        <i class="fas fa-calendar mr-1"></i>Date: ${this.utilities.formatDate(record.record_date)}
                                        <i class="fas fa-user-md ml-3 mr-1"></i>Dr. ${record.doctor_name || 'Not specified'}
                                    </div>
                                    ${record.notes ? `
                                        <div class="text-sm text-gray-700">${record.notes}</div>
                                    ` : ''}
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}
            </div>
        `;
    }

    // ==================== TOOTH DETAILS MODAL ====================

    showToothDetailsModal(toothNumber, toothData, toothName) {
        try {
            const displayName = toothName || `Tooth ${toothNumber}`;
            const detailsHtml = this.generateToothDetailsHTML(toothNumber, toothData, displayName);
            
            // Add to body
            document.body.insertAdjacentHTML('beforeend', detailsHtml);
        } catch (error) {
            console.error('Error showing tooth details:', error);
            this.showErrorModal('Error Loading Details', 'Unable to load tooth details. Please try again.');
        }
    }

    generateToothDetailsHTML(toothNumber, toothData, displayName) {
        if (!Array.isArray(toothData) || toothData.length === 0) {
            return this.generateEmptyToothDetailsHTML(toothNumber, displayName);
        }

        return `
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                    ${this.generateToothDetailsHeader(toothNumber, displayName)}
                    ${this.generateToothDetailsContent(toothNumber, toothData, displayName)}
                    ${this.generateToothDetailsFooter()}
                </div>
            </div>
        `;
    }

    generateEmptyToothDetailsHTML(toothNumber, displayName) {
        return `
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                    ${this.generateToothDetailsHeader(toothNumber, displayName)}
                    <div class="p-6">
                        <div class="text-center py-8">
                            <i class="fas fa-search text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-500 text-lg">No records found for this tooth.</p>
                            <p class="text-gray-400 text-sm mt-2">This tooth has not been examined yet.</p>
                        </div>
                    </div>
                    ${this.generateToothDetailsFooter()}
                </div>
            </div>
        `;
    }

    generateToothDetailsHeader(toothNumber, displayName) {
        return `
            <div class="sticky top-0 bg-white border-b p-4 flex justify-between items-center">
                <h3 class="text-xl font-bold flex items-center">
                    <i class="fas fa-tooth text-blue-500 mr-3"></i>
                    <div class="flex flex-col">
                        <span>${displayName}</span>
                        <span class="text-sm text-gray-600 font-normal">Tooth #${toothNumber} - Complete History</span>
                    </div>
                </h3>
                <button onclick="this.closest('.fixed').remove()" class="text-gray-500 hover:text-gray-700 text-xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
    }

    generateToothDetailsContent(toothNumber, toothData, displayName) {
        // Implementation would go here - similar to the original but broken down
        return `<div class="p-6"><!-- Tooth details content --></div>`;
    }

    generateToothDetailsFooter() {
        return `
            <div class="sticky bottom-0 bg-gray-50 border-t p-4 flex justify-end">
                <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors">
                    <i class="fas fa-times mr-2"></i>Close
                </button>
            </div>
        `;
    }

    showErrorModal(title, message) {
        const errorModal = `
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                    <div class="text-center">
                        <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-4"></i>
                        <h3 class="text-lg font-bold text-red-800 mb-2">${title}</h3>
                        <p class="text-gray-600 mb-4">${message}</p>
                        <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', errorModal);
    }
}

// Export for use
window.DisplayManager = DisplayManager;
