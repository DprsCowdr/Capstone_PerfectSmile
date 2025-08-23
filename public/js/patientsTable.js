// Extracted JS for patients table panels and forms
document.addEventListener('DOMContentLoaded', function () {
    console.log('Patient panel JS loaded');

    // Initialize flatpickr on any elements that expect it (guarded to avoid errors if not present)
    if (typeof flatpickr === 'function') {
        try {
            flatpickr("#date_of_birth", { dateFormat: "Y-m-d", allowInput: false, maxDate: new Date() });
        } catch (e) {
            // ignore
        }
        try {
            flatpickr("#update-patient-date-of-birth", { dateFormat: "Y-m-d", allowInput: false, maxDate: new Date() });
        } catch (e) {
            // ignore
        }
    }

    // Add Patient
    var addBtn = document.getElementById('showAddPatientFormBtn');
    var addPanel = document.getElementById('addPatientPanel');
    var addCloseBtn = document.getElementById('closeAddPatientPanel');
    if (addBtn && addPanel && addCloseBtn) {
        addBtn.addEventListener('click', function() {
            console.log('Add Patient button clicked');
            addPanel.classList.add('active');
            document.body.classList.add('panel-open');
        });
        addCloseBtn.addEventListener('click', function() {
            addPanel.classList.remove('active');
            document.body.classList.remove('panel-open');
        });
    }

    // View Patient (explicit buttons)
    document.querySelectorAll('.showViewPatientPanelBtn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var viewPanel = document.getElementById('viewPatientPanel');
            if (viewPanel) {
                console.log('View Patient button clicked');
                
                // Get patient data from the button's data attribute
                const patientData = this.getAttribute('data-patient');
                if (patientData) {
                    try {
                        const patient = JSON.parse(patientData);
                        populatePatientViewPanel(patient);
                    } catch (error) {
                        console.error('Error parsing patient data:', error);
                    }
                }
                
                viewPanel.classList.add('active');
                document.body.classList.add('panel-open');
            }
        });
    });

    // View Patient (row click)
    document.querySelectorAll('tr.patient-row').forEach(function(row) {
        row.addEventListener('click', function() {
            const viewPanel = document.getElementById('viewPatientPanel');
            if (!viewPanel) return;
            const patientData = this.getAttribute('data-patient');
            if (!patientData) return;
            try {
                const patient = JSON.parse(patientData);
                populatePatientViewPanel(patient);
                viewPanel.classList.add('active');
                document.body.classList.add('panel-open');
            } catch (err) {
                console.error('Error parsing row patient data:', err);
            }
        });
    });
    var viewCloseBtn = document.getElementById('closeViewPatientPanel');
    if (viewCloseBtn) {
        viewCloseBtn.addEventListener('click', function() {
            var viewPanel = document.getElementById('viewPatientPanel');
            if (viewPanel) {
                viewPanel.classList.remove('active');
                viewPanel.classList.remove('shifted');
                document.body.classList.remove('panel-open');
            }
            if (newActionPanel) {
                newActionPanel.classList.remove('active');
            }
        });
    }

    // New Action Panel (opens beside the view panel)
    var newActionBtn = document.getElementById('showNewActionPanelBtn');
    var newActionPanel = document.getElementById('newActionPanel');
    var newActionCloseBtn = document.getElementById('closeNewActionPanel');
    var viewPanel = document.getElementById('viewPatientPanel');
    if (newActionBtn && newActionPanel && newActionCloseBtn) {
        newActionBtn.addEventListener('click', function() {
            console.log('New Action button clicked');
            const patientId = getCurrentPatientId();
            if (patientId) {
                // Open in editable mode (add or update)
                loadPatientMedicalHistory(patientId, { readOnly: false });
            } else {
                // Fallback: just open editable if no id yet
                setMedicalHistoryReadOnly(false);
                newActionPanel.classList.add('active');
                if (viewPanel) viewPanel.classList.add('shifted');
                document.body.classList.add('panel-open');
            }
        });
        newActionCloseBtn.addEventListener('click', function() {
            newActionPanel.classList.remove('active');
            if (viewPanel) viewPanel.classList.remove('shifted');
            document.body.classList.remove('panel-open');
        });
    }

    // Patient Records Button (View Medical History)
    var patientRecordsBtn = document.getElementById('showPatientRecordsBtn');
    if (patientRecordsBtn) {
        patientRecordsBtn.addEventListener('click', function() {
            console.log('Patient Records button clicked');
            // Get current patient ID
            const patientId = getCurrentPatientId();
            if (patientId) {
                // Load and display medical history data in READ-ONLY mode
                loadPatientMedicalHistory(patientId, { readOnly: true });
            } else {
                showNotification('Patient ID not found. Please select a patient first.', 'error');
            }
        });
    }

    // Dental Chart Button (View latest chart)
    var dentalChartBtn = document.getElementById('showDentalChartBtn');
    var dentalChartPanel = document.getElementById('dentalChartPanel');
    var dentalChartClose = document.getElementById('closeDentalChartPanel');
    if (dentalChartBtn && dentalChartPanel) {
        dentalChartBtn.addEventListener('click', function() {
            const patientId = getCurrentPatientId();
            if (!patientId) {
                showNotification('Patient ID not found. Please select a patient first.', 'error');
                return;
            }
            openDentalChart(patientId);
        });
    }
    if (dentalChartClose) {
        dentalChartClose.addEventListener('click', function() {
            dentalChartPanel.classList.remove('active');
            document.body.classList.remove('panel-open');
            // Cleanup 3D viewer to stop loading/animations
            try {
                if (window._chart3DViewer) {
                    window._chart3DViewer.destroy();
                    window._chart3DViewer = null;
                }
            } catch (e) { /* ignore */ }
        });
    }

    // Update Patient (open panels)
    document.querySelectorAll('.showUpdatePatientPanelBtnTable, .showUpdatePatientPanelBtn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var updatePanel = document.getElementById('updatePatientPanel');
            if (updatePanel) {
                console.log('Update Patient button clicked');
                
                // Get patient ID from the button's data attribute
                const patientId = this.getAttribute('data-patient-id');
                if (patientId) {
                    // Load patient data for update form
                    loadPatientForUpdate(patientId);
                }
                
                updatePanel.classList.add('active');
                document.body.classList.add('panel-open');
            }
        });
    });
    
    // Specific event listener for pencil icon in patient view panel
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('showUpdatePatientPanelBtn') || e.target.closest('.showUpdatePatientPanelBtn')) {
            e.preventDefault();
            console.log('Pencil icon clicked!');
            
            const btn = e.target.classList.contains('showUpdatePatientPanelBtn') ? e.target : e.target.closest('.showUpdatePatientPanelBtn');
            var updatePanel = document.getElementById('updatePatientPanel');
            
            console.log('Update panel found:', updatePanel);
            
            if (updatePanel) {
                console.log('Pencil icon clicked');
                
                // Get patient ID from the button's data attribute
                const patientId = btn.getAttribute('data-patient-id');
                console.log('Patient ID:', patientId);
                
                if (patientId) {
                    // Load patient data for update form
                    loadPatientForUpdate(patientId);
                }
                
                updatePanel.classList.add('active');
                document.body.classList.add('panel-open');
                console.log('Panel should be open now');
            } else {
                console.error('Update panel not found!');
            }
        }
    });
    
    var updateCloseBtn = document.getElementById('closeUpdatePatientPanel');
    if (updateCloseBtn) {
        updateCloseBtn.addEventListener('click', function() {
            var updatePanel = document.getElementById('updatePatientPanel');
            if (updatePanel) {
                updatePanel.classList.remove('active');
                document.body.classList.remove('panel-open');
            }
        });
    }

    // Close panels when clicking outside (mobile)
    document.addEventListener('click', function(e) {
        if (e.target.classList && e.target.classList.contains('slide-in-panel')) {
            e.target.classList.remove('active');
            document.body.classList.remove('panel-open');
        }
    });

    // Handle swipe to close on mobile
    let startX = 0;
    let currentX = 0;
    document.querySelectorAll('.slide-in-panel').forEach(function(panel) {
        panel.addEventListener('touchstart', function(e) {
            startX = e.touches[0].clientX;
        });
        panel.addEventListener('touchmove', function(e) {
            currentX = e.touches[0].clientX;
            const diffX = startX - currentX;
            
            // For right panels, swipe left to close
            if (diffX > 50) {
                panel.style.transform = `translateX(${diffX}px)`;
            }
        });
        panel.addEventListener('touchend', function(e) {
            const diffX = startX - currentX;
            
            // For right panels, swipe left to close
            if (diffX > 100) {
                panel.classList.remove('active');
                document.body.classList.remove('panel-open');
            }
            panel.style.transform = '';
        });
    });

    // Prevent zoom on double tap
    let lastTouchEnd = 0;
    document.addEventListener('touchend', function (event) {
        const now = (new Date()).getTime();
        if (now - lastTouchEnd <= 300) {
            event.preventDefault();
        }
        lastTouchEnd = now;
    }, false);

    // Update Patient form submission logging (if present)
    var updateForm = document.getElementById('updatePatientForm');
    if (updateForm) {
        updateForm.addEventListener('submit', function(e) {
            console.log('Form submitted!');
            var formData = new FormData(this);
            for (var pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
        });
    }
});

