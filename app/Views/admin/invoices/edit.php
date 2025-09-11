<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Edit Invoice #<?= esc($invoice['invoice_number'] ?? 'N/A') ?></h1>
                        <p class="text-gray-600 mt-1">Update invoice details and items</p>
                    </div>
                    <a href="<?= base_url('admin/invoices') ?>" 
                       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Invoices
                    </a>
                </div>
            </div>
        </div>

        <form method="POST" action="<?= base_url('admin/invoices/update/' . ($invoice['id'] ?? '')) ?>" id="invoiceUpdateForm">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Invoice Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Invoice Information</h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Patient -->
                                <div>
                                    <label for="patient_id" class="block text-sm font-medium text-gray-700 mb-2">Patient *</label>
                                    <select id="patient_id" 
                                            name="patient_id" 
                                            class="w-full border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5"
                                            required>
                                        <option value="">Select a patient</option>
                                        <?php foreach ($patients ?? [] as $patient): ?>
                                            <option value="<?= $patient['id'] ?>" <?= ($invoice['patient_id'] ?? '') == $patient['id'] ? 'selected' : '' ?>>
                                                <?= esc($patient['name']) ?> (<?= esc($patient['email']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Procedure -->
                                <div>
                                    <label for="procedure_id" class="block text-sm font-medium text-gray-700 mb-2">Primary Procedure *</label>
                                    <select id="procedure_id" 
                                            name="procedure_id" 
                                            class="w-full border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5" 
                                            required>
                                        <option value="">Select a procedure</option>
                                        <?php foreach ($procedures ?? [] as $proc): ?>
                                            <option value="<?= $proc['id'] ?>" 
                                                    data-price="<?= esc($proc['price'] ?? 0) ?>"
                                                    <?= ($invoice['procedure_id'] ?? '') == $proc['id'] ? 'selected' : '' ?>>
                                                <?= esc($proc['procedure_name'] ?? $proc['name'] ?? '') ?> - $<?= number_format($proc['price'] ?? 0, 2) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Discount -->
                                <div>
                                    <label for="discount" class="block text-sm font-medium text-gray-700 mb-2">Discount ($)</label>
                                    <input type="number" 
                                           id="discount" 
                                           name="discount" 
                                           step="0.01"
                                           value="<?= esc($invoice['discount'] ?? '0.00') ?>"
                                           oninput="updateTotals()"
                                           class="w-full border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                                </div>

                                <!-- Notes -->
                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                    <textarea id="notes" 
                                              name="notes" 
                                              rows="3"
                                              class="w-full border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5"
                                              placeholder="Additional notes..."><?= esc($invoice['notes'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Items -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-gray-900">Invoice Items</h2>
                                <button type="button" onclick="addItem()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
                                    <i class="fas fa-plus mr-2"></i>Add Item
                                </button>
                            </div>
                        </div>
                        <div class="p-6">
                            <div id="itemsContainer">
                                <div class="text-center py-8 text-gray-500" id="noItemsMessage">
                                    No items added yet. Click "Add Item" to start.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Totals & Actions -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Invoice Totals -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Invoice Totals</h2>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="font-medium" id="subtotalDisplay">$<?= number_format($totals['subtotal'] ?? ($invoice['total_amount'] ?? 0), 2) ?></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Discount:</span>
                                    <span class="font-medium" id="discountDisplay">$<?= number_format($invoice['discount'] ?? ($totals['discount'] ?? 0), 2) ?></span>
                                </div>
                                <div class="border-t pt-3">
                                    <div class="flex justify-between">
                                        <span class="text-lg font-semibold">Total:</span>
                                        <span class="text-lg font-bold text-blue-600" id="totalDisplay">$<?= number_format($totals['total'] ?? (($invoice['total_amount'] ?? 0) - ($invoice['discount'] ?? 0)), 2) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Actions</h2>
                        </div>
                        <div class="p-6 space-y-3">
                            <button type="submit" 
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-lg transition flex items-center justify-center gap-2">
                                <i class="fas fa-save"></i>
                                Update Invoice
                            </button>
                                     <a href="<?= site_url('admin/invoices/show/' . ($invoice['id'] ?? '')) ?>" target="_blank" 
                                         class="block w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2.5 rounded-lg text-center">
                                <i class="fas fa-eye mr-2"></i>View Invoice
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?= view('templates/alert_helper') ?>

<script>
let itemIndex = 0;
    let items = [];

// Initialize items from server-side data
<?php if (!empty($items) && is_array($items)): ?>
items = [
    <?php foreach ($items as $it): ?>
    {
        index: itemIndex++,
        description: <?= json_encode($it['description'] ?? '') ?>,
        quantity: <?= json_encode(floatval($it['quantity'] ?? 1)) ?>,
        unit_price: <?= json_encode(floatval($it['unit_price'] ?? 0)) ?>,
        total: <?= json_encode(floatval($it['total'] ?? 0)) ?>
    },
    <?php endforeach; ?>
];
<?php endif; ?>

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
    
    if (items.length === 0) {
        container.innerHTML = '<div class="text-center py-8 text-gray-500" id="noItemsMessage">No items added yet. Click "Add Item" to start.</div>';
        return;
    }
    
    let html = `
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-4 font-medium text-gray-900">Description</th>
                        <th class="text-left py-3 px-4 font-medium text-gray-900">Qty</th>
                        <th class="text-left py-3 px-4 font-medium text-gray-900">Unit Price</th>
                        <th class="text-left py-3 px-4 font-medium text-gray-900">Total</th>
                        <th class="text-left py-3 px-4 font-medium text-gray-900">Actions</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    items.forEach((item, arrayIndex) => {
        html += `
            <tr class="border-b border-gray-100">
                <td class="py-3 px-4">${item.description}</td>
                <td class="py-3 px-4">${item.quantity}</td>
                <td class="py-3 px-4">$${item.unit_price.toFixed(2)}</td>
                <td class="py-3 px-4">$${item.total.toFixed(2)}</td>
                <td class="py-3 px-4">
                    <button type="button" onclick="removeItem(${item.index})" 
                            class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
            <input type="hidden" name="items[${arrayIndex}][description]" value="${item.description}">
            <input type="hidden" name="items[${arrayIndex}][quantity]" value="${item.quantity}">
            <input type="hidden" name="items[${arrayIndex}][unit_price]" value="${item.unit_price}">
            <input type="hidden" name="items[${arrayIndex}][total]" value="${item.total}">
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = html;
}

function updateTotals() {
    try {
        // Calculate subtotal from items, but use server value if no items exist
        let subtotal = 0;
        if (items.length > 0) {
            subtotal = items.reduce((sum, item) => {
                const t = parseFloat(item.total) || 0;
                return sum + t;
            }, 0);
        } else {
            // Use server-side total if no client items
            subtotal = <?= json_encode(floatval($totals['subtotal'] ?? ($invoice['total_amount'] ?? 0))) ?>;
        }
        
        const discountEl = document.getElementById('discount');
        const discount = discountEl ? (parseFloat(discountEl.value) || 0) : (<?= json_encode(floatval($invoice['discount'] ?? 0)) ?>);
        const total = Math.max(0, subtotal - discount);

        const subtotalEl = document.getElementById('subtotalDisplay');
        const discountDisplayEl = document.getElementById('discountDisplay');
        const totalEl = document.getElementById('totalDisplay');

        if (subtotalEl) subtotalEl.textContent = '$' + subtotal.toFixed(2);
        if (discountDisplayEl) discountDisplayEl.textContent = '$' + discount.toFixed(2);
        if (totalEl) totalEl.textContent = '$' + total.toFixed(2);
    } catch (err) {
        console.error('updateTotals error', err);
    }
}

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Debug logging
    console.log('Invoice edit page loaded');
    console.log('Items initialized:', items);
    console.log('Invoice data:', {
        id: <?= json_encode($invoice['id'] ?? null) ?>,
        total_amount: <?= json_encode($invoice['total_amount'] ?? null) ?>,
        discount: <?= json_encode($invoice['discount'] ?? null) ?>,
        totals: <?= json_encode($totals ?? null) ?>
    });
    
    renderItems();
    updateTotals();
    
    // Log final display values
    setTimeout(() => {
        console.log('Final displayed values:', {
            subtotal: document.getElementById('subtotalDisplay')?.textContent,
            discount: document.getElementById('discountDisplay')?.textContent,
            total: document.getElementById('totalDisplay')?.textContent
        });
    }, 100);
});
</script>

<?= $this->endSection() ?>
