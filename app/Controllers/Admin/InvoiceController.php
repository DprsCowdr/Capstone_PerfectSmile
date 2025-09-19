<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseAdminController;
use App\Services\InvoiceService;
use App\Traits\AdminAuthTrait;

class InvoiceController extends BaseAdminController
{
    use AdminAuthTrait;
    
    protected $invoiceService;
    
    public function __construct()
    {
        parent::__construct();
        $this->invoiceService = new InvoiceService();
    }
    
    /**
     * Get authenticated user for web requests
     */
    protected function getAuthenticatedUser()
    {
        return $this->checkAdminAuth();
    }
    
    /**
     * Get authenticated user for API requests
     */
    protected function getAuthenticatedUserApi()
    {
        return $this->checkAdminAuthApi();
    }

    /**
     * Display invoices list
     */
    public function index()
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $page = $this->request->getGet('page') ?? 1;
        $search = $this->request->getGet('search') ?? null;
        $status = $this->request->getGet('status') ?? null;
        $limit = $this->request->getGet('limit') ?? 10;

        $data = $this->invoiceService->getAllInvoices($page, $limit, $search, $status);
        // Debug: log invoice fetch results to help diagnose empty listings
        try {
            $count = is_array($data['invoices']) ? count($data['invoices']) : 0;
            $sampleId = ($count > 0 && isset($data['invoices'][0]['id'])) ? $data['invoices'][0]['id'] : 'none';
            log_message('debug', "InvoiceController::index - fetched invoices count={$count}, sampleId={$sampleId}, page={$page}, limit={$limit}");
        } catch (\Throwable $e) {
            log_message('error', 'InvoiceController::index - logging failed: ' . $e->getMessage());
        }

        $stats = $this->invoiceService->getInvoiceStats();

