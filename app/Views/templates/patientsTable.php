<?php if (function_exists('file_exists') && file_exists(FCPATH . 'css/flatpickr.min.css')): ?>
    <link rel="stylesheet" href="<?= base_url('css/flatpickr.min.css') ?>">
<?php else: ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<?php endif; ?>
<!-- Tailwind Patients Table -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
    <h1 class="font-bold text-2xl md:text-3xl text-black tracking-tight">Lists of Patients</h1>
    <?php if (in_array($user['user_type'], ['admin', 'staff'])): ?>
    <!-- Use explicit Tailwind-like classes as fallback to ensure visibility across themes -->
    <button id="showAddPatientFormBtn" class="lavender-btn bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-base rounded-xl px-7 py-2.5 transition">+ Add New Patient</button>
    <?php endif; ?>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="flex items-center gap-2 bg-green-100 text-green-800 rounded-lg px-4 py-3 mb-4 text-sm font-semibold">
        <i class="fas fa-check-circle"></i>
        <span><?= session()->getFlashdata('success') ?></span>
        <button type="button" class="ml-auto text-green-700 hover:text-green-900 focus:outline-none" onclick="this.parentElement.remove()">&times;</button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="flex items-center gap-2 bg-red-100 text-red-800 rounded-lg px-4 py-3 mb-4 text-sm font-semibold">
        <i class="fas fa-exclamation-circle"></i>
        <span>
        <?php 
        $errors = session()->getFlashdata('error');
        if (is_array($errors)) {
            foreach ($errors as $field => $error) {
                if (is_array($error)) {
                    foreach ($error as $err) {
                        echo esc($err) . '<br>';
                    }
                } else {
                    echo esc($error) . '<br>';
                }
            }
        } else {
            echo esc($errors);
        }
        ?>
        </span>
        <button type="button" class="ml-auto text-red-700 hover:text-red-900 focus:outline-none" onclick="this.parentElement.remove()">&times;</button>
    </div>
<?php endif; ?>

<!-- Desktop Table View -->
<div class="hidden lg:block overflow-x-auto mb-8">
    <table class="min-w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        <thead class="bg-white">
            <tr class="text-black font-extrabold text-base">
                <th class="px-8 py-4 text-left">Name</th>
                <th class="px-4 py-4 text-left">ID</th>
                <th class="px-4 py-4 text-left">Email</th>
                <th class="px-4 py-4 text-left">Phone number</th>
                <th class="px-4 py-4 text-left">Address</th>
                <th class="px-4 py-4 text-left">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($patients)): ?>
                <?php foreach ($patients as $patient): ?>
                <tr class="patient-row border-b last:border-b-0 hover:bg-indigo-50 transition cursor-pointer" data-patient='<?= json_encode([
                            "id" => $patient["id"],
                            "name" => $patient["name"],
                            "email" => $patient["email"],
                            "phone" => $patient["phone"],
                            "gender" => $patient["gender"],
                            "date_of_birth" => $patient["date_of_birth"],
                            "address" => $patient["address"],
                            "age" => $patient["age"],
                            "occupation" => $patient["occupation"],
                            "nationality" => $patient["nationality"]
                        ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                    <td class="min-w-[180px] px-8 py-5">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center font-bold text-lg text-indigo-400">
                                <?= strtoupper(substr($patient['name'], 0, 1)) ?>
                            </div>
                            <div>
                                <div class="font-extrabold text-black text-base"> <?= esc($patient['name']) ?> </div>
                            </div>
                        </div>
                    </td>
                    <td class="font-bold text-black px-4 py-5"> <?= esc($patient['id']) ?> </td>
                    <td class="text-black px-4 py-5"> <?= esc($patient['email']) ?> </td>
                    <td class="text-black px-4 py-5"> <?= esc($patient['phone']) ?> </td>
                    <td class="text-black min-w-[140px] px-4 py-5"> <?= esc($patient['address']) ?> </td>
                    <td class="px-4 py-5">
                        <?php 
                        $status = $patient['status'] ?? 'active';
                        $statusClass = $status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
                        $statusText = ucfirst($status);
                        ?>
                        <span class="inline-block font-semibold rounded-md px-3 py-1 text-xs <?= $statusClass ?>">
                            <?= $statusText ?>
                        </span>
                    </td>
                    
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center py-12 text-black font-semibold">No patients found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Mobile Card View -->
<div class="lg:hidden space-y-4 mb-8">
    <?php if (!empty($patients)): ?>
        <?php foreach ($patients as $patient): ?>
        <div class="bg-white rounded-2xl shadow-xl p-4 border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center font-bold text-lg text-indigo-400">
                        <?= strtoupper(substr($patient['name'], 0, 1)) ?>
                    </div>
                    <div>
                        <div class="font-bold text-black text-base"><?= esc($patient['name']) ?></div>
                        <div class="text-sm text-black">ID: <?= esc($patient['id']) ?></div>
                    </div>
                </div>
                <?php 
                $status = $patient['status'] ?? 'active';
                $statusClass = $status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
                $statusText = ucfirst($status);
                ?>
                <span class="inline-block font-semibold rounded-md px-2 py-1 text-xs <?= $statusClass ?>">
                    <?= $statusText ?>
                </span>
            </div>
            
            <div class="space-y-2 mb-4">
                <div class="flex items-center gap-2 text-sm">
                    <i class="fas fa-envelope text-gray-400 w-4"></i>
                    <span class="text-black"><?= esc($patient['email']) ?></span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <i class="fas fa-phone text-gray-400 w-4"></i>
                    <span class="text-black"><?= esc($patient['phone']) ?></span>
                </div>
                <div class="flex items-start gap-2 text-sm">
                    <i class="fas fa-map-marker-alt text-gray-400 w-4 mt-0.5"></i>
                    <span class="text-black"><?= esc($patient['address']) ?></span>
                </div>
            </div>
            
            <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                <a href="#" title="View" class="showViewPatientPanelBtn p-2 text-indigo-400 hover:bg-indigo-50 rounded-lg transition" data-patient='<?= json_encode([
                    "id" => $patient["id"],
                    "name" => $patient["name"],
                    "email" => $patient["email"],
                    "phone" => $patient["phone"],
                    "gender" => $patient["gender"],
                    "date_of_birth" => $patient["date_of_birth"],
                    "address" => $patient["address"],
                    "age" => $patient["age"],
                    "occupation" => $patient["occupation"],
                    "nationality" => $patient["nationality"]
                ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                    <i class="fas fa-eye"></i>
                </a>
                <a href="#" title="Delete" class="p-2 text-red-400 hover:bg-red-50 rounded-lg transition">
                    <i class="fas fa-trash"></i>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="text-center py-12 text-black font-semibold bg-white rounded-2xl shadow-xl">
            <i class="fas fa-users text-4xl mb-4 block"></i>
            No patients found.
        </div>
    <?php endif; ?>
