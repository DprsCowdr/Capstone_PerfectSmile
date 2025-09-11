<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\InvoiceService;
use App\Models\InvoiceModel;

class DebugInvoiceStructure extends BaseCommand
{
    protected $group = 'debug';
    protected $name = 'debug:invoice-structure';
    protected $description = 'Debug invoice data structure';

    public function run(array $params)
    {
        CLI::write('=== INVOICE DATA STRUCTURE DEBUG ===', 'yellow');
        
        $invoiceService = new InvoiceService();
        
        // Get invoice details for ID 4 (or first available invoice)
        $result = $invoiceService->getInvoiceDetails(4);
        
        if ($result['success']) {
            CLI::write('SUCCESS: Invoice found', 'green');
            CLI::newLine();
            
            CLI::write('=== INVOICE FIELDS ===', 'yellow');
            $invoice = $result['data']['invoice'];
            foreach ($invoice as $key => $value) {
                if (is_array($value)) {
                    CLI::write("$key: [array with " . count($value) . " items]");
                } else {
                    CLI::write("$key: " . (is_null($value) ? 'NULL' : $value));
                }
            }
            
            CLI::newLine();
            CLI::write('=== ITEMS ===', 'yellow');
            $items = $result['data']['items'];
            CLI::write('Items count: ' . count($items));
            if (!empty($items)) {
                CLI::write('First item fields:');
                foreach ($items[0] as $key => $value) {
                    CLI::write("  $key: " . (is_null($value) ? 'NULL' : $value));
                }
            }
            
            CLI::newLine();
            CLI::write('=== TOTALS ===', 'yellow');
            $totals = $result['data']['totals'];
            foreach ($totals as $key => $value) {
                CLI::write("$key: $value");
            }
            
        } else {
            CLI::write('ERROR: ' . $result['message'], 'red');
            
            // Try to get any invoice
            CLI::newLine();
            CLI::write('=== TRYING TO GET ANY INVOICE ===', 'yellow');
            $invoiceModel = new InvoiceModel();
            $firstInvoice = $invoiceModel->first();
            
            if ($firstInvoice) {
                CLI::write('Found invoice with ID: ' . $firstInvoice['id'], 'green');
                CLI::write('Fields in raw invoice:');
                foreach ($firstInvoice as $key => $value) {
                    CLI::write("  $key: " . (is_null($value) ? 'NULL' : $value));
                }
            } else {
                CLI::write('No invoices found in database', 'red');
            }
        }
        
        CLI::newLine();
        CLI::write('Done.', 'green');
    }
}
