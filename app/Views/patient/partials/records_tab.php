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
                            <div class="text-sm text-gray-600 mt-1"><span class="inline-block mr-4"><strong>Treatment:</strong> <?php echo esc($t['treatment'] ?? $t['procedure_name'] ?? $t['procedure'] ?? ''); ?></span></div>
                            <?php if (!empty($t['notes'] ?? $t['summary'])): ?>
                                <div class="text-sm text-gray-600 mt-2"><strong>Notes:</strong> <?php echo esc(mb_substr($t['notes'] ?? $t['summary'],0,150)); ?><?php echo mb_strlen($t['notes'] ?? ($t['summary'] ?? ''))>150? '...':''; ?></div>
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

<?php elseif ($tab === '3d-chart'): ?>
    <h2 class="text-xl font-semibold mb-4">3D Dental Chart</h2>
    <?php if (!empty($dentalChart)): ?>
        <div class="mb-3 text-sm text-gray-600">
            Latest record: <b><?= date('Y-m-d', strtotime($dentalChart['record_date'] ?? '')) ?></b>
            <?php if (!empty($dentalChart['treatment'])): ?>
                <span class="text-gray-500">- <?= esc($dentalChart['treatment']) ?></span>
            <?php endif; ?>
        </div>
        
        <!-- Dental Chart Grid -->
        <div id="dental-chart-list" class="mb-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
            <?php if (!empty($dentalChart['chart_data'])): ?>
                <?php 
                $chartData = $dentalChart['chart_data'];
                if (is_array($chartData) && !empty($chartData)): 
                ?>
                    <?php foreach ($chartData as $tooth): ?>
                        <div class="border rounded p-2 text-center text-xs">
                            <div class="font-semibold">#<?= esc($tooth['tooth_number'] ?? '') ?></div>
                            <div class="text-gray-600"><?= esc($tooth['condition'] ?? 'healthy') ?></div>
                            <?php if (!empty($tooth['surface'])): ?>
                                <div class="text-blue-600 text-xs"><?= esc($tooth['surface']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($tooth['notes'])): ?>
                                <div class="text-indigo-600 text-xs"><?= esc($tooth['notes']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full text-center py-4 text-gray-500">
                        No dental chart data available
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-4 text-gray-500">
                    No dental chart data available
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Color Legend -->
        <div class="mb-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Color Legend:</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-green-500 rounded mr-2"></div>
                    <span>Healthy</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-red-500 rounded mr-2"></div>
                    <span>Cavity</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-blue-500 rounded mr-2"></div>
                    <span>Filled</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-yellow-400 rounded mr-2"></div>
                    <span>Crown</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-orange-500 rounded mr-2"></div>
                    <span>Root Canal</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-gray-500 rounded mr-2"></div>
                    <span>Other</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 border-2 border-gray-400 rounded mr-2 bg-transparent"></div>
                    <span>Missing</span>
                </div>
            </div>
        </div>

        <!-- 3D Viewer Container (Admin System Integration) -->
        <div class="mt-4">
            <div class="dental-3d-viewer-container">
                <div id="dentalChart3DViewer" class="dental-3d-viewer" style="height: 460px;">
                    <div class="model-loading" id="chart3dLoading">
                        <i class="fas fa-spinner fa-spin mr-2"></i>Loading 3D Model...
                    </div>
                    <div class="model-error hidden" id="chart3dError">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <div>Failed to load 3D model</div>
                    </div>
                    <canvas class="dental-3d-canvas"></canvas>
                </div>
            </div>
        </div>
        
        <div class="text-xs text-gray-500 text-center mt-2">
            Charts are read-only here. Go to records to edit.
        </div>
    <?php else: ?>
        <div class="text-center py-8">
            <div class="mb-4">
                <i class="fas fa-tooth text-gray-400 text-4xl mb-4"></i>
            </div>
            <p class="text-gray-500 mb-4">No dental chart data available yet.</p>
            <p class="text-sm text-gray-400">Your dental chart will appear here after your first dental examination.</p>
        </div>
    <?php endif; ?>

<?php else: ?>
    <div class="text-center py-8"><p class="text-gray-500">Invalid tab selected.</p></div>
<?php endif; ?>
