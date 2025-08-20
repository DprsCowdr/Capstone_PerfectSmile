<!-- Tailwind Invoices Table -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
    <h1 class="font-bold text-2xl md:text-3xl text-black tracking-tight">Lists of Invoices</h1>
    <?php if (in_array($user['user_type'], ['admin', 'staff'])): ?>
        <a href="<?= base_url('admin/invoices/create') ?>" class="bg-[#c7aefc] hover:bg-[#a47be5] text-white font-bold text-base rounded-xl shadow px-7 py-2.5 transition">+ Create New Invoice</a>
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
        <span><?= esc(session()->getFlashdata('error')) ?></span>
        <button type="button" class="ml-auto text-red-700 hover:text-red-900 focus:outline-none" onclick="this.parentElement.remove()">&times;</button>
    </div>
<?php endif; ?>

<!-- Desktop Table View -->
<div class="hidden lg:block overflow-x-auto mb-8">
    <table class="min-w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        <thead class="bg-white">
            <tr class="text-black font-extrabold text-base">
                <th class="px-8 py-4 text-left">Invoice #</th>
                <th class="px-4 py-4 text-left">Patient</th>
                <th class="px-4 py-4 text-left">Date</th>
                <th class="px-4 py-4 text-left">Due Date</th>
                <th class="px-4 py-4 text-left">Total</th>
                <th class="px-4 py-4 text-left">Paid</th>
                <th class="px-4 py-4 text-left">Balance</th>
                <th class="px-4 py-4 text-left">Status</th>
                <th class="px-6 py-4 text-left">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($invoices)): ?>
                <?php foreach ($invoices as $invoice): ?>
                <tr class="border-b last:border-b-0 hover:bg-indigo-50 transition">
                    <td class="min-w-[180px] px-8 py-5">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center font-bold text-lg text-indigo-400">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <div>
                                <div class="font-extrabold text-black text-base"><?= esc($invoice['invoice_number']) ?></div>
                                <div class="text-sm text-gray-600"><?= esc($invoice['patient_name']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="text-black px-4 py-5"><?= esc($invoice['patient_name']) ?></td>
                    <td class="font-bold text-black px-4 py-5">
                        <div class="text-sm">
                            <?= date('d M Y', strtotime($invoice['created_at'])) ?>
                        </div>
                        <div class="text-xs text-gray-600">
                            <?= date('g:i A', strtotime($invoice['created_at'])) ?>
                        </div>
                    </td>
                    <td class="text-black px-4 py-5">
                        <?= $invoice['due_date'] ? date('d M Y', strtotime($invoice['due_date'])) : 'Not set' ?>
                    </td>
                    <td class="text-black px-4 py-5 font-bold">$<?= number_format($invoice['total_amount'], 2) ?></td>
                    <td class="text-black px-4 py-5">$<?= number_format($invoice['paid_amount'], 2) ?></td>
                    <td class="text-black px-4 py-5 font-bold">$<?= number_format($invoice['balance_amount'], 2) ?></td>
                    <td class="px-4 py-5">
                        <?php 
                        $status = $invoice['status'];
                        $statusClasses = [
                            'draft' => 'bg-gray-100 text-gray-700',
                            'sent' => 'bg-blue-100 text-blue-700',
                            'paid' => 'bg-green-100 text-green-700',
                            'overdue' => 'bg-red-100 text-red-700',
                            'cancelled' => 'bg-yellow-100 text-yellow-700'
                        ];
                        $statusClass = $statusClasses[$status] ?? 'bg-gray-100 text-gray-700';
                        ?>
                        <span class="inline-block font-semibold rounded-md px-3 py-1 text-xs <?= $statusClass ?>">
                            <?= ucfirst($status) ?>
                        </span>
                    </td>
                    <td class="px-6 py-5">
                        <a href="<?= base_url('admin/invoices/show/' . $invoice['id']) ?>" title="View" class="mr-2"><i class="fas fa-eye text-indigo-400 text-lg"></i></a>
                        <a href="<?= base_url('admin/invoices/edit/' . $invoice['id']) ?>" title="Edit" class="mr-2"><i class="fas fa-edit text-indigo-400 text-lg"></i></a>
                        <a href="<?= base_url('admin/invoices/print/' . $invoice['id']) ?>" title="Print" class="mr-2"><i class="fas fa-print text-blue-400 text-lg"></i></a>
                        <button onclick="deleteInvoice(<?= $invoice['id'] ?>)" title="Delete" class="mr-2"><i class="fas fa-trash text-red-400 text-lg"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="9" class="text-center py-12 text-black font-semibold">No invoices found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Mobile Card View -->
<div class="lg:hidden space-y-4 mb-8">
    <?php if (!empty($invoices)): ?>
        <?php foreach ($invoices as $invoice): ?>
        <div class="bg-white rounded-2xl shadow-xl p-4 border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center font-bold text-lg text-indigo-400">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div>
                        <div class="font-bold text-black text-base"><?= esc($invoice['invoice_number']) ?></div>
                        <div class="text-sm text-gray-600"><?= esc($invoice['patient_name']) ?></div>
                    </div>
                </div>
                <?php 
                $status = $invoice['status'];
                $statusClasses = [
                    'draft' => 'bg-gray-100 text-gray-700',
                    'sent' => 'bg-blue-100 text-blue-700',
                    'paid' => 'bg-green-100 text-green-700',
                    'overdue' => 'bg-red-100 text-red-700',
                    'cancelled' => 'bg-yellow-100 text-yellow-700'
                ];
                $statusClass = $statusClasses[$status] ?? 'bg-gray-100 text-gray-700';
                ?>
                <span class="inline-block font-semibold rounded-md px-2 py-1 text-xs <?= $statusClass ?>">
                    <?= ucfirst($status) ?>
                </span>
            </div>
            
            <div class="space-y-2 mb-4">
                <div class="flex items-center gap-2 text-sm">
                    <i class="fas fa-calendar text-gray-400 w-4"></i>
                    <span class="text-black">
                        <?= date('d M Y', strtotime($invoice['created_at'])) ?>
                    </span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <i class="fas fa-clock text-gray-400 w-4"></i>
                    <span class="text-black">
                        Due: <?= $invoice['due_date'] ? date('d M Y', strtotime($invoice['due_date'])) : 'Not set' ?>
                    </span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <i class="fas fa-dollar-sign text-gray-400 w-4"></i>
                    <span class="text-black font-bold">Total: $<?= number_format($invoice['total_amount'], 2) ?></span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <i class="fas fa-credit-card text-gray-400 w-4"></i>
                    <span class="text-black">Paid: $<?= number_format($invoice['paid_amount'], 2) ?></span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <i class="fas fa-balance-scale text-gray-400 w-4"></i>
                    <span class="text-black font-bold">Balance: $<?= number_format($invoice['balance_amount'], 2) ?></span>
                </div>
            </div>
            
            <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                <a href="<?= base_url('admin/invoices/show/' . $invoice['id']) ?>" title="View" class="p-2 text-indigo-400 hover:bg-indigo-50 rounded-lg transition">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="<?= base_url('admin/invoices/edit/' . $invoice['id']) ?>" title="Edit" class="p-2 text-indigo-400 hover:bg-indigo-50 rounded-lg transition">
                    <i class="fas fa-edit"></i>
                </a>
                <a href="<?= base_url('admin/invoices/print/' . $invoice['id']) ?>" title="Print" class="p-2 text-blue-400 hover:bg-blue-50 rounded-lg transition">
                    <i class="fas fa-print"></i>
                </a>
                <button onclick="deleteInvoice(<?= $invoice['id'] ?>)" title="Delete" class="p-2 text-red-400 hover:bg-red-50 rounded-lg transition">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="text-center py-12 text-black font-semibold bg-white rounded-2xl shadow-xl">
            <i class="fas fa-file-invoice text-4xl mb-4 block"></i>
            No invoices found.
        </div>
    <?php endif; ?>
</div>

<script>
function deleteInvoice(id) {
    if (confirm('Are you sure you want to delete this invoice?')) {
        fetch('<?= base_url('admin/invoices/delete/') ?>' + id, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the invoice.');
        });
    }
}
</script>
