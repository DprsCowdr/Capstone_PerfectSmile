<?php

namespace App\Services;

use App\Models\InvoiceModel;
use App\Models\InvoiceItemModel;
use App\Models\UserModel;
use App\Models\ServiceModel;
use App\Models\ProcedureModel;

class InvoiceService
{
    protected $invoiceModel;
    protected $itemModel;
    // payments may not be used in some installations; handle dynamically
    protected $userModel;
    protected $serviceModel;
    protected $procedureModel;

    public function __construct()
    {
        $this->invoiceModel = new InvoiceModel();
    $this->itemModel = new InvoiceItemModel();
    // do not instantiate PaymentModel here; use it dynamically if available
        $this->userModel = new UserModel();
        $this->serviceModel = new ServiceModel();
    $this->procedureModel = new ProcedureModel();
    }

    /**
     * Find invoice by id with related items/payments and patient/procedure details
     */
    public function find($id)
    {
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) return null;

        // Get patient information if patient_id exists
        if (!empty($invoice['patient_id'])) {
            try {
                $patient = $this->userModel->find($invoice['patient_id']);
                if ($patient) {
                    $invoice['patient_name'] = $patient['name'] ?? 'Unknown Patient';
                    $invoice['patient_email'] = $patient['email'] ?? 'N/A';
                    $invoice['patient_phone'] = $patient['phone'] ?? 'N/A';
                }
            } catch (\Throwable $e) {
                log_message('error', "Error fetching patient data: " . $e->getMessage());
                $invoice['patient_name'] = 'Unknown Patient';
                $invoice['patient_email'] = 'N/A';
                $invoice['patient_phone'] = 'N/A';
            }
        }

        // Get service information if service_id exists
        if (!empty($invoice['service_id'])) {
            try {
                if ($this->serviceModel) {
                    $service = $this->serviceModel->find($invoice['service_id']);
                    if ($service) {
                        $invoice['service_name'] = $service['name'] ?? 'Unknown Service';
                        $invoice['service_description'] = $service['description'] ?? 'N/A';
                    }
                } else {
                    // Fallback if service model not available
                    $invoice['service_name'] = 'Service ID: ' . $invoice['service_id'];
                    $invoice['service_description'] = 'N/A';
                }
            } catch (\Throwable $e) {
                log_message('error', "Error fetching service data: " . $e->getMessage());
                $invoice['service_name'] = 'Unknown Service';
                $invoice['service_description'] = 'N/A';
            }
        }

        // load items and payments if tables exist
        $items = [];
        $payments = [];
        try {
            $db = \Config\Database::connect();
            $tables = $db->listTables();
            if (in_array('invoice_items', $tables)) {
                try {
                    $items = $this->itemModel->where('invoice_id', $id)->findAll();
                } catch (\Throwable $_e) {
                    $items = [];
                }
            }
            if (in_array('payments', $tables) || in_array('payment', $tables)) {
                try {
                    if (class_exists('\\App\\Models\\PaymentModel')) {
                        $pm = new \App\Models\PaymentModel();
                        $payments = $pm->where('invoice_id', $id)->findAll();
                    }
                } catch (\Throwable $_e) {
                    $payments = [];
                }
            }
        } catch (\Throwable $_e) {
            // if introspection fails, attempt to fetch but remain resilient
            try { $items = $this->itemModel->where('invoice_id', $id)->findAll(); } catch (\Throwable $__e) { $items = []; }
            try {
                if (class_exists('\\App\\Models\\PaymentModel')) {
                    $pm = new \App\Models\PaymentModel();
                    $payments = $pm->where('invoice_id', $id)->findAll();
                }
            } catch (\Throwable $__e) { $payments = []; }
        }

        $invoice['items'] = $items;
        $invoice['payments'] = $payments;

