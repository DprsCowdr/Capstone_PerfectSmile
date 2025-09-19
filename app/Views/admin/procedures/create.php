<?= view('templates/header') ?>

<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    
    <div class="flex-1 flex flex-col min-h-screen bg-white">
        <main class="flex-1 px-6 py-8 bg-white">
            <!-- Breadcrumb removed as requested by admin UX -->

            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
                <h1 class="text-2xl font-bold text-gray-900">Create New Procedure</h1>
                
                <a href="<?= base_url('admin/procedures') ?>" 
                   class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2.5 px-4 rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    Back to Procedures
                </a>
            </div>

            <!-- Flash Messages -->
            <?php if (session()->getFlashdata('error')): ?>
                <div class="flex items-center gap-2 bg-red-100 text-red-800 rounded-lg px-4 py-3 mb-4 text-sm font-semibold">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= esc(session()->getFlashdata('error')) ?></span>
                    <button type="button" class="ml-auto text-red-700 hover:text-red-900 focus:outline-none" onclick="this.parentElement.remove()">&times;</button>
                </div>
            <?php endif; ?>

            <!-- Create Procedure Form -->
            <div class="bg-white rounded-lg shadow-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Procedure Information</h2>
                </div>
                
                <form method="POST" action="<?= base_url('admin/procedures/store') ?>" class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Title -->
                        <div class="md:col-span-2">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Procedure Title *</label>
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   value="<?= old('title') ?>"
                                   class="w-full border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5" 
                                   placeholder="Enter procedure title"
                                   required>
                        </div>

                        <!-- Procedure Name -->
                        <div class="md:col-span-2">
                            <label for="procedure_name" class="block text-sm font-medium text-gray-700 mb-2">Procedure Name *</label>
                            <input type="text" 
                                   id="procedure_name" 
                                   name="procedure_name" 
                                   value="<?= old('procedure_name') ?>"
                                   class="w-full border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5" 
                                   placeholder="Enter procedure name"
                                   required>
                        </div>

                        <!-- Patient -->
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">Patient *</label>
                            <select id="user_id" 
                                    name="user_id" 
                                    class="w-full border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5"
                                    required>
                                <option value="">Select a patient</option>
                                <?php if (isset($patients) && is_array($patients)): ?>
                                    <?php foreach ($patients as $patient): ?>
                                        <option value="<?= $patient['id'] ?>" <?= old('user_id') == $patient['id'] ? 'selected' : '' ?>>
                                            <?= esc($patient['name'] ?? $patient['first_name'] . ' ' . $patient['last_name'] ?? 'Unknown') ?> (<?= esc($patient['email']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No patients found</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Service (populated from services endpoint) -->
                        <div>
                            <label for="service_id" class="block text-sm font-medium text-gray-700 mb-2">Service *</label>
                            <select id="service_id"
                                    name="service_id"
                                    class="w-full border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5"
                                    required>
                                <option value="">Loading services...</option>
                            </select>
                        </div>

                        <!-- Fee -->
                        <div>
                            <label for="fee" class="block text-sm font-medium text-gray-700 mb-2">Fee ($)</label>
                            <input type="number" 
                                   id="fee" 
                                   name="fee" 
                                   value="<?= old('fee') ?>"
                                   step="0.01" 
                                   min="0"
                                   class="w-full border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5" 
                                   placeholder="0.00">
                        </div>

                        <!-- Duration (minutes) -->
                        <div>
                            <label for="duration" class="block text-sm font-medium text-gray-700 mb-2">Duration (minutes)</label>
                            <input type="number"
                                   id="duration"
                                   name="duration"
                                   value="<?= old('duration') ?>"
                                   min="0"
                                   step="1"
                                   class="w-full border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5"
                                   placeholder="e.g. 30">
                        </div>

                        <!-- Treatment Area (hidden by default; shown when a service requires it) -->
                        <div id="treatment_area_wrapper" style="display: none;">
                            <label for="treatment_area" class="block text-sm font-medium text-gray-700 mb-2">Treatment Area</label>
                            <select id="treatment_area" 
                                    name="treatment_area" 
                                    class="w-full border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                                <option value="Surface" <?= old('treatment_area') == 'Surface' ? 'selected' : '' ?>>Surface</option>
                                <option value="ToothRange" <?= old('treatment_area') == 'ToothRange' ? 'selected' : '' ?>>Tooth Range</option>
                                <option value="Upper" <?= old('treatment_area') == 'Upper' ? 'selected' : '' ?>>Upper</option>
                                <option value="Lower" <?= old('treatment_area') == 'Lower' ? 'selected' : '' ?>>Lower</option>
                                <option value="Full" <?= old('treatment_area') == 'Full' ? 'selected' : '' ?>>Full Mouth</option>
                            </select>
                        </div>

                        <!-- Procedure Date -->
                        <div>
                            <label for="procedure_date" class="block text-sm font-medium text-gray-700 mb-2">Procedure Date *</label>
                            <input type="date" 
                                   id="procedure_date" 
                                   name="procedure_date" 
                                   value="<?= old('procedure_date') ?>"
                                   class="w-full border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5"
                                   required>
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select id="status" 
                                    name="status" 
                                    class="w-full border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                                <option value="scheduled" <?= old('status') == 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                <option value="in_progress" <?= old('status') == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="completed" <?= old('status') == 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="cancelled" <?= old('status') == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea id="description" 
                                      name="description" 
                                      rows="4"
                                      class="w-full border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5" 
                                      placeholder="Enter procedure description"><?= old('description') ?></textarea>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-4 mt-8 pt-6 border-t border-gray-200">
                        <a href="<?= base_url('admin/procedures') ?>" 
                           class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2.5 px-6 rounded-lg transition">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg transition flex items-center gap-2">
                            <i class="fas fa-save"></i>
                            Create Procedure
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

<script>
// Set minimum date to today
document.getElementById('procedure_date').min = new Date().toISOString().split('T')[0];

// Fetch services and populate the Service select
(function() {
    const serviceSelect = document.getElementById('service_id');
    const feeField = document.getElementById('fee');
    const durationField = document.getElementById('duration');
    const treatmentAreaWrapper = document.getElementById('treatment_area_wrapper');
    const procedureNameField = document.getElementById('procedure_name');

    // Helper to clear options
    function clearOptions(select) {
        while (select.firstChild) select.removeChild(select.firstChild);
    }

    // Load services from endpoint
    fetch('<?= base_url('checkup/services/all') ?>', { credentials: 'same-origin' })
        .then(resp => resp.json())
        .then(json => {
            clearOptions(serviceSelect);
            if (!json || !json.services) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = 'No services available';
                serviceSelect.appendChild(opt);
                return;
            }

            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = 'Select a service';
            serviceSelect.appendChild(defaultOpt);

            json.services.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = s.name + (s.price ? ` ($${parseFloat(s.price).toFixed(2)})` : '');
                // Store metadata on option for quick access
                opt.dataset.price = s.price ?? '';
                // duration and treatment_area may not exist - keep flexible
                if (s.duration !== undefined) opt.dataset.duration = s.duration;
                if (s.treatment_area_required !== undefined) opt.dataset.treatmentAreaRequired = s.treatment_area_required;
                serviceSelect.appendChild(opt);
            });

            // If there's an old value (server validation), select it
            const oldService = '<?= old('service_id') ?>';
            if (oldService) {
                serviceSelect.value = oldService;
                serviceSelect.dispatchEvent(new Event('change'));
            }
        })
        .catch(err => {
            clearOptions(serviceSelect);
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = 'Failed to load services';
            serviceSelect.appendChild(opt);
            console.error('Failed to load services:', err);
        });

    // On service change, autofill fee, duration, and toggle treatment area
    serviceSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (!selected || !selected.value) {
            feeField.value = '';
            durationField.value = '';
            treatmentAreaWrapper.style.display = 'none';
            return;
        }

        const price = selected.dataset.price;
        const duration = selected.dataset.duration;
        const treatmentRequired = selected.dataset.treatmentAreaRequired;

        if (price !== undefined) {
            feeField.value = parseFloat(price).toFixed(2);
        }

        if (duration !== undefined) {
            durationField.value = duration;
        }

        // If service includes a treatment_area_required flag (truthy), show the control
        if (treatmentRequired !== undefined && (treatmentRequired === '1' || treatmentRequired === 'true' || treatmentRequired === 'yes')) {
            treatmentAreaWrapper.style.display = '';
        } else {
            // Keep it hidden by default
            treatmentAreaWrapper.style.display = 'none';
        }

        // Optionally set a default procedure name to the service name if procedure_name is empty
        if (procedureNameField && (!procedureNameField.value || procedureNameField.value.trim() === '')) {
            procedureNameField.value = selected.textContent.replace(/\s*\(\$.*\)$/, '');
        }
    });
})();
</script>

<?= view('templates/footer') ?>