// Medical History Form Functions
function updateHiddenField(fieldName, value) {
    const hiddenField = document.querySelector(`input[type="hidden"][name="${fieldName}"]`);
    if (hiddenField) {
        hiddenField.value = value;
    }
}

// Show popup info for a tooth in dental chart view
function showChartToothPopup(toothNumber, clickPoint, event, meta) {
    const viewerContainer = document.getElementById('dentalChart3DViewer');
    if (!viewerContainer) return;
    let popup = document.getElementById('chartTreatmentPopup');
    if (!popup) {
        popup = document.createElement('div');
        popup.id = 'chartTreatmentPopup';
        popup.className = 'treatment-popup';
        popup.innerHTML = `
            <div class="treatment-popup-header">
                <span class="treatment-popup-title" id="chartPopupTitle">Tooth Information</span>
                <button class="treatment-popup-close" id="chartPopupClose"><i class="fas fa-times"></i></button>
            </div>
            <div class="treatment-popup-content" id="chartPopupContent"></div>
        `;
        viewerContainer.appendChild(popup);
        const closeBtn = popup.querySelector('#chartPopupClose');
        closeBtn.addEventListener('click', () => { popup.style.display = 'none'; });
    }

    const data = (window._latestChartDataMap || {})[parseInt(toothNumber)] || {};
    const title = document.getElementById('chartPopupTitle');
    if (title && window._chart3DViewer) {
        title.textContent = `Tooth #${toothNumber} — ${window._chart3DViewer.getToothName(toothNumber)}`;
    }
    const content = document.getElementById('chartPopupContent');
    if (content) {
        content.innerHTML = `
            <div class="text-sm"><b>Condition:</b> ${(data.condition || '—').replace('_', ' ')}</div>
            <div class="text-sm"><b>Treatment:</b> ${(data.status || 'none').replace('_', ' ')}</div>
            <div class="text-sm"><b>Notes:</b> ${data.notes ? data.notes : '<span class="text-gray-500">None</span>'}</div>
        `;
    }
    popup.style.display = 'block';

    // Position near the click if event available
    try {
        const canvas = viewerContainer.querySelector('canvas');
        if (event && canvas) {
            const rect = canvas.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const y = event.clientY - rect.top;
            popup.style.position = 'absolute';
            popup.style.left = Math.max(8, Math.min(x + 10, rect.width - 220)) + 'px';
            popup.style.top = Math.max(8, Math.min(y + 10, rect.height - 140)) + 'px';
        }
    } catch (e) { /* ignore positioning errors */ }

    // No highlight for read-only viewer (keep condition colors only)
}

function updateHiddenFieldRadio(fieldName, value) {
    updateHiddenField(fieldName, value);
    // Also handle conditional fields
    if (fieldName === 'under_treatment') {
        toggleTreatmentCondition();
    } else if (fieldName === 'serious_illness') {
        toggleIllnessDetails();
    } else if (fieldName === 'hospitalized') {
        toggleHospitalizationDetails();
    }
}

function updateHiddenFieldCheckbox(fieldName, value) {
    // For medical conditions, we need to handle multiple values
    if (fieldName === 'medical_conditions') {
        updateMedicalConditions();
    }
}

function toggleTreatmentCondition() {
    const underTreatmentYes = document.querySelector('input[name="under_treatment"][value="yes"]');
    const treatmentConditionDiv = document.getElementById('treatment_condition_div');
    
    if (underTreatmentYes && underTreatmentYes.checked) {
        treatmentConditionDiv.classList.remove('hidden');
    } else {
        treatmentConditionDiv.classList.add('hidden');
    }
}

