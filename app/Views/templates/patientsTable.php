<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<!-- Tailwind Patients Table -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
    <h1 class="font-bold text-2xl md:text-3xl text-black tracking-tight">Lists of Patients</h1>
    <?php if (in_array($user['user_type'], ['admin', 'staff'])): ?>
        <button id="showAddPatientFormBtn" class="bg-[#c7aefc] hover:bg-[#a47be5] text-white font-bold text-base rounded-xl shadow px-7 py-2.5 transition">+ Add New Patient</button>
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
                <th class="px-6 py-4 text-left">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($patients)): ?>
                <?php foreach ($patients as $patient): ?>
                <tr class="border-b last:border-b-0 hover:bg-indigo-50 transition">
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
                    <td class="px-6 py-5">
                        <a href="#" title="View" class="showViewPatientPanelBtn mr-2" data-patient='<?= json_encode([
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
                        ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>'><i class="fas fa-eye text-indigo-400 text-lg"></i></a>
                        <a href="#" title="Edit" class="showUpdatePatientPanelBtnTable mr-2" data-patient-id="<?= $patient['id'] ?>"><i class="fas fa-edit text-indigo-400 text-lg"></i></a>
                        <a href="#" title="Delete" class="mr-2"><i class="fas fa-trash text-red-400 text-lg"></i></a>
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
                <a href="#" title="Edit" class="showUpdatePatientPanelBtnTable p-2 text-indigo-400 hover:bg-indigo-50 rounded-lg transition" data-patient-id="<?= $patient['id'] ?>">
                    <i class="fas fa-edit"></i>
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
                        <option value="Male">Male</option>
                        <option value="Female"> Female</option>
                        <option value="Other"> Other</option>
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
        <button class="close-btn text-gray-500 hover:text-gray-700 text-2xl font-bold w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors" id="closeViewPatientPanel" aria-label="Close">&times;</button>
    </div>
    
    <!-- Patient Header Section -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
        <div class="flex flex-col items-center text-center gap-4">
            <div class="flex flex-col items-center gap-2">
                <button class="bg-indigo-50 rounded-full w-8 h-8 flex items-center justify-center text-indigo-400 hover:bg-indigo-100 transition">
                    <i class="fas fa-plus text-sm"></i>
                </button>
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
                    <i class="fas fa-pen showUpdatePatientPanelBtn text-indigo-300 cursor-pointer hover:text-indigo-500 transition"></i>
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

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to calculate age from date of birth
    function calculateAge(dateOfBirth) {
        if (!dateOfBirth) return '';
        
        const today = new Date();
        const birthDate = new Date(dateOfBirth);
        
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        return age > 0 ? age + ' years' : 'Invalid date';
    }

    // Initialize modern date picker with age calculation
    flatpickr(".modern-date-input", {
        dateFormat: "Y-m-d",
        allowInput: false,
        clickOpens: true,
        maxDate: new Date(),
        yearDropdown: true,
        monthDropdown: true,
        theme: "light",
        disableMobile: false,
        locale: {
            firstDayOfWeek: 1
        },
        onChange: function(selectedDates, dateStr, instance) {
            console.log('Date changed:', dateStr);
            // Calculate and display age when date changes
            const age = calculateAge(dateStr);
            console.log('Calculated age:', age);
            
            // Make sure elements exist before setting values
            const calculatedAgeInput = document.getElementById('calculated_age');
            const ageInput = document.getElementById('age');
            
            if (calculatedAgeInput) {
                calculatedAgeInput.value = age;
            }
            if (ageInput) {
                const ageNumber = age.replace(' years', '').replace('Invalid date', '0');
                ageInput.value = ageNumber; // Store just the number
                console.log('Age input set to:', ageNumber);
            }
        }
    });

    // Initialize date picker for update form
    flatpickr("#update-patient-date-of-birth", {
        dateFormat: "Y-m-d",
        allowInput: false,
        clickOpens: true,
        maxDate: new Date(),
        yearDropdown: true,
        monthDropdown: true,
        theme: "light",
        disableMobile: false,
        locale: {
            firstDayOfWeek: 1
        }
    });

    // Add Patient Panel
    var addBtn = document.getElementById('showAddPatientFormBtn');
    var addPanel = document.getElementById('addPatientPanel');
    var addCloseBtn = document.getElementById('closeAddPatientPanel');
    if (addBtn && addPanel && addCloseBtn) {
        addBtn.addEventListener('click', function() {
            addPanel.classList.add('active');
        });
        addCloseBtn.addEventListener('click', function() {
            addPanel.classList.remove('active');
        });
    }

    // View Patient Panel
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.showViewPatientPanelBtn');
        if (btn) {
            e.preventDefault();
            var viewPanel = document.getElementById('viewPatientPanel');
            if (viewPanel) viewPanel.classList.add('active');
            // Get patient data
            var patient = btn.getAttribute('data-patient');
            if (patient) {
                try {
                    var data = JSON.parse(patient);
                    document.getElementById('view-patient-name').textContent = data.name || '';
                    document.getElementById('view-patient-email').textContent = data.email || '';
                    document.getElementById('view-patient-phone').textContent = data.phone || '';
                    document.getElementById('view-patient-gender').textContent = data.gender || '';
                    document.getElementById('view-patient-date-of-birth').textContent = data.date_of_birth || '';
                    document.getElementById('view-patient-address').textContent = data.address || '';
                } catch (err) {
                    console.error('Error parsing patient data:', err);
                }
            }
        }
        if (e.target.closest('#closeViewPatientPanel')) {
            var viewPanel = document.getElementById('viewPatientPanel');
            if (viewPanel) viewPanel.classList.remove('active');
        }
    });

    // Update Patient Panel (table edit icon)
    document.addEventListener('click', function(e) {
        if (e.target.closest('.showUpdatePatientPanelBtnTable')) {
            e.preventDefault();
            e.stopPropagation();
            var updatePanel = document.getElementById('updatePatientPanel');
            if (updatePanel) {
                // Close any other open panels first
                document.querySelectorAll('.slide-in-panel.active').forEach(function(panel) {
                    if (panel !== updatePanel) {
                        panel.classList.remove('active');
                    }
                });
                updatePanel.classList.add('active');
                document.body.classList.add('panel-open');
            }
            
            // Get patient ID and load data
            var patientId = e.target.closest('.showUpdatePatientPanelBtnTable').getAttribute('data-patient-id');
            if (patientId) {
                loadPatientData(patientId);
            }
        }
        if (e.target.closest('#closeUpdatePatientPanel')) {
            e.preventDefault();
            e.stopPropagation();
            var updatePanel = document.getElementById('updatePatientPanel');
            if (updatePanel) {
                updatePanel.classList.remove('active');
                document.body.classList.remove('panel-open');
            }
        }
    });
    
    // Function to load patient data for update
    function loadPatientData(patientId) {
        console.log('Loading patient data for ID:', patientId);
        var userType = '<?= $user['user_type'] ?>';
        
        // Set form action first
        document.getElementById('updatePatientForm').action = '<?= base_url() ?>' + userType + '/patients/update/' + patientId;
        console.log('Form action set to:', document.getElementById('updatePatientForm').action);
        
        fetch('<?= base_url() ?>' + userType + '/patients/get/' + patientId)
            .then(response => response.json())
            .then(data => {
                console.log('Patient data received:', data);
                if (data.error) {
                    console.error('Error loading patient data:', data.error);
                    return;
                }
                
                // Fill the form fields
                document.getElementById('update-patient-id').value = data.id;
                document.getElementById('update-patient-name').value = data.name || '';
                document.getElementById('update-patient-email').value = data.email || '';
                document.getElementById('update-patient-phone').value = data.phone || '';
                document.getElementById('update-patient-address').value = data.address || '';
                document.getElementById('update-patient-gender').value = data.gender || '';
                document.getElementById('update-patient-date-of-birth').value = data.date_of_birth || '';
                document.getElementById('update-patient-age').value = data.age || '';
                document.getElementById('update-patient-occupation').value = data.occupation || '';
                document.getElementById('update-patient-nationality').value = data.nationality || '';
                
                console.log('Form fields populated');
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
    
    // Handle update form submission
    document.getElementById('updatePatientForm').addEventListener('submit', function(e) {
        console.log('Form submitted!');
        console.log('Form action:', this.action);
        console.log('Form method:', this.method);
        
        // Log all form fields
        var formData = new FormData(this);
        for (var pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }
        
        // Let the form submit normally - no need to prevent default
        // The form will post to the correct URL and handle the redirect
    });
    
    // Update Patient Panel (pen in view panel)
    var showUpdateBtnPen = document.querySelector('.showUpdatePatientPanelBtn');
    if (showUpdateBtnPen) {
        showUpdateBtnPen.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var updatePanel = document.getElementById('updatePatientPanel');
            if (updatePanel) {
                // Close any other open panels first
                document.querySelectorAll('.slide-in-panel.active').forEach(function(panel) {
                    if (panel !== updatePanel) {
                        panel.classList.remove('active');
                    }
                });
                updatePanel.classList.add('active');
                document.body.classList.add('panel-open');
            }
            
            // Get patient data from view panel
            var patientName = document.getElementById('view-patient-name').textContent;
            var patientEmail = document.getElementById('view-patient-email').textContent;
            var patientPhone = document.getElementById('view-patient-phone').textContent;
            var patientGender = document.getElementById('view-patient-gender').textContent;
            var patientDateOfBirth = document.getElementById('view-patient-date-of-birth').textContent;
            var patientAddress = document.getElementById('view-patient-address').textContent;
            
            // Fill the form fields (we'll need to get the full data via AJAX)
            // For now, we'll use the data from the view panel
            document.getElementById('update-patient-name').value = patientName;
            document.getElementById('update-patient-email').value = patientEmail;
            document.getElementById('update-patient-phone').value = patientPhone;
            document.getElementById('update-patient-gender').value = patientGender;
            document.getElementById('update-patient-date-of-birth').value = patientDateOfBirth;
            document.getElementById('update-patient-address').value = patientAddress;
        });
    }

    // Tab switching for View Patient panel
    var tabBtns = document.querySelectorAll('.view-patient-tab-btn');
    var tabContents = document.querySelectorAll('.view-patient-tab-content');
    tabBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var tab = btn.getAttribute('data-tab');
            
            // Remove active from all buttons
            tabBtns.forEach(function(b) { 
                b.classList.remove('active'); 
                b.classList.remove('bg-white', 'text-gray-800', 'shadow-sm');
                b.classList.add('text-indigo-500');
            });
            
            // Hide all content
            tabContents.forEach(function(c) { 
                c.classList.add('hidden');
            });
            
            // Set active button
            btn.classList.add('active');
            btn.classList.add('bg-white', 'text-gray-800', 'shadow-sm');
            btn.classList.remove('text-indigo-500');
            
            // Show active content
            var content = document.getElementById('view-patient-tab-content-' + tab);
            if (content) {
                content.classList.remove('hidden');
            }
        });
    });
});
</script> 

