<?= view('templates/header', ['title' => 'Create New Invoice']) ?>
<?= view('templates/alert_helper') ?>

<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user ?? null]) ?>

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
                            <span class="text-sm font-medium text-gray-700">Create New Invoice</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
                <h1 class="text-2xl font-bold text-gray-900">Create New Invoice</h1>
                
                <div class="flex items-center gap-2">
                    <a href="<?= base_url('admin/invoices') ?>" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Cancel</a>
                </div>
            </div>

            <!-- Flash Messages -->
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
                    <span><?= esc(session()->getFlashdata('error')) ?></span>
                    <button type="button" class="ml-auto text-red-700 hover:text-red-900 focus:outline-none" onclick="this.parentElement.remove()">&times;</button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="bg-red-100 text-red-800 rounded-lg px-4 py-3 mb-4 text-sm">
                    <div class="flex items-center gap-2 font-semibold mb-2">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Please fix the following errors:</span>
                    </div>
                    <ul class="list-disc list-inside ml-4">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Invoice Form -->
                <div class="lg:col-span-2">
                    <form method="POST" action="<?= base_url('admin/invoices/store') ?>" id="invoiceForm">
                        <!-- Invoice Information -->
                        <div class="bg-white rounded-lg shadow-lg border border-gray-200 mb-6">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Invoice Information</h2>
                            </div>
                            
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Patient Selection -->
                                    <div class="md:col-span-2">
                                        <label for="patient_id" class="block text-sm font-medium text-gray-700 mb-2">Patient *</label>
                                        <select id="patient_id" 
                                                name="patient_id" 
                                                class="w-full border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5"
                                                required>
                                            <option value="">Select a patient</option>
                                            <?php foreach ($patients ?? [] as $patient): ?>
                                                <option value="<?= $patient['id'] ?>" <?= old('patient_id') == $patient['id'] ? 'selected' : '' ?>>
                                                    <?= esc($patient['name']) ?> (<?= esc($patient['email']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (isset($errors['patient_id'])): ?>
                                            <p class="text-red-600 text-sm mt-1"><?= esc($errors['patient_id']) ?></p>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Procedure Selection -->
                                    <div>
                                        <label for="procedure_id" class="block text-sm font-medium text-gray-700 mb-2">Primary Procedure
                                            <button type="button" id="refreshProcedures" title="Refresh procedures" class="ml-2 text-xs text-blue-600 hover:underline">Refresh</button>
                                        </label>
                                        <select id="procedure_id" 
                                                name="procedure_id" 
                                                class="w-full border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                                            <option value="">Select a procedure (optional)</option>
                                        </select>
                                    </div>

                                    <!-- status and payment_status columns removed from database -->

                                    <!-- Discount -->
                                    <div>
                                        <label for="discount" class="block text-sm font-medium text-gray-700 mb-2">Discount ($)</label>
                                        <input type="number" 
                                               id="discount" 
                                               name="discount" 
                                               step="0.01"
                                               min="0"
                                               value="<?= old('discount', '0.00') ?>"
                                               class="w-full border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                                    </div>

                                    <!-- Notes -->
                                    <div class="md:col-span-2">
                                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                        <textarea id="notes" 
                                                  name="notes" 
                                                  rows="3"
                                                  class="w-full border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5"
                                                  placeholder="Optional notes about this invoice..."><?= old('notes') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Items -->
                        <div class="bg-white rounded-lg shadow-lg border border-gray-200 mb-6">
                            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-gray-900">Invoice Items</h2>
                                <button type="button" onclick="addItem()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
                                    <i class="fas fa-plus mr-2"></i>Add Item
                                </button>
                            </div>
                            
                            <div class="p-6">
                                <div id="itemsContainer">
                                    <!-- Items will be added here dynamically -->
                                    <div class="text-center py-8 text-gray-600" id="noItemsMessage">
                                        No items added yet. Click "Add Item" to start building your invoice.
                                    </div>
                                </div>
                                
                                <!-- Items Table Template (hidden) -->
                                <div id="itemsTableTemplate" style="display: none;">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full table-auto" id="itemsTable">
                                            <thead>
                                                <tr class="text-left text-sm text-gray-700 border-b">
                                                    <th class="px-4 py-3">Description</th>
                                                    <th class="px-4 py-3 w-24">Qty</th>
                                                    <th class="px-4 py-3 w-32">Unit Price</th>
                                                    <th class="px-4 py-3 w-32">Total</th>
                                                    <th class="px-4 py-3 w-20">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="itemsTableBody">
                                                <!-- Items will be inserted here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-6">
                            <div class="flex items-center justify-end gap-4">
                                <a href="<?= base_url('admin/invoices') ?>" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2.5 rounded-lg transition">
                                    Cancel
                                </a>
                                <button type="submit" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg transition flex items-center gap-2">
                                    <i class="fas fa-save"></i>
                                    Create Invoice
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Right Column: Totals -->
                <div class="lg:col-span-1">
                    <!-- Invoice Totals -->
                    <div class="bg-white rounded-lg shadow-lg border border-gray-200 sticky top-6">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Invoice Totals</h2>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3" id="totalsContainer">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="font-medium" id="subtotalAmount">$0.00</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Discount:</span>
                                    <span class="font-medium" id="discountAmount">$0.00</span>
                                </div>
                                <div class="border-t pt-3">
                                    <div class="flex justify-between">
                                        <span class="text-lg font-semibold">Total:</span>
                                        <span class="text-lg font-bold" id="totalAmount">$0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
let itemIndex = 0;
let items = [];

async function addItem() {
    const description = await (typeof showPrompt === 'function' ? showPrompt('Enter item description:', 'Description') : Promise.resolve(prompt('Enter item description:')));
    if (!description) return;
    
    const qStr = await (typeof showPrompt === 'function' ? showPrompt('Enter quantity:', 'Quantity', '1') : Promise.resolve(prompt('Enter quantity:') || '1'));
    const quantity = parseFloat(qStr || '1');
    if (isNaN(quantity) || quantity <= 0) {
        if (typeof showInvoiceAlert === 'function') showInvoiceAlert('Please enter a valid quantity', 'warning', 4000); else alert('Please enter a valid quantity');
        return;
    }
    
    const upStr = await (typeof showPrompt === 'function' ? showPrompt('Enter unit price:', 'Unit price', '0') : Promise.resolve(prompt('Enter unit price:') || '0'));
    const unitPrice = parseFloat(upStr || '0');
    if (isNaN(unitPrice) || unitPrice < 0) {
        if (typeof showInvoiceAlert === 'function') showInvoiceAlert('Please enter a valid unit price', 'warning', 4000); else alert('Please enter a valid unit price');
        return;
    }
    
    const total = quantity * unitPrice;
    
    const item = {
        index: itemIndex++,
        description: description,
        quantity: quantity,
        unit_price: unitPrice,
        total: total
    };
    
    items.push(item);
    renderItems();
    updateTotals();
}

function removeItem(index) {
    items = items.filter(item => item.index !== index);
    renderItems();
    updateTotals();
}

function renderItems() {
    const container = document.getElementById('itemsContainer');
    const noItemsMessage = document.getElementById('noItemsMessage');
    
    if (items.length === 0) {
        container.innerHTML = '<div class="text-center py-8 text-gray-600" id="noItemsMessage">No items added yet. Click "Add Item" to start building your invoice.</div>';
        return;
    }
    
    // Show items table
    const tableTemplate = document.getElementById('itemsTableTemplate').innerHTML;
    container.innerHTML = tableTemplate;
    
    const tbody = document.getElementById('itemsTableBody');
    tbody.innerHTML = '';
    
    items.forEach(item => {
        const row = document.createElement('tr');
        row.className = 'border-b';
        row.innerHTML = `
            <td class="px-4 py-3">
                ${escapeHtml(item.description)}
                <input type="hidden" name="items[${item.index}][description]" value="${escapeHtml(item.description)}">
            </td>
            <td class="px-4 py-3">
                ${item.quantity}
                <input type="hidden" name="items[${item.index}][quantity]" value="${item.quantity}">
            </td>
            <td class="px-4 py-3">$${item.unit_price.toFixed(2)}
                <input type="hidden" name="items[${item.index}][unit_price]" value="${item.unit_price}">
            </td>
            <td class="px-4 py-3">$${item.total.toFixed(2)}
                <input type="hidden" name="items[${item.index}][total]" value="${item.total}">
            </td>
            <td class="px-4 py-3">
                <button type="button" onclick="removeItem(${item.index})" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function updateTotals() {
    const subtotal = items.reduce((sum, item) => sum + item.total, 0);
    const discount = parseFloat(document.getElementById('discount').value || '0');
    const total = Math.max(0, subtotal - discount);

    document.getElementById('subtotalAmount').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('discountAmount').textContent = '$' + discount.toFixed(2);
    document.getElementById('totalAmount').textContent = '$' + total.toFixed(2);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Update totals when discount changes
document.getElementById('discount').addEventListener('input', updateTotals);

// Auto-populate item when procedure is selected
document.getElementById('procedure_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value && selectedOption.dataset.price) {
        const description = selectedOption.textContent.split(' - ')[0];
        const price = parseFloat(selectedOption.dataset.price);
        
        const item = {
            index: itemIndex++,
            description: description,
            quantity: 1,
            unit_price: price,
            total: price
        };
        
        items.push(item);
        renderItems();
        updateTotals();
    }
});

// Form validation
document.getElementById('invoiceForm').addEventListener('submit', function(e) {
    if (items.length === 0) {
        e.preventDefault();
        if (typeof showInvoiceAlert === 'function') showInvoiceAlert('Please add at least one item to the invoice.', 'warning', 4000); else alert('Please add at least one item to the invoice.');
        return;
    }
    
    const patientId = document.getElementById('patient_id').value;
    if (!patientId) {
        e.preventDefault();
        if (typeof showInvoiceAlert === 'function') showInvoiceAlert('Please select a patient.', 'warning', 4000); else alert('Please select a patient.');
        return;
    }
});

// Fetch procedures via AJAX and populate the select
function loadProcedures() {
    fetch('<?= base_url('admin/procedures/ajax-list') ?>', { credentials: 'same-origin' })
        .then(resp => resp.json())
        .then(data => {
            if (!data || !data.success) return;
            const select = document.getElementById('procedure_id');
            // Clear existing options except default
            select.innerHTML = '<option value="">Select a procedure (optional)</option>';
            data.data.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.dataset.price = p.price || 0;
                opt.textContent = p.name + ' - $' + (Number(p.price || 0)).toFixed(2);
                select.appendChild(opt);
            });
        })
        .catch(err => console.error('Failed to load procedures', err));
}

document.getElementById('refreshProcedures').addEventListener('click', function() {
    loadProcedures();
});

// Load on page start
document.addEventListener('DOMContentLoaded', function() {
    loadProcedures();
});
</script>

<?= view('templates/footer') ?>