function toggleIllnessDetails() {
    const seriousIllnessYes = document.querySelector('input[name="serious_illness"][value="yes"]');
    const illnessDetailsDiv = document.getElementById('illness_details_div');
    
    if (seriousIllnessYes && seriousIllnessYes.checked) {
        illnessDetailsDiv.classList.remove('hidden');
    } else {
        illnessDetailsDiv.classList.add('hidden');
    }
}

function toggleHospitalizationDetails() {
    const hospitalizedYes = document.querySelector('input[name="hospitalized"][value="yes"]');
    const hospitalizationDetailsDiv = document.getElementById('hospitalization_details_div');
    
    if (hospitalizedYes && hospitalizedYes.checked) {
        hospitalizationDetailsDiv.classList.remove('hidden');
    } else {
        hospitalizationDetailsDiv.classList.add('hidden');
    }
}

function updateMedicalConditions() {
    // Remove existing hidden medical condition inputs
    const existingConditions = document.querySelectorAll('input[type="hidden"][name="medical_conditions[]"]');
    existingConditions.forEach(input => input.remove());
    
    // Add new hidden inputs for checked conditions
    const checkedConditions = document.querySelectorAll('input[name="medical_conditions[]"]:checked');
    const form = document.querySelector('form[action*="/checkup/save/"]');
    
    if (form) {
        checkedConditions.forEach(checkbox => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'medical_conditions[]';
            hiddenInput.value = checkbox.value;
            form.appendChild(hiddenInput);
        });
    }
}

// Initialize medical conditions checkboxes when panel opens
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners for medical conditions checkboxes
    const medicalConditionCheckboxes = document.querySelectorAll('input[name="medical_conditions[]"]');
    medicalConditionCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateMedicalConditions();
        });
    });

    // Initialize conditional fields
    toggleTreatmentCondition();
    toggleIllnessDetails();
    toggleHospitalizationDetails();
});

// Save Medical History Function
function saveMedicalHistory() {
    // Collect all form data from the medical history panel
    const formData = new FormData();
    
    // Get patient ID from the view panel (you may need to adjust this based on your data structure)
    const patientId = getCurrentPatientId();
    if (!patientId) {
        alert('Patient ID not found. Please select a patient first.');
        return;
    }
    
    // Add patient ID
    formData.append('patient_id', patientId);
    
    // Dental History
    formData.append('previous_dentist', document.getElementById('previous_dentist')?.value || '');
    formData.append('last_dental_visit', document.getElementById('last_dental_visit')?.value || '');
    
    // Physician Information
    formData.append('physician_name', document.getElementById('physician_name')?.value || '');
    formData.append('physician_specialty', document.getElementById('physician_specialty')?.value || '');
    formData.append('physician_phone', document.getElementById('physician_phone')?.value || '');
    formData.append('physician_address', document.getElementById('physician_address')?.value || '');
    
    // General Health
    const goodHealth = document.querySelector('input[name="good_health"]:checked');
    formData.append('good_health', goodHealth ? goodHealth.value : '');
    
    const underTreatment = document.querySelector('input[name="under_treatment"]:checked');
    formData.append('under_treatment', underTreatment ? underTreatment.value : '');
    formData.append('treatment_condition', document.getElementById('treatment_condition')?.value || '');
    
    const seriousIllness = document.querySelector('input[name="serious_illness"]:checked');
    formData.append('serious_illness', seriousIllness ? seriousIllness.value : '');
    formData.append('illness_details', document.getElementById('illness_details')?.value || '');
    
    const hospitalized = document.querySelector('input[name="hospitalized"]:checked');
    formData.append('hospitalized', hospitalized ? hospitalized.value : '');
    formData.append('hospitalization_where', document.querySelector('input[name="hospitalization_where"]')?.value || '');
    formData.append('hospitalization_when', document.querySelector('input[name="hospitalization_when"]')?.value || '');
    formData.append('hospitalization_why', document.querySelector('input[name="hospitalization_why"]')?.value || '');
    
    const tobaccoUse = document.querySelector('input[name="tobacco_use"]:checked');
    formData.append('tobacco_use', tobaccoUse ? tobaccoUse.value : '');
    
    formData.append('blood_pressure', document.getElementById('blood_pressure')?.value || '');
    formData.append('allergies', document.getElementById('allergies')?.value || '');
    
    // Women Only
    const pregnant = document.querySelector('input[name="pregnant"]:checked');
    formData.append('pregnant', pregnant ? pregnant.value : '');
    
    const nursing = document.querySelector('input[name="nursing"]:checked');
    formData.append('nursing', nursing ? nursing.value : '');
    
    const birthControl = document.querySelector('input[name="birth_control"]:checked');
    formData.append('birth_control', birthControl ? birthControl.value : '');
    
    // Medical Conditions
    const checkedConditions = document.querySelectorAll('input[name="medical_conditions[]"]:checked');
    checkedConditions.forEach(checkbox => {
        formData.append('medical_conditions[]', checkbox.value);
    });
    
    formData.append('other_conditions', document.getElementById('other_conditions')?.value || '');
    
    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                     document.querySelector('input[name="<?= csrf_token() ?>"]')?.value;
    if (csrfToken) {
        formData.append('<?= csrf_token() ?>', csrfToken);
    }
    
    // Show loading state
    const saveButton = document.querySelector('button[onclick="saveMedicalHistory()"]');
    const originalText = saveButton.innerHTML;
    saveButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    saveButton.disabled = true;
    
    // Send data to server
    fetch('/patient/save-medical-history', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showNotification('Medical history saved successfully!', 'success');
            
            // Close the panel after successful save
            setTimeout(() => {
                const newActionPanel = document.getElementById('newActionPanel');
                const viewPanel = document.getElementById('viewPatientPanel');
                if (newActionPanel) {
                    newActionPanel.classList.remove('active');
                }
                if (viewPanel) {
                    viewPanel.classList.remove('shifted');
                }
                document.body.classList.remove('panel-open');
            }, 1500);
        } else {
            showNotification('Error saving medical history: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error saving medical history. Please try again.', 'error');
    })
    .finally(() => {
        // Restore button state
        saveButton.innerHTML = originalText;
        saveButton.disabled = false;
    });
}

