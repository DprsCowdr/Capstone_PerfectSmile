<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #<?= esc($invoice['id'] ?? '') ?></title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:Arial, Helvetica, sans-serif;font-size:11px;color:#111}
    @page { size: 148mm 210mm; margin: 6mm; }
    .page{width:100%;padding:6mm}
    .header{display:flex;align-items:center;gap:8px;border-bottom:2px solid #2563eb;padding-bottom:6px;margin-bottom:8px}
    .clinic{font-weight:700;color:#1e40af}
        .meta{display:flex;justify-content:space-between;margin-top:6px}
        .items{width:100%;border-collapse:collapse;margin-top:8px}
        .items th{border-bottom:1px solid #ddd;padding:6px;text-align:left;font-size:10px}
        .items td{padding:6px;border-bottom:1px dashed #eee;font-size:10px}
        .totals{margin-top:8px;display:flex;justify-content:flex-end}
        .total-box{width:220px;border:1px solid #e5e7eb;padding:8px}
        .small{font-size:9px;color:#6b7280}
    </style>
</head>
<body>
        <div class="page">
        <div class="header">
            <?php
            // Prefer logo in public/img with common filenames, then public/uploads
            $candidates = ['clinic_logo.png', 'clinic-logo.png', 'clinic_logo.jpg', 'clinic-logo.jpg'];
            $found = null;
            if (defined('FCPATH')) {
                foreach ($candidates as $c) {
                    $p = FCPATH . 'img' . DIRECTORY_SEPARATOR . $c;
                    if (file_exists($p)) { $found = ['src' => base_url('img/' . $c), 'path' => $p]; break; }
                }
                if (!$found) {
                    foreach ($candidates as $c) {
                        $p = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . $c;
                        if (file_exists($p)) { $found = ['src' => base_url('uploads/' . $c), 'path' => $p]; break; }
                    }
                }
            }
            if ($found): ?>
                <img src="<?= esc($found['src']) ?>" alt="Clinic logo" style="height:48px;object-fit:contain;" />
            <?php else: ?>
                <div style="width:48px;height:48px;border-radius:4px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;color:#1f2937;font-weight:700;">PS</div>
            <?php endif; ?>
            <div style="flex:1">
                <div class="clinic">Perfect Smile Dental Clinic</div>
                <div class="small">Unit No. 201 Tansylit Bldg., Alfelor St., Brgy San Roque, Iriga City — 0946-060-6381</div>
            </div>
        </div>

        <div class="meta">
            <div>
                <div><strong>Patient:</strong> <?= esc($user['name'] ?? '') ?></div>
                <div class="small"><?= esc($user['address'] ?? '') ?></div>
            </div>
            <div style="text-align:right">
                <div><strong>Invoice #</strong> <?= esc($invoice['id'] ?? '') ?></div>
                <div class="small"><?= esc(date('M d, Y', strtotime($invoice['created_at'] ?? '' )) ) ?></div>
            </div>
        </div>

        <?php if (!empty($items)): ?>
        <table class="items">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($items as $it): ?>
                <tr>
                    <td><?= esc($it['description'] ?? $it['name'] ?? '') ?></td>
                    <td><?= esc($it['qty'] ?? $it['quantity'] ?? '') ?></td>
                    <td><?= esc($it['unit_price'] ?? $it['price'] ?? '') ?></td>
                    <td><?= esc($it['total'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="small">No line items available.</p>
        <?php endif; ?>

        <div class="totals">
            <div class="total-box">
                <div class="small">Subtotal</div>
                <div><strong><?= esc($invoice['subtotal'] ?? $invoice['amount'] ?? $invoice['total'] ?? '') ?></strong></div>
                <div class="small">Status: <?= esc($invoice['status'] ?? '') ?></div>
            </div>
        </div>
    </div>
</body>
</html>
