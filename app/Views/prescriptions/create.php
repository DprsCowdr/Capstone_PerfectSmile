<?php $user = $user ?? session('user') ?? []; ?>
<div data-sidebar-offset>
    <nav class="sticky top-0 bg-white shadow-sm z-20 p-4 border-b border-gray-200 flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <a href="<?= base_url('admin/prescriptions') ?>" 
               class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Prescriptions
            </a>
            <div class="h-5 w-px bg-gray-300"></div>
            <h1 class="text-2xl font-bold text-gray-900">New Prescription</h1>
        </div>
    </nav>
    
    <main class="p-6 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto">
            <form method="post" action="<?= base_url('admin/prescriptions/store') ?>" class="space-y-6">
                <?= csrf_field() ?>

                <!-- Patient Information (combined with details) -->
                <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Patient Information
                    </h2>
                    <div class="grid grid-cols-1 gap-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Patient</label>
                                <select name="patient_id" id="patient_select" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    <option value="">Select a patient</option>
                                    <?php foreach ($patients as $pt): ?>
                                    <option value="<?= $pt['id'] ?>" 
                                            data-age="<?= esc($pt['age'] ?? '') ?>"
                                            data-gender="<?= esc($pt['gender'] ?? '') ?>"
                                            data-address="<?= esc($pt['address'] ?? '') ?>">
                                        <?= esc($pt['name']) ?> (<?= esc($pt['email']) ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Issue Date</label>
                                <input type="date" name="issue_date" value="<?= date('Y-m-d') ?>" 
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" />
                            </div>
                        </div>

                        <!-- Inline patient detail fields -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Age</label>
                                <input type="text" name="age" id="patient_age" placeholder="Enter age"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                                <select name="gender" id="patient_gender" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    <option value="">Select gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                                <input type="text" name="address" id="patient_address" placeholder="Enter address"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instructions Card -->
                <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Instructions
                    </h2>
                    <textarea name="instructions" placeholder="Enter any additional instructions or observations..."
                              class="w-full border border-gray-300 rounded-lg px-3 py-3 h-24 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"></textarea>
                </div>

                <!-- Medicines Section -->
                <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                            </svg>
                            Medicines
                        </h2>
                        <button type="button" id="addRow" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Medicine
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table id="itemsTable" class="w-full border border-gray-200 rounded-lg overflow-hidden">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <tr>
                                    <th class="p-3 text-left text-sm font-semibold text-gray-900">Medicine</th>
                                    <th class="p-3 text-left text-sm font-semibold text-gray-900">Dosage</th>
                                    <th class="p-3 text-left text-sm font-semibold text-gray-900">Frequency</th>
                                    <th class="p-3 text-left text-sm font-semibold text-gray-900">Duration</th>
                                    <th class="p-3 text-left text-sm font-semibold text-gray-900">Instructions</th>
                                    <th class="p-3 text-left text-sm font-semibold text-gray-900 w-20">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100"></tbody>
                        </table>
                    </div>
                    <div class="mt-4 text-sm text-gray-500 text-center py-4" id="emptyState">
                        <svg class="mx-auto h-8 w-8 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                        No medicines added yet. Click "Add Medicine" to get started.
                    </div>
                </div>

                <!-- Next Appointment & Dentist Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Next Appointment Card -->
                    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Next Appointment
                        </h2>
                        <input type="date" name="next_appointment" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" />
                    </div>

                    <!-- Dentist Info Card -->
                    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                            Dentist Information
                        </h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">License No.</label>
                                <input type="text" name="license_no" value="<?= esc($user['license_no'] ?? '') ?>" 
                                       placeholder="Enter license number"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Dentist Name</label>
                                <input type="text" name="dentist_name" value="<?= esc($user['name'] ?? '') ?>" 
                                       placeholder="Enter dentist name"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">PTR No.</label>
                                <input type="text" name="ptr_no" value="<?= esc($user['ptr_no'] ?? '') ?>" 
                                       placeholder="Enter PTR number"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                    <div class="flex flex-col sm:flex-row gap-4 justify-between items-center">
                        <div class="text-sm text-gray-600">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Make sure all required information is filled before saving.
                        </div>
                        <div class="flex space-x-3">
                            <a href="<?= base_url('admin/prescriptions') ?>" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 rounded-lg text-sm font-medium transition-colors duration-200">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Save Prescription
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
function updateEmptyState() {
    const tbody = document.querySelector('#itemsTable tbody');
    const emptyState = document.getElementById('emptyState');
    if (tbody.children.length === 0) {
        emptyState.style.display = 'block';
    } else {
        emptyState.style.display = 'none';
    }
}

document.getElementById('addRow').addEventListener('click', function(){
    const tbody = document.querySelector('#itemsTable tbody');
    const idx = tbody.children.length;
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-gray-50 transition-colors duration-150';
    tr.innerHTML = `
        <td class="p-3">
            <input name="items[${idx}][medicine_name]" placeholder="Medicine name" 
                   class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500"/>
        </td>
        <td class="p-3">
            <input name="items[${idx}][dosage]" placeholder="e.g., 500mg" 
                   class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500"/>
        </td>
        <td class="p-3">
            <input name="items[${idx}][frequency]" placeholder="e.g., 3x daily" 
                   class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500"/>
        </td>
        <td class="p-3">
            <input name="items[${idx}][duration]" placeholder="e.g., 7 days" 
                   class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500"/>
        </td>
        <td class="p-3">
            <input name="items[${idx}][instructions]" placeholder="Special instructions" 
                   class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500"/>
        </td>
        <td class="p-3">
            <button type="button" class="removeBtn inline-flex items-center px-2 py-1 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-medium rounded-md transition-colors duration-200 border border-red-200 hover:border-red-300">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Remove
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    updateEmptyState();
});

document.addEventListener('click', function(e){
    if (e.target && e.target.classList.contains('removeBtn')) {
        e.target.closest('tr').remove();
        updateEmptyState();
    }
});

// Patient selection handler for prefilling information
document.getElementById('patient_select').addEventListener('change', function(){
    const selectedOption = this.options[this.selectedIndex];
    
    // Clear fields first
    document.getElementById('patient_age').value = '';
    document.getElementById('patient_gender').value = '';
    document.getElementById('patient_address').value = '';
    
    // Prefill if patient is selected
    if (selectedOption.value) {
        const age = selectedOption.getAttribute('data-age');
        const gender = selectedOption.getAttribute('data-gender');
        const address = selectedOption.getAttribute('data-address');
        
        if (age) document.getElementById('patient_age').value = age;
        if (gender) document.getElementById('patient_gender').value = gender;
        if (address) document.getElementById('patient_address').value = address;
    }
});

// Initialize empty state
updateEmptyState();
</script>