// Helper function to get current patient ID
function getCurrentPatientId() {
    // Try to get patient ID from the body data attribute (set when viewing a patient)
    const currentPatientId = document.body.getAttribute('data-current-patient-id');
    if (currentPatientId) {
        return currentPatientId;
    }
    
    // Try to get patient ID from various sources
    const patientIdElement = document.querySelector('[data-patient-id]');
    if (patientIdElement) {
        return patientIdElement.getAttribute('data-patient-id');
    }
    
    // Check if there's a patient ID in the URL or other elements
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('patient_id') || urlParams.get('id');
}

// Populate patient view panel with patient data
function populatePatientViewPanel(patient) {
    // Set patient ID for other functions to use
    document.body.setAttribute('data-current-patient-id', patient.id);
    
    // Set patient ID on the pencil icon for editing
    const pencilIcon = document.querySelector('.showUpdatePatientPanelBtn');
    if (pencilIcon) {
        pencilIcon.setAttribute('data-patient-id', patient.id);
    }
    
    // Wire delete button
    const deleteBtn = document.getElementById('deletePatientBtn');
    if (deleteBtn) {
        deleteBtn.onclick = function() {
            const confirmed = confirm('Delete this patient and all related records? This cannot be undone.');
            if (!confirmed) return;
            
            // CSRF token
            const csrfName = '<?= csrf_token() ?>';
            const csrfVal = document.querySelector(`input[name="${csrfName}"]`)?.value || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            fetch(`/admin/patients/delete/${patient.id}`, {
                method: 'POST',
                headers: csrfVal ? { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfVal } : { 'Content-Type': 'application/json' },
                body: JSON.stringify({})
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    showNotification('Patient deleted successfully', 'success');
                    // Close panels and optionally remove the row
                    const viewPanel = document.getElementById('viewPatientPanel');
                    if (viewPanel) viewPanel.classList.remove('active');
                    document.body.classList.remove('panel-open');
                    // Remove row if present
                    const row = document.querySelector(`tr.patient-row[data-patient*="\"id\":${patient.id}"]`);
                    if (row) row.remove();
                } else {
                    showNotification(res.message || 'Failed to delete patient', 'error');
                }
            })
            .catch(err => {
                console.error('Delete failed:', err);
                showNotification('Error deleting patient', 'error');
            });
        };
    }
    
    // Populate patient header information
    const patientNameElement = document.getElementById('view-patient-name');
    const patientEmailElement = document.getElementById('view-patient-email');
    
    if (patientNameElement) {
        patientNameElement.textContent = patient.name || 'N/A';
    }
    if (patientEmailElement) {
        patientEmailElement.textContent = patient.email || 'N/A';
    }
    
    // Populate contact information
    const patientPhoneElement = document.getElementById('view-patient-phone');
    const patientGenderElement = document.getElementById('view-patient-gender');
    const patientDateOfBirthElement = document.getElementById('view-patient-date-of-birth');
    const patientAddressElement = document.getElementById('view-patient-address');
    
    if (patientPhoneElement) {
        patientPhoneElement.textContent = patient.phone || 'N/A';
    }
    if (patientGenderElement) {
        patientGenderElement.textContent = patient.gender || 'N/A';
    }
    if (patientDateOfBirthElement) {
        patientDateOfBirthElement.textContent = patient.date_of_birth || 'N/A';
    }
    if (patientAddressElement) {
        patientAddressElement.textContent = patient.address || 'N/A';
    }
    
    // Load patient statistics (treatments, appointments, bills)
    loadPatientStatistics(patient.id);
}

// Load patient statistics (treatments, appointments, bills)
function loadPatientStatistics(patientId) {
    // Ensure patient ID is an integer
    patientId = parseInt(patientId);
    
    // Load treatments count
    fetch(`/patient/get-treatments/${patientId}`)
        .then(response => response.json())
        .then(data => {
            const treatmentsElement = document.querySelector('#viewPatientPanel .flex.items-center.gap-1 span b');
            if (treatmentsElement) {
                treatmentsElement.textContent = data.success ? data.treatments.length : '0';
            }
        })
        .catch(error => {
            console.error('Error loading treatments count:', error);
        });
    
    // Load total spent amount from bills
    fetch(`/patient/get-bills/${patientId}`)
        .then(response => response.json())
        .then(data => {
            const spentElement = document.querySelector('#viewPatientPanel .flex.items-center.gap-1 + .flex.items-center.gap-1 span b');
            if (spentElement && data.success) {
                const totalSpent = data.bills.reduce((sum, bill) => sum + parseFloat(bill.paid_amount || 0), 0);
                spentElement.textContent = `$${totalSpent.toFixed(2)}`;
            } else if (spentElement) {
                spentElement.textContent = '$0.00';
            }
        })
        .catch(error => {
            console.error('Error loading bills:', error);
            const spentElement = document.querySelector('#viewPatientPanel .flex.items-center.gap-1 + .flex.items-center.gap-1 span b');
            if (spentElement) {
                spentElement.textContent = '$0.00';
            }
        });
    
    // Load tab content when tabs are clicked
    setupPatientTabHandlers(patientId);
    
    // Load initial treatments tab content since it's active by default
    loadPatientTreatments(patientId);
}

// Setup patient tab handlers
function setupPatientTabHandlers(patientId) {
    const tabButtons = document.querySelectorAll('.view-patient-tab-btn');
    const tabContents = document.querySelectorAll('.view-patient-tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            
            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => {
                btn.classList.remove('active', 'bg-white', 'shadow-sm');
                btn.classList.add('font-semibold', 'text-indigo-500');
            });
            tabContents.forEach(content => {
                content.classList.add('hidden');
            });
            
            // Add active class to clicked button
            this.classList.add('active', 'bg-white', 'shadow-sm');
            this.classList.remove('font-semibold', 'text-indigo-500');
            
            // Show corresponding content
            const targetContent = document.getElementById(`view-patient-tab-content-${tabName}`);
            if (targetContent) {
                targetContent.classList.remove('hidden');
                loadTabContent(tabName, patientId);
            }
        });
    });
}

// Load content for specific tabs
function loadTabContent(tabName, patientId) {
    switch(tabName) {
        case 'treatments':
            loadPatientTreatments(patientId);
            break;
        case 'appointments':
            loadPatientAppointments(patientId);
            break;
        case 'bills':
            loadPatientBills(patientId);
            break;
    }
}