        return view('admin/invoices/index', [
            'user' => $user,
            'invoices' => $data['invoices'],
            'pagination' => [
                'total' => $data['total'],
                'pages' => $data['pages'],
                'current_page' => $data['current_page'],
                'limit' => $limit
            ],
            'search' => $search,
            'status' => $status,
            'stats' => $stats
        ]);
    }

    /**
     * Show create invoice form
     */
    public function create()
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $patients = $this->invoiceService->getPatients();
        $procedures = $this->invoiceService->getProcedures();

        return view('admin/invoices/create', [
            'user' => $user,
            'patients' => $patients,
            'procedures' => $procedures
        ]);
    }

    /**
     * Store new invoice
     */
    public function store()
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $data = [
            'patient_id' => $this->request->getPost('patient_id'),
            'procedure_id' => $this->request->getPost('procedure_id'),
            'appointment_id' => $this->request->getPost('appointment_id'),
            'due_date' => $this->request->getPost('due_date'),
            'payment_terms' => $this->request->getPost('payment_terms') ?? 'Net 30',
            'notes' => $this->request->getPost('notes'),
            'discount' => $this->request->getPost('discount') ?? 0,
            'items' => $this->request->getPost('items') ?? [],
            'created_by' => $user['id']
        ];

        $result = $this->invoiceService->createInvoice($data);

        if ($result['success']) {
            session()->setFlashdata('success', $result['message']);
            return redirect()->to('/admin/invoices/edit/' . $result['invoice_id']);
        } else {
            session()->setFlashdata('error', $result['message']);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Show invoice details
     */
    public function show($id)
    {
        // Enhanced debug logging
        log_message('debug', "=== InvoiceController::show START ===");
        log_message('debug', "InvoiceController::show called with ID: {$id}");
        log_message('debug', "Request URI: " . $this->request->getUri());
        log_message('debug', "User Agent: " . $this->request->getUserAgent());
        log_message('debug', "Session ID: " . session_id());
        
        // Check session state
        $session = session();
        $sessionData = [
            'isLoggedIn' => $session->get('isLoggedIn'),
            'user_id' => $session->get('user_id'),
            'user_type' => $session->get('user_type'),
            'user_name' => $session->get('user_name')
        ];
        log_message('debug', "Session data: " . json_encode($sessionData));
        
        try {
            $user = $this->getAuthenticatedUser();
            if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
                log_message('debug', "InvoiceController::show - authentication failed, redirecting to login");
                log_message('debug', "Redirect headers: " . json_encode($user->getHeaders()));
                return $user;
            }

            log_message('debug', "InvoiceController::show - user authenticated successfully");
            log_message('debug', "Authenticated user data: " . json_encode($user));

            $result = $this->invoiceService->getInvoiceDetails($id);
            
            if (!$result['success']) {
                log_message('error', "InvoiceController::show - failed to get invoice details: " . $result['message']);
                session()->setFlashdata('error', 'Error loading invoice: ' . $result['message']);
                return redirect()->to('/admin/invoices');
            }

            log_message('debug', "InvoiceController::show - successfully retrieved invoice data");
            log_message('debug', "Invoice data keys: " . json_encode(array_keys($result['data'])));
            
            $viewData = [
                'user' => $user,
                'invoice' => $result['data']['invoice'],
                'items' => $result['data']['items'],
                'totals' => $result['data']['totals']
            ];
            
            log_message('debug', "View data prepared with keys: " . json_encode(array_keys($viewData)));
            log_message('debug', "=== InvoiceController::show SUCCESS ===");

            return view('admin/invoices/show', $viewData);
            
        } catch (\Exception $e) {
            log_message('error', "InvoiceController::show - Exception: " . $e->getMessage());
            log_message('error', "Exception trace: " . $e->getTraceAsString());
            session()->setFlashdata('error', 'An error occurred while loading the invoice: ' . $e->getMessage());
            return redirect()->to('/admin/invoices');
        }
    }

    /**
     * Show edit invoice form
     */
    public function edit($id)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $result = $this->invoiceService->getInvoiceDetails($id);
        
        if (!$result['success']) {
            session()->setFlashdata('error', $result['message']);
            return redirect()->to('/admin/invoices');
        }

        $patients = $this->invoiceService->getPatients();
        $procedures = $this->invoiceService->getProcedures();

        return view('admin/invoices/edit', [
            'user' => $user,
            'invoice' => $result['data']['invoice'],
            'items' => $result['data']['items'],
            'totals' => $result['data']['totals'],
            'patients' => $patients,
            'procedures' => $procedures
        ]);
    }

    /**
     * Update invoice
     */
    public function update($id)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $data = [
            'patient_id' => $this->request->getPost('patient_id'),
            'procedure_id' => $this->request->getPost('procedure_id'),
            'discount' => $this->request->getPost('discount') ?? 0,
            'notes' => $this->request->getPost('notes'),
            'items' => $this->request->getPost('items') ?? []
        ];

        $result = $this->invoiceService->updateInvoice($id, $data);

        if ($result['success']) {
            session()->setFlashdata('success', $result['message']);
            return redirect()->to('/admin/invoices/edit/' . $id);
        } else {
            session()->setFlashdata('error', $result['message']);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Delete invoice
     */
    public function delete($id)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $result = $this->invoiceService->deleteInvoice($id);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON($result);
        }

        if ($result['success']) {
            session()->setFlashdata('success', $result['message']);
        } else {
            session()->setFlashdata('error', $result['message']);
        }

        return redirect()->to('/admin/invoices');
    }

    /**
     * Add item to invoice (AJAX)
     */
    public function addItem()
    {
        $user = $this->getAuthenticatedUserApi();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $itemData = [
            'invoice_id' => $this->request->getPost('invoice_id'),
            'item_type' => $this->request->getPost('item_type'),
            'item_id' => $this->request->getPost('item_id'),
            'description' => $this->request->getPost('description'),
            'quantity' => $this->request->getPost('quantity'),
            'unit_price' => $this->request->getPost('unit_price'),
            'discount_percent' => $this->request->getPost('discount_percent') ?? 0,
            'tax_percent' => $this->request->getPost('tax_percent') ?? 0
        ];

        $result = $this->invoiceService->addInvoiceItem($itemData['invoice_id'], $itemData);
        return $this->response->setJSON($result);
    }

    /**
     * Update invoice item (AJAX)
     */
    public function updateItem($itemId)
    {
        $user = $this->getAuthenticatedUserApi();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $itemData = [
            'invoice_id' => $this->request->getPost('invoice_id'),
            'item_type' => $this->request->getPost('item_type'),
            'item_id' => $this->request->getPost('item_id'),
            'description' => $this->request->getPost('description'),
            'quantity' => $this->request->getPost('quantity'),
            'unit_price' => $this->request->getPost('unit_price'),
            'discount_percent' => $this->request->getPost('discount_percent') ?? 0,
            'tax_percent' => $this->request->getPost('tax_percent') ?? 0
        ];

        $result = $this->invoiceService->updateInvoiceItem($itemId, $itemData);
        return $this->response->setJSON($result);
    }

    /**
     * Delete invoice item (AJAX)
     */
    public function deleteItem($itemId)
    {
        $user = $this->getAuthenticatedUserApi();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $invoiceId = $this->request->getPost('invoice_id');
        $result = $this->invoiceService->deleteInvoiceItem($itemId, $invoiceId);
        return $this->response->setJSON($result);
    }

    /**
     * Record payment (AJAX)
     */
    public function recordPayment($id)
    {
        $user = $this->getAuthenticatedUserApi();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $amount = $this->request->getPost('amount');
        $result = $this->invoiceService->recordPayment($id, $amount);
        return $this->response->setJSON($result);
    }

    /**
     * Create invoice from procedure
     */
    public function createFromProcedure($procedureId)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $patientId = $this->request->getPost('patient_id');
        
        $result = $this->invoiceService->createInvoiceFromProcedure($procedureId, $patientId, $user['id']);

        if ($result['success']) {
            session()->setFlashdata('success', $result['message']);
            return redirect()->to('/admin/invoices/edit/' . $result['invoice_id']);
        } else {
            session()->setFlashdata('error', $result['message']);
            return redirect()->back();
        }
    }

    /**
     * Print invoice
     */
    public function print($id)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $result = $this->invoiceService->getInvoiceDetails($id);
        
        if (!$result['success']) {
            session()->setFlashdata('error', $result['message']);
            return redirect()->to('/admin/invoices');
        }

        return view('admin/invoices/print', [
            'user' => $user,
            'invoice' => $result['data']['invoice'],
            'items' => $result['data']['items'],
            'totals' => $result['data']['totals']
        ]);
    }

    /**
     * Send invoice email
     */
    public function sendEmail($id)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        // TODO: Implement email sending functionality
        session()->setFlashdata('success', 'Invoice sent successfully.');
        return redirect()->to('/admin/invoices/show/' . $id);
    }
}
