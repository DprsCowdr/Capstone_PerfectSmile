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

        $data = [
            'name' => $this->request->getPost('name'),
            'price' => $this->request->getPost('price') ?: 0,
            'description' => $this->request->getPost('description'),
            'status' => $this->request->getPost('status') ?? 'active'
        ];

        $this->procedureModel->insert($data);
        session()->setFlashdata('success', 'Procedure created');
        return redirect()->to('/admin/procedures');
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
            'category' => $this->request->getPost('category'),
            'fee' => $this->request->getPost('fee') ?: 0,
            'treatment_area' => $this->request->getPost('treatment_area'),
            'status' => $this->request->getPost('status') ?? 'scheduled'
        ];

        try {
            $this->procedureModel->update($id, $data);
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => 'Procedure updated successfully']);
            }
            
            session()->setFlashdata('success', 'Procedure updated');
            return redirect()->to('/admin/procedures');
        } catch (\Exception $e) {
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