        return $invoice;
    }

    /**
     * Create an invoice with items (transaction-safe if DB supports it)
     */
    public function create(array $data)
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        // Debug: Log the incoming data
        log_message('debug', 'InvoiceService::create - incoming data: ' . json_encode($data));

    try {
            // Ensure required numeric fields exist so model validation doesn't fail on insert
            $data['total_amount'] = isset($data['total_amount']) ? $data['total_amount'] : 0;
            // DB uses `discount` column
            $data['discount'] = isset($data['discount']) ? $data['discount'] : 0;
            $data['final_amount'] = isset($data['final_amount']) ? $data['final_amount'] : $data['total_amount'];

            // Proactively remove keys that aren't actual columns in the invoices table to avoid DB errors
            try {
                $db = \Config\Database::connect();
                $tableFields = [];
                try {
                    $tableFields = $db->getFieldNames('invoices');
                } catch (\Throwable $_e) {
                    // If introspection fails, fall back to model allowedFields to avoid data loss
                    $tableFields = property_exists($this->invoiceModel, 'allowedFields') ? $this->invoiceModel->allowedFields : [];
                }

                // Ensure critical fields are always preserved
                $criticalFields = ['patient_id', 'service_id', 'total_amount', 'discount', 'final_amount', 'invoice_number'];
                foreach ($criticalFields as $field) {
                    if (!in_array($field, $tableFields)) {
                        $tableFields[] = $field;
                    }
                }

                $removed = [];
                foreach (array_keys($data) as $key) {
                    if (!in_array($key, $tableFields)) {
                        $removed[] = $key;
                        unset($data[$key]);
                    }
                }
                if (!empty($removed)) {
                    log_message('warning', 'InvoiceService::create - removed non-table fields before insert: ' . implode(', ', $removed));
                }

                // Log sanitized payload for debugging
                $logData = $data;
                if (isset($logData['password'])) unset($logData['password']);
                log_message('debug', 'InvoiceService::create - inserting invoice with payload: ' . json_encode($logData));

                // Insert invoice (initial totals are zero; will be recalculated below)
                $id = $this->invoiceModel->insert($data, true);
                if (!$id) {
                    $errors = $this->invoiceModel->errors();
                    log_message('error', 'InvoiceService::create - insert returned false. Model errors: ' . json_encode($errors));
                    return ['success' => false, 'message' => 'Invoice insert failed: ' . implode(', ', $errors)];
                }
            } catch (\Throwable $e) {
                // As a last-resort, attempt the previous unknown-column parsing and retry once
                $msg = $e->getMessage();
                $removed = [];
                if (preg_match_all("/Unknown column '([^']+)' in 'field list'/i", $msg, $m)) {
                    $removed = $m[1];
                }

                if (!empty($removed)) {
                    foreach ($removed as $col) {
                        if (array_key_exists($col, $data)) {
                            unset($data[$col]);
                        }
                    }
                    log_message('warning', 'InvoiceService::create - removed unknown columns and retrying insert: ' . implode(', ', $removed));
                    // retry once
                    $id = $this->invoiceModel->insert($data, true);
                    if (!$id) {
                        $retryErrors = $this->invoiceModel->errors();
                        log_message('error', 'InvoiceService::create - retry insert also failed. Model errors: ' . json_encode($retryErrors));
                        return ['success' => false, 'message' => 'Invoice retry insert failed: ' . implode(', ', $retryErrors)];
                    }
                } else {
                    // rethrow if it's not the unknown-column case
                    throw $e;
                }
            }

            if (!$id) return ['success' => false, 'message' => 'Failed to create invoice'];

            // Insert items
            foreach ($items as $itm) {
                $itm['invoice_id'] = $id;
                // Protect against missing keys
                $this->itemModel->insert($itm);
            }

            // Recalculate totals and update
            $totals = $this->calculateTotals($items, $data['discount'] ?? 0);
            $this->invoiceModel->update($id, [
                'total_amount' => $totals['subtotal'],
                'final_amount' => $totals['total'],
            ]);

            return ['success' => true, 'id' => $id];
        } catch (\Throwable $e) {
            log_message('error', 'InvoiceService::create failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create invoice: ' . $e->getMessage()];
        }
    }

    /**
     * Update invoice and optionally items
     */
    public function update($id, array $data)
    {
        $items = $data['items'] ?? null;
        if (isset($data['items'])) unset($data['items']);

        try {
            $this->invoiceModel->update($id, $data);

            if (is_array($items)) {
                // naive approach: delete existing items and re-insert
                $this->itemModel->where('invoice_id', $id)->delete();
                foreach ($items as $itm) {
                    $itm['invoice_id'] = $id;
                    $this->itemModel->insert($itm);
                }
                $totals = $this->calculateTotals($items, $data['discount'] ?? 0);
                $this->invoiceModel->update($id, [
                    'total_amount' => $totals['subtotal'],
                    'final_amount' => $totals['total'],
                ]);
            }

            return ['success' => true];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Delete an invoice and its items/payments
     */
    public function delete($id)
    {
        try {
            $this->itemModel->where('invoice_id', $id)->delete();
            // delete payments only if PaymentModel/class exists
            if (class_exists('\\App\\Models\\PaymentModel')) {
                try {
                    $pm = new \App\Models\PaymentModel();
                    $pm->where('invoice_id', $id)->delete();
                } catch (\Throwable $_e) {
                    // ignore
                }
            }
            $this->invoiceModel->delete($id);
            return ['success' => true];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Record a payment and update invoice payment status
     */
    public function recordPayment($invoiceId, $amount, $method = 'cash', $notes = '')
    {
        try {
            // If payments table/model exists, insert a payment record and compute total paid.
            $totalPaid = 0;
            if (class_exists('\\App\\Models\\PaymentModel')) {
                $pm = new \App\Models\PaymentModel();
                $pm->insert([
                    'invoice_id' => $invoiceId,
                    'amount' => $amount,
                    'method' => $method,
                    'notes' => $notes,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                try {
                    $totalPaid = $pm->select('SUM(amount) as paid')->where('invoice_id', $invoiceId)->get()->getRow()->paid ?? 0;
                } catch (\Throwable $_e) {
                    $totalPaid = 0;
                }
            } else {
                // No payments table: update an invoice-level paid amount field if present
                $invoice = $this->invoiceModel->find($invoiceId);
                $prevPaid = floatval($invoice['paid_amount'] ?? $invoice['amount_paid'] ?? 0);
                $totalPaid = $prevPaid + floatval($amount);
                // attempt to update known invoice fields
                try {
                    if (array_key_exists('paid_amount', $this->invoiceModel->allowedFields ?? [])) {
                        $this->invoiceModel->update($invoiceId, ['paid_amount' => $totalPaid]);
                    } else {
                        // try common field names
                        $this->invoiceModel->update($invoiceId, ['amount_paid' => $totalPaid]);
                    }
                } catch (\Throwable $__e) {
                    // ignore update errors; best-effort
                }
            }

            // fetch invoice and compute remaining due
            $invoice = $this->invoiceModel->find($invoiceId);
            $totalAmount = floatval($invoice['total_amount'] ?? $invoice['final_amount'] ?? $invoice['amount'] ?? 0);

            return ['success' => true, 'paid' => $totalPaid, 'due' => max(0, $totalAmount - $totalPaid)];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Simple totals calculation: subtotal, tax (10%), total
     */
    public function calculateTotals(array $items, $discount = 0)
    {
        $subtotal = 0;
        foreach ($items as $i) {
            $qty = isset($i['quantity']) ? (float) $i['quantity'] : (isset($i['qty']) ? (float) $i['qty'] : 1);
            $price = isset($i['unit_price']) ? (float) $i['unit_price'] : (float) ($i['price'] ?? 0);
            $subtotal += $qty * $price;
        }

        $discount = (float) $discount;
        // Tax is not used; total is subtotal minus discount
        $tax = 0;
        $total = $subtotal - $discount;
        if ($total < 0) $total = 0;

        return ['subtotal' => round($subtotal, 2), 'tax' => round($tax, 2), 'total' => round($total, 2)];
    }

    /**
     * Basic stats for admin dashboard
     */
    public function getStats()
    {
        try {
            // Use a fresh model instance to avoid query builder conflicts
            $freshModel = new \App\Models\InvoiceModel();
            $total = $freshModel->countAllResults();
            // Note: status columns removed, so just return total count
            return ['total' => $total, 'paid' => 0, 'unpaid' => 0];
        } catch (\Throwable $e) {
            return ['total' => 0, 'paid' => 0, 'unpaid' => 0];
        }
    }

    /**
     * Controller-friendly wrapper: get paginated invoices with optional search/status
     */
    public function getAllInvoices($page = 1, $limit = 10, $search = null, $status = null)
    {
        try {
            $offset = max(0, ((int)$page - 1) * (int)$limit);

            $builder = $this->invoiceModel->select('invoices.*, user.name as patient_name, user.email as patient_email')
                                        ->join('user', 'user.id = invoices.patient_id', 'left');

            // Note: status filtering removed since status column was dropped

            if ($search) {
                $builder->groupStart()
                        ->like('user.name', $search)
                        ->orLike('user.email', $search)
                        ->orLike('invoices.id', $search)
                        ->orLike('invoices.invoice_number', $search)
                        ->groupEnd();
            }

            $total = $builder->countAllResults(false);

            $invoices = $builder->orderBy('invoices.created_at', 'DESC')
                                ->limit((int)$limit, (int)$offset)
                                ->get()
                                ->getResultArray();

            $pages = $limit > 0 ? (int) ceil($total / $limit) : 1;

            return [
                'invoices' => $invoices,
                'total' => $total,
                'pages' => $pages,
                'current_page' => (int)$page
            ];
        } catch (\Throwable $e) {
            return ['invoices' => [], 'total' => 0, 'pages' => 0, 'current_page' => 1];
        }
    }

    /**
     * Alias for controller
     */
    public function getInvoiceStats()
    {
        return $this->getStats();
    }

    public function getPatients()
    {
        try {
            return $this->userModel->where('user_type', 'patient')->where('status', 'active')->findAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getServices()
    {
        try {
            // Fetch all services from services table
            $services = $this->serviceModel->findAll();
            $result = [];
            foreach ($services as $s) {
                $result[] = [
                    'id' => $s['id'],
                    'service_name' => $s['name'] ?? 'Service',
                    'name' => $s['name'] ?? 'Service',
                    'description' => $s['description'] ?? '',
                    'price' => $s['price'] ?? 0
                ];
            }
            return $result;
        } catch (\Throwable $e) {
            log_message('error', 'Error fetching services: ' . $e->getMessage());
            return [];
        }
    }

    public function createInvoice(array $data)
    {
        $items = $data['items'] ?? [];
        
        // No field mapping needed - the database actually uses patient_id and procedure_id
        // which matches what the form sends
        
        // Calculate totals if items are provided
        if (!empty($items)) {
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += floatval($item['total'] ?? 0);
            }
            $discount = floatval($data['discount'] ?? 0);
            $finalAmount = max(0, $subtotal - $discount);
            $data['total_amount'] = $subtotal;
            $data['final_amount'] = $finalAmount;
        } else {
            // If no items, try to get service price
            if (isset($data['service_id'])) {
                try {
                    $service = $this->serviceModel->find($data['service_id']);
                    if ($service) {
                        $price = floatval($service['price'] ?? 0);
                        $discount = floatval($data['discount'] ?? 0);
                        $finalAmount = max(0, $price - $discount);
                        $data['total_amount'] = $price;
                        $data['final_amount'] = $finalAmount;
                    }
                } catch (\Throwable $e) {
                    log_message('error', 'Failed to get service price: ' . $e->getMessage());
                }
            }
        }
        
        // Generate invoice number if not provided
        if (empty($data['invoice_number'])) {
            $data['invoice_number'] = $this->generateInvoiceNumber();
        }

        // Log the data for debugging
        log_message('debug', 'InvoiceService::createInvoice - data for insert: ' . json_encode($data));
        // Ensure branch_id is attached for branch-scoped records (staff session or branch assignment)
        if (empty($data['branch_id'])) {
            // Prefer session selected branch
            $sessionBranch = session('selected_branch_id') ?: null;
            if ($sessionBranch) {
                $data['branch_id'] = $sessionBranch;
            } else if (!empty($data['created_by'])) {
                // Fallback: if created_by is provided (user id), try to get the user's assigned branch
                try {
                    $branchUserModel = new \App\Models\BranchStaffModel();
                    $assignments = $branchUserModel->where('user_id', $data['created_by'])->findAll();
                    if (!empty($assignments)) {
                        // pick the first assignment
                        $data['branch_id'] = $assignments[0]['branch_id'] ?? null;
                    }
                } catch (\Throwable $_e) {
                    // ignore failures; it's best-effort
                }
            }
        }

        $result = $this->create($data + ['items' => $items]);

        if ($result['success']) {
            return ['success' => true, 'message' => 'Invoice created', 'invoice_id' => $result['id'] ?? null];
        }

        return ['success' => false, 'message' => $result['message'] ?? 'Failed to create invoice'];
    }

    public function getInvoiceDetails($id)
    {
        $invoice = $this->find($id);
        if (!$invoice) {
            return ['success' => false, 'message' => 'Invoice not found'];
        }

    $items = $invoice['items'] ?? [];
    $totals = $this->calculateTotals($items, $invoice['discount'] ?? 0);

        return ['success' => true, 'data' => ['invoice' => $invoice, 'items' => $items, 'totals' => $totals]];
    }

    public function updateInvoice($id, array $data)
    {
        // No field mapping needed - database uses patient_id and procedure_id directly
        
        $result = $this->update($id, $data);
        if ($result['success']) {
            return ['success' => true, 'message' => 'Invoice updated'];
        }
        return ['success' => false, 'message' => $result['message'] ?? 'Failed to update invoice'];
    }

    public function deleteInvoice($id)
    {
        $result = $this->delete($id);
        if ($result['success']) {
            return ['success' => true, 'message' => 'Invoice deleted'];
        }
        return ['success' => false, 'message' => $result['message'] ?? 'Failed to delete invoice'];
    }

    /**
     * Generate a unique invoice number
     */
    private function generateInvoiceNumber()
    {
        // Get the last invoice number
        $lastInvoice = $this->invoiceModel->orderBy('id', 'DESC')->first();
        
        if ($lastInvoice && !empty($lastInvoice['invoice_number'])) {
            // Extract number from format INV-000001
            if (preg_match('/INV-(\d+)/', $lastInvoice['invoice_number'], $matches)) {
                $nextNumber = intval($matches[1]) + 1;
                return 'INV-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            }
        }
        
        // Fallback: count invoices and add 1
        $count = $this->invoiceModel->countAllResults();
        $nextNumber = $count + 1;
        return 'INV-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