</div>

<!-- Add Patient Slide-in Panel -->
<div id="addPatientPanel" class="slide-in-panel p-4 lg:p-6 flex flex-col gap-4">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-700 capitalize dark:text-white">Add New Patient</h2>
    <button class="close-btn text-gray-500 hover:text-gray-700 text-2xl font-bold w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors" id="closeAddPatientPanel" aria-label="Close">&times;</button>
    </div>
    
    <div>
        <form action="<?= base_url($user['user_type'] . '/patients/store') ?>" method="post" novalidate>
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-black text-sm font-medium" for="name">Full Name</label>
                    <input id="name" name="name" type="text" required class="block w-full px-3 py-2 mt-1 text-black bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:ring-blue-300 focus:ring-opacity-40 focus:outline-none focus:ring">
                </div>

                <div>
                    <label class="text-black text-sm font-medium" for="email">Email Address</label>
                    <input id="email" name="email" type="email" required class="block w-full px-3 py-2 mt-1 text-black bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:ring-blue-300 focus:ring-opacity-40 focus:outline-none focus:ring">
                </div>

                <div>
                    <label class="text-black text-sm font-medium" for="phone">Phone Number</label>
                    <input id="phone" name="phone" type="text" required pattern="[0-9+\-() ]+" class="block w-full px-3 py-2 mt-1 text-black bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:ring-blue-300 focus:ring-opacity-40 focus:outline-none focus:ring">
                </div>

                <div>
                    <label class="text-black text-sm font-medium" for="gender">Gender</label>
                    <select id="gender" name="gender" required class="block w-full px-3 py-2 mt-1 text-black bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:ring-blue-300 focus:ring-opacity-40 focus:outline-none focus:ring">
                        <option value="">Select Gender</option>
                        <option value="Male">👨 Male</option>
                        <option value="Female">👩 Female</option>
                        <option value="Other">⚧ Other</option>
                    </select>
                </div>

                <div>
                    <label class="text-black text-sm font-medium" for="date_of_birth">Date of Birth</label>
                    <input id="date_of_birth" name="date_of_birth" type="text" required readonly class="modern-date-input block w-full px-3 py-2 mt-1 text-black bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:ring-blue-300 focus:ring-opacity-40 focus:outline-none focus:ring">
                </div>

                <div>
                    <label class="text-black text-sm font-medium" for="calculated_age">Age (Calculated)</label>
                    <input id="calculated_age" type="text" readonly class="block w-full px-3 py-2 mt-1 text-black bg-gray-50 border border-gray-200 rounded-md cursor-not-allowed">
                    <input type="hidden" id="age" name="age" value="">
                </div>

                <div>
                    <label class="text-black text-sm font-medium" for="occupation">Occupation</label>
                    <input id="occupation" name="occupation" type="text" class="block w-full px-3 py-2 mt-1 text-black bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:ring-blue-300 focus:ring-opacity-40 focus:outline-none focus:ring">
                </div>

                <div>
                    <label class="text-black text-sm font-medium" for="nationality">Nationality</label>
                    <input id="nationality" name="nationality" type="text" class="block w-full px-3 py-2 mt-1 text-black bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:ring-blue-300 focus:ring-opacity-40 focus:outline-none focus:ring">
                </div>

                <div class="sm:col-span-2">
                    <label class="text-black text-sm font-medium" for="address">Address</label>
                    <input id="address" name="address" type="text" required class="block w-full px-3 py-2 mt-1 text-black bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:ring-blue-300 focus:ring-opacity-40 focus:outline-none focus:ring">
                </div>
            </div>

            <div class="flex justify-end mt-4">
                <button type="submit" onclick="debugFormData()" class="px-6 py-2 leading-5 text-white transition-colors duration-300 transform bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:bg-blue-700 font-medium">Add Patient</button>
            </div>
        </form>
    </div>
</div>

