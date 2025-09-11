<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\InvoiceService;

class DebugInvoiceDisplay extends BaseCommand
{
    protected $group = 'debug';
    protected $name = 'debug:invoice-display';
    protected $description = 'Debug invoice display data';

    public function run(array $params)
    {
        $invoiceService = new InvoiceService();

        CLI::write("=== Checking Invoice Data for Edit Display ===", 'yellow');

        // Get first invoice
        $invoices = $invoiceService->getAllInvoices(1, 1);
        if (empty($invoices['invoices'])) {
            CLI::write("No invoices found in database.", 'red');
            return;
        }

        $firstInvoice = $invoices['invoices'][0];
        $invoiceId = $firstInvoice['id'];

        CLI::write("Testing invoice ID: {$invoiceId}");
        CLI::write("Invoice Number: " . ($firstInvoice['invoice_number'] ?? 'N/A'));

        // Test getInvoiceDetails (what the edit controller uses)
        $result = $invoiceService->getInvoiceDetails($invoiceId);

        CLI::write("\n=== getInvoiceDetails Result ===", 'yellow');
        CLI::write("Success: " . ($result['success'] ? 'YES' : 'NO'));

        if (!$result['success']) {
            CLI::write("Error: " . $result['message'], 'red');
            return;
        }

        $data = $result['data'];
        $invoice = $data['invoice'];
        $items = $data['items'];
        $totals = $data['totals'];

        CLI::write("\n=== Invoice Data ===", 'yellow');
        CLI::write("ID: " . ($invoice['id'] ?? 'N/A'));
        CLI::write("Patient ID: " . ($invoice['patient_id'] ?? 'N/A'));
        CLI::write("Procedure ID: " . ($invoice['procedure_id'] ?? 'N/A'));
        CLI::write("Total Amount: " . ($invoice['total_amount'] ?? 'N/A'));
        CLI::write("Discount: " . ($invoice['discount'] ?? 'N/A'));
        CLI::write("Final Amount: " . ($invoice['final_amount'] ?? 'N/A'));

        CLI::write("\n=== Items Data ===", 'yellow');
        CLI::write("Items count: " . count($items));
        foreach ($items as $i => $item) {
            CLI::write("Item {$i}: " . ($item['description'] ?? 'N/A') . " - Qty: " . ($item['quantity'] ?? 'N/A') . " - Price: " . ($item['unit_price'] ?? 'N/A') . " - Total: " . ($item['total'] ?? 'N/A'));
        }

        CLI::write("\n=== Totals Data ===", 'yellow');
        CLI::write("Subtotal: " . ($totals['subtotal'] ?? 'N/A'));
        CLI::write("Tax: " . ($totals['tax'] ?? 'N/A'));
        CLI::write("Total: " . ($totals['total'] ?? 'N/A'));

        CLI::write("\n=== Testing View Calculations ===", 'yellow');
        CLI::write("Display Subtotal: $" . number_format($totals['subtotal'] ?? ($invoice['total_amount'] ?? 0), 2));
        CLI::write("Display Discount: $" . number_format($invoice['discount'] ?? ($totals['discount'] ?? 0), 2));
        CLI::write("Display Total: $" . number_format($totals['total'] ?? (($invoice['total_amount'] ?? 0) - ($invoice['discount'] ?? 0)), 2));

        CLI::write("\n=== Testing Show Page URL ===", 'yellow');
        $showUrl = site_url('admin/invoices/show/' . $invoiceId);
        CLI::write("Show URL: {$showUrl}");

        CLI::write("\nDone.", 'green');
    }
}
