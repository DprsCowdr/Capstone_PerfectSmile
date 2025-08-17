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
                            <a href="<?= base_url('admin/invoices') ?>" class="text-sm font-medium text-gray-500 hover:text-gray-700">Invoices</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                            <span class="text-sm font-medium text-gray-700">New Invoice</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
                <h1 class="text-2xl font-bold text-gray-900">Create New Invoice</h1>
                
                <a href="<?= base_url('admin/invoices') ?>" 
                   class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2.5 px-4 rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    Back to Invoices
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

            <!-- Create Invoice Form -->
            <div class="bg-white rounded-lg shadow-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Invoice Information</h2>
                </div>
                
                <form method="POST" action="<?= base_url('admin/invoices/store') ?>" class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Patient -->
                        <div>
                            <label for="patient_id" class="block text-sm font-medium text-gray-700 mb-2">Patient *</label>
                            <select id="patient_id" 
                                    name="patient_id" 
                                    class="w-full border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5"
                                    required>
                                <option value="">Select a patient</option>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?= $patient['id'] ?>" <?= old('patient_id') == $patient['id'] ? 'selected' : '' ?>>
                                        <?= esc($patient['name']) ?> (<?= esc($patient['email']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Procedure (Optional) -->
                        <div>
                            <label for="procedure_id" class="block text-sm font-medium text-gray-700 mb-2">Procedure (Optional)</label>
                            <select id="procedure_id" 
                                    name="procedure_id" 
                                    class="w-full border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                                <option value="">Select a procedure</option>
                                <?php foreach ($procedures as $procedure): ?>
                                    <option value="<?= $procedure['id'] ?>" <?= old('procedure_id') == $procedure['id'] ? 'selected' : '' ?>>
                                        <?= esc($procedure['title'] ?? $procedure['procedure_name']) ?> - $<?= number_format($procedure['fee'] ?? 0, 2) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Due Date -->
                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                            <input type="date" 
                                   id="due_date" 
                                   name="due_date" 
                                   value="<?= old('due_date', date('Y-m-d', strtotime('+30 days'))) ?>"
                                   class="w-full border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                        </div>

                        <!-- Payment Terms -->
                        <div>
                            <label for="payment_terms" class="block text-sm font-medium text-gray-700 mb-2">Payment Terms</label>
                            <select id="payment_terms" 
                                    name="payment_terms" 
                                    class="w-full border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                                <option value="Net 30" <?= old('payment_terms') == 'Net 30' ? 'selected' : '' ?>>Net 30</option>
                                <option value="Net 15" <?= old('payment_terms') == 'Net 15' ? 'selected' : '' ?>>Net 15</option>
                                <option value="Net 7" <?= old('payment_terms') == 'Net 7' ? 'selected' : '' ?>>Net 7</option>
                                <option value="Due on Receipt" <?= old('payment_terms') == 'Due on Receipt' ? 'selected' : '' ?>>Due on Receipt</option>
                            </select>
                        </div>

                        <!-- Notes -->
                        <div class="md:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea id="notes" 
                                      name="notes" 
                                      rows="3"
                                      class="w-full border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5" 
                                      placeholder="Enter any additional notes for this invoice"><?= old('notes') ?></textarea>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-4 mt-8 pt-6 border-t border-gray-200">
                        <a href="<?= base_url('admin/invoices') ?>" 
                           class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2.5 px-6 rounded-lg transition">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg transition flex items-center gap-2">
                            <i class="fas fa-save"></i>
                            Create Invoice
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

<script>
// Set minimum date to today
document.getElementById('due_date').min = new Date().toISOString().split('T')[0];
</script>

<?= view('templates/footer') ?>