<!-- View Patient Slide-in Panel -->
<div id="viewPatientPanel" class="slide-in-panel p-4 lg:p-6 flex flex-col gap-4">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-700 capitalize">Patient Info</h2>
        <div class="flex items-center gap-2">
            <button id="deletePatientBtn" class="px-3 py-1.5 text-sm bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition" title="Delete Patient">
                <i class="fas fa-trash mr-1"></i> Delete
            </button>
        <button class="close-btn text-gray-500 hover:text-gray-700 text-2xl font-bold w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors" id="closeViewPatientPanel" aria-label="Close">&times;</button>
        </div>
    </div>
    
    <!-- Patient Header Section -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-4 relative">
        <div class="flex flex-col items-center text-center gap-4">
            <div class="flex flex-col items-center gap-2">
                <div class="flex gap-2">
                    <button id="showNewActionPanelBtn" class="bg-indigo-50 rounded-full w-8 h-8 flex items-center justify-center text-indigo-400 hover:bg-indigo-100 transition" title="Medical History">
                    <i class="fas fa-plus text-sm"></i>
                </button>
                    <button id="showPatientRecordsBtn" class="bg-green-50 rounded-full w-8 h-8 flex items-center justify-center text-green-400 hover:bg-green-100 transition" title="View Records">
                        <i class="fas fa-folder-open text-sm"></i>
                    </button>
                    <button id="showDentalChartBtn" class="bg-blue-50 rounded-full w-8 h-8 flex items-center justify-center text-blue-400 hover:bg-blue-100 transition" title="View Latest Dental Chart">
                        <i class="fas fa-tooth text-sm"></i>
                    </button>
                </div>
                <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center text-2xl text-indigo-300">
                    <i class="fas fa-user"></i>
                </div>
                <button class="bg-indigo-50 rounded-full w-8 h-8 flex items-center justify-center text-indigo-400 hover:bg-indigo-100 transition">
                    <i class="fas fa-file-invoice-dollar text-sm"></i>
                </button>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-center gap-2 mb-1">
                    <span class="font-semibold text-lg text-black truncate" id="view-patient-name"></span>
                    <i class="fas fa-pen showUpdatePatientPanelBtn text-indigo-300 cursor-pointer hover:text-indigo-500 transition" data-patient-id=""></i>
                </div>
                <div class="text-black text-sm mb-2" id="view-patient-email"></div>
                <div class="flex flex-wrap gap-4 justify-center text-xs text-black">
                    <div class="flex items-center gap-1">
                        <i class="far fa-clipboard"></i>
                        <span>Treatments: <b>0</b></span>
                    </div>
                    <div class="flex items-center gap-1">
                        <i class="fas fa-coins"></i>
                        <span>Spent: <b>$0</b></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Patient Details Section -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
        <h3 class="text-sm font-semibold text-black mb-3">Contact Information</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1">
                <div class="text-xs text-black uppercase tracking-wide">Phone</div>
                <div class="font-medium text-black text-sm flex items-center gap-2">
                    <i class="fas fa-phone text-indigo-400 w-4"></i>
                    <span id="view-patient-phone"></span>
                </div>
            </div>
            <div class="space-y-1">
                <div class="text-xs text-black uppercase tracking-wide">Gender</div>
                <div class="font-medium text-black text-sm flex items-center gap-2">
                    <i class="fas fa-venus-mars text-indigo-400 w-4"></i>
                    <span id="view-patient-gender"></span>
                </div>
            </div>
            <div class="space-y-1">
                <div class="text-xs text-black uppercase tracking-wide">Date of Birth</div>
                <div class="font-medium text-black text-sm flex items-center gap-2">
                    <i class="fas fa-birthday-cake text-indigo-400 w-4"></i>
                    <span id="view-patient-date-of-birth"></span>
                </div>
            </div>
            <div class="space-y-1 sm:col-span-2">
                <div class="text-xs text-black uppercase tracking-wide">Full Address</div>
                <div class="font-medium text-black text-sm flex items-start gap-2">
                    <i class="fas fa-map-marker-alt text-indigo-400 w-4 mt-0.5"></i>
                    <span id="view-patient-address"></span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabs Section -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="bg-indigo-50 p-1 flex gap-0 overflow-x-auto">
            <button class="view-patient-tab-btn active flex-1 bg-white rounded-lg font-semibold text-black py-3 text-sm shadow-sm whitespace-nowrap transition-all" data-tab="treatments">
                <i class="fas fa-notes-medical mr-2"></i> Treatments
            </button>
            <button class="view-patient-tab-btn flex-1 font-semibold text-indigo-500 py-3 text-sm whitespace-nowrap transition-all" data-tab="appointments">
                <i class="far fa-calendar-alt mr-2"></i> Appointments
            </button>
            <button class="view-patient-tab-btn flex-1 font-semibold text-indigo-500 py-3 text-sm whitespace-nowrap transition-all" data-tab="bills">
                <i class="fas fa-file-invoice-dollar mr-2"></i> Patient Bills
            </button>
        </div>
        
        <!-- Tab Content -->
        <div class="p-4">
            <div id="view-patient-tab-content-treatments" class="view-patient-tab-content bg-gray-50 rounded-lg min-h-[120px] flex items-center justify-center text-black text-sm">
                <div class="text-center">
                    <i class="fas fa-notes-medical text-2xl mb-2 block text-gray-400"></i>
                    <span>No treatments recorded yet</span>
                </div>
            </div>
            <div id="view-patient-tab-content-appointments" class="view-patient-tab-content hidden bg-gray-50 rounded-lg min-h-[120px] flex items-center justify-center text-black text-sm">
                <div class="text-center">
                    <i class="far fa-calendar-alt text-2xl mb-2 block text-gray-400"></i>
                    <span>No appointments found</span>
                </div>
            </div>
            <div id="view-patient-tab-content-bills" class="view-patient-tab-content hidden bg-gray-50 rounded-lg min-h-[120px] flex items-center justify-center text-black text-sm">
                <div class="text-center">
                    <i class="fas fa-file-invoice-dollar text-2xl mb-2 block text-gray-400"></i>
                    <span>No bills available</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Patient Slide-in Panel -->
