<?php
// Staff Records Tab Partial
// Renders content for a specific tab based on $tab and $tabData
?>

<?php if ($tab === 'appointments'): ?>
    <h2 class="text-xl font-semibold mb-4">Appointment History</h2>
    <?php if (!empty($tabData)): ?>
        <div class="space-y-3">
            <?php foreach ($tabData as $appointment): ?>
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="font-semibold text-lg text-gray-900">
                                <?php echo date('M d, Y \a\t H:i', strtotime($appointment['appointment_datetime'])); ?>
                            </div>
                            <div class="text-sm text-gray-600 mt-1">
                                <span class="inline-block mr-4">
                                    <strong>Branch:</strong> <?php echo esc($appointment['branch_name'] ?? 'Unknown'); ?>
                                </span>
                                <span class="inline-block mr-4">
                                    <strong>Dentist:</strong> <?php echo esc($appointment['dentist_name'] ?? 'Unknown'); ?>
                                </span>
                            </div>
                            <div class="text-sm mt-2">
                                <span class="px-2 py-1 rounded text-xs font-medium
                                    <?php 
                                    switch($appointment['status']) {
                                        case 'confirmed': echo 'bg-green-100 text-green-800'; break;
                                        case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                        case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                        case 'completed': echo 'bg-blue-100 text-blue-800'; break;
                                        default: echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?php echo ucfirst(esc($appointment['status'] ?? 'unknown')); ?>
                                </span>
                            </div>
                            <?php if (!empty($appointment['notes'])): ?>
                                <div class="text-sm text-gray-600 mt-2">
                                    <strong>Notes:</strong>
                                    <?php
                                        $__notes = $appointment['notes'] ?? '';
                                        if (function_exists('character_limiter')) {
                                            echo esc(character_limiter($__notes, 120));
                                        } else {
                                            $__lim = 120;
                                            $__trunc = mb_substr($__notes, 0, $__lim);
                                            echo esc(mb_strlen($__notes) > $__lim ? $__trunc . '...' : $__trunc);
                                        }
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="ml-4">
                            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View Details
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-8">
            <div class="text-gray-400 mb-2">
                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <p class="text-gray-500">No appointment records found for this patient in your branches.</p>
        </div>
    <?php endif; ?>

<?php elseif ($tab === 'treatments'): ?>
    <h2 class="text-xl font-semibold mb-4">Treatment Records</h2>
    <?php if (!empty($tabData)): ?>
        <div class="space-y-3">
            <?php foreach ($tabData as $treatment): ?>
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="font-semibold text-lg text-gray-900">
                                <?php echo date('M d, Y', strtotime($treatment['session_date'] ?? $treatment['started_at'])); ?>
                            </div>
                            <div class="text-sm text-gray-600 mt-1">
                                <span class="inline-block mr-4">
                                    <strong>Type:</strong> <?php echo esc($treatment['appointment_type'] ?? 'Treatment'); ?>
                                </span>
                                <span class="inline-block mr-4">
                                    <strong>Dentist:</strong> <?php echo esc($treatment['dentist_name'] ?? 'Unknown'); ?>
                                </span>
                            </div>
                            <?php if (!empty($treatment['room_number'])): ?>
                                <div class="text-sm text-gray-600 mt-1">
                                    <strong>Room:</strong> <?php echo esc($treatment['room_number']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($treatment['treatment_notes'])): ?>
                                <div class="text-sm text-gray-600 mt-2">
                                    <strong>Notes:</strong>
                                    <?php
                                        $__notes = $treatment['treatment_notes'] ?? '';
                                        if (function_exists('character_limiter')) {
                                            echo esc(character_limiter($__notes, 120));
                                        } else {
                                            $__lim = 120;
                                            $__trunc = mb_substr($__notes, 0, $__lim);
                                            echo esc(mb_strlen($__notes) > $__lim ? $__trunc . '...' : $__trunc);
                                        }
                                    ?>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($treatment['treatment_status'])): ?>
                                <div class="text-sm mt-2">
                                    <span class="px-2 py-1 rounded text-xs font-medium
                                        <?php 
                                        switch($treatment['treatment_status']) {
                                            case 'completed': echo 'bg-green-100 text-green-800'; break;
                                            case 'in_progress': echo 'bg-yellow-100 text-yellow-800'; break;
                                            case 'paused': echo 'bg-blue-100 text-blue-800'; break;
                                            case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                            default: echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', esc($treatment['treatment_status'] ?? 'unknown'))); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="ml-4">
                            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View Details
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-8">
            <div class="text-gray-400 mb-2">
                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <p class="text-gray-500">No treatment records found for this patient.</p>
        </div>
    <?php endif; ?>

<?php elseif ($tab === 'prescriptions'): ?>
    <h2 class="text-xl font-semibold mb-4">Prescriptions</h2>
    <?php if (!empty($tabData)): ?>
        <div class="space-y-3">
            <?php foreach ($tabData as $prescription): ?>
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="font-semibold text-lg text-gray-900">
                                Prescription #<?php echo esc($prescription['id']); ?>
                            </div>
                            <div class="text-sm text-gray-600 mt-1">
                                <span class="inline-block mr-4">
                                    <strong>Issue Date:</strong> <?php echo date('M d, Y', strtotime($prescription['issue_date'] ?? $prescription['created_at'])); ?>
                                </span>
                                <span class="inline-block mr-4">
                                    <strong>Issued by:</strong> <?php echo esc($prescription['issued_by_name'] ?? 'Unknown'); ?>
                                </span>
                            </div>
                            
                            <?php if (!empty($prescription['items'])): ?>
                                <div class="mt-3">
                                    <strong class="text-sm text-gray-700">Medications:</strong>
                                    <ul class="mt-1 text-sm text-gray-600">
                                        <?php foreach ($prescription['items'] as $item): ?>
                                            <li class="flex justify-between py-1">
                                                <span><?php echo esc($item['medicine_name'] ?? $item['medication']); ?></span>
                                                <span class="text-gray-500">
                                                    <?php echo esc($item['dosage'] ?? ''); ?> 
                                                    <?php if (!empty($item['quantity'])): ?>
                                                        (Qty: <?php echo esc($item['quantity']); ?>)
                                                    <?php endif; ?>
                                                </span>
                                            </li>
                                            <?php if (!empty($item['instructions'])): ?>
                                                <li class="text-xs text-gray-500 pl-4 mb-1">
                                                    <?php echo esc($item['instructions']); ?>
                                                </li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($prescription['notes'])): ?>
                                <div class="text-sm text-gray-600 mt-2">
                                    <strong>Notes:</strong>
                                    <?php
                                        $__notes = $prescription['notes'] ?? '';
                                        if (function_exists('character_limiter')) {
                                            echo esc(character_limiter($__notes, 120));
                                        } else {
                                            $__lim = 120;
                                            $__trunc = mb_substr($__notes, 0, $__lim);
                                            echo esc(mb_strlen($__notes) > $__lim ? $__trunc . '...' : $__trunc);
                                        }
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="ml-4 flex flex-col space-y-2">
                            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View Details
                            </button>
                            <button class="text-green-600 hover:text-green-800 text-sm font-medium">
                                Download PDF
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-8">
            <div class="text-gray-400 mb-2">
                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
            </div>
            <p class="text-gray-500">No prescriptions found for this patient.</p>
        </div>
    <?php endif; ?>