// Open dental chart panel and load latest chart
function openDentalChart(patientId) {
    const panel = document.getElementById('dentalChartPanel');
    const content = document.getElementById('dental-chart-content');
    if (!panel || !content) return;
    
    // Build skeleton with header, list, and 3D viewer (kept in DOM during updates)
    content.innerHTML = `
        <div class="mb-3 text-sm text-gray-600">Latest record: <b id="chart-latest-date">Loading…</b> <span id="chart-diagnosis" class="text-gray-500"></span></div>
        <div id="dental-chart-list" class="mb-3 grid grid-cols-1 sm:grid-cols-2 gap-2"></div>
        <div class="mt-2">
            <div class="dental-3d-viewer-container">
                <div id="dentalChart3DViewer" class="dental-3d-viewer" style="height: 460px;">
                    <div class="model-loading" id="chart3dLoading">
                        <i class="fas fa-spinner fa-spin mr-2"></i>Loading 3D Model...
                    </div>
                    <div class="model-error hidden" id="chart3dError">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <div>Failed to load 3D model</div>
                    </div>
                    <canvas class="dental-3d-canvas"></canvas>
                </div>
            </div>
        </div>
    `;
    
    panel.classList.add('active');
    document.body.classList.add('panel-open');
    
    // Fetch latest chart for this patient
    fetch(`/admin/patient-dental-chart/${patientId}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                const dateEl = content.querySelector('#chart-latest-date');
                if (dateEl) dateEl.textContent = 'N/A';
                const listEl = content.querySelector('#dental-chart-list');
                if (listEl) listEl.innerHTML = `<div class="text-center text-sm text-gray-600">No teeth data recorded.</div>`;
                return;
            }
            const rows = Array.isArray(data.chart) ? data.chart : [];
            if (rows.length === 0) {
                renderDentalChart(content, { chart: [], record_date: 'N/A' });
                return;
            }
            // Determine latest record_date and keep only that record's teeth
            const latestDate = rows.reduce((acc, row) => {
                const d = row.record_date || row.created_at || ''; return acc && acc > d ? acc : d;
            }, '');
            const latestChart = rows.filter(r => (r.record_date || '') === latestDate);
            renderDentalChart(content, { chart: latestChart, record_date: latestDate, diagnosis: rows[0]?.diagnosis || '' });
        })
        .catch(err => {
            console.error('Error loading dental chart:', err);
            content.innerHTML = `<div class="text-center text-red-600 text-sm">Error loading dental chart.</div>`;
        });
}

// Render dental chart simple grid
function renderDentalChart(container, data) {
    const chart = data.chart || [];
    const recordDate = data.record_date || 'N/A';
    const diagnosis = data.diagnosis || '';
    const dateEl = container.querySelector('#chart-latest-date');
    if (dateEl) dateEl.textContent = recordDate;
    const diagEl = container.querySelector('#chart-diagnosis');
    if (diagEl && diagnosis) diagEl.textContent = ` — ${diagnosis}`;
    
    const listEl = container.querySelector('#dental-chart-list');
    if (listEl) {
        if (chart.length === 0) {
            listEl.innerHTML = `<div class="text-center text-sm text-gray-600">No teeth data recorded.</div>`;
        } else {
            let listHtml = '';
            chart.forEach(tooth => {
                listHtml += `
                    <div class="border rounded p-2 text-center text-xs">
                        <div class="font-semibold">#${tooth.tooth_number}</div>
                        <div class="text-gray-600">${(tooth.condition || '—').replace('_',' ')}</div>
                        <div class="text-indigo-600">${(tooth.status || 'none').replace('_',' ')}</div>
                    </div>
                `;
            });
            listEl.innerHTML = listHtml;
        }
    }

    // Also color the 3D model using chart data
    try {
        if (window.Dental3DViewer) {
            // Initialize once per open
            if (!window._chart3DViewer) {
                window._chart3DViewer = new window.Dental3DViewer('dentalChart3DViewer', {
                    enableToothSelection: true,
                    showControls: true,
                    highlightOnClick: false,
                    onToothClick: (toothNumber, clickPoint, event, meta) => {
                        showChartToothPopup(toothNumber, clickPoint, event, meta);
                    }
                });
                window._chart3DViewer.init();
            } else {
                // Reset any prior colors
                window._chart3DViewer.resetAllTeethColor();
            }

            // Map conditions to colors
            const colorMap = {
                healthy: { r: 0.0, g: 0.8, b: 0.2 },
                cavity: { r: 0.9, g: 0.0, b: 0.0 },
                filled: { r: 0.2, g: 0.4, b: 0.9 },
                crown: { r: 0.9, g: 0.9, b: 0.9 },
                root_canal: { r: 1.0, g: 0.5, b: 0.0 },
                extraction_needed: { r: 0.6, g: 0.0, b: 0.0 },
                other: { r: 0.6, g: 0.6, b: 0.6 },
                missing: { r: 0.0, g: 0.0, b: 0.0 } // special: invisible
            };

            // Apply chart colors
            chart.forEach(tooth => {
                const condition = (tooth.condition || '').replace(' ', '_') || 'healthy';
                const color = colorMap[condition] || colorMap.other;
                window._chart3DViewer.setToothColor(parseInt(tooth.tooth_number), color);
            });

            // Save chart data map for popup lookups
            window._latestChartDataMap = {};
            chart.forEach(t => { window._latestChartDataMap[parseInt(t.tooth_number)] = t; });
        }
    } catch (e) {
        console.warn('3D chart color mapping failed:', e);
    }
}
// Load patient treatments
function loadPatientTreatments(patientId) {
    const treatmentsContent = document.getElementById('view-patient-tab-content-treatments');
    if (treatmentsContent) {
        // Ensure patient ID is an integer
        patientId = parseInt(patientId);
        
        // Debug: Log the patient ID being searched
        console.log('Loading treatments for patient ID:', patientId);
        
        // Show loading state
        treatmentsContent.innerHTML = `
            <div class="text-center">
                <i class="fas fa-spinner fa-spin text-2xl mb-2 block text-gray-400"></i>
                <span>Loading treatments...</span>
            </div>
        `;
        
        // Fetch treatments from server
        fetch(`/patient/get-treatments/${patientId}`)
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                // Debug: Log the response
                console.log('Treatments response:', data);
                
                if (data.success && data.treatments.length > 0) {
                    // Display treatments
                    let treatmentsHtml = '<div class="space-y-3">';
                    data.treatments.forEach(treatment => {
                        const date = new Date(treatment.record_date).toLocaleDateString();
                        treatmentsHtml += `
                            <div class="bg-white rounded-lg p-3 border border-gray-200">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-semibold text-gray-800">Treatment #${treatment.id}</h4>
                                    <span class="text-sm text-gray-500">${date}</span>
                                </div>
                                <div class="text-sm text-gray-600 mb-2">
                                    <strong>Diagnosis:</strong> ${treatment.diagnosis || 'N/A'}
                                </div>
                                <div class="text-sm text-gray-600 mb-2">
                                    <strong>Treatment:</strong> ${treatment.treatment || 'N/A'}
                                </div>
                                ${treatment.notes ? `<div class="text-sm text-gray-600"><strong>Notes:</strong> ${treatment.notes}</div>` : ''}
                            </div>
                        `;
                    });
                    treatmentsHtml += '</div>';
                    treatmentsContent.innerHTML = treatmentsHtml;
                } else {
                    treatmentsContent.innerHTML = `
                        <div class="text-center">
                            <i class="fas fa-notes-medical text-2xl mb-2 block text-gray-400"></i>
                            <span>No treatments recorded yet</span>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading treatments:', error);
                treatmentsContent.innerHTML = `
                    <div class="text-center">
                        <i class="fas fa-exclamation-triangle text-2xl mb-2 block text-red-400"></i>
                        <span>Error loading treatments</span>
                    </div>
                `;
            });
    }
}