<div id="updatePatientPanel" class="slide-in-panel p-4 lg:p-6 flex flex-col gap-4">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-700 capitalize">Update Patient</h2>
        <button class="close-btn text-gray-500 hover:text-gray-700 text-2xl font-bold w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors" id="closeUpdatePatientPanel" aria-label="Close">&times;</button>
    </div>
    <form class="update-patient-form flex flex-col gap-4" id="updatePatientForm" method="post" action="">
        <?= csrf_field() ?>
        <input type="hidden" id="update-patient-id" name="patient_id">
        <div class="flex flex-col items-center gap-2 mb-4">
            <label for="update-patient-photo" class="cursor-pointer">
                <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center text-2xl text-indigo-300">
                    <i class="fas fa-camera"></i>
                </div>
            </label>
            <input type="file" id="update-patient-photo" class="hidden">
        </div>
        
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="text-black text-sm font-medium" for="update-patient-name">Full Name</label>
                <input type="text" id="update-patient-name" name="name" placeholder="Full Name" required class="block w-full px-3 py-2 mt-1 text-black bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:ring-blue-300 focus:ring-opacity-40 focus:outline-none focus:ring" />
            </div>
            
            <div>
                <label class="text-black text-sm font-medium" for="update-patient-email">Email Address</label>
                <input type="email" id="update-patient-email" name="email" placeholder="Email Address" required class="block w-full px-3 py-2 mt-1 text-black bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:ring-blue-300 focus:ring-opacity-40 focus:outline-none focus:ring" />
            </div>
            
            <div>
                <label class="text-black text-sm font-medium" for="update-patient-phone">Phone Number</label>
                <input type="text" id="update-patient-phone" name="phone" placeholder="Phone Number" required class="block w-full px-3 py-2 mt-1 text-black bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:ring-blue-300 focus:ring-opacity-40 focus:outline-none focus:ring" />
            </div>
            
            <div>
                <label class="text-black text-sm font-medium" for="update-patient-gender">Gender</label>
                <select id="update-patient-gender" name="gender" class="block w-full px-3 py-2 mt-1 text-black bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:ring-blue-300 focus:ring-opacity-40 focus:outline-none focus:ring" required>
                    <option value="">Select Gender</option>
                    <option value="Male">👨 Male</option>
                    <option value="Female">👩 Female</option>
                    <option value="Other">⚧ Other</option>
                </select>
            </div>
            
            <div>
                <label class="text-black text-sm font-medium" for="update-patient-date-of-birth">Date of Birth</label>
                <input type="text" id="update-patient-date-of-birth" name="date_of_birth" class="modern-date-input block w-full px-3 py-2 mt-1 text-black bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:ring-blue-300 focus:ring-opacity-40 focus:outline-none focus:ring" placeholder="Select Date" required />
            </div>
            
            <div>
                <label class="text-black text-sm font-medium" for="update-patient-age">Age</label>
                <input type="number" id="update-patient-age" name="age" placeholder="Age" min="0" max="150" class="block w-full px-3 py-2 mt-1 text-black bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:ring-blue-300 focus:ring-opacity-40 focus:outline-none focus:ring" />
            </div>
            
            <div>
                <label class="text-black text-sm font-medium" for="update-patient-occupation">Occupation</label>
                <input type="text" id="update-patient-occupation" name="occupation" placeholder="Occupation" class="block w-full px-3 py-2 mt-1 text-black bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:ring-blue-300 focus:ring-opacity-40 focus:outline-none focus:ring" />
            </div>
            
            <div>
                <label class="text-black text-sm font-medium" for="update-patient-nationality">Nationality</label>
                <input type="text" id="update-patient-nationality" name="nationality" placeholder="Nationality" class="block w-full px-3 py-2 mt-1 text-black bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:ring-blue-300 focus:ring-opacity-40 focus:outline-none focus:ring" />
            </div>
            
            <div class="sm:col-span-2">
                <label class="text-black text-sm font-medium" for="update-patient-address">Address</label>
                <input type="text" id="update-patient-address" name="address" placeholder="Address" required class="block w-full px-3 py-2 mt-1 text-black bg-white border border-gray-200 rounded-md focus:border-blue-400 focus:ring-blue-300 focus:ring-opacity-40 focus:outline-none focus:ring" />
            </div>
        </div>
        
        <div class="flex justify-end mt-4">
            <button type="submit" class="px-6 py-2 leading-5 text-white transition-colors duration-300 transform bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:bg-blue-700 font-medium">Save Changes</button>
        </div>
    </form>
</div>

<?php if (function_exists('file_exists') && file_exists(FCPATH . 'js/flatpickr.min.js')): ?>
    <script src="<?= base_url('js/flatpickr.min.js') ?>"></script>
<?php else: ?>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<?php endif; ?>
<!-- Three.js libraries for 3D Dental Model -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
<!-- 3D Dental Viewer styles -->
<link rel="stylesheet" href="<?= base_url('css/dental-3d-viewer.css') ?>">
<!-- 3D Dental Viewer component -->
<script src="<?= base_url('js/dental-3d-viewer.js') ?>"></script>
<!-- Externalized patient panel JS -->
<script src="<?= base_url('js/patientsTable.js') ?>"></script>

