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

    // View Patient
    document.querySelectorAll('.showViewPatientPanelBtn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var viewPanel = document.getElementById('viewPatientPanel');
            if (viewPanel) {
                console.log('View Patient button clicked');
                viewPanel.classList.add('active');
                document.body.classList.add('panel-open');
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
            newActionPanel.classList.add('active');
            // Shift the view panel to the left so both are visible side-by-side
            if (viewPanel) viewPanel.classList.add('shifted');
            document.body.classList.add('panel-open');
        });
        newActionCloseBtn.addEventListener('click', function() {
            newActionPanel.classList.remove('active');
            if (viewPanel) viewPanel.classList.remove('shifted');
            document.body.classList.remove('panel-open');
        });
    }

    // Update Patient (open panels)
    document.querySelectorAll('.showUpdatePatientPanelBtnTable, .showUpdatePatientPanelBtn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var updatePanel = document.getElementById('updatePatientPanel');
            if (updatePanel) {
                console.log('Update Patient button clicked');
                updatePanel.classList.add('active');
                document.body.classList.add('panel-open');
            }
        });
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
