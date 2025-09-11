<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= esc($invoice['invoice_number'] ?? $invoice['id'] ?? '') ?> - Perfect Smile Dental</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #ffffff;
            color: #333;
            line-height: 1.5;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        .logo {
            max-height: 60px;
            margin-bottom: 10px;
        }
        .clinic-name {
            font-size: 28px;
            font-weight: bold;
            margin: 10px 0 5px 0;
        }
        .clinic-tagline {
            font-size: 14px;
            opacity: 0.9;
        }
        .invoice-details {
            display: flex;
            justify-content: space-between;
            padding: 30px;
            background-color: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
        }
        .invoice-info, .patient-info {
            flex: 1;
        }
        .invoice-info h3, .patient-info h3 {
            margin: 0 0 15px 0;
            color: #495057;
            font-size: 18px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
            display: inline-block;
        }
        .info-row {
            margin: 8px 0;
            display: flex;
        }
        .info-label {
            font-weight: bold;
            color: #6c757d;
            width: 120px;
            flex-shrink: 0;
        }
        .info-value {
            color: #495057;
        }
        .content {
            padding: 30px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        .items-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: bold;
            font-size: 14px;
        }
        .items-table td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
        }
        .items-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .items-table tr:hover {
            background-color: #e3f2fd;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals {
            margin-top: 30px;
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 5px 0;
        }
        .total-row.final {
            border-top: 2px solid #007bff;
            margin-top: 15px;
            padding-top: 15px;
            font-weight: bold;
            font-size: 18px;
            color: #007bff;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-overdue {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status-draft {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .footer {
            background-color: #343a40;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 12px;
        }
        .footer-section {
            margin: 10px 0;
        }
        .print-actions {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 10px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #545b62;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
                background-color: white;
            }
            .invoice-container {
                box-shadow: none;
                border-radius: 0;
            }
            .print-actions {
                display: none;
            }
            .items-table {
                box-shadow: none;
            }
        }
        @media (max-width: 768px) {
            .invoice-details {
                flex-direction: column;
            }
            .invoice-info {
                margin-bottom: 20px;
            }
            .items-table {
                font-size: 12px;
            }
            .items-table th,
            .items-table td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Print Actions (hidden in print) -->
    <div class="print-actions">
        <button onclick="window.print()" class="btn">
            <i class="fas fa-print"></i> Print Invoice
        </button>
        <a href="<?= base_url('admin/invoices/edit/' . ($invoice['id'] ?? '')) ?>" class="btn btn-secondary">
            <i class="fas fa-edit"></i> Edit Invoice
        </a>
        <a href="<?= base_url('admin/invoices') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Invoices
        </a>
    </div>

    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <?php 
            // Logo detection logic
            $logoFound = false;
            $logoExtensions = ['png', 'jpg', 'jpeg', 'gif', 'svg'];
            $logoNames = ['logo', 'Logo', 'LOGO'];
            $logoPaths = ['public/img/', 'public/uploads/'];
            
            foreach ($logoPaths as $path) {
                foreach ($logoNames as $name) {
                    foreach ($logoExtensions as $ext) {
                        $logoFile = $path . $name . '.' . $ext;
                        if (file_exists(FCPATH . $logoFile)) {
                            echo '<img src="' . base_url($logoFile) . '" alt="Perfect Smile Dental Logo" class="logo">';
                            $logoFound = true;
                            break 3;
                        }
                    }
                }
            }
            ?>
            
            <div class="clinic-name">Perfect Smile Dental</div>
            <div class="clinic-tagline">Your Smile, Our Passion</div>
        </div>

        <!-- Invoice & Patient Details -->
        <div class="invoice-details">
            <div class="invoice-info">
                <h3>Invoice Details</h3>
                <div class="info-row">
                    <span class="info-label">Invoice #:</span>
                    <span class="info-value"><?= esc($invoice['invoice_number'] ?? $invoice['id'] ?? '') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date:</span>
                    <span class="info-value"><?= date('F j, Y', strtotime($invoice['created_at'] ?? 'now')) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Due Date:</span>
                    <span class="info-value"><?= date('F j, Y', strtotime($invoice['due_date'] ?? '+30 days')) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">
                        <?php
                        $status = $invoice['status'] ?? 'draft';
                        $statusClass = 'status-' . $status;
                        ?>
                        <span class="status-badge <?= $statusClass ?>"><?= ucfirst($status) ?></span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Payment:</span>
                    <span class="info-value">
                        <?php
                        $paymentStatus = $invoice['payment_status'] ?? 'pending';
                        $paymentClass = 'status-' . $paymentStatus;
                        ?>
                        <span class="status-badge <?= $paymentClass ?>"><?= ucfirst($paymentStatus) ?></span>
                    </span>
                </div>
            </div>

            <div class="patient-info">
                <h3>Patient Information</h3>
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value"><?= esc($patient['name'] ?? 'N/A') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?= esc($patient['email'] ?? 'N/A') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value"><?= esc($patient['phone'] ?? 'N/A') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Address:</span>
                    <span class="info-value"><?= esc($patient['address'] ?? 'N/A') ?></span>
                </div>
            </div>
        </div>

        <!-- Invoice Content -->
        <div class="content">
            <h3 style="color: #495057; margin-bottom: 20px;">Services & Items</h3>
            
            <?php if (!empty($items)): ?>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 50%;">Description</th>
                            <th style="width: 15%;" class="text-center">Quantity</th>
                            <th style="width: 15%;" class="text-right">Unit Price</th>
                            <th style="width: 20%;" class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <strong><?= esc($item['description'] ?? 'Service Item') ?></strong>
                                <?php if (!empty($item['procedure_name'])): ?>
                                    <br><small style="color: #6c757d;">Procedure: <?= esc($item['procedure_name']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= esc($item['quantity'] ?? 1) ?></td>
                            <td class="text-right">$<?= number_format($item['unit_price'] ?? 0, 2) ?></td>
                            <td class="text-right"><strong>$<?= number_format($item['total'] ?? 0, 2) ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <p>No items found for this invoice.</p>
                </div>
            <?php endif; ?>

            <!-- Totals Section -->
            <div class="totals">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>$<?= number_format($totals['subtotal'] ?? 0, 2) ?></span>
                </div>
                
                <?php if (($totals['tax'] ?? 0) > 0): ?>
                <div class="total-row">
                    <span>Tax:</span>
                    <span>$<?= number_format($totals['tax'], 2) ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (($invoice['discount'] ?? 0) > 0): ?>
                <div class="total-row">
                    <span>Discount:</span>
                    <span>-$<?= number_format($invoice['discount'], 2) ?></span>
                </div>
                <?php endif; ?>
                
                <div class="total-row final">
                    <span>Total Amount:</span>
                    <span>$<?= number_format($invoice['total_amount'] ?? 0, 2) ?></span>
                </div>
            </div>

            <!-- Payment Information -->
            <?php if (!empty($payments)): ?>
            <div style="margin-top: 30px;">
                <h4 style="color: #495057; margin-bottom: 15px;">Payment History</h4>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Method</th>
                            <th class="text-right">Amount</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?= date('M j, Y', strtotime($payment['created_at'])) ?></td>
                            <td><?= ucfirst($payment['method'] ?? 'Cash') ?></td>
                            <td class="text-right">$<?= number_format($payment['amount'], 2) ?></td>
                            <td><?= esc($payment['notes'] ?? '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Notes Section -->
            <?php if (!empty($invoice['notes'])): ?>
            <div style="margin-top: 30px; padding: 20px; background-color: #f8f9fa; border-left: 4px solid #007bff; border-radius: 0 8px 8px 0;">
                <h4 style="color: #495057; margin-bottom: 10px;">Notes</h4>
                <p style="margin: 0; color: #6c757d;"><?= nl2br(esc($invoice['notes'])) ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-section">
                <strong>Perfect Smile Dental Clinic</strong><br>
                123 Dental Street, Health City, HC 12345<br>
                Phone: (555) 123-4567 | Email: info@perfectsmile.com
            </div>
            <div class="footer-section">
                Thank you for choosing Perfect Smile Dental for your dental care needs.
            </div>
        </div>
    </div>

    <script>
        // Auto-focus print dialog when page loads (optional)
        window.addEventListener('load', function() {
            // Uncomment the line below to auto-open print dialog
            // window.print();
        });
    </script>
</body>
</html>