<!-- Debug CSS for slide-in-panel -->
<style>
/* Left slide-in panel */
.slide-in-panel-left {
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  width: 100vw;
  max-width: 28rem;
  background: #ffffff;
  box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
  z-index: 50;
  transform: translateX(-100%);
  transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
  overflow-y: auto;
  -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
}
.slide-in-panel-left.active {
  transform: translateX(0);
}

/* Make slide-in panels wider */
  .slide-in-panel {
  position: fixed;
  top: 0;
  right: 0;
  height: 100vh;
  width: 100vw;
  max-width: 35rem; /* Increased from 28rem to 35rem */
  background: #ffffff;
  box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
  z-index: 50;
  transform: translateX(100%);
  transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
  overflow-y: auto;
  -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
}

/* Update patient panel should be on top */
#updatePatientPanel {
  z-index: 60;
}
.slide-in-panel.active {
  transform: translateX(0);
}

:root { --panel-width: 35rem; } /* Updated panel width */
@media (min-width: 1024px) {
  /* When action panel is open, shift the view panel left by panel width */
  #viewPatientPanel.shifted {
    right: var(--panel-width);
  }
}

/* Mobile-friendly close button */
.close-btn {
  cursor: pointer;
  transition: all 0.2s ease;
  -webkit-tap-highlight-color: transparent;
}
.close-btn:hover {
  background-color: rgba(0,0,0,0.05);
}
.close-btn:active {
  transform: scale(0.95);
}

/* Touch-friendly buttons */
button, a {
  -webkit-tap-highlight-color: transparent;
  touch-action: manipulation;
}

/* Mobile-friendly form inputs */
input, select, textarea {
  font-size: 16px; /* Prevents zoom on iOS */
  -webkit-appearance: none;
  border-radius: 0.375rem;
}

/* Radio buttons and checkboxes styling */
input[type="radio"], input[type="checkbox"] {
  -webkit-appearance: auto;
  appearance: auto;
  width: 16px;
  height: 16px;
  margin: 0;
  cursor: pointer;
}

/* Ensure disabled radio buttons and checkboxes remain visible */
input[type="radio"][disabled], input[type="checkbox"][disabled] {
  opacity: 1;
  filter: none;
  cursor: not-allowed;
}

/* Custom radio button styling */
.form-radio {
  -webkit-appearance: auto;
  appearance: auto;
  width: 16px;
  height: 16px;
  border: 2px solid #d1d5db;
  border-radius: 50%;
  background-color: white;
  cursor: pointer;
  position: relative;
}

.form-radio:checked {
  background-color: #3b82f6;
  border-color: #3b82f6;
}

.form-radio:checked::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 6px;
  height: 6px;
  background-color: white;
  border-radius: 50%;
}

/* Custom checkbox styling */
.form-checkbox {
  -webkit-appearance: auto;
  appearance: auto;
  width: 16px;
  height: 16px;
  border: 2px solid #d1d5db;
  border-radius: 4px;
  background-color: white;
  cursor: pointer;
  position: relative;
}

.form-checkbox:checked {
  background-color: #3b82f6;
  border-color: #3b82f6;
}

.form-checkbox:checked::after {
  content: '✓';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: white;
  font-size: 12px;
  font-weight: bold;
}

/* Mobile-friendly table */
@media (max-width: 1023px) {
  .table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
}

/* Mobile-friendly cards */
@media (max-width: 1023px) {
  .patient-card {
    margin-bottom: 1rem;
    border-radius: 0.75rem;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
  }
}

/* Prevent body scroll when panel is open on mobile */
body.panel-open {
  overflow: hidden;
  position: fixed;
  width: 100%;
}

