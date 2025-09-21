<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseAdminController;
use App\Models\ProcedureModel;
use App\Models\UserModel;
use App\Traits\AdminAuthTrait;

class ProcedureController extends BaseAdminController
{
    use AdminAuthTrait;

    protected $procedureModel;
    protected $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->procedureModel = new ProcedureModel();
        $this->userModel = new UserModel();
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

    public function index()
    {
    $user = $this->checkAdminAuth();
        if (!$user) return redirect()->to('/login');

    $procedures = $this->procedureModel->orderBy('procedure_name', 'ASC')->findAll();

        return view('admin/procedures/index', [
            'user' => $user,
            'procedures' => $procedures
        ]);
    }

    /**
     * AJAX: return procedures as JSON for UI consumption
     */
    public function ajaxList()
    {
        $user = $this->getAuthenticatedUserApi();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        try {
            $procs = $this->procedureModel->orderBy('procedure_name', 'ASC')->findAll();
            $result = [];
            foreach ($procs as $p) {
                $result[] = [
                    'id' => $p['id'],
                    'name' => $p['procedure_name'] ?? ($p['title'] ?? 'Procedure'),
                    'price' => $p['fee'] ?? ($p['price'] ?? 0)
                ];
            }
            return $this->response->setJSON(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            log_message('error', 'ProcedureController::ajaxList failed: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to load procedures']);
        }
    }

    public function create()
    {
    $user = $this->checkAdminAuth();
        if (!$user) return redirect()->to('/login');

        // Load patients for the dropdown
        $patients = $this->userModel->where('user_type', 'patient')->findAll();

        return view('admin/procedures/create', [
            'user' => $user,
            'patients' => $patients
        ]);
    }

    public function store()
    {
    $user = $this->checkAdminAuth();
        if (!$user) return redirect()->to('/login');

        // Collect fields from the new form schema
        $serviceId = $this->request->getPost('service_id');

        $data = [
            'user_id' => $this->request->getPost('user_id'),
            'procedure_name' => $this->request->getPost('procedure_name'),
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            // 'category' left blank - we're using service_id instead
            'fee' => $this->request->getPost('fee') ?: 0,
            'treatment_area' => $this->request->getPost('treatment_area') ?: null,
            'procedure_date' => $this->request->getPost('procedure_date'),
            'status' => $this->request->getPost('status') ?? 'scheduled'
        ];

        try {
            // Use scheduleProcedure so the procedure -> service link is created
            $serviceIds = $serviceId ? [$serviceId] : [];
            $result = $this->procedureModel->scheduleProcedure($data, $serviceIds);

            if ($result === false) {
                session()->setFlashdata('error', 'Failed to create procedure');
                return redirect()->to('/admin/procedures/create')->withInput();
            }

            session()->setFlashdata('success', 'Procedure created');
            return redirect()->to('/admin/procedures');
        } catch (\Exception $e) {
            log_message('error', 'ProcedureController::store failed: ' . $e->getMessage());
            session()->setFlashdata('error', 'An error occurred while creating the procedure');
            return redirect()->to('/admin/procedures/create')->withInput();
        }
    }

    public function show($id)
    {
    $user = $this->checkAdminAuth();
        if (!$user) return redirect()->to('/login');

        $proc = $this->procedureModel->find($id);
        if (!$proc) {
            session()->setFlashdata('error', 'Procedure not found');
            return redirect()->to('/admin/procedures');
        }

        return view('admin/procedures/view_edit', ['user' => $user, 'procedure' => $proc]);
    }

    public function edit($id)
    {
        return $this->show($id);
    }

    public function update($id)
    {
    $user = $this->checkAdminAuth();
        if (!$user) return redirect()->to('/login');
        $data = [
            'title' => $this->request->getPost('title'),
            'procedure_date' => $this->request->getPost('procedure_date'),
            // keep category for backward compatibility but prefer service_id
            'category' => $this->request->getPost('category') ?: null,
            'fee' => $this->request->getPost('fee') ?: 0,
            'treatment_area' => $this->request->getPost('treatment_area') ?: null,
            'status' => $this->request->getPost('status') ?? 'scheduled'
        ];

        $serviceId = $this->request->getPost('service_id');

        try {
            // Update procedure record
            $ok = $this->procedureModel->updateProcedure($id, $data);

            // Update linked services: remove existing and link the new one if provided
            $procServiceModel = new \App\Models\ProcedureServiceModel();
            $procServiceModel->removeServicesFromProcedure($id);
            if ($serviceId) {
                $procServiceModel->linkServices($id, [$serviceId]);
            }

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => 'Procedure updated successfully']);
            }

            session()->setFlashdata('success', 'Procedure updated');
            return redirect()->to('/admin/procedures');
        } catch (\Exception $e) {
            log_message('error', 'ProcedureController::update failed: ' . $e->getMessage());
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to update procedure: ' . $e->getMessage()]);
            }

            session()->setFlashdata('error', 'Failed to update procedure');
            return redirect()->to('/admin/procedures');
        }
    }

    public function delete($id)
    {
    $user = $this->checkAdminAuth();
        if (!$user) return redirect()->to('/login');

        $this->procedureModel->delete($id);
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true]);
        }

        session()->setFlashdata('success', 'Procedure deleted');
        return redirect()->to('/admin/procedures');
    }
}