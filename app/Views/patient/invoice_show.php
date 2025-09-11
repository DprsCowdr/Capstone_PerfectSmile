<?php echo view('templates/header', ['title' => 'Invoice']); ?>

<?php echo view('templates/sidebar', ['active' => 'records']); ?>

<div class="main-content" data-sidebar-offset>
    <?php echo view('templates/patient_topbar'); ?>

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-semibold mb-4">Invoice #<?= esc($invoice['id'] ?? '') ?></h1>

        <div class="bg-white border p-4 rounded shadow-sm">
            <div class="flex justify-between items-start">
                <div>
                    <div class="text-sm text-gray-500">Date</div>
                    <div class="font-medium"><?= esc(date('M d, Y', strtotime($invoice['created_at'] ?? $invoice['date'] ?? ''))) ?></div>

                    <div class="mt-4 text-sm text-gray-500">Patient</div>
                    <div class="font-medium"><?= esc($user['name'] ?? $user['email'] ?? '') ?></div>
                </div>

                <div class="text-right">
                    <div class="text-sm text-gray-500">Total</div>
                    <div class="font-medium"><?= esc($invoice['total_amount'] ?? $invoice['amount'] ?? $invoice['total'] ?? '') ?></div>

                    <div class="mt-2 text-sm text-gray-500">Discount</div>
                    <div class="font-medium"><?= esc(isset($invoice['discount']) ? $invoice['discount'] : '0.00') ?></div>

                    <div class="mt-4">
                        <div class="text-sm text-gray-500">Amount Due</div>
                        <div class="font-semibold text-lg"><?= esc($invoice['final_amount'] ?? $invoice['final'] ?? $invoice['amount_due'] ?? '') ?></div>
                    </div>
                </div>
            </div>

            <hr class="my-4" />

            <?php if (!empty($items)): ?>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-600">
                            <th class="pb-2">Description</th>
                            <th class="pb-2">Qty</th>
                            <th class="pb-2">Unit</th>
                            <th class="pb-2">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $it): ?>
                        <tr>
                            <td class="pt-2 pb-1"><?= esc($it['description'] ?? $it['name'] ?? '') ?></td>
                            <td class="pt-2 pb-1"><?= esc($it['qty'] ?? $it['quantity'] ?? '') ?></td>
                            <td class="pt-2 pb-1"><?= esc($it['unit_price'] ?? $it['price'] ?? '') ?></td>
                            <td class="pt-2 pb-1"><?= esc($it['total'] ?? ( (float)($it['qty'] ?? 1) * (float)($it['unit_price'] ?? $it['price'] ?? 0) ) ) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="text-sm text-gray-600">No line items available for this invoice.</div>
            <?php endif; ?>

            <div class="mt-4 flex justify-end space-x-2">
                <a href="<?= site_url('patient/invoice/' . ($invoice['id'] ?? '') . '/download') ?>" class="btn">Download PDF</a>
                <a href="#" id="back-to-records" class="btn btn-secondary">Back to Records</a>
            </div>
            <script>
            (function(){
                const back = document.getElementById('back-to-records');
                back.addEventListener('click', function(e){
                    e.preventDefault();
                    // Prefer history back so we return to the exact previous tab/state
                    if (window.history && window.history.length > 1) {
                        window.history.back();
                        // give browser a short time to navigate; if it doesn't, fallback
                        setTimeout(function(){
                            if (document.visibilityState === 'visible') {
                                window.location.href = '<?= site_url('patient/records?tab=invoices') ?>';
                            }
                        }, 300);
                    } else {
                        window.location.href = '<?= site_url('patient/records?tab=invoices') ?>';
                    }
                });
            })();
            </script>
        </div>
    </div>
</div>

<?php echo view('templates/footer'); ?>
