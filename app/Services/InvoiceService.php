<?php

namespace App\Services;

use App\Models\InvoiceModel;
use App\Models\InvoiceItemModel;
use App\Models\ProcedureModel;
use App\Models\UserModel;

class InvoiceService
{
    protected $invoiceModel;
    protected $invoiceItemModel;
    protected $procedureModel;
    protected $userModel;

    public function __construct()
    {
        $this->invoiceModel = new InvoiceModel();
        $this->invoiceItemModel = new InvoiceItemModel();
        $this->procedureModel = new ProcedureModel();
        $this->userModel = new UserModel();
    }

    /**
     * Get all invoices with pagination and search
     */
    public function getAllInvoices($page = 1, $limit = 10, $search = null, $status = null)
    {
        return $this->invoiceModel->getAllInvoicesWithDetails($page, $limit, $search, $status);
    }

    /**
     * Create new invoice
     */
    public function createInvoice($data)
    {
        try {
            // Generate invoice number
            $data['invoice_number'] = $this->invoiceModel->generateInvoiceNumber();
            
            // Set default values
            $data['subtotal'] = 0;
            $data['tax_amount'] = 0;
            $data['discount_amount'] = 0;
            $data['total_amount'] = 0;
            $data['paid_amount'] = 0;
            $data['balance_amount'] = 0;
            $data['status'] = 'draft';
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');

            $invoiceId = $this->invoiceModel->insert($data);
            
            if ($invoiceId) {
                return [
                    'success' => true,
                    'message' => 'Invoice created successfully.',
                    'invoice_id' => $invoiceId
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to create invoice.'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error creating invoice: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get invoice details with items
     */
    public function getInvoiceDetails($id)
    {
        $invoice = $this->invoiceModel->getInvoiceWithDetails($id);
        
        if (!$invoice) {
            return [
                'success' => false,
                'message' => 'Invoice not found.'
            ];
        }

        $items = $this->invoiceItemModel->getItemsByInvoice($id);
        $totals = $this->invoiceItemModel->getInvoiceTotals($id);

        return [
            'success' => true,
            'data' => [
                'invoice' => $invoice,
                'items' => $items,
                'totals' => $totals
            ]
        ];
    }

    /**
     * Update invoice
     */
    public function updateInvoice($id, $data)
    {
        try {
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            $result = $this->invoiceModel->update($id, $data);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Invoice updated successfully.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update invoice.'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error updating invoice: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete invoice
     */
    public function deleteInvoice($id)
    {
        try {
            // Delete invoice items first
            $this->invoiceItemModel->where('invoice_id', $id)->delete();
            
            // Delete invoice
            $result = $this->invoiceModel->delete($id);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Invoice deleted successfully.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete invoice.'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error deleting invoice: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Add item to invoice
     */
    public function addInvoiceItem($invoiceId, $itemData)
    {
        try {
            $itemId = $this->invoiceItemModel->addItem($itemData);
            
            if ($itemId) {
                // Update invoice totals
                $this->updateInvoiceTotals($invoiceId);
                
                return [
                    'success' => true,
                    'message' => 'Item added successfully.',
                    'item_id' => $itemId
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to add item.'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error adding item: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update invoice item
     */
    public function updateInvoiceItem($itemId, $itemData)
    {
        try {
            $result = $this->invoiceItemModel->updateItem($itemId, $itemData);
            
            if ($result) {
                // Update invoice totals
                $this->updateInvoiceTotals($itemData['invoice_id']);
                
                return [
                    'success' => true,
                    'message' => 'Item updated successfully.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update item.'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error updating item: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete invoice item
     */
    public function deleteInvoiceItem($itemId, $invoiceId)
    {
        try {
            $result = $this->invoiceItemModel->deleteItem($itemId);
            
            if ($result) {
                // Update invoice totals
                $this->updateInvoiceTotals($invoiceId);
                
                return [
                    'success' => true,
                    'message' => 'Item deleted successfully.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete item.'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error deleting item: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update invoice totals
     */
    public function updateInvoiceTotals($invoiceId)
    {
        $totals = $this->invoiceItemModel->getInvoiceTotals($invoiceId);
        
        $updateData = [
            'subtotal' => $totals['subtotal'],
            'tax_amount' => $totals['total_tax'],
            'discount_amount' => $totals['total_discount'],
            'total_amount' => $totals['total_amount'],
            'balance_amount' => $totals['total_amount'] - $this->invoiceModel->find($invoiceId)['paid_amount'],
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->invoiceModel->update($invoiceId, $updateData);
    }

    /**
     * Record payment
     */
    public function recordPayment($invoiceId, $amount)
    {
        try {
            $result = $this->invoiceModel->recordPayment($invoiceId, $amount);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Payment recorded successfully.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to record payment.'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error recording payment: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get patients for invoice creation
     */
    public function getPatients()
    {
        return $this->userModel->where('user_type', 'patient')->findAll();
    }

    /**
     * Get procedures for invoice creation
     */
    public function getProcedures()
    {
        return $this->procedureModel->findAll();
    }

    /**
     * Get invoice statistics
     */
    public function getInvoiceStats()
    {
        return $this->invoiceModel->getInvoiceStats();
    }

    /**
     * Get overdue invoices
     */
    public function getOverdueInvoices()
    {
        return $this->invoiceModel->getOverdueInvoices();
    }

    /**
     * Create invoice from procedure
     */
    public function createInvoiceFromProcedure($procedureId, $patientId, $createdBy)
    {
        try {
            $procedure = $this->procedureModel->find($procedureId);
            if (!$procedure) {
                return [
                    'success' => false,
                    'message' => 'Procedure not found.'
                ];
            }

            // Create invoice
            $invoiceData = [
                'patient_id' => $patientId,
                'procedure_id' => $procedureId,
                'subtotal' => $procedure['fee'] ?? 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => $procedure['fee'] ?? 0,
                'paid_amount' => 0,
                'balance_amount' => $procedure['fee'] ?? 0,
                'status' => 'draft',
                'due_date' => date('Y-m-d', strtotime('+30 days')),
                'payment_terms' => 'Net 30',
                'notes' => 'Invoice created from procedure: ' . ($procedure['title'] ?? $procedure['procedure_name']),
                'created_by' => $createdBy
            ];

            $result = $this->createInvoice($invoiceData);
            
            if ($result['success']) {
                // Add procedure as invoice item
                $this->invoiceItemModel->addProcedureItem($result['invoice_id'], $procedureId, $procedure);
                
                return $result;
            } else {
                return $result;
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error creating invoice from procedure: ' . $e->getMessage()
            ];
        }
    }
}
