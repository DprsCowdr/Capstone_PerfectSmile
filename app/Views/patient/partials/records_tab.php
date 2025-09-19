<?php
// Patient records tab partial
// Expects: $tab, and corresponding arrays: $appointments, $treatments, $prescriptions, $invoices
?>

<?php if ($tab === 'appointments'): ?>
    <h2 class="text-xl font-semibold mb-4">Appointment History</h2>
    <?php if (!empty($appointments)): ?>
        <div class="space-y-3">
            <?php foreach ($appointments as $appointment): ?>
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="font-semibold text-lg text-gray-900"><?php echo date('M d, Y \a\t H:i', strtotime($appointment['appointment_datetime'])); ?></div>
                            <div class="text-sm text-gray-600 mt-1">
                                <span class="inline-block mr-4"><strong>Branch:</strong> <?php echo esc($appointment['branch_name'] ?? ''); ?></span>
                                <span class="inline-block mr-4"><strong>Dentist:</strong> <?php echo esc($appointment['dentist_name'] ?? ''); ?></span>
                            </div>
                            <?php if (!empty($appointment['notes'])): ?>
                                <div class="text-sm text-gray-600 mt-2"><strong>Notes:</strong> <?php echo esc(mb_substr($appointment['notes'],0,150)); ?><?php echo mb_strlen($appointment['notes'])>150? '...':''; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="ml-4">
                            <a href="<?php echo site_url('patient/appointments') ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-8">
            <p class="text-gray-500">You have no appointment records yet.</p>
        </div>
    <?php endif; ?>

<?php elseif ($tab === 'treatments'): ?>
    <h2 class="text-xl font-semibold mb-4">Treatment Records</h2>
    <?php if (!empty($treatments)): ?>
        <div class="space-y-3">
            <?php foreach ($treatments as $t): ?>
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="font-semibold text-lg text-gray-900"><?php echo date('M d, Y', strtotime($t['record_date'] ?? $t['session_date'] ?? '')); ?></div>
                            <div class="text-sm text-gray-600 mt-1"><span class="inline-block mr-4"><strong>Procedure:</strong> <?php echo esc($t['procedure_name'] ?? $t['procedure'] ?? ''); ?></span></div>
                            <?php if (!empty($t['summary'] ?? $t['notes'])): ?>
                                <div class="text-sm text-gray-600 mt-2"><strong>Notes:</strong> <?php echo esc(mb_substr($t['summary'] ?? $t['notes'],0,150)); ?><?php echo mb_strlen($t['summary'] ?? ($t['notes'] ?? ''))>150? '...':''; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="ml-4">
                            <a href="<?php echo site_url('patient/treatment/') . (int)($t['id'] ?? 0); ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-8">
            <p class="text-gray-500">You have no treatment records yet.</p>
        </div>
    <?php endif; ?>

<?php elseif ($tab === 'prescriptions'): ?>
    <h2 class="text-xl font-semibold mb-4">Prescriptions</h2>
    <?php if (!empty($prescriptions)): ?>
        <div class="space-y-3">
            <?php foreach ($prescriptions as $pres): ?>
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="font-semibold text-lg text-gray-900">Prescription #<?php echo esc($pres['id']); ?></div>
                            <div class="text-sm text-gray-600 mt-1"><strong>Issue Date:</strong> <?php echo date('M d, Y', strtotime($pres['issue_date'] ?? $pres['created_at'])); ?></div>
                            <?php if (!empty($pres['items'])): ?>
                                <div class="mt-3 text-sm text-gray-700"><strong>Medications:</strong>
                                    <ul class="mt-1">
                                        <?php foreach ($pres['items'] as $it): ?>
                                            <li><?php echo esc($it['medicine_name'] ?? $it['medication'] ?? ''); ?> <?php echo esc($it['dosage'] ?? ''); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="ml-4 flex flex-col space-y-2">
                            <a href="<?php echo site_url('patient/prescription/') . (int)($pres['id'] ?? 0); ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-8">
            <p class="text-gray-500">You have no prescriptions yet.</p>
        </div>
    <?php endif; ?>

<?php elseif ($tab === 'invoices'): ?>
    <h2 class="text-xl font-semibold mb-4">Invoices</h2>
    <?php if (!empty($invoices)): ?>
        <div class="space-y-3">
            <?php foreach ($invoices as $inv): ?>
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="font-semibold text-lg text-gray-900">Invoice #<?php echo esc($inv['invoice_number'] ?? $inv['id']); ?></div>
                            <div class="text-sm text-gray-600 mt-1"><strong>Date:</strong> <?php echo date('M d, Y', strtotime($inv['created_at'] ?? $inv['invoice_date'])); ?></div>
                            <div class="mt-2 text-sm"><div><strong>Total:</strong> ₱<?php echo number_format($inv['total_amount'] ?? $inv['amount'] ?? 0,2); ?></div></div>
                        </div>
                        <div class="ml-4 flex flex-col space-y-2">
                            <a href="<?php echo site_url('patient/invoice/') . (int)($inv['id'] ?? 0); ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View Invoice</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-8">
            <p class="text-gray-500">You have no invoices yet.</p>
        </div>
    <?php endif; ?>

<?php else: ?>
    <div class="text-center py-8"><p class="text-gray-500">Invalid tab selected.</p></div>
<?php endif; ?>