<?php elseif ($tab === 'invoices'): ?>
    <h2 class="text-xl font-semibold mb-4">Invoices</h2>
    <?php if (!empty($tabData)): ?>
        <div class="space-y-3">
            <?php foreach ($tabData as $invoice): ?>
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="font-semibold text-lg text-gray-900">
                                Invoice #<?php echo esc($invoice['invoice_number'] ?? $invoice['id']); ?>
                            </div>
                            <div class="text-sm text-gray-600 mt-1">
                                <span class="inline-block mr-4">
                                    <strong>Date:</strong> <?php echo date('M d, Y', strtotime($invoice['created_at'] ?? $invoice['invoice_date'])); ?>
                                </span>
                                <?php if (!empty($invoice['due_date'])): ?>
                                    <span class="inline-block mr-4">
                                        <strong>Due Date:</strong> <?php echo date('M d, Y', strtotime($invoice['due_date'])); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mt-2 grid grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Total Amount:</span>
                                    <div class="font-semibold">₱<?php echo number_format($invoice['total_amount'] ?? $invoice['amount'] ?? 0, 2); ?></div>
                                </div>
                                <?php if (isset($invoice['discount']) && $invoice['discount'] > 0): ?>
                                    <div>
                                        <span class="text-gray-600">Discount:</span>
                                        <div class="font-semibold text-green-600">₱<?php echo number_format($invoice['discount'], 2); ?></div>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <span class="text-gray-600">Amount Due:</span>
                                    <div class="font-semibold text-blue-600">₱<?php echo number_format($invoice['final_amount'] ?? ($invoice['total_amount'] ?? $invoice['amount'] ?? 0) - ($invoice['discount'] ?? 0), 2); ?></div>
                                </div>
                            </div>
                            
                            <?php if (isset($invoice['status'])): ?>
                                <div class="text-sm mt-2">
                                    <span class="px-2 py-1 rounded text-xs font-medium
                                        <?php 
                                        switch($invoice['status']) {
                                            case 'paid': echo 'bg-green-100 text-green-800'; break;
                                            case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                            case 'overdue': echo 'bg-red-100 text-red-800'; break;
                                            case 'cancelled': echo 'bg-gray-100 text-gray-800'; break;
                                            default: echo 'bg-blue-100 text-blue-800';
                                        }
                                        ?>">
                                        <?php echo ucfirst(esc($invoice['status'] ?? 'pending')); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($invoice['notes'])): ?>
                                <div class="text-sm text-gray-600 mt-2">
                                    <strong>Notes:</strong>
                                    <?php
                                        $__notes = $invoice['notes'] ?? '';
                                        if (function_exists('character_limiter')) {
                                            echo esc(character_limiter($__notes, 120));
                                        } else {
                                            $__lim = 120;
                                            $__trunc = mb_substr($__notes, 0, $__lim);
                                            echo esc(mb_strlen($__notes) > $__lim ? $__trunc . '...' : $__trunc);
                                        }
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="ml-4 flex flex-col space-y-2">
                            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View Invoice
                            </button>
                            <button class="text-green-600 hover:text-green-800 text-sm font-medium">
                                Download PDF
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-8">
            <div class="text-gray-400 mb-2">
                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <p class="text-gray-500">No invoices found for this patient.</p>
        </div>
    <?php endif; ?>

