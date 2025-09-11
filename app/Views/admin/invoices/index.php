<?= view('templates/header', ['title' => 'Invoices']) ?>

<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user ?? null]) ?>

    <div class="flex-1 flex flex-col min-h-screen bg-white">
        <main class="flex-1 px-6 py-8 bg-white">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Invoices</h1>
                <a href="<?= base_url('admin/invoices/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Create Invoice</a>
            </div>

            <div class="bg-white rounded shadow p-4 mb-6">
                <form method="GET" action="<?= current_url() ?>" class="flex gap-2">
                    <input type="text" name="search" placeholder="Search by patient, email, id or invoice number" value="<?= esc($search ?? '') ?>" class="border px-3 py-2 rounded w-full" />
                    <button type="submit" class="bg-gray-700 text-white px-4 py-2 rounded">Search</button>
                </form>
            </div>

            <div class="bg-white rounded shadow p-4">
                <?php if (empty($invoices) || count($invoices) === 0): ?>
                    <div class="text-center py-8 text-gray-600">No invoices found.</div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead>
                                <tr class="text-left text-sm text-gray-700 border-b">
                                    <th class="px-4 py-3">#</th>
                                    <th class="px-4 py-3">Invoice #</th>
                                    <th class="px-4 py-3">Patient</th>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">Total</th>
                                    <th class="px-4 py-3">Created</th>
                                    <th class="px-4 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($invoices as $inv): ?>
                                <tr class="border-b">
                                    <td class="px-4 py-3"><?= esc($inv['id'] ?? '') ?></td>
                                    <td class="px-4 py-3"><?= esc($inv['invoice_number'] ?? $inv['id']) ?></td>
                                    <td class="px-4 py-3"><?= esc($inv['patient_name'] ?? $inv['name'] ?? '') ?></td>
                                    <td class="px-4 py-3"><?= esc($inv['patient_email'] ?? $inv['email'] ?? '') ?></td>
                                    <td class="px-4 py-3">$<?= number_format($inv['total_amount'] ?? $inv['total'] ?? 0, 2) ?></td>
                                    <td class="px-4 py-3"><?= esc($inv['created_at'] ?? '') ?></td>
                                    <td class="px-4 py-3">
                                        <a href="<?= base_url('admin/invoices/show/' . ($inv['id'] ?? '')) ?>" class="text-blue-600 mr-2">View</a>
                                        <a href="<?= base_url('admin/invoices/edit/' . ($inv['id'] ?? '')) ?>" class="text-green-600 mr-2">Edit</a>
                                        <a href="<?= base_url('admin/invoices/print/' . ($inv['id'] ?? '')) ?>" class="text-gray-600 mr-2">Print</a>
                                        <form method="POST" action="<?= base_url('admin/invoices/delete/' . ($inv['id'] ?? '')) ?>" style="display:inline" onsubmit="return confirm('Delete this invoice?')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="text-red-600">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if (!empty($pagination) && isset($pagination['total']) && $pagination['total'] > 0): ?>
                        <div class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-gray-600">Showing page <?= esc($pagination['current_page'] ?? 1) ?> of <?= esc($pagination['pages'] ?? 1) ?> (<?= esc($pagination['total'] ?? 0) ?> total)</div>
                            <div>
                                <?php for ($p = 1; $p <= ($pagination['pages'] ?? 1); $p++): ?>
                                    <a href="<?= current_url() ?>?page=<?= $p ?>" class="px-3 py-1 rounded <?= ($p == ($pagination['current_page'] ?? 1)) ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' ?> ml-1"><?= $p ?></a>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?= view('templates/footer') ?>