// Load patient appointments
function loadPatientAppointments(patientId) {
    const appointmentsContent = document.getElementById('view-patient-tab-content-appointments');
    if (appointmentsContent) {
        // Ensure patient ID is an integer
        patientId = parseInt(patientId);
        
        // Show loading state
        appointmentsContent.innerHTML = `
            <div class="text-center">
                <i class="fas fa-spinner fa-spin text-2xl mb-2 block text-gray-400"></i>
                <span>Loading appointments...</span>
            </div>
        `;
        
        // Fetch appointments from server
        fetch(`/patient/get-appointments/${patientId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.appointments.length > 0) {
                    // Display appointments
                    let appointmentsHtml = '<div class="space-y-3">';
                    data.appointments.forEach(appointment => {
                        const date = new Date(appointment.appointment_datetime).toLocaleDateString();
                        const time = new Date(appointment.appointment_datetime).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                        const statusClass = getStatusClass(appointment.status);
                        
                        appointmentsHtml += `
                            <div class="bg-white rounded-lg p-3 border border-gray-200">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-semibold text-gray-800">Appointment #${appointment.id}</h4>
                                    <span class="text-xs px-2 py-1 rounded-full ${statusClass}">${appointment.status}</span>
                                </div>
                                <div class="text-sm text-gray-600 mb-2">
                                    <strong>Date:</strong> ${date} at ${time}
                                </div>
                                <div class="text-sm text-gray-600 mb-2">
                                    <strong>Type:</strong> ${appointment.appointment_type}
                                </div>
                                ${appointment.remarks ? `<div class="text-sm text-gray-600"><strong>Remarks:</strong> ${appointment.remarks}</div>` : ''}
                            </div>
                        `;
                    });
                    appointmentsHtml += '</div>';
                    appointmentsContent.innerHTML = appointmentsHtml;
                } else {
                    appointmentsContent.innerHTML = `
                        <div class="text-center">
                            <i class="far fa-calendar-alt text-2xl mb-2 block text-gray-400"></i>
                            <span>No appointments found</span>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading appointments:', error);
                appointmentsContent.innerHTML = `
                    <div class="text-center">
                        <i class="fas fa-exclamation-triangle text-2xl mb-2 block text-red-400"></i>
                        <span>Error loading appointments</span>
                    </div>
                `;
            });
    }
}

