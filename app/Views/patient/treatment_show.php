<?php echo view('templates/header', ['title' => 'Treatment Record']); ?>
<?php echo view('templates/sidebar', ['active' => 'records']); ?>

<div class="main-content" data-sidebar-offset>
    <?php echo view('templates/patient_topbar'); ?>

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-semibold mb-4">Treatment Record</h1>

        <?php if (!empty($record)): ?>
            <div class="p-4 border rounded">
                <div class="mb-2"><strong>Date:</strong> <?php echo date('M d, Y', strtotime($record['record_date'] ?? $record['created_at'] ?? '')); ?></div>
                <div class="mb-2"><strong>Tooth / Area:</strong> <?php echo esc($record['tooth'] ?? $record['area'] ?? 'N/A'); ?></div>
                <div class="mb-2"><strong>Procedure:</strong> <?php echo esc($record['procedure_name'] ?? $record['procedure'] ?? ''); ?></div>
                <div class="mb-2"><strong>Dentist:</strong> <?php echo esc($record['dentist_name'] ?? $record['dentist_id'] ?? ''); ?></div>
                <div class="mb-2"><strong>Notes:</strong>
                    <div class="mt-1 text-sm text-gray-700"><?php echo nl2br(esc($record['summary'] ?? $record['notes'] ?? '')); ?></div>
                </div>
            </div>
        <?php else: ?>
            <div class="p-4 bg-yellow-50 border rounded">Record not found.</div>
        <?php endif; ?>
    </div>
</div>

<?php echo view('templates/footer'); ?>
