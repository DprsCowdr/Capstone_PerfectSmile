<?= view('templates/header') ?>

<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    
    <div class="flex-1 flex flex-col min-h-screen bg-white">
        <main class="flex-1 px-6 py-8 bg-white">
            <!-- Breadcrumb -->
            <nav class="flex mb-6" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="<?= base_url('admin/dashboard') ?>" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
                            <i class="fas fa-home mr-2"></i>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                            <a href="<?= base_url('admin/procedures') ?>" class="text-sm font-medium text-gray-500 hover:text-gray-700">Procedures</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                            <span class="text-sm font-medium text-gray-700">New Procedure</span>
                        </div>
                    </li>
                </ol>
            </nav>

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
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?= $patient['id'] ?>" <?= old('user_id') == $patient['id'] ? 'selected' : '' ?>>
                                        <?= esc($patient['name']) ?> (<?= esc($patient['email']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Category -->
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select id="category" 
                                    name="category" 
                                    class="w-full border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                                <option value="none" <?= old('category') == 'none' ? 'selected' : '' ?>>None</option>
                                <option value="cleaning" <?= old('category') == 'cleaning' ? 'selected' : '' ?>>Cleaning</option>
                                <option value="extraction" <?= old('category') == 'extraction' ? 'selected' : '' ?>>Extraction</option>
                                <option value="filling" <?= old('category') == 'filling' ? 'selected' : '' ?>>Filling</option>
                                <option value="crown" <?= old('category') == 'crown' ? 'selected' : '' ?>>Crown</option>
                                <option value="root_canal" <?= old('category') == 'root_canal' ? 'selected' : '' ?>>Root Canal</option>
                                <option value="whitening" <?= old('category') == 'whitening' ? 'selected' : '' ?>>Whitening</option>
                                <option value="consultation" <?= old('category') == 'consultation' ? 'selected' : '' ?>>Consultation</option>
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

                        <!-- Treatment Area -->
                        <div>
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

// Auto-fill procedure name based on category
document.getElementById('category').addEventListener('change', function() {
    const category = this.value;
    const procedureNameField = document.getElementById('procedure_name');
    
    const categoryNames = {
        'cleaning': 'Dental Cleaning',
        'extraction': 'Tooth Extraction',
        'filling': 'Dental Filling',
        'crown': 'Dental Crown',
        'root_canal': 'Root Canal Treatment',
        'whitening': 'Teeth Whitening',
        'consultation': 'Dental Consultation'
    };
    
    if (categoryNames[category]) {
        procedureNameField.value = categoryNames[category];
    }
});
</script>

<?= view('templates/footer') ?>