/* Popup used by Latest Dental Chart viewer */
.treatment-popup {
  position: absolute;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
  width: 280px;
  z-index: 70;
  display: none; /* shown on click */
}
.treatment-popup-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.5rem 0.75rem;
  border-bottom: 1px solid #e5e7eb;
}
.treatment-popup-title { font-weight: 600; font-size: 0.9rem; }
.treatment-popup-close { color: #6b7280; }
.treatment-popup-content { padding: 0.75rem; font-size: 0.85rem; color: #111827; }
</style>

<!-- Dental Chart Slide-in Panel (view latest) -->
<div id="dentalChartPanel" class="slide-in-panel p-4 lg:p-6 flex flex-col gap-4">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-700 capitalize">Latest Dental Chart</h2>
        <button class="close-btn text-gray-500 hover:text-gray-700 text-2xl font-bold w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors" id="closeDentalChartPanel" aria-label="Close">&times;</button>
    </div>
    <div id="dental-chart-content" class="bg-white rounded-lg shadow-sm p-4 min-h-[200px]">
        <div class="text-center text-sm text-gray-600">
            <i class="fas fa-spinner fa-spin text-2xl mb-2 block text-gray-400"></i>
            Loading dental chart...
        </div>

        <!-- 3D viewer container (hidden until we have data) -->
        <div class="mt-4">
            <div class="dental-3d-viewer-container">
                <div id="dentalChart3DViewer" class="dental-3d-viewer" style="height: 320px;">
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
    </div>
    <div class="text-xs text-gray-500 text-center">Charts are read-only here. Go to records to edit.</div>
 </div>

<!-- New Action Slide-in Panel (slides from right) -->
<div id="newActionPanel" class="slide-in-panel p-4 lg:p-6 flex flex-col gap-4">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-700 capitalize">Patient Medical History</h2>
        <div class="flex gap-2">
            <button type="button" onclick="clearMedicalHistoryForm()" class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-lg transition-colors">
                <i class="fas fa-eraser mr-1"></i>Clear Form
            </button>
        <button class="close-btn text-gray-500 hover:text-gray-700 text-2xl font-bold w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors" id="closeNewActionPanel" aria-label="Close">&times;</button>
        </div>
    </div>
    
    <!-- Panel Content -->
    <div class="bg-white rounded-lg shadow-sm p-4">
        <div class="flex flex-col gap-4">
            <!-- Dental History Section -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">Dental History</h3>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label for="previous_dentist" class="block text-sm font-medium text-gray-700 mb-2">Previous Dentist <span class="text-gray-500 font-normal">(Optional)</span>:</label>
                        <input type="text" id="previous_dentist" name="previous_dentist" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                               placeholder="Enter previous dentist name (leave blank if none)" 
                               value="<?= esc($patient['previous_dentist'] ?? old('previous_dentist')) ?>"
                               onchange="updateHiddenField('previous_dentist', this.value)">
                    </div>
                    <div>
                        <label for="last_dental_visit" class="block text-sm font-medium text-gray-700 mb-2">Last Dental Visit Date <span class="text-gray-500 font-normal">(Optional)</span>:</label>
                        <input type="date" id="last_dental_visit" name="last_dental_visit" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                               value="<?= esc($patient['last_dental_visit'] ?? old('last_dental_visit')) ?>"
                               onchange="updateHiddenField('last_dental_visit', this.value)">
                    </div>
                </div>
            </div>

            <!-- Medical History Section -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">Medical History <span class="text-gray-500 font-normal text-sm">(All fields optional)</span></h3>
                
                <!-- Optional Notice -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mr-2 mt-0.5"></i>
                        <p class="text-xs text-blue-700">
                            <span class="font-medium">For Staff Convenience:</span> All medical history fields are optional. You can leave any field blank if the patient doesn't have the information, doesn't know the answer, or prefers not to answer.
                        </p>
                    </div>
                </div>

                <!-- Physician Information -->
                <div class="grid grid-cols-1 gap-3 mb-4">
                    <div>
                        <label for="physician_name" class="block text-sm font-medium text-gray-700 mb-1">Name of Physician <span class="text-gray-500 font-normal">(Optional)</span>:</label>
                        <input type="text" id="physician_name" name="physician_name" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                               placeholder="Enter physician name (leave blank if none)" 
                               value="<?= esc($patient['physician_name'] ?? old('physician_name')) ?>"
                               onchange="updateHiddenField('physician_name', this.value)">
                    </div>
                    <div>
                        <label for="physician_specialty" class="block text-sm font-medium text-gray-700 mb-1">Specialty <span class="text-gray-500 font-normal">(Optional)</span>:</label>
                        <input type="text" id="physician_specialty" name="physician_specialty" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                               placeholder="Enter specialty (leave blank if not applicable)" 
                               value="<?= esc($patient['physician_specialty'] ?? old('physician_specialty')) ?>"
                               onchange="updateHiddenField('physician_specialty', this.value)">
                    </div>
                    <div>
                        <label for="physician_phone" class="block text-sm font-medium text-gray-700 mb-1">Office Telephone Number <span class="text-gray-500 font-normal">(Optional)</span>:</label>
                        <input type="tel" id="physician_phone" name="physician_phone" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                               placeholder="Enter office phone number (leave blank if unknown)" 
                               value="<?= esc($patient['physician_phone'] ?? old('physician_phone')) ?>"
                               onchange="updateHiddenField('physician_phone', this.value)">
                    </div>
                    <div>
                        <label for="physician_address" class="block text-sm font-medium text-gray-700 mb-1">Office Address <span class="text-gray-500 font-normal">(Optional)</span>:</label>
                        <textarea id="physician_address" name="physician_address" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                  placeholder="Enter office address (leave blank if unknown)"
                                  onchange="updateHiddenField('physician_address', this.value)"><?= esc($patient['physician_address'] ?? old('physician_address')) ?></textarea>
                    </div>
                </div>

                <!-- General Health Questions -->
                <div class="space-y-3 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Are you in good health? <span class="text-gray-500 font-normal">(Optional)</span></label>
                        <div class="flex space-x-3">
                            <label class="inline-flex items-center">
                                <input type="radio" name="good_health" value="yes" class="form-radio text-blue-600" onchange="updateHiddenFieldRadio('good_health', this.value)">
                                <span class="ml-1 text-xs">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="good_health" value="no" class="form-radio text-blue-600" onchange="updateHiddenFieldRadio('good_health', this.value)">
                                <span class="ml-1 text-xs">No</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="good_health" value="" class="form-radio text-blue-600" onchange="updateHiddenFieldRadio('good_health', this.value)">
                                <span class="ml-1 text-xs">Skip</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Are you under medical treatment now? <span class="text-gray-500 font-normal">(Optional)</span></label>
                        <div class="flex space-x-3">
                            <label class="inline-flex items-center">
                                <input type="radio" name="under_treatment" value="yes" class="form-radio text-blue-600" onchange="updateHiddenFieldRadio('under_treatment', this.value); toggleTreatmentCondition()">
                                <span class="ml-1 text-xs">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="under_treatment" value="no" class="form-radio text-blue-600" onchange="updateHiddenFieldRadio('under_treatment', this.value); toggleTreatmentCondition()">
                                <span class="ml-1 text-xs">No</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="under_treatment" value="" class="form-radio text-blue-600" onchange="updateHiddenFieldRadio('under_treatment', this.value); toggleTreatmentCondition()">
                                <span class="ml-1 text-xs">Skip</span>
                            </label>
                        </div>
                        <div id="treatment_condition_div" class="hidden mt-2">
                            <input type="text" id="treatment_condition" name="treatment_condition" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                   placeholder="What condition is being treated?" 
                                   value="<?= esc($patient['treatment_condition'] ?? old('treatment_condition')) ?>"
                                   onchange="updateHiddenField('treatment_condition', this.value)">
                        </div>
                    </div>
                </div>

                <!-- Serious Illness and Hospitalization -->
                <div class="space-y-3 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Have you ever had a serious illness or surgical operation?</label>
                        <div class="flex space-x-3 mb-2">
                            <label class="inline-flex items-center">
                                <input type="radio" name="serious_illness" value="yes" class="form-radio text-blue-600" onchange="updateHiddenFieldRadio('serious_illness', this.value); toggleIllnessDetails()">
                                <span class="ml-1 text-xs">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="serious_illness" value="no" class="form-radio text-blue-600" onchange="updateHiddenFieldRadio('serious_illness', this.value); toggleIllnessDetails()">
                                <span class="ml-1 text-xs">No</span>
                            </label>
                        </div>
                        <div id="illness_details_div" class="hidden">
                            <input type="text" id="illness_details" name="illness_details" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                   placeholder="If yes, what illness or operation?" 
                                   value="<?= esc($patient['illness_details'] ?? old('illness_details')) ?>"
                                   onchange="updateHiddenField('illness_details', this.value)">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Have you ever been hospitalized?</label>
                        <div class="flex space-x-3 mb-2">
                            <label class="inline-flex items-center">
                                <input type="radio" name="hospitalized" value="yes" class="form-radio text-blue-600" onchange="updateHiddenFieldRadio('hospitalized', this.value); toggleHospitalizationDetails()">
                                <span class="ml-1 text-xs">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="hospitalized" value="no" class="form-radio text-blue-600" onchange="updateHiddenFieldRadio('hospitalized', this.value); toggleHospitalizationDetails()">
                                <span class="ml-1 text-xs">No</span>
                            </label>
                        </div>
                        <div id="hospitalization_details_div" class="hidden grid grid-cols-1 gap-2">
                            <input type="text" name="hospitalization_where" placeholder="Where?" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                   value="<?= esc($patient['hospitalization_where'] ?? old('hospitalization_where')) ?>"
                                   onchange="updateHiddenField('hospitalization_where', this.value)">
                            <input type="text" name="hospitalization_when" placeholder="When?" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                   value="<?= esc($patient['hospitalization_when'] ?? old('hospitalization_when')) ?>"
                                   onchange="updateHiddenField('hospitalization_when', this.value)">
                            <input type="text" name="hospitalization_why" placeholder="Why?" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                   value="<?= esc($patient['hospitalization_why'] ?? old('hospitalization_why')) ?>"
                                   onchange="updateHiddenField('hospitalization_why', this.value)">
                        </div>
                    </div>
                </div>

                <!-- Tobacco Use and Blood Pressure -->
                <div class="grid grid-cols-1 gap-3 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Do you use tobacco products? <span class="text-gray-500 font-normal">(Optional)</span></label>
                        <div class="flex space-x-3">
                            <label class="inline-flex items-center">
                                <input type="radio" name="tobacco_use" value="yes" class="form-radio text-blue-600" onchange="updateHiddenFieldRadio('tobacco_use', this.value)">
                                <span class="ml-1 text-xs">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="tobacco_use" value="no" class="form-radio text-blue-600" onchange="updateHiddenFieldRadio('tobacco_use', this.value)">
                                <span class="ml-1 text-xs">No</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="tobacco_use" value="" class="form-radio text-blue-600" onchange="updateHiddenFieldRadio('tobacco_use', this.value)">
                                <span class="ml-1 text-xs">Skip</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label for="blood_pressure" class="block text-sm font-medium text-gray-700 mb-1">Blood Pressure (mmHg) <span class="text-gray-500 font-normal">(Optional)</span>:</label>
                        <input type="text" id="blood_pressure" name="blood_pressure" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                               placeholder="e.g., 120/80 (leave blank if unknown)" 
                               value="<?= esc($patient['blood_pressure'] ?? old('blood_pressure')) ?>"
                               onchange="updateHiddenField('blood_pressure', this.value)">
                    </div>
                </div>

                <!-- Allergies -->
                <div class="mb-4">
                    <label for="allergies" class="block text-sm font-medium text-gray-700 mb-1">Allergies <span class="text-gray-500 font-normal">(Optional)</span>:</label>
                    <textarea id="allergies" name="allergies" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                              placeholder="Specify any allergies (leave blank if none)"
                              onchange="updateHiddenField('allergies', this.value)"><?= esc($patient['allergies'] ?? old('allergies')) ?></textarea>
                </div>
            </div>

            <!-- For Women Only Section -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">For Women Only <span class="text-gray-500 font-normal text-sm">(All fields optional)</span></h3>
                <div class="grid grid-cols-1 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Are you pregnant? <span class="text-gray-500 font-normal">(Optional)</span></label>
                        <div class="flex space-x-3">
                            <label class="inline-flex items-center">
                                <input type="radio" name="pregnant" value="yes" class="form-radio text-blue-600" onchange="updateHiddenFieldRadio('pregnant', this.value)">
                                <span class="ml-1 text-xs">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="pregnant" value="no" class="form-radio text-blue-600" onchange="updateHiddenFieldRadio('pregnant', this.value)">
                                <span class="ml-1 text-xs">No</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="pregnant" value="na" class="form-radio text-blue-600" onchange="updateHiddenFieldRadio('pregnant', this.value)">
                                <span class="ml-1 text-xs">N/A</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Are you nursing? <span class="text-gray-500 font-normal">(Optional)</span></label>
                        <div class="flex space-x-3">
                            <label class="inline-flex items-center">
                                <input type="radio" name="nursing" value="yes" class="form-radio text-blue-600" onchange="updateHiddenFieldRadio('nursing', this.value)">
                                <span class="ml-1 text-xs">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="nursing" value="no" class="form-radio text-blue-600" onchange="updateHiddenFieldRadio('nursing', this.value)">
                                <span class="ml-1 text-xs">No</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="nursing" value="na" class="form-radio text-blue-600" onchange="updateHiddenFieldRadio('nursing', this.value)">
                                <span class="ml-1 text-xs">N/A</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Are you taking birth control pills? <span class="text-gray-500 font-normal">(Optional)</span></label>
                        <div class="flex space-x-3">
                            <label class="inline-flex items-center">
                                <input type="radio" name="birth_control" value="yes" class="form-radio text-blue-600" onchange="updateHiddenFieldRadio('birth_control', this.value)">
                                <span class="ml-1 text-xs">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="birth_control" value="no" class="form-radio text-blue-600" onchange="updateHiddenFieldRadio('birth_control', this.value)">
                                <span class="ml-1 text-xs">No</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="birth_control" value="na" class="form-radio text-blue-600" onchange="updateHiddenFieldRadio('birth_control', this.value)">
                                <span class="ml-1 text-xs">N/A</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medical Conditions Section -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">Medical Conditions <span class="text-gray-500 font-normal text-sm">(All optional)</span></h3>
                <p class="text-xs text-gray-600 mb-4">Do you have or have you ever had any of the following? (Check all that apply - leave blank if none or unknown)</p>
                <div class="grid grid-cols-1 gap-2 max-h-60 overflow-y-auto">
                    <?php
                    $medicalConditions = [
                        'high_blood_pressure' => 'High blood pressure',
                        'low_blood_pressure' => 'Low blood pressure',
                        'epilepsy' => 'Epilepsy/Convulsion',
                        'aids_hiv' => 'AIDS or HIV infection',
                        'std' => 'Sexually transmitted disease',
                        'stomach_ulcers' => 'Stomach trouble/Ulcers',
                        'fainting' => 'Fainting Seizure',
                        'weight_loss' => 'Rapid weight loss',
                        'radiation_therapy' => 'Radiation Therapy',
                        'joint_replacement' => 'Joint replacement/implant',
                        'heart_surgery' => 'Heart surgery',
                        'heart_attack' => 'Heart attack',
                        'thyroid_problem' => 'Thyroid problem',
                        'heart_disease' => 'Heart disease',
                        'heart_murmur' => 'Heart murmur',
                        'hepatitis_liver' => 'Hepatitis/Liver disease',
                        'rheumatic_fever' => 'Rheumatic fever',
                        'hay_fever' => 'Hay fever/Allergies',
                        'respiratory_problem' => 'Respiratory problem',
                        'hepatitis_jaundice' => 'Hepatitis/Jaundice',
                        'tuberculosis' => 'Tuberculosis',
                        'swollen_ankles' => 'Swollen ankles',
                        'kidney_disease' => 'Kidney disease',
                        'diabetes' => 'Diabetes',
                        'chest_pain' => 'Chest pain',
                        'stroke' => 'Stroke',
                        'cancer_tumors' => 'Cancer/Tumors',
                        'anemia' => 'Anemia',
                        'angina' => 'Angina',
                        'asthma' => 'Asthma',
                        'emphysema' => 'Emphysema',
                        'bleeding_problem' => 'Bleeding problem',
                        'blood_disease' => 'Blood disease',
                        'head_injuries' => 'Head injuries',
                        'arthritis' => 'Arthritis/Rheumatism'
                    ];
                    
                    foreach ($medicalConditions as $key => $condition): ?>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="medical_conditions[]" value="<?= $key ?>" class="form-checkbox text-blue-600 rounded" onchange="updateHiddenFieldCheckbox('medical_conditions', this.value)">
                            <span class="ml-2 text-xs"><?= $condition ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4">
                    <label for="other_conditions" class="block text-sm font-medium text-gray-700 mb-1">Others (specify) <span class="text-gray-500 font-normal">(Optional)</span>:</label>
                    <input type="text" id="other_conditions" name="other_conditions" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                           placeholder="Specify other medical conditions (leave blank if none)" 
                           value="<?= esc($patient['other_conditions'] ?? old('other_conditions')) ?>"
                           onchange="updateHiddenField('other_conditions', this.value)">
                </div>
            </div>

            <!-- Save Button -->
            <div class="pt-4 border-t border-gray-200">
                <button type="button" onclick="saveMedicalHistory()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                    <i class="fas fa-save mr-2"></i>Save Medical History
            </button>
                <p class="text-xs text-gray-500 mt-2 text-center">This will save the medical history to the patient's record</p>
            </div>
        </div>
    </div>
</div>

<!-- Externalized patient panel JS loaded above -->