<?php echo view('templates/header', ['title' => 'Patient Records']); ?>

<?php echo view('templates/sidebar', ['active' => 'records', 'user' => $user]); ?>

<div class="main-content" data-sidebar-offset>
    <?php echo view('templates/topbar', ['user' => $user]); ?>

    <div class="container mx-auto p-6">
        <!-- Enhanced Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg p-6 mb-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Patient Records Management</h1>
                    <p class="text-blue-100 text-lg">Comprehensive patient record access for your assigned branches</p>
                </div>
                <div class="hidden md:block">
                    <svg class="w-16 h-16 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Patient Selection Card -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center mb-4">
                <div class="p-2 bg-blue-100 rounded-lg mr-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-gray-900">Patient Selection</h2>
            </div>
            
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label for="patient-select" class="block text-sm font-medium text-gray-700 mb-3">Choose Patient to View Records:</label>
                    <div class="relative">
                        <select id="patient-select" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 bg-white">
                            <option value="">-- Select a patient --</option>
                            <?php if (!empty($patients)): ?>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?php echo esc($patient['id']); ?>" data-email="<?php echo esc($patient['email']); ?>" data-phone="<?php echo esc($patient['phone'] ?? ''); ?>"><?php echo esc($patient['name']); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="hidden md:flex items-center justify-center text-gray-400">
                    <div class="text-center">
                        <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <p class="text-sm">Select a patient to access their medical records</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Records Container -->
        <div id="records-container" class="hidden">
            <!-- Enhanced Patient Info Card -->
            <div id="patient-info" class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg mr-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white">Patient Information</h2>
                            <p class="text-green-100">Complete medical record overview</p>
                        </div>
                    </div>
                </div>
                <div id="patient-details" class="px-6 py-4">
                    <!-- Patient details will be populated here -->
                </div>
            </div>

            <!-- Enhanced Tab Navigation -->
            <div class="bg-white rounded-lg shadow-md mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Medical Records</h3>
                    <p class="text-sm text-gray-600">Access different sections of the patient's medical history</p>
                </div>
                <div class="px-6 py-4">
                    <div class="flex flex-wrap gap-2">
                        <button class="records-tab flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white shadow-sm hover:bg-blue-700 transition-colors" data-tab="appointments">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Appointments
                        </button>
                        <button class="records-tab flex items-center px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors" data-tab="treatments">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            Treatments
                        </button>
                        <button class="records-tab flex items-center px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors" data-tab="prescriptions">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                            </svg>
                            Prescriptions
                        </button>
                        <button class="records-tab flex items-center px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors" data-tab="invoices">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            Invoices
                        </button>
                        <button class="records-tab flex items-center px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors" data-tab="dental_charts">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Dental Charts
                        </button>
                        <button class="records-tab flex items-center px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors" data-tab="medical_history">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Medical History
                        </button>
                    </div>
                </div>
            </div>

            <!-- Enhanced Tab Content Container -->
            <div id="tab-content" class="bg-white rounded-lg shadow-md min-h-96">
                <div class="flex items-center justify-center py-16">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                        <p class="text-gray-500 text-lg">Loading patient records...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced No Patients Message -->
        <?php if (empty($patients)): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-8 text-center">
                    <div class="mx-auto w-24 h-24 bg-yellow-100 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-12 h-12 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">No Patients Available</h3>
                    <div class="max-w-md mx-auto text-gray-600 space-y-2">
                        <p>No patients have been assigned to your branches yet, or you are not assigned to any branches.</p>
                        <p class="text-sm">Please contact your administrator if you believe this is an error.</p>
                    </div>
                    <div class="mt-6">
                        <button onclick="window.location.reload()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Refresh Page
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const patientSelect = document.getElementById('patient-select');
    const recordsContainer = document.getElementById('records-container');
    const tabContent = document.getElementById('tab-content');
    const tabs = document.querySelectorAll('.records-tab');
    const patientDetails = document.getElementById('patient-details');
    
    let currentPatientId = null;
    let currentTab = 'appointments';

    // Patient selection change
    patientSelect.addEventListener('change', function() {
        const patientId = this.value;
        
        if (patientId) {
            currentPatientId = patientId;
            
            // Show records container
            recordsContainer.classList.remove('hidden');
            
            // Update patient info
            const selectedOption = this.options[this.selectedIndex];
            const patientName = selectedOption.text;
            const patientEmail = selectedOption.dataset.email || '';
            const patientPhone = selectedOption.dataset.phone || '';
            
            patientDetails.innerHTML = `
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg mr-3">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600">Patient Name</div>
                            <div class="font-semibold text-gray-900">${patientName}</div>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg mr-3">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600">Email Address</div>
                            <div class="font-semibold text-gray-900">${patientEmail || 'Not provided'}</div>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <div class="p-2 bg-purple-100 rounded-lg mr-3">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600">Phone Number</div>
                            <div class="font-semibold text-gray-900">${patientPhone || 'Not provided'}</div>
                        </div>
                    </div>
                </div>
            `;
            
            // Reset to appointments tab
            currentTab = 'appointments';
            updateActiveTab();
            loadTabContent();
        } else {
            currentPatientId = null;
            recordsContainer.classList.add('hidden');
        }
    });

    // Tab clicks
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            if (!currentPatientId) return;
            
            currentTab = this.dataset.tab;
            updateActiveTab();
            loadTabContent();
        });
    });

    function updateActiveTab() {
        tabs.forEach(tab => {
            if (tab.dataset.tab === currentTab) {
                tab.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                tab.classList.add('bg-blue-600', 'text-white', 'shadow-sm');
            } else {
                tab.classList.remove('bg-blue-600', 'text-white', 'shadow-sm');
                tab.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
            }
        });
    }

    function loadTabContent() {
        if (!currentPatientId || !currentTab) return;
        
        // Show loading
        tabContent.innerHTML = `
            <div class="flex items-center justify-center py-16">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                    <p class="text-gray-500 text-lg">Loading ${currentTab.replace('_', ' ')}...</p>
                    <p class="text-gray-400 text-sm mt-2">Please wait while we fetch the records</p>
                </div>
            </div>
        `;
        
        // Fetch tab content
        const url = `<?php echo site_url('staff/records'); ?>?ajax=1&tab=${currentTab}&patient_id=${currentPatientId}`;
        
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    // Treat non-2xx responses as errors so we can show a friendly UI
                    throw new Error('Server returned status ' + response.status);
                }
                return response.text();
            })
            .then(html => {
                const trimmed = (html || '').trim();
                if (!trimmed) {
                    // No content returned - show a contextual no-data card
                    const label = currentTab.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                    tabContent.innerHTML = `
                        <div class="flex items-center justify-center py-16">
                            <div class="text-center max-w-md">
                                <div class="mx-auto w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                                    <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">No ${label} Found</h3>
                                <p class="text-gray-500">This patient doesn't have any ${label.toLowerCase()} records yet.</p>
                            </div>
                        </div>
                    `;
                    return;
                }

                // Normal path: inject server-rendered HTML
                tabContent.innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading tab content:', error);
                const label = currentTab.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                tabContent.innerHTML = `
                    <div class="flex items-center justify-center py-16">
                        <div class="text-center max-w-md">
                            <div class="mx-auto w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mb-6">
                                <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-red-800 mb-2">Unable to Load ${label}</h3>
                            <p class="text-red-600 mb-6">There was a problem loading this section. This may be due to a server error or network issue.</p>
                            <div class="space-y-2">
                                <button onclick="loadTabContent()" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Try Again
                                </button>
                                <button onclick="window.location.reload()" class="block mx-auto text-sm text-gray-500 hover:text-gray-700 mt-2">
                                    Refresh entire page
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
    }
});
</script>

<?php echo view('templates/footer'); ?>