<?php elseif ($tab === 'dental_charts'): ?>
    <h2 class="text-xl font-semibold mb-4">Dental Charts</h2>
    <?php if (!empty($tabData)): ?>
        <div class="space-y-3">
            <?php foreach ($tabData as $chart): ?>
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="font-semibold text-lg text-gray-900">
                                Chart #<?php echo esc($chart['id']); ?>
                            </div>
                            <div class="text-sm text-gray-600 mt-1">
                                <span class="inline-block mr-4">
                                    <strong>Date:</strong> <?php echo date('M d, Y', strtotime($chart['record_date'] ?? $chart['created_at'])); ?>
                                </span>
                                <?php if (!empty($chart['dentist_name'])): ?>
                                    <span class="inline-block mr-4">
                                        <strong>Dentist:</strong> <?php echo esc($chart['dentist_name']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($chart['tooth_number'])): ?>
                                <div class="text-sm text-gray-600 mt-1">
                                    <strong>Tooth:</strong> <?php echo esc($chart['tooth_number']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($chart['condition']) || !empty($chart['treatment'])): ?>
                                <div class="mt-2 text-sm">
                                    <?php if (!empty($chart['condition'])): ?>
                                        <div><strong>Condition:</strong> <?php echo esc($chart['condition']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($chart['treatment'])): ?>
                                        <div><strong>Treatment:</strong> <?php echo esc($chart['treatment']); ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($chart['notes'])): ?>
                                <div class="text-sm text-gray-600 mt-2">
                                    <strong>Notes:</strong>
                                    <?php
                                        $__notes = $chart['notes'] ?? '';
                                        if (function_exists('character_limiter')) {
                                            echo esc(character_limiter($__notes, 120));
                                        } else {
                                            $__lim = 120;
                                            $__trunc = mb_substr($__notes, 0, $__lim);
                                            echo esc(mb_strlen($__notes) > $__lim ? $__trunc . '...' : $__trunc);
                                        }
                                    ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($chart['status'])): ?>
                                <div class="text-sm mt-2">
                                    <span class="px-2 py-1 rounded text-xs font-medium
                                        <?php 
                                        switch($chart['status']) {
                                            case 'completed': echo 'bg-green-100 text-green-800'; break;
                                            case 'in_progress': echo 'bg-yellow-100 text-yellow-800'; break;
                                            case 'planned': echo 'bg-blue-100 text-blue-800'; break;
                                            default: echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?php echo ucfirst(esc($chart['status'] ?? 'unknown')); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="ml-4">
                            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View Chart
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-8">
            <div class="text-gray-400 mb-2">
                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-gray-500">No dental charts found for this patient.</p>
        </div>
    <?php endif; ?>

<?php elseif ($tab === 'medical_history'): ?>
    <h2 class="text-xl font-semibold mb-4">Medical History</h2>
    <?php if (!empty($tabData)): ?>
        <div class="space-y-3">
            <?php foreach ($tabData as $history): ?>
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="font-semibold text-lg text-gray-900">
                                Medical Record #<?php echo esc($history['id']); ?>
                            </div>
                            <div class="text-sm text-gray-600 mt-1">
                                <span class="inline-block mr-4">
                                    <strong>Date:</strong> <?php echo date('M d, Y', strtotime($history['created_at'] ?? $history['last_dental_visit'])); ?>
                                </span>
                                <?php if (!empty($history['previous_dentist'])): ?>
                                    <span class="inline-block mr-4">
                                        <strong>Previous Dentist:</strong> <?php echo esc($history['previous_dentist']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($history['allergies'])): ?>
                                <div class="mt-2">
                                    <strong class="text-sm text-red-700">Allergies:</strong>
                                    <div class="text-sm text-red-600"><?php echo esc($history['allergies']); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($history['physician_name'])): ?>
                                <div class="mt-2">
                                    <strong class="text-sm text-gray-700">Physician:</strong>
                                    <div class="text-sm text-gray-600">
                                        <?php echo esc($history['physician_name']); ?>
                                        <?php if (!empty($history['physician_specialty'])): ?>
                                            (<?php echo esc($history['physician_specialty']); ?>)
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($history['medical_conditions'])): ?>
                                <div class="mt-2">
                                    <strong class="text-sm text-gray-700">Medical Conditions:</strong>
                                    <div class="text-sm text-gray-600"><?php echo esc($history['medical_conditions']); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($history['other_conditions'])): ?>
                                <div class="mt-2">
                                    <strong class="text-sm text-gray-700">Other Conditions:</strong>
                                    <div class="text-sm text-gray-600"><?php echo esc($history['other_conditions']); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="ml-4">
                            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View Details
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-8">
            <div class="text-gray-400 mb-2">
                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <p class="text-gray-500">No medical history found for this patient.</p>
        </div>
    <?php endif; ?>

<?php else: ?>
    <div class="text-center py-8">
        <p class="text-gray-500">Invalid tab selected.</p>
    </div>
<?php endif; ?>