<!-- Debug CSS for slide-in-panel -->
<style>
.slide-in-panel {
  position: fixed;
  top: 0;
  right: 0;
  height: 100vh;
  width: 100vw;
  max-width: 28rem;
  background: #ffffff;
  box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
  z-index: 50;
  transform: translateX(100%);
  transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
  overflow-y: auto;
  -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
}
.slide-in-panel.active {
  transform: translateX(0);
}
@media (min-width: 640px) {
  .slide-in-panel {
    width: 28rem;
    max-width: 100vw;
  }
}
@media (max-width: 639px) {
  .slide-in-panel {
    width: 100vw;
    max-width: 100vw;
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
</style>

<!-- Debug JS for Add, View, and Update Patient panels -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    console.log('Patient panel JS loaded');
    
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
                document.body.classList.remove('panel-open');
            }
        });
    }
    
    // Update Patient
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
        if (e.target.classList.contains('slide-in-panel')) {
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
            
            // Only allow right swipe to close
            if (diffX > 50) {
                panel.style.transform = `translateX(${diffX}px)`;
            }
        });
        
        panel.addEventListener('touchend', function(e) {
            const diffX = startX - currentX;
            
            if (diffX > 100) {
                // Close panel
                panel.classList.remove('active');
                document.body.classList.remove('panel-open');
            }
            
            // Reset transform
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
});

// Debug function to check form data before submission
function debugFormData() {
    console.log('Form submission debug:');
    console.log('Name:', document.getElementById('name').value);
    console.log('Email:', document.getElementById('email').value);
    console.log('Phone:', document.getElementById('phone').value);
    console.log('Gender:', document.getElementById('gender').value);
    console.log('Date of Birth:', document.getElementById('date_of_birth').value);
    console.log('Age:', document.getElementById('age').value);
    console.log('Calculated Age:', document.getElementById('calculated_age').value);
    console.log('Occupation:', document.getElementById('occupation').value);
    console.log('Nationality:', document.getElementById('nationality').value);
    console.log('Address:', document.getElementById('address').value);
}
</script> 