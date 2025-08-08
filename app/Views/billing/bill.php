<?= $this->extend('templates/layout') ?>

<?= $this->section('title') ?>
Bill Generation - Appointment #<?= $appointment['id'] ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-white">
    <div class="flex">
        <?= $this->include('templates/sidebar') ?>
        
        <div class="flex-1 ml-64">
            <main class="p-8">
                <!-- Header -->
                <div class="bg-gradient-to-r from-green-600 to-blue-600 rounded-xl shadow-lg mb-8">
                    <div class="p-6 text-white">
                        <h1 class="text-3xl font-bold flex items-center">
                            <i class="fas fa-file-invoice-dollar mr-4"></i>
                            Generate Bill
                        </h1>
                        <p class="mt-2 opacity-90">Invoice for completed dental treatment</p>
                    </div>
                </div>

                <!-- Bill Details -->
                <div class="bg-white rounded-xl shadow-lg">
                    <div class="p-8">
                        <!-- Bill Header -->
                        <div class="flex justify-between items-start mb-8">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">Perfect Smile Dental Clinic</h2>
                                <p class="text-gray-600">Professional Dental Care Services</p>
                                <p class="text-sm text-gray-500 mt-2">
                                    Bill #: <strong><?= $bill_number ?></strong><br>
                                    Date: <strong><?= date('F j, Y', strtotime($bill_date)) ?></strong>
                                </p>
                            </div>
                            <div class="text-right">
                                <div class="bg-green-100 text-green-800 px-4 py-2 rounded-lg inline-block">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Treatment Completed
                                </div>
                            </div>
                        </div>

                        <!-- Patient Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8 bg-gray-50 rounded-lg p-6">
                            <div>
                                <h3 class="font-semibold text-gray-800 mb-3">Patient Information</h3>
                                <p><strong>Name:</strong> <?= esc($appointment['patient_name']) ?></p>
                                <p><strong>Email:</strong> <?= esc($appointment['patient_email']) ?></p>
                                <p><strong>Phone:</strong> <?= esc($appointment['patient_phone']) ?></p>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800 mb-3">Treatment Details</h3>
                                <p><strong>Date:</strong> <?= date('F j, Y', strtotime($appointment['appointment_datetime'])) ?></p>
                                <p><strong>Time:</strong> <?= date('g:i A', strtotime($appointment['appointment_datetime'])) ?></p>
                                <p><strong>Dentist:</strong> <?= esc($appointment['dentist_name']) ?></p>
                                <p><strong>Branch:</strong> <?= esc($appointment['branch_name']) ?></p>
                            </div>
                        </div>

                        <!-- Services/Items -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Services Provided</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($billItems as $item): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= esc($item['service']) ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $item['quantity'] ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱<?= number_format($item['unit_price'], 2) ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">₱<?= number_format($item['total'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Totals -->
                        <div class="border-t pt-6">
                            <div class="flex justify-end">
                                <div class="w-64">
                                    <div class="flex justify-between py-2">
                                        <span class="text-gray-600">Subtotal:</span>
                                        <span class="font-medium">₱<?= number_format($subtotal, 2) ?></span>
                                    </div>
                                    <div class="flex justify-between py-2">
                                        <span class="text-gray-600">Tax (12%):</span>
                                        <span class="font-medium">₱<?= number_format($tax, 2) ?></span>
                                    </div>
                                    <div class="flex justify-between py-2 border-t-2 border-gray-300">
                                        <span class="text-lg font-bold text-gray-800">Total Amount:</span>
                                        <span class="text-lg font-bold text-gray-800">₱<?= number_format($total, 2) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Form -->
                        <div class="mt-8 bg-blue-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Process Payment</h3>
                            <form method="POST" action="<?= base_url('billing/payment/' . $appointment['id']) ?>">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                                        <select name="payment_method" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">Select Payment Method</option>
                                            <option value="cash">Cash</option>
                                            <option value="card">Credit/Debit Card</option>
                                            <option value="gcash">GCash</option>
                                            <option value="paymaya">PayMaya</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Amount Received</label>
                                        <input type="number" name="amount" step="0.01" value="<?= $total ?>" required 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                                        <input type="text" name="notes" placeholder="Payment notes..."
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors">
                                        <i class="fas fa-credit-card mr-2"></i>
                                        Process Payment
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-6 flex justify-between">
                    <a href="<?= base_url('admin/appointments') ?>" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Appointments
                    </a>
                    
                    <button onclick="window.print()" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-print mr-2"></i>
                        Print Bill
                    </button>
                </div>
            </main>
        </div>
    </div>
</div>

<script>
// Calculate change if cash payment
document.querySelector('select[name="payment_method"]').addEventListener('change', function() {
    const amountInput = document.querySelector('input[name="amount"]');
    const notesInput = document.querySelector('input[name="notes"]');
    
    if (this.value === 'cash') {
        amountInput.addEventListener('input', function() {
            const totalAmount = <?= $total ?>;
            const amountReceived = parseFloat(this.value) || 0;
            const change = amountReceived - totalAmount;
            
            if (change > 0) {
                notesInput.value = `Change: ₱${change.toFixed(2)}`;
            } else {
                notesInput.value = '';
            }
        });
    }
});
</script>
<?= $this->endSection() ?>