// Helper function to get status class for appointments
function getStatusClass(status) {
    switch(status) {
        case 'completed':
            return 'bg-green-100 text-green-800';
        case 'confirmed':
            return 'bg-blue-100 text-blue-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'ongoing':
            return 'bg-purple-100 text-purple-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

// Helper function to get payment status class
function getPaymentStatusClass(status) {
    switch(status) {
        case 'paid':
            return 'bg-green-100 text-green-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'partial':
            return 'bg-blue-100 text-blue-800';
        case 'waived':
            return 'bg-gray-100 text-gray-800';
        case 'refunded':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

// Load patient bills
function loadPatientBills(patientId) {
    const billsContent = document.getElementById('view-patient-tab-content-bills');
    if (billsContent) {
        // Ensure patient ID is an integer
        patientId = parseInt(patientId);
        
        // Show loading state
        billsContent.innerHTML = `
            <div class="text-center">
                <i class="fas fa-spinner fa-spin text-2xl mb-2 block text-gray-400"></i>
                <span>Loading bills...</span>
            </div>
        `;
        
        // Fetch bills from server
        fetch(`/patient/get-bills/${patientId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.bills.length > 0) {
                    // Display bills
                    let billsHtml = '<div class="space-y-3">';
                    data.bills.forEach(bill => {
                        const date = new Date(bill.created_at).toLocaleDateString();
                        const statusClass = getPaymentStatusClass(bill.payment_status);
                        
                        billsHtml += `
                            <div class="bg-white rounded-lg p-3 border border-gray-200">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-semibold text-gray-800">Invoice #${bill.invoice_number || bill.id}</h4>
                                    <span class="text-xs px-2 py-1 rounded-full ${statusClass}">${bill.payment_status}</span>
                                </div>
                                <div class="text-sm text-gray-600 mb-2">
                                    <strong>Total Amount:</strong> $${bill.total_amount}
                                </div>
                                <div class="text-sm text-gray-600 mb-2">
                                    <strong>Paid Amount:</strong> $${bill.paid_amount}
                                </div>
                                <div class="text-sm text-gray-600 mb-2">
                                    <strong>Balance:</strong> $${bill.balance_amount}
                                </div>
                                ${bill.payment_notes ? `<div class="text-sm text-gray-600"><strong>Notes:</strong> ${bill.payment_notes}</div>` : ''}
                            </div>
                        `;
                    });
                    billsHtml += '</div>';
                    billsContent.innerHTML = billsHtml;
                } else {
                    billsContent.innerHTML = `
                        <div class="text-center">
                            <i class="fas fa-file-invoice-dollar text-2xl mb-2 block text-gray-400"></i>
                            <span>No bills available</span>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading bills:', error);
                billsContent.innerHTML = `
                    <div class="text-center">
                        <i class="fas fa-exclamation-triangle text-2xl mb-2 block text-red-400"></i>
                        <span>Error loading bills</span>
                    </div>
                `;
            });
    }
}

// Load patient data for update form
function loadPatientForUpdate(patientId) {
    // Try to get patient data from the button's data attribute first
    const patientButton = document.querySelector(`[data-patient-id="${patientId}"]`);
    if (patientButton) {
        // Look for the corresponding view button that has the full patient data
        const viewButtons = document.querySelectorAll('.showViewPatientPanelBtn');
        let foundPatient = null;
        
        viewButtons.forEach(viewButton => {
            const patientData = viewButton.getAttribute('data-patient');
            if (patientData) {
                try {
                    const patient = JSON.parse(patientData);
                    if (patient.id == patientId) {
                        foundPatient = patient;
                    }
                } catch (error) {
                    console.error('Error parsing patient data:', error);
                }
            }
        });
        
        if (foundPatient) {
            populateUpdatePatientForm(foundPatient);
            return;
        }
    }
    
    // Fallback: try to get from current patient view panel
    const currentPatientId = document.body.getAttribute('data-current-patient-id');
    if (currentPatientId && currentPatientId == patientId) {
        // Get patient data from the view panel elements
        const patient = {
            id: currentPatientId,
            name: document.getElementById('view-patient-name')?.textContent || '',
            email: document.getElementById('view-patient-email')?.textContent || '',
            phone: document.getElementById('view-patient-phone')?.textContent || '',
            gender: document.getElementById('view-patient-gender')?.textContent || '',
            date_of_birth: document.getElementById('view-patient-date-of-birth')?.textContent || '',
            address: document.getElementById('view-patient-address')?.textContent || '',
            age: '', // We'll calculate this from date of birth
            occupation: '', // Not available in view panel
            nationality: '' // Not available in view panel
        };
        
        // Calculate age from date of birth if available
        if (patient.date_of_birth && patient.date_of_birth !== 'N/A') {
            const birthDate = new Date(patient.date_of_birth);
            const today = new Date();
            const age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                patient.age = age - 1;
            } else {
                patient.age = age;
            }
        }
        
        populateUpdatePatientForm(patient);
    } else {
        showNotification('Patient data not found. Please try again.', 'error');
    }
}

// Populate update patient form with patient data
function populateUpdatePatientForm(patient) {
    if (!patient) return;
    
    console.log('Populating update form with patient data:', patient);
    
    // Set patient ID
    const patientIdField = document.getElementById('update-patient-id');
    if (patientIdField) {
        patientIdField.value = patient.id;
    }
    
    // Populate form fields
    const fields = [
        { id: 'update-patient-name', value: patient.name },
        { id: 'update-patient-email', value: patient.email },
        { id: 'update-patient-phone', value: patient.phone },
        { id: 'update-patient-gender', value: patient.gender },
        { id: 'update-patient-date-of-birth', value: patient.date_of_birth },
        { id: 'update-patient-age', value: patient.age },
        { id: 'update-patient-occupation', value: patient.occupation },
        { id: 'update-patient-nationality', value: patient.nationality },
        { id: 'update-patient-address', value: patient.address }
    ];
    
    fields.forEach(field => {
        const element = document.getElementById(field.id);
        if (element) {
            element.value = field.value || '';
            console.log(`Set ${field.id} to:`, field.value || '');
        } else {
            console.error(`Element not found: ${field.id}`);
        }
    });
    
    // Set form action
    const updateForm = document.getElementById('updatePatientForm');
    if (updateForm) {
        updateForm.action = `/admin/patients/update/${patient.id}`;
    }
}

// Load and display patient medical history
function loadPatientMedicalHistory(patientId, options = { readOnly: false }) {
    // Show loading state
    showNotification('Loading medical history...', 'info');
    
    // Fetch medical history data from server
    fetch(`/patient/get-medical-history/${patientId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Populate the medical history form with existing data
                populateMedicalHistoryForm(data.medical_history);
                
                // Open the medical history panel
                const newActionPanel = document.getElementById('newActionPanel');
                const viewPanel = document.getElementById('viewPatientPanel');
                if (newActionPanel) {
                    newActionPanel.classList.add('active');
                }
                if (viewPanel) {
                    viewPanel.classList.add('shifted');
                }
                document.body.classList.add('panel-open');

                // Apply read-only state if requested
                setMedicalHistoryReadOnly(!!options.readOnly);
                
                showNotification('Medical history loaded successfully!', 'success');
            } else {
                showNotification('No medical history found for this patient.', 'info');
                // Still open the panel but with empty form
                const newActionPanel = document.getElementById('newActionPanel');
                const viewPanel = document.getElementById('viewPatientPanel');
                if (newActionPanel) {
                    newActionPanel.classList.add('active');
                }
                if (viewPanel) {
                    viewPanel.classList.add('shifted');
                }
                document.body.classList.add('panel-open');

                // If no data, respect requested mode (readOnly/editable)
                setMedicalHistoryReadOnly(!!options.readOnly);
            }
        })
        .catch(error => {
            console.error('Error loading medical history:', error);
            showNotification('Error loading medical history. Please try again.', 'error');
        });
}

// Populate medical history form with existing data
function populateMedicalHistoryForm(medicalHistory) {
    if (!medicalHistory) return;
    
    // Dental History
    if (medicalHistory.previous_dentist) {
        document.getElementById('previous_dentist').value = medicalHistory.previous_dentist;
    }
    if (medicalHistory.last_dental_visit) {
        document.getElementById('last_dental_visit').value = medicalHistory.last_dental_visit;
    }
    
    // Physician Information
    if (medicalHistory.physician_name) {
        document.getElementById('physician_name').value = medicalHistory.physician_name;
    }
    if (medicalHistory.physician_specialty) {
        document.getElementById('physician_specialty').value = medicalHistory.physician_specialty;
    }
    if (medicalHistory.physician_phone) {
        document.getElementById('physician_phone').value = medicalHistory.physician_phone;
    }
    if (medicalHistory.physician_address) {
        document.getElementById('physician_address').value = medicalHistory.physician_address;
    }
    
    // General Health
    if (medicalHistory.good_health) {
        const goodHealthRadio = document.querySelector(`input[name="good_health"][value="${medicalHistory.good_health}"]`);
        if (goodHealthRadio) goodHealthRadio.checked = true;
    }
    
    if (medicalHistory.under_treatment) {
        const underTreatmentRadio = document.querySelector(`input[name="under_treatment"][value="${medicalHistory.under_treatment}"]`);
        if (underTreatmentRadio) underTreatmentRadio.checked = true;
    }
    
    if (medicalHistory.treatment_condition) {
        document.getElementById('treatment_condition').value = medicalHistory.treatment_condition;
    }
    
    if (medicalHistory.serious_illness) {
        const seriousIllnessRadio = document.querySelector(`input[name="serious_illness"][value="${medicalHistory.serious_illness}"]`);
        if (seriousIllnessRadio) seriousIllnessRadio.checked = true;
    }
    
    if (medicalHistory.illness_details) {
        document.getElementById('illness_details').value = medicalHistory.illness_details;
    }
    
    if (medicalHistory.hospitalized) {
        const hospitalizedRadio = document.querySelector(`input[name="hospitalized"][value="${medicalHistory.hospitalized}"]`);
        if (hospitalizedRadio) hospitalizedRadio.checked = true;
    }
    
    if (medicalHistory.hospitalization_where) {
        document.querySelector('input[name="hospitalization_where"]').value = medicalHistory.hospitalization_where;
    }
    if (medicalHistory.hospitalization_when) {
        document.querySelector('input[name="hospitalization_when"]').value = medicalHistory.hospitalization_when;
    }
    if (medicalHistory.hospitalization_why) {
        document.querySelector('input[name="hospitalization_why"]').value = medicalHistory.hospitalization_why;
    }
    
    if (medicalHistory.tobacco_use) {
        const tobaccoUseRadio = document.querySelector(`input[name="tobacco_use"][value="${medicalHistory.tobacco_use}"]`);
        if (tobaccoUseRadio) tobaccoUseRadio.checked = true;
    }
    
    if (medicalHistory.blood_pressure) {
        document.getElementById('blood_pressure').value = medicalHistory.blood_pressure;
    }
    
    if (medicalHistory.allergies) {
        document.getElementById('allergies').value = medicalHistory.allergies;
    }
    
    // Women Only
    if (medicalHistory.pregnant) {
        const pregnantRadio = document.querySelector(`input[name="pregnant"][value="${medicalHistory.pregnant}"]`);
        if (pregnantRadio) pregnantRadio.checked = true;
    }
    
    if (medicalHistory.nursing) {
        const nursingRadio = document.querySelector(`input[name="nursing"][value="${medicalHistory.nursing}"]`);
        if (nursingRadio) nursingRadio.checked = true;
    }
    
    if (medicalHistory.birth_control) {
        const birthControlRadio = document.querySelector(`input[name="birth_control"][value="${medicalHistory.birth_control}"]`);
        if (birthControlRadio) birthControlRadio.checked = true;
    }
    
    // Medical Conditions
    if (medicalHistory.medical_conditions && Array.isArray(medicalHistory.medical_conditions)) {
        medicalHistory.medical_conditions.forEach(condition => {
            const checkbox = document.querySelector(`input[name="medical_conditions[]"][value="${condition}"]`);
            if (checkbox) checkbox.checked = true;
        });
    }
    
    if (medicalHistory.other_conditions) {
        document.getElementById('other_conditions').value = medicalHistory.other_conditions;
    }
    
    // Trigger conditional field visibility
    toggleTreatmentCondition();
    toggleIllnessDetails();
    toggleHospitalizationDetails();
}

// Toggle read-only state in Medical History panel
function setMedicalHistoryReadOnly(readOnly) {
    const panel = document.getElementById('newActionPanel');
    if (!panel) return;

    // Inputs
    const inputs = panel.querySelectorAll('input, textarea, select');
    inputs.forEach(el => {
        if (el.type === 'radio' || el.type === 'checkbox') {
            el.disabled = readOnly;
        } else {
            el.readOnly = readOnly;
            el.disabled = false; // keep enabled for readOnly visuals except radios/checkboxes
        }
        // Visual cue
        if (readOnly) {
            el.classList.add('bg-gray-50');
        } else {
            el.classList.remove('bg-gray-50');
        }
    });

    // Buttons: Save and Clear should be hidden in read-only
    const saveBtn = panel.querySelector('button[onclick="saveMedicalHistory()"]');
    const clearBtn = panel.querySelector('button[onclick="clearMedicalHistoryForm()"]');
    if (saveBtn) saveBtn.style.display = readOnly ? 'none' : '';
    if (clearBtn) clearBtn.style.display = readOnly ? 'none' : '';

    // Update header title to reflect mode
    const header = panel.querySelector('h2');
    if (header) {
        header.textContent = readOnly ? 'View Medical History' : 'Patient Medical History';
    }
}

// Clear medical history form
function clearMedicalHistoryForm() {
    if (confirm('Are you sure you want to clear all medical history data? This action cannot be undone.')) {
        // Clear all text inputs
        const textInputs = document.querySelectorAll('#newActionPanel input[type="text"], #newActionPanel input[type="date"], #newActionPanel input[type="tel"], #newActionPanel textarea');
        textInputs.forEach(input => {
            input.value = '';
        });
        
        // Clear all radio buttons
        const radioButtons = document.querySelectorAll('#newActionPanel input[type="radio"]');
        radioButtons.forEach(radio => {
            radio.checked = false;
        });
        
        // Clear all checkboxes
        const checkboxes = document.querySelectorAll('#newActionPanel input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        
        // Hide conditional fields
        toggleTreatmentCondition();
        toggleIllnessDetails();
        toggleHospitalizationDetails();
        
        showNotification('Medical history form cleared.', 'success');
    }
}

// Notification function
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'} mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Utility: debug form data
function debugFormData() {
    console.log('Form submission debug:');
    var getVal = function(id) { var el = document.getElementById(id); return el ? el.value : ''; };
    console.log('Name:', getVal('name'));
    console.log('Email:', getVal('email'));
    console.log('Phone:', getVal('phone'));
    console.log('Gender:', getVal('gender'));
    console.log('Date of Birth:', getVal('date_of_birth'));
    console.log('Age:', getVal('age'));
    console.log('Calculated Age:', getVal('calculated_age'));
    console.log('Occupation:', getVal('occupation'));
    console.log('Nationality:', getVal('nationality'));
    console.log('Address:', getVal('address'));
}
