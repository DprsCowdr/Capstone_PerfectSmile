<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CreateTestInvoicesAndPrescriptions extends BaseCommand
{
    protected $group = 'app';
    protected $name = 'app:create-test-invoices-prescriptions';
    protected $description = 'Create test invoices and prescriptions for testing';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        CLI::write('=== Creating Test Invoices and Prescriptions ===', 'green');
        CLI::newLine();

        try {
            // First, create a test procedure if none exists
            $existingProcedures = $db->query("SELECT id FROM procedures LIMIT 1")->getResultArray();
            $procedureId = null;
            
            if (empty($existingProcedures)) {
                CLI::write("Creating test procedure...", 'yellow');
                $testProcedureData = [
                    'user_id' => 1, // Assuming admin user exists
                    'procedure_name' => 'General Dental Consultation',
                    'title' => 'General Consultation',
                    'description' => 'Basic dental consultation and examination',
                    'category' => 'consultation',
                    'fee' => 500.00,
                    'treatment_area' => 'general',
                    'status' => 'completed',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'procedure_date' => date('Y-m-d')
                ];
                
                $result = $db->table('procedures')->insert($testProcedureData);
                if ($result) {
                    $procedureId = $db->insertID();
                    CLI::write("✓ Created test procedure with ID: $procedureId");
                }
            } else {
                $procedureId = $existingProcedures[0]['id'];
                CLI::write("Using existing procedure ID: $procedureId", 'yellow');
            }

            // Get some existing patients
            $patients = $db->query("SELECT id, name FROM user WHERE user_type = 'patient' LIMIT 3")->getResultArray();
            
            if (empty($patients)) {
                CLI::error("No patients found in database!");
                return;
            }

            CLI::write("Found " . count($patients) . " patients for testing", 'yellow');

            // Get some procedures and services
            $procedures = $db->query("SELECT id, procedure_name as name FROM procedures LIMIT 3")->getResultArray();
            $services = $db->query("SELECT id, name FROM services LIMIT 3")->getResultArray();
            $proceduresAndServices = array_merge($procedures, $services);
            
            // Create test invoices
            $invoicesCreated = 0;
            foreach ($patients as $patient) {
                for ($i = 0; $i < 2; $i++) {
                    $totalAmount = rand(100, 1000);
                    $discount = rand(0, 100);
                    $finalAmount = $totalAmount - $discount;
                    
                    $invoiceData = [
                        'invoice_number' => 'INV-' . date('Ymd') . '-' . str_pad($invoicesCreated + 1, 4, '0', STR_PAD_LEFT),
                        'procedure_id' => $procedureId, // Use the valid procedure ID
                        'total_amount' => $totalAmount,
                        'discount' => $discount,
                        'patient_id' => $patient['id'],
                        'final_amount' => $finalAmount,
                        'created_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days')),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    $result = $db->table('invoices')->insert($invoiceData);
                    if ($result) {
                        $invoicesCreated++;
                        CLI::write("✓ Created invoice {$invoiceData['invoice_number']} for {$patient['name']} - ₱{$finalAmount}");
                    }
                }
            }

            // Create test payments
            $paymentsCreated = 0;
            $paymentMethods = ['cash', 'credit_card', 'bank_transfer', 'check'];
            $paymentStatuses = ['paid', 'partial', 'pending'];

            // Get some appointment IDs to use
            $appointments = $db->query("SELECT id FROM appointments LIMIT 5")->getResultArray();
            $appointmentId = !empty($appointments) ? $appointments[0]['id'] : 1; // Use first available or fallback to 1

            // Get admin user ID for payment_received_by
            $adminUser = $db->query("SELECT id FROM user WHERE user_type = 'admin' LIMIT 1")->getRow();
            $adminUserId = $adminUser ? $adminUser->id : 1; // Fallback to ID 1

            foreach ($patients as $patient) {
                for ($i = 0; $i < 2; $i++) {
                    $totalAmount = rand(500, 2000);
                    $paidAmount = rand(100, $totalAmount);
                    $balanceAmount = $totalAmount - $paidAmount;
                    
                    $paymentData = [
                        'appointment_id' => $appointmentId, // Use valid appointment ID
                        'patient_id' => $patient['id'],
                        'payment_status' => $balanceAmount > 0 ? 'partial' : 'paid',
                        'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                        'total_amount' => $totalAmount,
                        'paid_amount' => $paidAmount,
                        'balance_amount' => $balanceAmount,
                        'payment_date' => date('Y-m-d', strtotime('-' . rand(1, 30) . ' days')),
                        'payment_received_by' => $adminUserId, // Use valid user ID
                        'payment_notes' => 'Test payment entry',
                        'invoice_number' => 'INV-' . date('Ymd') . '-' . str_pad($paymentsCreated + 1, 4, '0', STR_PAD_LEFT),
                        'receipt_number' => 'REC-' . date('Ymd') . '-' . str_pad($paymentsCreated + 1, 4, '0', STR_PAD_LEFT),
                        'transaction_reference' => 'TXN' . time() . rand(100, 999),
                        'discount_amount' => rand(0, 50),
                        'discount_reason' => rand(0, 1) ? 'Senior Citizen Discount' : null,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    $result = $db->table('payments')->insert($paymentData);
                    if ($result) {
                        $paymentsCreated++;
                        CLI::write("✓ Created payment {$paymentData['receipt_number']} for {$patient['name']} - ₱{$paidAmount}/{$totalAmount}");
                    }
                }
            }

            // Get some dentists
            $dentists = $db->query("SELECT id, name FROM user WHERE user_type = 'dentist' LIMIT 2")->getResultArray();
            
            // Create test prescriptions
            $prescriptionsCreated = 0;
            $prescriptionStatuses = ['active', 'completed', 'expired'];
            $sampleNotes = [
                'Take medication after meals. Return for follow-up in 2 weeks.',
                'Apply topical gel twice daily. Avoid hard foods.',
                'Pain medication as needed. Call if symptoms persist.',
                'Antibiotic course - complete full dosage. Follow-up required.',
                'Rinse with prescribed mouthwash twice daily.'
            ];

            foreach ($patients as $patient) {
                $dentist = $dentists[array_rand($dentists)] ?? null;
                if (!$dentist) continue;

                $prescriptionData = [
                    'dentist_id' => $dentist['id'],
                    'dentist_name' => $dentist['name'],
                    'license_no' => 'LIC-' . rand(10000, 99999),
                    'ptr_no' => 'PTR-' . rand(100000, 999999),
                    'patient_id' => $patient['id'],
                    'appointment_id' => null, // Can be null
                    'issue_date' => date('Y-m-d', strtotime('-' . rand(1, 30) . ' days')),
                    'next_appointment' => rand(0, 1) ? date('Y-m-d', strtotime('+' . rand(7, 30) . ' days')) : null,
                    // Clinic does not use prescription status in UI; leave blank
                    'status' => null,
                    'notes' => $sampleNotes[array_rand($sampleNotes)],
                    'signature_url' => null, // Could add actual signature URLs if needed
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $result = $db->table('prescriptions')->insert($prescriptionData);
                if ($result) {
                    $prescriptionsCreated++;
                    CLI::write("✓ Created prescription for {$patient['name']} by Dr. {$dentist['name']} - {$prescriptionData['status']}");
                }
            }

            CLI::newLine();
            CLI::write("SUCCESS: Created test data!", 'green');
            CLI::write("- Invoices: $invoicesCreated", 'white');
            CLI::write("- Payments: $paymentsCreated", 'white');
            CLI::write("- Prescriptions: $prescriptionsCreated", 'white');
            CLI::newLine();
            CLI::write("You can now test the Invoice History and Prescriptions tabs in admin records!", 'yellow');

        } catch (\Exception $e) {
            CLI::error("ERROR: " . $e->getMessage());
        }
    }
}
