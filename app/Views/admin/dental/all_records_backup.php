<?= view('templates/header') ?>

<!-- Three.js Library for 3D Dental Model -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>

<!-- 3D Dental Viewer Styles and Scripts -->
<link rel="stylesheet" href="<?= base_url('css/dental-3d-viewer.css') ?>">
<script src="<?= base_url('js/dental-3d-viewer.js') ?>"></script>

<div class="flex min-h-screen bg-gray-100">
    <!-- Include existing sidebar -->
    <?= view('templates/sidebar') ?>

    <!-- Main Content Area -->
    <div class="flex-1 lg:ml-0 p-6">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Records Management</h1>
            <p class="text-gray-600">Comprehensive dental records management system</p>
        </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Total Records Card -->
        <div class="bg-white border-l-4 border-blue-400 shadow rounded-lg p-5">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-bold text-blue-600 uppercase mb-1">Total Records</div>
                    <div class="text-2xl font-bold text-gray-800"><?= number_format($stats['total_records']) ?></div>
                </div>
                <i class="fas fa-folder fa-2x text-gray-300"></i>
            </div>
        </div>

        <!-- Active Patients Card -->
        <div class="bg-white border-l-4 border-green-400 shadow rounded-lg p-5">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-bold text-green-600 uppercase mb-1">Active Patients</div>
                    <div class="text-2xl font-bold text-gray-800"><?= number_format($stats['active_patients']) ?></div>
                </div>
                <i class="fas fa-user-check fa-2x text-gray-300"></i>
            </div>
        </div>

        <!-- With X-rays Card -->
        <div class="bg-white border-l-4 border-purple-400 shadow rounded-lg p-5">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-bold text-purple-600 uppercase mb-1">With X-rays</div>
                    <div class="text-2xl font-bold text-gray-800"><?= number_format($stats['with_xrays']) ?></div>
                </div>
                <i class="fas fa-x-ray fa-2x text-gray-300"></i>
            </div>
        </div>

        <!-- Pending Follow-ups Card -->
        <div class="bg-white border-l-4 border-orange-400 shadow rounded-lg p-5">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-bold text-orange-600 uppercase mb-1">Follow-ups</div>
                    <div class="text-2xl font-bold text-gray-800"><?= number_format($stats['pending_followups']) ?></div>
                </div>
                <i class="fas fa-clock fa-2x text-gray-300"></i>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Records Table -->
        <div class="lg:col-span-2 bg-white shadow rounded-lg">
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-bold text-gray-800">Recent Records</h2>
                    <div class="flex space-x-2">
                        <button class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                            <i class="fas fa-plus mr-2"></i>New Record
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($records)): ?>
                            <?php foreach ($records as $record): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= date('M d, Y', strtotime($record['record_date'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <button type="button" 
                                                onclick="openPatientRecordsModal(<?= $record['user_id'] ?>)"
                                                class="text-blue-600 hover:text-blue-800 font-medium cursor-pointer hover:underline">
                                            <?= esc($record['patient_name']) ?>
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php 
                                        $type = 'Checkup';
                                        $typeClass = 'text-blue-600 bg-blue-100';
                                        
                                        if (!empty($record['xray_image_url'])) {
                                            $type = 'X-ray';
                                            $typeClass = 'text-purple-600 bg-purple-100';
                                        }
                                        if (!empty($record['treatment']) && strlen($record['treatment']) > 10) {
                                            $type = 'Treatment';
                                            $typeClass = 'text-red-600 bg-red-100';
                                        }
                                        ?>
                                        <span class="px-2 py-1 text-xs font-semibold <?= $typeClass ?> rounded-full"><?= $type ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php 
                                        $status = 'Complete';
                                        $statusClass = 'text-green-600 bg-green-100';
                                        
                                        if (!empty($record['next_appointment_date']) && strtotime($record['next_appointment_date']) > time()) {
                                            $status = 'Follow-up';
                                            $statusClass = 'text-orange-600 bg-orange-100';
                                        }
                                        ?>
                                        <span class="px-2 py-1 text-xs font-semibold <?= $statusClass ?> rounded-full"><?= $status ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="<?= base_url('admin/dental-records/view/' . $record['id']) ?>" 
                                               class="text-blue-600 hover:text-blue-900" title="View Record">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= base_url('admin/dental-records/edit/' . $record['id']) ?>" 
                                               class="text-green-600 hover:text-green-900" title="Edit Record">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="#" onclick="deleteRecord(<?= $record['id'] ?>)" 
                                               class="text-red-600 hover:text-red-900" title="Delete Record">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    <div class="flex flex-col items-center py-8">
                                        <i class="fas fa-folder-open fa-3x text-gray-300 mb-4"></i>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Records Found</h3>
                                        <p class="text-gray-500">There are no dental records to display.</p>
                                        <a href="<?= base_url('admin/dental-records/create') ?>" 
                                           class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                            <i class="fas fa-plus mr-2"></i>Create First Record
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sidebar Info Panel -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="<?= base_url('admin/dental-records/create') ?>" 
                       class="block w-full bg-blue-600 text-white text-center py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i>Create New Record
                    </a>
                    <a href="<?= base_url('admin/patients') ?>" 
                       class="block w-full bg-green-600 text-white text-center py-2 rounded-lg hover:bg-green-700">
                        <i class="fas fa-users mr-2"></i>View Patients
                    </a>
                    <a href="#" 
                       class="block w-full bg-purple-600 text-white text-center py-2 rounded-lg hover:bg-purple-700">
                        <i class="fas fa-download mr-2"></i>Export Records
                    </a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Activity</h3>
                <div class="space-y-4">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-plus text-blue-600 text-xs"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900">New record created for John Doe</p>
                            <p class="text-xs text-gray-500">2 hours ago</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-edit text-green-600 text-xs"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900">Record updated for Jane Smith</p>
                            <p class="text-xs text-gray-500">5 hours ago</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-x-ray text-purple-600 text-xs"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900">X-ray uploaded for Mike Johnson</p>
                            <p class="text-xs text-gray-500">1 day ago</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Patient Records Modal -->
<div id="patientRecordsModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
        <!-- Modal Header -->
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg font-bold text-gray-900">Patient Records</h3>
            <button type="button" onclick="closePatientRecordsModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Modal Navigation -->
        <div class="flex space-x-2 my-4 border-b">
            <button id="basic-info-tab" onclick="showRecordTab('basic-info')" 
                    class="record-tab px-4 py-2 text-sm font-medium rounded-t-lg bg-blue-600 text-white">
                <i class="fas fa-user mr-2"></i>Basic Info
            </button>
            <button id="dental-records-tab" onclick="showRecordTab('dental-records')" 
                    class="record-tab px-4 py-2 text-sm font-medium rounded-t-lg bg-gray-200 text-gray-700 hover:bg-gray-300">
                <i class="fas fa-tooth mr-2"></i>Dental Records
            </button>
            <button id="dental-chart-tab" onclick="showRecordTab('dental-chart')" 
                    class="record-tab px-4 py-2 text-sm font-medium rounded-t-lg bg-gray-200 text-gray-700 hover:bg-gray-300">
                <i class="fas fa-chart-line mr-2"></i>Dental Chart
            </button>
            <button id="appointments-tab" onclick="showRecordTab('appointments')" 
                    class="record-tab px-4 py-2 text-sm font-medium rounded-t-lg bg-gray-200 text-gray-700 hover:bg-gray-300">
                <i class="fas fa-calendar mr-2"></i>Appointments
            </button>
            <button id="treatments-tab" onclick="showRecordTab('treatments')" 
                    class="record-tab px-4 py-2 text-sm font-medium rounded-t-lg bg-gray-200 text-gray-700 hover:bg-gray-300">
                <i class="fas fa-procedures mr-2"></i>Treatments
            </button>
            <button id="medical-records-tab" onclick="showRecordTab('medical-records')" 
                    class="record-tab px-4 py-2 text-sm font-medium rounded-t-lg bg-gray-200 text-gray-700 hover:bg-gray-300">
                <i class="fas fa-file-medical mr-2"></i>Medical Records
            </button>
        </div>
        
        <!-- Modal Content -->
        <div id="modalContent" class="mt-4 max-h-96 overflow-y-auto">
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin"></i> Loading...
            </div>
        </div>
    </div>
</div>

<script>
function deleteRecord(recordId) {
    if (confirm('Are you sure you want to delete this record? This action cannot be undone.')) {
        fetch(`<?= base_url('admin/dental-records/delete/') ?>${recordId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the row from the table
                const row = event.target.closest('tr');
                row.remove();
                
                // Show success message
                showAlert('Record deleted successfully', 'success');
                
                // Update stats if needed
                location.reload(); // Simple reload to update stats
            } else {
                showAlert(data.message || 'Failed to delete record', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while deleting the record', 'error');
        });
    }
}

function showAlert(message, type) {
    // Create alert element
    const alert = document.createElement('div');
    alert.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    alert.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(alert);
    
    // Remove after 3 seconds
    setTimeout(() => {
        alert.remove();
    }, 3000);
}

// ==================== PATIENT RECORDS MODAL ====================

function openPatientRecordsModal(patientId) {
    // Show modal
    document.getElementById('patientRecordsModal').classList.remove('hidden');
    
    // Load patient basic info
    loadPatientInfo(patientId);
    
    // Store patient ID for later use
    window.currentPatientId = patientId;
    
    // Show basic info tab by default
    showRecordTab('basic-info');
}

function closePatientRecordsModal() {
    document.getElementById('patientRecordsModal').classList.add('hidden');
    // Clean up 3D viewer resources
    cleanupModal3DViewer();
    // Clear content
    document.getElementById('modalContent').innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
}

function loadPatientInfo(patientId) {
    fetch(`<?= base_url('admin/patient-info') ?>/${patientId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayPatientInfo(data.patient);
            } else {
                showAlert(data.message || 'Failed to load patient information', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while loading patient information', 'error');
        });
}

function displayPatientInfo(patient) {
    const content = `
        <div class="bg-white p-6">
            <div class="flex items-center mb-6">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-user fa-2x text-blue-600"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">${patient.name}</h3>
                    <p class="text-gray-600">Patient ID: ${patient.id}</p>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full ${
                        patient.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                    }">${patient.status}</span>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <p class="text-gray-900">${patient.email || 'N/A'}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <p class="text-gray-900">${patient.phone || 'N/A'}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                    <p class="text-gray-900">${patient.date_of_birth || 'N/A'}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Age</label>
                    <p class="text-gray-900">${patient.age || 'N/A'}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                    <p class="text-gray-900">${patient.gender || 'N/A'}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Occupation</label>
                    <p class="text-gray-900">${patient.occupation || 'N/A'}</p>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <p class="text-gray-900">${patient.address || 'N/A'}</p>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('modalContent').innerHTML = content;
}

function showRecordTab(tabType) {
    // Update active tab
    document.querySelectorAll('.record-tab').forEach(tab => {
        tab.classList.remove('bg-blue-600', 'text-white');
        tab.classList.add('bg-gray-200', 'text-gray-700');
    });
    
    document.getElementById(`${tabType}-tab`).classList.remove('bg-gray-200', 'text-gray-700');
    document.getElementById(`${tabType}-tab`).classList.add('bg-blue-600', 'text-white');
    
    // Load appropriate content
    const patientId = window.currentPatientId;
    
    switch(tabType) {
        case 'basic-info':
            loadPatientInfo(patientId);
            break;
        case 'dental-records':
            loadDentalRecords(patientId);
            break;
        case 'dental-chart':
            loadDentalChart(patientId);
            break;
        case 'appointments':
            loadAppointments(patientId);
            break;
        case 'treatments':
            loadTreatments(patientId);
            break;
        case 'medical-records':
            loadMedicalRecords(patientId);
            break;
    }
}

function loadDentalRecords(patientId) {
    document.getElementById('modalContent').innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading dental records...</div>';
    
    fetch(`<?= base_url('admin/patient-dental-records') ?>/${patientId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayDentalRecords(data.records);
            } else {
                showAlert(data.message || 'Failed to load dental records', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while loading dental records', 'error');
        });
}

function displayDentalRecords(records) {
    let content = '<div class="bg-white p-6"><h3 class="text-lg font-bold mb-4">Dental Records</h3>';
    
    if (records.length === 0) {
        content += '<p class="text-gray-500 text-center py-8">No dental records found</p>';
    } else {
        content += '<div class="space-y-4">';
        records.forEach(record => {
            content += `
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
        });
        content += '</div>';
    }
    
    content += '</div>';
    document.getElementById('modalContent').innerHTML = content;
}

function loadDentalChart(patientId) {
    document.getElementById('modalContent').innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading dental chart...</div>';
    
    fetch(`<?= base_url('admin/patient-dental-chart') ?>/${patientId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayDentalChart(data);
            } else {
                showAlert(data.message || 'Failed to load dental chart', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while loading dental chart', 'error');
        });
}

function displayDentalChart(chartResponse) {
    let content = `
    <div class="bg-white p-6">
        <h3 class="text-lg font-bold mb-4">
            <i class="fas fa-chart-line text-blue-500 mr-2"></i>
            Dental Chart
        </h3>
        
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <!-- 3D Dental Model Viewer -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-semibold text-gray-800 mb-4 text-center">
                    <i class="fas fa-cube text-blue-500 mr-2"></i>
                    3D Dental Model
                </h4>
                <div class="dental-3d-viewer relative" id="dentalModalViewer">
                    <div class="model-loading text-center py-8" id="modalModelLoading">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2 text-blue-500"></i>
                        <p class="text-gray-600">Loading 3D Model...</p>
                    </div>
                    <div class="model-error hidden text-center py-8" id="modalModelError">
                        <i class="fas fa-exclamation-triangle text-red-500 text-2xl mb-2"></i>
                        <p class="text-red-600 mb-2">Failed to load 3D model</p>
                        <button onclick="initModalDental3D()" class="px-3 py-1 bg-blue-500 text-white rounded text-sm hover:bg-blue-600">
                            Retry
                        </button>
                    </div>
                    <canvas class="dental-3d-canvas"></canvas>
                    
                    <!-- Model Controls -->
                    <div class="model-controls">
                        <button class="model-control-btn" onclick="modalDental3DViewer?.resetCamera()" title="Reset View">
                            <i class="fas fa-home"></i>
                        </button>
                        <button class="model-control-btn" onclick="modalDental3DViewer?.toggleWireframe()" title="Toggle Wireframe">
                            <i class="fas fa-border-all"></i>
                        </button>
                        <button class="model-control-btn" onclick="modalDental3DViewer?.toggleAutoRotate()" title="Auto Rotate">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                
                <!-- 3D Model Color Legend -->
                <div class="mt-4 p-3 bg-white rounded-lg border">
                    <h5 class="text-sm font-semibold text-gray-700 mb-2">Color Legend:</h5>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-400 rounded mr-2"></div>
                            <span>Healthy</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-red-500 rounded mr-2"></div>
                            <span>Cavity</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-yellow-500 rounded mr-2"></div>
                            <span>Filled</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-purple-500 rounded mr-2"></div>
                            <span>Crown</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-gray-800 rounded mr-2"></div>
                            <span>Missing</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-blue-500 rounded mr-2"></div>
                            <span>Root Canal</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Chart Details -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-semibold text-gray-800 mb-4">
                    <i class="fas fa-list-alt text-blue-500 mr-2"></i>
                    Chart Details
                </h4>`;
    
    if (chartResponse.chart.length === 0) {
        content += '<p class="text-gray-500 text-center py-8">No dental chart data found</p>';
    } else {
        // Display visual teeth chart
        content += '<div class="mb-6">';
        content += '<h4 class="font-semibold mb-3">Teeth Overview</h4>';
        content += '<div class="grid grid-cols-8 gap-1 mb-4">';
        
        // Create visual representation of all 32 teeth
        for (let i = 1; i <= 32; i++) {
            const toothData = chartResponse.teeth_data[i] || [];
            let toothStatus = 'healthy';
            let statusClass = 'bg-green-100 text-green-800 border-green-300';
            
            if (toothData.length > 0) {
                const latestRecord = toothData[0];
                if (latestRecord.condition && latestRecord.condition !== 'healthy') {
                    toothStatus = latestRecord.condition;
                    switch(latestRecord.condition) {
                        case 'cavity':
                            statusClass = 'bg-red-100 text-red-800 border-red-300';
                            break;
                        case 'filled':
                            statusClass = 'bg-yellow-100 text-yellow-800 border-yellow-300';
                            break;
                        case 'crown':
                            statusClass = 'bg-purple-100 text-purple-800 border-purple-300';
                            break;
                        case 'missing':
                            statusClass = 'bg-gray-100 text-gray-800 border-gray-300';
                            break;
                        case 'root_canal':
                            statusClass = 'bg-blue-100 text-blue-800 border-blue-300';
                            break;
                        default:
                            statusClass = 'bg-orange-100 text-orange-800 border-orange-300';
                    }
                }
            }
            
            content += `
                <div class="w-8 h-8 border-2 ${statusClass} rounded flex items-center justify-center text-xs font-bold cursor-pointer hover:opacity-80"
                     title="Tooth ${i}: ${toothStatus}" onclick="showToothDetails(${i}, '${JSON.stringify(toothData).replace(/'/g, "\\'")}')">
                    ${i}
                </div>
            `;
        }
        content += '</div>';
        
        // Legend
        content += `
            <div class="grid grid-cols-2 gap-2 mb-6 text-xs">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-100 border-2 border-green-300 rounded mr-2"></div>
                    <span>Healthy</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-red-100 border-2 border-red-300 rounded mr-2"></div>
                    <span>Cavity</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-yellow-100 border-2 border-yellow-300 rounded mr-2"></div>
                    <span>Filled</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-purple-100 border-2 border-purple-300 rounded mr-2"></div>
                    <span>Crown</span>
                </div>
            </div>
        `;
        content += '</div>';
        
        // Detailed records list
        content += '<h5 class="font-semibold mb-3">Recent Records</h5>';
        content += '<div class="space-y-2 max-h-48 overflow-y-auto">';
        
        chartResponse.chart.slice(0, 5).forEach(record => {
            content += `
                <div class="border rounded-lg p-2 bg-white text-sm">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="font-medium">Tooth ${record.tooth_number}</span>
                            <span class="text-gray-500 ml-2">${record.condition || 'N/A'}</span>
                        </div>
                        <span class="text-xs text-gray-400">${formatDate(record.created_at)}</span>
                    </div>
                    ${record.notes ? `<p class="text-gray-600 mt-1 text-xs">${record.notes}</p>` : ''}
                </div>
            `;
        });
        content += '</div>';
    }
    
    content += `
            </div>
        </div>
    </div>
    `;
    
    document.getElementById('modalContent').innerHTML = content;
    
    // Initialize 3D viewer after content is loaded
    setTimeout(() => {
        initModalDental3D(chartResponse);
    }, 100);
}
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <span class="font-semibold">Tooth ${record.tooth_number}</span>
                            <span class="text-sm text-gray-600 ml-2">${new Date(record.record_date).toLocaleDateString()}</span>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full ${
                            record.condition === 'healthy' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                        }">${record.condition || 'Unknown'}</span>
                    </div>
                    ${record.diagnosis ? `<p class="text-sm mb-1"><strong>Diagnosis:</strong> ${record.diagnosis}</p>` : ''}
                    ${record.status ? `<p class="text-sm mb-1"><strong>Status:</strong> ${record.status}</p>` : ''}
                    ${record.recommended_service ? `<p class="text-sm mb-1"><strong>Recommended:</strong> ${record.recommended_service} ${record.service_price ? '($' + record.service_price + ')' : ''}</p>` : ''}
                    ${record.notes ? `<p class="text-sm text-gray-600">${record.notes}</p>` : ''}
                    ${record.dentist_name ? `<p class="text-xs text-gray-500 mt-2">Dr. ${record.dentist_name}</p>` : ''}
                </div>
            `;
        });
        content += '</div>';
    }
    
    content += '</div>';
    document.getElementById('modalContent').innerHTML = content;
}

function showToothDetails(toothNumber, toothDataJson) {
    try {
        const toothData = JSON.parse(toothDataJson);
        if (toothData.length === 0) {
            alert(`Tooth ${toothNumber}: No records found`);
            return;
        }
        
        let details = `Tooth ${toothNumber} Details:\\n\\n`;
        toothData.forEach((record, index) => {
            details += `Record ${index + 1}:\\n`;
            details += `Date: ${new Date(record.record_date).toLocaleDateString()}\\n`;
            details += `Condition: ${record.condition || 'Not specified'}\\n`;
            details += `Status: ${record.status || 'Not specified'}\\n`;
            if (record.notes) details += `Notes: ${record.notes}\\n`;
            details += `\\n`;
        });
        
        alert(details);
    } catch (e) {
        alert(`Tooth ${toothNumber}: Healthy`);
    }
}

function loadAppointments(patientId) {
    document.getElementById('modalContent').innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading appointments...</div>';
    
    fetch(`<?= base_url('admin/patient-appointments') ?>/${patientId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAppointments(data);
            } else {
                showAlert(data.message || 'Failed to load appointments', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while loading appointments', 'error');
        });
}

function displayAppointments(appointmentData) {
    let content = '<div class="bg-white p-6"><h3 class="text-lg font-bold mb-4">Appointment History</h3>';
    
    const presentAppointments = appointmentData.present_appointments || [];
    const pastAppointments = appointmentData.past_appointments || [];
    const totalAppointments = appointmentData.total_appointments || 0;
    
    // Summary
    content += `
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-50 p-3 rounded-lg text-center">
                <div class="text-2xl font-bold text-blue-600">${totalAppointments}</div>
                <div class="text-sm text-blue-800">Total Appointments</div>
            </div>
            <div class="bg-green-50 p-3 rounded-lg text-center">
                <div class="text-2xl font-bold text-green-600">${presentAppointments.length}</div>
                <div class="text-sm text-green-800">Upcoming</div>
            </div>
            <div class="bg-gray-50 p-3 rounded-lg text-center">
                <div class="text-2xl font-bold text-gray-600">${pastAppointments.length}</div>
                <div class="text-sm text-gray-800">Completed</div>
            </div>
        </div>
    `;
    
    // Present/Upcoming Appointments
    if (presentAppointments.length > 0) {
        content += '<h4 class="font-semibold mb-3 text-green-700">🔄 Upcoming Appointments</h4>';
        content += '<div class="space-y-3 mb-6">';
        presentAppointments.forEach(appointment => {
            const appointmentDate = new Date(appointment.appointment_datetime);
            content += `
                <div class="border-l-4 border-green-500 bg-green-50 rounded-lg p-4">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h5 class="font-semibold text-green-800">${appointmentDate.toLocaleDateString()} at ${appointmentDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</h5>
                            <p class="text-sm text-green-700">Dr. ${appointment.dentist_name || 'TBA'}</p>
                        </div>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">${appointment.status}</span>
                    </div>
                    ${appointment.branch_name ? `<p class="text-sm mb-2"><strong>Branch:</strong> ${appointment.branch_name}</p>` : ''}
                    ${appointment.services ? `<p class="text-sm mb-2"><strong>Services:</strong> ${appointment.services}</p>` : ''}
                    ${appointment.total_cost ? `<p class="text-sm mb-2"><strong>Estimated Cost:</strong> $${appointment.total_cost}</p>` : ''}
                    ${appointment.appointment_type ? `<p class="text-sm mb-2"><strong>Type:</strong> ${appointment.appointment_type}</p>` : ''}
                    ${appointment.remarks ? `<p class="text-sm text-green-600">${appointment.remarks}</p>` : ''}
                </div>
            `;
        });
        content += '</div>';
    }
    
    // Past Appointments
    if (pastAppointments.length > 0) {
        content += '<h4 class="font-semibold mb-3 text-gray-700">📋 Past Appointments</h4>';
        content += '<div class="space-y-3 max-h-64 overflow-y-auto">';
        pastAppointments.forEach(appointment => {
            const appointmentDate = new Date(appointment.appointment_datetime);
            content += `
                <div class="border rounded-lg p-4 ${appointment.status === 'completed' ? 'bg-gray-50' : 'bg-red-50'}">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h5 class="font-semibold">${appointmentDate.toLocaleDateString()} at ${appointmentDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</h5>
                            <p class="text-sm text-gray-600">Dr. ${appointment.dentist_name || 'Unknown'}</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full ${
                            appointment.status === 'completed' ? 'bg-green-100 text-green-800' :
                            appointment.status === 'cancelled' ? 'bg-red-100 text-red-800' :
                            appointment.status === 'no-show' ? 'bg-orange-100 text-orange-800' :
                            'bg-gray-100 text-gray-800'
                        }">${appointment.status}</span>
                    </div>
                    ${appointment.branch_name ? `<p class="text-sm mb-2"><strong>Branch:</strong> ${appointment.branch_name}</p>` : ''}
                    ${appointment.services ? `<p class="text-sm mb-2"><strong>Services:</strong> ${appointment.services}</p>` : ''}
                    ${appointment.total_cost ? `<p class="text-sm mb-2"><strong>Cost:</strong> $${appointment.total_cost}</p>` : ''}
                    ${appointment.appointment_type ? `<p class="text-sm mb-2"><strong>Type:</strong> ${appointment.appointment_type}</p>` : ''}
                    ${appointment.remarks ? `<p class="text-sm text-gray-600">${appointment.remarks}</p>` : ''}
                </div>
            `;
        });
        content += '</div>';
    }
    
    if (presentAppointments.length === 0 && pastAppointments.length === 0) {
        content += '<p class="text-gray-500 text-center py-8">No appointments found</p>';
    }
    
    content += '</div>';
    document.getElementById('modalContent').innerHTML = content;
}

function loadTreatments(patientId) {
    document.getElementById('modalContent').innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading treatments...</div>';
    
    fetch(`<?= base_url('admin/patient-treatments') ?>/${patientId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayTreatments(data);
            } else {
                showAlert(data.message || 'Failed to load treatments', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while loading treatments', 'error');
        });
}

function displayTreatments(treatmentData) {
    let content = '<div class="bg-white p-6"><h3 class="text-lg font-bold mb-4">Treatment Records</h3>';
    
    const treatments = treatmentData.treatments || [];
    const totalTreatments = treatmentData.total_treatments || 0;
    
    if (treatments.length === 0) {
        content += '<p class="text-gray-500 text-center py-8">No treatment records found</p>';
    } else {
        // Summary
        const totalAmount = treatments.reduce((sum, treatment) => sum + (parseFloat(treatment.amount) || 0), 0);
        const completedTreatments = treatments.filter(t => t.status === 'completed').length;
        const ongoingTreatments = treatments.filter(t => t.status === 'ongoing').length;
        
        content += `
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 p-3 rounded-lg text-center">
                    <div class="text-2xl font-bold text-blue-600">${totalTreatments}</div>
                    <div class="text-sm text-blue-800">Total Treatments</div>
                </div>
                <div class="bg-green-50 p-3 rounded-lg text-center">
                    <div class="text-2xl font-bold text-green-600">${completedTreatments}</div>
                    <div class="text-sm text-green-800">Completed</div>
                </div>
                <div class="bg-orange-50 p-3 rounded-lg text-center">
                    <div class="text-2xl font-bold text-orange-600">${ongoingTreatments}</div>
                    <div class="text-sm text-orange-800">Ongoing</div>
                </div>
                <div class="bg-purple-50 p-3 rounded-lg text-center">
                    <div class="text-2xl font-bold text-purple-600">$${totalAmount.toFixed(2)}</div>
                    <div class="text-sm text-purple-800">Total Cost</div>
                </div>
            </div>
        `;
        
        // Treatment list
        content += '<div class="space-y-4 max-h-96 overflow-y-auto">';
        treatments.forEach(treatment => {
            const treatmentDate = new Date(treatment.record_date);
            const amount = parseFloat(treatment.amount) || 0;
            
            content += `
                <div class="border rounded-lg p-4 ${
                    treatment.status === 'completed' ? 'bg-green-50 border-green-200' :
                    treatment.status === 'ongoing' ? 'bg-orange-50 border-orange-200' :
                    'bg-gray-50 border-gray-200'
                }">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <h5 class="font-semibold text-lg">${treatment.treatment}</h5>
                            <p class="text-sm text-gray-600">${treatmentDate.toLocaleDateString()}</p>
                        </div>
                        <div class="text-right">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full ${
                                treatment.status === 'completed' ? 'bg-green-100 text-green-800' :
                                treatment.status === 'ongoing' ? 'bg-orange-100 text-orange-800' :
                                'bg-gray-100 text-gray-800'
                            }">${treatment.status}</span>
                            ${amount > 0 ? `<div class="text-lg font-bold text-green-600 mt-1">$${amount.toFixed(2)}</div>` : ''}
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-3">
                        <div>
                            <p class="text-sm"><strong>👨‍⚕️ Doctor:</strong> Dr. ${treatment.doctor_name || 'Unknown'}</p>
                            <p class="text-sm"><strong>📋 Source:</strong> ${treatment.source_type}</p>
                        </div>
                        <div>
                            ${treatment.diagnosis ? `<p class="text-sm"><strong>🔍 Diagnosis:</strong> ${treatment.diagnosis}</p>` : ''}
                            ${treatment.next_appointment_date ? `<p class="text-sm"><strong>📅 Next Visit:</strong> ${new Date(treatment.next_appointment_date).toLocaleDateString()}</p>` : ''}
                        </div>
                    </div>
                    
                    ${treatment.notes ? `
                        <div class="mt-3 p-3 bg-white rounded border-l-4 border-blue-400">
                            <p class="text-sm"><strong>📝 Notes:</strong></p>
                            <p class="text-sm text-gray-700 mt-1">${treatment.notes}</p>
                        </div>
                    ` : ''}
                </div>
            `;
        });
        content += '</div>';
    }
    
    content += '</div>';
    document.getElementById('modalContent').innerHTML = content;
}

function loadMedicalRecords(patientId) {
    document.getElementById('modalContent').innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading medical records...</div>';
    
    fetch(`<?= base_url('admin/patient-medical-records') ?>/${patientId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMedicalRecords(data);
            } else {
                showAlert(data.message || 'Failed to load medical records', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while loading medical records', 'error');
        });
}

function displayMedicalRecords(medicalData) {
    let content = '<div class="bg-white p-6"><h3 class="text-lg font-bold mb-4">Medical Records</h3>';
    
    const records = medicalData.medical_records || [];
    const patientInfo = medicalData.patient_info || {};
    const diagnoses = medicalData.diagnoses || [];
    const xrays = medicalData.xrays || [];
    const summary = medicalData.summary || {};
    
    if (records.length === 0) {
        content += '<p class="text-gray-500 text-center py-8">No medical records found</p>';
    } else {
        // Patient Summary
        content += `
            <div class="bg-blue-50 rounded-lg p-4 mb-6">
                <h4 class="font-semibold mb-3">Patient Information</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm"><strong>Name:</strong> ${patientInfo.name || 'N/A'}</p>
                        <p class="text-sm"><strong>Age:</strong> ${patientInfo.age || 'N/A'}</p>
                    </div>
                    <div>
                        <p class="text-sm"><strong>Gender:</strong> ${patientInfo.gender || 'N/A'}</p>
                        <p class="text-sm"><strong>DOB:</strong> ${patientInfo.date_of_birth || 'N/A'}</p>
                    </div>
                </div>
            </div>
        `;
        
        // Summary Statistics
        content += `
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-green-50 p-3 rounded-lg text-center">
                    <div class="text-2xl font-bold text-green-600">${summary.total_records || 0}</div>
                    <div class="text-sm text-green-800">Total Records</div>
                </div>
                <div class="bg-purple-50 p-3 rounded-lg text-center">
                    <div class="text-2xl font-bold text-purple-600">${summary.total_diagnoses || 0}</div>
                    <div class="text-sm text-purple-800">Diagnoses</div>
                </div>
                <div class="bg-orange-50 p-3 rounded-lg text-center">
                    <div class="text-2xl font-bold text-orange-600">${summary.total_xrays || 0}</div>
                    <div class="text-sm text-orange-800">X-rays</div>
                </div>
            </div>
        `;
        
        // Quick Access Tabs
        content += `
            <div class="flex space-x-2 mb-4">
                <button onclick="showMedicalSection('all')" 
                        class="medical-tab-btn px-4 py-2 text-sm bg-blue-600 text-white rounded">All Records</button>
                <button onclick="showMedicalSection('diagnoses')" 
                        class="medical-tab-btn px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded">Diagnoses</button>
                <button onclick="showMedicalSection('xrays')" 
                        class="medical-tab-btn px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded">X-rays</button>
            </div>
        `;
        
        // All Records Section
        content += '<div id="medical-all" class="medical-section">';
        content += '<h4 class="font-semibold mb-3">Complete Medical History</h4>';
        content += '<div class="space-y-4 max-h-64 overflow-y-auto">';
        records.forEach(record => {
            content += `
                <div class="border rounded-lg p-4 bg-gray-50">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h5 class="font-semibold">${new Date(record.record_date).toLocaleDateString()}</h5>
                            <p class="text-sm text-gray-600">Recorded by: ${record.recorded_by} (${record.recorder_role})</p>
                        </div>
                        ${record.xray_image_url ? '<span class="px-2 py-1 text-xs bg-orange-100 text-orange-800 rounded">📷 X-ray</span>' : ''}
                    </div>
                    ${record.diagnosis ? `<p class="text-sm mb-2"><strong>🔍 Diagnosis:</strong> ${record.diagnosis}</p>` : ''}
                    ${record.treatment ? `<p class="text-sm mb-2"><strong>💊 Treatment:</strong> ${record.treatment}</p>` : ''}
                    ${record.notes ? `<p class="text-sm text-gray-600"><strong>📝 Notes:</strong> ${record.notes}</p>` : ''}
                    ${record.xray_image_url ? `<p class="text-sm mt-2"><a href="${record.xray_image_url}" target="_blank" class="text-blue-600 hover:underline">🔗 View X-ray Image</a></p>` : ''}
                </div>
            `;
        });
        content += '</div></div>';
        
        // Diagnoses Section
        content += '<div id="medical-diagnoses" class="medical-section hidden">';
        content += '<h4 class="font-semibold mb-3">Diagnoses History</h4>';
        if (diagnoses.length > 0) {
            content += '<div class="space-y-3">';
            diagnoses.forEach(diagnosis => {
                content += `
                    <div class="border-l-4 border-purple-400 bg-purple-50 rounded-r-lg p-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <h5 class="font-semibold text-purple-800">${diagnosis.diagnosis}</h5>
                                <p class="text-sm text-purple-600">${new Date(diagnosis.date).toLocaleDateString()}</p>
                            </div>
                            <p class="text-sm text-purple-700">Dr. ${diagnosis.doctor}</p>
                        </div>
                        ${diagnosis.treatment ? `<p class="text-sm mt-2 text-purple-600"><strong>Treatment:</strong> ${diagnosis.treatment}</p>` : ''}
                    </div>
                `;
            });
            content += '</div>';
        } else {
            content += '<p class="text-gray-500 text-center py-4">No specific diagnoses recorded</p>';
        }
        content += '</div>';
        
        // X-rays Section
        content += '<div id="medical-xrays" class="medical-section hidden">';
        content += '<h4 class="font-semibold mb-3">X-ray Images</h4>';
        if (xrays.length > 0) {
            content += '<div class="grid grid-cols-2 gap-4">';
            xrays.forEach(xray => {
                content += `
                    <div class="border rounded-lg p-3 bg-orange-50">
                        <div class="text-center mb-2">
                            <div class="w-full h-32 bg-gray-200 rounded flex items-center justify-center mb-2">
                                <a href="${xray.image_url}" target="_blank" class="text-orange-600 hover:text-orange-800">
                                    <i class="fas fa-image text-2xl"></i>
                                    <p class="text-sm mt-1">View X-ray</p>
                                </a>
                            </div>
                        </div>
                        <p class="text-sm font-semibold">${new Date(xray.date).toLocaleDateString()}</p>
                        <p class="text-sm text-gray-600">Dr. ${xray.doctor}</p>
                        ${xray.notes ? `<p class="text-xs text-gray-500 mt-1">${xray.notes}</p>` : ''}
                    </div>
                `;
            });
            content += '</div>';
        } else {
            content += '<p class="text-gray-500 text-center py-4">No X-ray images available</p>';
        }
        content += '</div>';
    }
    
    content += '</div>';
    document.getElementById('modalContent').innerHTML = content;
}

function showMedicalSection(section) {
    // Hide all sections
    document.querySelectorAll('.medical-section').forEach(sec => sec.classList.add('hidden'));
    // Reset all buttons
    document.querySelectorAll('.medical-tab-btn').forEach(btn => {
        btn.classList.remove('bg-blue-600', 'text-white');
        btn.classList.add('bg-gray-200', 'text-gray-700');
    });
    
    // Show selected section
    document.getElementById('medical-' + section).classList.remove('hidden');
    // Highlight active button
    event.target.classList.remove('bg-gray-200', 'text-gray-700');
    event.target.classList.add('bg-blue-600', 'text-white');
}

// Global variable for modal 3D viewer
let modalDental3DViewer = null;

// Initialize 3D Dental Viewer in Modal
function initModalDental3D(chartResponse) {
    if (modalDental3DViewer) {
        modalDental3DViewer.destroy();
        modalDental3DViewer = null;
    }
    
    const container = document.getElementById('dentalModalViewer');
    if (!container) {
        console.warn('3D Dental viewer container not found');
        return;
    }
    
    // Initialize the 3D viewer
    modalDental3DViewer = new Dental3DViewer('dentalModalViewer', {
        modelUrl: '<?= base_url('img/permanent_dentition-2.glb') ?>',
        enableToothSelection: true,
        showControls: false, // Controls are handled by buttons
        onToothClick: (toothNumber, clickPoint, event, data) => {
            handleModalToothClick(toothNumber, clickPoint, event, data, chartResponse);
        },
        onModelLoaded: () => {
            // Update 3D model colors when model is loaded
            updateModal3DModelColors(chartResponse);
        }
    });
    
    // Initialize the viewer
    setTimeout(() => {
        if (modalDental3DViewer.init()) {
            console.log('Modal 3D Dental viewer initialized successfully');
        } else {
            console.error('Failed to initialize modal 3D dental viewer');
        }
    }, 100);
}

// Handle tooth click in modal 3D viewer
function handleModalToothClick(toothNumber, clickPoint, event, data, chartResponse) {
    const toothData = chartResponse.teeth_data[toothNumber] || [];
    showToothDetails(toothNumber, JSON.stringify(toothData));
    
    // Optional: Show popup with tooth info
    const toothName = modalDental3DViewer.getToothName(toothNumber);
    const latestRecord = toothData.length > 0 ? toothData[0] : null;
    
    let popupContent = `
        <div class="bg-white border rounded-lg shadow-lg p-4 max-w-sm">
            <h4 class="font-bold text-lg mb-2">Tooth ${toothNumber}</h4>
            <p class="text-gray-600 mb-2">${toothName}</p>
    `;
    
    if (latestRecord) {
        popupContent += `
            <div class="space-y-2">
                <div><span class="font-medium">Condition:</span> ${latestRecord.condition || 'N/A'}</div>
                <div><span class="font-medium">Status:</span> ${latestRecord.status || 'N/A'}</div>
                ${latestRecord.notes ? `<div><span class="font-medium">Notes:</span> ${latestRecord.notes}</div>` : ''}
                <div class="text-sm text-gray-500">Last updated: ${formatDate(latestRecord.created_at)}</div>
            </div>
        `;
    } else {
        popupContent += '<p class="text-gray-500">No records found for this tooth</p>';
    }
    
    popupContent += '</div>';
    
    // Show temporary tooltip (you can enhance this)
    console.log(`Clicked tooth ${toothNumber}:`, toothData);
}

// Update 3D model colors based on dental chart data
function updateModal3DModelColors(chartResponse) {
    if (!modalDental3DViewer || !chartResponse) return;
    
    // Color mapping for different conditions
    const conditionColors = {
        'healthy': { r: 0.85, g: 0.95, b: 0.85 }, // Light green
        'cavity': { r: 0.95, g: 0.3, b: 0.3 },    // Red
        'filled': { r: 0.95, g: 0.85, b: 0.3 },   // Yellow
        'crown': { r: 0.7, g: 0.5, b: 0.95 },     // Purple
        'missing': { r: 0.0, g: 0.0, b: 0.0 },    // Black (will be transparent)
        'root_canal': { r: 0.3, g: 0.6, b: 0.95 }, // Blue
        'extraction_needed': { r: 0.95, g: 0.5, b: 0.0 }, // Orange
        'other': { r: 0.7, g: 0.7, b: 0.7 }       // Gray
    };
    
    // Update each tooth color based on its condition
    for (let toothNumber = 1; toothNumber <= 32; toothNumber++) {
        const toothData = chartResponse.teeth_data[toothNumber] || [];
        let condition = 'healthy';
        
        if (toothData.length > 0) {
            const latestRecord = toothData[0];
            condition = latestRecord.condition || 'healthy';
        }
        
        const color = conditionColors[condition] || conditionColors['healthy'];
        modalDental3DViewer.setToothColor(toothNumber, color);
    }
    
    console.log('Updated 3D model colors for dental chart');
}

// Utility function to format dates
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Clean up 3D viewer when modal is closed
function cleanupModal3DViewer() {
    if (modalDental3DViewer) {
        modalDental3DViewer.destroy();
        modalDental3DViewer = null;
    }
}

// Add event listener to clean up when modal is closed
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('patientRecordsModal');
    if (modal) {
        // Handle escape key and outside clicks
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                closePatientRecordsModal();
            }
        });
        
        // Handle clicks outside modal
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closePatientRecordsModal();
            }
        });
    }
});
</script>

<?= view('templates/footer') ?> 