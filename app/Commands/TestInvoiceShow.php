<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestInvoiceShow extends BaseCommand
{
    protected $group = 'debug';
    protected $name = 'debug:test-show';
    protected $description = 'Test invoice show functionality';

    public function run(array $params)
    {
        CLI::write("=== Testing Invoice Show Functionality ===", 'yellow');

        // Test direct access to show method
        $controller = new \App\Controllers\Admin\InvoiceController();
        
        // Mock session for testing
        $session = session();
        $session->set([
            'user_id' => 1,
            'user_type' => 'admin',
            'is_logged_in' => true,
            'name' => 'Test Admin'
        ]);

        CLI::write("Session data set for testing");
        
        // Test with a known invoice ID
        $invoiceId = 4; // From our earlier debug
        
        CLI::write("Testing show method with invoice ID: {$invoiceId}");
        
        try {
            $response = $controller->show($invoiceId);
            
            if ($response instanceof \CodeIgniter\HTTP\RedirectResponse) {
                CLI::write("Result: Redirect response", 'red');
                CLI::write("Redirect URL: " . $response->getHeaderLine('Location'));
            } else {
                CLI::write("Result: View rendered successfully", 'green');
            }
            
        } catch (\Exception $e) {
            CLI::write("Error: " . $e->getMessage(), 'red');
            CLI::write("Stack trace: " . $e->getTraceAsString());
        }

        // Check log file for debug messages
        CLI::write("\n=== Checking recent log entries ===", 'yellow');
        $logPath = WRITEPATH . 'logs/log-' . date('Y-m-d') . '.log';
        if (file_exists($logPath)) {
            $logContent = file_get_contents($logPath);
            $lines = explode("\n", $logContent);
            $recentLines = array_slice($lines, -20); // Get last 20 lines
            
            foreach ($recentLines as $line) {
                if (strpos($line, 'InvoiceController::show') !== false) {
                    CLI::write($line);
                }
            }
        } else {
            CLI::write("Log file not found: {$logPath}");
        }

        CLI::write("\nDone.", 'green');
    }
}
