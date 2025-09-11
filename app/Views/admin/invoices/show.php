<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<!-- DEBUG SECTION - Remove in production -->
<?php
// Debug authentication state
$session = session();
$isLoggedIn = $session->get('isLoggedIn');
$userId = $session->get('user_id');
$userType = $session->get('user_type');

// Debug variables passed to view
$debugInfo = [
    'session_logged_in' => $isLoggedIn ? 'true' : 'false',
    'session_user_id' => $userId ?? 'null',
    'session_user_type' => $userType ?? 'null',
    'invoice_exists' => isset($invoice) ? 'true' : 'false',
    'invoice_id' => isset($invoice) ? ($invoice['id'] ?? 'no_id') : 'no_invoice',
    'totals_exists' => isset($totals) ? 'true' : 'false',
    'items_exists' => isset($items) ? 'true' : 'false',
    'items_count' => isset($items) ? count($items) : 0
];
?>

<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Invoice #<?= esc($invoice['invoice_number'] ?? $invoice['id'] ?? 'N/A') ?></h1>
                        <p class="text-gray-600 mt-1">Invoice details and items</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="<?= base_url('admin/invoices/edit/' . ($invoice['id'] ?? '')) ?>" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                            <i class="fas fa-edit mr-2"></i>Edit
                        </a>
                        <a href="<?= base_url('admin/invoices/print/' . ($invoice['id'] ?? '')) ?>" 
                           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
                            <i class="fas fa-print mr-2"></i>Print
                        </a>
                        <a href="<?= base_url('admin/invoices') ?>" 
                           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Invoices
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Invoice Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Invoice Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Invoice Info -->
                    <div class="bg-white rounded-lg shadow border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Invoice Info</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-2">
                                <div class="text-sm">
                                    <span class="text-gray-600">Number:</span>
                                    <span class="font-medium">#<?= esc($invoice['invoice_number'] ?? $invoice['id'] ?? 'N/A') ?></span>
                                </div>
                                <div class="text-sm">
                                    <span class="text-gray-600">Date:</span>
                                    <span class="font-medium">
                                        <?php 
                                        $date = $invoice['created_at'] ?? null;
                                        if ($date) {
                                            echo date('M d, Y', strtotime($date));
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </span>
                                </div>
                                <!-- Status and Due Date removed for cash-only clinics -->
                            </div>
                        </div>
                    </div>

                    <!-- Patient Info -->
                    <div class="bg-white rounded-lg shadow border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Patient</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-2">
                                <div class="text-sm">
                                    <span class="text-gray-600">Name:</span>
                                    <span class="font-medium"><?= esc($invoice['patient_name'] ?? 'Unknown Patient') ?></span>
                                </div>
                                <div class="text-sm">
                                    <span class="text-gray-600">Patient ID:</span>
                                    <span class="font-medium"><?= esc($invoice['patient_id'] ?? 'N/A') ?></span>
                                </div>
                                <div class="text-sm">
                                    <span class="text-gray-600">Email:</span>
                                    <span class="font-medium"><?= esc($invoice['patient_email'] ?? 'N/A') ?></span>
                                </div>
                                <?php if (!empty($invoice['patient_phone'])): ?>
                                <div class="text-sm">
                                    <span class="text-gray-600">Phone:</span>
                                    <span class="font-medium"><?= esc($invoice['patient_phone']) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Procedure Info -->
                    <div class="bg-white rounded-lg shadow border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Procedure</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-2">
                                <div class="text-sm">
                                    <span class="text-gray-600">Name:</span>
                                    <span class="font-medium"><?= esc($invoice['procedure_name'] ?? 'Unknown Procedure') ?></span>
                                </div>
                                <div class="text-sm">
                                    <span class="text-gray-600">Procedure ID:</span>
                                    <span class="font-medium"><?= esc($invoice['procedure_id'] ?? 'N/A') ?></span>
                                </div>
                                <?php if (!empty($invoice['procedure_description'])): ?>
                                <div class="text-sm">
                                    <span class="text-gray-600">Description:</span>
                                    <span class="font-medium"><?= esc($invoice['procedure_description']) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Items -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Invoice Items</h2>
                    </div>
                    <div class="p-6">
                        <?php if (!empty($items) && is_array($items)): ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead>
                                        <tr class="border-b border-gray-200">
                                            <th class="text-left py-3 px-4 font-medium text-gray-900">Description</th>
                                            <th class="text-left py-3 px-4 font-medium text-gray-900">Quantity</th>
                                            <th class="text-left py-3 px-4 font-medium text-gray-900">Unit Price</th>
                                            <th class="text-left py-3 px-4 font-medium text-gray-900">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($items as $item): ?>
                                        <tr class="border-b border-gray-100">
                                            <td class="py-3 px-4"><?= esc($item['description'] ?? 'N/A') ?></td>
                                            <td class="py-3 px-4"><?= esc($item['quantity'] ?? 1) ?></td>
                                            <td class="py-3 px-4">$<?= number_format($item['unit_price'] ?? 0, 2) ?></td>
                                            <td class="py-3 px-4">$<?= number_format($item['total'] ?? 0, 2) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-receipt text-4xl mb-4"></i>
                                <p class="text-lg">No items found</p>
                                <p class="text-sm">This invoice doesn't have any line items yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Notes Section -->
                <?php if (!empty($invoice['notes'])): ?>
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Notes</h2>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-700"><?= esc($invoice['notes']) ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Column: Totals & Actions -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Financial Summary -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Financial Summary</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Subtotal:</span>
                                <span class="font-medium">
                                    $<?= number_format($totals['subtotal'] ?? ($invoice['total_amount'] ?? 0), 2) ?>
                                </span>
                            </div>
                            <?php if (!empty($invoice['discount']) && $invoice['discount'] > 0): ?>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Discount:</span>
                                <span class="font-medium text-red-600">
                                    -$<?= number_format($invoice['discount'], 2) ?>
                                </span>
                            </div>
                            <?php endif; ?>
                            <?php if (isset($totals['tax']) && $totals['tax'] > 0): ?>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tax:</span>
                                <span class="font-medium">
                                    $<?= number_format($totals['tax'], 2) ?>
                                </span>
                            </div>
                            <?php endif; ?>
                            <div class="border-t pt-3">
                                <div class="flex justify-between">
                                    <span class="text-lg font-semibold">Final Total:</span>
                                    <span class="text-lg font-bold text-green-600">
                                        $<?= number_format($totals['total'] ?? ($invoice['final_amount'] ?? ($invoice['total_amount'] ?? 0) - ($invoice['discount'] ?? 0)), 2) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <?php 
                            $totalPaid = 0;
                            if (!empty($invoice['payments'])) {
                                foreach ($invoice['payments'] as $payment) {
                                    $totalPaid += $payment['amount'] ?? 0;
                                }
                            }
                            $balance = ($invoice['final_amount'] ?? ($invoice['total_amount'] ?? 0) - ($invoice['discount'] ?? 0)) - $totalPaid;
                            ?>
                            
                            <?php if ($totalPaid > 0): ?>
                            <div class="border-t pt-3 mt-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Total Paid:</span>
                                    <span class="font-medium text-blue-600">
                                        $<?= number_format($totalPaid, 2) ?>
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-lg font-semibold">Outstanding Balance:</span>
                                    <span class="text-lg font-bold <?= $balance <= 0 ? 'text-green-600' : 'text-red-600' ?>">
                                        $<?= number_format($balance, 2) ?>
                                    </span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Payment history removed (cash-only clinics) -->

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Quick Actions</h2>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="<?= base_url('admin/invoices/edit/' . ($invoice['id'] ?? '')) ?>" 
                           class="block w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-center">
                            <i class="fas fa-edit mr-2"></i>Edit Invoice
                        </a>
                        <button onclick="window.print()" 
                                class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg">
                            <i class="fas fa-print mr-2"></i>Print Invoice
                        </button>
                        <button onclick="sendInvoice()" 
                                class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2.5 rounded-lg">
                            <i class="fas fa-envelope mr-2"></i>Email Invoice
                        </button>
                        <a href="<?= base_url('admin/invoices') ?>" 
                           class="block w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2.5 rounded-lg text-center">
                            <i class="fas fa-list mr-2"></i>All Invoices
                        </a>
                    </div>
                </div>

                <!-- Duplicate payment history removed -->
            </div>
        </div>
    </div>
</div>

<?= view('templates/alert_helper') ?>

<style>
/* Inline invoice alert styles + fade animation */
.invoice-alert-container { position: fixed; top: 1.5rem; right: 1.5rem; z-index: 60; max-width: 20rem; }
.invoice-alert { margin-bottom: .5rem; padding: .75rem 1rem; border-radius: .5rem; box-shadow: 0 6px 18px rgba(15,23,42,0.08); display:flex; align-items:flex-start; gap:.5rem; opacity:0; transform: translateY(-8px); transition: opacity .28s ease, transform .28s ease; }
.invoice-alert.show { opacity:1; transform: translateY(0); }
.invoice-alert.hide { opacity:0; transform: translateY(-8px); }
.invoice-alert .invoice-msg { flex:1; font-size: .875rem; }
.invoice-alert .invoice-close { background: transparent; border: none; font-size: 1.25rem; line-height: 1; cursor: pointer; opacity: .85; }
.invoice-alert.info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a; }
.invoice-alert.success { background: #ecfdf5; border: 1px solid #bbf7d0; color: #065f46; }
.invoice-alert.warning { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
.invoice-alert.error { background: #fff1f2; border: 1px solid #fecaca; color: #9f1239; }
</style>

<script>
// Inline alert helper with fade-in/out animation
function showInvoiceAlert(message, type = 'info', timeout = 5000) {
    var containerId = 'invoice-alert-container';
    var container = document.getElementById(containerId);

    if (!container) {
        container = document.createElement('div');
        container.id = containerId;
        container.className = 'invoice-alert-container';
        document.body.appendChild(container);
    }

    // Create alert element safely without innerHTML
    var el = document.createElement('div');
    el.className = 'invoice-alert ' + (type || 'info');

    var msg = document.createElement('div');
    msg.className = 'invoice-msg';
    msg.textContent = String(message);

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'invoice-close';
    btn.setAttribute('aria-label', 'Dismiss');
    btn.innerHTML = '&times;';

    el.appendChild(msg);
    el.appendChild(btn);

    // Dismiss handler with fade out
    var removeEl = function() {
        el.classList.remove('show');
        el.classList.add('hide');
        el.addEventListener('transitionend', function te() {
            if (el.parentNode) el.parentNode.removeChild(el);
            el.removeEventListener('transitionend', te);
        });
    };

    btn.addEventListener('click', function () { removeEl(); });

    container.appendChild(el);

    // Force a reflow then show (so transition runs)
    requestAnimationFrame(function() { el.classList.add('show'); });

    // Auto-dismiss
    if (timeout > 0) {
        setTimeout(function () { removeEl(); }, timeout);
    }
}

function sendInvoice() {
    // Use the inline alert instead of the browser alert
    showInvoiceAlert('Email functionality will be implemented soon!', 'info', 6000);
}

// Debug: log invoice id for troubleshooting front-end navigation
document.addEventListener('DOMContentLoaded', function() {
    try { console.log('Viewing invoice id:', <?= json_encode($invoice['id'] ?? null) ?>); } catch(e) {}
});
</script>

<?= $this->endSection() ?>
